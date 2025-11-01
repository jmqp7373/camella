<?php
/**
 * Script de depuración para verificar carga de categorías
 * TEMPORAL - Usar solo para diagnóstico, eliminar después
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Carga de Categorías</h1>";
echo "<pre>";

// 1. Verificar que el archivo del modelo existe
$modelPath = __DIR__ . '/models/Categorias.php';
echo "1. Verificando ruta del modelo:\n";
echo "   Ruta: $modelPath\n";
echo "   Existe: " . (file_exists($modelPath) ? "✅ SÍ" : "❌ NO") . "\n\n";

// 2. Verificar conexión a BD
echo "2. Verificando conexión a base de datos:\n";
try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getPDO();
    echo "   Conexión: ✅ EXITOSA\n";
    echo "   Base de datos: " . DB_NAME . "\n\n";
} catch (Exception $e) {
    echo "   Conexión: ❌ FALLÓ\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    die("No se puede continuar sin conexión a BD");
}

// 3. Verificar que la tabla categorias existe
echo "3. Verificando tabla 'categorias':\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'categorias'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "   Tabla: ✅ EXISTE\n";
        
        // Contar registros
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
        $count = $stmt->fetch();
        echo "   Categorías activas: " . $count['total'] . "\n\n";
    } else {
        echo "   Tabla: ❌ NO EXISTE\n\n";
        die("La tabla 'categorias' no existe en la base de datos");
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n\n";
}

// 4. Cargar el modelo y obtener categorías
echo "4. Cargando modelo Categorias:\n";
try {
    require_once $modelPath;
    echo "   Modelo cargado: ✅ OK\n";
    
    $categoriasModel = new Categorias();
    echo "   Instancia creada: ✅ OK\n";
    
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
    echo "   Categorías obtenidas: " . count($categorias) . "\n\n";
    
    if (!empty($categorias)) {
        echo "5. Primeras 3 categorías:\n";
        foreach (array_slice($categorias, 0, 3) as $cat) {
            echo "   - ID: {$cat['id']} | Nombre: {$cat['nombre']} | Oficios: {$cat['total_oficios']}\n";
        }
    } else {
        echo "   ⚠️ ADVERTENCIA: No se obtuvieron categorías\n";
    }
    
} catch (Exception $e) {
    echo "   Error: ❌ FALLÓ\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<p><strong>Estado general:</strong> ";
if (!empty($categorias) && count($categorias) > 0) {
    echo "<span style='color: green; font-size: 1.5em;'>✅ TODO FUNCIONA CORRECTAMENTE</span>";
} else {
    echo "<span style='color: red; font-size: 1.5em;'>❌ HAY PROBLEMAS</span>";
}
echo "</p>";

echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
