<?php
/**
 * Bloque Reutilizable: CTA Publicar Anuncio
 * Call-to-Action para crear un nuevo anuncio
 * Se incluye al final de todos los dashboards
 */
?>

<!-- CTA: Crear Anuncio -->
<section id="crear-anuncio" class="text-center" style="margin-top: 2rem;">
    <h2 style="color: #003d7a; font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; justify-content: center;">
        <i class="fas fa-bullhorn"></i> ¿Tienes un servicio para ofrecer?
    </h2>
    
    <div class="bloque-wrapper" style="background-color: #f8ecec;">
        <p class="text-muted mb-4 fs-5" style="max-width: 700px; margin: 0 auto 1.5rem auto;">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn fw-bold text-white px-5 py-3" 
           style="background-color: #b90000; border-radius: 40px; font-size: 1.15rem;">
            <i class="fas fa-plus-circle me-2"></i> Publicar anuncio
        </a>
    </div>
</section>
