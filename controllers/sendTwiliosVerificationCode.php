<?php
/**
 * sendTwiliosVerificationCode.php
 * 
 * Controlador para envío de códigos de verificación de 6 dígitos vía Twilio SMS
 * Reemplaza el sistema de MagicLink por códigos temporales
 */

declare(strict_types=1);
session_start();

// Configurar headers y error reporting
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Log de debug
error_log("🚀 sendTwiliosVerificationCode.php iniciado - " . date('Y-m-d H:i:s'));

// -------------------- Cargar configuración --------------------
require_once __DIR__ . '/../config/config.php';

// -------------------- Dependencias --------------------
$autoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoload)) {
    $response = [
        'ok' => false,
        'msg' => 'Falta vendor/autoload.php. Instala dependencias primero: composer require twilio/sdk:^7'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    error_log("❌ Error: Falta vendor/autoload.php");
    exit;
}
require $autoload;

use Twilio\Rest\Client;
use Twilio\Exceptions\RestException;

// -------------------- Función para formatear E.164 --------------------
function formatToE164(string $phone): string {
    // Limpiar el número de caracteres no numéricos
    $cleaned = preg_replace('/\D+/', '', $phone);
    
    // Remover ceros iniciales
    $cleaned = ltrim($cleaned, '0');
    
    if (empty($cleaned)) {
        return '';
    }
    
    // Si ya tiene código de país colombiano (57), usarlo
    if (strpos($cleaned, '57') === 0) {
        return '+' . $cleaned;
    }
    
    // Si es número móvil colombiano típico (10 dígitos, empieza con 3)
    if (strlen($cleaned) === 10 && $cleaned[0] === '3') {
        return '+57' . $cleaned;
    }
    
    // Fallback: asumir que es colombiano
    return '+57' . $cleaned;
}

// -------------------- Función para generar código de 6 dígitos --------------------
function generateVerificationCode(): string {
    return str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
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

// -------------------- Validar extensiones PHP necesarias --------------------
if (!extension_loaded('curl') || !extension_loaded('openssl')) {
    $response = [
        'ok' => false,
        'msg' => 'Faltan extensiones de PHP: cURL y/o OpenSSL. Habilítalas en php.ini.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Faltan extensiones PHP requeridas");
    exit;
}

// -------------------- Credenciales Twilio --------------------
// Usar credenciales del archivo config.php
$ACCOUNT_SID = defined('TWILIO_SID') ? TWILIO_SID : getenv('TWILIO_ACCOUNT_SID');
$AUTH_TOKEN = defined('TWILIO_AUTH_TOKEN') ? TWILIO_AUTH_TOKEN : getenv('TWILIO_AUTH_TOKEN');  
$FROM_NUMBER = defined('TWILIO_FROM_NUMBER') ? TWILIO_FROM_NUMBER : getenv('TWILIO_FROM_NUMBER');

// Validar que las credenciales estén configuradas
if (empty($ACCOUNT_SID) || $ACCOUNT_SID === 'YOUR_ACCOUNT_SID' || 
    empty($AUTH_TOKEN) || $AUTH_TOKEN === 'YOUR_AUTH_TOKEN' ||
    empty($FROM_NUMBER) || $FROM_NUMBER === '+1XXXXXXXXXX') {
    $response = [
        'ok' => false,
        'msg' => 'Credenciales de Twilio no configuradas. Verifica config.php o variables de entorno.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Credenciales de Twilio no configuradas correctamente");
    exit;
}

error_log("✅ Credenciales Twilio cargadas - SID: " . substr($ACCOUNT_SID, 0, 10) . "...");


// -------------------- Procesar datos de entrada --------------------
$phone = $_POST['phone'] ?? '';
$phone = trim($phone);

error_log("📱 Teléfono recibido: " . $phone);

if (empty($phone)) {
    $response = [
        'ok' => false,
        'msg' => 'Número de teléfono requerido.'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Teléfono vacío");
    exit;
}

// Formatear número a E.164
$phoneE164 = formatToE164($phone);
error_log("📱 Teléfono formateado: " . $phoneE164);

if (empty($phoneE164) || strlen($phoneE164) < 12) {
    $response = [
        'ok' => false,
        'msg' => 'Número de teléfono inválido. Usa formato colombiano (ej: 3001234567).'
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    error_log("❌ Teléfono inválido después de formateo: " . $phoneE164);
    exit;
}

// -------------------- Generar código de verificación --------------------
$verificationCode = generateVerificationCode();
error_log("🔑 Código generado: " . $verificationCode);

// -------------------- Guardar en sesión --------------------
$_SESSION['verification_code'] = $verificationCode;
$_SESSION['verification_phone'] = $phoneE164;
$_SESSION['verification_time'] = time();
error_log("💾 Código guardado en sesión para: " . $phoneE164);

// -------------------- Guardar en base de datos --------------------
try {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getPDO();
    
    // Eliminar códigos anteriores del mismo teléfono
    $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE phone = ?");
    $stmt->execute([$phoneE164]);
    
    // Insertar nuevo código
    $stmt = $pdo->prepare("
        INSERT INTO verification_codes (phone, code, created_at, expires_at)
        VALUES (?, ?, NOW(), NOW() + INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$phoneE164, $verificationCode]);
    error_log("💾 Código guardado en BD para: " . $phoneE164);
    
} catch (Exception $e) {
    error_log("⚠️ Error guardando en BD: " . $e->getMessage());
    // Continuar aunque falle la BD
}

// -------------------- Construir enlace corto --------------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$shortLink = "{$protocol}://{$host}/camella.com.co/in/{$verificationCode}";

// -------------------- Preparar mensaje SMS --------------------
$smsBody = "Tu código de verificación es: {$verificationCode}\n";
$smsBody .= "Accede directamente en: {$shortLink}\n";
$smsBody .= "Válido por 10 minutos.";

// -------------------- Envío vía Twilio --------------------
try {
    $client = new Client($ACCOUNT_SID, $AUTH_TOKEN);
    
    error_log("📤 Enviando SMS a: " . $phoneE164);
    
    $message = $client->messages->create($phoneE164, [
        'from' => $FROM_NUMBER,
        'body' => $smsBody,
    ]);
    
    $response = [
        'ok' => true,
        'msg' => 'Código enviado correctamente',
        'sid' => $message->sid,
        'status' => $message->status,
        'to' => $phoneE164
    ];
    
    error_log("✅ SMS enviado exitosamente. SID: " . $message->sid);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (RestException $e) {
    $code = (int) $e->getCode();
    $hint = 'Error de Twilio.';
    
    switch ($code) {
        case 20003:
            $hint = 'Credenciales inválidas (SID/AUTH TOKEN).';
            break;
        case 21211:
            $hint = 'Número de destino inválido.';
            break;
        case 21408:
            $hint = 'Geo-permissions: habilita Colombia en la consola de Twilio.';
            break;
        case 21606:
            $hint = 'El número FROM no tiene capacidad SMS.';
            break;
        case 21608:
            $hint = 'Cuenta Trial: el número de destino debe estar verificado en Twilio.';
            break;
    }
    
    $response = [
        'ok' => false,
        'msg' => $hint,
        'code' => $code,
        'error' => $e->getMessage()
    ];
    
    error_log("❌ Error Twilio {$code}: " . $e->getMessage());
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (\Throwable $e) {
    $response = [
        'ok' => false,
        'msg' => 'Error inesperado al enviar el SMS.',
        'error' => $e->getMessage()
    ];
    
    error_log("❌ Error inesperado: " . $e->getMessage());
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}