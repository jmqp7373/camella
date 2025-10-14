<?php
/**
 * Helper para obtener estadísticas de Twilio
 * Usado en el dashboard de admin
 */

// Cargar solo las dependencias necesarias
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

/**
 * Clase auxiliar para obtener estadísticas de Twilio sin ejecutar el controlador completo
 */
class TwilioStatsProvider {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en TwilioStatsProvider: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    public function getTwilioStats($period = '24h') {
        if (!$this->pdo) return null;

        try {
            // Definir la condición temporal según el período
            $timeCondition = match($period) {
                '24h' => "1 DAY",
                '7d' => "7 DAY",
                '30d' => "1 MONTH",
                default => "1 DAY"
            };

            // Obtener estadísticas
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_enviados,
                    SUM(CASE WHEN status = 'created' THEN 1 ELSE 0 END) as total_no_usados
                FROM verification_codes_history 
                WHERE created_at >= NOW() - INTERVAL {$timeCondition}
            ");
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $enviados = (int) ($stats['total_enviados'] ?? 0);
            $noUsados = (int) ($stats['total_no_usados'] ?? 0);
            
            // Calcular tasa de éxito: (Enviados - No Usados) / Enviados * 100
            $tasaExito = $enviados > 0 
                ? round((($enviados - $noUsados) / $enviados) * 100, 2) 
                : 0;

            // Calcular costo estimado (Twilio cobra aprox $0.0079 por SMS en Colombia)
            $costPerSMS = 0.0079;
            $totalCost = $enviados * $costPerSMS;

            return [
                'total_enviados' => $enviados,
                'total_no_usados' => $noUsados,
                'costo_estimado' => number_format($totalCost, 2),
                'tasa_exito' => $tasaExito
            ];

        } catch (Exception $e) {
            error_log("Error obteniendo stats de Twilio: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Función helper para obtener todas las estadísticas
 */
function getTwilioStatistics() {
    $provider = new TwilioStatsProvider();
    
    return [
        '24h' => $provider->getTwilioStats('24h'),
        '7d' => $provider->getTwilioStats('7d'),
        '30d' => $provider->getTwilioStats('30d')
    ];
}
