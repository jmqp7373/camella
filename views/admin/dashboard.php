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

    <!-- BLOQUE GESTIÓN: Categorías y Oficios (solo admin) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_gestion_categorias.php'; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
