<?php
/**
 * Página de error 403 - Acceso Denegado
 * Integrada con el layout principal del sitio
 */
$pageTitle = "Acceso Denegado";
include 'partials/header.php';
?>

<!-- CSS específico para páginas de error -->
<link rel="stylesheet" href="assets/css/error-pages.css<?= getCacheBuster('assets/css/error-pages.css'); ?>">

<!-- Error 403 Container -->
<div class="error-page-container">
    <div class="error-content">
        <div class="error-visual">
            <div class="error-code error-code-403">403</div>
            <div class="error-icon">
                <i class="fas fa-ban"></i>
            </div>
        </div>
        
        <div class="error-info">
            <h1 class="error-title">¡Acceso Denegado!</h1>
            <p class="error-description">
                No tienes los permisos necesarios para acceder a esta página o recurso. 
                Puede que necesites iniciar sesión o que esta área esté restringida.
            </p>
            
            <div class="error-suggestions">
                <h3>¿Qué puedes hacer?</h3>
                <ul>
                    <li><i class="fas fa-sign-in-alt"></i> <a href="index.php?view=login">Iniciar sesión</a> con tu cuenta</li>
                    <li><i class="fas fa-home"></i> Volver a la <a href="index.php">página principal</a></li>
                    <li><i class="fas fa-user-plus"></i> <a href="index.php?view=registro">Registrarse</a> si no tienes cuenta</li>
                    <li><i class="fas fa-phone"></i> <a href="index.php?view=contacto">Contactar soporte</a> si necesitas ayuda</li>
                </ul>
            </div>
            
            <div class="error-actions">
                <a href="index.php?view=login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Ir al inicio
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>