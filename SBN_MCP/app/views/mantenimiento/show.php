<?php
/**
 * =============================================================================
 * VISTA: DETALLE DE MANTENIMIENTO
 * =============================================================================
 * 
 * Muestra la información completa de un mantenimiento registrado.
 * Incluye datos del bien, tipo de servicio, fechas, proveedor y costo.
 * 
 * @var array  $mant         Datos completos del mantenimiento
 * @var string $base_url     URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <div>
            <h1>Mantenimiento #<?= $mant['id_mantenimiento'] ?></h1>
            <span class="badge" style="background:<?= $mant['tipo_servicio']==='correctivo'?'#e53e3e':($mant['tipo_servicio']==='preventivo'?'#38a169':'#3182ce') ?>;margin-top:.3rem;display:inline-block">
                <?= ucfirst($mant['tipo_servicio']) ?>
            </span>
        </div>
        <div class="page-actions">
            <a href="<?= $base_url ?>/mantenimientos/<?= $mant['id_mantenimiento'] ?>/editar" class="btn btn-warning btn-sm">Editar</a>
            <a href="<?= $base_url ?>/mantenimientos" class="btn btn-secondary btn-sm">← Volver</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem">
        <div>
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Bien Asociado</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Bien</span>
                            <span class="detail-value">
                                <a href="<?= $base_url ?>/bienes/<?= $mant['bien_id'] ?>">
                                    <?= htmlspecialchars($mant['bien_nombre'] ?? '—') ?>
                                </a>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Código Interno</span>
                            <span class="detail-value codigo-interno"><?= htmlspecialchars($mant['codigo_interno'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Diagnóstico y Trabajo Realizado</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Diagnóstico</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars($mant['diagnostico'] ?? '—')) ?></span>
                        </div>
                        <div class="detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Trabajo Realizado</span>
                            <span class="detail-value"><?= nl2br(htmlspecialchars($mant['trabajo_realizado'] ?? '—')) ?></span>
                        </div>
                        <?php if (!empty($mant['observaciones'])): ?>
                        <div class="detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Observaciones</span>
                            <span class="detail-value" style="font-size:.85rem"><?= nl2br(htmlspecialchars($mant['observaciones'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Fechas</h3></div>
                <div class="card-body">
                    <div class="detail-grid" style="grid-template-columns:1fr">
                        <div class="detail-item">
                            <span class="detail-label">Fecha Programada</span>
                            <span class="detail-value"><?= $mant['fecha_programada'] ? date('d/m/Y', strtotime($mant['fecha_programada'])) : '—' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha Ejecutada</span>
                            <span class="detail-value"><?= $mant['fecha_ejecutada'] ? date('d/m/Y', strtotime($mant['fecha_ejecutada'])) : '<em style="color:#d69e2e">Pendiente</em>' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Próximo Mantenimiento</span>
                            <span class="detail-value"><?= $mant['proxima_fecha_programada'] ? date('d/m/Y', strtotime($mant['proxima_fecha_programada'])) : '—' ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Garantía</span>
                            <span class="detail-value"><?= $mant['garantia_meses'] ? $mant['garantia_meses'] . ' meses' : '—' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Proveedor y Costo</h3></div>
                <div class="card-body">
                    <div class="detail-grid" style="grid-template-columns:1fr">
                        <div class="detail-item">
                            <span class="detail-label">Proveedor</span>
                            <span class="detail-value"><?= htmlspecialchars($mant['proveedor'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Técnico</span>
                            <span class="detail-value"><?= htmlspecialchars($mant['tecnico'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Costo</span>
                            <span class="detail-value" style="font-weight:700;color:#1e3a5f">
                                <?= $mant['costo'] ? 'Bs. ' . number_format($mant['costo'], 2) : '—' ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registrado por</span>
                            <span class="detail-value"><?= htmlspecialchars($mant['realizado_por_nombre'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
