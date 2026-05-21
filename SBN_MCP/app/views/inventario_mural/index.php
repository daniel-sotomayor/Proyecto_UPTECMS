<?php
require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <h1>Control Mural — Inventario por Departamento</h1>
        <div class="page-actions">
            <a href="<?= $base_url ?>/bienes" class="btn btn-secondary btn-sm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Inventario Global
            </a>
        </div>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-body">
            <div style="display:grid; gap:1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div class="summary-card">
                    <h4>Total General</h4>
                    <p><strong><?= number_format($global['total_global'] ?? 0) ?></strong> bienes</p>
                </div>
                <div class="summary-card">
                    <h4>Valor Global</h4>
                    <p><strong>Bs. <?= number_format($global['valor_global'] ?? 0, 2, ',', '.') ?></strong></p>
                </div>
                <div class="summary-card">
                    <h4>Sin área asignada</h4>
                    <p><strong><?= number_format($sin_area['total'] ?? 0) ?></strong> bienes</p>
                    <p><strong>Bs. <?= number_format($sin_area['valor'] ?? 0, 2, ',', '.') ?></strong></p>
                </div>
            </div>
            <p style="margin-top:1rem; color:#475569; font-size:.95rem;">El control mural presenta el inventario por área y debe coincidir con los bienes registrados en el inventario global.</p>
        </div>
    </div>

    <?php if (empty($por_edificio)): ?>
        <div class="card">
            <div class="card-body">
                <p>No hay áreas registradas para mostrar el control mural.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($por_edificio as $edificio => $areas): ?>
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card-header">
                    <h2 style="font-size:1rem; margin:0;">Edificio: <?= htmlspecialchars($edificio) ?></h2>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Área</th>
                                <th>Ubicación</th>
                                <th>Bienes</th>
                                <th>Valor Total</th>
                                <th>Operativo</th>
                                <th>Inoperativo</th>
                                <th>Resguardo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($areas as $area): ?>
                            <tr>
                                <td><?= htmlspecialchars($area['nombre_area']) ?></td>
                                <td>
                                    <?= htmlspecialchars($area['edificio']) ?>
                                    <?= $area['piso'] == -1 ? 'Sótano' : ($area['piso'] == 0 ? 'P.Baja' : 'Piso ' . htmlspecialchars($area['piso'])) ?>
                                </td>
                                <td style="text-align:center;"><?= (int)$area['total_bienes'] ?></td>
                                <td style="text-align:right;">Bs. <?= number_format($area['valor_total'] ?? 0, 2, ',', '.') ?></td>
                                <td style="text-align:center;"><?= (int)$area['operativos'] ?></td>
                                <td style="text-align:center;"><?= (int)$area['inoperativos'] ?></td>
                                <td style="text-align:center;"><?= (int)$area['resguardo'] ?></td>
                                <td>
                                    <a href="<?= $base_url ?>/inventario-mural/<?= $area['id_area'] ?>" class="btn btn-sm btn-secondary">Ver área</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
