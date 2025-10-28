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
     * Campos: id, titulo, popular
     */
    public function obtenerOficiosPorCategoria(int $categoriaId): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "
            SELECT id, titulo, popular
            FROM oficios
            WHERE categoria_id = :categoria_id
              AND activo = 1
            ORDER BY popular DESC, titulo ASC
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

    /**
     * Crear nueva categoría
     */
    public function crear(array $datos): int|false
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "INSERT INTO categorias (nombre, descripcion, icono, activo) VALUES (?, ?, ?, ?)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([
                $datos['titulo'] ?? $datos['nombre'] ?? '',
                $datos['descripcion'] ?? '',
                $datos['icono'] ?? '',
                $datos['activo'] ?? 1
            ]);
            
            return $resultado ? $this->pdo->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error creando categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar categoría existente
     */
    public function actualizar(int $id, array $datos): bool
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "UPDATE categorias SET nombre = ?, descripcion = ?, icono = ?, activo = ? WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $datos['titulo'] ?? $datos['nombre'] ?? '',
                $datos['descripcion'] ?? '',
                $datos['icono'] ?? '',
                $datos['activo'] ?? 1,
                $id
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar categoría
     */
    public function eliminar(int $id): bool
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "DELETE FROM categorias WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error eliminando categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener categoría por ID
     */
    public function obtenerPorId(int $id): array|false
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "SELECT id, nombre as titulo, descripcion, icono, activo FROM categorias WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: false;
        } catch (Exception $e) {
            error_log("Error obteniendo categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las categorías
     */
    public function obtenerTodas(): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT id, nombre as titulo, descripcion, icono, activo FROM categorias ORDER BY nombre ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }
}
?>