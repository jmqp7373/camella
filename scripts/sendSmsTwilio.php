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

// Número de destino - CAMBIA ESTO por el número que quieras probar
$numeroDestino = '+573103951529'; // Tu número verificado

// Código de prueba
$codigoPrueba = rand(100000, 999999);

// Mensaje de prueba (corto para cuenta trial de Twilio)
$mensajePrueba = "Camella.com.co\n";
$mensajePrueba .= "Codigo de prueba: {$codigoPrueba}\n";
$mensajePrueba .= "Valido 5 min.";

try {
    echo "📤 Enviando SMS de prueba...\n";
    echo "📱 Destino: {$numeroDestino}\n";
    echo "💬 Mensaje: {$mensajePrueba}\n";
    echo "-----------------------------------\n";
    
    // Enviar el mensaje de prueba
    $message = $twilio->messages->create(
        $numeroDestino, // Número de destino
        [
            'from' => TWILIO_FROM_NUMBER, // Número Twilio remitente
            'body' => $mensajePrueba
        ]
    );

    echo "\n✅ Mensaje enviado correctamente!\n";
    echo "📋 SID: " . $message->sid . "\n";
    echo "📊 Estado: " . $message->status . "\n";
    echo "💰 Precio: " . $message->price . " " . $message->priceUnit . "\n";
    echo "📅 Fecha: " . $message->dateCreated->format('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "\n❌ Error al enviar el mensaje:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
}
?>
