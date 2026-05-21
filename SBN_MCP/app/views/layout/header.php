<?php
/**
 * =============================================================================
 * LAYOUT PRINCIPAL: HEADER
 * =============================================================================
 * 
 * Este archivo es la cabecera HTML compartida por todas las páginas del sistema.
 * Se encarga de:
 * - Definir la estructura HTML5 base
 * - Cargar las hojas de estilo CSS según el tipo de página
 * - Inicializar el tema (claro/oscuro) antes de renderizar
 * - Configurar el contenedor de notificaciones (toasts)
 * 
 * Variables esperadas:
 * @var string $title      Título de la página (se muestra en la pestaña)
 * @var string $base_url   URL base de la aplicación para recursos estáticos
 * @var bool   $is_app     Indica si es página interna (carga app.css)
 * @var bool   $is_auth    Indica si es página de autenticación (carga auth.css)
 * 
 * Ejemplo de uso en controladores:
 * $data = [
 *     'title'    => 'Dashboard',
 *     'is_app'   => true,
 *     'base_url' => '/sbn_mcp'
 * ];
 * View::render('dashboard/index', $data);
 * =============================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Meta tags esenciales para responsive y compatibilidad -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Título dinámico con fallback seguro usando htmlspecialchars para prevenir XSS -->
    <title><?= htmlspecialchars($title ?? 'Sistema de Bienes Nacionales') ?> — MCP</title>
    
    <!-- Favicon en formato SVG para mejor calidad en cualquier tamaño -->
    <link rel="icon" type="image/svg+xml" href="<?= $base_url ?? '' ?>/img/favicon.svg">
    
    <!-- Meta descripción para SEO -->
    <meta name="description" content="Sistema de Gestión de Bienes Nacionales — Maternidad Concepción Palacios">
    
    <!-- ==========================================================================
        SCRIPT DE TEMA: Se ejecuta inmediatamente para evitar parpadeo de tema
        ==========================================================================
        Este script se ejecuta antes de que el DOM se renderice completamente,
        aplicando el tema guardado en localStorage para evitar el "flash" de
        tema incorrecto al cargar la página.
        ========================================================================== -->
    <script>
        (function() {
            // Recuperar tema guardado o usar 'light' como valor por defecto
            const savedTheme = localStorage.getItem('theme') || 'light';
            // Aplicar tema al elemento HTML raíz
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- ==========================================================================
        HOJAS DE ESTILO CSS
        ==========================================================================
        Se cargan diferentes CSS según el tipo de página:
        - main.css: Siempre se carga (estilos base, landing page)
        - app.css: Solo páginas internas del sistema (dashboard, bienes, etc.)
        - auth.css: Solo páginas de autenticación (login, recuperar clave)
        ========================================================================== -->
    <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/main.css" media="all">
    <?php if(isset($is_app)): ?>
        <!-- Estilos específicos para el sistema interno -->
        <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/app.css" media="all">
    <?php endif; ?>
    <?php if(isset($is_auth)): ?>
        <!-- Estilos específicos para páginas de autenticación -->
        <link rel="stylesheet" href="<?= $base_url ?? '' ?>/css/auth.css" media="all">
    <?php endif; ?>
</head>

<!-- ==========================================================================
    CLASE DEL BODY
    ==========================================================================
    Se añade una clase según el modo para facilitar estilos específicos:
    - app-mode: Páginas internas con sidebar
    - auth-mode: Páginas de login/recuperación
    - public-mode: Landing page y páginas públicas
    ========================================================================== -->
<body class="<?= isset($is_app) ? 'app-mode' : (isset($is_auth) ? 'auth-mode' : 'public-mode') ?>">
    
    <?php if(isset($is_app)): ?>
        <!-- =====================================================================
            OVERLAY DEL SIDEBAR (móvil)
            =====================================================================
            Este div se muestra en pantallas pequeñas cuando el sidebar está
            abierto, permitiendo cerrarlo al hacer clic fuera.
            ===================================================================== -->
        <div class="sidebar-overlay" id="sidebarOverlay" role="presentation" aria-hidden="true"></div>
        
        <!-- Contenedor principal del layout de aplicación -->
        <div class="app-layout">
    <?php else: ?>
        <!-- Layout público (landing page, login, etc.) -->
        <main class="public-layout">
    <?php endif; ?>

    <!-- =========================================================================
        CONTENEDOR DE NOTIFICACIONES (TOASTS)
        =========================================================================
        Aquí se inyectan dinámicamente las notificaciones del sistema.
        El atributo aria-live="polite" asegura que lectores de pantalla
        anuncien las notificaciones sin interrumpir al usuario.
        ========================================================================= -->
    <div class="toast-container" id="toastContainer" aria-live="polite"></div>