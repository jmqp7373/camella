<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Directo de Base de Datos</h1>";

// Conexión directa
try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
} catch (PDOException $e) {
    die("<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>");
}

// Test 1: Buscar oficio ID 1
echo "<h2>Test 1: Buscar oficio ID 1</h2>";
$sql = "SELECT id, categoria_id, titulo, popular, activo, created_at, updated_at 
        FROM oficios 
        WHERE id = :id 
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => 1]);
$oficio = $stmt->fetch(PDO::FETCH_ASSOC);

if ($oficio) {
    echo "<p style='color: green;'>✅ Oficio encontrado:</p>";
    echo "<pre>" . print_r($oficio, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Oficio ID 1 NO encontrado</p>";
}

// Test 2: Listar primeros 5 oficios
echo "<h2>Test 2: Primeros 5 oficios en la tabla</h2>";
$stmt = $pdo->query("SELECT id, titulo, popular FROM oficios LIMIT 5");
$oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Título</th><th>Popular</th><th>Acción</th></tr>";
foreach ($oficios as $ofi) {
    echo "<tr>";
    echo "<td>{$ofi['id']}</td>";
    echo "<td>{$ofi['titulo']}</td>";
    echo "<td>{$ofi['popular']}</td>";
    echo "<td><button onclick='testToggle({$ofi['id']})'>Test Toggle</button></td>";
    echo "</tr>";
}
echo "</table>";

// Test 3: Probar OficioModel directamente
echo "<h2>Test 3: Probar OficioModel::togglePopular()</h2>";
require_once __DIR__ . '/models/OficioModel.php';

$modelo = new OficioModel();
echo "<p>Modelo creado: " . (is_object($modelo) ? "✅" : "❌") . "</p>";

// Probar con ID 1
$resultado = $modelo->togglePopular(1);
echo "<p>Resultado de togglePopular(1):</p>";
echo "<pre>" . print_r($resultado, true) . "</pre>";

?>

<script>
function testToggle(id) {
    console.log('Testing toggle for ID:', id);
    
    fetch(`controllers/OficioController.php?action=togglePopular&id=${id}`)
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data);
            alert(JSON.stringify(data, null, 2));
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
}
</script>
