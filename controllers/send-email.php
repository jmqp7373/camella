<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__.'/../config/email-config.php';
require_once __DIR__.'/../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__.'/../includes/PHPMailer/src/SMTP.php';
require_once __DIR__.'/../includes/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function ok($d,$c=200){http_response_code($c);echo json_encode($d,JSON_UNESCAPED_UNICODE);exit;}
function logdir(){ if(!is_dir(EMAIL_LOG_DIR)) @mkdir(EMAIL_LOG_DIR,0755,true);}
function logline($lvl,$msg){logdir();@file_put_contents(EMAIL_LOG_DIR.'/email.log','['.date('Y-m-d H:i:s')."] [$lvl] $msg\n",FILE_APPEND);}
function ip(){return $_SERVER['REMOTE_ADDR']??'0.0.0.0';}
function stext($s,$m=200){$s=trim((string)$s);$s=strip_tags($s);$s=preg_replace('/\s+/',' ',$s);return mb_substr($s,0,$m);}
function smsg($s,$m=MAX_MESSAGE_LENGTH){$s=trim((string)$s);return mb_substr($s,0,$m);}
function rl(){logdir();$p=EMAIL_LOG_DIR.'/ratelimit.json';$n=time();$w=$n-3600;$d=[];$raw=@file_get_contents($p);if($raw)$d=json_decode($raw,true)?:[];$addr=ip();$h=$d[$addr]??[];$h=array_values(array_filter($h,fn($t)=>(int)$t>=$w));if(count($h)>=RATE_LIMIT_EMAILS){$d[$addr]=$h;@file_put_contents($p,json_encode($d));return false;}$h[]=$n;$d[$addr]=$h;@file_put_contents($p,json_encode($d));return true;}
function cors(){ $o=$_SERVER['HTTP_ORIGIN']??''; if($o && in_array($o,ALLOWED_ORIGINS,true)){header('Access-Control-Allow-Origin: '.$o);header('Vary: Origin');} header('Access-Control-Allow-Methods: POST, OPTIONS'); header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');}
cors(); if($_SERVER['REQUEST_METHOD']==='OPTIONS') exit(0);
if($_SERVER['REQUEST_METHOD']!=='POST') ok(['success'=>false,'error'=>'Método no permitido'],405);
if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='XMLHttpRequest') ok(['success'=>false,'error'=>'Solicitud inválida'],400);
if(!empty($_POST['website'])) ok(['success'=>true,'message'=>'OK']); // honeypot

$name=stext($_POST['name']??'',120);
$email=filter_var($_POST['email']??'',FILTER_VALIDATE_EMAIL);
$subject=stext($_POST['subject']??'Contacto desde camella.com.co',140);
$message=smsg($_POST['message']??'');
if(!$name||!$email||!$message) ok(['success'=>false,'error'=>'Campos requeridos faltantes'],400);
if(!rl()) {logline('ERROR','Rate limit excedido | IP: '.ip()); ok(['success'=>false,'error'=>'Límite de envíos excedido. Intenta más tarde.'],429);}

$c=MIGADU_CONFIG; $tid=uniqid('CAMELLA-'); $ip=ip();
try{
  $m=new PHPMailer(true); if(EMAIL_DEBUG)$m->SMTPDebug=2;
  $m->isSMTP(); $m->Host='smtp.migadu.com'; $m->SMTPAuth=true; $m->Username=$c['username']; $m->Password=$c['password']; $m->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS; $m->Port=465; $m->CharSet='UTF-8';
  $m->setFrom($c['from_email'],'Camella Contacto'); $m->addAddress($c['to_email']); $m->addReplyTo($email,$name);
  $m->isHTML(true); $m->Subject=$subject; $safe=nl2br(htmlspecialchars($message,ENT_QUOTES,'UTF-8'));
  $m->Body="<h2>Nuevo mensaje de contacto</h2><p><b>Nombre:</b> $name</p><p><b>Email:</b> $email</p><p><b>IP:</b> $ip</p><p><b>Tracking ID:</b> $tid</p><hr><p><b>Mensaje:</b><br>$safe</p>";
  $m->AltBody="Nombre: $name\nEmail: $email\nIP: $ip\nTracking: $tid\n\nMensaje:\n$message";
  $m->send();

  if(defined('SEND_USER_CONFIRMATION') && SEND_USER_CONFIRMATION){
    $a=new PHPMailer(true); if(EMAIL_DEBUG)$a->SMTPDebug=2;
    $a->isSMTP(); $a->Host='smtp.migadu.com'; $a->SMTPAuth=true; $a->Username=$c['username']; $a->Password=$c['password']; $a->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS; $a->Port=465; $a->CharSet='UTF-8';
    $a->setFrom($c['from_email'],'Camella'); $a->addAddress($email,$name); $a->isHTML(true);
    $a->Subject='Hemos recibido tu mensaje - Camella';
    $a->Body="<p>Hola $name,</p><p>Hemos recibido tu mensaje y te responderemos pronto.</p><p><b>Tracking ID:</b> $tid</p><hr><p>— Equipo Camella</p>";
    $a->AltBody="Hola $name,\nHemos recibido tu mensaje. Tracking: $tid\n— Equipo Camella";
    $a->send();
  }

  logline('SUCCESS',"Email enviado a {$c['to_email']} | IP: $ip | Tracking: $tid");
  ok(['success'=>true,'message'=>'Email enviado correctamente','tracking_id'=>$tid]);
}catch(Exception $e){
  logline('ERROR','Mailer: '.$e->getMessage());
  ok(['success'=>false,'error'=>EMAIL_DEBUG?$e->getMessage():'Error al enviar email'],500);
}catch(Throwable $t){
  logline('ERROR','Throwable: '.$t->getMessage());
  ok(['success'=>false,'error'=>'Error inesperado'],500);
}
