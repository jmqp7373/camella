<?php
/**
 * Función para generar cache buster para archivos CSS y JS
 * Usa la fecha de modificación del archivo si existe, o timestamp actual
 */
function getCacheBuster($filepath) {
    if (file_exists($filepath)) {
        return '?v=' . filemtime($filepath);
    }
    return '?v=' . time();
}

/**
 * Verificar si el usuario está logueado
 */
function isUserLoggedIn() {
    if (!isset($_SESSION)) {
        session_start();
    }
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Inicializar sesión si no está activa
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Camella.com.co - Portal de empleo líder en Colombia. Encuentra trabajo o talento profesional.">
    <meta name="keywords" content="empleo, trabajo, colombia, ofertas laborales, talentos, empresas, camella">
    <meta name="author" content="Camella.com.co">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Camella.com.co - Portal de Empleo</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- ========================================
         HOJAS DE ESTILO CON CACHE BUSTING
         Evita cacheo de archivos CSS locales
         ======================================== -->
    
    <!-- Paleta de Colores Oficial -->
    <link rel="stylesheet" href="assets/css/colors.css<?= getCacheBuster('assets/css/colors.css'); ?>">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="assets/css/style.css<?= getCacheBuster('assets/css/style.css'); ?>">
    
    <!-- CSS del Header -->
    <link rel="stylesheet" href="assets/css/header.css<?= getCacheBuster('assets/css/header.css'); ?>">
    
    <!-- Font Awesome para iconos (CDN externo - no necesita cache busting) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Meta tags para SEO y redes sociales -->
    <meta property="og:title" content="Camella.com.co - Portal de Empleo">
    <meta property="og:description" content="El portal de empleo líder en Colombia. Conectamos talentos con empresas.">
    <meta property="og:image" content="assets/images/logo/logo_horizontal.png">
    <meta property="og:url" content="https://camella.com.co">
    <meta property="og:type" content="website">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Camella.com.co - Portal de Empleo">
    <meta name="twitter:description" content="El portal de empleo líder en Colombia. Conectamos talentos con empresas.">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <a href="index.php" class="logo" aria-label="Ir a inicio">
                <img src="assets/images/logo/logo_horizontal.png" alt="Camella Logo">
            </a>
            <nav class="header-actions" aria-label="Acciones">
                <?php if (isUserLoggedIn()): ?>
                    <a href="index.php?view=publicar-oferta" class="btn btn-publish">+ Publícate</a>
                <?php else: ?>
                    <a href="index.php?view=login" class="btn btn-publish">+ Publícate</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
