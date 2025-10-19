<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Básico - Paso 1</h1>";

// Test 1: Conexión
echo "<p>1. Probando conexión a base de datos...</p>";
try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db', 'root', '');
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>");
}

// Test 2: Tabla oficios
echo "<p>2. Verificando tabla oficios...</p>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✅ Tabla existe con {$result['total']} registros</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>");
}

// Test 3: Obtener oficios
echo "<p>3. Obteniendo oficios...</p>";
$stmt = $pdo->query("SELECT id, titulo, popular FROM oficios LIMIT 5");
$oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<p style='color: green;'>✅ Encontrados " . count($oficios) . " oficios</p>";

echo "<hr><h2>Oficios encontrados:</h2>";
foreach ($oficios as $oficio) {
    $imagen = $oficio['popular'] == 1 ? '🔥' : '⚪';
    echo "<div style='padding: 10px; margin: 5px; background: #f0f0f0;'>";
    echo "$imagen <strong>{$oficio['titulo']}</strong> (ID: {$oficio['id']}, Popular: {$oficio['popular']})";
    echo "</div>";
}

// Test 4: Verificar imágenes
echo "<hr><h2>Test de imágenes:</h2>";
$img1Path = __DIR__ . '/assets/images/app/candela1.png';
$img0Path = __DIR__ . '/assets/images/app/candela0.png';

echo "<p>Ruta candela1: $img1Path</p>";
echo file_exists($img1Path) 
    ? "<p style='color: green;'>✅ candela1.png existe</p>" 
    : "<p style='color: red;'>❌ candela1.png NO existe</p>";

echo "<p>Ruta candela0: $img0Path</p>";
echo file_exists($img0Path) 
    ? "<p style='color: green;'>✅ candela0.png existe</p>" 
    : "<p style='color: red;'>❌ candela0.png NO existe</p>";

// Test 5: Verificar OficioController.php
echo "<hr><h2>Test de controlador:</h2>";
$controllerPath = __DIR__ . '/controllers/OficioController.php';
echo "<p>Ruta: $controllerPath</p>";
echo file_exists($controllerPath) 
    ? "<p style='color: green;'>✅ OficioController.php existe</p>" 
    : "<p style='color: red;'>❌ OficioController.php NO existe</p>";

echo "<hr><h2>✅ Todas las verificaciones básicas completadas</h2>";
echo "<p><a href='test_candela_funcional.php'>➡️ Siguiente: Test funcional con clicks</a></p>";
?>
