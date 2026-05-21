<?php
/**
 * =============================================================================
 * VISTA: CONFIGURACIÓN DEL SISTEMA
 * =============================================================================
 * 
 * Permite a los administradores modificar los parámetros globales del sistema.
 * Los cambios se guardan vía AJAX sin recargar la página.
 * 
 * Parámetros configurables típicos:
 * - Límites de paginación
 * - Configuraciones de email
 * - Parámetros de seguridad (intentos de login, expiración de sesión)
 * - Configuraciones de reportes
 * 
 * @var array  $params     Listado de parámetros del sistema con sus valores
 * @var string $csrf_token Token CSRF para la petición AJAX
 * @var string $base_url   URL base de la aplicación
 * =============================================================================
 */

require APP_PATH . '/views/partials/sidebar.php';
?>
<main class="main-content">
    <div class="page-header">
        <h1>Configuración del Sistema</h1>
    </div>

    <div style="max-width:680px">
        <div id="form-alert" style="display:none;padding:.75rem 1rem;border-radius:6px;margin-bottom:1rem;font-size:.9rem"></div>

        <div class="card">
            <div class="card-header"><h3>Parámetros del Sistema</h3></div>
            <div class="card-body">
                <form id="form-config" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <?php foreach ($params as $p): ?>
                    <div class="form-group" style="margin-bottom:1.25rem">
                        <label class="form-label" style="font-weight:600">
                            <?= htmlspecialchars($p['clave']) ?>
                        </label>
                        <?php if (!empty($p['descripcion'])): ?>
                        <small style="display:block;color:#718096;margin-bottom:.35rem"><?= htmlspecialchars($p['descripcion']) ?></small>
                        <?php endif; ?>
                        <input type="<?= $p['tipo_dato'] === 'integer' ? 'number' : 'text' ?>"
                            name="config[<?= htmlspecialchars($p['clave']) ?>]"
                            value="<?= htmlspecialchars($p['valor'] ?? '') ?>"
                            class="form-control"
                            <?= $p['tipo_dato'] === 'integer' ? 'min="0"' : '' ?>>
                    </div>
                    <?php endforeach; ?>

                    <div style="display:flex;gap:1rem;margin-top:1.5rem">
                        <button type="submit" id="btn-save" class="btn btn-primary">Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script>
document.getElementById('form-config').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save');
    btn.disabled = true; btn.textContent = 'Guardando...';
    const al = document.getElementById('form-alert');
    try {
        const res  = await fetch('<?= $base_url ?>/configuracion', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams(new FormData(this)).toString()
        });
        const data = await res.json();
        al.textContent = data.message || (data.error || 'Error');
        al.style.cssText = 'display:block;background:' + (data.success ? '#f0fff4;border:1px solid #9ae6b4;color:#276749' : '#fff5f5;border:1px solid #fed7d7;color:#c53030');
    } catch(err) {
        al.textContent = 'Error de conexión';
        al.style.cssText = 'display:block;background:#fff5f5;border:1px solid #fed7d7;color:#c53030';
    }
    btn.disabled = false; btn.textContent = 'Guardar Configuración';
});
</script>
