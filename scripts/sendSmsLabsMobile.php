<?php
/**
 * Envío de SMS mediante LabsMobile OTP API
 * 
 * Proveedor de SMS para Camella.com.co
 * Genera código OTP de 6 dígitos y magic link
 * Envía SMS usando la API OTP de LabsMobile
 * 
 * @author Camella.com.co
 * @version 1.0
 * @date 2025-10-20
 */

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// ============================================
// CONFIGURACIÓN LABSMOBILE
// ============================================
$LABSMOBILE_USER = 'superadmin@dwc.com.co';
$LABSMOBILE_TOKEN = 'aiAmzJNQAEnittI4nAUOpvWvsYzw8PF9'; // REEMPLAZAR por token real del panel LabsMobile

// ============================================
// LOG DE INICIO Y DEBUGGING
// ============================================
error_log("=== LabsMobile OTP Script Iniciado ===");
error_log("LabsMobile: Método de petición: " . $_SERVER['REQUEST_METHOD']);
error_log("LabsMobile: POST data: " . json_encode($_POST));
error_log("LabsMobile: PHP Version: " . PHP_VERSION);

// ============================================
// VERIFICAR MÉTODO DE PETICIÓN
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Usar POST.'
    ]);
    exit;
}

// ============================================
// VALIDAR CONFIGURACIÓN
// ============================================
if (empty($LABSMOBILE_USER) || empty($LABSMOBILE_TOKEN) || $LABSMOBILE_TOKEN === 'a1izm2JNQAN...') {
    error_log("LabsMobile: Credenciales no configuradas correctamente");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Configuración de LabsMobile incompleta'
    ]);
    exit;
}

// Log de confirmación de configuración
error_log("LabsMobile: Credenciales configuradas para usuario: {$LABSMOBILE_USER}");

// ============================================
// RECIBIR Y VALIDAR NÚMERO DE TELÉFONO
// ============================================
$phone = $_POST['phone'] ?? '';

// Sanitizar: solo números y el símbolo +
$phone = preg_replace('/[^\d+]/', '', $phone);

// Validar formato colombiano: que empiece por 57 y tenga 12 dígitos
if (!preg_match('/^\+?57[3][0-9]{9}$/', $phone)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Número de teléfono inválido'
    ]);
    exit;
}

// Asegurar formato sin + para LabsMobile (formato requerido: 573103951529)
$phoneOriginal = $phone;
$phone = ltrim($phone, '+');

// Log del proceso de limpieza
error_log("LabsMobile: Número original: '{$phoneOriginal}'");
error_log("LabsMobile: Número limpio (sin +): '{$phone}'");

// Verificar que sea exactamente 12 dígitos empezando por 57
if (strlen($phone) !== 12 || !str_starts_with($phone, '57')) {
    error_log("LabsMobile: ADVERTENCIA - Formato de número puede ser problemático: {$phone}");
    error_log("LabsMobile: Longitud: " . strlen($phone) . " (esperado: 12)");
    error_log("LabsMobile: Empieza con 57: " . (str_starts_with($phone, '57') ? 'SÍ' : 'NO'));
} else {
    error_log("LabsMobile: Formato de número CORRECTO: {$phone} (12 dígitos, inicia con 57)");
}

// ============================================
// GENERAR CÓDIGO DE 6 DÍGITOS
// ============================================
$codigo = sprintf('%06d', mt_rand(0, 999999));

// Log del código generado
error_log("LabsMobile: Código generado: {$codigo}");

// ============================================
// GENERAR TOKEN PARA MAGIC LINK (6 caracteres)
// ============================================
$token = substr(bin2hex(random_bytes(4)), 0, 6);

// Log del token generado
error_log("LabsMobile: Token generado: {$token}");

// ============================================
// CONSTRUIR LINK CORTO
// ============================================
$shortLink = "https://camella.com.co/m/{$token}";

// Log del link
error_log("LabsMobile: Link generado: {$shortLink}");

// ============================================
// PREPARAR MENSAJE SMS (SIN URL - SOLO CÓDIGO)
// ============================================
$mensaje = "Tu codigo de acceso Camella es: {$codigo}";

// Log del mensaje
error_log("LabsMobile: Mensaje: {$mensaje}");
error_log("LabsMobile: Destinatario: {$phone}");

// ============================================
// CONFIGURAR AUTENTICACIÓN BÁSICA
// ============================================
$auth = base64_encode("{$LABSMOBILE_USER}:{$LABSMOBILE_TOKEN}");
error_log("LabsMobile: Autenticación Basic configurada");

