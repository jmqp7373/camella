<?php
// helpers/MailHelper.php
if (!class_exists('MailHelper')) {
  class MailHelper {

    /**
     * Envío genérico de correo HTML.
     * Usa Gmail SMTP si SMTP_PASS está presente; de lo contrario fallback a smtp-relay,
     * y como último recurso usa mail() nativa. Retorna true/false.
     */
    public static function send(string $to, string $subject, string $html): bool {
      try {
        // 1) Si hay PHPMailer, úsalo
        $phpMailerPath = __DIR__ . '/../vendor/autoload.php';
        if (is_file($phpMailerPath)) {
          require_once $phpMailerPath;
          $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
          $mailer->isSMTP();

          if (defined('SMTP_PASS') && trim(SMTP_PASS) !== '') {
            // Gmail autenticado
            $mailer->Host = SMTP_HOST;
            $mailer->SMTPAuth = true;
            $mailer->Username = SMTP_USER;
            $mailer->Password = SMTP_PASS;
            $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = (int) SMTP_PORT;
          } else {
            // Fallback relay por dominio
            error_log('[MAIL] SMTP_PASS vacío/ausente. Fallback smtp-relay.gmail.com');
            $mailer->Host = 'smtp-relay.gmail.com';
            $mailer->SMTPAuth = false;
            $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = 587;
          }

          $from = defined('SMTP_USER') ? SMTP_USER : 'no-reply@camella.com.co';
          $mailer->CharSet = 'UTF-8';
          $mailer->setFrom($from, 'Camella.com.co');
          $mailer->addAddress($to);
          $mailer->isHTML(true);
          $mailer->Subject = $subject;
          $mailer->Body    = $html;

          $ok = $mailer->send();
          if (!$ok) { error_log('[MAIL][ERROR] ' . substr($mailer->ErrorInfo ?? 'unknown', 0, 250)); }
          return $ok;
        }

        // 2) Sin PHPMailer → intentar mail() nativa
        $from = defined('SMTP_USER') ? SMTP_USER : 'no-reply@camella.com.co';
        @ini_set('sendmail_from', $from);
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Camella.com.co <{$from}>\r\n";

        $ok = @mail($to, $subject, $html, $headers);
        if (!$ok) error_log('[MAIL][ERROR] mail() returned false');
        return $ok;

      } catch (Throwable $e) {
        error_log('[MAIL][EXCEPTION] ' . substr($e->getMessage(), 0, 350));
        return false;
      }
    }

    // (Opcional) Adapta nombres legacy: si existía enviar(), envíarCorreo(), etc., redirígelos:
    public static function enviar(string $to, string $subject, string $html): bool {
      return self::send($to, $subject, $html);
    }

    /**
     * Enviar email de reset de contraseña - MÉTODO LEGACY para compatibilidad
     */
    public static function enviarResetPassword(string $email, string $resetLink): bool {
      $subject = "Recuperar contraseña - " . (defined('APP_NAME') ? APP_NAME : 'Camella.com.co');
      $html = self::construirMensajeResetHTML($email, $resetLink);
      return self::send($email, $subject, $html);
    }

    /**
     * Construir mensaje HTML de recuperación de contraseña
     */
    private static function construirMensajeResetHTML(string $email, string $resetLink): string {
      $appName = defined('APP_NAME') ? APP_NAME : 'Camella.com.co';
      return "
      <!DOCTYPE html>
      <html>
      <head>
          <meta charset='UTF-8'>
          <title>Recuperar contraseña - {$appName}</title>
      </head>
      <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
              <h2 style='color: #2c3e50;'>Recuperar contraseña</h2>
              
              <p>Hola,</p>
              
              <p>Has solicitado recuperar tu contraseña en <strong>{$appName}</strong>.</p>
              
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
                  Equipo de {$appName}
              </p>
          </div>
      </body>
      </html>";
    }
  }
}
?>