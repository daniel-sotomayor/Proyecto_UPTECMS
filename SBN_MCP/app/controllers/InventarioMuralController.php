<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class InventarioMuralController extends Controller
{
    /**
     * Listado de todas las áreas con resumen de bienes.
     * Cada área muestra cuántos bienes tiene y su valor total.
     * La suma de todos los departamentos coincide con el inventario global.
     */
    public function index(): void
    {
        // Resumen global para verificar coherencia con BM-1
        $global = Database::fetch("
            SELECT COUNT(*) AS total_global, COALESCE(SUM(b.valor_inicial), 0) AS valor_global
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            WHERE e.es_baja = FALSE
        ");

        // Áreas con bienes activos agrupadas por edificio
        $areas = Database::fetchAll("
            SELECT
                a.id_area,
                a.nombre_area,
                a.edificio,
                a.piso,
                COUNT(b.id_bien)                          AS total_bienes,
                COALESCE(SUM(b.valor_inicial), 0)         AS valor_total,
                SUM(CASE WHEN e.nombre = 'Operativo'    THEN 1 ELSE 0 END) AS operativos,
                SUM(CASE WHEN e.nombre = 'Inoperativo'  THEN 1 ELSE 0 END) AS inoperativos,
                SUM(CASE WHEN e.nombre = 'En Resguardo' THEN 1 ELSE 0 END) AS resguardo
            FROM areas a
            LEFT JOIN bienes b ON b.id_area = a.id_area
            LEFT JOIN estados e ON b.id_estado = e.id_estado AND e.es_baja = FALSE
            GROUP BY a.id_area, a.nombre_area, a.edificio, a.piso
            ORDER BY a.edificio, a.piso, a.nombre_area
        ");

        // Bienes sin área asignada
        $sin_area = Database::fetch("
            SELECT COUNT(*) AS total, COALESCE(SUM(b.valor_inicial), 0) AS valor
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            WHERE b.id_area IS NULL AND e.es_baja = FALSE
        ");

        // Agrupar por edificio para la vista
        $por_edificio = [];
        foreach ($areas as $a) {
            $edificio = $a['edificio'] ?: 'Sin edificio';
            $por_edificio[$edificio][] = $a;
        }

        $this->title = 'Control Mural — Inventario por Departamento';
        $this->renderWithLayout('inventario_mural/index', compact(
            'por_edificio', 'global', 'sin_area'
        ));
    }

    /**
     * Inventario detallado de un área específica (Control Mural del departamento).
     * Los bienes aquí deben coincidir exactamente con los del inventario global.
     */
    public function area(int $id): void
    {
        $area = Database::fetch("
            SELECT id_area, nombre_area, edificio, piso, descripcion
            FROM areas WHERE id_area = :id
        ", ['id' => $id]);

        if (!$area) {
            $this->notFound();
            return;
        }

        $bienes = Database::fetchAll("
            SELECT
                b.id_bien, b.codigo_sudebip, b.codigo_interno,
                b.nro_bien_ministerio, b.es_sn, b.nombre, b.descripcion,
                b.marca, b.modelo, b.serial, b.valor_inicial, b.updated_at,
                b.cin_oficina, b.cin_posicion,
                t.codigo AS tipo_codigo, t.nombre AS tipo_nombre,
                e.nombre AS estado_nombre, e.color AS estado_color
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            JOIN tipos_bien t ON b.id_tipo = t.id_tipo
            WHERE b.id_area = :id AND e.es_baja = FALSE
            ORDER BY b.codigo_interno
        ", ['id' => $id]);

        $totales = Database::fetch("
            SELECT
                COUNT(*)                                              AS total,
                COALESCE(SUM(b.valor_inicial), 0)                    AS valor_total,
                SUM(CASE WHEN e.nombre = 'Operativo'    THEN 1 ELSE 0 END) AS operativos,
                SUM(CASE WHEN e.nombre = 'Inoperativo'  THEN 1 ELSE 0 END) AS inoperativos,
                SUM(CASE WHEN e.nombre = 'En Resguardo' THEN 1 ELSE 0 END) AS resguardo
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            WHERE b.id_area = :id AND e.es_baja = FALSE
        ", ['id' => $id]);

        // Exportar CSV del área
        if (($_GET['export'] ?? '') === 'csv') {
            $nombre_archivo = 'mural_' . preg_replace('/[^a-z0-9]/i', '_', $area['nombre_area'])
                            . '_' . date('Ymd') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Código Interno','Nro. Bien','Nombre','Marca','Modelo','Serial','Clasificación','Estado','Oficina','Posición','Valor (Bs.)'], ';');
            foreach ($bienes as $b) {
                fputcsv($out, [
                    $b['codigo_interno'] ?? '—',
                    $b['es_sn'] ? 'S/N' : ($b['nro_bien_ministerio'] ?? '—'),
                    $b['nombre'],
                    $b['marca'] ?? '—',
                    $b['modelo'] ?? '—',
                    $b['serial'] ?? '—',
                    $b['tipo_codigo'] . ' - ' . $b['tipo_nombre'],
                    $b['estado_nombre'],
                    $b['cin_oficina'] ?? '—',
                    $b['cin_posicion'] ?? '—',
                    number_format((float)$b['valor_inicial'], 2, ',', '.'),
                ], ';');
            }
            fclose($out);
            throw new \App\Core\HttpResponseException(200);
        }

        $this->title = 'Control Mural — ' . $area['nombre_area'];
        $this->renderWithLayout('inventario_mural/area', compact(
            'area', 'bienes', 'totales'
        ));
    }
}
