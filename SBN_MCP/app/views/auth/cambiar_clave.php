<?php
/**
 * =============================================================================
 * VISTA: CAMBIAR CONTRASEÑA (PRIMER LOGIN)
 * =============================================================================
 * 
 * Página obligatoria para usuarios que ingresan por primera vez o cuya
 * contraseña fue restablecida. Requiere que el usuario establezca una
 * nueva contraseña segura antes de continuar.
 * 
 * Características de seguridad:
 * - Validación de fortaleza de contraseña en tiempo real
 * - Confirmación de contraseña (debe coincidir)
 * - No permite usar la contraseña anterior
 * - Redirección forzosa al dashboard tras cambio exitoso
 * 
 * @var string $csrf_token Token CSRF para seguridad del formulario
 * @var string $base_url   URL base de la aplicación
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña — MCP</title>
    <link rel="icon" type="image/svg+xml" href="<?= $base_url ?? '' ?>/img/favicon.svg">
    <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/auth.css">
</head>
<body>

<main class="login-container" role="main">
    <div class="login-box">

        <header class="login-header">
            <div class="login-logo">
                <img src="<?= $base_url ?? '' ?>/img/logo-mcp.svg"
                     alt="Maternidad Concepción Palacios" width="80" height="80">
            </div>
            <h1>Cambio de Contraseña Requerido</h1>
            <p class="login-subtitle">Por seguridad, establezca una nueva contraseña antes de continuar.</p>
        </header>

        <form id="cambiarClaveForm" class="login-form" method="POST"
              action="<?= ($base_url ?? '') . '/cambiar-clave' ?>"
              novalidate aria-label="Formulario de cambio de contraseña">

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

            <div class="form-group">
                <label for="nueva_clave">Nueva contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="nueva_clave" name="nueva_clave"
                           class="form-input" placeholder="Mínimo 8 caracteres"
                           required autocomplete="off"
                           aria-required="true" aria-describedby="nueva_clave-error pwd-strength-label">
                    <button type="button" class="toggle-pwd" aria-label="Mostrar contraseña" aria-pressed="false"
                            data-target="nueva_clave">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <span class="form-error" id="nueva_clave-error" role="alert" aria-live="polite"></span>

                <!-- Indicador de fortaleza -->
                <div class="pwd-strength" aria-live="polite">
                    <div class="pwd-strength-bar">
                        <div class="pwd-strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="pwd-strength-label" id="pwd-strength-label"></span>
                </div>

                <!-- Reglas de contraseña -->
                <ul class="pwd-rules" id="pwdRules" aria-label="Requisitos de contraseña">
                    <li id="rule-len"     class="rule">Mínimo 8 caracteres</li>
                    <li id="rule-upper"   class="rule">Al menos una mayúscula (A–Z)</li>
                    <li id="rule-lower"   class="rule">Al menos una minúscula (a–z)</li>
                    <li id="rule-num"     class="rule">Al menos un número (0–9)</li>
                    <li id="rule-special" class="rule">Al menos un carácter especial (!@#$…)</li>
                </ul>
            </div>

            <div class="form-group">
                <label for="confirmar_clave">Confirmar contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="confirmar_clave" name="confirmar_clave"
                           class="form-input" placeholder="Repita la contraseña"
                           required autocomplete="off"
                           aria-required="true" aria-describedby="confirmar_clave-error">
                    <button type="button" class="toggle-pwd" aria-label="Mostrar confirmación" aria-pressed="false"
                            data-target="confirmar_clave">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                <span class="form-error" id="confirmar_clave-error" role="alert" aria-live="polite"></span>
            </div>

            <button type="submit" class="btn-login" id="btnGuardar">
                <span class="btn-text">Guardar Nueva Contraseña</span>
                <span class="btn-spinner" aria-hidden="true"></span>
            </button>

            <div id="formMessage" class="login-message" role="alert" aria-live="assertive"></div>

        </form>

        <footer class="login-footer">
            <p>Sistema de Gestión de Bienes Nacionales — Maternidad Concepción Palacios</p>
        </footer>

    </div>
</main>

<style>
.pwd-rules {
    list-style:none; padding:.6rem 0 0; margin:0;
    display:grid; grid-template-columns:1fr 1fr; gap:.2rem .75rem;
}
.rule {
    font-size:.72rem; color:var(--gray-400); padding:.15rem 0;
    padding-left:1.1rem; position:relative;
}
.rule::before { content:'○'; position:absolute; left:0; }
.rule.ok  { color:var(--accent); }
.rule.ok::before  { content:'✓'; }
.rule.bad { color:var(--danger); }
.rule.bad::before { content:'✗'; }
</style>

<script>
'use strict';
(function () {
    const BASE    = '<?= ($base_url ?? '') ?>';
    const form    = document.getElementById('cambiarClaveForm');
    const btn     = document.getElementById('btnGuardar');
    const msgDiv  = document.getElementById('formMessage');
    const pwdInput= document.getElementById('nueva_clave');
    const cfmInput= document.getElementById('confirmar_clave');

    /* Toggle visibilidad */
    document.querySelectorAll('.toggle-pwd').forEach(function (b) {
        b.addEventListener('click', function () {
            const target = document.getElementById(this.dataset.target);
            const visible = target.type === 'text';
            target.type = visible ? 'password' : 'text';
            this.setAttribute('aria-pressed', String(!visible));
            this.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });

    /* Reglas de validación */
    const RULES = {
        'rule-len':     v => v.length >= 8,
        'rule-upper':   v => /[A-Z]/.test(v),
        'rule-lower':   v => /[a-z]/.test(v),
        'rule-num':     v => /[0-9]/.test(v),
        'rule-special': v => /[\W_]/.test(v),
    };
    const STRENGTH_LABELS = ['', 'Muy débil', 'Débil', 'Aceptable', 'Buena', 'Fuerte'];
    const STRENGTH_CLASSES = ['', 'weak', 'weak', 'fair', 'good', 'strong'];

    pwdInput.addEventListener('input', function () {
        const v = this.value;
        let score = 0;
        Object.entries(RULES).forEach(([id, fn]) => {
            const ok = fn(v);
            const li = document.getElementById(id);
            li.className = 'rule ' + (v.length === 0 ? '' : ok ? 'ok' : 'bad');
            if (ok) score++;
        });
        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('pwd-strength-label');
        fill.className  = 'pwd-strength-fill ' + (v.length ? STRENGTH_CLASSES[score] : '');
        label.textContent = v.length ? STRENGTH_LABELS[score] : '';
        document.getElementById('nueva_clave-error').textContent = '';
        this.classList.remove('is-invalid');
    });

    cfmInput.addEventListener('input', function () {
        document.getElementById('confirmar_clave-error').textContent = '';
        this.classList.remove('is-invalid');
    });

    function setLoading(loading) {
        btn.disabled = loading;
        btn.classList.toggle('loading', loading);
    }

    function showMsg(text, type) {
        msgDiv.textContent = text;
        msgDiv.className   = 'login-message ' + type;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        /* Limpiar errores */
        ['nueva_clave', 'confirmar_clave'].forEach(id => {
            document.getElementById(id + '-error').textContent = '';
            document.getElementById(id).classList.remove('is-invalid');
        });
        msgDiv.className = 'login-message';

        /* Validación frontend */
        const pwd = pwdInput.value;
        const cfm = cfmInput.value;
        let valid = true;

        const failedRule = Object.entries(RULES).find(([, fn]) => !fn(pwd));
        if (failedRule) {
            document.getElementById('nueva_clave-error').textContent =
                'La contraseña no cumple todos los requisitos';
            pwdInput.classList.add('is-invalid');
            valid = false;
        }
        if (pwd !== cfm) {
            document.getElementById('confirmar_clave-error').textContent = 'Las contraseñas no coinciden';
            cfmInput.classList.add('is-invalid');
            valid = false;
        }
        if (!valid) return;

        setLoading(true);

        try {
            const res  = await fetch(BASE + '/cambiar-clave', { method:'POST', body: new FormData(this) });
            const data = await res.json();

            if (data.success) {
                showMsg(data.message || 'Contraseña actualizada correctamente.', 'success');
                setTimeout(() => { window.location.href = BASE + (data.redirect || '/dashboard'); }, 900);
            } else if (data.errors) {
                Object.entries(data.errors).forEach(([k, v]) => {
                    const errEl = document.getElementById(k + '-error');
                    const input = document.getElementById(k);
                    if (errEl) errEl.textContent = v;
                    if (input) input.classList.add('is-invalid');
                });
                setLoading(false);
            } else {
                showMsg(data.error || 'Error al cambiar la contraseña.', 'error');
                setLoading(false);
            }
        } catch {
            showMsg('Error de conexión. Intente nuevamente.', 'error');
            setLoading(false);
        }
    });
})();
</script>
</body>
</html>
