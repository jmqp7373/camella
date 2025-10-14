<?php
/**
 * Logout - Camella.com.co
 * Cierra la sesión del usuario y lo redirige al home
 */

// Iniciar sesión si no está activa
if (!isset($_SESSION)) {
    session_start();
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Redirigir al home con mensaje
header('Location: index.php?logout=success');
exit;
?>
