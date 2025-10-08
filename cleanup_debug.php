<?php
/**
 * Script para limpiar logs de debug y archivos temporales
 * Ejecutar después de resolver el problema de actualización de categorías
 */

// Limpiar logs de debug
$debugFiles = [
    __DIR__ . '/debug_categorias.log',
    __DIR__ . '/debug_logger.php'
];

foreach ($debugFiles as $file) {
    if (file_exists($file)) {
        unlink($file);
        echo "Eliminado: " . basename($file) . "\n";
    }
}

echo "Limpieza completada.\n";
?>