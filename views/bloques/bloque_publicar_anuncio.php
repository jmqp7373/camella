<?php
/**
 * Bloque Reutilizable: CTA Publicar Anuncio
 * Call-to-Action para crear un nuevo anuncio
 * Se incluye al final de todos los dashboards
 */
?>

<!-- CTA: Crear Anuncio -->
<section class="text-center" style="margin-top: 2rem;">
    <div id="crear-anuncio" class="bloque-wrapper bloque-cta-publicar bloque-parpadeo">
        <h3 class="fw-bold text-danger mb-4" style="font-size: 1.6rem;">
            <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
        </h3>
        <p class="text-muted mb-4 fs-5" style="max-width: 700px; margin: 0 auto 1.5rem auto;">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn btn-publicar-parpadeo fw-bold text-white px-5 py-3" 
           style="background-color: #b90000; border-radius: 40px; font-size: 1.15rem;">
            <i class="fas fa-plus-circle me-2"></i> Publicar anuncio
        </a>
    </div>
</section>

<style>
  /* Parpadeo del bloque completo - 6 ciclos */
  .bloque-parpadeo {
    animation: fondoParpadeo 1.5s ease-in-out 6;
  }

  /* Parpadeo sincronizado del botón - 6 ciclos */
  .btn-publicar-parpadeo {
    animation: botonParpadeo 1.5s ease-in-out 6;
  }

  @keyframes fondoParpadeo {
    0%, 100% {
      background-color: #f8ecec; /* color original del bloque */
    }
    50% {
      background-color: #ffe0e0; /* tono más claro para el parpadeo */
    }
  }

  @keyframes botonParpadeo {
    0%, 100% {
      transform: scale(1);
      background-color: #b90000 !important;
      box-shadow: 0 4px 15px rgba(185, 0, 0, 0.3);
    }
    50% {
      transform: scale(1.08);
      background-color: #ff3b3b !important;
      box-shadow: 0 6px 20px rgba(255, 59, 59, 0.6);
    }
  }
</style>