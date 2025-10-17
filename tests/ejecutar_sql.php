<?php
/**
 * Script para ejecutar create_servicios_table.sql automáticamente
 * Ejecutar desde línea de comandos: php tests/ejecutar_sql.php
 */

echo "================================================\n";
echo "  CREACIÓN DE TABLA ANUNCIOS\n";
echo "================================================\n\n";

// Conexión a la base de datos
$mysqli = new mysqli('localhost', 'camella_user', 'Reylondres7373', 'camella_db');

// Verificar conexión
if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "✅ Conectado a la base de datos: camella_db\n\n";

// Leer archivo SQL
$sqlFile = __DIR__ . '/create_anuncios_table.sql';
if (!file_exists($sqlFile)) {
    die("❌ No se encuentra el archivo: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);
echo "📄 Leyendo archivo SQL...\n\n";

// Ejecutar queries
if ($mysqli->multi_query($sql)) {
    echo "✅ Tabla 'anuncios' creada exitosamente\n";
    echo "✅ Datos de ejemplo insertados\n\n";
    
    // Limpiar resultados pendientes
    while ($mysqli->next_result()) {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    }
    
    // Verificar la creación
    echo "================================================\n";
    echo "  VERIFICACIÓN\n";
    echo "================================================\n\n";
    
    // Contar registros
    $result = $mysqli->query("SELECT COUNT(*) as total FROM anuncios");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📊 Total de registros en anuncios: " . $row['total'] . "\n\n";
    }
    
    // Mostrar algunos registros
    echo "📋 Anuncios insertados:\n";
    echo "------------------------------------------------\n";
    $result = $mysqli->query("SELECT id, titulo, precio, status, created_at FROM anuncios LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "  ID: {$row['id']}\n";
            echo "  Título: {$row['titulo']}\n";
            echo "  Precio: $" . number_format($row['precio'], 0, ',', '.') . "\n";
            echo "  Status: {$row['status']}\n";
            echo "  Creado: {$row['created_at']}\n";
            echo "------------------------------------------------\n";
        }
    }
    
    echo "\n✨ ¡Proceso completado exitosamente!\n";
    echo "🌐 Ahora puedes acceder al dashboard y ver tus anuncios.\n\n";
    
} else {
    echo "❌ Error ejecutando SQL: " . $mysqli->error . "\n";
}

$mysqli->close();
