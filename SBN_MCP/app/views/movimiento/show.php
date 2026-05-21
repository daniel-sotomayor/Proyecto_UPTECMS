<?php
/**
 * Vista: Detalle de Acta / Movimiento
 *
 * @var array  $movimiento Datos completos del movimiento.
 * @var string $base_url   URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$rol          = $_SESSION['rol'] ?? '';
$puedeAprobar = in_array($rol, ['administrador', 'gerencia_bn'], true);
$esPendiente  = $movimiento['estado'] === 'pendiente';

$colorTipo = match ($movimiento['tipo_movimiento']) {
    'incorporacion'    => '#38a169',
    'traslado'         => '#3182ce',
    'desincorporacion' => '#e53e3e',
    default            => '#718096',
};
$colorEstado = match ($movimiento['estado']) {
    'aprobado'  => '#38a169',
    'rechazado' => '#e53e3e',
    default     => '#d69e2e',
};
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <div>
            <h1>Acta de <?= ucfirst(htmlspecialchars($movimiento['tipo_movimiento'])) ?></h1>
            <div style="display:flex;gap:.5rem;align-items:center;margin-top:.35rem">
                <span class="badge" style="background:<?= $colorTipo ?>">
                    <?= ucfirst(htmlspecialchars($movimiento['tipo_movimiento'])) ?>
                </span>
                <span class="badge" style="background:<?= $colorEstado ?>">
                    <?= ucfirst(htmlspecialchars($movimiento['estado'])) ?>
                </span>
                <span style="font-size:.82rem;color:var(--gray-400)">#<?= (int)$movimiento['id_movimiento'] ?></span>
            </div>
        </div>
        <div class="page-actions">
            <?php if ($puedeAprobar && $esPendiente): ?>
            <button id="btnAprobar" class="btn btn-success btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Aprobar
            </button>
            <button id="btnRechazar" class="btn btn-danger btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Rechazar
            </button>
            <?php endif; ?>
            <a href="<?= $base_url ?>/movimientos" class="btn btn-secondary btn-sm">← Volver</a>
        </div>
    </div>

    <div class="detail-layout">

        <!-- Columna principal -->
        <div>

            <!-- Bien asociado -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Bien Asociado</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre</span>
                            <span class="detail-value">
                                <a href="<?= $base_url ?>/bienes/<?= (int)$movimiento['bien_id'] ?>">
                                    <?= htmlspecialchars($movimiento['bien_nombre']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Código Interno</span>
                            <span class="detail-value codigo-interno">
                                <?= htmlspecialchars($movimiento['codigo_interno'] ?? '—') ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nro. Ministerio</span>
                            <span class="detail-value">
                                <?= $movimiento['es_sn']
                                    ? '<em style="color:var(--gray-400)">S/N</em>'
                                    : htmlspecialchars($movimiento['nro_bien_ministerio'] ?? '—') ?>
                            </span>
                        </div>
                        <?php if (!empty($movimiento['marca']) || !empty($movimiento['modelo'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Marca / Modelo</span>
                            <span class="detail-value">
                                <?= htmlspecialchars(trim(($movimiento['marca'] ?? '') . ' ' . ($movimiento['modelo'] ?? ''))) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($movimiento['bien_descripcion'])): ?>
                        <div class="detail-item" style="grid-column:1/-1">
                            <span class="detail-label">Descripción</span>
                            <span class="detail-value" style="font-size:.85rem">
                                <?= nl2br(htmlspecialchars($movimiento['bien_descripcion'])) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Origen y destino -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Origen y Destino</h3></div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Área Origen</span>
                            <span class="detail-value">
                                <?php if ($movimiento['area_origen']): ?>
                                    <?= htmlspecialchars($movimiento['area_origen']) ?>
                                    <?php if ($movimiento['edif_origen']): ?>
                                    <span style="color:var(--gray-400);font-size:.82rem">
                                        — <?= htmlspecialchars($movimiento['edif_origen']) ?>
                                    </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <em style="color:var(--gray-400)">—</em>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Área Destino</span>
                            <span class="detail-value">
                                <?php if ($movimiento['area_destino']): ?>
                                    <?= htmlspecialchars($movimiento['area_destino']) ?>
                                    <?php if ($movimiento['edif_destino']): ?>
                                    <span style="color:var(--gray-400);font-size:.82rem">
                                        — <?= htmlspecialchars($movimiento['edif_destino']) ?>
                                    </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <em style="color:var(--gray-400)">—</em>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motivo -->
            <div class="card">
                <div class="card-header"><h3>Motivo</h3></div>
                <div class="card-body">
                    <p style="font-size:.9rem;line-height:1.6;color:var(--gray-700)">
                        <?= nl2br(htmlspecialchars($movimiento['motivo'] ?? '—')) ?>
                    </p>
                </div>
            </div>

        </div>

        <!-- Columna lateral -->
        <div>

            <!-- Trazabilidad -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header"><h3>Trazabilidad</h3></div>
                <div class="card-body">
                    <div class="detail-grid" style="grid-template-columns:1fr">
                        <div class="detail-item">
                            <span class="detail-label">Solicitado por</span>
                            <span class="detail-value"><?= htmlspecialchars($movimiento['usuario_solicita'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha Solicitud</span>
                            <span class="detail-value">
                                <?= !empty($movimiento['fecha_solicitud'])
                                    ? date('d/m/Y H:i', strtotime($movimiento['fecha_solicitud']))
                                    : '—' ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Resuelto por</span>
                            <span class="detail-value"><?= htmlspecialchars($movimiento['usuario_aprueba'] ?? '—') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha Resolución</span>
                            <span class="detail-value">
                                <?= !empty($movimiento['fecha_aprobacion'])
                                    ? date('d/m/Y H:i', strtotime($movimiento['fecha_aprobacion']))
                                    : '—' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div class="card">
                <div class="card-header"><h3>Estado del Acta</h3></div>
                <div class="card-body" style="text-align:center;padding:2rem 1rem">
                    <span class="badge"
                          style="background:<?= $colorEstado ?>;font-size:1rem;padding:.5rem 1.5rem;border-radius:9999px">
                        <?= ucfirst(htmlspecialchars($movimiento['estado'])) ?>
                    </span>
                    <?php if ($esPendiente && $puedeAprobar): ?>
                    <p style="color:var(--gray-500);font-size:.85rem;margin-top:1rem">
                        Este acta está pendiente de resolución.
                    </p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</main>

<style>
.detail-layout {
    display:grid; grid-template-columns:2fr 1fr; gap:1.5rem; align-items:start;
}
@media (max-width:768px) {
    .detail-layout { grid-template-columns:1fr; }
}
</style>

<?php if ($puedeAprobar && $esPendiente): ?>
<script>
'use strict';
(function () {
    const id   = <?= (int)$movimiento['id_movimiento'] ?>;
    const BASE = '<?= $base_url ?>';
    const csrf = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';

    async function resolver(accion) {
        const label = accion === 'aprobar' ? 'aprobar' : 'rechazar';
        if (!confirm(`¿Confirmar ${label} este movimiento?`)) return;

        const btn = document.getElementById(accion === 'aprobar' ? 'btnAprobar' : 'btnRechazar');
        setLoading(btn, true);

        try {
            const res  = await fetch(`${BASE}/movimientos/${id}/${accion}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `csrf_token=${encodeURIComponent(csrf)}`
            });
            const data = await res.json();

            if (data.success) {
                Toast.success(`Movimiento ${label === 'aprobar' ? 'aprobado' : 'rechazado'} correctamente`);
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.error(data.error || 'Error al procesar la solicitud');
                setLoading(btn, false);
            }
        } catch {
            Toast.error('Error de conexión. Intente nuevamente.');
            setLoading(btn, false);
        }
    }

    document.getElementById('btnAprobar').addEventListener('click', () => resolver('aprobar'));
    document.getElementById('btnRechazar').addEventListener('click', () => resolver('rechazar'));
})();
</script>
<?php endif; ?>
