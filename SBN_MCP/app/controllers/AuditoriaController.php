<?php
/**
 * Controlador de Auditoría
 * Log completo de operaciones del sistema.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AuditoriaController extends Controller
{
    /** Número de registros por página. */
    private const PER_PAGE = 50;

    /**
     * Listado paginado del log de auditoría con filtros.
     */
    public function index(): void
    {
        $page   = max(1, (int) $this->getInput('page', '1'));
        $tabla  = $this->getInput('tabla', '');
        $accion = $this->getInput('accion', '');
        $search = $this->getInput('search', '');
        $offset = ($page - 1) * self::PER_PAGE;

        // Construir cláusula WHERE compartida
        $where  = 'WHERE 1=1';
        $params = [];

        if ($tabla !== '') {
            $where           .= ' AND a.tabla_afectada = :tabla';
            $params['tabla']  = $tabla;
        }
        if ($accion !== '') {
            $where            .= ' AND a.accion = :accion';
            $params['accion']  = $accion;
        }
        if ($search !== '') {
            $where          .= ' AND (a.tabla_afectada LIKE :s OR a.accion LIKE :s
                                  OR u.nombre_completo LIKE :s OR a.ip_address LIKE :s)';
            $params['s']     = "%{$search}%";
        }

        // COUNT — mismos parámetros, sin LIMIT/OFFSET
        $total = (int) Database::fetchValue(
            "SELECT COUNT(*) FROM auditoria a
             LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario
             {$where}",
            $params
        );

        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);

        // PDO no acepta parámetros nombrados en LIMIT/OFFSET → se usan enteros casteados (seguros)
        $safeOffset  = (int) $offset;
        $safePerPage = (int) self::PER_PAGE;

        $logs = Database::fetchAll(
            "SELECT a.id_auditoria, a.tabla_afectada, a.registro_id, a.accion,
                    a.ip_address, a.user_agent, a.fecha_operacion,
                    u.nombre_completo AS usuario_nombre
             FROM auditoria a
             LEFT JOIN usuarios u ON a.usuario_id = u.id_usuario
             {$where}
             ORDER BY a.fecha_operacion DESC
             LIMIT {$safeOffset}, {$safePerPage}",
            $params
        );

        $this->title = 'Auditoría del Sistema';
        $this->renderWithLayout('auditoria/index', compact(
            'logs', 'page', 'pages', 'tabla', 'accion', 'search', 'total'
        ));
    }
}