// ============================================
// CONFIGURAR PETICIÓN A LABSMOBILE OTP API
// ============================================
$params = [
    'env' => 'CamellaApp',
    'sender' => 'INFO',  // Sender que funcionó mejor en las pruebas
    'phone_number' => $phone,
    'message' => $mensaje,
    'digits' => 6
];

$endpoint = 'https://api.labsmobile.com/otp/sendCode?' . http_build_query($params);

// Log de la petición
error_log("LabsMobile: Endpoint: {$endpoint}");
error_log("LabsMobile: Parámetros: " . json_encode($params));

// ============================================
// CONFIGURAR cURL PARA LABSMOBILE API
// ============================================
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Basic {$auth}",
        "Content-Type: application/x-www-form-urlencoded"
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Camella.com.co LabsMobile Client'
]);

// ============================================
// EJECUTAR PETICIÓN
// ============================================
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

// Log de la respuesta
error_log("LabsMobile: HTTP Code: {$httpCode}");
error_log("LabsMobile: Response: {$response}");

// Cerrar cURL
curl_close($ch);

// ============================================
// PROCESAR RESPUESTA DE LABSMOBILE API
// ============================================
header('Content-Type: application/json');

// Verificar si hubo error de cURL/conexión
if (!empty($curlError)) {
    error_log("LabsMobile: cURL Error: {$curlError}");
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión con LabsMobile API'
    ]);
    exit;
}

// LabsMobile OTP devuelve texto plano: "1" = éxito, "0" = error
$responseData = json_decode($response, true);
$isJsonResponse = (json_last_error() === JSON_ERROR_NONE && is_array($responseData));

// Verificar código HTTP exitoso (200-299)
if ($httpCode >= 200 && $httpCode < 300) {
    // LabsMobile OTP devuelve diferentes formatos
    $isSuccess = false;
    
    if ($isJsonResponse && isset($responseData['status'])) {
        // Respuesta JSON completa con campo status
        $isSuccess = ($responseData['status'] == 1);
        error_log("LabsMobile: Formato JSON detectado - status: " . $responseData['status']);
    } else {
        // Respuesta texto plano (formato normal de LabsMobile OTP)
        $trimmedResponse = trim($response);
        $isSuccess = ($trimmedResponse === '1');
        error_log("LabsMobile: Formato texto detectado - respuesta: '{$trimmedResponse}'");
    }
    
    if ($isSuccess) {
        error_log("LabsMobile: SMS enviado exitosamente");
        error_log("LabsMobile: Response format: " . ($isJsonResponse ? 'JSON' : 'Text'));
        error_log("LabsMobile: Raw response: {$response}");
        
        echo json_encode([
            'success' => true,
            'code' => $codigo,
            'token' => $token,
            'link' => $shortLink,
            'status' => 'sent',
            'provider' => 'labsmobile'
        ]);
        exit;
    } else {
        // Error en el envío
        if ($isJsonResponse) {
            $errorDetail = $responseData['message'] ?? 'Error desconocido';
        } else {
            $errorDetail = $response === '0' ? 'Error en LabsMobile API' : "Respuesta inesperada: {$response}";
        }
        
        error_log("LabsMobile: Error en envío");
        error_log("LabsMobile: Raw response: {$response}");
        error_log("LabsMobile: Error detail: {$errorDetail}");
        
        echo json_encode([
            'success' => false,
            'error' => "No se pudo enviar SMS (detalle: {$errorDetail})"
        ]);
        exit;
    }
}

// ============================================
// MANEJO DE ERRORES DE LABSMOBILE API
// ============================================
error_log("LabsMobile: Error al enviar SMS - HTTP {$httpCode}");

// Construir mensaje de error detallado
$errorMessage = 'Error al enviar SMS';

// LabsMobile puede retornar errores en diferentes formatos
if ($isJsonResponse && isset($responseData['message'])) {
    // Formato JSON con message
    $errorMessage = $responseData['message'];
    error_log("LabsMobile: JSON Error message: " . $errorMessage);
} elseif ($isJsonResponse && isset($responseData['error'])) {
    // Formato JSON con error
    $errorMessage = $responseData['error'];
    error_log("LabsMobile: JSON Error: " . $errorMessage);
} else {
    // Respuesta texto plano o error HTTP
    $errorMessage = !empty($response) ? "Error LabsMobile: {$response}" : "Error HTTP {$httpCode}";
    error_log("LabsMobile: Text/HTTP Error: " . $errorMessage);
}

// Respuesta de error
echo json_encode([
    'success' => false,
    'error' => $errorMessage
]);

exit;
?>