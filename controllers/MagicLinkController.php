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
                // Obtener el rol del usuario
                $userRole = $this->getUserRole($userId);
                
                $_SESSION['usuario'] = $userId;
                $_SESSION['user_id'] = $userId;
                $_SESSION['phone'] = $phone;
                $_SESSION['role'] = $userRole; // Guardar rol en sesión
                $_SESSION['original_role'] = $userRole; // Guardar rol original (para cambio de rol de admin)
                $_SESSION['login_time'] = time();
                $_SESSION['login_expires'] = time() + (24 * 60 * 60);
                
                // Actualizar historial como "usado"
                $this->updateHistoryStatus($phone, $code, 'used', $userId);
                
                $this->clearUsedCode($phone, $code);
                
                // Devolver éxito con el rol para redirigir desde el frontend
                return $this->jsonResponse(true, 'Acceso exitoso', ['role' => $userRole]);
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
        // Generar token corto de 12 caracteres para URLs más amigables
        // Suficiente entropía para 5 minutos de validez
        return bin2hex(random_bytes(6)); // 6 bytes = 12 caracteres hex
    }

    private function saveVerificationCode($phone, $code, $magicToken) {
        if (!$this->pdo) return false;

        try {
            // Limpiar códigos antiguos
            $stmt = $this->pdo->prepare("
                DELETE FROM verification_codes 
                WHERE phone = ? AND created_at < NOW() - INTERVAL 5 MINUTE
            ");
            $stmt->execute([$phone]);

            // Guardar el código de verificación
            $stmt = $this->pdo->prepare("
                INSERT INTO verification_codes (phone, code, magic_token, created_at, expires_at) 
                VALUES (?, ?, ?, NOW(), NOW() + INTERVAL 5 MINUTE)
            ");
            $saved = $stmt->execute([$phone, $code, $magicToken]);

            // TAMBIÉN guardar el magic token en la tabla magic_links
            if ($saved) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO magic_links (token, phone, created_at, usos) 
                    VALUES (?, ?, NOW(), 0)
                    ON DUPLICATE KEY UPDATE created_at = NOW(), usos = 0
                ");
                $stmt->execute([$magicToken, $phone]);
                error_log("Magic token guardado en magic_links: {$magicToken}");
            }

            return $saved;

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

    private function getUserRole($userId) {
        if (!$this->pdo) return 'publicante';

        try {
            $stmt = $this->pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            return $user ? $user['role'] : 'publicante';
        } catch (Exception $e) {
            error_log("Error obteniendo rol: " . $e->getMessage());
            return 'publicante';
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

            // Construir URL amigable CORTA del magic link
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // URL amigable corta: /m/abc123 (12 caracteres)
            $magicLinkUrl = "{$protocol}://{$host}/camella.com.co/m/{$magicToken}";

            // Mensaje ULTRA optimizado para SMS clickeable
            $message = "Camella.com.co\n";
            $message .= "Codigo: {$code}\n";
            $message .= "Link: {$magicLinkUrl}\n";
            $message .= "Valido 5 min.";

            error_log("SMS a enviar: {$message}");
            error_log("Magic Link: {$magicLinkUrl} (token: {$magicToken}, longitud: " . strlen($magicToken) . ")");

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

    /**
     * Obtener estadísticas de Twilio desde la base de datos
     * @param string $period '24h', '7d', '30d'
     * @return array Estadísticas del período
     */
    public function getTwilioStats($period = '24h') {
        if (!$this->pdo) return null;

        try {
            // Definir la condición temporal según el período
            $timeCondition = match($period) {
                '24h' => "created_at >= NOW() - INTERVAL 1 DAY",
                '7d' => "created_at >= NOW() - INTERVAL 7 DAY",
                '30d' => "created_at >= NOW() - INTERVAL 30 DAY",
                default => "created_at >= NOW() - INTERVAL 1 DAY"
            };

            // Obtener estadísticas
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_enviados,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as entregas_exitosas,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as fallidos,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expirados
                FROM verification_codes_history 
                WHERE $timeCondition
            ");
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calcular costo estimado (Twilio cobra aprox $0.0079 por SMS en Colombia)
            $costPerSMS = 0.0079;
            $totalCost = ($stats['total_enviados'] ?? 0) * $costPerSMS;

            return [
                'total_enviados' => $stats['total_enviados'] ?? 0,
                'entregas_exitosas' => $stats['entregas_exitosas'] ?? 0,
                'fallidos' => $stats['fallidos'] ?? 0,
                'expirados' => $stats['expirados'] ?? 0,
                'costo_estimado' => number_format($totalCost, 2),
                'tasa_exito' => $stats['total_enviados'] > 0 
                    ? round(($stats['entregas_exitosas'] / $stats['total_enviados']) * 100, 1) 
                    : 0
            ];

        } catch (Exception $e) {
            error_log("Error obteniendo stats de Twilio: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Login automático con Magic Link Token
     * URL: /m/{token} o index.php?view=m&token={token}
     * 
     * @param string $token Token único del magic link
     */
    public function loginConToken($token) {
        // Asegurar que la sesión esté iniciada (puede haberse iniciado en index.php)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Sanitizar token
        $token = preg_replace('/[^a-zA-Z0-9]/', '', $token);
        
        // Obtener la base URL del sitio
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = "$protocol://$host/camella.com.co";
        
        if (empty($token)) {
            header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Token no válido"));
            exit;
        }

        if (!$this->pdo) {
            header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Error de conexión"));
            exit;
        }

        try {
            // Buscar el token en la tabla magic_links
            $stmt = $this->pdo->prepare("SELECT * FROM magic_links WHERE token = ? LIMIT 1");
            $stmt->execute([$token]);
            $link = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$link) {
                error_log("MagicLink: Token no encontrado: $token");
                header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Token no válido"));
                exit;
            }

            // Verificar expiración
            // - Si el token tiene 0 usos = nuevo desde SMS (5 minutos)
            // - Si ya fue usado = compartido (24 horas)
            $created = strtotime($link['created_at']);
            $ahora = time();
            $tiempoTranscurrido = $ahora - $created;
            
            $usos = (int)$link['usos'];
            $tiempoMaximo = ($usos === 0) ? 300 : 86400; // 5 min si es nuevo, 24h si ya fue usado
            $tiempoDescripcion = ($usos === 0) ? "5 minutos" : "24 horas";

            if ($tiempoTranscurrido > $tiempoMaximo) {
                error_log("MagicLink: Token vencido. Creado: {$link['created_at']}, Transcurrido: $tiempoTranscurrido segundos, Límite: $tiempoMaximo");
                header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Token vencido (válido solo $tiempoDescripcion)"));
                exit;
            }

            // Verificar número de usos (máximo 100)
            if ($usos >= 100) {
                error_log("MagicLink: Token con demasiados usos: {$link['usos']}");
                header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Este enlace ha sido usado muchas veces"));
                exit;
            }

            // Buscar usuario por teléfono
            $stmt2 = $this->pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
            $stmt2->execute([$link['phone']]);
            $user = $stmt2->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("MagicLink: Usuario no encontrado para teléfono: {$link['phone']}");
                header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Usuario no encontrado"));
                exit;
            }

            // La sesión ya está iniciada al principio del método
            // Configurar variables de sesión compatibles con los dashboards existentes
            $_SESSION['usuario'] = $user['id']; // Variable que esperan los dashboards
            $_SESSION['user_id'] = $user['id']; // Variable adicional para compatibilidad
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['user_email'] = $user['email'] ?? '';
            $_SESSION['role'] = $user['role'] ?? 'user'; // Variable que esperan los dashboards
            $_SESSION['user_role'] = $user['role'] ?? 'user'; // Variable adicional para compatibilidad
            $_SESSION['logged_in'] = true;
            
            // Guardar role original si existe
            if (!empty($user['original_role'])) {
                $_SESSION['original_role'] = $user['original_role'];
            }
            
            // Log para verificar que la sesión se configuró
            error_log("MagicLink: Variables de sesión configuradas:");
            error_log("  - usuario: " . $_SESSION['usuario']);
            error_log("  - role: " . $_SESSION['role']);
            error_log("  - logged_in: " . ($_SESSION['logged_in'] ? 'true' : 'false'));

            // Incrementar contador de usos
            $stmt3 = $this->pdo->prepare("UPDATE magic_links SET usos = usos + 1 WHERE id = ?");
            $stmt3->execute([$link['id']]);

            // Log de éxito con información detallada
            error_log("MagicLink: Login exitoso");
            error_log("  - Usuario ID: {$user['id']}");
            error_log("  - Teléfono: {$user['phone']}");
            error_log("  - Rol: {$user['role']}");
            error_log("  - Usos del token: " . ($link['usos'] + 1));

            // Redirigir según el rol del usuario con URL absoluta
            // Las vistas de dashboard están en subdirectorios
            $role = strtolower(trim($user['role']));
            
            $redirectMap = [
                'admin' => "$baseUrl/views/admin/dashboard.php",
                'promotor' => "$baseUrl/views/promotor/dashboard.php",
                'publicante' => "$baseUrl/views/publicante/dashboard.php"
            ];
            
            if (isset($redirectMap[$role])) {
                $redirect = $redirectMap[$role];
                error_log("  - Redirigiendo a: $redirect");
            } else {
                $redirect = "$baseUrl/index.php?view=home";
                error_log("  - Rol '$role' no reconocido, redirigiendo a home");
            }
            
            error_log("MagicLink: Session ID antes de redirect: " . session_id());
            
            // Forzar escritura de la sesión antes de redirigir
            session_write_close();
            
            header("Location: $redirect");
            exit;

        } catch (PDOException $e) {
            error_log("MagicLink: Error de BD: " . $e->getMessage());
            header("Location: $baseUrl/index.php?view=loginPhone&error=" . urlencode("Error interno del servidor"));
            exit;
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

// Procesar si es llamado directamente VÍA POST (para AJAX)
// NO ejecutar si es llamado desde index.php para magic link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['view'])) {
    $controller = new MagicLinkController();
    $controller->handleRequest();
}
