<?php 
/**
 * Panel de Administración - Dashboard Modular
 * Vista principal con bloques heredados
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Cargar configuración de rutas
require_once __DIR__ . '/../../config/app_paths.php';

// Cargar estadísticas de Twilio
require_once __DIR__ . '/../../controllers/TwilioStatsHelper.php';
$twilioStats = getTwilioStatistics();

$pageTitle = "Panel de Administración";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="dashboard-container">
    <!-- Título Principal del Panel (solo título, sin CTA) -->
    <?php 
    $mostrarSoloTitulo = true;
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

    <!-- Header del Dashboard con Role Switcher -->
    <div class="admin-header dashboard-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="text-align: left;">
            <h2 style="color: white; font-size: 1.8rem; margin-bottom: 0.5rem;">
                <i class="fas fa-chart-line"></i>
                Estadísticas y Control
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1rem; margin: 0;">
                <span class="role-label" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-user-circle me-1"></i>
                    <?= ucfirst($_SESSION['role'] ?? 'Admin') ?>
                </span>
            </p>
        </div>
        
        <!-- Role Switcher (Solo Admin) -->
        <?php include __DIR__ . '/../bloques/role_switcher.php'; ?>
    </div>

    <!-- BLOQUE ADMIN: Estadísticas de Twilio + Gestión del Sistema -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>
    </div>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción (visible para admin) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para todos) -->
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
