<?php
/**
 * Envío de SMS mediante Bird API (API moderna)
 * 
 * Proveedor de SMS para Camella.com.co
 * Genera código OTP de 6 dígitos y magic link
 * Envía SMS usando la API moderna de Bird (https://api.bird.com/messages/v1)
 * 
 * @author Camella.com.co
 * @version 2.0
 * @date 2025-10-20
 */

// Configurar zona horaria
date_default_timezone_set('America/Bogota');

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// ============================================
// LOG DE INICIO Y DEBUGGING
// ============================================
error_log("=== Bird API Script Iniciado ===");
error_log("Bird API: Método de petición: " . $_SERVER['REQUEST_METHOD']);
error_log("Bird API: POST data: " . json_encode($_POST));
error_log("Bird API: PHP Version: " . PHP_VERSION);

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
// VALIDAR API KEY
// ============================================
if (!defined('MESSAGEBIRD_API_KEY') || empty(MESSAGEBIRD_API_KEY)) {
    error_log("Bird API: API Key no configurada o vacía");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'API Key de Bird no configurada'
    ]);
    exit;
}

// Log de confirmación de configuración
error_log("Bird API: API Key configurada correctamente");

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

// Asegurar formato con + para Bird API
if (substr($phone, 0, 1) !== '+') {
    $phone = '+' . $phone;
}

// ============================================
// GENERAR CÓDIGO DE 6 DÍGITOS
// ============================================
$codigo = sprintf('%06d', mt_rand(0, 999999));

// Log del código generado
error_log("Bird API: Código generado: {$codigo}");

// ============================================
// GENERAR TOKEN PARA MAGIC LINK (6 caracteres)
// ============================================
$token = substr(bin2hex(random_bytes(4)), 0, 6);

// Log del token generado
error_log("Bird API: Token generado: {$token}");

// ============================================
// CONSTRUIR LINK CORTO
// ============================================
$shortLink = "https://camella.com.co/m/{$token}";

// Log del link
error_log("Bird API: Link generado: {$shortLink}");

// ============================================
// PREPARAR MENSAJE SMS
// ============================================
$mensaje = "Código: {$codigo}\n{$shortLink}";

// Log del mensaje
error_log("Bird API: Mensaje: {$mensaje}");
error_log("Bird API: Destinatario: {$phone}");

// ============================================
// CONFIGURAR PETICIÓN A BIRD API (HÍBRIDA)
// ============================================
// Nota: Usando endpoint clásico con formato JSON moderno
$endpoint = 'https://rest.messagebird.com/messages';

// Preparar datos para MessageBird API (formato URL-encoded clásico)
$requestData = [
    'recipients' => $phone,
    'originator' => 'Camella',
    'body' => $mensaje
];

// Convertir a formato URL-encoded (formato original que funcionaba)
$postFields = http_build_query($requestData);

// Log de la petición
error_log("Bird API: Endpoint: {$endpoint}");
error_log("Bird API: Request Data: {$postFields}");
error_log("Bird API: Usando formato clásico URL-encoded");

// ============================================
// CONFIGURAR cURL PARA MESSAGEBIRD API CLÁSICA
// ============================================
$ch = curl_init($endpoint);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_HTTPHEADER => [
        'Authorization: AccessKey ' . MESSAGEBIRD_API_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'Camella.com.co MessageBird Client'
]);

// ============================================
// EJECUTAR PETICIÓN
// ============================================
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

// Log de la respuesta
error_log("Bird API: HTTP Code: {$httpCode}");
error_log("Bird API: Response: {$response}");

// Cerrar cURL
curl_close($ch);

// ============================================
// PROCESAR RESPUESTA DE BIRD API
// ============================================
header('Content-Type: application/json');

// Verificar si hubo error de cURL/conexión
if (!empty($curlError)) {
    error_log("Bird API: cURL Error: {$curlError}");
    echo json_encode([
        'success' => false,
        'error' => 'No se pudo conectar con Bird API'
    ]);
    exit;
}

// Decodificar respuesta JSON
$responseData = json_decode($response, true);

// Verificar código HTTP exitoso (200-299)
if ($httpCode >= 200 && $httpCode < 300) {
    // API clásica de MessageBird retorna 'id' en lugar de 'message_id'
    if (isset($responseData['id'])) {
        error_log("Bird API: SMS enviado exitosamente");
        error_log("Bird API: Message ID: " . $responseData['id']);
        error_log("Bird API: Recipients: " . json_encode($responseData['recipients'] ?? []));
        
        echo json_encode([
            'success' => true,
            'code' => $codigo,
            'token' => $token,
            'link' => $shortLink,
            'message_id' => $responseData['id'],
            'status' => 'sent' // API clásica no retorna status específico
        ]);
        exit;
    } else {
        // Respuesta exitosa pero sin ID
        error_log("Bird API: Respuesta exitosa pero sin ID");
        error_log("Bird API: Response data: " . json_encode($responseData));
        
        echo json_encode([
            'success' => true,
            'code' => $codigo,
            'token' => $token,
            'link' => $shortLink,
            'message_id' => null,
            'status' => 'sent'
        ]);
        exit;
    }
}

// ============================================
// MANEJO DE ERRORES DE BIRD API
// ============================================
error_log("Bird API: Error al enviar SMS - HTTP {$httpCode}");

// Construir mensaje de error detallado
$errorMessage = 'Error al enviar SMS';

// MessageBird puede retornar errores en diferentes formatos
if (isset($responseData['errors'])) {
    // Formato array de errores
    $errors = [];
    foreach ($responseData['errors'] as $error) {
        $errorDesc = $error['description'] ?? $error['message'] ?? 'Error desconocido';
        $errors[] = $errorDesc;
        
        // Log específico para errores de API Key
        if ($error['code'] == 2) {
            error_log("Bird API: ERROR CRÍTICO - API Key inválida o expirada");
            error_log("Bird API: Verificar MESSAGEBIRD_API_KEY en config.php");
            error_log("Bird API: Contactar proveedor para renovar API Key");
        }
    }
    $errorMessage = implode(', ', $errors);
    error_log("Bird API: Errores: " . json_encode($responseData['errors']));
} elseif (isset($responseData['error']['message'])) {
    // Formato error.message
    $errorMessage = $responseData['error']['message'];
    error_log("Bird API: Error message: " . $errorMessage);
} elseif (isset($responseData['message'])) {
    // Formato message simple
    $errorMessage = $responseData['message'];
    error_log("Bird API: Message: " . $errorMessage);
} else {
    // Error genérico con código HTTP
    $errorMessage = 'Error en MessageBird API (HTTP ' . $httpCode . ')';
    error_log("Bird API: Error genérico, respuesta: " . json_encode($responseData));
}

// Respuesta de error
echo json_encode([
    'success' => false,
    'error' => $errorMessage
]);

exit;
?>
