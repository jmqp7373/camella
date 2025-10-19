<?php
/**
 * Modelo de Oficios
 * Gestiona las operaciones de la tabla oficios
 */

class OficioModel
{
    protected $pdo;
    
    public function __construct() {
        try {
            // Conexión directa sin dependencias
            $this->pdo = new PDO('mysql:host=localhost;dbname=camella_db;charset=utf8mb4', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error conectando BD en OficioModel: " . $e->getMessage());
            $this->pdo = null;
        }
    }

    /**
     * Obtener un oficio por su ID
     * 
     * @param int $id ID del oficio
     * @return array|false Datos del oficio o false si no existe
     */
    public function obtenerPorId(int $id) {
        if (!$this->pdo) {
            return false;
        }

        $sql = "SELECT id, categoria_id, titulo, popular, activo, created_at, updated_at 
                FROM oficios 
                WHERE id = :id 
                LIMIT 1";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo oficio por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar la popularidad de un oficio
     * 
     * @param int $id ID del oficio
     * @param int $nuevoEstado Nuevo valor de popularidad (0 o 1)
     * @return bool True si se actualizó correctamente
     */
    public function actualizarPopularidad(int $id, int $nuevoEstado): bool {
        if (!$this->pdo) {
            return false;
        }

        // Validar que el nuevo estado sea 0 o 1
        $nuevoEstado = ($nuevoEstado == 1) ? 1 : 0;

        $sql = "UPDATE oficios 
                SET popular = :nuevoEstado, 
                    updated_at = NOW() 
                WHERE id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'nuevoEstado' => $nuevoEstado,
                'id' => $id
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando popularidad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los oficios activos de una categoría
     * 
     * @param int $categoriaId ID de la categoría
     * @return array Lista de oficios
     */
    public function obtenerPorCategoria(int $categoriaId): array {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT id, categoria_id, titulo, popular, activo 
                FROM oficios 
                WHERE categoria_id = :categoria_id 
                  AND activo = 1 
                ORDER BY popular DESC, titulo ASC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['categoria_id' => $categoriaId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Error obteniendo oficios por categoría: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los oficios populares (popular = 1)
     * 
     * @return array Lista de oficios populares
     */
    public function obtenerPopulares(): array {
        if (!$this->pdo) {
            return [];
        }

        $sql = "SELECT o.id, o.titulo, o.categoria_id, c.nombre as categoria_nombre 
                FROM oficios o
                INNER JOIN categorias c ON o.categoria_id = c.id
                WHERE o.popular = 1 
                  AND o.activo = 1 
                  AND c.activo = 1
                ORDER BY c.nombre ASC, o.titulo ASC";
        
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
     * Alternar la popularidad de un oficio (toggle)
     * 
     * @param int $id ID del oficio
     * @return array Resultado con success, newState y message
     */
    public function togglePopular(int $id): array {
        // Obtener estado actual
        $oficio = $this->obtenerPorId($id);
        
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
        $exito = $this->actualizarPopularidad($id, $nuevoEstado);

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
    }

    /**
     * Crear un nuevo oficio
     * 
     * @param int $categoriaId ID de la categoría
     * @param string $titulo Título del oficio
     * @param int $popular Si es popular (0 o 1)
     * @return int|false ID del nuevo oficio o false si falla
     */
    public function crear(int $categoriaId, string $titulo, int $popular = 0) {
        if (!$this->pdo) {
            return false;
        }

        $sql = "INSERT INTO oficios (categoria_id, titulo, popular, activo, created_at) 
                VALUES (:categoria_id, :titulo, :popular, 1, NOW())";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'categoria_id' => $categoriaId,
                'titulo' => trim($titulo),
                'popular' => ($popular == 1) ? 1 : 0
            ]);
            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error creando oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un oficio existente
     * 
     * @param int $id ID del oficio
     * @param array $datos Datos a actualizar (titulo, popular, activo, categoria_id)
     * @return bool True si se actualizó correctamente
     */
    public function actualizar(int $id, array $datos): bool {
        if (!$this->pdo) {
            return false;
        }

        $campos = [];
        $valores = ['id' => $id];

        if (isset($datos['titulo'])) {
            $campos[] = "titulo = :titulo";
            $valores['titulo'] = trim($datos['titulo']);
        }

        if (isset($datos['popular'])) {
            $campos[] = "popular = :popular";
            $valores['popular'] = ($datos['popular'] == 1) ? 1 : 0;
        }

        if (isset($datos['activo'])) {
            $campos[] = "activo = :activo";
            $valores['activo'] = ($datos['activo'] == 1) ? 1 : 0;
        }

        if (isset($datos['categoria_id'])) {
            $campos[] = "categoria_id = :categoria_id";
            $valores['categoria_id'] = (int)$datos['categoria_id'];
        }

        if (empty($campos)) {
            return false;
        }

        $campos[] = "updated_at = NOW()";

        $sql = "UPDATE oficios SET " . implode(', ', $campos) . " WHERE id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($valores);
        } catch (Exception $e) {
            error_log("Error actualizando oficio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar (soft delete) un oficio
     * 
     * @param int $id ID del oficio
     * @return bool True si se eliminó correctamente
     */
    public function eliminar(int $id): bool {
        return $this->actualizar($id, ['activo' => 0]);
    }

    /**
     * Contar oficios por categoría
     * 
     * @param int $categoriaId ID de la categoría
     * @return int Cantidad de oficios activos
     */
    public function contarPorCategoria(int $categoriaId): int {
        if (!$this->pdo) {
            return 0;
        }

        $sql = "SELECT COUNT(*) as total 
                FROM oficios 
                WHERE categoria_id = :categoria_id 
                  AND activo = 1";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['categoria_id' => $categoriaId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (Exception $e) {
            error_log("Error contando oficios: " . $e->getMessage());
            return 0;
        }
    }
}
?>
