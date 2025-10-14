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
                '24h' => "created_at >= NOW() - INTERVAL 1 DAY",
                '7d' => "created_at >= NOW() - INTERVAL 7 DAY",
                '30d' => "created_at >= NOW() - INTERVAL 30 DAY",
                default => "created_at >= NOW() - INTERVAL 1 DAY"
            };

            // Obtener estadísticas
            $stmt = $this->pdo->prepare("
                SELECT 
                    COUNT(*) as total_enviados,
                    SUM(CASE WHEN status = 'used' THEN 1 ELSE 0 END) as entregas_exitosas,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as fallidos,
                    SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expirados
                FROM verification_codes_history 
                WHERE $timeCondition
            ");
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calcular costo estimado (Twilio cobra aprox $0.0079 por SMS en Colombia)
            $costPerSMS = 0.0079;
            $totalCost = ($stats['total_enviados'] ?? 0) * $costPerSMS;

            return [
                'total_enviados' => $stats['total_enviados'] ?? 0,
                'entregas_exitosas' => $stats['entregas_exitosas'] ?? 0,
                'fallidos' => $stats['fallidos'] ?? 0,
                'expirados' => $stats['expirados'] ?? 0,
                'costo_estimado' => number_format($totalCost, 2),
                'tasa_exito' => $stats['total_enviados'] > 0 
                    ? round(($stats['entregas_exitosas'] / $stats['total_enviados']) * 100, 1) 
                    : 0
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
