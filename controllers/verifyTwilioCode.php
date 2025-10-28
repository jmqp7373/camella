<?php
/**
 * verifyTwilioCode.php
 * 
 * Controlador para verificar códigos de verificación de 6 dígitos
 * y realizar el login del usuario
 */

declare(strict_types=1);

// Inicializar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar headers y error reporting
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Log de debug
error_log("🚀 verifyTwilioCode.php iniciado - " . date('Y-m-d H:i:s'));

// -------------------- Cargar configuración y BD --------------------
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// -------------------- Función para formatear E.164 --------------------
function formatToE164(string $phone): string {
    $cleaned = preg_replace('/\D+/', '', $phone);
    $cleaned = ltrim($cleaned, '0');
    
    if (empty($cleaned)) {
        return '';
    }
    
    if (strpos($cleaned, '57') === 0) {
        return '+' . $cleaned;
    }
    
    if (strlen($cleaned) === 10 && $cleaned[0] === '3') {
        return '+57' . $cleaned;
    }
    
    return '+57' . $cleaned;
}

// -------------------- Validar método POST --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        'ok' => false,
        'msg' => 'Método no permitido. Solo se acepta POST.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    exit;
}

// -------------------- Procesar datos de entrada --------------------
$phone = $_POST['phone'] ?? '';
$code = $_POST['code'] ?? '';

$phone = trim($phone);
$code = trim($code);

error_log("📱 Datos recibidos - Teléfono: " . $phone . " | Código: " . $code);

if (empty($phone) || empty($code)) {
    $response = [
        'ok' => false,
        'msg' => 'Teléfono y código son requeridos.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Datos incompletos");
    exit;
}

// Formatear número a E.164
$phoneE164 = formatToE164($phone);
error_log("📱 Teléfono formateado: " . $phoneE164);

// -------------------- Validar código de sesión --------------------
if (!isset($_SESSION['verification_code']) || 
    !isset($_SESSION['verification_phone']) || 
    !isset($_SESSION['verification_time'])) {
    $response = [
        'ok' => false,
        'msg' => 'No hay código de verificación activo. Solicita uno nuevo.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ No hay código activo en sesión");
    exit;
}

// -------------------- Verificar expiración (5 minutos) --------------------
$codeTime = $_SESSION['verification_time'];
$currentTime = time();
$timeDiff = $currentTime - $codeTime;

if ($timeDiff > 300) { // 5 minutos = 300 segundos
    // Limpiar sesión
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_phone']);
    unset($_SESSION['verification_time']);
    
    $response = [
        'ok' => false,
        'msg' => 'Código incorrecto o expirado.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Código expirado - Tiempo transcurrido: " . $timeDiff . " segundos");
    exit;
}

// -------------------- Verificar código y teléfono --------------------
$sessionCode = $_SESSION['verification_code'];
$sessionPhone = $_SESSION['verification_phone'];

error_log("🔍 Comparando - Sesión: {$sessionPhone} vs Recibido: {$phoneE164}");
error_log("🔍 Comparando - Código sesión: {$sessionCode} vs Recibido: {$code}");

if ($sessionCode !== $code || $sessionPhone !== $phoneE164) {
    $response = [
        'ok' => false,
        'msg' => 'Código incorrecto o expirado.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Código o teléfono no coinciden");
    exit;
}

// -------------------- Código válido: buscar/crear usuario --------------------
try {
    $pdo = getPDO();
    
    // Buscar usuario existente
    $stmt = $pdo->prepare("
        SELECT id, phone, role, created_at 
        FROM users 
        WHERE phone = :phone 
        LIMIT 1
    ");
    $stmt->execute([':phone' => $phoneE164]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Usuario existente
        error_log("👤 Usuario existente encontrado - ID: " . $user['id'] . " | Rol: " . $user['role']);
        $userId = $user['id'];
        $userRole = $user['role'];
    } else {
        // Crear nuevo usuario
        error_log("👤 Creando nuevo usuario para: " . $phoneE164);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (phone, role, created_at, updated_at) 
            VALUES (:phone, 'publicante', NOW(), NOW())
        ");
        $stmt->execute([':phone' => $phoneE164]);
        
        $userId = $pdo->lastInsertId();
        $userRole = 'publicante';
        
        error_log("✅ Nuevo usuario creado - ID: " . $userId);
    }
    
    // -------------------- Crear sesión de usuario (igual que MagicLinkController) --------------------
    $_SESSION['usuario'] = $userId; // Variable que esperan los dashboards
    $_SESSION['user_id'] = $userId; // Variable adicional para compatibilidad
    $_SESSION['user_phone'] = $phoneE164;
    $_SESSION['user_email'] = $user['email'] ?? '';
    $_SESSION['role'] = $userRole; // Variable que esperan los dashboards
    $_SESSION['user_role'] = $userRole; // Variable adicional para compatibilidad
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['login_expires'] = time() + (24 * 60 * 60);
    
    // Si el usuario tiene un rol original guardado, mantenerlo
    if (isset($user['original_role'])) {
        $_SESSION['original_role'] = $user['original_role'];
    } else {
        $_SESSION['original_role'] = $userRole;
    }
    
    // Limpiar códigos de verificación para seguridad
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_phone']);
    unset($_SESSION['verification_time']);
    
    error_log("✅ Sesión creada para usuario ID: " . $userId . " con rol: " . $userRole);
    error_log("  - usuario: " . $_SESSION['usuario']);
    error_log("  - role: " . $_SESSION['role']);
    
    // -------------------- Determinar URL de redirección (URLs absolutas como el sistema anterior) --------------------
    // Detectar si estamos en localhost o producción
    $httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isLocalhost = (
        $httpHost === 'localhost' ||
        strpos($httpHost, 'localhost:') === 0 ||
        strpos($httpHost, '127.0.0.1') === 0 ||
        strpos($httpHost, '.ngrok') !== false
    );
    
    if ($isLocalhost) {
        $baseUrl = "http://{$httpHost}/camella.com.co";
    } else {
        $baseUrl = "https://camella.com.co";
    }
    
    $role = strtolower(trim($userRole));
    $redirectMap = [
        'admin' => "$baseUrl/views/admin/dashboard.php",
        'promotor' => "$baseUrl/views/promotor/dashboard.php",
        'publicante' => "$baseUrl/views/publicante/dashboard.php"
    ];
    
    if (isset($redirectMap[$role])) {
        $redirectUrl = $redirectMap[$role];
        error_log("  - Redirigiendo a: $redirectUrl");
    } else {
        $redirectUrl = "$baseUrl/index.php?view=home";
        error_log("  - Rol '$role' no reconocido, redirigiendo a home");
    }
    
    $response = [
        'ok' => true,
        'msg' => 'Inicio de sesión exitoso.',
        'redirect' => $redirectUrl
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("✅ Login completado exitosamente para usuario ID: " . $userId . " - Redirigiendo a: " . $redirectUrl);
    
} catch (Exception $e) {
    error_log("❌ Error de base de datos: " . $e->getMessage());
    
    $response = [
        'ok' => false,
        'msg' => 'Error interno del servidor. Intenta nuevamente.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}