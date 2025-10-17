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
    <div class="admin-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 40px; background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); padding: 2rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="text-align: left;">
            <h2 style="color: white; font-size: 1.8rem; margin-bottom: 0.5rem;">
                <i class="fas fa-chart-line"></i>
                Estadísticas y Control
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1rem; margin: 0;">
                Monitoreo del Sistema
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

    <!-- CTA: Crear Anuncio (desde bloque reutilizable) -->
    <?php 
    unset($mostrarSoloTitulo);
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
