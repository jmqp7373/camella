<?php
require_once __DIR__ . '/BaseModel.php';

class Oficios extends BaseModel
{
    protected $pdo;
    
    public function __construct() {
        parent::__construct();
        try {
            // Load database configuration
            require_once __DIR__ . '/../config/database.php';
            $this->pdo = getPDO();
        } catch (Exception $e) {
            error_log("Error conectando BD en Oficios: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    /**
     * Crear nuevo oficio
     */
    public function crear(array $datos): int|false
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "INSERT INTO oficios (titulo, descripcion, categoria_id, popular, activo) VALUES (?, ?, ?, ?, ?)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $resultado = $stmt->execute([
                $datos['titulo'] ?? '',
                $datos['descripcion'] ?? '',
                $datos['categoria_id'] ?? 0,
                $datos['popular'] ?? 0,
                $datos['activo'] ?? 1
            ]);
            
            return $resultado ? $this->pdo->lastInsertId() : false;
        } catch (Exception $e) {
            error_log("Error creando oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar oficio existente
     */
    public function actualizar(int $id, array $datos): bool
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "UPDATE oficios SET titulo = ?, descripcion = ?, popular = ?, activo = ? WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $datos['titulo'] ?? '',
                $datos['descripcion'] ?? '',
                $datos['popular'] ?? 0,
                $datos['activo'] ?? 1,
                $id
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar oficio
     */
    public function eliminar(int $id): bool
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "DELETE FROM oficios WHERE id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error eliminando oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener oficio por ID
     */
    public function obtenerPorId(int $id): array|false
    {
        if (!$this->pdo) {
            return false;
        }

        $sql = "SELECT o.*, c.nombre as categoria_nombre 
                FROM oficios o 
                LEFT JOIN categorias c ON o.categoria_id = c.id 
                WHERE o.id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: false;
        } catch (Exception $e) {
            error_log("Error obteniendo oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los oficios
     */
    public function obtenerTodos(): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT o.*, c.nombre as categoria_nombre 
                FROM oficios o 
                LEFT JOIN categorias c ON o.categoria_id = c.id 
                ORDER BY o.titulo ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo oficios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener oficios populares
     */
    public function obtenerPopulares(): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT o.*, c.nombre as categoria_nombre 
                FROM oficios o 
                LEFT JOIN categorias c ON o.categoria_id = c.id 
                WHERE o.popular = 1 AND o.activo = 1
                ORDER BY o.titulo ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo oficios populares: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener oficios por categoría
     */
    public function obtenerPorCategoria(int $categoriaId): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT * FROM oficios 
                WHERE categoria_id = ? AND activo = 1
                ORDER BY popular DESC, titulo ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$categoriaId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo oficios por categoría: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar oficios por término
     */
    public function buscar(string $termino): array
    {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT o.*, c.nombre as categoria_nombre 
                FROM oficios o 
                LEFT JOIN categorias c ON o.categoria_id = c.id 
                WHERE o.titulo LIKE ? OR o.descripcion LIKE ?
                AND o.activo = 1
                ORDER BY o.popular DESC, o.titulo ASC";

        try {
            $termino = "%{$termino}%";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$termino, $termino]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error buscando oficios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Toggle popularidad de un oficio
     * Invierte el valor del campo 'popular' (0 a 1, o 1 a 0)
     * 
     * @param int $id ID del oficio
     * @return array Resultado con success, newState y message
     */
    public function togglePopular(int $id): array
    {
        if (!$this->pdo) {
            return [
                'success' => false,
                'message' => 'Error de conexi�n a la base de datos',
                'newState' => null
            ];
        }

        try {
            // Obtener estado actual
            $sql = "SELECT popular FROM oficios WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            $oficio = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$oficio) {
                return [
                    'success' => false,
                    'message' => 'Oficio no encontrado',
                    'newState' => null
                ];
            }

            // Invertir el valor
            $nuevoEstado = ($oficio['popular'] == 1) ? 0 : 1;

            // Actualizar
            $sqlUpdate = "UPDATE oficios SET popular = ? WHERE id = ?";
            $stmtUpdate = $this->pdo->prepare($sqlUpdate);
            $exito = $stmtUpdate->execute([$nuevoEstado, $id]);

            if ($exito) {
                return [
                    'success' => true,
                    'newState' => $nuevoEstado,
                    'message' => $nuevoEstado == 1 
                        ? 'Oficio marcado como popular' 
                        : 'Oficio desmarcado como popular'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la popularidad',
                    'newState' => null
                ];
            }
        } catch (Exception $e) {
            error_log("Error en togglePopular: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage(),
                'newState' => null
            ];
        }
    }
}
?>
