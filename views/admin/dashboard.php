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
    <!-- Header del Dashboard -->
    <div class="admin-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 40px; background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="text-align: left;">
            <h1 style="color: white; font-size: 2.5rem; margin-bottom: 0.5rem;">
                <i class="fas fa-tachometer-alt"></i>
                Panel de Administración
            </h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin: 0;">
                Gestión Completa del Sistema
            </p>
        </div>
        
        <!-- Role Switcher (Solo Admin) -->
        <?php include __DIR__ . '/../bloques/role_switcher.php'; ?>
    </div>

    <!-- BLOQUE ADMIN: Estadísticas de Twilio + Gestión del Sistema -->
    <?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción (visible para admin) -->
    <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>

    <!-- CTA: Crear Anuncio -->
    <section id="crear-anuncio" class="text-center border rounded p-5 bg-light shadow-sm my-4">
        <h4 class="fw-bold text-primary mb-3">
            <i class="fas fa-bullhorn me-2"></i>
            ¿Tienes un servicio para ofrecer?
        </h4>
        <p class="text-muted mb-4">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn btn-primary btn-lg px-4">
            <i class="fas fa-plus-circle me-2"></i> Crear mi anuncio ahora
        </a>
    </section>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
