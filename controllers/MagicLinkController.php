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
    
    /**
     * Procesar solicitudes POST
     */
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
            return;
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'send_code':
            case 'enviarCodigo':
                $this->sendCode();
                break;
            case 'verify_code':
            case 'validarCodigo':
                $this->verifyCode();
                break;
            default:
                $this->jsonResponse(false, 'Acción no válida');
        }
    }
    
    /**
     * Enviar código de verificación y magic link
     */
    private function sendCode() {
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        
        if (!$this->isValidPhone($phone)) {
            $this->jsonResponse(false, 'Número de teléfono no válido');
            return;
        }
        
        // Generar código de 6 dígitos
        $code = $this->generateVerificationCode();
        
        // Generar magic link token
        $magicToken = $this->generateMagicToken();
        
        // Guardar en base de datos
        if ($this->saveVerificationCode($phone, $code, $magicToken)) {
            
            // Enviar por WhatsApp/SMS (simulado por ahora)
            $sent = $this->sendWhatsAppMessage($phone, $code, $magicToken);
            
            if ($sent) {
                $this->jsonResponse(true, 'Código enviado exitosamente');
            } else {
                $this->jsonResponse(false, 'Error al enviar el código');
            }
        } else {
            $this->jsonResponse(false, 'Error interno del servidor');
        }
    }
    
    /**
     * Verificar código e iniciar sesión
     */
    private function verifyCode() {
        $phone = $this->sanitizePhone($_POST['phone'] ?? '');
        $code = $_POST['code'] ?? '';
        
        if (!$this->isValidPhone($phone) || !$this->isValidCode($code)) {
            $this->jsonResponse(false, 'Datos no válidos');
            return;
        }
        
        // Verificar código en base de datos
        if ($this->checkVerificationCode($phone, $code)) {
            
            // Iniciar sesión (crear/obtener usuario)
            $userId = $this->createOrGetUser($phone);
            
            if ($userId) {
                // Establecer sesión válida por 24 horas
                $_SESSION['usuario'] = $userId;
                $_SESSION['user_id'] = $userId; // Mantener compatibilidad
                $_SESSION['phone'] = $phone;
                $_SESSION['login_time'] = time();
                $_SESSION['login_expires'] = time() + (24 * 60 * 60); // 24 horas
                
                // Limpiar código usado
                $this->clearUsedCode($phone, $code);
                
                $this->jsonResponse(true, 'Acceso exitoso');
            } else {
                $this->jsonResponse(false, 'Error creando sesión de usuario');
            }
        } else {
            $this->jsonResponse(false, 'Código incorrecto o expirado');
        }
    }
    
    /**
     * Sanitizar número de teléfono
     */
    private function sanitizePhone($phone) {
        // Remover caracteres no numéricos excepto +
        $phone = preg_replace('/[^\d+]/', '', $phone);
        
        // Asegurar formato +57XXXXXXXXXX
        if (strpos($phone, '+57') === 0) {
            return $phone;
        } elseif (strlen($phone) === 10 && $phone[0] === '3') {
            return '+57' . $phone;
        }
        
        return '';
    }
    
    /**
     * Validar formato de teléfono
     */
    private function isValidPhone($phone) {
        return preg_match('/^\+57[3][0-9]{9}$/', $phone);
    }
    
    /**
     * Validar código de 6 dígitos
     */
    private function isValidCode($code) {
        return preg_match('/^[0-9]{6}$/', $code);
    }
    
    /**
     * Generar código de verificación de 6 dígitos
     */
    private function generateVerificationCode() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generar token para magic link
     */
    private function generateMagicToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Guardar código de verificación en BD
     */
    private function saveVerificationCode($phone, $code, $magicToken) {
        if (!$this->pdo) return false;
        
        try {
            // Limpiar códigos antiguos del mismo teléfono
            $stmt = $this->pdo->prepare("
                DELETE FROM verification_codes 
                WHERE phone = ? AND created_at < NOW() - INTERVAL 5 MINUTE
            ");
            $stmt->execute([$phone]);
            
            // Insertar nuevo código
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
    
    /**
     * Verificar código en BD
     */
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
    
    /**
     * Crear o obtener usuario por teléfono
     */
    private function createOrGetUser($phone) {
        if (!$this->pdo) return false;
        
        try {
            // Buscar usuario existente
            $stmt = $this->pdo->prepare("
                SELECT id FROM users WHERE phone = ? LIMIT 1
            ");
            $stmt->execute([$phone]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $user['id'];
            }
            
            // Crear nuevo usuario
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
    
    /**
     * Limpiar código usado
     */
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
    
    /**
     * Enviar mensaje de WhatsApp/SMS (simulado)
     */
    private function sendWhatsAppMessage($phone, $code, $magicToken) {
        // Por ahora simulamos el envío
        // En producción aquí iría la integración con WhatsApp API o SMS
        
        $message = "🚀 Camella.com.co\n\n";
        $message .= "Tu código de acceso es: *{$code}*\n\n";
        $message .= "O usa este enlace mágico:\n";
        $message .= "https://camella.com.co/magic-login?token={$magicToken}\n\n";
        $message .= "Válido por 5 minutos. ¡Bienvenido! 🎉";
        
        // Log del mensaje (para debugging)
        error_log("WhatsApp enviado a {$phone}: {$message}");
        
        // Simular éxito
        return true;
    }
    
    /**
     * Enviar respuesta JSON
     */
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

// Procesar solicitud si es llamado directamente
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    $controller = new MagicLinkController();
    $controller->handleRequest();
}
?>