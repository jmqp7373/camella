<?php
echo "🚨 TEST BÁSICO CAMELLA.COM.CO<br>";
echo "Fecha: " . date('Y-m-d H:i:s') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Directorio actual: " . getcwd() . "<br><br>";

echo "<h3>Archivos críticos:</h3>";
$archivos = ['index.php', 'views/home.php', 'partials/header.php', 'partials/footer.php'];
foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - EXISTE (tamaño: " . filesize($archivo) . " bytes)<br>";
    } else {
        echo "❌ $archivo - NO EXISTE<br>";
    }
}

echo "<h3>Test include básico:</h3>";
if (file_exists('partials/header.php')) {
    echo "Intentando incluir header...<br>";
    try {
        include 'partials/header.php';
        echo "Header incluido exitosamente<br>";
    } catch (Exception $e) {
        echo "Error incluyendo header: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Header no existe<br>";
}

echo "<br>Si ves este mensaje, PHP está funcionando correctamente en el servidor.";
?>