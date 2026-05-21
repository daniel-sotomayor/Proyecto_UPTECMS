<?php
/**
 * Vista: Reporte BM-4 — Resumen Ejecutivo del Inventario
 *
 * @var array  $resumen      Métricas generales del inventario.
 * @var array  $por_estado   Distribución por estado.
 * @var array  $por_tipo     Distribución por clasificación.
 * @var array  $por_edificio Distribución por edificio.
 * @var array  $mov_mes      Movimientos del mes actual por tipo.
 * @var array  $top_valor    Top 10 bienes de mayor valor.
 * @var array  $top_areas    Top 10 áreas con más bienes.
 * @var string $base_url     URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$colorTipo  = ['incorporacion' => '#38a169', 'traslado' => '#3182ce', 'desincorporacion' => '#e53e3e'];
$labelTipo  = ['incorporacion' => 'Incorporaciones', 'traslado' => 'Traslados', 'desincorporacion' => 'Desincorporaciones'];
$totalBienes = (int)($resumen['total_bienes'] ?? 1);
$totalMov    = array_sum(array_column($mov_mes, 'cantidad'));
?>

<main class="main-content" id="mainContent">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Reporte BM-4 — Resumen Ejecutivo</h1>
            <p class="text-muted small">
                Generado: <?= date('d/m/Y H:i') ?> &nbsp;·&nbsp; Movimientos del mes: <?= date('F Y') ?>
            </p>
        </div>
        <div class="page-actions">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm me-2">Imprimir</button>
            <a href="<?= $base_url ?>/reportes" class="btn btn-executive btn-sm">Volver a Reportes</a>
        </div>
    </div>

    <!-- Métricas generales -->
    <div class="metrics-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.5rem">
        <div class="dashboard-card mb-3">
            <div class="metric-icon blue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7h-9"/><path d="M14 17H5"/>
                    <circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($resumen['total_bienes'] ?? 0) ?></span>
                <span class="metric-label">Total Bienes</span>
            </div>
        </div>
        <div class="dashboard-card mb-3" style="border-left-color: var(--success-color);">
            <div class="metric-icon green" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($resumen['bienes_activos'] ?? 0) ?></span>
                <span class="metric-label">Bienes Activos</span>
            </div>
        </div>
        <div class="dashboard-card mb-3" style="border-left-color: var(--danger-color);">
            <div class="metric-icon red" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($resumen['bienes_baja'] ?? 0) ?></span>
                <span class="metric-label">Dados de Baja</span>
            </div>
        </div>
        <div class="dashboard-card mb-3" style="border-left-color: var(--accent-color);">
            <div class="metric-icon purple" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <span class="metric-value">Bs. <?= number_format((float)($resumen['valor_total'] ?? 0), 0, ',', '.') ?></span>
                <span class="metric-label">Valor Total</span>
            </div>
        </div>
    </div>

    <!-- Grid: Estado + Movimientos del mes -->
    <div class="dash-grid" style="margin-bottom:1.5rem">

        <!-- Por estado -->
        <div class="card">
            <div class="card-header"><h3>Distribución por Estado</h3></div>
            <div class="table-wrapper">
                <table class="data-table" aria-label="Distribución por estado">
                    <thead>
                        <tr>
                            <th scope="col">Estado</th>
                            <th scope="col" style="text-align:right">Cantidad</th>
                            <th scope="col" style="text-align:right">Valor (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($por_estado as $e): ?>
                    <tr>
                        <td>
                            <span class="badge" style="background:<?= htmlspecialchars($e['color']) ?>">
                                <?= htmlspecialchars($e['nombre']) ?>
                            </span>
                        </td>
                        <td style="text-align:right;font-weight:600"><?= number_format($e['cantidad']) ?></td>
                        <td style="text-align:right;font-size:.85rem;font-variant-numeric:tabular-nums">
                            <?= $e['valor'] > 0 ? number_format((float)$e['valor'], 2, ',', '.') : '—' ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Movimientos del mes -->
        <div class="card">
            <div class="card-header">
                <h3>Movimientos — <?= date('F Y') ?></h3>
            </div>
            <div class="card-body">
                <?php if (empty($mov_mes)): ?>
                <p style="color:var(--gray-500);text-align:center;padding:1rem">Sin movimientos este mes.</p>
                <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:.875rem">
                    <?php foreach ($mov_mes as $m):
                        $pct = $totalMov > 0 ? round($m['cantidad'] / $totalMov * 100) : 0;
                        $col = $colorTipo[$m['tipo_movimiento']] ?? '#718096';
                        $lbl = $labelTipo[$m['tipo_movimiento']] ?? ucfirst($m['tipo_movimiento']);
                    ?>
                    <div>
                        <div class="dist-label">
                            <span class="dist-name" style="color:<?= $col ?>"><?= $lbl ?></span>
                            <span class="dist-count"><?= $m['cantidad'] ?> (<?= $pct ?>%)</span>
                        </div>
                        <div class="progress-bar-wrap" role="progressbar"
                             aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $col ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <p style="text-align:right;font-size:.82rem;color:var(--gray-500);margin-top:.25rem">
                        Total: <strong><?= $totalMov ?></strong> movimientos
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Por clasificación -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><h3>Bienes por Clasificación (Publicación 9)</h3></div>
        <div class="table-wrapper">
            <table class="data-table" aria-label="Bienes por clasificación">
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Clasificación</th>
                        <th scope="col" style="text-align:right">Cantidad</th>
                        <th scope="col" style="text-align:right">Valor Total (Bs.)</th>
                        <th scope="col" style="text-align:right">% Inventario</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($por_tipo as $t):
                    $pct = $totalBienes > 0 ? round($t['cantidad'] / $totalBienes * 100, 1) : 0;
                ?>
                <tr>
                    <td>
                        <code style="font-size:.78rem;background:var(--gray-100);padding:.1rem .35rem;border-radius:.25rem">
                            <?= htmlspecialchars($t['codigo']) ?>
                        </code>
                    </td>
                    <td style="font-size:.875rem"><?= htmlspecialchars($t['nombre']) ?></td>
                    <td style="text-align:right;font-weight:600"><?= number_format($t['cantidad']) ?></td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;font-size:.85rem">
                        <?= $t['valor'] > 0 ? number_format((float)$t['valor'], 2, ',', '.') : '—' ?>
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:.5rem">
                            <div class="progress-bar-wrap" style="width:60px">
                                <div class="progress-bar" style="width:<?= min($pct, 100) ?>%;background:var(--primary)"></div>
                            </div>
                            <span style="font-size:.8rem;min-width:36px"><?= $pct ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);font-weight:700">
                        <td colspan="2" style="padding:.75rem 1rem">Total</td>
                        <td style="text-align:right;padding:.75rem 1rem"><?= number_format($resumen['total_bienes'] ?? 0) ?></td>
                        <td style="text-align:right;padding:.75rem 1rem;font-variant-numeric:tabular-nums">
                            <?= number_format((float)($resumen['valor_total'] ?? 0), 2, ',', '.') ?>
                        </td>
                        <td style="text-align:right;padding:.75rem 1rem">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Por edificio -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><h3>Bienes por Edificio</h3></div>
        <div class="table-wrapper">
            <table class="data-table" aria-label="Bienes por edificio">
                <thead>
                    <tr>
                        <th scope="col">Edificio</th>
                        <th scope="col" style="text-align:right">Cantidad</th>
                        <th scope="col" style="text-align:right">Valor Total (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($por_edificio as $e): ?>
                <tr>
                    <td style="font-weight:500"><?= htmlspecialchars($e['edificio']) ?></td>
                    <td style="text-align:right;font-weight:600"><?= number_format($e['cantidad']) ?></td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;font-size:.85rem">
                        <?= $e['valor'] > 0 ? number_format((float)$e['valor'], 2, ',', '.') : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top 10 -->
    <div class="dash-grid">

        <div class="card">
            <div class="card-header"><h3>Top 10 — Mayor Valor</h3></div>
            <div class="table-wrapper">
                <table class="data-table" aria-label="Top 10 bienes de mayor valor">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Bien</th>
                            <th scope="col">Área</th>
                            <th scope="col" style="text-align:right">Valor (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($top_valor)): ?>
                        <tr class="empty-row"><td colspan="4">Sin datos de valor</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_valor as $i => $b): ?>
                        <tr>
                            <td style="color:var(--gray-400);font-size:.78rem"><?= $i + 1 ?></td>
                            <td>
                                <span style="font-size:.85rem;font-weight:600;display:block">
                                    <?= htmlspecialchars($b['nombre']) ?>
                                </span>
                                <span class="codigo-interno" style="font-size:.75rem">
                                    <?= htmlspecialchars($b['codigo_interno'] ?? '') ?>
                                </span>
                            </td>
                            <td style="font-size:.8rem;color:var(--gray-500)">
                                <?= htmlspecialchars($b['nombre_area'] ?? '—') ?>
                            </td>
                            <td style="text-align:right;font-weight:700;font-variant-numeric:tabular-nums;font-size:.85rem">
                                <?= number_format((float)$b['valor_inicial'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Top 10 — Áreas con Más Bienes</h3></div>
            <div class="table-wrapper">
                <table class="data-table" aria-label="Top 10 áreas con más bienes">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Área</th>
                            <th scope="col">Edificio</th>
                            <th scope="col" style="text-align:right">Bienes</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($top_areas)): ?>
                        <tr class="empty-row"><td colspan="4">Sin datos</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_areas as $i => $a): ?>
                        <tr>
                            <td style="color:var(--gray-400);font-size:.78rem"><?= $i + 1 ?></td>
                            <td style="font-size:.875rem;font-weight:500"><?= htmlspecialchars($a['nombre_area']) ?></td>
                            <td style="font-size:.82rem;color:var(--gray-500)"><?= htmlspecialchars($a['edificio']) ?></td>
                            <td style="text-align:right;font-weight:700"><?= number_format($a['cantidad']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</main>

<style>
.page-subtitle { font-size:.85rem; color:var(--gray-500); margin-top:.2rem; }
.dist-label { display:flex; justify-content:space-between; margin-bottom:.3rem; }
.dist-name  { font-size:.85rem; font-weight:600; }
.dist-count { font-size:.85rem; color:var(--gray-500); }
</style>
