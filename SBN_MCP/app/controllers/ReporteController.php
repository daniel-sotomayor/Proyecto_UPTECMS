<?php
/**
 * =============================================================================
 * CONTROLADOR: REPORTES OFICIALES
 * =============================================================================
 * 
 * Genera los reportes formales según normativas LOBIP y MCP:
 * 
 * Reportes implementados:
 * - BM-1: Inventario de Bienes Activos (Operativo, Inoperativo, Resguardo)
 * - BM-2: Bienes Desincorporados (Chatarra, Donación, Transferencia)
 * - BM-3: Movimientos del Período (Incorporaciones, Traslados, Desincorporaciones)
 * - BM-4: Resumen Ejecutivo (Métricas y distribuciones)
 * 
 * Formatos de exportación:
 * - HTML: Visualización en navegador con filtros
 * - PDF: Documento formal para archivo
 * - CSV: Datos para análisis en Excel
 * 
 * @package App\Controllers
 * @author  MCP Development Team
 * @version 1.0.0
 * =============================================================================
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ReporteController extends Controller
{
    public function index(): void
    {
        $this->title = 'Reportes de Bienes Nacionales';
        $this->renderWithLayout('reportes/index');
    }

    // ── Exportación CSV genérica ──────────────────────────────────────────────
    private function exportCsv(string $filename, array $headers, array $rows): void
    {
        // Sanitizar nombre de archivo — previene path traversal y header injection
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-.]/', '', basename($filename));
        if (empty($safeFilename)) {
            $safeFilename = 'reporte_' . date('Ymd') . '.csv';
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($out, $headers, ';');
        foreach ($rows as $row) fputcsv($out, $row, ';');
        fclose($out);
        throw new \App\Core\HttpResponseException(200);
    }
    
    // ── Exportación PDF ─────────────────────────────────────────────────────
    private function exportPdf(string $titulo, string $vista, array $data, string $filename): void
    {
        // Sanitizar nombre de archivo
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-.]/', '', basename($filename));
        if (empty($safeFilename)) {
            $safeFilename = 'reporte_' . date('Ymd') . '.pdf';
        }
        ob_clean();
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Bienes Nacionales MCP');
        $pdf->SetAuthor('Maternidad Concepción Palacios');
        $pdf->SetTitle(htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetMargins(10, 15, 10);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Maternidad Concepción Palacios', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, $titulo, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);
        $html = $this->generarHtmlReporte($vista, $data);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($safeFilename, 'D');
        throw new \App\Core\HttpResponseException(200);
    }
    
    private function generarHtmlReporte(string $vista, array $data): string
    {
        switch ($vista) {
            case 'bm1':
                return $this->htmlBm1($data);
            case 'bm2':
                return $this->htmlBm2($data);
            case 'bm3':
                return $this->htmlBm3($data);
            case 'bm4':
                return $this->htmlBm4($data);
            default:
                return '';
        }
    }
    
    private function htmlBm1(array $data): string
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0;font-weight:bold;">';
        $html .= '<th>Código</th><th>Nro. Bien</th><th>Nombre</th><th>Clasificación</th><th>Estado</th><th>Área</th><th>Valor</th>';
        $html .= '</tr>';
        foreach ($data['bienes'] as $b) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($b['codigo_interno']) . '</td>';
            $html .= '<td>' . ($b['nro_bien_ministerio'] ?: 'S/N') . '</td>';
            $html .= '<td>' . htmlspecialchars($b['nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($b['tipo_codigo'] . ' - ' . $b['tipo_nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($b['estado_nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($b['nombre_area'] . ' / ' . $b['edificio']) . '</td>';
            $html .= '<td align="right">' . number_format($b['valor_inicial'], 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }
        $html .= '<tr style="background-color:#e8e8e8;font-weight:bold;">';
        $html .= '<td colspan="6" align="right">TOTALES:</td>';
        $html .= '<td align="right">' . number_format($data['valor_total'], 2, ',', '.') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        return $html;
    }
    
    private function htmlBm2(array $data): string
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0;font-weight:bold;">';
        $html .= '<th>Código</th><th>Nro. Bien</th><th>Nombre</th><th>Estado</th><th>Área</th><th>Actualizado</th><th>Valor</th>';
        $html .= '</tr>';
        foreach ($data['bienes'] as $b) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($b['codigo_interno']) . '</td>';
            $html .= '<td>' . ($b['nro_bien_ministerio'] ?: 'S/N') . '</td>';
            $html .= '<td>' . htmlspecialchars($b['nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($b['estado_nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($b['nombre_area']) . '</td>';
            $html .= '<td>' . ($b['updated_at'] ? date('d/m/Y', strtotime($b['updated_at'])) : '') . '</td>';
            $html .= '<td align="right">' . number_format($b['valor_inicial'], 2, ',', '.') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    private function htmlBm3(array $data): string
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0;font-weight:bold;">';
        $html .= '<th>Fecha</th><th>Tipo</th><th>Estado</th><th>Bien</th><th>Código</th><th>Origen</th><th>Destino</th>';
        $html .= '</tr>';
        foreach ($data['movimientos'] as $m) {
            $html .= '<tr>';
            $html .= '<td>' . date('d/m/Y H:i', strtotime($m['fecha_solicitud'])) . '</td>';
            $html .= '<td>' . ucfirst($m['tipo_movimiento']) . '</td>';
            $html .= '<td>' . ucfirst($m['estado']) . '</td>';
            $html .= '<td>' . htmlspecialchars($m['bien_nombre']) . '</td>';
            $html .= '<td>' . htmlspecialchars($m['codigo_interno']) . '</td>';
            $html .= '<td>' . ($m['area_origen'] ?: 'N/A') . '</td>';
            $html .= '<td>' . ($m['area_destino'] ?: 'N/A') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    private function htmlBm4(array $data): string
    {
        $html = '<h3>Resumen General</h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr><td>Total Bienes:</td><td>' . number_format($data['resumen']['total_bienes']) . '</td></tr>';
        $html .= '<tr><td>Bienes Activos:</td><td>' . number_format($data['resumen']['bienes_activos']) . '</td></tr>';
        $html .= '<tr><td>Bienes de Baja:</td><td>' . number_format($data['resumen']['bienes_baja']) . '</td></tr>';
        $html .= '<tr><td>Valor Total:</td><td>' . number_format($data['resumen']['valor_total'], 2, ',', '.') . ' Bs.</td></tr>';
        $html .= '</table>';
        
        $html .= '<h3>Por Estado</h3>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color:#f0f0f0;font-weight:bold;"><th>Estado</th><th>Cantidad</th><th>Valor</th></tr>';
        foreach ($data['por_estado'] as $e) {
            $html .= '<tr><td>' . htmlspecialchars($e['nombre']) . '</td>';
            $html .= '<td>' . number_format($e['cantidad']) . '</td>';
            $html .= '<td>' . number_format($e['valor'], 2, ',', '.') . '</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }

    // ── Exportación Excel ───────────────────────────────────────────────────
    private function exportExcel(string $titulo, string $hoja, array $headers, array $rows, string $filename): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($hoja);
        
        // Título
        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Headers
        $col = 1;
        foreach ($headers as $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '3';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle($cell)->getFill()->getStartColor()->setRGB('E2E8F0');
            $col++;
        }
        
        // Datos
        $row = 4;
        foreach ($rows as $dataRow) {
            $col = 1;
            foreach ($dataRow as $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->setCellValue($cell, $value);
                $col++;
            }
            $row++;
        }
        
        // Auto-ajustar columnas
        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        
        // Sanitizar nombre de archivo
        $safeFilename = preg_replace('/[^a-zA-Z0-9_\-.]/', '', basename($filename));
        if (empty($safeFilename)) {
            $safeFilename = 'reporte_' . date('Ymd') . '.xlsx';
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        throw new \App\Core\HttpResponseException(200);
    }

    public function bm1(): void
    {
        $aggregates = Database::fetch("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_inicial), 0) AS valor_total
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            WHERE e.es_baja = FALSE
        ");
        $total = (int) ($aggregates['total'] ?? 0);
        $valor_total = (float) ($aggregates['valor_total'] ?? 0);

        $bienes = Database::fetchAll("
            SELECT b.id_bien, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                   b.nombre, b.valor_inicial, b.updated_at,
                   t.codigo AS tipo_codigo, t.nombre AS tipo_nombre,
                   e.nombre AS estado_nombre, e.color AS estado_color,
                   a.nombre_area, a.edificio
            FROM bienes b
            JOIN tipos_bien t ON b.id_tipo = t.id_tipo
            JOIN estados e ON b.id_estado = e.id_estado
            LEFT JOIN areas a ON b.id_area = a.id_area
            WHERE e.es_baja = FALSE
            ORDER BY b.codigo_interno
        ");

        if (($_GET['export'] ?? '') === 'csv') {
            $this->exportCsv('BM1_inventario_activo_' . date('Ymd') . '.csv',
                ['Codigo Interno','Nro. Bien','Nombre','Clasificacion','Estado','Area','Edificio','Valor (Bs.)'],
                array_map(fn($b) => [
                    $b['codigo_interno'], $b['nro_bien_ministerio'] ?: 'S/N',
                    $b['nombre'], $b['tipo_codigo'].' - '.$b['tipo_nombre'],
                    $b['estado_nombre'], $b['nombre_area'], $b['edificio'],
                    number_format($b['valor_inicial'],2,',','.')
                ], $bienes)
            );
        }
        
        if (($_GET['export'] ?? '') === 'pdf') {
            $this->exportPdf('Reporte BM-1 - Inventario Activo', 'bm1', 
                ['bienes' => $bienes, 'valor_total' => $valor_total],
                'BM1_inventario_activo_' . date('Ymd') . '.pdf');
        }
        
        if (($_GET['export'] ?? '') === 'excel') {
            $this->exportExcel('Reporte BM-1 - Inventario Activo', 'BM1',
                ['Código Interno','Nro. Bien','Nombre','Clasificación','Estado','Área','Edificio','Valor (Bs.)'],
                array_map(fn($b) => [
                    $b['codigo_interno'], $b['nro_bien_ministerio'] ?: 'S/N',
                    $b['nombre'], $b['tipo_codigo'].' - '.$b['tipo_nombre'],
                    $b['estado_nombre'], $b['nombre_area'], $b['edificio'],
                    $b['valor_inicial']
                ], $bienes),
                'BM1_inventario_activo_' . date('Ymd') . '.xlsx'
            );
        }

        $this->title = 'Reporte BM-1 - Inventario Activo';
        $this->renderWithLayout('reportes/bm1', compact('bienes', 'total', 'valor_total'));
    }

    public function bm2(): void
    {
        $aggregates = Database::fetch("
            SELECT COUNT(*) AS total, COALESCE(SUM(valor_inicial), 0) AS valor_total
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            WHERE e.es_baja = TRUE
        ");
        $total = (int) ($aggregates['total'] ?? 0);
        $valor_total = (float) ($aggregates['valor_total'] ?? 0);

        $bienes = Database::fetchAll("
            SELECT b.codigo_interno, b.nro_bien_ministerio, b.nombre, b.updated_at, b.valor_inicial,
                   t.codigo AS tipo_codigo, t.nombre AS tipo_nombre,
                   e.nombre AS estado_nombre, e.color AS estado_color,
                   a.nombre_area, a.edificio
            FROM bienes b
            JOIN tipos_bien t ON b.id_tipo = t.id_tipo
            JOIN estados e ON b.id_estado = e.id_estado
            LEFT JOIN areas a ON b.id_area = a.id_area
            WHERE e.es_baja = TRUE
            ORDER BY e.id_estado, b.updated_at DESC
        ");

        if (($_GET['export'] ?? '') === 'csv') {
            $this->exportCsv('BM2_desincorporados_' . date('Ymd') . '.csv',
                ['Codigo Interno','Nro. Bien','Nombre','Clasificacion','Estado','Area','Edificio','Ultima Actualizacion','Valor (Bs.)'],
                array_map(fn($b) => [
                    $b['codigo_interno'], $b['nro_bien_ministerio'] ?: 'S/N',
                    $b['nombre'], $b['tipo_codigo'].' - '.$b['tipo_nombre'],
                    $b['estado_nombre'], $b['nombre_area'], $b['edificio'],
                    $b['updated_at'] ? date('d/m/Y', strtotime($b['updated_at'])) : '',
                    number_format($b['valor_inicial'],2,',','.')
                ], $bienes)
            );
        }

        $this->title = 'Reporte BM-2 - Bienes Desincorporados';
        $this->renderWithLayout('reportes/bm2', compact('bienes', 'total', 'valor_total'));
    }

    public function bm3(): void
    {
        $fecha_desde = $this->getSanitizedInput('fecha_desde', date('Y-m-01'));
        $fecha_hasta = $this->getSanitizedInput('fecha_hasta', date('Y-m-d'));
        $tipo_filtro = $this->getSanitizedInput('tipo', '');

        $sql = "SELECT m.id_movimiento, m.tipo_movimiento, m.estado,
                       m.fecha_solicitud, m.fecha_aprobacion, m.motivo,
                       b.nombre AS bien_nombre, b.codigo_interno, b.nro_bien_ministerio, b.es_sn,
                       ao.nombre_area AS area_origen, ad.nombre_area AS area_destino,
                       us.nombre_completo AS usuario_solicita,
                       ua.nombre_completo AS usuario_aprueba
                FROM movimientos m
                JOIN bienes b ON m.bien_id = b.id_bien
                LEFT JOIN areas ao ON m.area_origen_id = ao.id_area
                LEFT JOIN areas ad ON m.area_destino_id = ad.id_area
                LEFT JOIN usuarios us ON m.usuario_solicita_id = us.id_usuario
                LEFT JOIN usuarios ua ON m.usuario_aprueba_id = ua.id_usuario
                WHERE DATE(m.fecha_solicitud) BETWEEN :desde AND :hasta";

        $params = ['desde' => $fecha_desde, 'hasta' => $fecha_hasta];

        if (!empty($tipo_filtro)) {
            $sql .= " AND m.tipo_movimiento = :tipo";
            $params['tipo'] = $tipo_filtro;
        }

        $sql .= " ORDER BY m.fecha_solicitud DESC";

        $movimientos = Database::fetchAll($sql, $params);

        if (($_GET['export'] ?? '') === 'csv') {
            $this->exportCsv('BM3_movimientos_' . date('Ymd') . '.csv',
                ['Fecha', 'Tipo', 'Estado', 'Bien', 'Codigo', 'Origen', 'Destino', 'Solicitante'],
                array_map(fn($m) => [
                    date('d/m/Y H:i', strtotime($m['fecha_solicitud'])),
                    ucfirst($m['tipo_movimiento']),
                    ucfirst($m['estado']),
                    $m['bien_nombre'],
                    $m['codigo_interno'],
                    $m['area_origen'] ?: 'N/A',
                    $m['area_destino'] ?: 'N/A',
                    $m['usuario_solicita']
                ], $movimientos)
            );
        }

        // Totales por tipo
        $totales_tipo = array_count_values(array_column($movimientos, 'tipo_movimiento'));

        $this->title = 'Reporte BM-3 - Movimientos del Período';
        $this->renderWithLayout('reportes/bm3', compact('movimientos', 'fecha_desde', 'fecha_hasta', 'tipo_filtro', 'totales_tipo'));
    }

    public function bm4(): void
    {
        // Resumen general
        $resumen = Database::fetch("
            SELECT
                COUNT(*) AS total_bienes,
                SUM(b.valor_inicial) AS valor_total,
                SUM(CASE WHEN e.es_baja = FALSE THEN 1 ELSE 0 END) AS bienes_activos,
                SUM(CASE WHEN e.es_baja = TRUE  THEN 1 ELSE 0 END) AS bienes_baja
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
        ");

        // Por estado
        $por_estado = Database::fetchAll("
            SELECT e.nombre, e.color, COUNT(*) AS cantidad,
                   SUM(b.valor_inicial) AS valor
            FROM bienes b
            JOIN estados e ON b.id_estado = e.id_estado
            GROUP BY e.id_estado, e.nombre, e.color
            ORDER BY e.id_estado
        ");

        // Por clasificación
        $por_tipo = Database::fetchAll("
            SELECT t.codigo, t.nombre, COUNT(*) AS cantidad,
                   SUM(b.valor_inicial) AS valor
            FROM bienes b
            JOIN tipos_bien t ON b.id_tipo = t.id_tipo
            GROUP BY t.id_tipo, t.codigo, t.nombre
            ORDER BY cantidad DESC
        ");

        // Por edificio
        $por_edificio = Database::fetchAll("
            SELECT COALESCE(a.edificio, 'Sin edificio') AS edificio,
                   COUNT(*) AS cantidad, SUM(b.valor_inicial) AS valor
            FROM bienes b
            LEFT JOIN areas a ON b.id_area = a.id_area
            GROUP BY a.edificio
            ORDER BY cantidad DESC
        ");

        // Movimientos del mes actual por tipo
        $mov_mes = Database::fetchAll("
            SELECT tipo_movimiento, COUNT(*) AS cantidad
            FROM movimientos
            WHERE YEAR(fecha_solicitud) = YEAR(NOW())
              AND MONTH(fecha_solicitud) = MONTH(NOW())
            GROUP BY tipo_movimiento
        ");

        // Top 10 bienes mayor valor
        $top_valor = Database::fetchAll("
            SELECT b.nombre, b.codigo_interno, b.valor_inicial,
                   t.nombre AS tipo_nombre, a.nombre_area
            FROM bienes b
            JOIN tipos_bien t ON b.id_tipo = t.id_tipo
            LEFT JOIN areas a ON b.id_area = a.id_area
            WHERE b.valor_inicial > 0
            ORDER BY b.valor_inicial DESC
            LIMIT 10
        ");

        // Top 10 áreas con más bienes
        $top_areas = Database::fetchAll("
            SELECT COALESCE(a.nombre_area, 'Sin área') AS nombre_area,
                   COALESCE(a.edificio, '—') AS edificio,
                   COUNT(*) AS cantidad
            FROM bienes b
            LEFT JOIN areas a ON b.id_area = a.id_area
            GROUP BY b.id_area, a.nombre_area, a.edificio
            ORDER BY cantidad DESC
            LIMIT 10
        ");

        $this->title = 'Reporte BM-4 - Resumen Ejecutivo';
        $this->renderWithLayout('reportes/bm4', compact(
            'resumen', 'por_estado', 'por_tipo', 'por_edificio',
            'mov_mes', 'top_valor', 'top_areas'
        ));
    }
}