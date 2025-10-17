<?php
require_once __DIR__ . '/BaseModel.php';

class Categorias extends BaseModel
{
    protected $pdo;
    
    public function __construct() {
        parent::__construct();
        try {
            // Load database configuration
            require_once __DIR__ . '/../config/database.php';
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en Categorias: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    /**
     * Devuelve categorías activas con conteo de oficios activos por categoría.
     * Campos: id, nombre, descripcion, icono, activo, total_oficios
     */
    public function obtenerCategoriasConOficios(): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "
            SELECT 
                c.id,
                c.nombre,
                c.descripcion,
                c.icono,
                c.activo,
                COALESCE(COUNT(o.id), 0) AS total_oficios
            FROM categorias c
            LEFT JOIN oficios o 
                ON o.categoria_id = c.id 
               AND o.activo = 1
            WHERE c.activo = 1
            GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
            ORDER BY c.id ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Devuelve los oficios activos para una categoría dada.
     * Campos: id, titulo
     */
    public function obtenerOficiosPorCategoria(int $categoriaId): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "
            SELECT id, titulo
            FROM oficios
            WHERE categoria_id = :categoria_id
              AND activo = 1
            ORDER BY titulo ASC
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':categoria_id' => $categoriaId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo oficios: " . $e->getMessage());
            return [];
        }
    }
}
?>