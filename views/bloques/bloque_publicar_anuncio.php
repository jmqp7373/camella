<?php
/**
 * Bloque Reutilizable: CTA Publicar Anuncio
 * Call-to-Action para crear un nuevo anuncio
 * Se incluye al final de todos los dashboards
 */
?>

<!-- CTA: Crear Anuncio -->
<section id="crear-anuncio" class="text-center my-5 border rounded-4 shadow-sm bg-white position-relative mb-5" style="background-color: #fff8f8; padding: 20px 0px 20px 0px;">
    <div class="container">
        <h3 class="fw-bold text-danger mb-4" style="font-size: 1.6rem;">
            <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
        </h3>
        <p class="text-muted mb-4 fs-5" style="max-width: 700px; margin: 0 auto;">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn fw-bold text-white px-5 py-3 mt-2" 
           style="background-color: #b90000; border-radius: 40px; font-size: 1.15rem;">
            + Publicar anuncio
        </a>
    </div>
</section>
