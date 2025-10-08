<?php
/**
 * Página de error 500 - Error interno del servidor
 * Integrada con el layout principal del sitio
 */
$pageTitle = "¡Uy! Algo salió mal";
include 'partials/header.php';
?>

<!-- CSS específico para páginas de error -->
<link rel="stylesheet" href="assets/css/error-pages.css<?= getCacheBuster('assets/css/error-pages.css'); ?>">

<!-- Error 500 Container -->
<div class="error-500-container">
    <div class="particles-bg">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="error-content-500">
        <div class="error-visual">
            <div class="error-code-500">500</div>
            <div class="error-icon-animated">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        
        <div class="error-info-500">
            <h1 class="error-title-500">¡Uy! Algo salió mal</h1>
            <p class="error-description-500">
                Nuestro servidor está experimentando dificultades técnicas temporales. 
                Nuestro equipo técnico ya está trabajando en solucionarlo.
            </p>
            
            <div class="error-details">
                <div class="detail-item">
                    <i class="fas fa-clock"></i>
                    <span>Tiempo estimado de solución: <strong>5-10 minutos</strong></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-tools"></i>
                    <span>Estado: <strong>En reparación</strong></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Tus datos están <strong>seguros</strong></span>
                </div>
            </div>
            
            <div class="error-suggestions-500">
                <h3>¿Qué puedes hacer mientras tanto?</h3>
                <ul>
                    <li><i class="fas fa-refresh"></i> Refrescar la página en unos minutos</li>
                    <li><i class="fas fa-home"></i> Volver a la <a href="index.php">página principal</a></li>
                    <li><i class="fas fa-phone"></i> <a href="index.php?view=contacto">Reportar el problema</a></li>
                    <li><i class="fas fa-coffee"></i> Tomar un café mientras solucionamos esto</li>
                </ul>
            </div>
            
            <div class="error-actions-500">
                <a href="javascript:location.reload()" class="btn btn-primary-500">
                    <i class="fas fa-refresh"></i> Intentar de nuevo
                </a>
                <a href="index.php" class="btn btn-secondary-500">
                    <i class="fas fa-home"></i> Ir al inicio
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>