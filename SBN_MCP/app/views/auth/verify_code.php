<?php
/**
 * =============================================================================
 * VISTA: VERIFICAR CÓDIGO DE RECUPERACIÓN
 * =============================================================================
 * 
 * Permite al usuario ingresar el código de 6 dígitos enviado a su correo
 * electrónico para verificar su identidad antes de restablecer la contraseña.
 * 
 * Flujo de recuperación:
 * 1. Usuario solicita recuperación (forgot_password.php)
 * 2. Sistema envía código por email
 * 3. Usuario ingresa código en esta página
 * 4. Si es válido, redirige a reset_password.php
 * 
 * Seguridad:
 * - Código válido por 30 minutos
 * - Máximo 5 intentos por día
 * - Bloqueo temporal tras intentos fallidos
 * 
 * @var string $email      Email del usuario (prellenado desde URL)
 * @var string $csrf_token Token CSRF
 * @var string $base_url   URL base de la aplicación
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Verificar Código') ?> - SBN MCP</title>
    <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/auth.css">
    <style>
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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-container {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
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
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: all 0.2s;
            text-align: center;
            letter-spacing: 0.5rem;
            font-size: 1.25rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
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
        .btn-secondary {
            background: var(--bg);
            color: var(--text-main);
            border: 1.5px solid var(--border);
            margin-top: 0.75rem;
        }
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
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .code-input {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        .timer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .timer-expired {
            color: var(--danger);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">🔐</div>
            <h1>Verificar Código</h1>
            <p>Ingresa el código de 6 dígitos enviado a tu correo</p>
        </div>

        <div id="alert-container"></div>

        <div class="timer" id="timer">
            El código expira en: <strong id="countdown">30:00</strong>
        </div>

        <form id="verify-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <input type="hidden" name="email" id="email-field" value="">
            
            <div class="form-group">
                <label class="form-label">Código de Verificación</label>
                <input type="text" name="codigo" class="form-control code-input" 
                       placeholder="000000" maxlength="6" pattern="\d{6}" required>
            </div>

            <button type="submit" class="btn btn-primary" id="submit-btn">
                Verificar Código
            </button>
            
            <a href="<?= $base_url ?? '' ?>/recuperar-clave" class="btn btn-secondary" style="display:block;text-align:center;text-decoration:none;">
                Solicitar nuevo código
            </a>
        </form>

        <div class="auth-footer">
            <a href="<?= $base_url ?? '' ?>/login">← Volver al inicio de sesión</a>
        </div>
    </div>

    <script>
    // Obtener email de URL
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');
    document.getElementById('email-field').value = email || '';

    // Contador regresivo de 30 minutos
    let timeLeft = 30 * 60; // 30 minutos en segundos
    const countdownEl = document.getElementById('countdown');
    const timerEl = document.getElementById('timer');
    
    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        countdownEl.textContent = minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerEl.innerHTML = '<span class="timer-expired">El código ha expirado. Solicita uno nuevo.</span>';
            document.getElementById('submit-btn').disabled = true;
        }
        timeLeft--;
    }
    
    const timerInterval = setInterval(updateCountdown, 1000);
    updateCountdown();

    document.getElementById('verify-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('submit-btn');
        const alertContainer = document.getElementById('alert-container');
        
        btn.disabled = true;
        btn.textContent = 'Verificando...';
        alertContainer.innerHTML = '';
        
        try {
            const formData = new FormData(this);
            const response = await fetch('<?= $base_url ?? '' ?>/verificar-codigo/verificar', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alertContainer.innerHTML = '<div class="alert alert-error">' + (data.error || 'Código inválido') + '</div>';
            }
        } catch (error) {
            alertContainer.innerHTML = '<div class="alert alert-error">Error de conexión. Intenta nuevamente.</div>';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Verificar Código';
        }
    });
    </script>
</body>
</html>
