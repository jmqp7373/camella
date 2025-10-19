<?php
/**
 * Diagnóstico de Magic Link - Ver qué está pasando
 * http://localhost/camella.com.co/debug_magic_link.php?token=TU_TOKEN
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Proporciona un token en la URL: ?token=TU_TOKEN");
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug Magic Link</title></head><body>";
echo "<h1>🔍 Debug Magic Link</h1>";
echo "<pre>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Token a verificar: <strong>$token</strong>\n\n";
    
    // 1. Buscar el token
    echo "=== 1. VERIFICAR TOKEN ===\n";
    $stmt = $pdo->prepare("SELECT * FROM magic_links WHERE token = ?");
    $stmt->execute([$token]);
    $link = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$link) {
        echo "❌ Token NO encontrado en la base de datos\n";
        die("</pre></body></html>");
    }
    
    echo "✅ Token encontrado:\n";
    print_r($link);
    
    // 2. Verificar expiración
    echo "\n=== 2. VERIFICAR EXPIRACIÓN ===\n";
    $created = strtotime($link['created_at']);
    $ahora = time();
    $transcurrido = $ahora - $created;
    $horas = floor($transcurrido / 3600);
    
    echo "Creado: {$link['created_at']}\n";
    echo "Ahora: " . date('Y-m-d H:i:s') . "\n";
    echo "Transcurrido: $horas horas\n";
    
    if ($transcurrido > 86400) {
        echo "❌ Token VENCIDO (más de 24 horas)\n";
    } else {
        echo "✅ Token VIGENTE\n";
    }
    
    // 3. Verificar usos
    echo "\n=== 3. VERIFICAR USOS ===\n";
    $usos = $link['usos'] ?? 0;
    echo "Usos actuales: $usos/100\n";
    
    if ($usos >= 100) {
        echo "❌ Límite de usos ALCANZADO\n";
    } else {
        echo "✅ Puede usarse ($usos usos restantes)\n";
    }
    
    // 4. Buscar usuario
    echo "\n=== 4. VERIFICAR USUARIO ===\n";
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$link['phone']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Usuario NO encontrado para teléfono: {$link['phone']}\n";
        die("</pre></body></html>");
    }
    
    echo "✅ Usuario encontrado:\n";
    echo "  ID: {$user['id']}\n";
    echo "  Teléfono: {$user['phone']}\n";
    echo "  Email: " . ($user['email'] ?? 'Sin email') . "\n";
    echo "  Rol: '{$user['role']}'\n";
    echo "  Rol (longitud): " . strlen($user['role']) . " caracteres\n";
    echo "  Rol (hex): " . bin2hex($user['role']) . "\n";
    
    // 5. Determinar redirección
    echo "\n=== 5. DETERMINAR REDIRECCIÓN ===\n";
    $role = strtolower(trim($user['role']));
    echo "Rol procesado: '$role'\n";
    
    $baseUrl = "http://localhost/camella.com.co";
    $redirectMap = [
        'admin' => "$baseUrl/views/admin/dashboard.php",
        'promotor' => "$baseUrl/views/promotor/dashboard.php",
        'publicante' => "$baseUrl/views/publicante/dashboard.php"
    ];
    
    if (isset($redirectMap[$role])) {
        echo "✅ Redirección: {$redirectMap[$role]}\n";
    } else {
        echo "❌ Rol no reconocido, iría a: $baseUrl/index.php?view=home\n";
        echo "\nRoles disponibles:\n";
        foreach ($redirectMap as $r => $url) {
            echo "  - '$r'\n";
        }
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "Token: " . ($link ? "✅" : "❌") . "\n";
    echo "Vigente: " . ($transcurrido <= 86400 ? "✅" : "❌") . "\n";
    echo "Usos OK: " . ($usos < 100 ? "✅" : "❌") . "\n";
    echo "Usuario: " . ($user ? "✅" : "❌") . "\n";
    echo "Rol reconocido: " . (isset($redirectMap[$role]) ? "✅" : "❌") . "\n";
    
    $testUrl = "$baseUrl/index.php?view=m&token=$token";
    echo "\n<a href='$testUrl' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Probar Magic Link</a>\n";
    echo "\nURL completa: <code>$testUrl</code>\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre></body></html>";
?>
