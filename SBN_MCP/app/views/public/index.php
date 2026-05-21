<?php
/**
 * =============================================================================
 * VISTA: LANDING PAGE — PÁGINA DE INICIO PÚBLICA
 * =============================================================================
 * 
 * Página principal del sistema accesible sin autenticación.
 * Presenta el Sistema de Gestión de Bienes Nacionales de la
 * Maternidad Concepción Palacios.
 * 
 * Secciones:
 * - Hero: Presentación del sistema con llamado a login
 * - Estadísticas: Resumen de bienes registrados (si hay datos públicos)
 * - Características: Funcionalidades principales del sistema
 * - Contacto: Información de soporte
 * 
 * @var string $base_url URL base de la aplicación
 * @var array  $stats    Estadísticas públicas (opcional)
 * =============================================================================
 */

require_once __DIR__ . '/../layout/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <div style="margin-bottom: 2rem;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--accent)">
                <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16M15 3v18M9 3v18"/>
            </svg>
        </div>
        <h1>Gestión de Bienes Nacionales</h1>
        <p class="hero-subtitle">Maternidad Concepción Palacios</p>
        <p style="font-size: 1.1rem; color: #e2e8f0; margin-bottom: 2.5rem; line-height: 1.8;">
            Sistema integral para el control, registro y trazabilidad del patrimonio hospitalario. Garantizamos transparencia y eficiencia tecnológica.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= $base_url ?? '' ?>/login" class="btn btn-primary">
                Acceder al Sistema
            </a>
            <a href="#servicios" class="btn btn-secondary">Conocer Más</a>
        </div>
    </div>
</section>

<section id="servicios" class="section-padding">
    <div class="container">
        <h2 class="section-title">Capacidades del Sistema</h2>
        <p class="section-subtitle">Diseñado bajo los estándares de Métrica V3 y normativas nacionales.</p>
        
        <div class="grid-4">
            <div class="modern-card">
                <div class="card-icon">📝</div>
                <h3>Registro Codificado</h3>
                <p>Incorporación de bienes con codificación SUDEBIP, Publicación 9 y MinSalud.</p>
            </div>
            <div class="modern-card">
                <div class="card-icon">🔍</div>
                <h3>Trazabilidad Real</h3>
                <p>Monitoreo del ciclo de vida: nuevo, en uso, reparación o desincorporado.</p>
            </div>
            <div class="modern-card">
                <div class="card-icon">📊</div>
                <h3>Reportes BM</h3>
                <p>Generación automatizada de reportes BM-1 a BM-4 listos para auditoría.</p>
            </div>
            <div class="modern-card">
                <div class="card-icon">⚖️</div>
                <h3>Marco Legal</h3>
                <p>Estricto cumplimiento de la Ley Orgánica de Bienes Públicos (LOBIP).</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding" style="background: white;">
    <div class="container text-center">
        <h2 style="margin-bottom: 1rem;">¿Requiere soporte o asistencia?</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">El equipo de infraestructura está a su disposición.</p>
        <a href="<?= $base_url ?? '' ?>/contacto" class="btn btn-primary">Contactar Soporte</a>
    </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>