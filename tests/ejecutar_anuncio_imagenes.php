<?php
/**
 * Script para crear la tabla anuncio_imagenes
 * Ejecutar: php tests/ejecutar_anuncio_imagenes.php
 */

// Configurar HTTP_HOST para modo CLI (localhost)
if (php_sapi_name() === 'cli') {
    $_SERVER['HTTP_HOST'] = 'localhost';
}

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "CREAR TABLA: anuncio_imagenes\n";
echo "========================================\n\n";

try {
    $db = getPDO();
    
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Paso 1: Crear tabla
    echo "📋 Paso 1: Creando tabla anuncio_imagenes...\n";
    
    $createTable = "
    CREATE TABLE IF NOT EXISTS anuncio_imagenes (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      anuncio_id INT(11) NOT NULL,
      ruta VARCHAR(255) NOT NULL,
      orden TINYINT UNSIGNED DEFAULT 1,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
      INDEX idx_anuncio_id (anuncio_id),
      INDEX idx_orden (orden)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    try {
        $db->exec($createTable);
        echo "✅ Tabla 'anuncio_imagenes' creada exitosamente\n\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️  La tabla 'anuncio_imagenes' ya existe\n\n";
        } else {
            throw $e;
        }
    }
    
    // Paso 2: Insertar datos de ejemplo
    echo "📋 Paso 2: Insertando imágenes de ejemplo...\n";
    
    $insertImages = [
        [1, '/assets/images/anuncios/ejemplos/plomero.jpg', 1],
        [2, '/assets/images/anuncios/ejemplos/electricista.jpg', 1],
        [3, '/assets/images/anuncios/ejemplos/carpintero.jpg', 1]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE ruta = VALUES(ruta)
    ");
    
    $insertCount = 0;
    foreach ($insertImages as $image) {
        try {
            $stmt->execute($image);
            $insertCount++;
            echo "  ✅ Imagen insertada: {$image[1]}\n";
        } catch (PDOException $e) {
            echo "  ⚠️  Error insertando imagen: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📊 Total imágenes insertadas: $insertCount\n\n";
    
    // Paso 3: Verificar datos
    echo "📋 Paso 3: Verificando datos...\n";
    echo "----------------------------------------\n";
    
    $stmt = $db->query("
        SELECT 
            ai.id,
            ai.anuncio_id,
            a.titulo,
            ai.ruta,
            ai.orden,
            ai.created_at
        FROM anuncio_imagenes ai
        INNER JOIN anuncios a ON ai.anuncio_id = a.id
        ORDER BY ai.anuncio_id, ai.orden
    ");
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($results as $row) {
        echo sprintf(
            "ID: %d | Anuncio #%d: %s | Ruta: %s | Orden: %d\n",
            $row['id'],
            $row['anuncio_id'],
            $row['titulo'],
            $row['ruta'],
            $row['orden']
        );
    }
    echo "----------------------------------------\n\n";
    
    // Conteo final
    $stmt = $db->query("SELECT COUNT(*) as total FROM anuncio_imagenes");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "========================================\n";
    echo "RESUMEN FINAL\n";
    echo "========================================\n";
    echo "✅ Tabla creada: anuncio_imagenes\n";
    echo "📊 Total de imágenes registradas: $total\n";
    echo "========================================\n\n";
    
    echo "✅ Proceso completado exitosamente!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR FATAL: " . $e->getMessage() . "\n\n";
    exit(1);
}
