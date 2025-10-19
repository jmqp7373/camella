<?php
/**
 * Bloque Reutilizable: CTA Publicar Anuncio
 * Call-to-Action para crear un nuevo anuncio
 * Se incluye al final de todos los dashboards
 */
?>

<!-- CTA: Crear Anuncio -->
<section class="text-center" style="margin-top: 2rem;">
    <div id="crear-anuncio" class="bloque-wrapper bloque-cta-publicar">
        <h3 class="fw-bold text-danger mb-4" style="font-size: 1.6rem;">
            <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
        </h3>
        <p class="text-muted mb-4 fs-5" style="max-width: 700px; margin: 0 auto 1.5rem auto;">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn btn-publicar-pulso fw-bold text-white px-5 py-3" 
           style="background-color: #b90000; border-radius: 40px; font-size: 1.15rem;">
            <i class="fas fa-plus-circle me-2"></i> Publicar anuncio
        </a>
    </div>
</section>

<style>
  /* Animación de pulso para botón Publicar anuncio */
  .btn-publicar-pulso {
    animation: pulsoPublicar 1.2s infinite;
  }

  @keyframes pulsoPublicar {
    0%, 100% {
      transform: scale(1);
      background-color: #b90000 !important;
      box-shadow: 0 4px 15px rgba(185, 0, 0, 0.3);
    }
    50% {
      transform: scale(1.05);
      background-color: #d10000 !important;
      box-shadow: 0 6px 20px rgba(185, 0, 0, 0.5);
    }
  }
</style>