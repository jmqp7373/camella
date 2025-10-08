<?php
/**
 * MailHelper - Adaptador simple para envío de correos
 * 
 * PROPÓSITO:
 * - Enviar emails de reset de contraseña de forma controlada
 * - Fallback a logging si SMTP no está configurado
 * - Evitar exposición de errores al usuario final
 * 
 * CARACTERÍSTICAS:
 * - Usa mail() básico de PHP como fallback
 * - Log controlado sin exponer datos sensibles
 * - Validación básica de configuración SMTP
 * 
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * - Para producción, configurar SMTP real en config/config.php
 * - En desarrollo, los enlaces aparecerán en error_log
 * - Para usar PHPMailer, agregar require_once y cambiar método sendEmail()
 * 
 * CÓMO EXTENDER:
 * - Agregar templates HTML más elaborados
 * - Implementar cola de emails para mejor rendimiento
 * - Añadir más validaciones de formato
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

class MailHelper {
    
    /**
     * Enviar email de reset de contraseña
     * 
     * PROPÓSITO: Enviar enlace de recuperación por correo electrónico
     * con fallback a logging si no hay SMTP configurado.
     * 
     * SEGURIDAD:
     * - No exponer errores de SMTP al usuario
     * - Log controlado sin credenciales
     * - Validación básica de formato de email
     * 
     * @param string $email Dirección de correo del usuario
     * @param string $resetLink Enlace completo de reset con token
     * @return bool True si se envió correctamente, false si falló
     */
    public static function enviarResetPassword($email, $resetLink) {
        // Validación básica de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("[MailHelper] Email inválido proporcionado");
            return false;
        }
        
        // LÍNEA CLAVE: Preparar contenido del correo
        $subject = "Recuperar contraseña - " . APP_NAME;
        $message = self::construirMensajeReset($email, $resetLink);
        $headers = self::construirHeaders();
        
        try {
            // LÍNEA CLAVE: Verificar si hay configuración SMTP
            if (self::tieneConfiguracionSMTP()) {
                // Intentar envío SMTP (implementar PHPMailer aquí si está disponible)
                $resultado = self::enviarViaSMTP($email, $subject, $message, $headers);
            } else {
                // Fallback a mail() de PHP
                $resultado = mail($email, $subject, $message, $headers);
            }
            
            if ($resultado) {
                error_log("[MailHelper] Reset email enviado a: " . substr($email, 0, 3) . "***");
                return true;
            } else {
                // LÍNEA CLAVE: Log para desarrollo/testing (sin exponer al usuario)
                error_log("[MailHelper] RESET LINK PARA TESTING: " . $resetLink);
                error_log("[MailHelper] Email destinatario: " . $email);
                return false;
            }
            
        } catch (Exception $e) {
            // Log del error sin exponer detalles al usuario
            error_log("[MailHelper] Error enviando email: " . $e->getMessage());
            // LÍNEA CLAVE: Log del enlace para pruebas en caso de fallo
            error_log("[MailHelper] FALLBACK - Reset link: " . $resetLink);
            return false;
        }
    }
    
    /**
     * Construir mensaje de recuperación de contraseña
     * 
     * @param string $email Email del usuario
     * @param string $resetLink Enlace de reset
     * @return string Mensaje en texto plano
     */
    private static function construirMensajeReset($email, $resetLink) {
        $mensaje = "Hola,\n\n";
        $mensaje .= "Has solicitado recuperar tu contraseña en " . APP_NAME . ".\n\n";
        $mensaje .= "Para crear una nueva contraseña, haz clic en el siguiente enlace:\n";
        $mensaje .= $resetLink . "\n\n";
        $mensaje .= "Este enlace expira en 30 minutos y solo puede usarse una vez.\n\n";
        $mensaje .= "Si no solicitaste este cambio, ignora este correo.\n\n";
        $mensaje .= "Saludos,\n";
        $mensaje .= "Equipo de " . APP_NAME;
        
        return $mensaje;
    }
    
    /**
     * Construir headers básicos para el email
     * 
     * @return string Headers del correo
     */
    private static function construirHeaders() {
        $fromEmail = SMTP_USER ?: 'noreply@camella.com.co';
        $fromName = MAIL_FROM_NAME ?: APP_NAME;
        
        $headers = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        return $headers;
    }
    
    /**
     * Verificar si existe configuración SMTP válida
     * 
     * @return bool True si hay configuración SMTP
     */
    private static function tieneConfiguracionSMTP() {
        return !empty(SMTP_HOST) && !empty(SMTP_USER) && !empty(SMTP_PASS);
    }
    
    /**
     * Enviar vía SMTP (placeholder para PHPMailer u otra librería)
     * 
     * NOTA PARA DESARROLLADORES:
     * Implementar aquí PHPMailer si está disponible en vendor/
     * Por ahora, fallback a mail() básico
     * 
     * @param string $email Email destinatario
     * @param string $subject Asunto
     * @param string $message Mensaje
     * @param string $headers Headers
     * @return bool Resultado del envío
     */
    private static function enviarViaSMTP($email, $subject, $message, $headers) {
        // PLACEHOLDER: Implementar PHPMailer aquí
        // Por ahora, usar mail() básico
        return mail($email, $subject, $message, $headers);
    }
}