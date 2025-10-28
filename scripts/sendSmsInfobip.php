<?php
/**
 * Envío de SMS mediante Infobip
 * 
 * Proveedor principal de SMS para Camella.com.co
 * Con fallback a WhatsApp Infobip y Twilio en caso de error
 * 
 * @param string $telefono Número en formato internacional (+573103951529)
 * @param string $codigo Código OTP de 6 dígitos
 * @param string $token Magic link token de 8 caracteres
 * @return array ['status' => 'success'|'error', 'response' => mixed, 'provider' => 'infobip'|'twilio']
 */
function sendSmsInfobip($telefono, $codigo, $token) {
    // Verificar que las constantes existan
    if (!defined('INFOBIP_API_KEY') || !defined('INFOBIP_BASE_URL')) {
        error_log("ERROR sendSmsInfobip: Constantes INFOBIP no definidas");
        return [
            'status' => 'error',
            'response' => 'Configuración de Infobip no disponible',
            'provider' => 'none'
        ];
    }

    // Configuración del endpoint
    $endpoint = INFOBIP_BASE_URL . '/sms/2/text/advanced';
    $apiKey = INFOBIP_API_KEY;

    // Construir el mensaje
    $magicLink = "camella.com.co/m/{$token}";
    $mensaje = "Tu código de acceso a Camella es {$codigo}. Ingresa aquí: {$magicLink}";

    // Payload JSON para Infobip
    $payload = [
        'messages' => [
            [
                'from' => 'Camella',
                'destinations' => [
                    ['to' => $telefono]
                ],
                'text' => $mensaje
            ]
        ]
    ];

    error_log("Infobip: Enviando SMS a {$telefono}");
    error_log("Infobip: Mensaje: {$mensaje}");

    // Configurar cURL
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: App ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    // Ejecutar petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Log de respuesta
    error_log("Infobip: HTTP Code: {$httpCode}");
    error_log("Infobip: Response: {$response}");

    // Verificar éxito
    if ($httpCode >= 200 && $httpCode < 300 && empty($curlError)) {
        $responseData = json_decode($response, true);
        
        // Verificar que el mensaje fue aceptado
        if (isset($responseData['messages'][0]['status']['groupName']) 
            && $responseData['messages'][0]['status']['groupName'] === 'PENDING') {
            
            error_log("Infobip: SMS enviado exitosamente");
            return [
                'status' => 'success',
                'response' => $responseData,
                'provider' => 'infobip',
                'message_id' => $responseData['messages'][0]['messageId'] ?? null
            ];
        }
    }

    // Si llegamos aquí, el SMS falló
    error_log("Infobip: Error enviando SMS - HTTP {$httpCode}");
    if (!empty($curlError)) {
        error_log("Infobip: cURL Error: {$curlError}");
    }

    /* 
    // FALLBACK 1: WhatsApp Infobip (PREPARADO - Descomentar cuando esté configurado)
    error_log("Infobip: Intentando fallback a WhatsApp...");
    
    $whatsappEndpoint = INFOBIP_BASE_URL . '/whatsapp/1/message/text';
    $whatsappPayload = [
        'from' => 'TU_NUMERO_WHATSAPP_INFOBIP',
        'to' => $telefono,
        'content' => [
            'text' => $mensaje
        ]
    ];
    
    $ch = curl_init($whatsappEndpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($whatsappPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: App ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $whatsappResponse = curl_exec($ch);
    $whatsappHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($whatsappHttpCode >= 200 && $whatsappHttpCode < 300) {
        error_log("Infobip WhatsApp: Mensaje enviado exitosamente");
        return [
            'status' => 'success',
            'response' => json_decode($whatsappResponse, true),
            'provider' => 'infobip-whatsapp'
        ];
    }
    
    error_log("Infobip WhatsApp: Falló - HTTP {$whatsappHttpCode}");
    */

    // FALLBACK 2: Twilio (ACTIVO)
    error_log("Infobip: Todos los métodos fallaron, intentando Twilio como respaldo...");
    
    // Verificar si existe la función de Twilio
    if (file_exists(__DIR__ . '/sendSmsTwilio.php')) {
        require_once __DIR__ . '/sendSmsTwilio.php';
        
        if (function_exists('sendSmsTwilio')) {
            error_log("Infobip: Usando Twilio como fallback");
            $twilioResult = sendSmsTwilio($telefono, $codigo, $token);
            
            if ($twilioResult['status'] === 'success') {
                return [
                    'status' => 'success',
                    'response' => $twilioResult['response'],
                    'provider' => 'twilio-fallback',
                    'note' => 'Infobip falló, se usó Twilio'
                ];
            }
        }
    } else {
        // Intentar con Twilio directamente si no existe el script
        if (defined('TWILIO_SID') && defined('TWILIO_AUTH_TOKEN') && defined('TWILIO_FROM_NUMBER')) {
            try {
                // Cargar Twilio SDK si no está cargado
                if (!class_exists('Twilio\Rest\Client')) {
                    require_once __DIR__ . '/../vendor/autoload.php';
                }
                
                $twilio = new Twilio\Rest\Client(TWILIO_SID, TWILIO_AUTH_TOKEN);
                
                // Mensaje para Twilio (sin magic link por ahora)
                $twilioMessage = "Camella.com.co\n";
                $twilioMessage .= "Tu codigo de acceso: {$codigo}\n";
                $twilioMessage .= "Valido 5 min.";
                
                $message = $twilio->messages->create(
                    $telefono,
                    [
                        'from' => TWILIO_FROM_NUMBER,
                        'body' => $twilioMessage
                    ]
                );
                
                error_log("Twilio fallback: SMS enviado - SID: {$message->sid}");
                
                return [
                    'status' => 'success',
                    'response' => [
                        'sid' => $message->sid,
                        'status' => $message->status
                    ],
                    'provider' => 'twilio-fallback',
                    'note' => 'Infobip falló, se usó Twilio'
                ];
                
            } catch (Exception $e) {
                error_log("Twilio fallback: Error - " . $e->getMessage());
            }
        }
    }

    // Si llegamos aquí, todo falló
    error_log("ERROR CRÍTICO: Todos los proveedores de SMS fallaron");
    
    return [
        'status' => 'error',
        'response' => 'No se pudo enviar el SMS por ningún proveedor',
        'provider' => 'none',
        'infobip_error' => $response,
        'infobip_http_code' => $httpCode
    ];
}
