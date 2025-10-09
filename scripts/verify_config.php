<?php
/**
 * scripts/verify_config.php
 * Verificador seguro de configuración (no expone secretos).
 * Uso: /scripts/verify_config.php?t=CAMELLA-VERIF-UNICO-2025
 * IMPORTANTE: borrar este archivo al terminar.
 */
header('Content-Type: text/plain; charset=utf-8');

// --- Protección por token simple (cámbialo antes de usar) ---
$TOKEN = 'CAMELLA-VERIF-UNICO-2025';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) {
  http_response_code(403);
  exit("Forbidden\n");
}

require_once __DIR__ . '/../config/config.php';

function ok($b){ return $b ? 'OK' : 'FAIL'; }

$checks = [];

// SMTP_* checks (sin revelar valores)
$checks['SMTP_HOST_defined']   = defined('SMTP_HOST');
$checks['SMTP_USER_defined']   = defined('SMTP_USER');
$checks['SMTP_PASS_defined']   = defined('SMTP_PASS');
$checks['SMTP_PORT_defined']   = defined('SMTP_PORT');
$checks['SMTP_SECURE_defined'] = defined('SMTP_SECURE');

$checks['SMTP_HOST_nonempty']  = $checks['SMTP_HOST_defined'] && (trim(constant('SMTP_HOST')) !== '');
$checks['SMTP_USER_nonempty']  = $checks['SMTP_USER_defined'] && (trim(constant('SMTP_USER')) !== '');
$checks['SMTP_PASS_len>=16']   = $checks['SMTP_PASS_defined'] && (strlen(str_replace(' ', '', (string)constant('SMTP_PASS'))) >= 16);
$checks['SMTP_PORT_numeric']   = $checks['SMTP_PORT_defined'] && is_numeric(constant('SMTP_PORT'));
$checks['SMTP_SECURE_valid']   = $checks['SMTP_SECURE_defined'] && in_array(strtolower(constant('SMTP_SECURE')), ['tls','ssl'], true);

// ENCRYPTION_KEY
$checks['ENCRYPTION_KEY_defined'] = defined('ENCRYPTION_KEY');
$ek = $checks['ENCRYPTION_KEY_defined'] ? (string)constant('ENCRYPTION_KEY') : '';
$checks['ENCRYPTION_KEY_len32']    = $checks['ENCRYPTION_KEY_defined'] && (strlen($ek) === 32);

// Resumen
echo "CONFIG CHECK — Camella.com.co\n";
foreach ($checks as $k => $v) {
  echo str_pad($k, 28) . ': ' . ok($v) . "\n";
}

// Recomendación final
$allGood = $checks['SMTP_HOST_nonempty'] && $checks['SMTP_USER_nonempty'] && $checks['SMTP_PASS_len>=16']
           && $checks['SMTP_PORT_numeric'] && $checks['SMTP_SECURE_valid'] && $checks['ENCRYPTION_KEY_len32'];
echo "\nOVERALL: " . ($allGood ? "READY ✅" : "INCOMPLETE ❌") . "\n";