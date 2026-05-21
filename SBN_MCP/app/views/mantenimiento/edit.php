<?php
/**
 * =============================================================================
 * VISTA: EDITAR MANTENIMIENTO
 * =============================================================================
 * 
 * Formulario para editar un registro de mantenimiento existente.
 * Permite corregir datos o completar información de un mantenimiento.
 * 
 * @var array  $mant         Datos del mantenimiento a editar
 * @var array  $bienes       Listado de bienes disponibles
 * @var string $csrf_token   Token CSRF
 * @var string $base_url     URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1>Editar Mantenimiento #<?= $mant['id_mantenimiento'] ?></h1>
        <a href="<?= $base_url ?>/mantenimientos/<?= $mant['id_mantenimiento'] ?>" class="btn btn-secondary btn-sm">← Volver</a>
    </div>

    <div style="max-width:760px">
        <div class="card">
            <div class="card-body">
                <div id="form-alert" style="display:none;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem"></div>
                <form id="form-edit" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="_method" value="PUT">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Bien</label>
                            <select name="bien_id" class="form-control">
                                <?php foreach ($bienes as $b): ?>
                                <option value="<?= $b['id_bien'] ?>" <?= $mant['bien_id']==$b['id_bien']?'selected':'' ?>>
                                    <?= htmlspecialchars($b['nombre'] . ' [' . $b['codigo_interno'] . ']') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Tipo de Servicio</label>
                            <select name="tipo_servicio" class="form-control">
                                <option value="preventivo" <?= $mant['tipo_servicio']==='preventivo'?'selected':'' ?>>Preventivo</option>
                                <option value="correctivo" <?= $mant['tipo_servicio']==='correctivo'?'selected':'' ?>>Correctivo</option>
                                <option value="predictivo" <?= $mant['tipo_servicio']==='predictivo'?'selected':'' ?>>Predictivo</option>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Fecha Programada</label>
                            <input type="date" name="fecha_programada" class="form-control" value="<?= htmlspecialchars($mant['fecha_programada'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Fecha Ejecutada</label>
                            <input type="date" name="fecha_ejecutada" class="form-control" value="<?= htmlspecialchars($mant['fecha_ejecutada'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Proveedor</label>
                            <input type="text" name="proveedor" class="form-control" value="<?= htmlspecialchars($mant['proveedor'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Técnico</label>
                            <input type="text" name="tecnico" class="form-control" value="<?= htmlspecialchars($mant['tecnico'] ?? '') ?>">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Costo (Bs.)</label>
                            <input type="number" name="costo" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($mant['costo'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Garantía (meses)</label>
                            <input type="number" name="garantia_meses" class="form-control" min="0" value="<?= htmlspecialchars($mant['garantia_meses'] ?? '') ?>">
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Próximo Mantenimiento</label>
                            <input type="date" name="proxima_fecha_programada" class="form-control" value="<?= htmlspecialchars($mant['proxima_fecha_programada'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label">Diagnóstico</label>
                        <textarea name="diagnostico" class="form-control" rows="2"><?= htmlspecialchars($mant['diagnostico'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:1rem">
                        <label class="form-label">Trabajo Realizado</label>
                        <textarea name="trabajo_realizado" class="form-control" rows="2"><?= htmlspecialchars($mant['trabajo_realizado'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group" style="margin-bottom:1.5rem">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"><?= htmlspecialchars($mant['observaciones'] ?? '') ?></textarea>
                    </div>

                    <div style="display:flex;gap:1rem">
                        <button type="submit" id="btn-submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="<?= $base_url ?>/mantenimientos/<?= $mant['id_mantenimiento'] ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script>
document.getElementById('form-edit').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    btn.disabled = true; btn.textContent = 'Guardando...';
    try {
        const res  = await fetch('<?= $base_url ?>/mantenimientos/<?= $mant['id_mantenimiento'] ?>', {
            method: 'PUT',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams(new FormData(this)).toString()
        });
        const data = await res.json();
        if (data.success) { window.location.href = '<?= $base_url ?>' + data.redirect; }
        else {
            const al = document.getElementById('form-alert');
            al.textContent = data.error || 'Error al guardar';
            al.style.cssText = 'display:block;background:#fff5f5;border:1px solid #fed7d7;color:#c53030';
            btn.disabled = false; btn.textContent = 'Guardar Cambios';
        }
    } catch(err) { btn.disabled = false; btn.textContent = 'Guardar Cambios'; }
});
</script>
