<?php
/**
 * Vista: Dashboard principal
 * Métricas del inventario, bienes recientes, movimientos y distribución.
 *
 * @var array  $metrics           Métricas generales del inventario.
 * @var array  $recentBienes      Últimos 8 bienes registrados.
 * @var array  $recentMovimientos Últimos 6 movimientos.
 * @var array  $bienesPorEstado   Distribución por estado.
 * @var array  $bienesPorEdificio Distribución por edificio.
 * @var string $base_url          URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <h1>Dashboard</h1>
        <div class="page-actions">
            <span class="dash-datetime">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <?= date('d/m/Y H:i') ?>
                &nbsp;·&nbsp;
                <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
            </span>
        </div>
    </div>

    <!-- ── Métricas ──────────────────────────────────────────────── -->
<div class="metrics-grid stagger-parent" role="region" aria-label="Métricas del inventario" style="--delay:0.1s;">
        <div class="metric-card stagger-child" style="animation-delay:calc(var(--delay)*1);padding:1rem;">
            <div class="metric-icon blue pulse" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 7h-9"/><path d="M14 17H5"/>
                    <circle cx="17" cy="17" r="3"/><circle cx="7" cy="7" r="3"/>
                </svg>
            </div>
            <div>
                <span class="metric-value counter" data-target="<?= number_format($metrics['total']) ?>">0</span>
                <span class="metric-label">Total Bienes</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon green" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['operativos']) ?></span>
                <span class="metric-label">Operativos</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon yellow" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['inoperativos']) ?></span>
                <span class="metric-label">Inoperativos</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon teal" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['resguardo']) ?></span>
                <span class="metric-label">En Resguardo</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon red" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['chatarra']) ?></span>
                <span class="metric-label">Chatarra</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon gray" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['desincorp']) ?></span>
                <span class="metric-label">Desincorporados</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon purple" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div>
                <span class="metric-value">
                    <?= number_format($metrics['valor'], 0, ',', '.') ?>
                </span>
                <span class="metric-label">Valor Total (Bs.)</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon orange" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($metrics['movMes']) ?></span>
                <span class="metric-label">Movimientos (mes)</span>
            </div>
        </div>

    </div><!-- /.metrics-grid -->

    <!-- ── Grid de cards ─────────────────────────────────────────── -->
    <div class="dash-grid">

        <!-- Bienes recientes -->
        <div class="card">
            <div class="card-header">
                <h3>Últimos Bienes Registrados</h3>
                <a href="<?= $base_url ?>/bienes" class="btn btn-sm btn-secondary">Ver todos</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table" aria-label="Últimos bienes registrados">
                    <thead>
                        <tr>
                            <th scope="col">Cód. Interno</th>
                            <th scope="col">Nro. Bien</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Área</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentBienes)): ?>
                        <tr class="empty-row">
                            <td colspan="5">No hay bienes registrados aún</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentBienes as $b): ?>
                        <tr>
                            <td>
                                <span class="codigo-interno">
                                    <?= htmlspecialchars($b['codigo_interno'] ?? '') ?>
                                </span>
                            </td>
                            <td class="text-muted-sm">
                                <?php if ($b['es_sn']): ?>
                                    <em class="text-muted">S/N</em>
                                <?php else: ?>
                                    <?= htmlspecialchars($b['nro_bien_ministerio'] ?? '') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= $base_url ?>/bienes/<?= (int)$b['id_bien'] ?>">
                                    <?= htmlspecialchars($b['nombre']) ?>
                                </a>
                            </td>
                            <td class="text-sm"><?= htmlspecialchars($b['nombre_area'] ?? '—') ?></td>
                            <td>
                                <span class="badge"
                                      style="background:<?= htmlspecialchars($b['estado_color']) ?>">
                                    <?= htmlspecialchars($b['estado']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Distribución por estado -->
        <div class="card">
            <div class="card-header">
                <h3>Distribución por Estado</h3>
            </div>
            <div class="card-body">
                <?php foreach ($bienesPorEstado as $est): ?>
                <?php $pct = $metrics['total'] > 0
                    ? round($est['cantidad'] / $metrics['total'] * 100)
                    : 0; ?>
                <div class="dist-row">
                    <div class="dist-label">
                        <span class="dist-name"><?= htmlspecialchars($est['nombre']) ?></span>
                        <span class="dist-count"><?= number_format($est['cantidad']) ?></span>
                    </div>
                    <div class="progress-bar-wrap" role="progressbar"
                         aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"
                         aria-label="<?= htmlspecialchars($est['nombre']) ?>: <?= $pct ?>%">
                        <div class="progress-bar"
                             style="width:<?= $pct ?>%;background:<?= htmlspecialchars($est['color']) ?>">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Movimientos recientes -->
        <div class="card">
            <div class="card-header">
                <h3>Movimientos Recientes</h3>
                <a href="<?= $base_url ?>/movimientos" class="btn btn-sm btn-secondary">Ver todos</a>
            </div>
            <div class="table-wrapper">
                <table class="data-table" aria-label="Movimientos recientes">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col">Bien</th>
                            <th scope="col">Origen → Destino</th>
                            <th scope="col">Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentMovimientos)): ?>
                        <tr class="empty-row">
                            <td colspan="4">No hay movimientos registrados</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentMovimientos as $m):
                            $colorTipo = match($m['tipo_movimiento']) {
                                'incorporacion'    => '#38a169',
                                'traslado'         => '#3182ce',
                                'desincorporacion' => '#e53e3e',
                                default            => '#718096',
                            };
                        ?>
                        <tr>
                            <td>
                                <span class="badge badge-sm"
                                      style="background:<?= $colorTipo ?>">
                                    <?= ucfirst($m['tipo_movimiento']) ?>
                                </span>
                            </td>
                            <td class="text-sm"><?= htmlspecialchars($m['bien_nombre']) ?></td>
                            <td class="text-xs text-muted">
                                <?= htmlspecialchars($m['area_origen'] ?? '—') ?>
                                →
                                <?= htmlspecialchars($m['area_destino'] ?? '—') ?>
                            </td>
                            <td class="text-xs"><?= htmlspecialchars($m['usuario'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bienes por edificio -->
        <div class="card">
            <div class="card-header">
                <h3>Bienes por Edificio</h3>
            </div>
            <div class="card-body">
                <?php if (empty($bienesPorEdificio)): ?>
                    <p class="text-muted text-sm">Sin datos disponibles.</p>
                <?php else: ?>
                    <?php foreach ($bienesPorEdificio as $ed): ?>
                    <div class="edificio-row">
                        <span class="edificio-name"><?= htmlspecialchars($ed['edificio'] ?? '—') ?></span>
                        <span class="badge" style="background:var(--primary)">
                            <?= number_format($ed['cantidad']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="chart-grid">
            <div class="card">
                <div class="card-header">
                    <h3>Distribución por Estado</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Bienes por Edificio</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartEdificios"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.dash-grid -->

</main>

<script src="<?= $base_url ?>/js/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de estados
    const ctxEstados = document.getElementById('chartEstados').getContext('2d');
    new Chart(ctxEstados, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($bienesPorEstado, 'nombre')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($bienesPorEstado, 'cantidad')) ?>,
                backgroundColor: <?= json_encode(array_column($bienesPorEstado, 'color')) ?>,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
    
    // Gráfico de edificios
    const ctxEdificios = document.getElementById('chartEdificios').getContext('2d');
    new Chart(ctxEdificios, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($bienesPorEdificio, 'edificio')) ?>,
            datasets: [{
                label: 'Cantidad de Bienes',
                data: <?= json_encode(array_column($bienesPorEdificio, 'cantidad')) ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 11 } }
                },
                x: {
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});
</script>

<style>
/* Estilos específicos del dashboard */
.dash-datetime {
    display:inline-flex; align-items:center; gap:.4rem;
    font-size:.8rem; color:var(--text-muted);
}
.metric-card { padding: 1rem !important; }
.metric-icon { width: 44px !important; height: 44px !important; }
.metric-value { font-size: 1.6rem !important; margin-bottom: 0.25rem !important; }
.dist-row    { margin-bottom:.875rem; }
.dist-label  { display:flex; justify-content:space-between; margin-bottom:.3rem; }
.dist-name   { font-size:.85rem; font-weight:600; color:var(--gray-700); }
.dist-count  { font-size:.85rem; color:var(--gray-500); }
.edificio-row {
    display:flex; justify-content:space-between; align-items:center;
    padding:.5rem 0; border-bottom:1px solid var(--gray-100);
}
.edificio-row:last-child { border-bottom:none; }
.edificio-name { font-size:.875rem; font-weight:600; color:var(--gray-700); }
.text-sm   { font-size:.85rem; }
.text-xs   { font-size:.78rem; }
.text-muted{ color:var(--gray-500); }
.text-muted-sm { font-size:.8rem; color:var(--gray-400); }
</style>
