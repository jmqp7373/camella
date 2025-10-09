<?php
// /scripts/mail_ping.php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

$TOKEN = 'CAMELLA-PING-2025';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(403); exit("Forbidden\n"); }

try {
  require_once __DIR__ . '/../config/config.php';     // recordar: este archivo lo sube Mauricio por FTP
  require_once __DIR__ . '/../helpers/MailHelper.php';

  if (!class_exists('MailHelper') || !method_exists('MailHelper', 'send')) {
    error_log('[MAIL_PING] MailHelper::send no existe');
    echo "MAIL_PING: FAIL\n";
    exit;
  }

  $ok = MailHelper::send('superadmin@camella.com.co', 'Ping SMTP Camella', '<p>OK</p>');
  echo $ok ? "MAIL_PING: OK\n" : "MAIL_PING: FAIL\n";

} catch (Throwable $e) {
  error_log('[MAIL_PING][EXCEPTION] ' . substr($e->getMessage(), 0, 350));
  http_response_code(500);
  echo "MAIL_PING: FAIL\n";
}