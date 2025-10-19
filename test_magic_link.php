<?php
/**
 * Test del sistema Magic Link
 * http://localhost/camella.com.co/test_magic_link.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión
try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>Test Magic Link</title>";
echo "<style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.success { color: green; }
.error { color: red; }
.info { color: blue; }
table { border-collapse: collapse; width: 100%; margin: 20px 0; background: white; }
th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
th { background: #4CAF50; color: white; }
.btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
</style></head><body>";

echo "<h1>🔗 Test Magic Link System</h1>";

// PASO 1: Verificar tabla
echo "<h2>1. Verificar tabla magic_links</h2>";
$stmt = $pdo->query("SHOW TABLES LIKE 'magic_links'");
if ($stmt->fetch()) {
    echo "<p class='success'>✅ Tabla 'magic_links' existe</p>";
    
    // Mostrar registros existentes
    $stmt = $pdo->query("SELECT * FROM magic_links ORDER BY created_at DESC LIMIT 10");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($links) > 0) {
        echo "<h3>Últimos 10 magic links:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Token</th><th>Teléfono</th><th>Creado</th><th>Usos</th><th>Acción</th></tr>";
        foreach ($links as $link) {
            $edad = time() - strtotime($link['created_at']);
            $horas = floor($edad / 3600);
            $vigente = $edad < 86400 ? '✅' : '❌';
            $usos = isset($link['usos']) ? $link['usos'] : 0;
            
            echo "<tr>";
            echo "<td>{$link['id']}</td>";
            echo "<td><code>{$link['token']}</code></td>";
            echo "<td>{$link['phone']}</td>";
            echo "<td>{$link['created_at']}<br><small>($horas horas) $vigente</small></td>";
            echo "<td>$usos/100</td>";
            echo "<td><a href='index.php?view=m&token={$link['token']}' class='btn' target='_blank'>Probar</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ️ No hay magic links registrados aún</p>";
    }
    
} else {
    echo "<p class='error'>❌ Tabla no existe. <a href='setup_magic_links.php'>Crear tabla</a></p>";
}

// PASO 2: Crear magic link de prueba
echo "<h2>2. Crear Magic Link de Prueba</h2>";

if (isset($_POST['crear_test'])) {
    $testPhone = $_POST['test_phone'];
    $testToken = substr(md5(uniqid(rand(), true)), 0, 12);
    
    try {
        // Crear magic link sin columna 'code' si no existe
        $stmt = $pdo->prepare("INSERT INTO magic_links (token, phone, created_at, usos) VALUES (?, ?, NOW(), 0)");
        $stmt->execute([$testToken, $testPhone]);
        
        echo "<p class='success'>✅ Magic Link creado exitosamente</p>";
        echo "<p><strong>Token:</strong> <code>$testToken</code></p>";
        echo "<p><strong>Teléfono:</strong> $testPhone</p>";
        echo "<p><strong>Prueba estas URLs:</strong></p>";
        echo "<ul>";
        echo "<li><a href='index.php?view=m&token=$testToken' target='_blank'>Formato GET: index.php?view=m&token=$testToken</a></li>";
        echo "<li><a href='m/$testToken' target='_blank'>Formato amigable: m/$testToken</a></li>";
        echo "</ul>";
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
}

// Formulario para crear test
echo "<form method='POST'>";
echo "<p>Teléfono para el test: <input type='text' name='test_phone' value='+573001234567' required></p>";
echo "<button type='submit' name='crear_test' class='btn'>Crear Magic Link de Prueba</button>";
echo "</form>";

// PASO 3: Verificar usuarios
echo "<h2>3. Usuarios en la base de datos</h2>";
$stmt = $pdo->query("SELECT * FROM users LIMIT 5");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($users) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Teléfono</th><th>Email</th><th>Rol</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . ($user['phone'] ?? 'Sin teléfono') . "</td>";
        echo "<td>" . ($user['email'] ?? 'Sin email') . "</td>";
        echo "<td>" . ($user['role'] ?? 'Sin rol') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No hay usuarios en la base de datos</p>";
}

echo "</body></html>";
?>
