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
    <!-- Header del Dashboard con Título y Role Switcher -->
    <?php include __DIR__ . '/../bloques/bloque_titulo.php'; ?>

    <!-- BLOQUE PROMOTOR: Herramientas de Promoción -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
    </div>

    <!-- CTA: Publicar Anuncio -->
    <?php include __DIR__ . '/../bloques/bloque_publicar_anuncio.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios (visible para promotores también) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
