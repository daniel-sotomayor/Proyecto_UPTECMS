<?php
/**
 * =============================================================================
 * VISTA: SOLICITAR RECUPERACIÓN DE CONTRASEÑA
 * =============================================================================
 * 
 * Esta vista permite a los usuarios solicitar un código de verificación
 * para restablecer su contraseña. El proceso es:
 * 1. Usuario ingresa cédula y correo electrónico
 * 2. Sistema valida los datos y envía código al correo
 * 3. Usuario es redirigido a la página de verificación
 * 
 * Reglas de seguridad implementadas:
 * - Máximo 5 códigos por día por usuario
 * - Código válido por 30 minutos
 * - CSRF token en todos los formularios
 * 
 * @var string $title      Título de la página
 * @var string $base_url   URL base para enlaces
 * @var string $csrf_token Token CSRF para seguridad del formulario
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Recuperar Contraseña') ?> - SBN MCP</title>
    
    <!-- Hoja de estilos base para autenticación -->
    <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/auth.css">

    <!-- Estilos específicos de esta página -->
    <style>
        /* Variables CSS locales para personalización rápida */
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

        /* Layout principal - centra el formulario en pantalla */
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Contenedor del formulario - tarjeta blanca con sombra */
        .auth-container {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
        }

        /* Encabezado con logo e indicaciones */
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Logo circular con gradiente */
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

        /* Grupos de campos del formulario */
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

        /* Campos de entrada de texto */
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

        /* Botón de acción principal */
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
            transform: none;
        }

        /* Footer con enlace de retorno */
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

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Alertas de éxito y error */
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

        /* Caja informativa con reglas del sistema */
        .info-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .info-box ul {
            margin-left: 1rem;
            margin-top: 0.5rem;
        }
        
        .info-box li {
            margin-bottom: 0.25rem;
        }

        /* Utilidad para ocultar elementos */
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">MCP</div>
            <h1>Recuperar Contraseña</h1>
            <p>Ingresa tus datos para recibir un código de verificación</p>
        </div>

        <div id="alert-container"></div>

        <div class="info-box">
            <strong>Información importante:</strong>
            <ul>
                <li>El código tiene una validez de 30 minutos</li>
                <li>Solo puedes solicitar 5 códigos por día</li>
                <li>El código será enviado al correo registrado</li>
            </ul>
        </div>

        <form id="forgot-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            
            <div class="form-group">
                <label class="form-label">Cédula de Identidad</label>
                <input type="text" name="cedula" class="form-control" placeholder="Ej: V12345678" required>
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" placeholder="tu@correo.com" required>
            </div>

            <button type="submit" class="btn btn-primary" id="submit-btn">
                Enviar Código de Verificación
            </button>
        </form>

        <div class="auth-footer">
            <a href="<?= $base_url ?? '' ?>/login">← Volver al inicio de sesión</a>
        </div>
    </div>

    <script>
    /**
     * =============================================================================
     * MANEJADOR DEL FORMULARIO DE RECUPERACIÓN
     * =============================================================================
     * 
     * Envía la solicitud de recuperación vía AJAX para mejor UX.
     * Muestra spinner durante la carga y maneja errores de conexión.
     * =============================================================================
     */
    document.getElementById('forgot-form').addEventListener('submit', async function(e) {
        // Prevenir envío tradicional del formulario
        e.preventDefault();

        // Referencias a elementos del DOM
        const btn = document.getElementById('submit-btn');
        const alertContainer = document.getElementById('alert-container');

        // Deshabilitar botón y mostrar estado de carga
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        alertContainer.innerHTML = '';

        try {
            // Preparar datos del formulario
            const formData = new FormData(this);

            // Enviar petición al servidor
            const response = await fetch('<?= $base_url ?? '' ?>/recuperar-clave/enviar', {
                method: 'POST',
                body: formData
            });

            // Parsear respuesta JSON
            const data = await response.json();

            if (data.success) {
                // Mostrar mensaje de éxito
                alertContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                
                // Redirigir a página de verificación después de 2 segundos
                setTimeout(() => {
                    window.location.href = '<?= $base_url ?? '' ?>/verificar-codigo?email=' + encodeURIComponent(formData.get('email'));
                }, 2000);
            } else {
                // Mostrar mensaje de error
                alertContainer.innerHTML = '<div class="alert alert-error">' + (data.error || 'Error desconocido') + '</div>';
            }
        } catch (error) {
            // Manejar errores de red/conexión
            alertContainer.innerHTML = '<div class="alert alert-error">Error de conexión. Intenta nuevamente.</div>';
        } finally {
            // Restaurar estado del botón
            btn.disabled = false;
            btn.textContent = 'Enviar Código de Verificación';
        }
    });
    </script>
</body>
</html>
