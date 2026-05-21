<?php
/**
 * Vista: Historial de Verificaciones Físicas (semestrales) para un bien
 * @var array $bien
 * @var array $verificaciones
 */
require APP_PATH . '/views/partials/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1>Verificaciones — <?= htmlspecialchars($bien['nombre'] ?? 'Bien') ?></h1>
        <div class="page-actions">
            <a href="<?= $base_url ?>/bienes/<?= $bien['id_bien'] ?>" class="btn btn-secondary btn-sm">← Volver</a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Fecha</th><th>Usuario</th><th>Tipo</th><th>Observaciones</th>
                </tr></thead>
                <tbody>
                <?php if (empty($verificaciones)): ?>
                    <tr class="empty-row"><td colspan="4">Sin verificaciones registradas</td></tr>
                <?php else: foreach ($verificaciones as $v): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($v['fecha_verificacion'])) ?></td>
                        <td><?= htmlspecialchars($v['usuario_nombre'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($v['tipo'] ?? '') ?></td>
                        <td style="font-size:.9rem"><?= nl2br(htmlspecialchars($v['observaciones'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
