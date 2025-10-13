<?php
/**
 * Script de prueba para envío de SMS con Twilio
 * Camella.com.co - Prueba de conectividad local con ngrok
 */

require __DIR__ . '/../vendor/autoload.php'; // Autoload de Composer
require __DIR__ . '/../config/config.php'; // Incluye la configuración de Twilio

use Twilio\Rest\Client;

// Crear cliente Twilio con las constantes definidas
$twilio = new Client(TWILIO_SID, TWILIO_AUTH_TOKEN);

try {
    // Enviar el mensaje de prueba
    $message = $twilio->messages->create(
        '+573103951529', // Número de destino (tu número verificado)
        [
            'from' => TWILIO_FROM_NUMBER, // Número Twilio remitente
            'body' => 'Hola desde tu entorno local con Twilio y ngrok 🚀'
        ]
    );

    echo "✅ Mensaje enviado correctamente. SID: " . $message->sid;

} catch (Exception $e) {
    echo "❌ Error al enviar el mensaje: " . $e->getMessage();
}
?>