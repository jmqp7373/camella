<?php
/**
 * VERIFICAR ESTADO DE CUENTA TWILIO
 * 
 * Este script consulta la API de Twilio para verificar:
 * - Si la cuenta está en modo Trial o Paid
 * - Si el número +573103951529 está en la lista de verificados
 * - Balance de la cuenta
 * - Estado del número desde (+14783959907)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado Cuenta Twilio - Camella</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 24px; margin-bottom: 10px; }
        .content { padding: 30px; }
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
            text-align: right;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status.trial { background: #fff3cd; color: #856404; }
        .status.active { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .status.warning { background: #fff3cd; color: #856404; }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .warning-box ul {
            margin-left: 20px;
            color: #856404;
        }
        .success-box {
            background: #d4edda;
            border: 2px solid #28a745;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .success-box h3 {
            color: #155724;
            margin-bottom: 10px;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Estado de Cuenta Twilio</h1>
            <p>Verificación de modo Trial y números verificados</p>
        </div>
        
        <div class="content">
            <?php
            try {
                // Crear cliente Twilio
                $twilio = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);
                
                echo '<div class="section">';
                echo '<h2>✅ Conexión a Twilio Exitosa</h2>';
                echo '<div class="info-item">';
                echo '<span class="info-label">Account SID:</span>';
                echo '<span class="info-value">' . TWILIO_SID . '</span>';
                echo '</div>';
                echo '</div>';
                
                // Obtener información de la cuenta
                echo '<div class="section">';
                echo '<h2>📊 Información de la Cuenta</h2>';
                
                try {
                    $account = $twilio->api->v2010->accounts(TWILIO_SID)->fetch();
                    
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Nombre de cuenta:</span>';
                    echo '<span class="info-value">' . htmlspecialchars($account->friendlyName) . '</span>';
                    echo '</div>';
                    
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Estado:</span>';
                    echo '<span class="info-value"><span class="status active">' . $account->status . '</span></span>';
                    echo '</div>';
                    
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Tipo de cuenta:</span>';
                    $isTrial = ($account->type === 'Trial');
                    echo '<span class="info-value"><span class="status ' . ($isTrial ? 'trial' : 'active') . '">';
                    echo $isTrial ? '⚠️ TRIAL' : '✅ PAID';
                    echo '</span></span>';
                    echo '</div>';
                    
                    if ($isTrial) {
                        echo '<div class="warning-box">';
                        echo '<h3>⚠️ CUENTA EN MODO TRIAL</h3>';
                        echo '<p>Tu cuenta está en modo Trial, lo que significa:</p>';
                        echo '<ul>';
                        echo '<li><strong>Solo puedes enviar SMS a números verificados</strong></li>';
                        echo '<li>Los mensajes incluyen texto adicional de Twilio</li>';
                        echo '<li>Límites en cantidad de mensajes</li>';
                        echo '</ul>';
                        echo '<p style="margin-top: 15px;"><strong>Solución:</strong></p>';
                        echo '<ul>';
                        echo '<li>Verificar +573103951529 en: <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/verified" target="_blank">Twilio Console</a></li>';
                        echo '<li>O actualizar a cuenta Paid (recomendado)</li>';
                        echo '</ul>';
                        echo '</div>';
                    } else {
                        echo '<div class="success-box">';
                        echo '<h3>✅ Cuenta PAID Activa</h3>';
                        echo '<p>Puedes enviar SMS a cualquier número sin restricciones</p>';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Error:</span>';
                    echo '<span class="info-value status error">' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                // Verificar el número desde (FROM)
                echo '<div class="section">';
                echo '<h2>📱 Número Desde (FROM)</h2>';
                
                try {
                    $incomingPhoneNumbers = $twilio->incomingPhoneNumbers->read([
                        'phoneNumber' => TWILIO_FROM_NUMBER,
                        'limit' => 1
                    ]);
                    
                    if (count($incomingPhoneNumbers) > 0) {
                        $phoneNumber = $incomingPhoneNumbers[0];
                        
                        echo '<div class="info-item">';
                        echo '<span class="info-label">Número:</span>';
                        echo '<span class="info-value">' . $phoneNumber->phoneNumber . '</span>';
                        echo '</div>';
                        
                        echo '<div class="info-item">';
                        echo '<span class="info-label">Nombre:</span>';
                        echo '<span class="info-value">' . htmlspecialchars($phoneNumber->friendlyName) . '</span>';
                        echo '</div>';
                        
                        echo '<div class="info-item">';
                        echo '<span class="info-label">Capacidades SMS:</span>';
                        echo '<span class="info-value"><span class="status ' . ($phoneNumber->capabilities['sms'] ? 'active' : 'error') . '">';
                        echo $phoneNumber->capabilities['sms'] ? '✅ HABILITADO' : '❌ DESHABILITADO';
                        echo '</span></span>';
                        echo '</div>';
                        
                    } else {
                        echo '<div class="warning-box">';
                        echo '<h3>⚠️ Número no encontrado en tu cuenta</h3>';
                        echo '<p>El número <strong>' . TWILIO_FROM_NUMBER . '</strong> no está registrado en tu cuenta Twilio.</p>';
                        echo '<p>Verifica que sea el número correcto o cómpralo en <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/search" target="_blank">Twilio Console</a></p>';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Error:</span>';
                    echo '<span class="info-value status error">' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                // Números verificados (para cuentas Trial)
                echo '<div class="section">';
                echo '<h2>✓ Números Verificados</h2>';
                echo '<p style="margin-bottom: 15px; color: #666;">Si tu cuenta es Trial, solo estos números pueden recibir SMS:</p>';
                
                try {
                    $outgoingCallerIds = $twilio->outgoingCallerIds->read(['limit' => 20]);
                    
                    if (count($outgoingCallerIds) > 0) {
                        foreach ($outgoingCallerIds as $callerId) {
                            $phoneNumber = $callerId->phoneNumber;
                            $isTarget = ($phoneNumber === '+573103951529');
                            
                            echo '<div class="info-item" style="' . ($isTarget ? 'background: #d4edda; border: 2px solid #28a745;' : '') . '">';
                            echo '<span class="info-label">' . $phoneNumber . '</span>';
                            echo '<span class="info-value">';
                            if ($isTarget) {
                                echo '<span class="status active">✅ TU NÚMERO</span>';
                            } else {
                                echo '<span class="status">Verificado</span>';
                            }
                            echo '</span>';
                            echo '</div>';
                        }
                        
                        $encontrado = false;
                        foreach ($outgoingCallerIds as $callerId) {
                            if ($callerId->phoneNumber === '+573103951529') {
                                $encontrado = true;
                                break;
                            }
                        }
                        
                        if (!$encontrado) {
                            echo '<div class="warning-box">';
                            echo '<h3>❌ +573103951529 NO está verificado</h3>';
                            echo '<p><strong>Este es el problema:</strong> Tu número no está en la lista de números verificados.</p>';
                            echo '<p style="margin-top: 15px;"><strong>Solución:</strong></p>';
                            echo '<ol>';
                            echo '<li>Ir a: <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/verified" target="_blank">Verificar Números</a></li>';
                            echo '<li>Click en "Verify a number"</li>';
                            echo '<li>Ingresar: <strong>+573103951529</strong></li>';
                            echo '<li>Recibirás una llamada con un código de verificación</li>';
                            echo '<li>Ingresa el código y listo</li>';
                            echo '</ol>';
                            echo '</div>';
                        } else {
                            echo '<div class="success-box">';
                            echo '<h3>✅ Tu número está verificado</h3>';
                            echo '<p>El número +573103951529 puede recibir SMS</p>';
                            echo '</div>';
                        }
                        
                    } else {
                        echo '<div class="warning-box">';
                        echo '<p>No hay números verificados en tu cuenta.</p>';
                        echo '<p><strong>Acción requerida:</strong> Verifica +573103951529 en <a href="https://console.twilio.com/us1/develop/phone-numbers/manage/verified" target="_blank">Twilio Console</a></p>';
                        echo '</div>';
                    }
                    
                } catch (Exception $e) {
                    echo '<div class="info-item">';
                    echo '<span class="info-label">Error:</span>';
                    echo '<span class="info-value status error">' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="section">';
                echo '<h2>❌ Error de Conexión</h2>';
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
                echo '</div>';
            }
            ?>
            
            <div class="warning-box" style="text-align: center;">
                <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de usarlo
            </div>
        </div>
    </div>
</body>
</html>
