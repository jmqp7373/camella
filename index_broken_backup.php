<?php
/**
 * CAMELLA.COM.CO - Index Simplificado y Funcional
 * Versión limpia enfocada en cargar vistas sin interferencias
 */

// Configuraciones básicas
error_reporting(E_ALL);
ini_set('display_errors', 0); // Producción

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

// Definir la ruta del archivo de vista
$viewPath = 'views/' . $view . '.php';

// Incluir header
include 'partials/header.php';

// Cargar la vista correspondiente
if (file_exists($viewPath)) {
    include $viewPath;
} else {
    // Vista de error 404 personalizada
    echo '<div style="text-align: center; padding: 4rem; background: #f8f9fa; margin: 20px; border-radius: 8px;">';
    echo '<h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Vista no encontrada</h2>';
    echo '<p>La página <strong>"' . htmlspecialchars($view) . '"</strong> no existe o está en desarrollo.</p>';
    echo '<a href="index.php" style="display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px;"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
}

// Incluir footer
include 'partials/footer.php';
?>