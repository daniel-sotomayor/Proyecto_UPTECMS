<?php
/**
 * =============================================================================
 * VISTA: ERROR 403 — ACCESO DENEGADO
 * =============================================================================
 * 
 * Página de error mostrada cuando un usuario intenta acceder a un recurso
 * sin los permisos necesarios. Incluye estilos inline para funcionar
 * independientemente del estado del sistema.
 * 
 * @var string $base_url URL base para recursos estáticos
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Acceso Denegado</title>
    <link rel="icon" type="image/svg+xml" href="<?= ($base_url ?? '') ?>/img/favicon.svg">
    <style>
        *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            background:#f0f4f8; color:#2d3748;
            display:flex; align-items:center; justify-content:center; min-height:100vh;
        }
        .error-wrap { text-align:center; max-width:480px; padding:2rem; }
        .error-icon {
            width:80px; height:80px; background:#fff5f5; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 1.5rem; color:#e53e3e;
        }
        .error-code  { font-size:5rem; font-weight:800; color:#e53e3e; line-height:1; letter-spacing:-.05em; }
        .error-title { font-size:1.4rem; font-weight:700; margin:.75rem 0 .5rem; color:#1a202c; }
        .error-msg   { color:#718096; line-height:1.6; margin-bottom:2rem; font-size:.95rem; }
        .btn {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.65rem 1.5rem; background:#1e3a5f; color:#fff;
            text-decoration:none; border-radius:.375rem; font-weight:600;
            font-size:.9rem; transition:background .15s;
        }
        .btn:hover { background:#2c5282; }
    </style>
</head>
<body>
    <div class="error-wrap" role="main">
        <div class="error-icon" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <div class="error-code" aria-label="Error 403">403</div>
        <h1 class="error-title">Acceso Denegado</h1>
        <p class="error-msg">
            No tiene permisos para acceder a esta sección.<br>
            Contacte al administrador si cree que esto es un error.
        </p>
        <a href="<?= ($base_url ?? '') ?>/dashboard" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Ir al Dashboard
        </a>
    </div>
</body>
</html>
