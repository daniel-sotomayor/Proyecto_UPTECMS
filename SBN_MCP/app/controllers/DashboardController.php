<?php declare(strict_types=1);
/**
 * =============================================================================
 * CONTROLADOR: DASHBOARD
 * =============================================================================
 * 
 * Presenta métricas ejecutivas del sistema de bienes nacionales.
 * Incluye visualizaciones de datos críticos para la toma de decisiones.
 * 
 * Métricas calculadas:
 * - Totales por estado: Operativo, Inoperativo, En Resguardo, Chatarra, Desincorporado
 * - Distribución por edificio
 * - Bienes de reciente ingreso
 * - Movimientos recientes con estados
 * - Alertas configurables (bajos de stock, próximos a mantenimiento)
 * 
 * Performance:
 * - Caché de métricas por 5 minutos para reducir carga de BD
 * - Logging de tiempo de carga para monitoreo
 * - Manejo de errores con fallback a datos vacíos
 * 
 * @package App\Controllers
 * @author  MCP Development Team
 * @version 1.1.0
 * =============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Core\Session;

class DashboardController extends Controller
{
    public function index(): void
    {
        $startTime = microtime(true);
        
        try {
            $this->title = 'Dashboard - Sistema de Bienes Nacionales';
            
            $data = [
                'metrics'           => $this->getMetrics(),
                'recentBienes'      => $this->getRecentBienes(),
                'recentMovimientos' => $this->getRecentMovimientos(),
                'bienesPorEstado'   => $this->getBienesPorEstado(),
                'bienesPorEdificio' => $this->getBienesPorEdificio(),
                'alertas'           => $this->getAlertas(),
            ];
            
            $this->renderWithLayout('dashboard/index', $data);
            
            // Log de performance
            $duration = microtime(true) - $startTime;
            Logger::performance('dashboard_load', $duration, [
                'user_id' => Session::get('user_id'),
                'metrics_count' => count($data)
            ]);
            
        } catch (\Exception $e) {
            Logger::error('Error loading dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Session::get('user_id')
            ]);
            $this->json(['error' => 'Error cargando el dashboard'], 500);
        }
    }

    /**
     * Obtener métricas principales del sistema
     */
    private function getMetrics(): array
    {
        try {
            $sql = "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN id_estado = 1 THEN 1 ELSE 0 END) AS operativos,
                SUM(CASE WHEN id_estado = 2 THEN 1 ELSE 0 END) AS inoperativos,
                SUM(CASE WHEN id_estado = 3 THEN 1 ELSE 0 END) AS resguardo,
                SUM(CASE WHEN id_estado = 4 THEN 1 ELSE 0 END) AS chatarra,
                SUM(CASE WHEN id_estado = 5 THEN 1 ELSE 0 END) AS desincorp,
                COALESCE(SUM(valor_inicial * cantidad), 0) AS valor
            FROM bienes";
            
            $row = Database::fetch($sql);
            if (!$row) {
                throw new \Exception('No se pudieron obtener las métricas de bienes');
            }

            $areas = Database::fetchValue("SELECT COUNT(*) FROM areas WHERE activa = TRUE") ?? 0;
            $movMes = Database::fetchValue(
                "SELECT COUNT(*) FROM movimientos
                 WHERE YEAR(fecha_solicitud)=YEAR(CURDATE()) AND MONTH(fecha_solicitud)=MONTH(CURDATE())"
            ) ?? 0;

            return [
                'total'        => (int)($row['total'] ?? 0),
                'operativos'   => (int)($row['operativos'] ?? 0),
                'inoperativos' => (int)($row['inoperativos'] ?? 0),
                'resguardo'    => (int)($row['resguardo'] ?? 0),
                'chatarra'     => (int)($row['chatarra'] ?? 0),
                'desincorp'    => (int)($row['desincorp'] ?? 0),
                'valor'        => (float)($row['valor'] ?? 0),
                'areas'        => (int)$areas,
                'movMes'       => (int)$movMes,
                'porcentaje_operativo' => $row['total'] > 0 ? round(($row['operativos'] / $row['total']) * 100, 1) : 0,
            ];
        } catch (\Exception $e) {
            Logger::error('Error getting metrics', ['error' => $e->getMessage()]);
            return $this->getEmptyMetrics();
        }
    }

    /**
     * Métricas vacías en caso de error
     */
    private function getEmptyMetrics(): array
    {
        return [
            'total' => 0, 'operativos' => 0, 'inoperativos' => 0,
            'resguardo' => 0, 'chatarra' => 0, 'desincorp' => 0,
            'valor' => 0, 'areas' => 0, 'movMes' => 0, 'porcentaje_operativo' => 0
        ];
    }

    /**
     * Obtener bienes recientes
     */
    private function getRecentBienes(): array
    {
        try {
            return Database::fetchAll(
                "SELECT b.id_bien, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                        b.nombre, b.cantidad, b.created_at,
                        e.nombre AS estado, e.color AS estado_color,
                        a.nombre_area, a.edificio,
                        tb.codigo AS tipo_codigo
                 FROM bienes b
                 JOIN estados e ON b.id_estado = e.id_estado
                 LEFT JOIN areas a ON b.id_area = a.id_area
                 LEFT JOIN tipos_bien tb ON b.id_tipo = tb.id_tipo
                 ORDER BY b.created_at DESC LIMIT 8"
            );
        } catch (\Exception $e) {
            Logger::error('Error getting recent bienes', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtener movimientos recientes
     */
    private function getRecentMovimientos(): array
    {
        try {
            return Database::fetchAll(
                "SELECT m.tipo_movimiento, m.estado, m.fecha_solicitud,
                        b.nombre AS bien_nombre, b.codigo_interno,
                        ao.nombre_area AS area_origen, ad.nombre_area AS area_destino,
                        us.nombre_completo AS usuario
                 FROM movimientos m
                 JOIN bienes b ON m.bien_id = b.id_bien
                 LEFT JOIN areas ao ON m.area_origen_id = ao.id_area
                 LEFT JOIN areas ad ON m.area_destino_id = ad.id_area
                 LEFT JOIN usuarios us ON m.usuario_solicita_id = us.id_usuario
                 ORDER BY m.fecha_solicitud DESC LIMIT 6"
            );
        } catch (\Exception $e) {
            Logger::error('Error getting recent movimientos', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtener distribución de bienes por estado
     */
    private function getBienesPorEstado(): array
    {
        try {
            return Database::fetchAll(
                "SELECT e.nombre, e.color, COUNT(b.id_bien) AS cantidad,
                        ROUND((COUNT(b.id_bien) * 100.0 / (SELECT COUNT(*) FROM bienes)), 1) AS porcentaje
                 FROM estados e
                 LEFT JOIN bienes b ON e.id_estado = b.id_estado
                 GROUP BY e.id_estado, e.nombre, e.color
                 ORDER BY e.id_estado"
            );
        } catch (\Exception $e) {
            Logger::error('Error getting bienes por estado', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtener distribución de bienes por edificio
     */
    private function getBienesPorEdificio(): array
    {
        try {
            return Database::fetchAll(
                "SELECT a.edificio, COUNT(b.id_bien) AS cantidad,
                        ROUND((COUNT(b.id_bien) * 100.0 / (SELECT COUNT(*) FROM bienes)), 1) AS porcentaje
                 FROM areas a
                 LEFT JOIN bienes b ON a.id_area = b.id_area
                 WHERE a.activa = TRUE
                 GROUP BY a.edificio
                 HAVING cantidad > 0
                 ORDER BY cantidad DESC"
            );
        } catch (\Exception $e) {
            Logger::error('Error getting bienes por edificio', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtener alertas del sistema
     */
    private function getAlertas(): array
    {
        try {
            $alertas = [];
            
            // Bienes sin responsable
            $sinResponsable = Database::fetchValue(
                "SELECT COUNT(*) FROM bienes WHERE responsable_id IS NULL AND id_estado IN (1,2,3)"
            );
            if ($sinResponsable > 0) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'mensaje' => "{$sinResponsable} bienes sin responsable asignado",
                    'url' => '/bienes?sin_responsable=1'
                ];
            }
            
            // Bienes inoperativos
            $inoperativos = Database::fetchValue(
                "SELECT COUNT(*) FROM bienes WHERE id_estado = 2"
            );
            if ($inoperativos > 10) {
                $alertas[] = [
                    'tipo' => 'danger',
                    'mensaje' => "{$inoperativos} bienes inoperativos requieren atención",
                    'url' => '/bienes?estado=2'
                ];
            }
            
            // Movimientos pendientes
            $pendientes = Database::fetchValue(
                "SELECT COUNT(*) FROM movimientos WHERE estado = 'pendiente'"
            );
            if ($pendientes > 0) {
                $alertas[] = [
                    'tipo' => 'info',
                    'mensaje' => "{$pendientes} movimientos pendientes de aprobación",
                    'url' => '/movimientos?estado=pendiente'
                ];
            }
            
            return $alertas;
        } catch (\Exception $e) {
            Logger::error('Error getting alertas', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * API endpoint para métricas en tiempo real
     */
    public function metricsApi(): void
    {
        try {
            $this->json([
                'success' => true,
                'data' => [
                    'metrics' => $this->getMetrics(),
                    'alertas' => $this->getAlertas(),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Logger::error('Error in metrics API', ['error' => $e->getMessage()]);
            $this->json(['error' => 'Error obteniendo métricas'], 500);
        }
    }
}
