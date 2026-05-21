<?php
/**
 * Vista: Formulario de edición de usuario
 *
 * @var array  $usuario   Datos del usuario a editar.
 * @var array  $roles     Lista de roles disponibles.
 * @var string $csrf_token Token CSRF.
 * @var string $base_url   URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';

$nombreCompleto = htmlspecialchars(trim(
    ($usuario['primer_nombre']   ?? '') . ' ' .
    ($usuario['segundo_nombre']  ?? '') . ' ' .
    ($usuario['primer_apellido'] ?? '') . ' ' .
    ($usuario['segundo_apellido']?? '')
));
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <h1>Editar Usuario</h1>
        <a href="<?= $base_url ?>/usuarios" class="btn btn-secondary btn-sm">← Volver</a>
    </div>

    <!-- Info del usuario (solo lectura) -->
    <div class="alert alert-info" style="margin-bottom:1.5rem">
        <strong>Username:</strong>
        <code style="font-weight:700;margin:0 .5rem"><?= htmlspecialchars($usuario['username'] ?? '') ?></code>
        &nbsp;·&nbsp;
        <strong>Nombre:</strong> <?= $nombreCompleto ?>
        &nbsp;·&nbsp;
        <strong>Cédula:</strong> <?= htmlspecialchars($usuario['cedula'] ?? '') ?>
    </div>

    <div class="form-card">
        <form id="editUserForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Datos editables -->
            <div class="form-section">
                <div class="form-section-title">Datos Editables</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="req">*</span></label>
                        <input type="email" name="email" id="email"
                               class="form-input" required autocomplete="email"
                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                               aria-required="true">
                        <span class="form-error" id="email-error" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <input type="text" name="cargo" id="cargo"
                               class="form-input"
                               value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="id_rol">Rol <span class="req">*</span></label>
                        <select name="id_rol" id="id_rol" class="form-select" required aria-required="true">
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>"
                                    <?= (int)$rol['id_rol'] === (int)$usuario['id_rol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activo">Estado</label>
                        <select name="activo" id="activo" class="form-select">
                            <option value="1" <?= $usuario['activo'] ? 'selected' : '' ?>>Activo</option>
                            <option value="0" <?= !$usuario['activo'] ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Resetear clave -->
            <div class="form-section">
                <div class="form-section-title">Resetear Clave Temporal</div>
                <p class="form-hint" style="margin-bottom:.75rem">
                    Déjelo vacío para no cambiar. Al asignar nueva clave, el usuario deberá cambiarla en su próximo login.
                </p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nueva_clave_temporal">Nueva Clave Temporal</label>
                        <div class="pwd-field-wrap">
                            <input type="password" name="nueva_clave_temporal" id="nueva_clave_temporal"
                                   class="form-input" placeholder="Dejar vacío para no cambiar"
                                   autocomplete="off"
                                   aria-describedby="clave_temporal-error">
                            <button type="button" class="pwd-toggle-btn" data-target="nueva_clave_temporal"
                                    aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-error" id="clave_temporal-error" role="alert"></span>
                        <ul class="pwd-rules-list" aria-label="Requisitos de contraseña">
                            <li id="er-len">Mínimo 8 caracteres</li>
                            <li id="er-upper">Al menos una mayúscula</li>
                            <li id="er-lower">Al menos una minúscula</li>
                            <li id="er-num">Al menos un número</li>
                            <li id="er-special">Al menos un carácter especial</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success" id="btnSave">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Guardar Cambios
                </button>
                <a href="<?= $base_url ?>/usuarios" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

</main>

<style>
.pwd-field-wrap { position:relative; }
.pwd-field-wrap .form-input { padding-right:2.75rem; }
.pwd-toggle-btn {
    position:absolute; right:.6rem; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer; color:var(--gray-400);
    padding:.2rem; display:flex; align-items:center;
}
.pwd-toggle-btn:hover { color:var(--gray-700); }
.pwd-rules-list {
    list-style:none; padding:.5rem 0 0; margin:0;
    display:grid; grid-template-columns:1fr 1fr; gap:.15rem .75rem;
}
.pwd-rules-list li { font-size:.75rem; color:var(--gray-400); padding:.1rem 0 .1rem 1rem; position:relative; }
.pwd-rules-list li::before { content:'○'; position:absolute; left:0; }
.pwd-rules-list li.ok  { color:var(--accent); }
.pwd-rules-list li.ok::before  { content:'✓'; }
.pwd-rules-list li.bad { color:#e53e3e; }
.pwd-rules-list li.bad::before { content:'✗'; }
</style>

<script>
'use strict';
(function () {
    const BASE    = '<?= $base_url ?>';
    const USER_ID = <?= (int)$usuario['id_usuario'] ?>;
    const form    = document.getElementById('editUserForm');
    const btn     = document.getElementById('btnSave');

    /* Toggle contraseña */
    document.querySelectorAll('.pwd-toggle-btn').forEach(function (b) {
        b.addEventListener('click', function () {
            const inp = document.getElementById(this.dataset.target);
            const vis = inp.type === 'text';
            inp.type = vis ? 'password' : 'text';
            this.setAttribute('aria-pressed', String(!vis));
        });
    });

    /* Reglas de contraseña (solo si se escribe algo) */
    const RULES = {
        'er-len':     v => v.length >= 8,
        'er-upper':   v => /[A-Z]/.test(v),
        'er-lower':   v => /[a-z]/.test(v),
        'er-num':     v => /[0-9]/.test(v),
        'er-special': v => /[\W_]/.test(v),
    };

    document.getElementById('nueva_clave_temporal').addEventListener('input', function () {
        const v = this.value;
        Object.entries(RULES).forEach(([id, fn]) => {
            const li = document.getElementById(id);
            li.className = v.length === 0 ? '' : fn(v) ? 'ok' : 'bad';
        });
        document.getElementById('clave_temporal-error').textContent = '';
        this.classList.remove('is-invalid');
    });

    /* Submit */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFormErrors(form);

        setLoading(btn, true);
        const body = new URLSearchParams(new FormData(this)).toString();
        const res  = await fetch(`${BASE}/usuarios/${USER_ID}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        const data = await res.json();
        setLoading(btn, false);

        if (data.success) {
            Toast.success('Cambios guardados correctamente');
            setTimeout(() => { window.location.href = BASE + data.redirect; }, 1000);
        } else if (data.errors) {
            showFormErrors(form, data.errors);
        } else {
            Toast.error(data.error || 'Error al guardar los cambios');
        }
    });
})();
</script>
