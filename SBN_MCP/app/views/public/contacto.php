<?php
/**
 * =============================================================================
 * VISTA: PÁGINA DE CONTACTO
 * =============================================================================
 * 
 * Página pública con información de contacto del departamento de
 * Bienes Nacionales y soporte técnico del sistema.
 * 
 * @var string $base_url URL base de la aplicación
 * =============================================================================
 */

require_once __DIR__ . '/../layout/header.php';
?>

<section class="hero" style="min-height: 40vh;">
    <div class="hero-content">
        <h1>Centro de Contacto</h1>
        <p class="hero-subtitle">Soporte y Departamento de Bienes</p>
    </div>
</section>

<section class="section-padding">
    <div class="container">
        <div class="grid-2">
            <div class="modern-card">
                <h2 style="margin-bottom: 2rem;">Información Institucional</h2>
                
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; gap: 1rem;">
                        <div class="card-icon" style="width: 45px; height: 45px; font-size: 1.2rem; margin: 0;">📍</div>
                        <div>
                            <strong>Dirección Física</strong>
                            <p style="font-size: 0.9rem; margin: 0;">Av. San Martín c/ Av. J.A. Lamas<br>Caracas 1020, Venezuela</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div class="card-icon" style="width: 45px; height: 45px; font-size: 1.2rem; margin: 0;">📞</div>
                        <div>
                            <strong>Central Telefónica</strong>
                            <p style="font-size: 0.9rem; margin: 0;">(0212) 451-0011 / 451-0022</p>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem;">
                        <div class="card-icon" style="width: 45px; height: 45px; font-size: 1.2rem; margin: 0;">✉️</div>
                        <div>
                            <strong>Correo Oficial</strong>
                            <p style="font-size: 0.9rem; margin: 0;">bienes@mcp.gob.ve</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-card">
                <h2 style="margin-bottom: 1.5rem;">Envíenos un Requerimiento</h2>
                
                <form id="form-contacto">
                    <div class="form-group">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Detalle del Mensaje</label>
                        <textarea name="mensaje" class="form-control" rows="4" placeholder="¿En qué podemos ayudarle?" required style="resize: vertical;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('form-contacto').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button');
    btn.innerHTML = 'Enviando...'; btn.disabled = true;
    
    // Simulación de envío fluido
    setTimeout(() => {
        if(window.Toast) Toast.success('Mensaje enviado correctamente. El equipo de Bienes Nacionales le responderá pronto.');
        this.reset();
        btn.innerHTML = 'Enviar Mensaje'; btn.disabled = false;
    }, 1500);
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>