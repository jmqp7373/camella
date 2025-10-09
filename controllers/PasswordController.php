<?php
/**
 * PasswordController - Robust password reset flow for PHP 7.1 + GoDaddy
 * 
 * PROPÓSITO: Flujo completo "Olvidé mi contraseña" robusto
 * - Tabla simple password_resets (id, email, token, created_at)
 * - Tokens seguros con bin2hex(random_bytes(32))
 * - Logging detallado [RESET] sin exponer datos sensibles
 * - Compatibilidad PHP 7.1 (sin type hints)
 * 
 * @author Camella Development Team
 * @version 3.0 - Robust + PHP 7.1 Compatible
 * @date 2025-10-08
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/BaseController.php';

class PasswordController extends BaseController {
    
    /**
     * Mostrar formulario de solicitud de reset
     */
    public function mostrarSolicitud() {
        // Generar token CSRF para el formulario
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Preparar datos para la vista (mantener estructura existente)
        $pageTitle = "Recuperar Contraseña";
        $mensaje = isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : null;
        $tipo_mensaje = isset($_SESSION['tipo_mensaje']) ? $_SESSION['tipo_mensaje'] : null;
        
        // Limpiar mensajes de sesión después de mostrarlos
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
        
        // Renderizar vista existente (no cambiar UI)
        include __DIR__ . '/../views/auth/recuperar_password.php';
    }
    
    /**
     * Procesar solicitud de recuperación - ROBUST IMPLEMENTATION
     */
    public function procesarSolicitud() {
        error_log('[RESET] POST recibido');
        
        try {
            // Sanitizar email según especificación
            $email = filter_var(trim(isset($_POST['email']) ? $_POST['email'] : ''), FILTER_SANITIZE_EMAIL);
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log('[RESET] email inválido');
                // devolver genérico (no revelar detalles a la UI)
                $this->mostrarExito();
                return;
            }

            // Crear/verificar tabla password_resets (idempotente)
            $this->asegurarTablaPasswordResets();

            // (Opcional) comprobar si existe usuario; si no, igual continuar para no revelar existencia.
            $pdo = getPDO();

            // limpiar tokens previos de ese correo (idempotencia)
            $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);

            // generar token seguro y su hash HMAC
            $token = bin2hex(random_bytes(32));
            
            // Cargar APP_KEY si no está definido
            if (!defined('APP_KEY')) {
                require_once __DIR__ . '/../config/config.php';
            }
            $token_hash = hash_hmac('sha256', $token, APP_KEY);
            
            // Insertar con hash y expiración de 30 minutos
            $pdo->prepare('INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))')->execute([$email, $token_hash]);

            // armar link absoluto
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $base = $scheme . $_SERVER['HTTP_HOST'];
            $link = $base . '/index.php?view=reset-password&token=' . urlencode($token);

            // enviar correo
            $subject = 'Recupera tu contraseña en Camella.com.co';
            $html = '<p>Para recuperar tu contraseña haz clic en el botón:</p>
                     <p><a href="'.$link.'" style="display:inline-block;background:#0a58ca;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;">Cambiar mi contraseña</a></p>
                     <p>Si no fuiste tú, ignora este mensaje.</p>';

            if (!class_exists('MailHelper')) { 
                require_once __DIR__ . '/../helpers/MailHelper.php'; 
            }
            $sent = MailHelper::send($email, $subject, $html);
            error_log('[RESET] email='.$email.' token='.substr($token,0,8).'... sent=' . ($sent?'OK':'FAIL'));

            // La UI conserva su texto genérico de éxito/fracaso; no revelar existencia de cuenta.
            $this->mostrarExito();
            
        } catch (Exception $e) { // Use Exception for PHP 7.1 compatibility
            error_log('[RESET][EXCEPTION] '.substr($e->getMessage(),0,300));
            // La UI sigue mostrando "Error procesando la solicitud"
            $this->mostrarError('Error procesando la solicitud.');
        }
    }
    
    /**
     * Crear tabla password_resets si no existe (idempotente)
     */
    private function asegurarTablaPasswordResets() {
        try {
            $pdo = getPDO();
            $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at TIMESTAMP NULL,
                used_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (email),
                INDEX (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($sql);
            error_log('[RESET] Tabla password_resets asegurada con HMAC');
        } catch (Exception $e) {
            error_log('[RESET][ERROR] Error asegurando tabla: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Mostrar formulario de reset con token - GET
     * DELEGADO a views/reset-password.php que maneja HMAC internamente
     */
    public function mostrarReset() {
        // La nueva implementación está en views/reset-password.php
        // que maneja toda la lógica HMAC internamente
        include __DIR__ . '/../views/reset-password.php';
    }
    
    /**
     * Procesar nueva contraseña - POST
     */
    public function procesarReset() {
        try {
            // Verificar método POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                exit('Método no permitido.');
            }
            
            // Verificar CSRF
            $csrfSession = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
            $csrfPost = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            if (!$csrfPost || !hash_equals($csrfSession, $csrfPost)) {
                error_log('[RESET] CSRF token inválido en reset');
                $this->mostrarError('Solicitud inválida.');
                return;
            }
            
            $token = isset($_SESSION['reset_token']) ? $_SESSION['reset_token'] : '';
            $email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
            $newPassword = isset($_POST['password']) ? $_POST['password'] : '';
            
            if (empty($token) || empty($email) || empty($newPassword)) {
                $this->mostrarError('Datos incompletos.');
                return;
            }
            
            // Validar longitud mínima (≥ 8)
            if (strlen($newPassword) < 8) {
                $this->mostrarError('La contraseña debe tener al menos 8 caracteres.');
                return;
            }
            
            $pdo = getPDO();
            
            // Verificar token una vez más
            $stmt = $pdo->prepare("
                SELECT email FROM password_resets 
                WHERE token = ? AND email = ? AND created_at > (NOW() - INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $stmt->execute([$token, $email]);
            $resetData = $stmt->fetch();
            
            if (!$resetData) {
                error_log('[RESET] token inválido en procesarReset: '.substr($token,0,10).'...');
                $this->mostrarError('Token inválido o expirado.');
                return;
            }
            
            // Hash de nueva contraseña
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // UPDATE usuarios SET password=? WHERE email=?
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $updateOk = $stmt->execute([$hashedPassword, $email]);
            
            if ($updateOk && $stmt->rowCount() > 0) {
                // DELETE FROM password_resets WHERE token=?
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                // Limpiar sesión
                unset($_SESSION['reset_token'], $_SESSION['reset_email']);
                
                // Log según especificación
                error_log('[RESET] token usado OK para '.$email);
                
                // Mensaje de éxito (mantener formato UI existente)
                $_SESSION['mensaje'] = 'Contraseña actualizada exitosamente. Puedes iniciar sesión.';
                $_SESSION['tipo_mensaje'] = 'success';
                header('Location: /index.php?view=login');
                exit();
            } else {
                error_log('[RESET][ERROR] no se pudo actualizar password para '.$email);
                $this->mostrarError('Error actualizando contraseña.');
            }
            
        } catch (Exception $e) {
            error_log('[RESET][EXCEPTION] '.substr($e->getMessage(),0,300));
            $this->mostrarError('Error procesando la solicitud.');
        }
    }
    
    /**
     * Mostrar mensaje de éxito genérico
     */
    private function mostrarExito() {
        $_SESSION['mensaje'] = 'Si existe una cuenta con ese correo, te enviaremos instrucciones de recuperación.';
        $_SESSION['tipo_mensaje'] = 'info';
        header('Location: /index.php?view=recuperar-password');
        exit();
    }
    
    /**
     * Mostrar mensaje de error genérico
     */
    private function mostrarError($mensaje) {
        $_SESSION['mensaje'] = $mensaje;
        $_SESSION['tipo_mensaje'] = 'error';
        header('Location: /index.php?view=recuperar-password');
        exit();
    }
}