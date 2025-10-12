<?php
/**
 * INDEX SIMPLE PARA DEBUGGING
 * 
 * Versión ultra simplificada para identificar el problema
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Camella</title></head><body>";
echo "<h1>🚨 INDEX TEST - CAMELLA.COM.CO</h1>";

echo "<p>✅ PHP funcionando correctamente</p>";

// Test 1: Verificar archivos
echo "<h2>Test 1: Archivos</h2>";
if (file_exists('views/home.php')) {
    echo "<p>✅ views/home.php existe</p>";
    $size = filesize('views/home.php');
    echo "<p>Tamaño: $size bytes</p>";
} else {
    echo "<p>❌ views/home.php NO existe</p>";
}

if (file_exists('partials/header.php')) {
    echo "<p>✅ partials/header.php existe</p>";
} else {
    echo "<p>❌ partials/header.php NO existe</p>";
}

// Test 2: Intentar incluir header
echo "<h2>Test 2: Incluir Header</h2>";
try {
    ob_start();
    include 'partials/header.php';
    $header_content = ob_get_contents();
    ob_end_clean();
    echo "<p>✅ Header incluido sin errores</p>";
    echo "<p>Tamaño header: " . strlen($header_content) . " bytes</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en header: " . $e->getMessage() . "</p>";
}

// Test 3: Intentar incluir home
echo "<h2>Test 3: Incluir Home</h2>";
try {
    ob_start();
    include 'views/home.php';
    $home_content = ob_get_contents();
    ob_end_clean();
    echo "<p>✅ Home incluido sin errores</p>";
    echo "<p>Tamaño home: " . strlen($home_content) . " bytes</p>";
} catch (Exception $e) {
    echo "<p>❌ Error en home: " . $e->getMessage() . "</p>";
}

// Test 4: Variables de entorno
echo "<h2>Test 4: Variables</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Current dir: " . getcwd() . "</p>";

echo "<p><strong>Si ves este mensaje, PHP está funcionando correctamente</strong></p>";
echo "</body></html>";
?>