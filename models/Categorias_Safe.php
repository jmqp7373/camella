<?php
/**
 * Modelo de Categorias - Versión PDO Compatible
 * Versión simplificada sin inicialización automática para evitar errores
 */

require_once 'config/database.php';

class Categorias {
    private $conexion;
    
    public function __construct() {
        try {
            $this->conexion = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en Categorias: " . $e->getMessage());
            $this->conexion = null;
        }
    }
    
    /**
     * Obtener categorías con oficios (versión segura)
     */
    public function obtenerCategoriasConOficios() {
        // Si no hay conexión, retornar categorías por defecto
        if (!$this->conexion) {
            return $this->getCategoriasDefault();
        }
        
        try {
            $stmt = $this->conexion->query("
                SELECT c.id, c.nombre, c.icono, c.orden,
                       COUNT(o.id) as total_oficios
                FROM categorias c
                LEFT JOIN oficios o ON c.id = o.categoria_id AND o.activo = 1
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre, c.icono, c.orden
                ORDER BY c.orden ASC, c.nombre ASC
            ");
            
            $categorias = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categorias[] = $row;
            }
            
            // Si no hay categorías en BD, usar las por defecto
            return !empty($categorias) ? $categorias : $this->getCategoriasDefault();
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return $this->getCategoriasDefault();
        }
    }
    
    /**
     * Obtener categorías simples (para compatibilidad)
     */
    public function obtenerCategoriasSimples() {
        return $this->obtenerCategoriasConOficios();
    }
    
    /**
     * Categorías por defecto (fallback)
     */
    private function getCategoriasDefault() {
        return [
            ['id' => 1, 'nombre' => 'Tecnología', 'icono' => 'fas fa-laptop-code', 'orden' => 1, 'total_oficios' => 5],
            ['id' => 2, 'nombre' => 'Salud', 'icono' => 'fas fa-heartbeat', 'orden' => 2, 'total_oficios' => 3],
            ['id' => 3, 'nombre' => 'Educación', 'icono' => 'fas fa-graduation-cap', 'orden' => 3, 'total_oficios' => 4],
            ['id' => 4, 'nombre' => 'Ventas', 'icono' => 'fas fa-chart-line', 'orden' => 4, 'total_oficios' => 6],
            ['id' => 5, 'nombre' => 'Construcción', 'icono' => 'fas fa-hard-hat', 'orden' => 5, 'total_oficios' => 7],
            ['id' => 6, 'nombre' => 'Hostelería', 'icono' => 'fas fa-utensils', 'orden' => 6, 'total_oficios' => 4],
            ['id' => 7, 'nombre' => 'Marketing', 'icono' => 'fas fa-bullhorn', 'orden' => 7, 'total_oficios' => 3],
            ['id' => 8, 'nombre' => 'Finanzas', 'icono' => 'fas fa-coins', 'orden' => 8, 'total_oficios' => 5]
        ];
    }
}
?>