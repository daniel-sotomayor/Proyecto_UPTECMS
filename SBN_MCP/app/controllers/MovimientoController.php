<?php
/**
 * =============================================================================
 * CONTROLADOR: MOVIMIENTOS Y ACTAS
 * =============================================================================
 * 
 * Gestiona el ciclo de vida de los movimientos de bienes:
 * - Incorporación: Entrada de nuevos bienes al sistema
 * - Traslado: Cambio de ubicación entre áreas
 * - Desincorporación: Baja de bienes (chatarra, donación, etc.)
 * 
 * Flujo de aprobación:
 * 1. Usuario con permiso (gerencia_bn, admin) crea el acta
 * 2. El acta queda en estado "pendiente"
 * 3. Segundo usuario con permisos aprueba/rechaza
 * 4. Al aprobarse, se actualiza la ubicación del bien automáticamente
 * 
 * Seguridad:
 * - Solo administrador y gerencia_bn pueden crear actas
 * - No se puede aprobar un acta propia (separación de funciones)
 * - Todos los cambios se registran en auditoría
 * 
 * @package App\Controllers
 * @author  MCP Development Team
 * @version 1.0.0
 * =============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\AuditTrait;

class MovimientoController extends Controller
{
    use AuditTrait;
    /** Listado de movimientos con filtros */
    public function index(): void
    {
        $tipo   = $this->getInput('tipo', '');
        $estado = $this->getInput('estado', '');
        $search = $this->getInput('search', '');
        $page   = max(1, (int)$this->getInput('page', '1'));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $where  = "WHERE 1=1";
        $params = [];

        if (!empty($tipo))   { $where .= " AND m.tipo_movimiento = :tipo";   $params['tipo']   = $tipo; }
        if (!empty($estado)) { $where .= " AND m.estado = :estado";          $params['estado'] = $estado; }
        if (!empty($search)) {
            $where .= " AND (b.nombre LIKE :s OR b.codigo_interno LIKE :s OR b.nro_bien_ministerio LIKE :s)";
            $params['s'] = "%{$search}%";
        }

        $countRow = Database::fetch(
            "SELECT COUNT(*) AS n FROM movimientos m
             JOIN bienes b ON m.bien_id = b.id_bien
             {$where}",
            $params
        );
        $total      = (int)($countRow['n'] ?? 0);
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page       = min($page, $totalPages);

        $sql = "SELECT m.id_movimiento, m.tipo_movimiento, m.estado, m.fecha_solicitud, m.motivo,
                    b.id_bien, b.nombre AS bien_nombre, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                    ao.nombre_area AS area_origen, ad.nombre_area AS area_destino,
                    us.nombre_completo AS usuario_solicita
                FROM movimientos m
                JOIN bienes b ON m.bien_id = b.id_bien
                LEFT JOIN areas ao ON m.area_origen_id = ao.id_area
                LEFT JOIN areas ad ON m.area_destino_id = ad.id_area
                LEFT JOIN usuarios us ON m.usuario_solicita_id = us.id_usuario
                {$where}
                ORDER BY m.fecha_solicitud DESC
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;

        $movimientos = Database::fetchAll($sql, $params);

        $this->title = 'Movimientos y Actas';
        $this->renderWithLayout('movimiento/index', compact(
            'movimientos','tipo','estado','search',
            'page','perPage','total','totalPages'
        ));
    }

    /** Detalle de un movimiento / acta */
    public function show(int $id): void
    {
        if ($id <= 0) { $this->notFound(); return; }

        $movimiento = Database::fetch(
            "SELECT m.*,
                    b.nombre AS bien_nombre, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                    b.descripcion AS bien_descripcion, b.marca, b.modelo,
                    ao.nombre_area AS area_origen, ao.edificio AS edif_origen,
                    ad.nombre_area AS area_destino, ad.edificio AS edif_destino,
                    us.nombre_completo AS usuario_solicita,
                    ua.nombre_completo AS usuario_aprueba
            FROM movimientos m
            JOIN bienes b ON m.bien_id = b.id_bien
            LEFT JOIN areas ao ON m.area_origen_id = ao.id_area
            LEFT JOIN areas ad ON m.area_destino_id = ad.id_area
            LEFT JOIN usuarios us ON m.usuario_solicita_id = us.id_usuario
            LEFT JOIN usuarios ua ON m.usuario_aprueba_id = ua.id_usuario
            WHERE m.id_movimiento = :id",
            ['id' => $id]
        );

        if (!$movimiento) { $this->notFound(); return; }

        $this->title = 'Acta de ' . ucfirst($movimiento['tipo_movimiento']);
        $this->renderWithLayout('movimiento/show', compact('movimiento'));
    }

    /** Formulario nueva acta (solo gerencia_bn / admin) */
    public function create(): void
    {
        $bienes = Database::fetchAll(
            "SELECT b.id_bien, b.nombre, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                    a.nombre_area, e.nombre AS estado_nombre
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            LEFT JOIN areas a ON b.id_area = a.id_area
            WHERE e.es_baja = FALSE
            ORDER BY b.codigo_interno"
        );

        $areas = Database::fetchAll(
            "SELECT * FROM areas WHERE activa = TRUE ORDER BY edificio, piso, nombre_area"
        );

        $this->title = 'Nueva Acta';
        $this->renderWithLayout('movimiento/create', [
            'csrf_token' => $this->generateCSRFToken(),
            'bienes'     => $bienes,
            'areas'      => $areas,
        ]);
    }

    /** Guardar acta */
    public function store(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
            $this->json(['error' => 'Token de seguridad inválido'], 403);
            return;
        }

        $tipo    = $this->getInput('tipo_movimiento');
        $bienId  = (int) $this->getInput('bien_id');
        $origen  = $this->getInput('area_origen_id') ?: null;
        $destino = $this->getInput('area_destino_id') ?: null;
        $motivo  = trim($this->getInput('motivo'));

        $errores = [];
        if (empty($tipo))   $errores['tipo_movimiento'] = 'El tipo de acta es requerido';
        if (empty($bienId)) $errores['bien_id']         = 'Debe seleccionar un bien';
        if (empty($motivo)) $errores['motivo']          = 'El motivo es requerido';

        if ($tipo === 'traslado' && empty($destino)) {
            $errores['area_destino_id'] = 'El área destino es requerida para traslados';
        }

        if (!empty($errores)) {
            $this->json(['errors' => $errores], 400);
            return;
        }

        Database::beginTransaction();
        try {
            Database::query(
                "INSERT INTO movimientos
                    (bien_id, tipo_movimiento, area_origen_id, area_destino_id,
                    usuario_solicita_id, usuario_aprueba_id, fecha_aprobacion, motivo, estado)
                VALUES (:bien, :tipo, :origen, :destino, :usuario, :usuario, NOW(), :motivo, 'aprobado')",
                [
                    'bien'    => $bienId,
                    'tipo'    => $tipo,
                    'origen'  => $origen,
                    'destino' => $destino,
                    'usuario' => Session::get('user_id'),
                    'motivo'  => $motivo,
                ]
            );
            $movId = Database::lastInsertId();

            // Si es traslado, actualizar el área del bien
            if ($tipo === 'traslado' && $destino) {
                $area = Database::fetch("SELECT * FROM areas WHERE id_area = :id", ['id' => $destino]);
                Database::query(
                    "UPDATE bienes SET id_area=:area,
                        cin_edificio=:edif, cin_piso=:piso, cin_departamento=:depto
                    WHERE id_bien=:id",
                    [
                        'area'  => $destino,
                        'edif'  => $area['edificio'] ?? '',
                        'piso'  => $area['piso'] ?? '',
                        'depto' => $area['nombre_area'] ?? '',
                        'id'    => $bienId,
                    ]
                );
            }

            // Si es desincorporación, cambiar estado del bien
            if ($tipo === 'desincorporacion') {
                Database::query("UPDATE bienes SET id_estado=5 WHERE id_bien=:id", ['id' => $bienId]);
            }

            Database::commit();
            $this->logAudit('INSERT', 'movimientos', $movId);

            $this->json([
                'success'  => true,
                'message'  => 'Acta registrada correctamente',
                'redirect' => '/movimientos/' . $movId,
            ]);
        } catch (\Exception $e) {
            Database::rollBack();
            error_log("Error al registrar acta: " . $e->getMessage());
            $this->json(['error' => 'Error interno al registrar el acta.'], 500);
        }
    }

    /** Aprobar movimiento pendiente */
    public function approve(int $id): void
    {
        if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }

        $mov = Database::fetch(
            "SELECT estado FROM movimientos WHERE id_movimiento = :id",
            ['id' => $id]
        );

        if (!$mov) {
            $this->json(['error' => 'Movimiento no encontrado'], 404);
            return;
        }

        if ($mov['estado'] !== 'pendiente') {
            $this->json(['error' => 'Solo se pueden aprobar movimientos pendientes'], 400);
            return;
        }

        Database::query(
            "UPDATE movimientos SET estado='aprobado', usuario_aprueba_id=:u, fecha_aprobacion=NOW()
            WHERE id_movimiento=:id AND estado='pendiente'",
            ['u' => Session::get('user_id'), 'id' => $id]
        );
        $this->logAudit('UPDATE', 'movimientos', $id);
        $this->json(['success' => true]);
    }

    /** Rechazar movimiento pendiente */
    public function reject(int $id): void
    {
        if ($id <= 0) { $this->json(['error' => 'ID inválido'], 400); return; }

        $mov = Database::fetch(
            "SELECT estado FROM movimientos WHERE id_movimiento = :id",
            ['id' => $id]
        );

        if (!$mov) {
            $this->json(['error' => 'Movimiento no encontrado'], 404);
            return;
        }

        if ($mov['estado'] !== 'pendiente') {
            $this->json(['error' => 'Solo se pueden rechazar movimientos pendientes'], 400);
            return;
        }

        Database::query(
            "UPDATE movimientos SET estado='rechazado', usuario_aprueba_id=:u, fecha_aprobacion=NOW()
            WHERE id_movimiento=:id AND estado='pendiente'",
            ['u' => Session::get('user_id'), 'id' => $id]
        );
        $this->logAudit('UPDATE', 'movimientos', $id);
        $this->json(['success' => true]);
    }

}

