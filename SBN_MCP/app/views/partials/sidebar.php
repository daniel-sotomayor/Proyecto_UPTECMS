<?php
/**
 * Partial: Sidebar de navegación del sistema interno.
 *
 * @var string $base_url URL base de la aplicación.
 */

$rol = $_SESSION['rol'] ?? '';

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$ruta = $base ? str_replace($base, '', $uri) : $uri;
$ruta = strtok($ruta, '?') ?: '/';

if (!function_exists('navActivo')) {
    function navActivo(string $prefijo, string $ruta): string
    {
        return str_starts_with($ruta, $prefijo) ? 'active' : '';
    }
}

$rolLabel = match ($rol) {
    'administrador'          => 'Administrador',
    'gerencia_bn'            => 'Gerente BN',
    'controlador_inventario' => 'Control Inventario',
    'registrador'            => 'Registrador',
    default                  => ucfirst($rol),
};

$inicial = strtoupper(mb_substr($_SESSION['nombre'] ?? 'U', 0, 1, 'UTF-8'));
?>
<aside class="sidebar" id="appSidebar" role="navigation" aria-label="Menú principal">

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon" aria-hidden="true">BN</div>
            <div class="logo-text">
                <span class="logo-title">Bienes Nacionales</span>
                <span class="logo-sub">Maternidad Concepción P.</span>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Navegación principal">

        <a href="<?= $base_url ?>/dashboard"
           class="nav-item <?= navActivo('/dashboard', $ruta) ?>"
           aria-current="<?= navActivo('/dashboard', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>

        <a href="<?= $base_url ?>/bienes"
           class="nav-item <?= navActivo('/bienes', $ruta) ?>"
           aria-current="<?= navActivo('/bienes', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            Inventario
        </a>
        
        <a href="<?= $base_url ?>/inventario-mural"
           class="nav-item <?= navActivo('/inventario-mural', $ruta) ?>"
           aria-current="<?= navActivo('/inventario-mural', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16v16H4V4z"></path>
                <path d="M8 4v16"></path>
                <path d="M16 4v16"></path>
                <path d="M4 8h16"></path>
                <path d="M4 16h16"></path>
            </svg>
            Control Mural
        </a>

        <a href="<?= $base_url ?>/movimientos"
           class="nav-item <?= navActivo('/movimientos', $ruta) ?>"
           aria-current="<?= navActivo('/movimientos', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 3 21 3 21 8"></polyline><line x1="4" y1="20" x2="21" y2="3"></line>
                <polyline points="21 16 21 21 16 21"></polyline><line x1="15" y1="15" x2="21" y2="21"></line>
                <line x1="4" y1="4" x2="9" y2="9"></line>
            </svg>
            Movimientos
        </a>

        <?php if (in_array($rol, ['administrador', 'gerencia_bn', 'controlador_inventario'], true)): ?>
        <a href="<?= $base_url ?>/reportes"
           class="nav-item <?= navActivo('/reportes', $ruta) ?>"
           aria-current="<?= navActivo('/reportes', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Reportes
        </a>

        <a href="<?= $base_url ?>/auditoria"
           class="nav-item <?= navActivo('/auditoria', $ruta) ?>"
           aria-current="<?= navActivo('/auditoria', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Auditoría
        </a>

        <a href="<?= $base_url ?>/mantenimientos"
           class="nav-item <?= navActivo('/mantenimientos', $ruta) ?>"
           aria-current="<?= navActivo('/mantenimientos', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
            Mantenimientos
        </a>
        <?php endif; ?>

        <?php if ($rol === 'administrador'): ?>
        <div class="nav-divider" style="height:1px;background:rgba(255,255,255,0.05);margin:1rem;"></div>

        <a href="<?= $base_url ?>/usuarios"
           class="nav-item <?= navActivo('/usuarios', $ruta) ?>"
           aria-current="<?= navActivo('/usuarios', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Usuarios
        </a>

        <a href="<?= $base_url ?>/configuracion"
           class="nav-item <?= navActivo('/configuracion', $ruta) ?>"
           aria-current="<?= navActivo('/configuracion', $ruta) === 'active' ? 'page' : 'false' ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            </svg>
            Configuración
        </a>
        <?php endif; ?>

    </nav>

    <div class="sidebar-user" aria-label="Usuario actual">
        <div class="user-avatar" aria-hidden="true"><?= $inicial ?></div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
            <span class="user-role"><?= htmlspecialchars($rolLabel) ?></span>
        </div>
    </div>

    <div class="sidebar-footer">
        <form action="<?= $base_url ?>/logout" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="nav-item logout-btn" aria-label="Cerrar sesión">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Cerrar Sesión
            </button>
        </form>
    </div>

</aside>
