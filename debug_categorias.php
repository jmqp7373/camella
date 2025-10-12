<?php
/**
 * DIAGNÓSTICO ESPECÍFICO - views/home.php
 */

echo "<h1>🔍 DIAGNÓSTICO ESPECÍFICO HOME.PHP</h1>";
echo "<hr>";

// Test 1: Verificar que el modelo existe
echo "<h2>📁 Test 1: Archivo del modelo</h2>";
if (file_exists('models/Categorias.php')) {
    echo "✅ models/Categorias.php existe<br>";
    echo "Tamaño: " . filesize('models/Categorias.php') . " bytes<br>";
} else {
    echo "❌ models/Categorias.php NO existe<br>";
}

// Test 2: Intentar incluir solo el modelo
echo "<h2>🔧 Test 2: Incluir modelo Categorias</h2>";
try {
    require_once 'models/Categorias.php';
    echo "✅ Modelo incluido sin errores<br>";
    
    // Test 3: Instanciar el modelo
    echo "<h2>🏗️ Test 3: Instanciar modelo</h2>";
    $categoriasModel = new Categorias();
    echo "✅ Modelo instanciado<br>";
    
    // Test 4: Llamar método
    echo "<h2>📊 Test 4: Obtener categorías</h2>";
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
    echo "✅ Método ejecutado<br>";
    echo "Categorías obtenidas: " . count($categorias) . "<br>";
    
    // Mostrar primeras categorías
    echo "<h3>Primeras 3 categorías:</h3>";
    foreach (array_slice($categorias, 0, 3) as $i => $cat) {
        echo ($i+1) . ". " . $cat['nombre'] . " (ID: " . $cat['id'] . ")<br>";
    }
    
} catch (Error $e) {
    echo "❌ <strong>ERROR FATAL:</strong><br>";
    echo "Tipo: " . get_class($e) . "<br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>Trace: " . $e->getTraceAsString() . "</pre>";
    
} catch (Exception $e) {
    echo "❌ <strong>EXCEPCIÓN:</strong><br>";
    echo "Tipo: " . get_class($e) . "<br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "<pre>Trace: " . $e->getTraceAsString() . "</pre>";
}

// Test 5: Simular exactamente lo que hace views/home.php
echo "<h2>🎯 Test 5: Simular views/home.php</h2>";
try {
    $categorias = [];
    require_once 'models/Categorias.php';
    $categoriasModel = new Categorias();
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
    
    echo "✅ Simulación exitosa<br>";
    echo "Total categorías: " . count($categorias) . "<br>";
    
} catch (Exception $e) {
    echo "❌ Falla en simulación: " . $e->getMessage() . "<br>";
}

echo "<h2>🔍 Test 6: Información de error</h2>";
$lastError = error_get_last();
if ($lastError) {
    echo "Último error:<br>";
    echo "Tipo: " . $lastError['type'] . "<br>";
    echo "Mensaje: " . $lastError['message'] . "<br>";
    echo "Archivo: " . $lastError['file'] . "<br>";
    echo "Línea: " . $lastError['line'] . "<br>";
} else {
    echo "No hay errores pendientes<br>";
}
?>