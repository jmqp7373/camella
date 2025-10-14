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
        if (!$this->pdo) return null;

        try {
            // Definir la condición temporal según el período
            $timeCondition = match($period) {
                '24h' => "1 DAY",
                '7d' => "7 DAY",
                '30d' => "1 MONTH",
                default => "1 DAY"
            };

            // Usuarios registrados
            $usuariosQuery = $this->pdo->prepare("
                SELECT COUNT(*) AS total
                FROM users
                WHERE created_at >= NOW() - INTERVAL {$timeCondition}
            ");
            $usuariosQuery->execute();
            $usuariosRegistrados = (int) $usuariosQuery->fetchColumn();

            // Publicaciones activas
            $publicacionesQuery = $this->pdo->prepare("
                SELECT COUNT(*) AS total
                FROM servicios
                WHERE created_at >= NOW() - INTERVAL {$timeCondition}
                AND status = 'activo'
            ");
            $publicacionesQuery->execute();
            $publicacionesActivas = (int) $publicacionesQuery->fetchColumn();

            // Publicaciones por usuario (evitar división por cero)
            $porUsuario = ($usuariosRegistrados > 0) 
                ? number_format($publicacionesActivas / $usuariosRegistrados, 2)
                : '0.00';

            return [
                'usuarios_registrados' => $usuariosRegistrados,
                'publicaciones_activas' => $publicacionesActivas,
                'promedio_por_usuario' => $porUsuario
            ];

        } catch (Exception $e) {
            error_log("Error obteniendo stats de Promotor: " . $e->getMessage());
            return null;
        }
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
