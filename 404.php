<?php
/**
 * Página de error 404 - Página no encontrada
 * Integrada con el layout principal del sitio
 */
$pageTitle = "Página no encontrada";
include 'partials/header.php';
?>

<!-- CSS específico para páginas de error -->
<link rel="stylesheet" href="assets/css/error-pages.css<?= getCacheBuster('assets/css/error-pages.css'); ?>">

<!-- Error 404 Container -->
<div class="error-page-container">
    <div class="error-content">
        <div class="error-visual">
            <div class="error-code">404</div>
            <div class="error-icon">
                <i class="fas fa-search"></i>
            </div>
        </div>
        
        <div class="error-info">
            <h1 class="error-title">¡Página no encontrada!</h1>
            <p class="error-description">
                Lo sentimos, la página que estás buscando no existe o ha sido movida. 
                Puede que el enlace esté roto o que hayas escrito mal la dirección.
            </p>
            
            <div class="error-suggestions">
                <h3>¿Qué puedes hacer?</h3>
                <ul>
                    <li><i class="fas fa-home"></i> Volver a la <a href="index.php">página principal</a></li>
                    <li><i class="fas fa-search"></i> Buscar ofertas de empleo disponibles</li>
                    <li><i class="fas fa-phone"></i> <a href="index.php?view=contacto">Contactar soporte</a></li>
                </ul>
            </div>
            
            <div class="error-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al inicio
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver atrás
                </a>
            </div>
        </div>
    </div>
</div>



<?php include 'partials/footer.php'; ?>