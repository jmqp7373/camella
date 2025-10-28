<?php
/**
 * Script para crear la tabla oficios en la base de datos
 * Ejecutar una sola vez desde el navegador: http://localhost/camella.com.co/scripts/crear_tabla_oficios.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(60);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Crear tabla oficios</title></head><body>";
echo "<h1>Creación de tabla OFICIOS</h1>";
echo "<pre>";

try {
    echo "0. Intentando conectar a la base de datos...\n";
    flush();
    
    // Conexión directa sin config
    $host = 'localhost';
    $dbname = 'camella_db';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa.\n\n";
    flush();
    
    // Verificar si la tabla ya existe
    echo "1. Verificando tablas existentes...\n";
    flush();
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas encontradas: " . count($tables) . "\n";
    flush();
    
    $tablaExiste = in_array('oficios', $tables);
    
    if ($tablaExiste) {
        echo "\n⚠️  La tabla 'oficios' YA EXISTE.\n";
        echo "¿Deseas eliminarla y recrearla? (Esto borrará todos los datos)\n";
        echo "<a href='?drop=1' style='color: red; font-weight: bold;'>SÍ, ELIMINAR Y RECREAR</a>\n";
        flush();
        
        if (!isset($_GET['drop']) || $_GET['drop'] != '1') {
            echo "\n</pre></body></html>";
            exit;
        }
        
        echo "\n2. Eliminando tabla existente...\n";
        flush();
        $pdo->exec("DROP TABLE IF EXISTS oficios");
        echo "✅ Tabla eliminada.\n\n";
        flush();
    }
    
    echo "\n3. Creando tabla OFICIOS...\n";
    flush();
    
    $sql = "CREATE TABLE `oficios` (
      `id` INT(11) NOT NULL AUTO_INCREMENT,
      `categoria_id` INT(11) NOT NULL,
      `titulo` VARCHAR(255) NOT NULL,
      `popular` TINYINT(1) DEFAULT 0,
      `orden` INT(11) DEFAULT 0,
      `activo` TINYINT(1) DEFAULT 1,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      INDEX `idx_categoria` (`categoria_id`),
      INDEX `idx_popular` (`popular`),
      INDEX `idx_activo` (`activo`),
      CONSTRAINT `fk_oficios_categoria` 
        FOREIGN KEY (`categoria_id`) 
        REFERENCES `categorias` (`id`) 
        ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'oficios' creada exitosamente.\n\n";
    flush();
    
    // Verificar estructura
    echo "4. Verificando estructura de la tabla...\n";
    flush();
    $stmt = $pdo->query("DESCRIBE oficios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas creadas:\n";
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    flush();
    
    echo "\n✅ PROCESO COMPLETADO CON ÉXITO\n";
    echo "\nAhora puedes:\n";
    echo "1. Actualizar phpMyAdmin (F5) para ver la tabla 'oficios'\n";
    echo "2. Agregar oficios manualmente desde phpMyAdmin\n";
    echo "3. O <a href='?insert=1' style='color: green;'>INSERTAR DATOS DE EJEMPLO</a>\n";
    flush();
    
    if (isset($_GET['insert']) && $_GET['insert'] == '1') {
        echo "\n\n5. Insertando datos de ejemplo...\n";
        flush();
        
        // Obtener categorías
        $stmt = $pdo->query("SELECT id, nombre FROM categorias WHERE activo = 1 LIMIT 5");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($categorias)) {
            echo "⚠️  No hay categorías activas.\n";
        } else {
            $oficios = [
                'Plomero', 'Electricista', 'Carpintero', 'Pintor', 'Albañil',
                'Soldador', 'Mecánico', 'Jardinero', 'Techista', 'Cerrajero'
            ];
            
            $count = 0;
            foreach ($categorias as $cat) {
                for ($i = 0; $i < 3; $i++) {
                    if (isset($oficios[$count])) {
                        $popular = ($count < 3) ? 1 : 0; // Primeros 3 populares
                        $stmt = $pdo->prepare("INSERT INTO oficios (categoria_id, titulo, popular, activo) VALUES (?, ?, ?, 1)");
                        $stmt->execute([$cat['id'], $oficios[$count], $popular]);
                        echo "  ✅ {$oficios[$count]} → {$cat['nombre']}" . ($popular ? " 🔥" : "") . "\n";
                        flush();
                        $count++;
                    }
                }
            }
            echo "\n✅ Total oficios insertados: $count\n";
        }
    }
    
} catch (PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}

echo "</pre></body></html>";
?>
