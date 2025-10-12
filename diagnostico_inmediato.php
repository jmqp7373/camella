<?php
/**
 * DIAGNÓSTICO INMEDIATO - ¿Por qué el index está en blanco?
 */

echo "<h1>🔍 DIAGNÓSTICO CAMELLA.COM.CO</h1>";
echo "<hr>";

// 1. Verificar archivos críticos
echo "<h2>📁 Archivos críticos:</h2>";
$archivos = [
    'index.php',
    'views/home.php', 
    'partials/header.php',
    'partials/footer.php',
    'config/config.php',
    'config/database.php'
];

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - EXISTS<br>";
    } else {
        echo "❌ $archivo - MISSING<br>";
    }
}

// 2. Verificar qué vista se está cargando
echo "<h2>🎯 Variables de REQUEST:</h2>";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "GET parameters: " . print_r($_GET, true) . "<br>";
echo "POST parameters: " . print_r($_POST, true) . "<br>";

// 3. Simular lógica del index.php
echo "<h2>🔄 Simulación lógica del index:</h2>";

// Lógica copiada del index actual
$view = 'home'; // Por defecto

if (isset($_GET['view'])) {
    $view = sanitize_input($_GET['view']);
    echo "Vista desde GET: $view<br>";
} elseif (isset($_GET['categoria'])) {
    $view = 'categoria';
    echo "Vista categoria detectada<br>";
} else {
    echo "Vista por defecto: home<br>";
}

$viewPath = 'views/' . $view . '.php';
echo "Ruta calculada: $viewPath<br>";

if (file_exists($viewPath)) {
    echo "✅ Archivo de vista encontrado<br>";
    echo "Tamaño: " . filesize($viewPath) . " bytes<br>";
    
    // Mostrar las primeras líneas
    $content = file_get_contents($viewPath);
    echo "Primeras 200 caracteres:<br>";
    echo "<pre>" . htmlspecialchars(substr($content, 0, 200)) . "...</pre>";
    
} else {
    echo "❌ Archivo de vista NO encontrado<br>";
}

// 4. Test de base de datos
echo "<h2>🗄️ Test Base de Datos:</h2>";
try {
    if (file_exists('config/config.php')) {
        require_once 'config/config.php';
        echo "✅ Config cargado<br>";
        
        if (file_exists('config/database.php')) {
            require_once 'config/database.php';
            echo "✅ Database config cargado<br>";
            
            $pdo = getPDO();
            echo "✅ Conexión PDO establecida<br>";
            
        } else {
            echo "❌ config/database.php no existe<br>";
        }
    } else {
        echo "❌ config/config.php no existe<br>";
    }
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

// 5. Información del servidor
echo "<h2>🌐 Información del servidor:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "Current Directory: " . getcwd() . "<br>";

// Función sanitize necesaria
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>