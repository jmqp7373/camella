<?php
/**
 * Vista: Publicar Anuncio - Página Completa
 * Incluye header y footer para acceso directo desde navegador
 */

// Cargar configuración de rutas
require_once __DIR__ . '/../config/app_paths.php';

$pageTitle = "Publicar Anuncio";
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container py-5">
    <?php include_once __DIR__ . '/bloques/publicar.php'; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
