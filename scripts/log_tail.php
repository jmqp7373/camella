<?php
header('Content-Type: text/plain; charset=utf-8');
$T='CAMELLA-LOGS-2025'; if(($_GET['t']??'')!==$T){http_response_code(403);exit("Forbidden\n");}
$paths=[ini_get('error_log'), __DIR__.'/../error_log', __DIR__.'/../logs/error_log','/usr/local/apache/logs/error_log'];
foreach($paths as $p){ if($p && is_readable($p)){ $lines=@file($p); if($lines){ echo "== $p ==\n"; echo implode('',array_slice($lines,-200)); exit; } } }
echo "No readable error_log found.\n";