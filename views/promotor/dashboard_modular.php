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
    <!-- Header del Dashboard -->
    <div class="promotor-header" style="background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro)); color: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px; box-shadow: var(--shadow-card); text-align: center;">
        <h1 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 600;">
            <i class="fas fa-bullhorn"></i>
            Panel de Promotor
        </h1>
        <p style="margin: 0; opacity: 0.9; font-size: 16px;">
            Bienvenido, <?= htmlspecialchars($_SESSION['phone'] ?? '') ?>
        </p>
    </div>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción -->
    <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para promotores también) -->
    <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
