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
    <!-- Header del Dashboard con Título y Role Switcher -->
    <?php include __DIR__ . '/../bloques/bloque_titulo.php'; ?>

    <!-- BLOQUE ADMIN: Estadísticas de Twilio + Gestión del Sistema -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>
    </div>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción (visible para admin) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
    </div>

    <!-- CTA: Publicar Anuncio -->
    <?php include __DIR__ . '/../bloques/bloque_publicar_anuncio.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
    </div>

    <!-- TARJETA: Gestión de Categorías y Oficios -->
    <div class="dashboard-section">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title mb-3">
                    <i class="fas fa-layer-group me-2"></i>Gestión de Categorías y Oficios
                </h5>
                <p class="card-text text-muted">
                    Administra las categorías, sus oficios y destaca los más populares 🔥
                </p>
                <a href="<?= app_url('views/admin/categoriasOficios.php') ?>" class="btn btn-primary">
                    <i class="fas fa-tools me-2"></i> Ir a gestión
                </a>
            </div>
        </div>
    </div>

    <!-- Botón de acceso rápido -->
    <div class="mt-4 text-center mb-5">
        <a href="<?= app_url('views/admin/categoriasOficios.php') ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-layer-group me-2"></i> Gestionar Categorías y Oficios
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
