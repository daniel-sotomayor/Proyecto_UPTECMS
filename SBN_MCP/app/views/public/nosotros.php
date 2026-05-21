<?php
/**
 * =============================================================================
 * VISTA: PÁGINA NOSOTROS — IDENTIDAD INSTITUCIONAL
 * =============================================================================
 * 
 * Página pública con información sobre la Maternidad Concepción Palacios,
 * su misión, visión y el equipo de Bienes Nacionales.
 * 
 * @var string $base_url URL base de la aplicación
 * =============================================================================
 */

require_once __DIR__ . '/../layout/header.php';
?>

<section class="hero" style="min-height: 40vh;">
    <div class="hero-content">
        <h1>Identidad Institucional</h1>
        <p class="hero-subtitle">Maternidad Concepción Palacios</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="grid-2 mb-2">
            <div class="modern-card">
                <div class="card-icon">🎯</div>
                <h2 style="margin-bottom: 1rem;">Nuestra Misión</h2>
                <p>Brindar atención médica integral, especializada y humanizada en el área materno-infantil, garantizando servicios de salud de calidad a la población venezolana, con personal altamente capacitado y tecnología de vanguardia.</p>
            </div>
            <div class="modern-card">
                <div class="card-icon">👁️</div>
                <h2 style="margin-bottom: 1rem;">Nuestra Visión</h2>
                <p>Ser la institución de referencia nacional e internacional en atención materno-infantil, reconocida por la excelencia de sus servicios, la formación de talento humano especializado y la gestión transparente de sus recursos.</p>
            </div>
        </div>

        <div class="modern-card mt-2" style="background: linear-gradient(to right, white, var(--bg-color));">
            <h2 style="color: var(--primary); margin-bottom: 1.5rem;">Reseña Histórica</h2>
            <p>Fundada en 1939, la Maternidad Concepción Palacios es el centro de atención obstétrica más grande e importante de Venezuela y uno de los pilares de la salud en América Latina.</p>
            <p>Hoy, apoyados en la innovación tecnológica, implementamos este <strong>Sistema de Gestión de Bienes Nacionales</strong> para modernizar la administración de nuestro patrimonio, asegurando que cada recurso esté donde más se necesite.</p>
        </div>
        
        <div class="text-center mt-2">
            <a href="<?= $base_url ?? '' ?>/" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>