<?php
header('Content-Type: text/plain; charset=utf-8');
$T='CAMELLA-PHPINFO-2025'; if(($_GET['t']??'')!==$T){http_response_code(403);exit("Forbidden\n");}
phpinfo();