<?php 
/**
 * Panel de Promotor - Dashboard Modular
 * Vista con bloques heredados
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'promotor') {
    header('Location: ../../index.php');
    exit;
}

// Cargar configuración de rutas
require_once __DIR__ . '/../../config/app_paths.php';

$pageTitle = "Panel de Promotor";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="dashboard-container">
    <!-- Título Principal del Panel (solo título, sin CTA) -->
    <?php 
    $mostrarSoloTitulo = true;
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

    <!-- Header del Dashboard con Role Switcher -->
    <div class="promotor-header dashboard-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); color: white; padding: 30px; border-radius: var(--border-radius); box-shadow: var(--shadow-card);">
        <div style="text-align: left;">
            <h2 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 600;">
                <i class="fas fa-bullhorn"></i>
                Herramientas de Promoción
            </h2>
            <p style="margin: 0; opacity: 0.9; font-size: 16px;">
                <span class="role-label" style="color: rgba(255,255,255,0.9);">
                    <i class="fas fa-user-circle me-1"></i>
                    <?= ucfirst($_SESSION['role'] ?? 'Promotor') ?>
                </span>
            </p>
        </div>
        
        <!-- Role Switcher (Solo Admin) -->
        <?php include __DIR__ . '/../bloques/role_switcher.php'; ?>
    </div>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para promotores también) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
    </div>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>
    </div>

    <!-- CTA: Crear Anuncio (desde bloque reutilizable) -->
    <div class="dashboard-section-large">
        <?php 
        unset($mostrarSoloTitulo);
        include __DIR__ . '/../bloques/bloque_titulo.php'; 
        ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
