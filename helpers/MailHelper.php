<?php
/**
 * MailHelper - Adaptador SMTP con fallback automático
 * 
 * PROPÓSITO:
 * - Enviar emails con SMTP Gmail + fallback smtp-relay.gmail.com
 * - Logging seguro sin exponer credenciales
 * - Manejo de errores sin exponer detalles al usuario
 * 
 * CARACTERÍSTICAS:
 * - Fallback automático cuando SMTP_PASS está vacío
 * - Uso de smtp-relay.gmail.com sin autenticación
 * - Log controlado para debugging
 * - Soporte para mail() básico como último recurso
 * 
 * CONFIGURACIÓN SMTP:
 * - SMTP_PASS vacío → usa smtp-relay.gmail.com (sin auth)
 * - SMTP_PASS válido → usa SMTP_HOST con autenticación
 * 
 * @author Camella Development Team
 * @version 2.0 - Con fallback SMTP
 * @date 2025-10-08
 */

// Incluir PHPMailer si está disponible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
}

class MailHelper {
    
    /**
     * Método principal para envío de emails - NUEVA API
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto del correo
     * @param string $body Contenido HTML del correo
     * @return bool True si se envió correctamente
     */
    public static function send($to, $subject, $body) {
        // Validación básica
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("[MAIL][ERROR] Email inválido: {$to}");
            return false;
        }
        
        try {
            // Intentar con PHPMailer si está disponible
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return self::enviarConPHPMailer($to, $subject, $body);
            } else {
                // Fallback a mail() básico
                error_log("[MAIL] PHPMailer no disponible, usando mail() básico");
                return self::enviarConMailBasico($to, $subject, $body);
            }
        } catch (Throwable $e) {
            error_log('[MAIL][EXCEPTION] ' . substr($e->getMessage(), 0, 350));
            return false;
        }
    }
    
    /**
     * Enviar con PHPMailer + fallback SMTP automático
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $body Contenido HTML
     * @return bool
     */
    private static function enviarConPHPMailer($to, $subject, $body) {
        $mail = new PHPMailer(true);
        
        try {
            // Configuración SMTP con fallback automático
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // Sin debug en producción
            
            // Fallback y logging seguro para SMTP
            if (trim(SMTP_PASS) === '') {
                error_log('[MAIL] SMTP_PASS vacío. Usando fallback smtp-relay.gmail.com.');
                $mail->Host = 'smtp-relay.gmail.com';
                $mail->SMTPAuth = false;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
            } else {
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;
            }
            
            // Configurar remitente y destinatario
            $mail->setFrom(SMTP_USER ?: 'noreply@camella.com.co', 'Camella.com.co');
            $mail->addAddress($to);
            
            // Contenido del email
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->CharSet = 'UTF-8';
            
            $resultado = $mail->send();
            
            if ($resultado) {
                error_log("[MAIL] Email enviado exitosamente a: " . substr($to, 0, 3) . "***");
                return true;
            } else {
                error_log('[MAIL][ERROR] ' . substr($mail->ErrorInfo, 0, 250));
                return false;
            }
            
        } catch (Exception $e) {
            error_log('[MAIL][EXCEPTION] ' . substr($e->getMessage(), 0, 350));
            return false;
        }
    }
    
    /**
     * Fallback a mail() básico de PHP
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $body Contenido
     * @return bool
     */
    private static function enviarConMailBasico($to, $subject, $body) {
        $headers = "From: " . (SMTP_USER ?: 'noreply@camella.com.co') . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $resultado = mail($to, $subject, $body, $headers);
        
        if ($resultado) {
            error_log("[MAIL] Email básico enviado a: " . substr($to, 0, 3) . "***");
        } else {
            error_log("[MAIL][ERROR] Fallo en mail() básico");
        }
        
        return $resultado;
    }
    
    /**
     * Enviar email de reset de contraseña - MÉTODO LEGACY
     * 
     * PROPÓSITO: Enviar enlace de recuperación por correo electrónico
     * con fallback automático SMTP.
     * 
     * @param string $email Dirección de correo del usuario
     * @param string $resetLink Enlace completo de reset con token
     * @return bool True si se envió correctamente, false si falló
     */
    public static function enviarResetPassword($email, $resetLink) {
        // Preparar contenido del correo
        $subject = "Recuperar contraseña - " . APP_NAME;
        $messageHtml = self::construirMensajeResetHTML($email, $resetLink);
        
        // Usar el nuevo método send() con fallback automático
        $resultado = self::send($email, $subject, $messageHtml);
        
        if (!$resultado) {
            // Log del enlace para pruebas en caso de fallo
            error_log("[MailHelper] FALLBACK - Reset link: " . $resetLink);
        }
        
        return $resultado;
    }
    
    /**
     * Construir mensaje HTML de recuperación de contraseña
     * 
     * @param string $email Email del usuario
     * @param string $resetLink Enlace de reset
     * @return string Mensaje en HTML
     */
    private static function construirMensajeResetHTML($email, $resetLink) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Recuperar contraseña - " . APP_NAME . "</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2c3e50;'>Recuperar contraseña</h2>
                
                <p>Hola,</p>
                
                <p>Has solicitado recuperar tu contraseña en <strong>" . APP_NAME . "</strong>.</p>
                
                <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Restablecer Contraseña
                    </a>
                </div>
                
                <p>O copia y pega este enlace en tu navegador:</p>
                <p style='word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 3px;'>{$resetLink}</p>
                
                <div style='margin-top: 30px; padding: 15px; background-color: #fff3cd; border-radius: 5px;'>
                    <p style='margin: 0; font-size: 14px;'><strong>Importante:</strong></p>
                    <ul style='margin: 5px 0; font-size: 14px;'>
                        <li>Este enlace expira en 30 minutos</li>
                        <li>Solo puede usarse una vez</li>
                        <li>Si no solicitaste este cambio, ignora este correo</li>
                    </ul>
                </div>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                
                <p style='color: #666; font-size: 14px;'>
                    Saludos,<br>
                    Equipo de " . APP_NAME . "
                </p>
            </div>
        </body>
        </html>";
    }
}
}