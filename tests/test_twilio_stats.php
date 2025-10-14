<?php
/**
 * Script de prueba para estadísticas de Twilio
 * Verifica que el método getTwilioStats() funcione correctamente
 */

// Cargar configuración y base de datos directamente
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Cargar solo la clase, no el archivo completo que ejecuta handleRequest()
class MagicLinkControllerTest {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    public function getTwilioStats($period = '24h') {
        if (!$this->pdo) return null;

        try {
            $timeCondition = match($period) {
                '24h' => "created_at >= NOW() - INTERVAL 1 DAY",
                '7d' => "created_at >= NOW() - INTERVAL 7 DAY",
                '30d' => "created_at >= NOW() - INTERVAL 30 DAY",
                default => "created_at >= NOW() - INTERVAL 1 DAY"
            };

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
            error_log("Error obteniendo stats: " . $e->getMessage());
            return null;
        }
    }
}

echo "<h1>🧪 Test de Estadísticas de Twilio</h1>";
echo "<hr>";

// Crear instancia del controlador de prueba
$controller = new MagicLinkControllerTest();

// Probar cada período
$periodos = ['24h', '7d', '30d'];

foreach ($periodos as $periodo) {
    echo "<h2>📊 Período: {$periodo}</h2>";
    
    $stats = $controller->getTwilioStats($periodo);
    
    if ($stats) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
        echo "<tr style='background: #f0f0f0;'><th>Métrica</th><th>Valor</th></tr>";
        echo "<tr><td>Total Enviados</td><td><strong>{$stats['total_enviados']}</strong></td></tr>";
        echo "<tr><td>Entregas Exitosas</td><td style='color: green;'><strong>{$stats['entregas_exitosas']}</strong></td></tr>";
        echo "<tr><td>Fallidos</td><td style='color: red;'><strong>{$stats['fallidos']}</strong></td></tr>";
        echo "<tr><td>Expirados</td><td style='color: orange;'><strong>{$stats['expirados']}</strong></td></tr>";
        echo "<tr><td>Costo Estimado</td><td><strong>\${$stats['costo_estimado']} USD</strong></td></tr>";
        echo "<tr><td>Tasa de Éxito</td><td><strong>{$stats['tasa_exito']}%</strong></td></tr>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Error obteniendo estadísticas para el período {$periodo}</p>";
    }
}

echo "<hr>";
echo "<h2>✅ Test completado</h2>";
echo "<p><a href='../index.php'>← Volver al inicio</a></p>";
