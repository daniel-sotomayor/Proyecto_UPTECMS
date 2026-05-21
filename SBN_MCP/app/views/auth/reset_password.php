<?php
/**
 * =============================================================================
 * VISTA: RESTABLECER CONTRASEÑA (DESPUÉS DE VERIFICAR CÓDIGO)
 * =============================================================================
 * 
 * Esta vista permite al usuario crear una nueva contraseña después de
 * haber verificado el código de recuperación. Incluye:
 * - Validación de fortaleza de contraseña en tiempo real
 * - Indicador visual de fortaleza (barra de progreso)
 * - Lista de requisitos que se actualizan dinámicamente
 * - Confirmación de contraseña (debe coincidir)
 * 
 * Requisitos de contraseña:
 * - Mínimo 8 caracteres
 * - Al menos una mayúscula
 * - Al menos una minúscula
 * - Al menos un número
 * - Al menos un carácter especial
 * 
 * @var string $title      Título de la página
 * @var string $base_url   URL base para enlaces y peticiones AJAX
 * @var string $csrf_token Token CSRF para seguridad
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Restablecer Contraseña') ?> - SBN MCP</title>
    
    <!-- Hoja de estilos base para autenticación -->
    <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/auth.css">
    
    <!-- Estilos específicos de esta página -->
    <style>
        /* Variables CSS locales para personalización */
        :root {
            --primary: #0f172a;
            --accent: #3b82f6;
            --success: #10b981;
            --danger: #ef4444;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text-main: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        /* Reset básico */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* Layout principal - centra el formulario */
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Contenedor del formulario */
        .auth-container {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }

        /* Encabezado del formulario */
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Logo con gradiente */
        .auth-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent), #2563eb);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 800;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Grupos de campos */
        .form-group {
            margin-bottom: 1.25rem;
        }

        /* Etiquetas de campos */
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        /* Campos de entrada */
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* ==========================================================================
        INDICADOR DE FORTALEZA DE CONTRASEÑA
        ========================================================================== */

        /* Contenedor de la barra de fortaleza */
        .password-strength {
            height: 4px;
            background: var(--border);
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }

        /* Barra de progreso que cambia de color según fortaleza */
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
            border-radius: 2px;
        }

        /* Estados de fortaleza: débil (rojo), medio (amarillo), fuerte (verde) */
        .strength-weak { background: var(--danger); width: 33%; }
        .strength-medium { background: var(--warning); width: 66%; }
        .strength-strong { background: var(--success); width: 100%; }

        /* Botón de acción */
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: var(--radius);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #2563eb);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Footer con enlace */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .auth-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }

        /* Alertas de notificación */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* ==========================================================================
        LISTA DE REQUISITOS DE CONTRASEÑA
        ========================================================================== */

        /* Lista de requisitos */
        .requirements {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        .requirements li {
            margin-bottom: 0.25rem;
        }

        /* Requisito cumplido (cambia a verde) */
        .requirements li.met {
            color: var(--success);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">🔑</div>
            <h1>Nueva Contraseña</h1>
            <p>Crea una contraseña segura para tu cuenta</p>
        </div>

        <div id="alert-container"></div>

        <form id="reset-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <div class="form-group">
                <label class="form-label">Nueva Contraseña</label>
                <input type="password" name="nueva_clave" id="password" class="form-control" 
                    placeholder="Mínimo 8 caracteres" minlength="8" required>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strength-bar"></div>
                </div>
                <ul class="requirements" id="requirements">
                    <li id="req-length">Mínimo 8 caracteres</li>
                    <li id="req-upper">Al menos una mayúscula</li>
                    <li id="req-lower">Al menos una minúscula</li>
                    <li id="req-number">Al menos un número</li>
                    <li id="req-special">Al menos un carácter especial</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Contraseña</label>
                <input type="password" name="confirmar_clave" id="confirm-password" class="form-control" 
                    placeholder="Repite la contraseña" required>
            </div>

            <button type="submit" class="btn btn-primary" id="submit-btn">
                Guardar Nueva Contraseña
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= $base_url ?? '' ?>/login">← Volver al inicio de sesión</a>
        </div>
    </div>

    <script>
    /**
     * =============================================================================
     * SISTEMA DE VALIDACIÓN DE CONTRASEÑA
     * =============================================================================
     * 
     * Valida la fortaleza de la contraseña en tiempo real y actualiza:
     * - La barra de fortaleza visual
     * - La lista de requisitos cumplidos
     * - El estado del botón de envío
     * =============================================================================
     */
    
    // Referencias a elementos del DOM
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const strengthBar = document.getElementById('strength-bar');
    
    /**
     * Evalúa la fortaleza de una contraseña y actualiza la UI
     * @param {string} pass - Contraseña a evaluar
     * @returns {boolean} - true si cumple todos los requisitos
     */
    function checkPasswordStrength(pass) {
        let strength = 0;
    
        // Verificar cada requisito
        if (pass.length >= 8) strength++;
        if (/[A-Z]/.test(pass)) strength++;
        if (/[a-z]/.test(pass)) strength++;
        if (/\d/.test(pass)) strength++;
        if (/[\W_]/.test(pass)) strength++;

        // Actualizar barra de fortaleza visual
        strengthBar.className = 'password-strength-bar';
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');      // Débil: rojo
        } else if (strength <= 4) {
            strengthBar.classList.add('strength-medium');    // Media: amarillo
        } else {
            strengthBar.classList.add('strength-strong');    // Fuerte: verde
        }

        // Actualizar lista de requisitos (marcar los cumplidos en verde)
        document.getElementById('req-length').classList.toggle('met', pass.length >= 8);
        document.getElementById('req-upper').classList.toggle('met', /[A-Z]/.test(pass));
        document.getElementById('req-lower').classList.toggle('met', /[a-z]/.test(pass));
        document.getElementById('req-number').classList.toggle('met', /\d/.test(pass));
        document.getElementById('req-special').classList.toggle('met', /[\W_]/.test(pass));
        
        return strength === 5; // true solo si cumple todos los requisitos
    }

    // Evento: validar en tiempo real mientras el usuario escribe
    password.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });

    /**
     * =============================================================================
     * MANEJADOR DEL FORMULARIO
     * =============================================================================
     * 
     * Valida que las contraseñas coincidan y cumplan los requisitos antes
     * de enviar la petición AJAX al servidor.
     * =============================================================================
     */
    document.getElementById('reset-form').addEventListener('submit', async function(e) {
        // Prevenir envío tradicional
        e.preventDefault();

        // Referencias a elementos
        const btn = document.getElementById('submit-btn');
        const alertContainer = document.getElementById('alert-container');

        // Validación: las contraseñas deben coincidir
        if (password.value !== confirmPassword.value) {
            alertContainer.innerHTML = '<div class="alert alert-error">Las contraseñas no coinciden</div>';
            return;
        }

        // Validación: debe cumplir todos los requisitos de fortaleza
        if (!checkPasswordStrength(password.value)) {
            alertContainer.innerHTML = '<div class="alert alert-error">La contraseña no cumple con todos los requisitos de seguridad</div>';
            return;
        }

        // Mostrar estado de carga
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        alertContainer.innerHTML = '';

        try {
            // Preparar datos del formulario
            const formData = new FormData(this);

            // Enviar petición al servidor
            const response = await fetch('<?= $base_url ?? '' ?>/restablecer-clave/guardar', {
                method: 'POST',
                body: formData
            });

            // Parsear respuesta JSON
            const data = await response.json();

            if (data.success) {
                // Éxito: mostrar mensaje y redirigir al login
                alertContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                setTimeout(() => {
                    window.location.href = data.redirect || '<?= $base_url ?? '' ?>/login';
                }, 2000);
            } else if (data.errors) {
                // Errores de validación del servidor
                const errors = Object.values(data.errors).join('<br>');
                alertContainer.innerHTML = '<div class="alert alert-error">' + errors + '</div>';
            } else {
                // Otro tipo de error
                alertContainer.innerHTML = '<div class="alert alert-error">' + (data.error || 'Error desconocido') + '</div>';
            }
        } catch (error) {
            // Error de conexión
            alertContainer.innerHTML = '<div class="alert alert-error">Error de conexión. Intenta nuevamente.</div>';
        } finally {
            // Restaurar estado del botón
            btn.disabled = false;
            btn.textContent = 'Guardar Nueva Contraseña';
        }
    });
    </script>
</body>
</html>
