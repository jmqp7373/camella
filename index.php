<?php
/**
 * CAMELLA.COM.CO - Index MVC Robusto
 * Versión corregida con rutas absolutas y mejor manejo de errores
 */

// Configuraciones básicas
error_reporting(E_ALL);
ini_set('display_errors', 0); // Producción

// Definir rutas absolutas desde el directorio raíz
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views/');
define('PARTIALS_PATH', BASE_PATH . '/partials/');
define('MODELS_PATH', BASE_PATH . '/models/');

// Función para sanitizar entradas
function sanitize_input($data) {
    return htmlspecialchars(trim(stripslashes($data)));
}

// Obtener la vista solicitada (default: home)
$view = isset($_GET['view']) ? sanitize_input($_GET['view']) : 'home';

// Lista de vistas permitidas (seguridad)
$allowed_views = [
    'home',
    'categoria', 
    'contacto',
    'publicar-oferta',
    'buscar-empleo',
    'registro-empresa',
    'registro-talento',
    'login',
    'registro',
    'recuperar-password',
    'privacidad',
    'terminos',
    'ayuda'
];

// Verificar que la vista sea válida
if (!in_array($view, $allowed_views)) {
    $view = 'home';
}

// Definir rutas de archivos con rutas absolutas
$viewPath = VIEWS_PATH . $view . '.php';
$headerPath = PARTIALS_PATH . 'header.php';
$footerPath = PARTIALS_PATH . 'footer.php';

// Incluir header de forma segura
if (file_exists($headerPath)) {
    try {
        include $headerPath;
    } catch (Exception $e) {
        echo "<!-- Error cargando header: " . $e->getMessage() . " -->";
        // Header mínimo de respaldo
        echo '<!DOCTYPE html><html><head><title>Camella.com.co</title></head><body>';
        echo '<header style="background: #007bff; color: white; padding: 15px; text-align: center;">';
        echo '<h1>🚧 Camella.com.co - Modo Mantenimiento</h1></header>';
    }
} else {
    // Header de respaldo si no existe el archivo
    echo '<!DOCTYPE html><html><head><title>Camella.com.co</title></head><body>';
    echo '<header style="background: #007bff; color: white; padding: 15px; text-align: center;">';
    echo '<h1>Camella.com.co</h1></header>';
}

// Cargar la vista correspondiente de forma segura
if (file_exists($viewPath)) {
    try {
        include $viewPath;
    } catch (Exception $e) {
        echo '<div style="text-align: center; padding: 2rem; background: #ffe6e6; margin: 20px; border-radius: 8px;">';
        echo '<h2 style="color: #d63031;">⚠️ Error cargando la vista</h2>';
        echo '<p>Hubo un problema técnico. Por favor, intenta más tarde.</p>';
        echo '<a href="index.php" style="display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px;">🏠 Volver al Inicio</a>';
        echo '</div>';
    }
} else {
    // Vista de error 404 personalizada
    echo '<div style="text-align: center; padding: 4rem; background: #f8f9fa; margin: 20px; border-radius: 8px;">';
    echo '<h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Vista no encontrada</h2>';
    echo '<p>La página <strong>"' . htmlspecialchars($view) . '"</strong> no existe o está en desarrollo.</p>';
    echo '<a href="index.php" style="display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px;">🏠 Volver al Inicio</a>';
    echo '</div>';
}

// Incluir footer de forma segura
if (file_exists($footerPath)) {
    try {
        include $footerPath;
    } catch (Exception $e) {
        echo "<!-- Error cargando footer: " . $e->getMessage() . " -->";
        // Footer mínimo de respaldo
        echo '<footer style="background: #007bff; color: white; padding: 15px; text-align: center; margin-top: 20px;">';
        echo '<p>&copy; 2025 Camella.com.co - Portal de Empleo</p></footer>';
        echo '</body></html>';
    }
} else {
    // Footer de respaldo
    echo '<footer style="background: #007bff; color: white; padding: 15px; text-align: center; margin-top: 20px;">';
    echo '<p>&copy; 2025 Camella.com.co - Portal de Empleo</p></footer>';
    echo '</body></html>';
}
?>