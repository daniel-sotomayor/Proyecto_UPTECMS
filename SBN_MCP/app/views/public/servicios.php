<?php
/**
 * =============================================================================
 * VISTA: PÁGINA SERVICIOS — ATENCIÓN MATERNO-INFANTIL
 * =============================================================================
 * 
 * Página pública que presenta los servicios especializados ofrecidos por
 * la Maternidad Concepción Palacios. No requiere autenticación.
 * 
 * @var string $base_url URL base de la aplicación
 * =============================================================================
 */

require_once __DIR__ . '/../layout/header.php';
?>

<section class="hero" style="min-height: 40vh;">
    <div class="hero-content">
        <h1>Servicios Especializados</h1>
        <p class="hero-subtitle">Atención Materno-Infantil Integral</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="text-center mb-2">
            <h2 class="section-title">Áreas de Atención</h2>
            <p class="section-subtitle">Nuestros bienes e infraestructura están destinados a soportar las siguientes áreas vitales.</p>
        </div>

        <div class="grid-3">
            <?php
            $servicios = [
                ['icono'=>'🤱','titulo'=>'Obstetricia','desc'=>'Control prenatal, partos normales y de alto riesgo.'],
                ['icono'=>'👶','titulo'=>'Neonatología','desc'=>'Cuidados intensivos y atención al recién nacido.'],
                ['icono'=>'🏥','titulo'=>'Ginecología','desc'=>'Diagnóstico, cirugía y tratamientos especializados.'],
                ['icono'=>'🔬','titulo'=>'Materno-Fetal','desc'=>'Medicina fetal, ecosonografía de alta resolución.'],
                ['icono'=>'🚑','titulo'=>'Emergencias','desc'=>'Atención obstétrica activa las 24 horas del día.'],
                ['icono'=>'🎓','titulo'=>'Investigación','desc'=>'Formación de residentes y postgrados médicos.']
            ];
            foreach ($servicios as $s):
            ?>
            <div class="modern-card" style="text-align: center; align-items: center;">
                <div class="card-icon" style="background: transparent; font-size: 3rem;"><?= $s['icono'] ?></div>
                <h3 style="margin-bottom: 0.5rem;"><?= $s['titulo'] ?></h3>
                <p style="font-size: 0.9rem; color: var(--text-muted);"><?= $s['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>