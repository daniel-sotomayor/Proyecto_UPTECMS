<?php
require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1>Control Mural — <?= htmlspecialchars($area['nombre_area']) ?></h1>
            <p style="color:#475569; margin-top:.4rem;">Edificio <?= htmlspecialchars($area['edificio']) ?> · <?= $area['piso'] == -1 ? 'Sótano' : ($area['piso'] == 0 ? 'P.Baja' : 'Piso ' . htmlspecialchars($area['piso'])) ?></p>
        </div>
        <div class="page-actions">
            <a href="<?= $base_url ?>/inventario-mural" class="btn btn-secondary btn-sm">← Volver al Control Mural</a>
            <a href="<?= $base_url ?>/inventario-mural/<?= $area['id_area'] ?>?export=csv" class="btn btn-secondary btn-sm">
                Exportar CSV
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-body" style="display:grid; gap:1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div class="summary-card">
                <h4>Total Bienes</h4>
                <p><strong><?= number_format($totales['total'] ?? 0) ?></strong></p>
            </div>
            <div class="summary-card">
                <h4>Valor Total</h4>
                <p><strong>Bs. <?= number_format($totales['valor_total'] ?? 0, 2, ',', '.') ?></strong></p>
            </div>
            <div class="summary-card">
                <h4>Operativos</h4>
                <p><strong><?= number_format($totales['operativos'] ?? 0) ?></strong></p>
            </div>
            <div class="summary-card">
                <h4>Inoperativos</h4>
                <p><strong><?= number_format($totales['inoperativos'] ?? 0) ?></strong></p>
            </div>
            <div class="summary-card">
                <h4>En Resguardo</h4>
                <p><strong><?= number_format($totales['resguardo'] ?? 0) ?></strong></p>
            </div>
        </div>
    </div>

    <?php if (empty($bienes)): ?>
    <div class="card">
        <div class="card-body">
            <p>No se encontraron bienes para esta área.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Clasif.</th>
                        <th>Cod. Interno</th>
                        <th>Nro. Bien</th>
                        <th>Nombre</th>
                        <th>Serial</th>
                        <th>Oficina</th>
                        <th>Posición</th>
                        <th>Valor</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bienes as $b): ?>
                    <tr>
                        <td><span class="badge badge-sm" style="background:#1e3a5f"><?= htmlspecialchars($b['tipo_codigo']) ?></span></td>
                        <td><?= htmlspecialchars($b['codigo_interno']) ?></td>
                        <td><?= $b['es_sn'] ? 'S/N' : htmlspecialchars($b['nro_bien_ministerio'] ?? '—') ?></td>
                        <td>
                            <div style="font-weight:600;"><?= htmlspecialchars($b['nombre']) ?></div>
                            <?php if (!empty($b['descripcion'])): ?>
                            <div style="font-size:.8rem;color:#64748b; margin-top:.2rem;">
                                <?= htmlspecialchars(mb_substr($b['descripcion'], 0, 80)) ?><?= mb_strlen($b['descripcion']) > 80 ? '…' : '' ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($b['serial'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($b['cin_oficina'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($b['cin_posicion'] ?? '—') ?></td>
                        <td style="text-align:right;">Bs. <?= number_format($b['valor_inicial'] ?? 0, 2, ',', '.') ?></td>
                        <td><span class="badge" style="background:<?= htmlspecialchars($b['estado_color']) ?>"><?= htmlspecialchars($b['estado_nombre']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</main>
