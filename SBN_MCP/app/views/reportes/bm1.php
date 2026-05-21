<?php
/**
 * Vista: Reporte BM-1 — Inventario de Bienes Activos
 *
 * @var array  $bienes      Lista de bienes activos.
 * @var int    $total       Total de bienes.
 * @var float  $valor_total Valor total del inventario activo.
 * @var string $base_url    URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$operativos   = count(array_filter($bienes, fn($b) => $b['estado_nombre'] === 'Operativo'));
$inoperativos = count(array_filter($bienes, fn($b) => $b['estado_nombre'] === 'Inoperativo'));
$resguardo    = count(array_filter($bienes, fn($b) => $b['estado_nombre'] === 'En Resguardo'));
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1>Reporte BM-1 — Inventario de Bienes Activos</h1>
            <p class="page-subtitle">
                Bienes en estado Operativo, Inoperativo y En Resguardo &nbsp;·&nbsp;
                Generado: <?= date('d/m/Y H:i') ?>
            </p>
        </div>
        <div class="page-actions">
            <a href="<?= $base_url ?>/reportes/bm1?export=pdf" class="btn btn-danger btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                PDF
            </a>
            <a href="<?= $base_url ?>/reportes/bm1?export=excel" class="btn btn-success btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                Excel
            </a>
            <a href="<?= $base_url ?>/reportes/bm1?export=csv" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                CSV
            </a>
            <button onclick="window.print()" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Imprimir
            </button>
            <a href="<?= $base_url ?>/reportes" class="btn btn-secondary btn-sm">← Reportes</a>
        </div>
    </div>

    <!-- Métricas -->
    <div class="metrics-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.5rem">
        <div class="metric-card">
            <div class="metric-icon blue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($total) ?></span>
                <span class="metric-label">Total Activos</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon green" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($operativos) ?></span>
                <span class="metric-label">Operativos</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon orange" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($inoperativos) ?></span>
                <span class="metric-label">Inoperativos</span>
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
                <span class="metric-value">Bs. <?= number_format($valor_total, 0, ',', '.') ?></span>
                <span class="metric-label">Valor Total</span>
            </div>
        </div>
    </div>

    <?php if (empty($bienes)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:3rem">
            <p style="color:var(--gray-500)">No hay bienes activos registrados.</p>
        </div>
    </div>
    <?php else: ?>

    <div style="margin-bottom:1rem">
        <input type="search" id="bm1Search" class="form-input"
               placeholder="Filtrar por nombre, código, estado o área…"
               style="max-width:420px"
               aria-label="Filtrar tabla de bienes activos">
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="data-table" id="bm1Table" aria-label="Inventario de bienes activos">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Código Interno</th>
                        <th scope="col">Nro. Bien</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Clasificación</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Área / Edificio</th>
                        <th scope="col">Última Actualización</th>
                        <th scope="col" style="text-align:right">Valor (Bs.)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bienes as $i => $b): ?>
                <tr>
                    <td style="color:var(--gray-400);font-size:.78rem"><?= $i + 1 ?></td>
                    <td>
                        <a href="<?= $base_url ?>/bienes/<?= (int)$b['id_bien'] ?>" class="codigo-interno">
                            <?= htmlspecialchars($b['codigo_interno'] ?? '—') ?>
                        </a>
                    </td>
                    <td style="font-size:.85rem">
                        <?= $b['es_sn']
                            ? '<em style="color:var(--gray-400)">S/N</em>'
                            : htmlspecialchars($b['nro_bien_ministerio'] ?? '—') ?>
                    </td>
                    <td style="font-weight:500"><?= htmlspecialchars($b['nombre']) ?></td>
                    <td style="font-size:.82rem">
                        <span title="<?= htmlspecialchars($b['tipo_nombre']) ?>">
                            <?= htmlspecialchars($b['tipo_codigo']) ?>
                        </span>
                        <span style="display:block;color:var(--gray-400);font-size:.75rem">
                            <?= htmlspecialchars($b['tipo_nombre']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:<?= htmlspecialchars($b['estado_color']) ?>">
                            <?= htmlspecialchars($b['estado_nombre']) ?>
                        </span>
                    </td>
                    <td style="font-size:.85rem">
                        <?= htmlspecialchars($b['nombre_area'] ?? '—') ?>
                        <?php if (!empty($b['edificio'])): ?>
                        <span style="display:block;color:var(--gray-400);font-size:.75rem">
                            <?= htmlspecialchars($b['edificio']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.8rem;color:var(--gray-500)">
                        <?= !empty($b['updated_at']) ? date('d/m/Y', strtotime($b['updated_at'])) : '—' ?>
                    </td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;font-size:.85rem">
                        <?= $b['valor_inicial'] > 0
                            ? number_format((float)$b['valor_inicial'], 2, ',', '.')
                            : '<span style="color:var(--gray-300)">—</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);font-weight:700">
                        <td colspan="8" style="text-align:right;padding:.75rem 1rem;font-size:.85rem">
                            Total (<?= number_format($total) ?> bienes):
                        </td>
                        <td style="text-align:right;padding:.75rem 1rem;font-variant-numeric:tabular-nums">
                            <?= number_format($valor_total, 2, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php endif; ?>

    <div class="alert alert-info" role="alert" style="margin-top:1.5rem">
        <strong>Nota de Confidencialidad:</strong> Este reporte es emitido para la Dirección de la Maternidad. Su difusión no autorizada está prohibida.
    </div>

</main>

<style>
.page-subtitle { font-size:.85rem; color:var(--gray-500); margin-top:.2rem; }
</style>

<script>
document.getElementById('bm1Search')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#bm1Table tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
