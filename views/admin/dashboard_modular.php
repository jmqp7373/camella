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
    <div class="admin-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: var(--color-azul); font-size: 2.5rem; margin-bottom: 0.5rem;">
            <i class="fas fa-tachometer-alt"></i>
            Panel de Administración
        </h1>
        <p style="color: var(--color-gris-oscuro); font-size: 1.1rem;">
            Gestión Completa del Sistema
        </p>
    </div>

    <!-- BLOQUE ADMIN: Estadísticas de Twilio + Gestión del Sistema -->
    <?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción (visible para admin) -->
    <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
