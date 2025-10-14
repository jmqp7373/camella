<?php
/**
 * Configuración de rutas de la aplicación
 * Define el subdirectorio de la app según el entorno
 */

// Detectar si estamos en local o producción
$isLocal = (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
    strpos($_SERVER['HTTP_HOST'], '::1') !== false
);

// Definir subdirectorio según entorno
if ($isLocal) {
    define('APP_SUBDIR', '/camella.com.co');
} else {
    define('APP_SUBDIR', ''); // En producción está en la raíz
}

// Definir otras rutas útiles
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] . APP_SUBDIR);
define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . APP_SUBDIR);

// Helper para generar URLs
function app_url($path = '') {
    $path = ltrim($path, '/');
    return APP_URL . ($path ? '/' . $path : '');
}

// Helper para generar rutas de assets
function asset($path = '') {
    $path = ltrim($path, '/');
    return APP_SUBDIR . '/assets/' . $path;
}
