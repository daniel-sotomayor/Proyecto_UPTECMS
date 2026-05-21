<?php
/**
 * Vista: Reporte BM-3 — Movimientos del Período
 *
 * @var array  $movimientos  Lista de movimientos del período.
 * @var string $fecha_desde  Fecha inicio del filtro (Y-m-d).
 * @var string $fecha_hasta  Fecha fin del filtro (Y-m-d).
 * @var string $tipo_filtro  Tipo de movimiento filtrado.
 * @var array  $totales_tipo Conteo por tipo de movimiento.
 * @var string $base_url     URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$colorTipo = fn(string $t): string => match ($t) {
    'incorporacion'    => '#38a169',
    'traslado'         => '#3182ce',
    'desincorporacion' => '#e53e3e',
    default            => '#718096',
};
$colorEstado = fn(string $e): string => match ($e) {
    'aprobado'  => '#38a169',
    'rechazado' => '#e53e3e',
    default     => '#d69e2e',
};
$labelTipo = ['incorporacion' => 'Incorporaciones', 'traslado' => 'Traslados', 'desincorporacion' => 'Desincorporaciones'];
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1>Reporte BM-3 — Movimientos del Período</h1>
            <p class="page-subtitle">Generado: <?= date('d/m/Y H:i') ?></p>
        </div>
        <div class="page-actions">
            <a href="<?= $base_url ?>/reportes" class="btn btn-secondary btn-sm">← Reportes</a>
        </div>
    </div>

    <!-- Filtros de período -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-body">
            <form method="GET" role="search" aria-label="Filtros de período">
                <div class="filters-bar" style="padding:0;box-shadow:none;background:none">
                    <div class="filter-group">
                        <label for="f-desde">Desde</label>
                        <input type="date" name="fecha_desde" id="f-desde"
                               class="form-input" value="<?= htmlspecialchars($fecha_desde) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="f-hasta">Hasta</label>
                        <input type="date" name="fecha_hasta" id="f-hasta"
                               class="form-input" value="<?= htmlspecialchars($fecha_hasta) ?>">
                    </div>
                    <div class="filter-group">
                        <label for="f-tipo">Tipo</label>
                        <select name="tipo" id="f-tipo" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach (['incorporacion', 'traslado', 'desincorporacion'] as $t): ?>
                            <option value="<?= $t ?>" <?= $tipo_filtro === $t ? 'selected' : '' ?>>
                                <?= $labelTipo[$t] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group" style="min-width:auto">
                        <label>&nbsp;</label>
                        <div style="display:flex;gap:.5rem">
                            <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                            <a href="<?= $base_url ?>/reportes/bm3" class="btn btn-secondary btn-sm">Limpiar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Totales por tipo -->
    <?php if (!empty($totales_tipo)): ?>
    <div class="metrics-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:1.5rem">
        <?php foreach ($totales_tipo as $tipo => $cant): ?>
        <div class="metric-card">
            <div class="metric-icon" style="background:<?= $colorTipo($tipo) ?>" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format($cant) ?></span>
                <span class="metric-label"><?= $labelTipo[$tipo] ?? ucfirst($tipo) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="metric-card">
            <div class="metric-icon blue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18"/><path d="M9 21V9"/>
                </svg>
            </div>
            <div>
                <span class="metric-value"><?= number_format(array_sum($totales_tipo)) ?></span>
                <span class="metric-label">Total del Período</span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($movimientos)): ?>
    <div class="card">
        <div class="card-body" style="text-align:center;padding:3rem">
            <p style="color:var(--gray-500)">
                No se registraron movimientos entre
                <?= date('d/m/Y', strtotime($fecha_desde)) ?> y
                <?= date('d/m/Y', strtotime($fecha_hasta)) ?>.
            </p>
        </div>
    </div>
    <?php else: ?>

    <div style="margin-bottom:1rem">
        <input type="search" id="bm3Search" class="form-input"
               placeholder="Filtrar por bien, código, área o usuario…"
               style="max-width:420px"
               aria-label="Filtrar tabla de movimientos">
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="data-table" id="bm3Table" aria-label="Movimientos del período">
                <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Bien</th>
                        <th scope="col">Código</th>
                        <th scope="col">Área Origen</th>
                        <th scope="col">Área Destino</th>
                        <th scope="col">Solicitante</th>
                        <th scope="col">Aprobador</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td style="font-size:.8rem;white-space:nowrap">
                        <?= date('d/m/Y H:i', strtotime($m['fecha_solicitud'])) ?>
                    </td>
                    <td>
                        <span class="badge badge-sm" style="background:<?= $colorTipo($m['tipo_movimiento']) ?>">
                            <?= ucfirst($m['tipo_movimiento']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-sm" style="background:<?= $colorEstado($m['estado']) ?>">
                            <?= ucfirst($m['estado']) ?>
                        </span>
                    </td>
                    <td style="font-size:.875rem;font-weight:500">
                        <a href="<?= $base_url ?>/bienes/<?= (int)$m['bien_id'] ?>">
                            <?= htmlspecialchars($m['bien_nombre']) ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?= $base_url ?>/movimientos/<?= (int)$m['id_movimiento'] ?>"
                           class="codigo-interno" style="font-size:.78rem">
                            <?= htmlspecialchars($m['codigo_interno'] ?? '—') ?>
                        </a>
                    </td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['area_origen'] ?? '—') ?></td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['area_destino'] ?? '—') ?></td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['usuario_solicita'] ?? '—') ?></td>
                    <td style="font-size:.82rem"><?= htmlspecialchars($m['usuario_aprueba'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background:var(--gray-50);font-weight:700">
                        <td colspan="9" style="padding:.75rem 1rem;font-size:.85rem">
                            Total: <?= number_format(count($movimientos)) ?> movimiento(s) en el período
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <?php endif; ?>

</main>

<style>
.page-subtitle { font-size:.85rem; color:var(--gray-500); margin-top:.2rem; }
</style>

<script>
document.getElementById('bm3Search')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#bm3Table tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
