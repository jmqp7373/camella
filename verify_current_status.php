<?php
echo "🔍 VERIFICACIÓN ESTADO ACTUAL - " . date('Y-m-d H:i:s') . "<br><br>";

echo "<h3>1. Test del index.php actual:</h3>";
try {
    ob_start();
    include 'index.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    if (strlen($output) > 100) {
        echo "✅ Index.php genera contenido (" . strlen($output) . " bytes)<br>";
        echo "✅ No errores fatales detectados<br>";
    } else {
        echo "❌ Index.php genera muy poco contenido<br>";
        echo "Contenido: " . htmlspecialchars(substr($output, 0, 200)) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error ejecutando index.php: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Test directo de archivos críticos:</h3>";
echo "views/home.php: " . (file_exists('views/home.php') ? '✅ EXISTS' : '❌ MISSING') . "<br>";
echo "partials/header.php: " . (file_exists('partials/header.php') ? '✅ EXISTS' : '❌ MISSING') . "<br>";
echo "partials/footer.php: " . (file_exists('partials/footer.php') ? '✅ EXISTS' : '❌ MISSING') . "<br>";

echo "<h3>3. ¿Funciona el sitio en este momento?</h3>";
echo "Accede a <a href='https://camella.com.co/'>https://camella.com.co/</a> para verificar<br>";
echo "Si ves contenido completo = ✅ Funciona<br>";
echo "Si ves página en blanco = ❌ Sigue fallando<br>";
?>