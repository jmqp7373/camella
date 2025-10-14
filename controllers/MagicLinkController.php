<?php

/**
 * Controlador Magic Link - Camella.com.co
 * Maneja el envío de códigos de verificación y magic links
 */

// Inicializar sesión
if (!isset($_SESSION)) {
    session_start();
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Cargar Twilio SDK
require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

class MagicLinkController {
    
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en MagicLinkController: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    public function handleRequest() {
        // Log de depuración
        error_log("MagicLinkController: Solicitud recibida - Método: " . $_SERVER['REQUEST_METHOD']);
        error_log("MagicLinkController: POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("MagicLinkController: Error - Método no es POST");
            return $this->jsonResponse(false, 'Método no permitido');
        }

        if (!$this->pdo) {
            error_log("MagicLinkController: Error - No hay conexión a BD");
            return $this->jsonResponse(false, 'Error de conexión a la base de datos');
        }

        $action = $_POST['action'] ?? '';
        error_log("MagicLinkController: Acción solicitada: " . $action);

        switch ($action) {
            case 'send_code':
            case 'enviarCodigo':
                return $this->sendCode();
            case 'verify_code':
            case 'validarCodigo':
                return $this->verifyCode();
            default:
                error_log("MagicLinkController: Error - Acción no válida: " . $action);
                return $this->jsonResponse(false, 'Acción no válida');
        }
    }

    private function sendCode() {
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        error_log("MagicLinkController sendCode: Teléfono recibido: " . ($_POST['phone'] ?? 'vacio'));
        error_log("MagicLinkController sendCode: Teléfono sanitizado: " . $phone);

        if (!$phone || !$this->isValidPhone($phone)) {
            error_log("MagicLinkController sendCode: Error - Teléfono inválido");
            return $this->jsonResponse(false, 'Número no ingresado o inválido');
        }

        $code = $this->generateVerificationCode();
        $magicToken = $this->generateMagicToken();
        error_log("MagicLinkController sendCode: Código generado: " . $code);

        if ($this->saveVerificationCode($phone, $code, $magicToken)) {
            error_log("MagicLinkController sendCode: Código guardado en BD, enviando SMS...");
            
            // Guardar en historial
            $this->saveToHistory($phone, $code, $magicToken, 'created');
            
            $sent = $this->sendWhatsAppMessage($phone, $code, $magicToken);
            if ($sent) {
                error_log("MagicLinkController sendCode: SMS enviado exitosamente");
                return $this->jsonResponse(true, 'Código enviado exitosamente');
            } else {
                error_log("MagicLinkController sendCode: Error al enviar SMS");
                $this->updateHistoryStatus($phone, $code, 'failed');
                return $this->jsonResponse(false, 'Error al enviar el código');
            }
        } else {
            error_log("MagicLinkController sendCode: Error al guardar en BD");
            return $this->jsonResponse(false, 'Error interno del servidor');
        }
    }

    private function verifyCode() {
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        $code = $_POST['code'] ?? '';

        if (!$this->isValidPhone($phone) || !$this->isValidCode($code)) {
            return $this->jsonResponse(false, 'Datos no válidos');
        }

        if ($this->checkVerificationCode($phone, $code)) {
            $userId = $this->createOrGetUser($phone);
            if ($userId) {
                $_SESSION['usuario'] = $userId;
                $_SESSION['user_id'] = $userId;
                $_SESSION['phone'] = $phone;
                $_SESSION['login_time'] = time();
                $_SESSION['login_expires'] = time() + (24 * 60 * 60);
                
                // Actualizar historial como "usado"
                $this->updateHistoryStatus($phone, $code, 'used', $userId);
                
                $this->clearUsedCode($phone, $code);
                return $this->jsonResponse(true, 'Acceso exitoso');
            } else {
                return $this->jsonResponse(false, 'Error creando sesión de usuario');
            }
        } else {
            // Marcar como expirado si el código no es válido
            $this->updateHistoryStatus($phone, $code, 'expired');
            return $this->jsonResponse(false, 'Código incorrecto o expirado');
        }
    }

    private function sanitizePhone($phone) {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        if (strpos($phone, '+57') === 0) {
            return $phone;
        } elseif (strlen($phone) === 10 && $phone[0] === '3') {
            return '+57' . $phone;
        }
        return '';
    }

    private function isValidPhone($phone) {
        return preg_match('/^\+57[3][0-9]{9}$/', $phone);
    }

    private function isValidCode($code) {
        return preg_match('/^[0-9]{6}$/', $code);
    }

    private function generateVerificationCode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function generateMagicToken() {
        return bin2hex(random_bytes(32));
    }

    private function saveVerificationCode($phone, $code, $magicToken) {
        if (!$this->pdo) return false;

        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM verification_codes 
                WHERE phone = ? AND created_at < NOW() - INTERVAL 5 MINUTE
            ");
            $stmt->execute([$phone]);

            $stmt = $this->pdo->prepare("
                INSERT INTO verification_codes (phone, code, magic_token, created_at, expires_at) 
                VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 5 MINUTE)
            ");
            return $stmt->execute([$phone, $code, $magicToken]);

        } catch (Exception $e) {
            error_log("Error guardando código: " . $e->getMessage());
            return false;
        }
    }

    private function checkVerificationCode($phone, $code) {
        if (!$this->pdo) return false;

        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM verification_codes 
                WHERE phone = ? AND code = ? AND expires_at > NOW()
                LIMIT 1
            ");
            $stmt->execute([$phone, $code]);

            return $stmt->fetch() !== false;

        } catch (Exception $e) {
            error_log("Error verificando código: " . $e->getMessage());
            return false;
        }
    }

    private function createOrGetUser($phone) {
        if (!$this->pdo) return false;

        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            $user = $stmt->fetch();

            if ($user) {
                return $user['id'];
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO users (phone, created_at, last_login) 
                VALUES (?, NOW(), NOW())
            ");

            if ($stmt->execute([$phone])) {
                return $this->pdo->lastInsertId();
            }

            return false;

        } catch (Exception $e) {
            error_log("Error creando/obteniendo usuario: " . $e->getMessage());
            return false;
        }
    }

    private function clearUsedCode($phone, $code) {
        if (!$this->pdo) return;

        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM verification_codes 
                WHERE phone = ? AND code = ?
            ");
            $stmt->execute([$phone, $code]);

        } catch (Exception $e) {
            error_log("Error limpiando código: " . $e->getMessage());
        }
    }

    private function sendWhatsAppMessage($phone, $code, $magicToken) {
        try {
            // El autoload ya se cargó al inicio del archivo
            $twilio = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);

            // Mensaje más corto para cuenta trial de Twilio (límite: ~160 caracteres)
            $message = "Camella.com.co\n";
            $message .= "Tu codigo de acceso: {$code}\n";
            $message .= "Valido 5 min.";

            $twilioMessage = $twilio->messages->create(
                $phone,
                [
                    'from' => TWILIO_FROM_NUMBER,
                    'body' => $message
                ]
            );

            error_log("SMS enviado a {$phone}. SID: " . $twilioMessage->sid);
            
            // Guardar SID en el historial
            $this->updateHistorySMSSID($phone, $twilioMessage->sid);
            
            return true;

        } catch (Exception $e) {
            error_log("Error enviando SMS a {$phone}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guardar código en historial para auditoría
     */
    private function saveToHistory($phone, $code, $magicToken, $status = 'created') {
        if (!$this->pdo) return;

        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            $stmt = $this->pdo->prepare("
                INSERT INTO verification_codes_history 
                (phone, code, magic_token, created_at, expires_at, status, ip_address, user_agent) 
                VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 5 MINUTE, ?, ?, ?)
            ");

            $stmt->execute([$phone, $code, $magicToken, $status, $ip, $userAgent]);
            error_log("Historial: Código guardado para {$phone}");

        } catch (Exception $e) {
            error_log("Error guardando en historial: " . $e->getMessage());
        }
    }

    /**
     * Actualizar estado del código en historial
     */
    private function updateHistoryStatus($phone, $code, $status, $userId = null) {
        if (!$this->pdo) return;

        try {
            if ($status === 'used') {
                $stmt = $this->pdo->prepare("
                    UPDATE verification_codes_history 
                    SET status = ?, used_at = NOW(), user_id = ? 
                    WHERE phone = ? AND code = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([$status, $userId, $phone, $code]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE verification_codes_history 
                    SET status = ? 
                    WHERE phone = ? AND code = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([$status, $phone, $code]);
            }

            error_log("Historial: Estado actualizado a '{$status}' para {$phone}");

        } catch (Exception $e) {
            error_log("Error actualizando historial: " . $e->getMessage());
        }
    }

    /**
     * Actualizar SID del SMS en historial
     */
    private function updateHistorySMSSID($phone, $smsSid) {
        if (!$this->pdo) return;

        try {
            $stmt = $this->pdo->prepare("
                UPDATE verification_codes_history 
                SET sms_sid = ? 
                WHERE phone = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$smsSid, $phone]);

        } catch (Exception $e) {
            error_log("Error actualizando SID en historial: " . $e->getMessage());
        }
    }

    private function jsonResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Procesar si es llamado directamente (ejecutar siempre cuando se carga el archivo)
$controller = new MagicLinkController();
$controller->handleRequest();
