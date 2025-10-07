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
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Font Awesome para iconos -->
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
    <header class="main-header">
        <div class="header-content">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="assets/images/logo/logo_horizontal.png" alt="Camella Logo">
                
            </a>
            
            <!-- Navegación Principal -->
            <nav class="main-nav">
                <ul class="nav-links">
                    <li>
                        <a href="index.php" class="<?php echo (!isset($_GET['view']) || $_GET['view'] == 'home') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li>
                        <a href="index.php?view=empresas" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'empresas') ? 'active' : ''; ?>">
                            <i class="fas fa-building"></i> Empresas
                        </a>
                    </li>
                    <li>
                        <a href="index.php?view=talentos" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'talentos') ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Talentos
                        </a>
                    </li>
                    <li>
                        <a href="index.php?view=contacto" class="<?php echo (isset($_GET['view']) && $_GET['view'] == 'contacto') ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i> Contacto
                        </a>
                    </li>
                    <li>
                        <a href="index.php?view=publicar-oferta" class="btn-publicar">
                            <i class="fas fa-plus-circle"></i> Publicar Oferta
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main-content">