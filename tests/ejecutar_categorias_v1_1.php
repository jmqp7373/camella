<?php
/**
 * Script para ejecutar insert_categorias_v1_1.sql
 * Ejecutar desde navegador: http://localhost/camella.com.co/tests/ejecutar_categorias_v1_1.php
 */

echo "<pre>";
echo "================================================\n";
echo "  INSERCIÓN DE CATEGORÍAS Y OFICIOS V1.1\n";
echo "================================================\n\n";

// Conexión a la base de datos
$mysqli = new mysqli('localhost', 'camella_user', 'Reylondres7373', 'camella_db');

// Verificar conexión
if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "✅ Conectado a la base de datos: camella_db\n\n";

// Verificar que existen las tablas necesarias
$tables_check = $mysqli->query("SHOW TABLES LIKE 'categorias'");
if ($tables_check->num_rows == 0) {
    die("❌ La tabla 'categorias' no existe. Ejecuta primero database_structure.sql\n");
}

$tables_check = $mysqli->query("SHOW TABLES LIKE 'oficios'");
if ($tables_check->num_rows == 0) {
    die("❌ La tabla 'oficios' no existe. Ejecuta primero database_structure.sql\n");
}

echo "✅ Tablas verificadas: categorias y oficios existen\n\n";

// Contar categorías existentes
$result = $mysqli->query("SELECT COUNT(*) as total FROM categorias");
$row = $result->fetch_assoc();
$categorias_antes = $row['total'];

$result = $mysqli->query("SELECT COUNT(*) as total FROM oficios");
$row = $result->fetch_assoc();
$oficios_antes = $row['total'];

echo "📊 Estado actual:\n";
echo "   - Categorías existentes: $categorias_antes\n";
echo "   - Oficios existentes: $oficios_antes\n\n";

// Leer archivo SQL
$sqlFile = __DIR__ . '/../insert_categorias_v1_1.sql';
if (!file_exists($sqlFile)) {
    die("❌ No se encuentra el archivo: insert_categorias_v1_1.sql\n");
}

$sql = file_get_contents($sqlFile);
echo "📄 Leyendo archivo SQL...\n\n";

// Dividir el SQL en statements individuales
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success_count = 0;
$error_count = 0;

echo "🔄 Ejecutando inserciones...\n\n";

foreach ($statements as $statement) {
    // Ignorar comentarios y líneas vacías
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    if ($mysqli->query($statement)) {
        $success_count++;
    } else {
        // Solo mostrar errores que no sean de duplicados
        if (strpos($mysqli->error, 'Duplicate entry') === false) {
            echo "⚠️  Error: " . $mysqli->error . "\n";
            $error_count++;
        }
    }
}

echo "✅ Statements ejecutados exitosamente: $success_count\n";
if ($error_count > 0) {
    echo "⚠️  Errores encontrados: $error_count\n";
}
echo "\n";

// Verificar el resultado
echo "================================================\n";
echo "  VERIFICACIÓN POST-INSERCIÓN\n";
echo "================================================\n\n";

$result = $mysqli->query("SELECT COUNT(*) as total FROM categorias");
$row = $result->fetch_assoc();
$categorias_despues = $row['total'];

$result = $mysqli->query("SELECT COUNT(*) as total FROM oficios");
$row = $result->fetch_assoc();
$oficios_despues = $row['total'];

echo "📊 Estado final:\n";
echo "   - Categorías totales: $categorias_despues (+". ($categorias_despues - $categorias_antes) .")\n";
echo "   - Oficios totales: $oficios_despues (+". ($oficios_despues - $oficios_antes) .")\n\n";

// Mostrar las categorías nuevas
echo "📋 Categorías insertadas (últimas 10):\n";
echo "------------------------------------------------\n";
$result = $mysqli->query("SELECT id, nombre, icono FROM categorias ORDER BY id DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']} | {$row['nombre']} | {$row['icono']}\n";
    }
}
echo "\n";

// Mostrar algunos oficios nuevos
echo "📋 Oficios insertados (últimos 15):\n";
echo "------------------------------------------------\n";
$result = $mysqli->query("
    SELECT o.id, o.nombre as oficio, c.nombre as categoria 
    FROM oficios o 
    JOIN categorias c ON o.categoria_id = c.id 
    ORDER BY o.id DESC 
    LIMIT 15
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']} | {$row['oficio']} | ({$row['categoria']})\n";
    }
}

echo "\n✨ ¡Proceso completado exitosamente!\n";
echo "🌐 Las nuevas categorías v1.1 están disponibles en el sistema.\n\n";

$mysqli->close();

echo "</pre>";
