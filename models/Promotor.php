<?php
/**
 * Modelo Promotor
 * 
 * Propósito: Gestionar datos de promotores, códigos únicos y operaciones
 * relacionadas con el sistema de referidos. Maneja la lógica de creación
 * automática de códigos seguros y búsquedas optimizadas.
 * 
 * Efectos: Operaciones CRUD sobre tabla promotores con generación segura
 * de códigos únicos y validación de integridad de datos.
 * 
 * @author Camella Development Team - Módulo Promotores
 * @version 1.0
 * @date 2025-10-08
 */

class Promotor {
    private $db;
    
    /**
     * Constructor del modelo Promotor
     * 
     * @param PDO|null $database Conexión PDO opcional
     */
    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            // Usar configuración existente del proyecto
            $this->initDatabase();
        }
    }
    
    /**
     * Inicializar conexión a base de datos
     * 
     * Propósito: Establecer conexión PDO usando la configuración
     * existente del proyecto para mantener consistencia.
     */
    private function initDatabase() {
        try {
            require_once dirname(__DIR__) . '/config/config.php';
            global $host, $usuario, $contrasena, $basedatos, $charset;
            
            $dsn = "mysql:host=$host;dbname=$basedatos;charset=$charset";
            $this->db = new PDO($dsn, $usuario, $contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (Exception $e) {
            error_log("[PROMOTOR MODEL] Error conexión BD: " . $e->getMessage());
            throw new Exception("Error de conexión a base de datos");
        }
    }
    
    /**
     * Buscar o crear promotor por ID de usuario
     * 
     * Propósito: Obtener datos de promotor existente o crear uno nuevo
     * con código único generado de forma segura. Operación idempotente
     * que garantiza que cada usuario tenga exactamente un registro promotor.
     * 
     * Parámetros:
     * @param int $usuarioId ID del usuario en tabla usuarios
     * 
     * Retorno:
     * @return array Datos del promotor con claves:
     *   - id: ID del promotor
     *   - usuario_id: ID del usuario referenciado
     *   - codigo: Código único alfanumérico (16 caracteres hex)
     *   - activo: Estado activo/inactivo (1/0)
     *   - creado_en: Timestamp de creación
     * 
     * Efectos:
     * - Consulta SELECT para buscar promotor existente
     * - INSERT si no existe, con código único generado
     * - Retry automático en caso de colisión de código (muy improbable)
     * - Logging de operaciones para auditoría
     * 
     * @throws Exception Si no se puede generar código único tras múltiples intentos
     */
    public function findOrCreateByUsuarioId(int $usuarioId): array {
        try {
            // Bloque 1: Verificar si ya existe promotor para este usuario
            // Query preparada para prevenir SQL injection
            $stmt = $this->db->prepare("
                SELECT id, usuario_id, codigo, activo, creado_en, actualizado_en
                FROM promotores 
                WHERE usuario_id = ? AND activo = 1
                LIMIT 1
            ");
            
            $stmt->execute([$usuarioId]);
            $promotor = $stmt->fetch();
            
            // Si ya existe, devolverlo directamente
            if ($promotor) {
                error_log("[PROMOTOR] Promotor existente encontrado para usuario $usuarioId: {$promotor['codigo']}");
                return $promotor;
            }
            
            // Bloque 2: Crear nuevo promotor con código único
            // Generar código seguro usando random_bytes para máxima entropía
            $codigo = $this->generarCodigoUnico();
            
            // Línea clave: INSERT con manejo de duplicados por UNIQUE constraint
            $stmt = $this->db->prepare("
                INSERT INTO promotores (usuario_id, codigo, activo, creado_en) 
                VALUES (?, ?, 1, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([$usuarioId, $codigo]);
            $promotorId = $this->db->lastInsertId();
            
            // Obtener el registro completo recién creado
            $stmt = $this->db->prepare("
                SELECT id, usuario_id, codigo, activo, creado_en, actualizado_en
                FROM promotores 
                WHERE id = ?
            ");
            
            $stmt->execute([$promotorId]);
            $nuevoPromotor = $stmt->fetch();
            
            error_log("[PROMOTOR] Nuevo promotor creado - ID: $promotorId, Usuario: $usuarioId, Código: $codigo");
            
            return $nuevoPromotor;
            
        } catch (PDOException $e) {
            // Manejo específico para duplicados de código (muy raro con 16 hex chars)
            if ($e->errorInfo[1] === 1062) { // Duplicate entry
                // Retry con nuevo código (máximo 3 intentos)
                static $retryCount = 0;
                if ($retryCount < 3) {
                    $retryCount++;
                    error_log("[PROMOTOR] Colisión de código detectada, reintentando ($retryCount/3)");
                    return $this->findOrCreateByUsuarioId($usuarioId);
                } else {
                    throw new Exception("No se pudo generar código único después de múltiples intentos");
                }
            }
            
            error_log("[PROMOTOR ERROR] " . $e->getMessage());
            throw new Exception("Error creando/buscando promotor: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar promotor por código único
     * 
     * Propósito: Obtener datos completos de un promotor usando su código
     * de referido. Usado principalmente para atribuir visitas y registros.
     * 
     * Parámetros:
     * @param string $codigo Código alfanumérico único (validado por regex)
     * 
     * Retorno:
     * @return array|null Datos del promotor o null si no existe/inactivo
     * 
     * Efectos:
     * - SELECT con validación de código activo
     * - Logging de búsquedas para análisis de tráfico
     */
    public function getByCodigo(string $codigo): ?array {
        try {
            // Validar formato del código para prevenir inyección
            if (!preg_match('/^[a-f0-9]{16,32}$/', $codigo)) {
                error_log("[PROMOTOR] Código inválido rechazado: $codigo");
                return null;
            }
            
            // Query preparada con filtro de estado activo
            $stmt = $this->db->prepare("
                SELECT p.id, p.usuario_id, p.codigo, p.activo, p.creado_en,
                       u.nombre, u.email, u.rol
                FROM promotores p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.codigo = ? AND p.activo = 1
                LIMIT 1
            ");
            
            $stmt->execute([$codigo]);
            $promotor = $stmt->fetch();
            
            if ($promotor) {
                error_log("[PROMOTOR] Búsqueda exitosa por código: $codigo");
            } else {
                error_log("[PROMOTOR] Código no encontrado o inactivo: $codigo");
            }
            
            return $promotor ?: null;
            
        } catch (Exception $e) {
            error_log("[PROMOTOR ERROR] Error búsqueda por código: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener estadísticas de un promotor
     * 
     * Propósito: Calcular métricas de rendimiento para mostrar en panel
     * del promotor: visitas, registros, comisiones pendientes/pagadas.
     * 
     * @param int $promotorId ID del promotor
     * @return array Estadísticas calculadas
     */
    public function getEstadisticas(int $promotorId): array {
        try {
            $stats = [
                'visitas_totales' => 0,
                'registros_completados' => 0,
                'comisiones_pendientes' => 0,
                'comisiones_pagadas' => 0,
                'monto_pendiente' => 0.00,
                'monto_pagado' => 0.00
            ];
            
            // Contar visitas totales (incluye todas las entradas)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM referidos 
                WHERE promotor_id = ?
            ");
            $stmt->execute([$promotorId]);
            $stats['visitas_totales'] = (int)$stmt->fetchColumn();
            
            // Contar registros completados
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM referidos 
                WHERE promotor_id = ? AND estado = 'registro'
            ");
            $stmt->execute([$promotorId]);
            $stats['registros_completados'] = (int)$stmt->fetchColumn();
            
            // Estadísticas de comisiones
            $stmt = $this->db->prepare("
                SELECT 
                    estado,
                    COUNT(*) as cantidad,
                    SUM(monto) as total_monto
                FROM comisiones 
                WHERE promotor_id = ?
                GROUP BY estado
            ");
            $stmt->execute([$promotorId]);
            
            while ($row = $stmt->fetch()) {
                switch ($row['estado']) {
                    case 'pendiente':
                    case 'aprobada':
                        $stats['comisiones_pendientes'] += (int)$row['cantidad'];
                        $stats['monto_pendiente'] += (float)$row['total_monto'];
                        break;
                    case 'pagada':
                        $stats['comisiones_pagadas'] += (int)$row['cantidad'];
                        $stats['monto_pagado'] += (float)$row['total_monto'];
                        break;
                }
            }
            
            error_log("[PROMOTOR] Estadísticas calculadas para promotor $promotorId: " . json_encode($stats));
            return $stats;
            
        } catch (Exception $e) {
            error_log("[PROMOTOR ERROR] Error calculando estadísticas: " . $e->getMessage());
            return $stats; // Devolver stats vacías en caso de error
        }
    }
    
    /**
     * Listar todos los promotores (para panel admin)
     * 
     * @return array Lista de promotores con estadísticas básicas
     */
    public function listarTodos(): array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.id, p.usuario_id, p.codigo, p.activo, p.creado_en,
                       u.nombre, u.email,
                       (SELECT COUNT(*) FROM referidos r WHERE r.promotor_id = p.id) as total_visitas,
                       (SELECT COUNT(*) FROM referidos r WHERE r.promotor_id = p.id AND r.estado = 'registro') as total_registros,
                       (SELECT COUNT(*) FROM comisiones c WHERE c.promotor_id = p.id AND c.estado IN ('pendiente','aprobada')) as comisiones_pendientes,
                       (SELECT SUM(c.monto) FROM comisiones c WHERE c.promotor_id = p.id AND c.estado = 'pagada') as total_pagado
                FROM promotores p
                LEFT JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY p.creado_en DESC
            ");
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("[PROMOTOR ERROR] Error listando promotores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar código único alfanumérico
     * 
     * Propósito: Crear código seguro de 16 caracteres hexadecimales
     * usando random_bytes para máxima entropía. Evita caracteres
     * ambiguos y garantiza URL-safe.
     * 
     * @return string Código único de 16 caracteres hex
     */
    private function generarCodigoUnico(): string {
        // Línea clave: Usar random_bytes para entropía criptográfica
        // 8 bytes = 16 caracteres hex = 2^64 combinaciones posibles
        return bin2hex(random_bytes(8));
    }
    
    /**
     * Activar/desactivar promotor
     * 
     * @param int $promotorId ID del promotor
     * @param bool $activo Estado deseado
     * @return bool Éxito de la operación
     */
    public function cambiarEstado(int $promotorId, bool $activo): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE promotores 
                SET activo = ?, actualizado_en = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $resultado = $stmt->execute([$activo ? 1 : 0, $promotorId]);
            
            if ($resultado && $stmt->rowCount() > 0) {
                error_log("[PROMOTOR] Estado cambiado - ID: $promotorId, Activo: " . ($activo ? 'Sí' : 'No'));
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[PROMOTOR ERROR] Error cambiando estado: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * GENERACIÓN DE CÓDIGOS ÚNICOS:
 * - Se usa bin2hex(random_bytes(8)) para máxima seguridad
 * - 8 bytes = 16 chars hex = 18,446,744,073,709,551,616 combinaciones
 * - Probabilidad de colisión prácticamente nula con millones de códigos
 * - Si necesitas códigos más legibles, considera: base32 o códigos pronunciables
 * 
 * BÚSQUEDAS OPTIMIZADAS:
 * - Índice en columna 'codigo' garantiza búsquedas O(log n)
 * - Validación regex previene ataques de inyección
 * - LIMIT 1 optimiza performance en búsquedas únicas
 * 
 * AMPLIACIÓN DEL MODELO:
 * 1. Para códigos personalizados: agregar método setCodigoCustom()
 * 2. Para métricas avanzadas: expandir getEstadisticas() con filtros de fecha
 * 3. Para geolocalización: agregar campo 'pais' y métodos relacionados
 * 4. Para segmentación: agregar categorías de promotor
 * 
 * CONSIDERACIONES DE PERFORMANCE:
 * - Usar paginación en listarTodos() si crece el número de promotores
 * - Cachear estadísticas pesadas con TTL apropiado
 * - Considerar índices compuestos para consultas complejas
 * 
 * SEGURIDAD:
 * - Nunca exponer IDs internos en URLs públicas, solo códigos
 * - Validar siempre permisos antes de operaciones de escritura  
 * - Loggear todas las operaciones críticas para auditoría
 */

    /**
     * Listar todos los promotores (para administración)
     */
    public function listarTodos($limite = 100, $offset = 0) {
        try {
            $query = "SELECT p.*, u.email as usuario_email, u.nombre as usuario_nombre
                     FROM promotores p
                     LEFT JOIN usuarios u ON p.usuario_id = u.id
                     ORDER BY p.fecha_registro DESC
                     LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error listando promotores: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener promotor por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT p.*, u.email as usuario_email, u.nombre as usuario_nombre
                     FROM promotores p
                     LEFT JOIN usuarios u ON p.usuario_id = u.id
                     WHERE p.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error obteniendo promotor: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cambiar estado de promotor (admin)
     */
    public function cambiarEstado($promotorId, $nuevoEstado) {
        try {
            $estadosValidos = ['activo', 'inactivo', 'suspendido'];
            
            if (!in_array($nuevoEstado, $estadosValidos)) {
                throw new Exception("Estado no válido: $nuevoEstado");
            }
            
            $query = "UPDATE promotores 
                     SET estado = :estado,
                         fecha_actualizacion = NOW()
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $nuevoEstado);
            $stmt->bindParam(':id', $promotorId, PDO::PARAM_INT);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            error_log("Error cambiando estado promotor: " . $e->getMessage());
            return false;
        }
    }
}
?>