<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Helpers\AuditTrait;

class MantenimientoController extends Controller
{
    use AuditTrait;

    public function index(): void
    {
        $bien     = $this->getInput('bien', '');
        $tipo     = $this->getInput('tipo', '');
        $desde    = $this->getInput('desde', '');
        $hasta    = $this->getInput('hasta', '');
        $page     = max(1, (int)$this->getInput('page', '1'));
        $perPage  = 20;
        $offset   = ($page - 1) * $perPage;

        $where  = 'WHERE 1=1';
        $params = [];

        if (!empty($bien))  { $where .= ' AND (b.nombre LIKE :bien OR b.codigo_interno LIKE :bien)'; $params['bien'] = "%{$bien}%"; }
        if (!empty($tipo))  { $where .= ' AND m.tipo_servicio = :tipo';  $params['tipo']  = $tipo; }
        if (!empty($desde)) { $where .= ' AND m.fecha_programada >= :desde'; $params['desde'] = $desde; }
        if (!empty($hasta)) { $where .= ' AND m.fecha_programada <= :hasta'; $params['hasta'] = $hasta; }

        $total = (int)(Database::fetch(
            "SELECT COUNT(*) AS n FROM mantenimientos m
             LEFT JOIN bienes b ON m.bien_id = b.id_bien {$where}", $params
        )['n'] ?? 0);

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = min($page, $totalPages);

        $mantenimientos = Database::fetchAll(
            "SELECT m.*, b.nombre AS bien_nombre, b.codigo_interno,
                    u.nombre_completo AS realizado_por_nombre
             FROM mantenimientos m
             LEFT JOIN bienes b ON m.bien_id = b.id_bien
             LEFT JOIN usuarios u ON m.realizado_por_id = u.id_usuario
             {$where}
             ORDER BY m.fecha_programada DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->title = 'Mantenimientos';
        $this->renderWithLayout('mantenimiento/index', compact(
            'mantenimientos', 'bien', 'tipo', 'desde', 'hasta',
            'page', 'totalPages', 'total'
        ));
    }

    public function show(int $id): void
    {
        $mant = Database::fetch(
            "SELECT m.*, b.nombre AS bien_nombre, b.codigo_interno, b.id_bien,
                    u.nombre_completo AS realizado_por_nombre
             FROM mantenimientos m
             LEFT JOIN bienes b ON m.bien_id = b.id_bien
             LEFT JOIN usuarios u ON m.realizado_por_id = u.id_usuario
             WHERE m.id_mantenimiento = :id",
            ['id' => $id]
        );
        if (!$mant) { $this->notFound(); return; }

        $this->title = 'Mantenimiento #' . $id;
        $this->renderWithLayout('mantenimiento/show', compact('mant'));
    }

    public function create(): void
    {
        $bienes   = Database::fetchAll("SELECT id_bien, nombre, codigo_interno FROM bienes WHERE id_estado != 5 ORDER BY nombre");
        $personal = Database::fetchAll("SELECT id_usuario, nombre_completo FROM usuarios WHERE activo = TRUE ORDER BY nombre_completo");
        $bienPresel = $this->getInput('bien_id', '');

        $this->title = 'Nuevo Mantenimiento';
        $this->renderWithLayout('mantenimiento/create', [
            'csrf_token'  => $this->generateCSRFToken(),
            'bienes'      => $bienes,
            'personal'    => $personal,
            'bienPresel'  => $bienPresel,
        ]);
    }

    public function store(): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
            $this->json(['error' => 'Token de seguridad inválido'], 403); return;
        }

        $bienId = (int)$this->getInput('bien_id');
        $tipo   = $this->getInput('tipo_servicio');

        if (!$bienId || !$tipo) {
            $this->json(['errors' => ['bien_id' => 'Requerido', 'tipo_servicio' => 'Requerido']], 400); return;
        }

