<?php 
/**
 * Panel de Publicante - Dashboard Modular
 * Vista con bloque básico
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'publicante') {
    header('Location: ../../index.php');
    exit;
}

// Cargar configuración de rutas
require_once __DIR__ . '/../../config/app_paths.php';

$pageTitle = "Mi Panel";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="dashboard-container">
    <!-- Título Principal del Panel (solo título, sin CTA) -->
    <?php 
    $mostrarSoloTitulo = true;
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

    <!-- Header del Dashboard con Role Switcher -->
    <div class="publicante-header dashboard-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-verde), #27ae60); color: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--shadow-card);">
        <div style="text-align: left;">
            <h2 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 600;">
                <i class="fas fa-user-circle"></i>
                Mis Anuncios
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 16px;">
                <span class="role-label" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-user-circle me-1"></i>
                    <?= ucfirst($_SESSION['role'] ?? 'Publicante') ?>
                </span>
            </p>
        </div>
        
        <!-- Role Switcher (Solo Admin) -->
        <?php include __DIR__ . '/../bloques/role_switcher.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
    </div>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>
    </div>

    <!-- CTA: Crear Anuncio -->
    <section id="crear-anuncio" class="text-center my-5 p-5 border rounded-4 shadow-sm bg-white position-relative mb-5" style="background-color: #fff8f8;">
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

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
