<?php
/**
 * Script para verificar estructura de tabla anuncios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Estructura de tabla anuncios</h1>";
echo "<pre>";

require_once __DIR__ . '/config/database.php';
$pdo = getPDO();

echo "=== Columnas de la tabla anuncios ===\n";
try {
    $stmt = $pdo->query("DESCRIBE anuncios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}\n";
    }
    
    echo "\n=== Primeros 2 registros de anuncios ===\n";
    $stmt = $pdo->query("SELECT * FROM anuncios LIMIT 2");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($rows)) {
        print_r($rows);
    } else {
        echo "No hay registros en la tabla\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<p><a href='test_sql_debug.php'>Volver</a></p>";
?>
