<?php
// /scripts/log_tail.php
declare(strict_types=1);
header('Content-Type: text/plain; charset=utf-8');

$TOKEN = 'CAMELLA-LOGS-2025';
if (!isset($_GET['t']) || $_GET['t'] !== $TOKEN) { http_response_code(403); exit("Forbidden\n"); }

// Rutas posibles (GoDaddy/cPanel). Intenta en orden:
$candidates = [
  ini_get('error_log'),
  __DIR__ . '/../error_log',
  __DIR__ . '/../logs/error_log',
  '/usr/local/apache/logs/error_log'
];

$path = null;
foreach ($candidates as $c) { if ($c && is_readable($c)) { $path = $c; break; } }

if (!$path) { echo "No readable error_log found.\n"; exit; }

$lines = @file($path);
if (!$lines) { echo "Empty or unreadable log.\n"; exit; }

$tail = array_slice($lines, -200);
echo "== error_log (tail) ==\n";
echo implode('', $tail);