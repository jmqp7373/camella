<?php
/**
 * Script para verificar y crear la tabla magic_links
 * Ejecutar una vez: http://localhost/camella.com.co/setup_magic_links.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup Magic Links</title></head><body>";
echo "<h1>🔗 Setup Magic Links Table</h1>";
echo "<pre>";

try {
    // Conexión directa
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Verificar si la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'magic_links'");
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "⚠️  La tabla 'magic_links' ya existe\n\n";
        
        // Mostrar estructura
        echo "Estructura actual:\n";
        $stmt = $pdo->query("DESCRIBE magic_links");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columnas as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM magic_links");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\nRegistros en la tabla: {$count['total']}\n";
        
    } else {
        echo "📝 Creando tabla 'magic_links'...\n\n";
        
        $sql = "CREATE TABLE `magic_links` (
          `id` INT(11) NOT NULL AUTO_INCREMENT,
          `token` VARCHAR(20) NOT NULL UNIQUE,
          `phone` VARCHAR(20) NOT NULL,
          `code` VARCHAR(6) DEFAULT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `usos` INT(11) DEFAULT 0,
          PRIMARY KEY (`id`),
          INDEX `idx_token` (`token`),
          INDEX `idx_phone` (`phone`),
          INDEX `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Magic links para login automático'";
        
        $pdo->exec($sql);
        echo "✅ Tabla 'magic_links' creada exitosamente\n\n";
        
        // Verificar estructura
        echo "Estructura creada:\n";
        $stmt = $pdo->query("DESCRIBE magic_links");
        $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columnas as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    }
    
    echo "\n✅ Setup completado con éxito\n";
    echo "\nPuedes probar el magic link con:\n";
    echo "http://localhost/camella.com.co/index.php?view=m&token=TU_TOKEN_AQUI\n";
    echo "o usando URL amigable:\n";
    echo "http://localhost/camella.com.co/m/TU_TOKEN_AQUI (requiere .htaccess)\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
?>
