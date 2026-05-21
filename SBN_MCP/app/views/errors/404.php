<?php
/**
 * =============================================================================
 * VISTA: ERROR 404 — PÁGINA NO ENCONTRADA
 * =============================================================================
 * 
 * Página de error mostrada cuando se solicita una URL que no existe
 * en el sistema. Proporciona enlaces para volver al inicio o al dashboard.
 * 
 * @var string $base_url URL base para recursos estáticos
 * @var bool   $is_auth  Indica si el usuario está autenticado (para mostrar enlace adecuado)
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página no encontrada</title>
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
            width:80px; height:80px; background:#ebf8ff; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 1.5rem; color:#3182ce;
        }
        .error-code  { font-size:5rem; font-weight:800; color:#1e3a5f; line-height:1; letter-spacing:-.05em; }
        .error-title { font-size:1.4rem; font-weight:700; margin:.75rem 0 .5rem; color:#1a202c; }
        .error-msg   { color:#718096; line-height:1.6; margin-bottom:2rem; font-size:.95rem; }
        .btn-group   { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; }
        .btn {
            display:inline-flex; align-items:center; gap:.5rem;
            padding:.65rem 1.5rem; background:#1e3a5f; color:#fff;
            text-decoration:none; border-radius:.375rem; font-weight:600;
            font-size:.9rem; transition:background .15s;
        }
        .btn:hover { background:#2c5282; }
        .btn-sec { background:#e2e8f0; color:#2d3748; }
        .btn-sec:hover { background:#cbd5e0; color:#1a202c; }
    </style>
</head>
<body>
    <div class="error-wrap" role="main">
        <div class="error-icon" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
        </div>
        <div class="error-code" aria-label="Error 404">404</div>
        <h1 class="error-title">Página no encontrada</h1>
        <p class="error-msg">
            La página que busca no existe o ha sido movida.<br>
            Verifique la URL o regrese al inicio.
        </p>
        <div class="btn-group">
            <a href="<?= ($base_url ?? '') ?>/dashboard" class="btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-sec">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Volver
            </a>
        </div>
    </div>
</body>
</html>
