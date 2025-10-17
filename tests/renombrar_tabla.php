<?php
/**
 * Script para renombrar la tabla servicios a anuncios
 */

echo "================================================\n";
echo "  RENOMBRAR TABLA: servicios → anuncios\n";
echo "================================================\n\n";

$mysqli = new mysqli('localhost', 'camella_user', 'Reylondres7373', 'camella_db');

if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "✅ Conectado a la base de datos\n\n";

// Verificar si existe la tabla servicios
$check = $mysqli->query("SHOW TABLES LIKE 'servicios'");
if ($check->num_rows > 0) {
    echo "📋 Tabla 'servicios' encontrada\n";
    
    // Renombrar la tabla
    if ($mysqli->query("RENAME TABLE `servicios` TO `anuncios`")) {
        echo "✅ Tabla renombrada exitosamente: servicios → anuncios\n\n";
        
        // Verificar
        $verify = $mysqli->query("SHOW TABLES LIKE 'anuncios'");
        if ($verify->num_rows > 0) {
            echo "✅ Verificación: Tabla 'anuncios' existe\n";
            
            // Contar registros
            $result = $mysqli->query("SELECT COUNT(*) as total FROM anuncios");
            $row = $result->fetch_assoc();
            echo "📊 Total de registros: " . $row['total'] . "\n\n";
            
            echo "✨ ¡Proceso completado exitosamente!\n";
        }
    } else {
        echo "❌ Error al renombrar: " . $mysqli->error . "\n";
    }
} else {
    echo "⚠️  La tabla 'servicios' no existe\n";
    
    // Verificar si ya existe anuncios
    $checkAnuncios = $mysqli->query("SHOW TABLES LIKE 'anuncios'");
    if ($checkAnuncios->num_rows > 0) {
        echo "✅ La tabla 'anuncios' ya existe\n";
        
        $result = $mysqli->query("SELECT COUNT(*) as total FROM anuncios");
        $row = $result->fetch_assoc();
        echo "📊 Total de registros: " . $row['total'] . "\n";
    } else {
        echo "❌ Ninguna de las dos tablas existe. Ejecuta: php tests/ejecutar_sql.php\n";
    }
}

$mysqli->close();
