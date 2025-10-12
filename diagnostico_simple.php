<?php
/**
 * Diagnóstico Simple - Verificar qué está pasando
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>Test Diagnóstico</title></head><body>";
echo "<h1>Test de Diagnóstico Camella.com.co</h1>";
echo "<hr>";

echo "<h2>Información del Servidor</h2>";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "<br>";
echo "Servidor: " . $_SERVER['SERVER_NAME'] . "<br>";
echo "Script: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

echo "<h2>Verificaciones de Archivos</h2>";

// Verificar index.php
$indexPath = $_SERVER['DOCUMENT_ROOT'] . '/index.php';
echo "index.php existe: " . (file_exists($indexPath) ? "✅ SÍ" : "❌ NO") . "<br>";

// Verificar views/home.php
$homePath = $_SERVER['DOCUMENT_ROOT'] . '/views/home.php';
echo "views/home.php existe: " . (file_exists($homePath) ? "✅ SÍ" : "❌ NO") . "<br>";

// Verificar partials
$headerPath = $_SERVER['DOCUMENT_ROOT'] . '/partials/header.php';
$footerPath = $_SERVER['DOCUMENT_ROOT'] . '/partials/footer.php';
echo "partials/header.php existe: " . (file_exists($headerPath) ? "✅ SÍ" : "❌ NO") . "<br>";
echo "partials/footer.php existe: " . (file_exists($footerPath) ? "✅ SÍ" : "❌ NO") . "<br>";

echo "<h2>Test de Inclusión</h2>";

try {
    echo "Intentando incluir views/home.php...<br>";
    ob_start();
    include $homePath;
    $content = ob_get_contents();
    ob_end_clean();
    
    echo "✅ Inclusión exitosa. Longitud del contenido: " . strlen($content) . " caracteres<br>";
    echo "Primeros 200 caracteres:<br>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>";
    
} catch (Exception $e) {
    echo "❌ Error en inclusión: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>Enlaces</h2>";
echo "<a href='/'>← Ir al index principal</a><br>";
echo "<a href='/views/home.php'>← Ver views/home.php directamente</a><br>";

echo "</body></html>";
?>