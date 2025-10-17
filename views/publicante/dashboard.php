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
    <!-- Header del Dashboard con Título y Role Switcher -->
    <?php include __DIR__ . '/../bloques/bloque_titulo.php'; ?>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
    </div>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <div class="dashboard-section">
        <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>
    </div>

    <!-- CTA: Publicar Anuncio -->
    <?php include __DIR__ . '/../bloques/bloque_publicar_anuncio.php'; ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
