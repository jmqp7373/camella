<?php
/**
 * Envío de SMS mediante Brevo (ex-Sendinblue)
 * 
 * Proveedor de SMS para Camella.com.co
 * Genera código OTP de 6 dígitos y magic link
 * Envía SMS usando la API de Brevo vía cURL
 * 
 * @author Camella.com.co
 * @version 1.0
 * @date 2025-10-21
 */

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// ============================================
// LOG DE INICIO Y DEBUGGING
// ============================================
error_log("=== Brevo SMS Script Iniciado ===");
error_log("Brevo: Método de petición: " . $_SERVER['REQUEST_METHOD']);
error_log("Brevo: POST data: " . json_encode($_POST));
error_log("Brevo: PHP Version: " . PHP_VERSION);

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
// CONFIGURACIÓN BREVO
// ============================================
$apiKey = 'szPL4ZFXNamqkfUb';
$baseUrl = 'https://camella.com.co/m/';
$tag = 'magicLink';

// Log de confirmación de configuración
error_log("Brevo: API Key configurada correctamente");

// ============================================
// RECIBIR Y VALIDAR NÚMERO DE TELÉFONO
// ============================================
$phone = $_POST['phone'] ?? '';

if (!$phone) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Número de teléfono no recibido'
    ]);
    exit;
}

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

// Asegurar formato con + para Brevo
if (!str_starts_with($phone, '+')) {
    $phone = '+' . $phone;
}

// Log del número procesado
error_log("Brevo: Número procesado: {$phone}");

// ============================================
// GENERAR CÓDIGO DE 6 DÍGITOS
// ============================================
$code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

// Log del código generado
error_log("Brevo: Código generado: {$code}");

// ============================================
// GENERAR HASH CORTO PARA MAGIC LINK
// ============================================
$linkHash = substr(md5(uniqid($code, true)), 0, 6);
$shortLink = $baseUrl . $linkHash;

// Log del link generado
error_log("Brevo: Link generado: {$shortLink}");

// ============================================
// CONSTRUIR CONTENIDO DEL SMS
// ============================================
$message = "Tu codigo Camella es: {$code}. Accede en {$shortLink}";

// Log del mensaje
error_log("Brevo: Mensaje: {$message}");

// ============================================
// CONFIGURAR PETICIÓN A BREVO API
// ============================================
$endpoint = 'https://api.brevo.com/v3/transactionalSMS/sms';

// Preparar datos para Brevo API
$requestData = [
    'type' => 'transactional',
    'sender' => 'Camella',
    'recipient' => $phone,
    'content' => $message,
    'tag' => $tag
];

// Convertir a JSON
$jsonData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

// Log de la petición
error_log("Brevo: Endpoint: {$endpoint}");
error_log("Brevo: Request Data: {$jsonData}");

// ============================================
// CONFIGURAR cURL PARA BREVO API
// ============================================
$ch = curl_init($endpoint);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonData,
    CURLOPT_HTTPHEADER => [
        'api-key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Camella.com.co Brevo SMS Client'
]);

// ============================================
// EJECUTAR PETICIÓN
// ============================================
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

// Log de la respuesta
error_log("Brevo: HTTP Code: {$httpCode}");
error_log("Brevo: Response: {$response}");

// Cerrar cURL
curl_close($ch);

// ============================================
// PROCESAR RESPUESTA DE BREVO API
// ============================================
header('Content-Type: application/json');

// Verificar si hubo error de cURL/conexión
if (!empty($curlError)) {
    error_log("Brevo: cURL Error: {$curlError}");
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión con Brevo API'
    ]);
    exit;
}

// Decodificar respuesta JSON
$responseData = json_decode($response, true);

// Verificar código HTTP exitoso (200-299)
if ($httpCode >= 200 && $httpCode < 300) {
    // Brevo devuelve diferentes campos según la respuesta
    $messageId = null;
    $status = 'sent';
    
    if (isset($responseData['messageId'])) {
        $messageId = $responseData['messageId'];
    } elseif (isset($responseData['id'])) {
        $messageId = $responseData['id'];
    }
    
    if (isset($responseData['status'])) {
        $status = $responseData['status'];
    }
    
    error_log("Brevo: SMS enviado exitosamente");
    error_log("Brevo: Message ID: " . ($messageId ?? 'N/A'));
    error_log("Brevo: Status: " . $status);
    
    echo json_encode([
        'success' => true,
        'status' => $status,
        'code' => $code,
        'link' => $shortLink,
        'message_id' => $messageId,
        'brevo_response' => $responseData,
        'provider' => 'brevo'
    ]);
    exit;
}

// ============================================
// MANEJO DE ERRORES DE BREVO API
// ============================================
error_log("Brevo: Error al enviar SMS - HTTP {$httpCode}");

// Construir mensaje de error detallado
$errorMessage = 'Error al enviar SMS';

// Brevo puede retornar errores en diferentes formatos
if (isset($responseData['message'])) {
    // Formato message
    $errorMessage = $responseData['message'];
    error_log("Brevo: Error message: " . $errorMessage);
} elseif (isset($responseData['error'])) {
    // Formato error
    $errorMessage = $responseData['error'];
    error_log("Brevo: Error: " . $errorMessage);
} elseif (isset($responseData['code']) && isset($responseData['detail'])) {
    // Formato Brevo específico
    $errorMessage = $responseData['detail'] . ' (Code: ' . $responseData['code'] . ')';
    error_log("Brevo: Error code: " . $responseData['code'] . ", Detail: " . $responseData['detail']);
} else {
    // Error genérico con código HTTP
    $errorMessage = 'Error en Brevo API (HTTP ' . $httpCode . ')';
    error_log("Brevo: Error genérico, respuesta: " . json_encode($responseData));
}

// Respuesta de error
echo json_encode([
    'success' => false,
    'error' => $errorMessage,
    'brevo_response' => $responseData
]);

exit;
?>