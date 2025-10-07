<?php
/**
 * CAMELLA.COM.CO - Portal de Empleo
 * Archivo principal del sitio web con estructura MVC
 * 
 * @author Camella Development Team
 * @version 2.0
 * @date 2025
 */

// Configuraciones globales
error_reporting(E_ALL);
ini_set('display_errors', 0); // En producción debe ser 0

// Definir rutas principales
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views/');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers/');
define('MODELS_PATH', BASE_PATH . '/models/');

// Función para sanitizar entradas
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Obtener la vista solicitada (default: home)
$view = isset($_GET['view']) ? sanitize_input($_GET['view']) : 'home';

// Lista de vistas permitidas (seguridad)
$allowed_views = [
    'home',
    'empresas', 
    'talentos',
    'contacto',
    'publicar-oferta',
    'buscar-empleo',
    'registro-empresa',
    'registro-talento',
    'privacidad',
    'terminos',
    'ayuda'
];

// Verificar que la vista sea válida
if (!in_array($view, $allowed_views)) {
    $view = 'home';
}

// Definir la ruta del archivo de vista
$viewPath = VIEWS_PATH . $view . '.php';

// Incluir header
include 'partials/header.php';

// Cargar la vista correspondiente
if (file_exists($viewPath)) {
    include $viewPath;
} else {
    // Vista de error 404 personalizada
    echo '<div style="text-align: center; padding: 4rem;">';
    echo '<h2><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Vista no encontrada</h2>';
    echo '<p>La página <strong>"' . htmlspecialchars($view) . '"</strong> no existe o está en desarrollo.</p>';
    echo '<a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
}

// Incluir footer
include 'partials/footer.php';
?>