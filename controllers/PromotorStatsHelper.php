<?php
/**
 * Helper para obtener estadísticas de Promotor
 * Usado en el dashboard de admin
 */

// Cargar solo las dependencias necesarias
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Clase auxiliar para obtener estadísticas de Promotor
 */
class PromotorStatsProvider {
    private $pdo;
    
    public function __construct() {
        try {
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en PromotorStatsProvider: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    public function getPromotorStats($period = '24h') {
        if (!$this->pdo) {
            return [
                'usuarios_registrados' => 0,
                'publicaciones_activas' => 0,
                'promedio_por_usuario' => '0.00'
            ];
        }

        // Definir la condición temporal según el período
        $timeCondition = match($period) {
            '24h' => "1 DAY",
            '7d' => "7 DAY",
            '30d' => "1 MONTH",
            default => "1 DAY"
        };

        // Inicializar valores por defecto
        $usuariosRegistrados = 0;
        $publicacionesActivas = 0;

        // Query 1: Usuarios registrados (independiente)
        try {
            $usuariosQuery = $this->pdo->query("
                SELECT COUNT(*) AS total
                FROM users
                WHERE created_at >= NOW() - INTERVAL {$timeCondition}
            ");
            $usuariosRegistrados = (int) $usuariosQuery->fetchColumn();
        } catch (Exception $e) {
            error_log("Error contando usuarios: " . $e->getMessage());
        }

        // Query 2: Publicaciones activas (independiente, puede fallar si tabla no existe)
        try {
            $publicacionesQuery = $this->pdo->query("
                SELECT COUNT(*) AS total
                FROM servicios
                WHERE created_at >= NOW() - INTERVAL {$timeCondition}
                AND status = 'activo'
            ");
            $publicacionesActivas = (int) $publicacionesQuery->fetchColumn();
        } catch (Exception $e) {
            // Si la tabla servicios no existe, simplemente dejar en 0
            error_log("Advertencia: tabla servicios no disponible - " . $e->getMessage());
            $publicacionesActivas = 0;
        }

        // Calcular promedio (evitar división por cero)
        $porUsuario = ($usuariosRegistrados > 0) 
            ? number_format($publicacionesActivas / $usuariosRegistrados, 2)
            : '0.00';

        return [
            'usuarios_registrados' => $usuariosRegistrados,
            'publicaciones_activas' => $publicacionesActivas,
            'promedio_por_usuario' => $porUsuario
        ];
    }
}

/**
 * Función helper para obtener todas las estadísticas
 */
function getPromotorStatistics() {
    $provider = new PromotorStatsProvider();
    
    return [
        '24h' => $provider->getPromotorStats('24h'),
        '7d' => $provider->getPromotorStats('7d'),
        '30d' => $provider->getPromotorStats('30d')
    ];
}
