<?php
/**
 * Bloque Reutilizable: Título del Panel y CTA Publicar Anuncio
 * Se incluye en todos los dashboards para mantener consistencia
 */

// Título dinámico según el rol del usuario
$titulos = [
    'admin' => 'Panel de Administración',
    'promotor' => 'Panel del Promotor',
    'publicante' => 'Mi Panel de Publicaciones'
];
$rol = $_SESSION['role'] ?? 'admin';
$titulo = $titulos[$rol] ?? 'Panel Principal';
?>

<!-- Título Principal del Dashboard -->
<section class="mb-4 text-center">
    <h1 class="fw-bold text-primary">
        <i class="fas fa-tachometer-alt me-2"></i> <?= $titulo ?>
    </h1>
    <p class="text-muted">Gestión completa del sistema y tus publicaciones</p>
</section>

<!-- CTA: Crear Anuncio (se muestra al final del dashboard) -->
<!-- Este bloque debe incluirse al final, después de todos los otros bloques -->
<?php if (!isset($mostrarSoloTitulo) || !$mostrarSoloTitulo): ?>
<section id="crear-anuncio" class="text-center my-5 p-5 border rounded-4 shadow-sm" style="background-color: #fff8f8;">
    <h3 class="fw-bold text-danger mb-3">
        <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
    </h3>
    <p class="text-muted mb-4 fs-5">
        Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
    </p>
    <a href="<?= app_url('views/bloques/publicar.php') ?>" 
       class="btn px-4 py-2 fw-bold text-white"
       style="background-color: #b90000; border-radius: 25px;">
        + Publicar anuncio
    </a>
</section>
<?php endif; ?>
