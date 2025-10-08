<?php
/**
 * Logger temporal para debugging de categorías
 * Este archivo será eliminado una vez resuelto el problema
 */

function logCategoria($tipo, $datos) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $tipo: " . print_r($datos, true) . "\n";
    file_put_contents(__DIR__ . '/debug_categorias.log', $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para limpiar el log
function limpiarLogCategorias() {
    $logFile = __DIR__ . '/debug_categorias.log';
    if (file_exists($logFile)) {
        unlink($logFile);
    }
}
?>