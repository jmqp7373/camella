<?php
/**
 * SCRIPT DE PRODUCCIÓN: Verificar y crear tabla magic_links
 * 
 * INSTRUCCIONES:
 * 1. Subir este archivo al servidor de producción
 * 2. Acceder a: https://camella.com.co/scripts/setup_magic_links_production.php
 * 3. Verificar que la tabla se creó correctamente
 * 4. ELIMINAR este archivo del servidor por seguridad
 */

// Cargar configuración de base de datos
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Magic Links - Producción</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border: 1px solid #c3e6cb;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border: 1px solid #f5c6cb;
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border: 1px solid #ffeeba;
        }
        .info { 
            background: #d1ecf1; 
            color: #0c5460; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0;
            border: 1px solid #bee5eb;
        }
        pre { 
            background: #fff; 
            padding: 15px; 
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🔧 Setup Magic Links - Servidor de Producción</h1>
    
    <?php
    try {
        $pdo = getPDO();
        echo "<div class='success'>✅ Conexión a base de datos exitosa</div>";
        
        // Verificar si la tabla existe
        echo "<h2>1. Verificar existencia de tabla magic_links</h2>";
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'magic_links'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            echo "<div class='warning'>⚠️ La tabla 'magic_links' YA EXISTE</div>";
            
            // Mostrar estructura
            echo "<h3>Estructura actual:</h3>";
            $stmt = $pdo->query("DESCRIBE magic_links");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>" . print_r($structure, true) . "</pre>";
            
            // Mostrar conteo
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM magic_links");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div class='info'>📊 Registros en la tabla: {$count['total']}</div>";
            
        } else {
            echo "<div class='info'>ℹ️ La tabla 'magic_links' NO existe. Creando...</div>";
            
            // Crear la tabla
            echo "<h2>2. Crear tabla magic_links</h2>";
            
            $sql = "
                CREATE TABLE magic_links (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    token VARCHAR(64) NOT NULL UNIQUE,
                    phone VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    usos INT DEFAULT 0,
                    INDEX idx_token (token),
                    INDEX idx_phone (phone),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $pdo->exec($sql);
            echo "<div class='success'>✅ Tabla 'magic_links' creada exitosamente</div>";
            
            // Verificar creación
            $stmt = $pdo->query("DESCRIBE magic_links");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Estructura creada:</h3>";
            echo "<pre>" . print_r($structure, true) . "</pre>";
        }
        
        // Verificar tabla verification_codes
        echo "<h2>3. Verificar tabla verification_codes</h2>";
        $stmt = $pdo->query("SHOW TABLES LIKE 'verification_codes'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Tabla 'verification_codes' existe</div>";
            
            $stmt = $pdo->query("DESCRIBE verification_codes");
            $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<pre>" . print_r($structure, true) . "</pre>";
        } else {
            echo "<div class='error'>❌ Tabla 'verification_codes' NO existe (necesaria para el sistema)</div>";
        }
        
        echo "<h2>✅ Setup completado</h2>";
        echo "<div class='success'>";
        echo "<p><strong>El sistema está listo para usar Magic Links</strong></p>";
        echo "<p>Ahora puedes:</p>";
        echo "<ul>";
        echo "<li>Probar el login desde: <a href='https://camella.com.co/index.php?view=loginPhone'>Login Phone</a></li>";
        echo "<li>Los SMS incluirán el magic link automáticamente</li>";
        echo "<li>⚠️ <strong>IMPORTANTE: Elimina este archivo (setup_magic_links_production.php) por seguridad</strong></li>";
        echo "</ul>";
        echo "</div>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>";
        echo "<h3>❌ Error de Base de Datos</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Código:</strong> " . $e->getCode() . "</p>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>";
        echo "<h3>❌ Error General</h3>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <hr style="margin: 40px 0;">
    <p style="color: #999; text-align: center; font-size: 12px;">
        Camella.com.co - Setup Magic Links v1.0
    </p>
</body>
</html>
