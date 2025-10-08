<?php
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../bootstrap.php';
echo "pdo_probe\n";
try {
  $pdo = $GLOBALS['pdo'] ?? (function_exists('getPDO') ? getPDO() : null);
  echo $pdo instanceof PDO ? "PDO OK\n" : "PDO FAIL\n";
  if ($pdo instanceof PDO) {
    echo "SELECT1: " . ($pdo->query('SELECT 1')->fetchColumn() ? "OK\n" : "FAIL\n");
  }
} catch (Throwable $e) {
  echo "EXC: " . get_class($e) . "\n";
  error_log('[pdo_probe] ' . $e->getMessage());
}