        Database::query(
            "INSERT INTO mantenimientos
                (bien_id, tipo_servicio, fecha_programada, fecha_ejecutada,
                 proveedor, tecnico, costo, diagnostico, trabajo_realizado,
                 proxima_fecha_programada, garantia_meses, observaciones, realizado_por_id)
             VALUES
                (:bien, :tipo, :fprog, :fejec, :prov, :tec, :costo,
                 :diag, :trabajo, :proxima, :garantia, :obs, :user)",
            [
                'bien'     => $bienId,
                'tipo'     => $tipo,
                'fprog'    => $this->getInput('fecha_programada') ?: null,
                'fejec'    => $this->getInput('fecha_ejecutada') ?: null,
                'prov'     => $this->getInput('proveedor'),
                'tec'      => $this->getInput('tecnico'),
                'costo'    => $this->getInput('costo') ?: null,
                'diag'     => $this->getInput('diagnostico'),
                'trabajo'  => $this->getInput('trabajo_realizado'),
                'proxima'  => $this->getInput('proxima_fecha_programada') ?: null,
                'garantia' => $this->getInput('garantia_meses') ?: null,
                'obs'      => $this->getInput('observaciones'),
                'user'     => Session::get('user_id'),
            ]
        );
        $newId = Database::lastInsertId();
        $this->logAudit('INSERT', 'mantenimientos', $newId);
        $this->json(['success' => true, 'redirect' => '/mantenimientos/' . $newId]);
    }

    public function edit(int $id): void
    {
        $mant = Database::fetch("SELECT * FROM mantenimientos WHERE id_mantenimiento = :id", ['id' => $id]);
        if (!$mant) { $this->notFound(); return; }

        $bienes   = Database::fetchAll("SELECT id_bien, nombre, codigo_interno FROM bienes WHERE id_estado != 5 ORDER BY nombre");
        $personal = Database::fetchAll("SELECT id_usuario, nombre_completo FROM usuarios WHERE activo = TRUE ORDER BY nombre_completo");

        $this->title = 'Editar Mantenimiento #' . $id;
        $this->renderWithLayout('mantenimiento/edit', [
            'csrf_token' => $this->generateCSRFToken(),
            'mant'       => $mant,
            'bienes'     => $bienes,
            'personal'   => $personal,
        ]);
    }

    public function update(int $id): void
    {
        if (!$this->verifyCSRFToken($this->getInput('csrf_token'))) {
            $this->json(['error' => 'Token de seguridad inválido'], 403); return;
        }

        Database::query(
            "UPDATE mantenimientos SET
                tipo_servicio = :tipo, fecha_programada = :fprog, fecha_ejecutada = :fejec,
                proveedor = :prov, tecnico = :tec, costo = :costo,
                diagnostico = :diag, trabajo_realizado = :trabajo,
                proxima_fecha_programada = :proxima, garantia_meses = :garantia,
                observaciones = :obs, realizado_por_id = :user
             WHERE id_mantenimiento = :id",
            [
                'tipo'     => $this->getInput('tipo_servicio'),
                'fprog'    => $this->getInput('fecha_programada') ?: null,
                'fejec'    => $this->getInput('fecha_ejecutada') ?: null,
                'prov'     => $this->getInput('proveedor'),
                'tec'      => $this->getInput('tecnico'),
                'costo'    => $this->getInput('costo') ?: null,
                'diag'     => $this->getInput('diagnostico'),
                'trabajo'  => $this->getInput('trabajo_realizado'),
                'proxima'  => $this->getInput('proxima_fecha_programada') ?: null,
                'garantia' => $this->getInput('garantia_meses') ?: null,
                'obs'      => $this->getInput('observaciones'),
                'user'     => Session::get('user_id'),
                'id'       => $id,
            ]
        );
        $this->logAudit('UPDATE', 'mantenimientos', $id);
        $this->json(['success' => true, 'redirect' => '/mantenimientos/' . $id]);
    }
}
