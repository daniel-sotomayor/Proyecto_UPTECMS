<?php require APP_PATH . '/views/partials/sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1>Generar Nueva Acta de Movimiento</h1>
        <a href="<?= $base_url ?>/movimientos" class="btn btn-secondary btn-sm">← Volver al listado</a>
    </div>

    <div class="form-card">
        <form id="formMovimiento" action="<?= $base_url ?>/movimientos" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
            
            <div class="form-section">
                <h3 class="form-section-title">Datos del Movimiento</h3>
                
                <div class="form-grid">
                    <!-- Selección de Bien -->
                    <div class="form-group full">
                        <label>Bien Nacional <span class="req">*</span></label>
                        <select name="bien_id" id="bien_id" class="form-control" required>
                            <option value="">Seleccione el bien a movilizar...</option>
                            <?php foreach ($bienes as $b): ?>
                                <option value="<?= $b['id_bien'] ?>" data-area="<?= $b['nombre_area'] ?>">
                                    <?= htmlspecialchars($b['codigo_interno']) ?> - <?= htmlspecialchars($b['nombre']) ?> 
                                    (<?= htmlspecialchars($b['estado_nombre']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="bien_id-error"></span>
                    </div>

                    <!-- Tipo de Movimiento -->
                    <div class="form-group full">
                        <label>Tipo de Acta <span class="req">*</span></label>
                        <select name="tipo_movimiento" id="tipo_movimiento" class="form-control" required>
                            <option value="">Seleccione tipo...</option>
                            <option value="traslado">Traslado (Cambio de Ubicación)</option>
                            <option value="desincorporacion">Desincorporación (Baja)</option>
                            <option value="asignacion">Asignación Directa</option>
                        </select>
                        <span class="form-error" id="tipo_movimiento-error"></span>
                    </div>

                    <!-- Área Destino (Solo para traslados) -->
                    <div class="form-group full" id="div_destino" style="display:none;">
                        <label>Área de Destino (Ubicación C.I.N) <span class="req">*</span></label>
                        <select name="area_destino_id" class="form-control">
                            <option value="">Seleccione ubicación destino...</option>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?= $a['id_area'] ?>">
                                    <?= htmlspecialchars($a['edificio']) ?> / <?= htmlspecialchars($a['piso']) ?> / <?= htmlspecialchars($a['nombre_area']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="area_destino_id-error"></span>
                    </div>

                    <!-- Motivo Justificado -->
                    <div class="form-group full">
                        <label>Motivo del Movimiento <span class="req">*</span></label>
                        <textarea name="motivo" class="form-control" rows="4" placeholder="Describa la razón técnica del cambio..." required></textarea>
                        <span class="form-error" id="motivo-error"></span>
                    </div>
                </div>
            </div>

            <div class="form-navigation">
                <button type="submit" class="btn btn-success" id="submitBtn">
                    ✓ Registrar Movimiento y Generar Acta
                </button>
            </div>
            
            <div id="formMessage" class="message"></div>
        </form>
    </div>
</main>

<script>
const BASE = '<?= $base_url ?>';

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formMovimiento');
    const selectTipo = document.getElementById('tipo_movimiento');
    const divDestino = document.getElementById('div_destino');

    // Mostrar/Ocultar destino según tipo
    selectTipo.addEventListener('change', function() {
        divDestino.style.display = (this.value === 'traslado') ? 'block' : 'none';
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const msg = document.getElementById('formMessage');
        const submitBtn = document.getElementById('submitBtn');
        msg.className = 'message';
        document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';
        
        try {
            const res = await fetch(BASE + '/movimientos', {
                method: 'POST',
                body: new FormData(form)
            });
            
            const data = await res.json();
            
            if (data.success) {
                msg.className = 'message success';
                msg.textContent = '✓ ' + data.message;
                setTimeout(() => window.location.href = BASE + '/movimientos', 1000);
            } else if (data.errors) {
                Object.entries(data.errors).forEach(([k,v]) => {
                    const el = document.getElementById(k + '-error');
                    if (el) el.textContent = v;
                });
                msg.className = 'message error';
                msg.textContent = '⚠ Corrija los errores indicados';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ Registrar Movimiento y Generar Acta';
            } else {
                msg.className = 'message error';
                msg.textContent = data.error || 'Error al procesar el acta';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✓ Registrar Movimiento y Generar Acta';
            }
        } catch (error) {
            msg.className = 'message error';
            msg.textContent = 'Error de conexión con el servidor';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '✓ Registrar Movimiento y Generar Acta';
        }
    });
});
</script>