<?php
/**
 * Vista: Formulario de creación de usuario
 *
 * @var array  $roles    Lista de roles disponibles.
 * @var string $csrf_token Token CSRF.
 * @var string $base_url   URL base de la aplicación.
 */

require APP_PATH . '/views/partials/sidebar.php';
?>

<main class="main-content" id="mainContent">

    <div class="page-header">
        <h1>Nuevo Usuario</h1>
        <a href="<?= $base_url ?>/usuarios" class="btn btn-secondary btn-sm">← Volver</a>
    </div>

    <div class="form-card">
        <form id="createUserForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <!-- Datos personales -->
            <div class="form-section">
                <div class="form-section-title">1. Datos Personales</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="primer_nombre">Primer Nombre <span class="req">*</span></label>
                        <input type="text" name="primer_nombre" id="primer_nombre"
                            class="form-input" required autocomplete="given-name"
                            aria-required="true" aria-describedby="primer_nombre-error">
                        <span class="form-error" id="primer_nombre-error" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="segundo_nombre">Segundo Nombre</label>
                        <input type="text" name="segundo_nombre" id="segundo_nombre"
                            class="form-input" autocomplete="additional-name">
                    </div>
                    <div class="form-group">
                        <label for="primer_apellido">Primer Apellido <span class="req">*</span></label>
                        <input type="text" name="primer_apellido" id="primer_apellido"
                            class="form-input" required autocomplete="family-name"
                            aria-required="true" aria-describedby="primer_apellido-error">
                        <span class="form-error" id="primer_apellido-error" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="segundo_apellido">Segundo Apellido</label>
                        <input type="text" name="segundo_apellido" id="segundo_apellido"
                            class="form-input">
                    </div>
                </div>

                <!-- Preview username -->
                <div class="username-preview-wrap" aria-live="polite">
                    <span class="username-preview-label">Username generado:</span>
                    <span id="usernamePreview" class="username-preview-value">—</span>
                    <span class="username-preview-hint">Se genera automáticamente al ingresar nombre y apellido</span>
                </div>
            </div>

            <!-- Datos de acceso -->
            <div class="form-section">
                <div class="form-section-title">2. Datos de Acceso</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cedula">Cédula <span class="req">*</span></label>
                        <input type="text" name="cedula" id="cedula"
                               class="form-input" placeholder="Ej: 12345678" required
                               inputmode="numeric" pattern="\d{6,9}" maxlength="9"
                               aria-required="true" aria-describedby="cedula-error">
                        <span class="form-error" id="cedula-error" role="alert"></span>
                        <small class="form-hint">Solo números, entre 6 y 9 dígitos</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="req">*</span></label>
                        <input type="email" name="email" id="email"
                               class="form-input" required autocomplete="email"
                               aria-required="true" aria-describedby="email-error">
                        <span class="form-error" id="email-error" role="alert"></span>
                    </div>
                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <input type="text" name="cargo" id="cargo" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="id_rol">Rol <span class="req">*</span></label>
                        <select name="id_rol" id="id_rol" class="form-select" required
                                aria-required="true" aria-describedby="id_rol-error">
                            <option value="">— Seleccione —</option>
                            <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>">
                                <?= htmlspecialchars($rol['nombre']) ?>
                                <?= !empty($rol['descripcion']) ? ' — ' . htmlspecialchars($rol['descripcion']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-error" id="id_rol-error" role="alert"></span>
                    </div>
                </div>
            </div>

            <!-- Clave temporal -->
            <div class="form-section">
                <div class="form-section-title">3. Clave Temporal</div>
                <p class="form-hint" style="margin-bottom:.75rem">
                    El usuario deberá cambiarla en su primer inicio de sesión.
                </p>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="clave_temporal">Clave Temporal <span class="req">*</span></label>
                        <div class="pwd-field-wrap">
                            <input type="password" name="clave_temporal" id="clave_temporal"
                                   class="form-input" placeholder="Mínimo 8 caracteres"
                                   required autocomplete="off"
                                   aria-required="true" aria-describedby="clave_temporal-error">
                            <button type="button" class="pwd-toggle-btn" data-target="clave_temporal"
                                    aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <span class="form-error" id="clave_temporal-error" role="alert"></span>
                        <ul class="pwd-rules-list" aria-label="Requisitos de contraseña">
                            <li id="cr-len">Mínimo 8 caracteres</li>
                            <li id="cr-upper">Al menos una mayúscula</li>
                            <li id="cr-lower">Al menos una minúscula</li>
                            <li id="cr-num">Al menos un número</li>
                            <li id="cr-special">Al menos un carácter especial</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success" id="btnCreate">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="8.5" cy="7" r="4"/>
                        <line x1="20" y1="8" x2="20" y2="14"/>
                        <line x1="23" y1="11" x2="17" y2="11"/>
                    </svg>
                    Crear Usuario
                </button>
                <a href="<?= $base_url ?>/usuarios" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

</main>

<style>
.username-preview-wrap {
    display:flex; align-items:center; gap:.75rem; flex-wrap:wrap;
    background:#ebf8ff; border:1px solid #bee3f8; border-radius:var(--radius);
    padding:.75rem 1rem; margin-top:1rem;
}
.username-preview-label { font-size:.8rem; font-weight:600; color:#2b6cb0; }
.username-preview-value { font-size:1.1rem; font-weight:700; color:#2b6cb0; font-family:monospace; }
.username-preview-hint  { font-size:.75rem; color:var(--gray-500); }
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
    const BASE = '<?= $base_url ?>';
    const form = document.getElementById('createUserForm');
    const btn  = document.getElementById('btnCreate');
    let previewTimer;

    // Patrones de validación
    const SOLO_LETRAS = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    const CEDULA = /^\d{6,9}$/;

    /* Toggle contraseña */
    document.querySelectorAll('.pwd-toggle-btn').forEach(function (b) {
        b.addEventListener('click', function () {
            const inp = document.getElementById(this.dataset.target);
            const vis = inp.type === 'text';
            inp.type = vis ? 'password' : 'text';
            this.setAttribute('aria-pressed', String(!vis));
        });
    });

    /* Reglas de contraseña */
    const RULES = {
        'cr-len':     v => v.length >= 8,
        'cr-upper':   v => /[A-Z]/.test(v),
        'cr-lower':   v => /[a-z]/.test(v),
        'cr-num':     v => /[0-9]/.test(v),
        'cr-special': v => /[\W_]/.test(v),
    };

    document.getElementById('clave_temporal').addEventListener('input', function () {
        const v = this.value;
        Object.entries(RULES).forEach(([id, fn]) => {
            const li = document.getElementById(id);
            li.className = v.length === 0 ? '' : fn(v) ? 'ok' : 'bad';
        });
        document.getElementById('clave_temporal-error').textContent = '';
        this.classList.remove('is-invalid');
    });

    /* Validación de nombres (solo letras) */
    ['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        
        input.addEventListener('input', function() {
            // Limpiar caracteres inválidos en tiempo real
            const valor = this.value;
            const limpio = valor.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            if (valor !== limpio) {
                this.value = limpio;
            }
        });
        
        input.addEventListener('blur', function() {
            const valor = this.value.trim();
            const errorEl = document.getElementById(id + '-error');
            
            if (!valor && id !== 'segundo_nombre' && id !== 'segundo_apellido') {
                errorEl.textContent = 'Este campo es requerido';
                this.classList.add('is-invalid');
            } else if (valor && !SOLO_LETRAS.test(valor)) {
                errorEl.textContent = 'Solo letras y espacios permitidos';
                this.classList.add('is-invalid');
            } else if (valor && (valor.length < 2 || valor.length > 50)) {
                errorEl.textContent = 'Entre 2 y 50 caracteres';
                this.classList.add('is-invalid');
            } else {
                errorEl.textContent = '';
                this.classList.remove('is-invalid');
            }
        });
    });

    /* Validación de cédula */
    const cedulaInput = document.getElementById('cedula');
    cedulaInput.addEventListener('blur', function() {
        const valor = this.value.trim();
        const errorEl = document.getElementById('cedula-error');
        
        if (!valor) {
            errorEl.textContent = 'La cédula es requerida';
            this.classList.add('is-invalid');
        } else if (!CEDULA.test(valor)) {
            errorEl.textContent = 'Formato inválido. Ej: V12345678, E123456789';
            this.classList.add('is-invalid');
        } else {
            errorEl.textContent = '';
            this.classList.remove('is-invalid');
        }
    });

    /* Preview username con debounce */
    function updatePreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(async function () {
            const nombre   = document.getElementById('primer_nombre').value.trim();
            const apellido = document.getElementById('primer_apellido').value.trim();
            const preview  = document.getElementById('usernamePreview');
            if (!nombre || !apellido) { preview.textContent = '—'; return; }
            try {
                const res  = await fetch(`${BASE}/usuarios/preview-username?primer_nombre=${encodeURIComponent(nombre)}&primer_apellido=${encodeURIComponent(apellido)}`);
                const data = await res.json();
                preview.textContent = data.username || '—';
            } catch { preview.textContent = '—'; }
        }, 400);
    }

    document.getElementById('primer_nombre').addEventListener('input', updatePreview);
    document.getElementById('primer_apellido').addEventListener('input', updatePreview);

    /* Submit con validaciones */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearFormErrors(form);

        // Validar todos los campos requeridos
        let hasErrors = false;
        
        // Validar cédula
        const cedula = cedulaInput.value.trim();
        if (!CEDULA.test(cedula)) {
            document.getElementById('cedula-error').textContent = 'Debe contener entre 6 y 9 números';
            cedulaInput.classList.add('is-invalid');
            hasErrors = true;
        }
        
        // Validar nombres
        ['primer_nombre', 'primer_apellido'].forEach(id => {
            const input = document.getElementById(id);
            const valor = input.value.trim();
            if (!valor || !SOLO_LETRAS.test(valor) || valor.length < 2) {
                document.getElementById(id + '-error').textContent = 
                    !valor ? 'Este campo es requerido' : 
                    !SOLO_LETRAS.test(valor) ? 'Solo letras permitidas' : 'Mínimo 2 caracteres';
                input.classList.add('is-invalid');
                hasErrors = true;
            }
        });
        
        if (hasErrors) {
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        setLoading(btn, true);
        const data = await apiPost(`${BASE}/usuarios`, new FormData(this), null);
        setLoading(btn, false);

        if (data.success) {
            Toast.success(`Usuario creado. Username: ${data.username}`);
            setTimeout(() => { window.location.href = BASE + data.redirect; }, 1200);
        } else if (data.errors) {
            showFormErrors(form, data.errors);
        } else {
            Toast.error(data.error || 'Error al crear el usuario');
        }
    });
})();
</script>
