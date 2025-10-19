<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Verificación de Estructura de Tabla</h1>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>");
}

// Verificar si existe tabla 'oficios'
echo "<h2>1. ¿Existe la tabla 'oficios'?</h2>";
$stmt = $pdo->query("SHOW TABLES LIKE 'oficios'");
$existe = $stmt->fetch();
if ($existe) {
    echo "<p style='color: green;'>✅ Tabla 'oficios' existe</p>";
} else {
    echo "<p style='color: red;'>❌ Tabla 'oficios' NO existe</p>";
    
    // Buscar tablas similares
    echo "<h3>Tablas disponibles:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li>$tabla</li>";
    }
    echo "</ul>";
}

// Describir estructura de la tabla
echo "<h2>2. Estructura de la tabla 'oficios'</h2>";
try {
    $stmt = $pdo->query("DESCRIBE oficios");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['Field']}</strong></td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Columnas encontradas:</h3>";
    echo "<ul>";
    foreach ($columnas as $col) {
        echo "<li><code>{$col['Field']}</code></li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Mostrar datos de ejemplo
echo "<h2>3. Primeros 3 registros (SELECT *)</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM oficios LIMIT 3");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($registros)) {
        echo "<p style='color: orange;'>⚠️ La tabla está vacía</p>";
    } else {
        foreach ($registros as $reg) {
            echo "<pre style='background: #f0f0f0; padding: 10px; border-radius: 5px;'>";
            print_r($reg);
            echo "</pre>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test específico de consulta que usa OficioModel
echo "<h2>4. Probar consulta de OficioModel</h2>";
$sql = "SELECT id, categoria_id, titulo, popular, activo, created_at, updated_at 
        FROM oficios 
        WHERE id = :id 
        LIMIT 1";

echo "<p>SQL: <code>$sql</code></p>";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => 1]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado) {
        echo "<p style='color: green;'>✅ Consulta exitosa</p>";
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay registro con ID 1</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error en consulta: " . $e->getMessage() . "</p>";
    echo "<p><strong>Probable causa:</strong> Las columnas en la consulta no coinciden con las reales</p>";
}

?>
