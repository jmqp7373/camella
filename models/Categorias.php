<?php
require_once __DIR__ . '/BaseModel.php';

class Categorias extends BaseModel
{
    protected $pdo;
    
    public function __construct() {
        parent::__construct();
        try {
            // Load database configuration con ruta absoluta
            require_once __DIR__ . '/../config/database.php';
            $this->pdo = getPDO();
            
            if (!$this->pdo) {
                error_log("ERROR: No se pudo obtener conexión PDO en Categorias::__construct()");
                throw new Exception("No se pudo conectar a la base de datos");
            }
        } catch (Exception $e) {
            error_log("ERROR CRÍTICO conectando BD en Categorias: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
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
            error_log("ERROR: PDO no disponible en obtenerCategoriasConOficios()");
            return [];
        }

        // Verificar si la columna oficio_id existe en la tabla anuncios
        $columnExists = false;
        try {
            $checkCol = $this->pdo->query("SHOW COLUMNS FROM anuncios LIKE 'oficio_id'");
            $columnExists = $checkCol->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error verificando columna oficio_id: " . $e->getMessage());
        }

        // Usar consulta con o sin subquery según si existe la columna
        if ($columnExists) {
            // Primero verificar si hay anuncios con oficio_id asignado
            $hasAssignedOficios = false;
            try {
                $checkAssigned = $this->pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE oficio_id IS NOT NULL AND status = 'activo'");
                $result = $checkAssigned->fetch();
                $hasAssignedOficios = $result['total'] > 0;
            } catch (Exception $e) {
                error_log("Error verificando anuncios con oficio_id: " . $e->getMessage());
            }
            
            if ($hasAssignedOficios) {
                // Si hay anuncios con oficio_id, usar conteo preciso
                $sql = "
                    SELECT 
                        c.id,
                        c.nombre,
                        c.descripcion,
                        c.icono,
                        c.activo,
                        COALESCE(COUNT(o.id), 0) AS total_oficios,
                        (SELECT COUNT(*) 
                         FROM anuncios a 
                         INNER JOIN oficios o2 ON a.oficio_id = o2.id 
                         WHERE o2.categoria_id = c.id 
                         AND a.status = 'activo') AS total_anuncios
                    FROM categorias c
                    LEFT JOIN oficios o 
                        ON o.categoria_id = c.id 
                       AND o.activo = 1
                    WHERE c.activo = 1
                    GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
                    ORDER BY total_anuncios DESC, c.nombre ASC
                ";
            } else {
                // Si NO hay anuncios con oficio_id, mostrar total general de anuncios
                $totalAnuncios = 0;
                try {
                    $countStmt = $this->pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE status = 'activo'");
                    $countResult = $countStmt->fetch();
                    $totalAnuncios = $countResult['total'];
                } catch (Exception $e) {
                    error_log("Error contando anuncios activos: " . $e->getMessage());
                }
                
                $sql = "
                    SELECT 
                        c.id,
                        c.nombre,
                        c.descripcion,
                        c.icono,
                        c.activo,
                        COALESCE(COUNT(o.id), 0) AS total_oficios,
                        $totalAnuncios AS total_anuncios
                    FROM categorias c
                    LEFT JOIN oficios o 
                        ON o.categoria_id = c.id 
                       AND o.activo = 1
                    WHERE c.activo = 1
                    GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
                    ORDER BY c.nombre ASC
                ";
            }
        } else {
            // Consulta simplificada sin la columna oficio_id
            $sql = "
                SELECT 
                    c.id,
                    c.nombre,
                    c.descripcion,
                    c.icono,
                    c.activo,
                    COALESCE(COUNT(o.id), 0) AS total_oficios,
                    0 AS total_anuncios
                FROM categorias c
                LEFT JOIN oficios o 
                    ON o.categoria_id = c.id 
                   AND o.activo = 1
                WHERE c.activo = 1
                GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
                ORDER BY c.nombre ASC
            ";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            
            if (empty($resultado)) {
                error_log("ADVERTENCIA: obtenerCategoriasConOficios() devolvió 0 resultados");
            } else {
                error_log("INFO: obtenerCategoriasConOficios() devolvió " . count($resultado) . " categorías");
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("ERROR obteniendo categorías: " . $e->getMessage());
            error_log("SQL ejecutado: " . $sql);
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