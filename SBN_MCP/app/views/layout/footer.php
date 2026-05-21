<?php
/**
 * =============================================================================
 * LAYOUT PRINCIPAL: FOOTER
 * =============================================================================
 * 
 * Este archivo cierra la estructura HTML iniciada en header.php.
 * Contiene:
 * - Cierre de contenedores principales según el tipo de página
 * - Footer público (solo para páginas no autenticadas)
 * - Sistema de notificaciones Toast (JavaScript)
 * - Gestión del tema claro/oscuro
 * - Animaciones de entrada al hacer scroll
 * - Cierre de tags HTML (body, html)
 * 
 * Variables esperadas:
 * @var bool $is_app Indica si es página interna del sistema
 * =============================================================================
 */
?>

    <?php if(isset($is_app)): ?>
        <!-- =====================================================================
            CIERRE DEL LAYOUT DE APLICACIÓN
            =====================================================================
            Se cierra .main-content (abierto en sidebar.php) y .app-layout
            (abierto en header.php). El sidebar se incluye antes del main-content.
            ===================================================================== -->
            </div> <!-- .main-content -->
        </div> <!-- .app-layout -->
    <?php else: ?>
        <!-- =====================================================================
            FOOTER PÚBLICO
            =====================================================================
            Se muestra solo en páginas públicas (landing, login, etc.)
            No aparece en el sistema interno (is_app)
            ===================================================================== -->
        </main>
        <footer class="public-footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-info">
                        <h3>Maternidad Concepción Palacios</h3>
                        <p>Sistema de Gestión de Bienes Nacionales. Control y transparencia para nuestra institución.</p>
                    </div>
                    <div class="footer-copy">
                        <p>&copy; <?= date('Y') ?> MCP. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </footer>
    <?php endif; ?>

    <!-- =========================================================================
        SCRIPTS JAVASCRIPT GLOBALES
        =========================================================================
        Todo el JavaScript se ejecuta en modo estricto ('use strict') para
        evitar errores comunes y mejorar el rendimiento.
        ========================================================================= -->
    <script>
    'use strict';

    /**
     * =============================================================================
     * SISTEMA DE NOTIFICACIONES TOAST
     * =============================================================================
     * 
     * Muestra notificaciones temporales en la esquina superior derecha.
     * Tipos disponibles: success, error, warning, info
     * 
     * Uso:
     *   Toast.success('Operación completada');
     *   Toast.error('Ha ocurrido un error');
     *   Toast.warning('Advertencia importante');
     *   Toast.info('Información relevante');
     * 
     * Las notificaciones desaparecen automáticamente después de 4 segundos.
     * =============================================================================
     */
    const Toast = (() => {
        // Contenedor donde se inyectarán las notificaciones
        const container = document.getElementById('toastContainer');
        
        /**
         * Muestra una notificación toast
         * @param {string} message - Texto a mostrar
         * @param {string} type - Tipo de notificación (success, error, warning, info)
         */
        function show(message, type = 'info') {
            if (!container) return;
            
            // Crear elemento toast
            const t = document.createElement('div');
            t.className = `toast toast-${type} slide-in-right`;
            
            // Iconos SVG para cada tipo de notificación
            const icons = {
                success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
                error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
            };

            // HTML interno del toast
            t.innerHTML = `
                <div class="toast-icon">${icons[type] || icons.info}</div>
                <div class="toast-body">${message}</div>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;

            // Añadir al contenedor
            container.appendChild(t);
            
            // Auto-eliminar después de 4 segundos con animación de salida
            setTimeout(() => {
                t.classList.replace('slide-in-right', 'fade-out');
                setTimeout(() => t.remove(), 500);
            }, 4000);
        }

        // API pública del sistema de Toast
        return {
            show,
            success: m => show(m, 'success'),
            error: m => show(m, 'error'),
            warning: m => show(m, 'warning'),
            info: m => show(m, 'info')
        };
    })();
    // Exponer globalmente para uso en otras partes del sistema
    window.Toast = Toast;

    /**
     * =============================================================================
     * INICIALIZACIÓN DE UI AL CARGAR EL DOM
     * =============================================================================
     * 
     * Se ejecuta cuando el DOM está completamente cargado.
     * Configura:
     * - Toggle del sidebar en móvil
     * - Cambio de tema claro/oscuro
     * - Animaciones de entrada al hacer scroll
     * =============================================================================
     */
    document.addEventListener('DOMContentLoaded', () => {
        // Elementos del DOM para manipulación
        const sidebar = document.getElementById('appSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const btnToggle = document.getElementById('hamburgerBtn');
        const themeToggle = document.getElementById('themeToggle');
        
        // -------------------------------------------------------------------------
        // CONTROL DEL SIDEBAR (MÓVIL)
        // -------------------------------------------------------------------------
        // Abrir/cerrar sidebar al hacer clic en el botón hamburguesa
        if (btnToggle && sidebar) {
            btnToggle.addEventListener('click', () => {
                sidebar.classList.toggle('mobile-open');
                if (overlay) overlay.classList.toggle('visible');
            });
        }

        // Cerrar sidebar al hacer clic en el overlay (fuera del sidebar)
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('visible');
            });
        }

        // -------------------------------------------------------------------------
        // CONTROL DEL TEMA CLARO/OSCURO
        // -------------------------------------------------------------------------
        if (themeToggle) {
            // Iconos de sol y luna dentro del botón
            const sunIcon = themeToggle.querySelector('.sun-icon');
            const moonIcon = themeToggle.querySelector('.moon-icon');

            /**
             * Actualiza la visibilidad de los iconos según el tema actual
             * @param {string} theme - 'dark' o 'light'
             */
            const updateIcons = (theme) => {
                if (theme === 'dark') {
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                }
            };

            // Establecer iconos según tema guardado
            const currentTheme = document.documentElement.getAttribute('data-theme');
            updateIcons(currentTheme);

            // Evento de clic para cambiar tema
            themeToggle.addEventListener('click', () => {
                const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateIcons(newTheme);
                
                // Notificar cambio al usuario
                Toast.info(`Tema ${newTheme === 'dark' ? 'oscuro' : 'claro'} activado`);
            });
        }

        /**
         * =============================================================================
         * ANIMACIONES DE ENTRADA AL HACER SCROLL
         * =============================================================================
         * 
         * Observa elementos con clase .card, .metric-card, y filas de tabla
         * Les añade la clase 'animate-in' cuando entran en el viewport.
         * 
         * Esto crea un efecto de "aparición progresiva" al hacer scroll.
         * =============================================================================
         */
        const observerOptions = { threshold: 0.1 };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target); // Solo animar una vez
                }
            });
        }, observerOptions);

        // Observar todos los elementos que deben animarse
        document.querySelectorAll('.card, .metric-card, .data-table tr').forEach(el => {
            observer.observe(el);
        });
    });
    </script>
</body>
</html>