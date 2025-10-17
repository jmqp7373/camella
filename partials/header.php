<?php
/**
 * Cargar configuración de rutas de la aplicación
 */
require_once __DIR__ . '/../config/app_paths.php';

/**
 * Función para generar cache buster para archivos CSS y JS
 * Usa la fecha de modificación del archivo si existe, o timestamp actual
 * VERSIÓN MANUAL: Incrementar para forzar recarga inmediata en todos los usuarios
 */
function getCacheBuster($filepath) {
    $manualVersion = '2.1'; // Incrementar este número para forzar recarga global
    $fullPath = APP_ROOT . '/' . ltrim($filepath, '/');
    if (file_exists($fullPath)) {
        return '?v=' . $manualVersion . '.' . filemtime($fullPath);
    }
    return '?v=' . $manualVersion . '.' . time();
}

// Inicializar sesión si no está activa
if (!isset($_SESSION)) {
    session_start();
}

// Detectar si estamos en un dashboard
$archivoActual = basename($_SERVER['PHP_SELF']);
$esDashboard = (strpos($archivoActual, 'dashboard') !== false);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Camella.com.co - Portal de empleo líder en Colombia. Encuentra trabajo o talento profesional.">
    <meta name="keywords" content="empleo, trabajo, colombia, ofertas laborales, talentos, empresas, camella">
    <meta name="author" content="Camella.com.co">
    
    <!-- Base href para rutas relativas -->
    <base href="<?= htmlspecialchars(APP_SUBDIR) ?>/">
    
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
    
    <!-- CSS de Bloques para Dashboards -->
    <link rel="stylesheet" href="assets/css/bloques.css<?= getCacheBuster('assets/css/bloques.css'); ?>">
    
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
                <?php if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])): ?>
                    <!-- Usuario autenticado -->
                    <?php if ($esDashboard): ?>
                        <!-- Vista Dashboard: Solo mostrar botón Salir -->
                        <a href="<?= app_url('logout.php') ?>" class="btn btn-logout" title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    <?php else: ?>
                        <!-- Vista Normal: Mostrar botón Publícate y Salir -->
                        <?php
                        // Determinar URL del panel según el rol del usuario
                        $rol = $_SESSION['role'] ?? 'publicante';
                        $paneles = [
                            'admin' => 'views/admin/dashboard.php',
                            'promotor' => 'views/promotor/dashboard.php',
                            'publicante' => 'views/publicante/dashboard.php'
                        ];
                        $urlPublicar = app_url($paneles[$rol] ?? 'views/publicante/dashboard.php') . '#crear-anuncio';
                        ?>
                        <a href="<?= $urlPublicar ?>" class="btn btn-publish">+ Publícate</a>
                        <a href="<?= app_url('logout.php') ?>" class="btn btn-logout" title="Cerrar sesión">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Usuario NO autenticado -->
                    <?php if (!$esDashboard): ?>
                        <a href="index.php?view=loginPhone" class="btn btn-publish">+ Publícate</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">
