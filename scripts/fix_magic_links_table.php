<?php
/**
 * Verificar y agregar columna 'usos' a la tabla magic_links
 * http://localhost/camella.com.co/scripts/fix_magic_links_table.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Fix Magic Links Table</title></head><body>";
echo "<h1>🔧 Fix Magic Links Table</h1>";
echo "<pre>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa\n\n";
    
    // Ver estructura actual
    echo "Estructura actual de magic_links:\n";
    $stmt = $pdo->query("DESCRIBE magic_links");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $tieneUsos = false;
    foreach ($columnas as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
        if ($col['Field'] === 'usos') {
            $tieneUsos = true;
        }
    }
    
    echo "\n";
    
    if ($tieneUsos) {
        echo "✅ La columna 'usos' ya existe\n";
    } else {
        echo "⚠️  La columna 'usos' NO existe. Agregando...\n\n";
        
        $pdo->exec("ALTER TABLE magic_links ADD COLUMN usos INT(11) DEFAULT 0 COMMENT 'Contador de usos del link'");
        
        echo "✅ Columna 'usos' agregada exitosamente\n\n";
        
        // Verificar de nuevo
        echo "Nueva estructura:\n";
        $stmt = $pdo->query("DESCRIBE magic_links");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columnas as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    }
    
    echo "\n✅ Proceso completado\n";
    echo "\nPuedes volver a: <a href='test_magic_link.php'>test_magic_link.php</a>\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
?>
