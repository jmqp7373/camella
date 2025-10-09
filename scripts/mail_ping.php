<?php
header('Content-Type: text/plain; charset=utf-8');
$TOKEN = 'CAMELLA-PING-2025';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(403); exit("Forbidden\n"); }

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

try {
  $ok = MailHelper::send(
    'superadmin@camella.com.co',
    'Ping SMTP Camella',
    '<p>OK</p>'
  );
  echo $ok ? "MAIL_PING: OK\n" : "MAIL_PING: FAIL\n";
} catch (Throwable $e) {
  error_log('[MAIL_PING][ERROR] ' . substr($e->getMessage(), 0, 350));
  echo "MAIL_PING: FAIL\n";
}