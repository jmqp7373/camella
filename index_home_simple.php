<?php
/**
 * INDEX TEMPORAL - USA HOME SIMPLE
 */

// Definir rutas principales
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views/');

// Función para sanitizar entradas
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$view = 'home_simple_test'; // Forzar la vista simple

echo "<!DOCTYPE html><html><head><title>Camella Test</title></head><body>";

echo "<p>🚨 <strong>INDEX TEMPORAL</strong> - Usando home simple sin modelo BD</p>";

// Incluir header
if (file_exists('partials/header.php')) {
    include 'partials/header.php';
} else {
    echo "<h1>CAMELLA.COM.CO - TEST</h1>";
}

// Definir la ruta del archivo de vista
$viewPath = VIEWS_PATH . $view . '.php';

// Cargar la vista correspondiente
if (file_exists($viewPath)) {
    echo "<div>✅ Cargando vista: $view</div>";
    include $viewPath;
} else {
    echo "<div>❌ Vista no encontrada: $viewPath</div>";
}

// Incluir footer
if (file_exists('partials/footer.php')) {
    include 'partials/footer.php';
} else {
    echo "<footer>Test Footer</footer>";
}

echo "</body></html>";
?>