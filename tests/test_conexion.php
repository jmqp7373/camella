<?php
/**
 * Test de Conexión a Base de Datos
 * Verifica que la configuración PDO funcione correctamente
 */

// Incluir el sistema de base de datos
require_once __DIR__ . '/../config/database.php';

echo "<h2>🧪 Test de Conexión - Camella.com.co</h2>\n";
echo "<hr>\n";

// Test 1: Verificar conexión básica
echo "<h3>Test 1: Conexión Básica</h3>\n";
try {
    $pdo = getPDO();
    echo "✅ <strong>Conexión exitosa</strong> a la base de datos.<br>\n";
    echo "📊 <strong>Servidor:</strong> " . DB_HOST . "<br>\n";
    echo "🗃️ <strong>Base de datos:</strong> " . DB_NAME . "<br>\n";
    echo "👤 <strong>Usuario:</strong> " . DB_USER . "<br>\n";
} catch (PDOException $e) {
    echo "❌ <strong>Error al conectar:</strong> " . $e->getMessage() . "<br>\n";
    exit;
}

echo "<hr>\n";

// Test 2: Verificar versión de MySQL
echo "<h3>Test 2: Información del Servidor</h3>\n";
try {
    $version = $pdo->query('SELECT VERSION() as version')->fetch();
    echo "🔧 <strong>Versión MySQL:</strong> " . $version['version'] . "<br>\n";
    
    $charset = $pdo->query("SHOW VARIABLES LIKE 'character_set_database'")->fetch();
    echo "🔤 <strong>Charset:</strong> " . $charset['Value'] . "<br>\n";
} catch (PDOException $e) {
    echo "⚠️ <strong>No se pudo obtener info del servidor:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";

// Test 3: Verificar si existe la base de datos
echo "<h3>Test 3: Verificación de Base de Datos</h3>\n";
try {
    $databases = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'")->fetchAll();
    if (empty($databases)) {
        echo "⚠️ <strong>Base de datos '" . DB_NAME . "' no existe</strong><br>\n";
        echo "💡 <strong>Solución:</strong> Crear la base de datos en phpMyAdmin<br>\n";
        echo "🔗 <strong>URL phpMyAdmin:</strong> <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a><br>\n";
    } else {
        echo "✅ <strong>Base de datos existe</strong><br>\n";
        
        // Test 4: Listar tablas
        echo "<h4>Tablas encontradas:</h4>\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        if (empty($tables)) {
            echo "📝 <strong>No hay tablas creadas aún</strong><br>\n";
        } else {
            echo "<ul>\n";
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                echo "<li>📋 " . $tableName . "</li>\n";
            }
            echo "</ul>\n";
        }
    }
} catch (PDOException $e) {
    echo "❌ <strong>Error verificando BD:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";

// Test 5: Probar funciones auxiliares
echo "<h3>Test 4: Funciones Auxiliares</h3>\n";
try {
    // Probar executeQuery con una consulta simple
    $stmt = executeQuery("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result['test'] == 1) {
        echo "✅ <strong>Función executeQuery() funciona correctamente</strong><br>\n";
    }
    
    // Probar fetchOne
    $testResult = fetchOne("SELECT 'Hola Camella' as mensaje");
    if ($testResult && $testResult['mensaje'] == 'Hola Camella') {
        echo "✅ <strong>Función fetchOne() funciona correctamente</strong><br>\n";
    }
    
    // Probar fetchAll
    $testResults = fetchAll("SELECT 1 as num UNION SELECT 2 as num");
    if (count($testResults) == 2) {
        echo "✅ <strong>Función fetchAll() funciona correctamente</strong><br>\n";
    }
    
} catch (PDOException $e) {
    echo "❌ <strong>Error en funciones auxiliares:</strong> " . $e->getMessage() . "<br>\n";
}

echo "<hr>\n";
echo "<h3>🎯 Resumen Final</h3>\n";
echo "<p><strong>Fecha del test:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
echo "<p><strong>Estado:</strong> Test completado</p>\n";

// Información adicional
echo "<hr>\n";
echo "<h3>📋 Próximos Pasos</h3>\n";
echo "<ol>\n";
echo "<li>Si la BD no existe, créala en phpMyAdmin</li>\n";
echo "<li>Importar estructura de tablas (categorias, ofertas, etc.)</li>\n";
echo "<li>Probar el modelo Categorias.php</li>\n";
echo "<li>Verificar que el sitio web funcione correctamente</li>\n";
echo "</ol>\n";
?>