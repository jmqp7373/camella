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

// Subtítulos dinámicos según el rol
$subtitulos = [
    'admin' => 'Estadísticas y Control',
    'promotor' => 'Herramientas de Promoción',
    'publicante' => 'Mis Anuncios'
];
$subtitulo = $subtitulos[$rol] ?? 'Gestión completa del sistema';

// Iconos por rol
$iconos = [
    'admin' => 'fa-chart-line',
    'promotor' => 'fa-bullhorn',
    'publicante' => 'fa-user-circle'
];
$icono = $iconos[$rol] ?? 'fa-tachometer-alt';
?>

<!-- Header del Dashboard con Título y Role Switcher -->
<div class="dashboard-header dashboard-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
    <div style="text-align: left;">
        <h1 class="fw-bold mb-2" style="color: white; font-size: 1.8rem; margin-bottom: 0.5rem;">
            <i class="fas <?= $icono ?> me-2"></i>
            <?= $titulo ?>
        </h1>
        <p style="color: rgba(255,255,255,0.9); font-size: 1rem; margin: 0;">
            <span class="role-label" style="color: rgba(255,255,255,0.9);">
                <i class="fas fa-user-circle me-1"></i>
                <?= ucfirst($_SESSION['role'] ?? 'Usuario') ?>
            </span>
            <span style="margin: 0 0.5rem;">•</span>
            <span><?= $subtitulo ?></span>
        </p>
    </div>
    
    <!-- Role Switcher (Solo visible para Admin) -->
    <?php include __DIR__ . '/role_switcher.php'; ?>
</div>

<!-- CTA: Crear Anuncio (se muestra al final del dashboard) -->
<!-- Este bloque debe incluirse al final, después de todos los otros bloques -->
<?php if (!isset($mostrarSoloTitulo) || !$mostrarSoloTitulo): ?>
<section id="crear-anuncio" class="text-center my-5 p-5 border rounded-4 shadow-sm mb-5" style="background-color: #fff8f8;">
    <h3 class="fw-bold text-danger mb-3">
        <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
    </h3>
    <p class="text-muted mb-4 fs-5">
        Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
    </p>
    <a href="<?= app_url('views/bloques/publicar.php') ?>" 
       class="btn px-5 py-3 fw-bold text-white"
       style="background-color: #b90000; border-radius: 30px; font-size: 1.1rem;">
        + Publicar anuncio
    </a>
</section>
<?php endif; ?>
