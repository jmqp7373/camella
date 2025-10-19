<?php
/**
 * TEST DIRECTO: Envío de SMS con Twilio
 * 
 * Este script hace una prueba directa de envío de SMS
 * sin pasar por el sistema completo de Magic Links
 * 
 * INSTRUCCIONES:
 * 1. Subir a: https://camella.com.co/test_sms_directo.php
 * 2. Acceder desde navegador
 * 3. Ingresar tu número de teléfono
 * 4. Ver resultado del envío
 * 5. ELIMINAR después de usar
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;

header('Content-Type: text/html; charset=utf-8');

$resultado = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = $_POST['phone'];
    
    try {
        // Validar teléfono
        if (!preg_match('/^\+57[3][0-9]{9}$/', $phone)) {
            throw new Exception('Formato de teléfono inválido. Debe ser +573XXXXXXXXX');
        }
        
        // Verificar credenciales
        if (!defined('TWILIO_SID') || !defined('TWILIO_AUTH_TOKEN') || !defined('TWILIO_FROM_NUMBER')) {
            throw new Exception('Credenciales de Twilio no configuradas');
        }
        
        // Crear cliente Twilio
        $twilio = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);
        
        // Generar código de prueba
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(4));
        
        // Construir mensaje
        $baseUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/camella.com.co';
        $magicLinkUrl = "{$baseUrl}/m/{$token}";
        
        $message = "Camella.com.co\n";
        $message .= "Codigo: {$code}\n";
        $message .= "{$magicLinkUrl}\n";
        $message .= "Valido 5 min.";
        
        // Enviar SMS
        $twilioMessage = $twilio->messages->create(
            $phone,
            [
                'from' => TWILIO_FROM_NUMBER,
                'body' => $message
            ]
        );
        
        $resultado = [
            'success' => true,
            'sid' => $twilioMessage->sid,
            'status' => $twilioMessage->status,
            'code' => $code,
            'token' => $token,
            'url' => $magicLinkUrl,
            'message' => $message
        ];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test SMS Directo - Camella</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            width: 100%;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="tel"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            line-height: 1.6;
        }
        .result.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .result.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .result h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        .result-item {
            background: rgba(255,255,255,0.7);
            padding: 10px;
            margin: 8px 0;
            border-radius: 5px;
        }
        .result-item strong {
            display: inline-block;
            min-width: 100px;
        }
        pre {
            background: rgba(0,0,0,0.05);
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Test SMS Directo</h1>
            <p>Prueba de envío de SMS con Twilio</p>
        </div>
        
        <div class="content">
            <form method="POST">
                <div class="form-group">
                    <label for="phone">Número de Teléfono (con +57)</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        placeholder="+573001234567"
                        value="+573103951529"
                        required
                        pattern="\+57[3][0-9]{9}"
                    >
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                        Formato: +57 seguido de 10 dígitos (ejemplo: +573001234567)
                    </small>
                </div>
                
                <button type="submit" class="btn">
                    🚀 Enviar SMS de Prueba
                </button>
            </form>
            
            <?php if ($resultado): ?>
                <div class="result success">
                    <h3>✅ SMS Enviado Exitosamente</h3>
                    
                    <div class="result-item">
                        <strong>SID:</strong> <?= htmlspecialchars($resultado['sid']) ?>
                    </div>
                    
                    <div class="result-item">
                        <strong>Estado:</strong> <?= htmlspecialchars($resultado['status']) ?>
                    </div>
                    
                    <div class="result-item">
                        <strong>Código:</strong> <?= htmlspecialchars($resultado['code']) ?>
                    </div>
                    
                    <div class="result-item">
                        <strong>Token:</strong> <?= htmlspecialchars($resultado['token']) ?>
                    </div>
                    
                    <div class="result-item">
                        <strong>Magic Link:</strong><br>
                        <a href="<?= htmlspecialchars($resultado['url']) ?>" target="_blank">
                            <?= htmlspecialchars($resultado['url']) ?>
                        </a>
                    </div>
                    
                    <div class="result-item">
                        <strong>Mensaje enviado:</strong>
                        <pre><?= htmlspecialchars($resultado['message']) ?></pre>
                    </div>
                    
                    <div style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.9); border-radius: 5px;">
                        <strong>📱 Revisa tu teléfono</strong><br>
                        Deberías recibir el SMS en los próximos segundos
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="result error">
                    <h3>❌ Error al Enviar SMS</h3>
                    <div class="result-item">
                        <strong>Error:</strong><br>
                        <?= htmlspecialchars($error) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="warning">
                <strong>⚠️ IMPORTANTE:</strong><br>
                Elimina este archivo después de usarlo
            </div>
        </div>
    </div>
</body>
</html>
