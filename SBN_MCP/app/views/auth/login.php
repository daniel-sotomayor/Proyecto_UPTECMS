<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Iniciar Sesión') ?> — MCP</title>
    <link rel="icon" type="image/svg+xml" href="<?= $base_url ?? '' ?>/img/favicon.svg">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary:      #6366f1;
            --primary-dark: #4f46e5;
            --primary-glow: rgba(99,102,241,0.35);
            --bg:           #09090b;
            --surface:      rgba(255,255,255,0.04);
            --border:       rgba(255,255,255,0.09);
            --text:         #f4f4f5;
            --muted:        #a1a1aa;
            --danger:       #ef4444;
            --success:      #10b981;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        /* Fondo con blobs */
        .bg-blobs {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.12;
            animation: drift 20s ease-in-out infinite alternate;
        }
        .blob-1 { width: 480px; height: 480px; background: #6366f1; top: -120px; left: -120px; }
        .blob-2 { width: 400px; height: 400px; background: #ec4899; bottom: -100px; right: -100px; opacity: 0.08; animation-delay: -8s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.1); }
        }

        /* Card */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px 36px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 24px 48px rgba(0,0,0,0.4);
            animation: fadeUp 0.6s cubic-bezier(0.16,1,0.3,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Header */
        .card-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo-box {
            width: 68px; height: 68px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px;
        }
        .card-header h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }
        .card-header p {
            font-size: 13px;
            color: var(--muted);
        }

        /* Campos */
        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .field input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 13px 16px;
            color: var(--text);
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .field input::placeholder { color: #52525b; }
        .field input:focus {
            outline: none;
            background: rgba(255,255,255,0.08);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* Botón */
        .btn {
            width: 100%;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn:hover:not(:disabled) {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -6px var(--primary-glow);
        }
        .btn:disabled { opacity: 0.55; cursor: not-allowed; transform: none; }

        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            display: none;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Mensaje */
        .msg {
            margin-top: 16px;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
            display: none;
        }
        .msg-error   { background: rgba(239,68,68,0.1);  color: var(--danger);  border: 1px solid rgba(239,68,68,0.2); }
        .msg-success { background: rgba(16,185,129,0.1); color: var(--success); border: 1px solid rgba(16,185,129,0.2); }

        /* Olvidé contraseña */
        .link-forgot {
            display: block;
            text-align: center;
            margin-top: 14px;
            font-size: 12px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .link-forgot:hover { color: var(--primary); }

        /* Footer */
        .card-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 11px;
            color: var(--muted);
            line-height: 1.7;
        }
        .card-footer span { opacity: 0.5; }
    </style>
</head>
<body>

<div class="bg-blobs">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<main class="card">
    <div class="card-header">
        <div class="logo-box">
            <img src="<?= $base_url ?? '' ?>/img/logo-mcp.svg" alt="Logo MCP" width="44" height="44"
                 onerror="this.style.display='none'">
        </div>
        <h1>Bienvenido</h1>
        <p>Gestión de Bienes Nacionales — MCP</p>
    </div>

    <form id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

        <div class="field">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username"
                   placeholder="Ej: jperez"
                   autocomplete="username"
                   spellcheck="false"
                   autocapitalize="none"
                   maxlength="50"
                   required>
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required>
        </div>

        <button type="submit" class="btn" id="btnSubmit">
            <span id="btnText">Entrar al Sistema</span>
            <div class="spinner" id="btnSpinner"></div>
        </button>

        <a href="<?= $base_url ?? '' ?>/recuperar-clave" class="link-forgot">
            ¿Olvidaste tu contraseña?
        </a>

        <div id="msg" class="msg"></div>
    </form>

    <footer class="card-footer">
        <div>Maternidad Concepción Palacios</div>
        <span>LOBIP · Gaceta Oficial 43.077 · v1.1.0</span>
    </footer>
</main>

<script>
'use strict';
(function () {
    const form    = document.getElementById('loginForm');
    const btn     = document.getElementById('btnSubmit');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');
    const msg     = document.getElementById('msg');
    const BASE    = '<?= addslashes($base_url ?? '') ?>';

    function showMsg(text, type) {
        msg.textContent = text;
        msg.className   = 'msg msg-' + type;
        msg.style.display = 'block';
    }

    function setLoading(on) {
        btn.disabled          = on;
        btnText.style.opacity = on ? '0.5' : '1';
        spinner.style.display = on ? 'block' : 'none';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        msg.style.display = 'none';

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        if (username.length < 3) {
            showMsg('El usuario debe tener al menos 3 caracteres', 'error');
            document.getElementById('username').focus();
            return;
        }
        if (!password) {
            showMsg('Ingresa tu contraseña', 'error');
            document.getElementById('password').focus();
            return;
        }

        setLoading(true);

        try {
            const fd = new FormData(this);
            const res = await fetch(BASE + '/login', { method: 'POST', body: fd });
            const data = await res.json();

            if (data.success) {
                showMsg('Acceso concedido. Redirigiendo…', 'success');
                setTimeout(() => {
                    window.location.href = BASE + (data.redirect || '/dashboard');
                }, 700);
            } else {
                showMsg(data.error || 'Credenciales incorrectas', 'error');
                setLoading(false);
            }
        } catch (_) {
            showMsg('Error de conexión con el servidor', 'error');
            setLoading(false);
        }
    });
})();
</script>

</body>
</html>
