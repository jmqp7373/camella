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

// Verificar estado de autenticación
$usuarioLogueado = false;
$usuarioActual = null;

if (session_status() === PHP_SESSION_ACTIVE) {
    require_once 'helpers/AuthHelper.php';
    $authHelper = new AuthHelper();
    
    if ($authHelper->estaAutenticado()) {
        $usuarioLogueado = true;
        $usuarioActual = $authHelper->obtenerUsuarioActual();
    }
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
                <?php if ($usuarioLogueado): ?>
                    <!-- Usuario autenticado -->
                    <div class="user-menu">
                        <span class="user-greeting">
                            Hola, <strong><?php echo htmlspecialchars($usuarioActual['nombre']); ?></strong>
                            <?php if ($usuarioActual['rol'] === 'admin'): ?>
                                <span class="role-badge admin">Admin</span>
                            <?php elseif ($usuarioActual['rol'] === 'promotor'): ?>
                                <span class="role-badge promotor">Promotor</span>
                            <?php endif; ?>
                        </span>
                        
                        <div class="user-actions">
                            <?php if ($usuarioActual['rol'] === 'admin'): ?>
                                <a href="index.php?view=admin" class="btn btn-admin" title="Panel de Administración">
                                    <i class="fas fa-cog"></i> Admin
                                </a>
                            <?php elseif ($usuarioActual['rol'] === 'promotor'): ?>
                                <a href="index.php?view=promotor" class="btn btn-promotor" title="Panel de Promotor">
                                    <i class="fas fa-bullhorn"></i> Promotor
                                </a>
                            <?php elseif ($usuarioActual['rol'] === 'publicante'): ?>
                                <a href="index.php?view=publicante" class="btn btn-publish" title="Panel de Publicante">
                                    <i class="fas fa-briefcase"></i> Mi Panel
                                </a>
                            <?php endif; ?>
                            
                            <a href="index.php?view=publicar-oferta" class="btn btn-publish">+ Publícate</a>
                            <a href="/logout" class="btn btn-logout" title="Cerrar Sesión">
                                <i class="fas fa-sign-out-alt"></i> Salir
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Usuario no autenticado -->
                    <a href="index.php?view=publicar-oferta" class="btn btn-publish">+ Publícate</a>
                    <a href="/login" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
