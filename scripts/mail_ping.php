<?php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

$TOKEN = 'CAMELLA-PING-2025';
if (($_GET['t'] ?? '') !== $TOKEN) { http_response_code(403); exit("Forbidden\n"); }

try {
  // Forzar logs visibles en error_log
  ini_set('log_errors','1');
  ini_set('display_errors','0');

  require_once __DIR__.'/../config/config.php';   // <- Mauricio lo sube por FTP

  // Carga MailHelper si existe y tiene send()
  $helperPath = __DIR__.'/../helpers/MailHelper.php';
  if (is_file($helperPath)) require_once $helperPath;

  $to = 'superadmin@camella.com.co';
  $sub= 'Ping SMTP Camella';
  $html = '<p>OK</p>';

  // 1) Si existe MailHelper::send(), úsalo
  if (class_exists('MailHelper') && method_exists('MailHelper','send')) {
    $ok = MailHelper::send($to, $sub, $html);
    echo $ok ? "MAIL_PING: OK\n" : "MAIL_PING: FAIL\n";
    exit;
  }

  // 2) Sin MailHelper (o PHPMailer), usar mail() nativa como fallback
  $from = defined('SMTP_USER') ? SMTP_USER : 'no-reply@camella.com.co';
  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=UTF-8\r\n";
  $headers .= "From: Camella.com.co <{$from}>\r\n";
  // En algunos hostings ayuda setear sendmail_from
  @ini_set('sendmail_from', $from);

  $ok = @mail($to, $sub, $html, $headers);
  if (!$ok) error_log('[MAIL_PING] mail() returned false');
  echo $ok ? "MAIL_PING: OK\n" : "MAIL_PING: FAIL\n";

} catch (Throwable $e) {
  error_log('[MAIL_PING][EXCEPTION] '.substr($e->getMessage(),0,350));
  http_response_code(500);
  echo "MAIL_PING: FAIL\n";
}