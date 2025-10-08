<?php
/**
 * PasswordController - Controlador para recuperación de contraseñas
 * 
 * PROPÓSITO:
 * - Manejo completo del flujo "Olvidé mi contraseña"
 * - Generación segura de tokens temporales
 * - Envío de emails de recuperación
 * - Reset seguro con validaciones CSRF
 * 
 * CARACTERÍSTICAS DE SEGURIDAD:
 * - Tokens hasheados en BD (no se guarda token plano)
 * - Rate limiting por email (5 minutos entre requests)
 * - Mensajes genéricos (no revelar existencia de emails)
 * - Tokens de un solo uso con expiración (30 minutos)
 * - Protección CSRF en todos los formularios
 * 
 * FLUJO COMPLETO:
 * 1. Usuario solicita reset → formulario con email
 * 2. Sistema genera token → lo hashea → guarda en BD → envía por email
 * 3. Usuario hace clic en enlace → valida token → formulario nueva contraseña
 * 4. Sistema valida → actualiza contraseña → marca token como usado
 * 
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * - Cambiar RESET_EXPIRY_MINUTES para ajustar tiempo de expiración
 * - Cambiar RATE_LIMIT_MINUTES para ajustar frecuencia de requests
 * - Los mensajes son genéricos a propósito (seguridad)
 * - Tokens solo se pueden usar una vez
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

class PasswordController extends BaseController {
    
    // Configuración de tiempos (en minutos)
    const RESET_EXPIRY_MINUTES = 30;
    const RATE_LIMIT_MINUTES = 5;
    
    /**
     * Mostrar formulario de solicitud de reset
     * 
     * PROPÓSITO: Renderizar formulario donde el usuario ingresa su email
     * para solicitar recuperación de contraseña.
     * 
     * NO REQUIERE AUTENTICACIÓN: Esta función debe ser accesible sin login
     * 
     * @return void Renderiza vista recuperar_password.php
     */
    public function mostrarSolicitud() {
        // Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Preparar datos para la vista
        $pageTitle = "Recuperar Contraseña";
        $mensaje = $_SESSION['mensaje'] ?? null;
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
        
        // Limpiar mensajes de sesión después de mostrarlos
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
        
        // Renderizar vista
        include __DIR__ . '/../views/auth/recuperar_password.php';
    }
    
    /**
     * Procesar solicitud de reset (POST)
     * 
     * PROPÓSITO: Generar token de reset y enviar email de recuperación.
     * 
     * VALIDACIONES:
     * - Token CSRF válido
     * - Email válido y normalizado
     * - Rate limiting (no más de 1 request por 5 min por email)
     * 
     * FLUJO:
     * 1. Validar CSRF y email
     * 2. Verificar rate limiting
     * 3. Generar token seguro
     * 4. Guardar en BD con hash del token
     * 5. Enviar email (o loggear si no hay SMTP)
     * 6. Mostrar mensaje genérico SIEMPRE
     * 
     * SEGURIDAD: No revelar si el email existe en el sistema
     * 
     * @return void Redirige con mensaje genérico
     */
    public function procesarSolicitud() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método no permitido.');
        }
        
        // LÍNEA CLAVE: Verificación CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            http_response_code(400);
            exit('Solicitud inválida.');
        }
        
        // Obtener y normalizar email
        $email = trim(strtolower($_POST['email'] ?? ''));
        
        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['mensaje'] = 'Si existe una cuenta con ese correo, te enviaremos instrucciones.';
            $_SESSION['tipo_mensaje'] = 'info';
            header('Location: index.php?view=recuperar-password');
            exit();
        }
        
        try {
            $pdo = getPDO();
            
            // LÍNEA CLAVE: Rate limiting - verificar si ya existe un reset reciente
            if ($this->verificarRateLimit($pdo, $email)) {
                // Ya hay un reset reciente - mostrar mensaje genérico sin crear otro
                $_SESSION['mensaje'] = 'Si existe una cuenta con ese correo, te enviaremos instrucciones.';
                $_SESSION['tipo_mensaje'] = 'info';
                header('Location: index.php?view=recuperar-password');
                exit();
            }
            
            // LÍNEA CLAVE: Verificar si el email existe en usuarios (sin revelar resultado)
            $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $usuarioExiste = $stmt->fetch();
            
            // Solo generar token si el usuario existe, pero SIEMPRE mostrar mensaje genérico
            if ($usuarioExiste) {
                // Generar token aleatorio seguro (32 bytes = 64 caracteres hex)
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                
                // Calcular tiempo de expiración
                $expiresAt = date('Y-m-d H:i:s', time() + (self::RESET_EXPIRY_MINUTES * 60));
                
                // Obtener IP y User-Agent para auditoría
                $ip = $this->obtenerIPCliente();
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                // LÍNEA CLAVE: Insertar token hasheado en BD
                $stmt = $pdo->prepare("
                    INSERT INTO password_resets (email, token_hash, expires_at, ip, user_agent) 
                    VALUES (?, ?, ?, INET6_ATON(?), ?)
                ");
                $stmt->execute([$email, $tokenHash, $expiresAt, $ip, $userAgent]);
                
                // Construir enlace de reset
                $resetLink = $this->construirEnlaceReset($token, $email);
                
                // LÍNEA CLAVE: Enviar email (o loggear si no hay SMTP)
                MailHelper::enviarResetPassword($email, $resetLink);
                
                // Log para auditoría (sin exponer token)
                error_log("[password_reset] Solicitud para: " . substr($email, 0, 3) . "*** desde IP: " . $ip);
            }
            
            // MENSAJE GENÉRICO SIEMPRE (seguridad)
            $_SESSION['mensaje'] = 'Si existe una cuenta con ese correo, te enviaremos instrucciones.';
            $_SESSION['tipo_mensaje'] = 'info';
            
        } catch (Exception $e) {
            // Log del error sin exponer detalles al usuario
            error_log("[password_reset] Error procesando solicitud: " . $e->getMessage());
            
            $_SESSION['mensaje'] = 'Error procesando la solicitud. Inténtalo más tarde.';
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        header('Location: index.php?view=recuperar-password');
        exit();
    }
    
    /**
     * Mostrar formulario de reset con token
     * 
     * PROPÓSITO: Validar token recibido por email y mostrar formulario
     * para establecer nueva contraseña.
     * 
     * VALIDACIONES:
     * - Token presente en URL
     * - Email presente en URL
     * - Token existe en BD
     * - Token no expirado
     * - Token no usado previamente
     * 
     * @return void Renderiza formulario de nueva contraseña o mensaje de error
     */
    public function mostrarReset() {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';
        
        // Validaciones básicas de parámetros URL
        if (empty($token) || empty($email)) {
            $this->mostrarErrorToken();
            return;
        }
        
        try {
            $pdo = getPDO();
            
            // LÍNEA CLAVE: Validar token (hash, no expirado, no usado)
            $tokenValido = $this->validarToken($pdo, $token, $email);
            
            if (!$tokenValido) {
                $this->mostrarErrorToken();
                return;
            }
            
            // Token válido - generar CSRF y mostrar formulario
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            
            // Preparar datos para la vista
            $pageTitle = "Nueva Contraseña";
            $mensaje = $_SESSION['mensaje'] ?? null;
            $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
            
            // Limpiar mensajes de sesión
            unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
            
            // Renderizar vista
            include __DIR__ . '/../views/auth/reset_password.php';
            
        } catch (Exception $e) {
            error_log("[password_reset] Error validando token: " . $e->getMessage());
            $this->mostrarErrorToken();
        }
    }
    
    /**
     * Procesar reset de contraseña (POST)
     * 
     * PROPÓSITO: Actualizar contraseña del usuario tras validar token y datos.
     * 
     * VALIDACIONES:
     * - Token CSRF válido
     * - Token de reset válido (mismo proceso que mostrarReset)
     * - Fortaleza de nueva contraseña
     * - Confirmación de contraseña
     * 
     * FLUJO:
     * 1. Validar CSRF y token de reset
     * 2. Validar nueva contraseña
     * 3. Hashear nueva contraseña (bcrypt)
     * 4. Actualizar en usuarios
     * 5. Marcar token como usado
     * 6. Redirigir a login con mensaje de éxito
     * 
     * @return void Redirige a login tras éxito o muestra error
     */
    public function procesarReset() {
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método no permitido.');
        }
        
        // LÍNEA CLAVE: Verificación CSRF
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            http_response_code(400);
            exit('Solicitud inválida.');
        }
        
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
        
        try {
            $pdo = getPDO();
            
            // LÍNEA CLAVE: Re-validar token (por seguridad)
            $tokenData = $this->validarToken($pdo, $token, $email, true);
            
            if (!$tokenData) {
                $_SESSION['mensaje'] = 'Enlace inválido o expirado. Solicita uno nuevo.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?view=recuperar-password');
                exit();
            }
            
            // Validar fortaleza de nueva contraseña
            if (!$this->esPasswordFuerte($newPassword)) {
                $_SESSION['mensaje'] = 'La contraseña debe tener al menos 10 caracteres, incluyendo mayúsculas, minúsculas y números.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?view=reset-password&token=' . urlencode($token) . '&email=' . urlencode($email));
                exit();
            }
            
            // Validar confirmación de contraseña
            if ($newPassword !== $newPasswordConfirm) {
                $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
                $_SESSION['tipo_mensaje'] = 'error';
                header('Location: index.php?view=reset-password&token=' . urlencode($token) . '&email=' . urlencode($email));
                exit();
            }
            
            // LÍNEA CLAVE: Hashear nueva contraseña con bcrypt
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Iniciar transacción para operaciones atómicas
            $pdo->beginTransaction();
            
            try {
                // LÍNEA CLAVE: Actualizar contraseña del usuario
                $stmt = $pdo->prepare("
                    UPDATE usuarios 
                    SET password = ?, actualizado_en = NOW() 
                    WHERE email = ?
                ");
                $resultado = $stmt->execute([$passwordHash, $email]);
                
                if (!$resultado || $stmt->rowCount() === 0) {
                    throw new Exception("No se pudo actualizar la contraseña");
                }
                
                // LÍNEA CLAVE: Marcar token como usado
                $stmt = $pdo->prepare("
                    UPDATE password_resets 
                    SET used_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$tokenData['id']]);
                
                $pdo->commit();
                
                // Log de éxito para auditoría
                error_log("[password_reset] Contraseña actualizada para: " . substr($email, 0, 3) . "***");
                
                // Mensaje de éxito y redirección a login
                $_SESSION['mensaje'] = 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.';
                $_SESSION['tipo_mensaje'] = 'success';
                header('Location: index.php?view=login');
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        } catch (Exception $e) {
            error_log("[password_reset] Error procesando reset: " . $e->getMessage());
            
            $_SESSION['mensaje'] = 'Error procesando la solicitud. Inténtalo más tarde.';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: index.php?view=recuperar-password');
            exit();
        }
    }
    
    /**
     * Verificar rate limiting para un email
     * 
     * @param PDO $pdo Conexión a base de datos
     * @param string $email Email a verificar
     * @return bool True si ya hay un reset reciente (rate limited)
     */
    private function verificarRateLimit($pdo, $email) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM password_resets 
            WHERE email = ? 
            AND creado_en > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND used_at IS NULL
        ");
        $stmt->execute([$email, self::RATE_LIMIT_MINUTES]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Validar token de reset
     * 
     * @param PDO $pdo Conexión a base de datos
     * @param string $token Token plano recibido
     * @param string $email Email asociado
     * @param bool $returnData Si debe retornar datos del token
     * @return bool|array True/false o datos del token si $returnData es true
     */
    private function validarToken($pdo, $token, $email, $returnData = false) {
        $tokenHash = hash('sha256', $token);
        
        $stmt = $pdo->prepare("
            SELECT id, email, expires_at, used_at
            FROM password_resets 
            WHERE email = ? 
            AND token_hash = ? 
            AND expires_at > NOW()
            AND used_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$email, $tokenHash]);
        $tokenData = $stmt->fetch();
        
        if ($returnData) {
            return $tokenData ?: false;
        }
        
        return $tokenData !== false;
    }
    
    /**
     * Construir enlace de reset completo
     * 
     * @param string $token Token plano
     * @param string $email Email del usuario
     * @return string URL completa del enlace de reset
     */
    private function construirEnlaceReset($token, $email) {
        $baseUrl = $this->obtenerBaseUrl();
        return $baseUrl . 'index.php?view=reset-password&token=' . urlencode($token) . '&email=' . urlencode($email);
    }
    
    /**
     * Obtener URL base del sitio
     * 
     * @return string URL base con protocolo y dominio
     */
    private function obtenerBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'camella.com.co';
        return $protocol . '://' . $host . '/';
    }
    
    /**
     * Obtener IP del cliente (considera proxies)
     * 
     * @return string IP del cliente
     */
    private function obtenerIPCliente() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 
              '0.0.0.0';
              
        // Si hay múltiples IPs (proxies), tomar la primera
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        
        return $ip;
    }
    
    /**
     * Verificar fortaleza de contraseña
     * 
     * @param string $password Contraseña a validar
     * @return bool True si cumple criterios de fortaleza
     */
    private function esPasswordFuerte($password) {
        return strlen($password) >= 10 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/\d/', $password);
    }
    
    /**
     * Mostrar mensaje de error para tokens inválidos
     * 
     * @return void
     */
    private function mostrarErrorToken() {
        $pageTitle = "Enlace Inválido";
        $error = "El enlace de recuperación es inválido o ha expirado. Solicita uno nuevo.";
        
        include __DIR__ . '/../views/auth/error_token.php';
    }
}