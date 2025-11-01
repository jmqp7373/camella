<?php
/**
 * Script para verificar y ejecutar ALTER TABLE en anuncios
 * IMPORTANTE: Revisar antes de ejecutar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h1>Verificar y Agregar columna oficio_id a anuncios</h1>";
echo "<pre>";

try {
    $pdo = getPDO();
    
    // Verificar si la columna ya existe
    echo "1. Verificando si columna 'oficio_id' existe...\n";
    $stmt = $pdo->query("DESCRIBE anuncios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existe = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'oficio_id') {
            $existe = true;
            break;
        }
    }
    
    if ($existe) {
        echo "   ✅ La columna 'oficio_id' YA EXISTE\n";
        echo "   No es necesario ejecutar el ALTER TABLE\n\n";
        
        // Verificar relaciones
        echo "2. Anuncios con oficio_id:\n";
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE oficio_id IS NOT NULL");
        $count = $stmt->fetch();
        echo "   Total con oficio: {$count['total']}\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE oficio_id IS NULL");
        $count = $stmt->fetch();
        echo "   Total sin oficio: {$count['total']}\n";
        
    } else {
        echo "   ❌ La columna 'oficio_id' NO EXISTE\n\n";
        
        if (isset($_GET['ejecutar']) && $_GET['ejecutar'] === 'si') {
            echo "2. EJECUTANDO ALTER TABLE...\n";
            
            $alterSQL = "
                ALTER TABLE `anuncios` 
                ADD COLUMN `oficio_id` INT(11) NULL DEFAULT NULL AFTER `user_id`,
                ADD KEY `oficio_id` (`oficio_id`),
                ADD CONSTRAINT `fk_anuncios_oficios` 
                  FOREIGN KEY (`oficio_id`) 
                  REFERENCES `oficios`(`id`) 
                  ON DELETE SET NULL 
                  ON UPDATE CASCADE
            ";
            
            $pdo->exec($alterSQL);
            echo "   ✅ ALTER TABLE ejecutado exitosamente\n";
            echo "   La columna 'oficio_id' ha sido agregada\n\n";
            
            echo "3. Recargar esta página para verificar: ";
            echo "<a href='?" . http_build_query(['ejecutar' => 'no']) . "'>Verificar ahora</a>\n";
            
        } else {
            echo "2. Para agregar la columna, haz clic aquí:\n";
            echo "   <a href='?" . http_build_query(['ejecutar' => 'si']) . "' ";
            echo "onclick='return confirm(\"¿Estás seguro de ejecutar el ALTER TABLE?\")' ";
            echo "style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>";
            echo "✅ EJECUTAR ALTER TABLE</a>\n\n";
            
            echo "   SQL que se ejecutará:\n";
            echo "   -----------------------------------------------\n";
            echo "   ALTER TABLE `anuncios` \n";
            echo "   ADD COLUMN `oficio_id` INT(11) NULL DEFAULT NULL AFTER `user_id`,\n";
            echo "   ADD KEY `oficio_id` (`oficio_id`),\n";
            echo "   ADD CONSTRAINT `fk_anuncios_oficios` \n";
            echo "     FOREIGN KEY (`oficio_id`) \n";
            echo "     REFERENCES `oficios`(`id`) \n";
            echo "     ON DELETE SET NULL \n";
            echo "     ON UPDATE CASCADE;\n";
            echo "   -----------------------------------------------\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='test_categorias_debug.php'>Volver al test</a> | <a href='index.php'>Ir al inicio</a></p>";
?>
