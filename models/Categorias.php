<?php
/**
 * Modelo de Categorias
 * Gestiona las categorías principales y sus subcategorías (oficios)
 * Incluye migración automática y datos iniciales
 */

require_once 'config/config.php';

class Categorias {
    private $conexion;
    
    public function __construct() {
        $this->conexion = conectarBD();
        $this->inicializarTablasYDatos();
    }
    
    /**
     * Método de logging para debugging
     */
    private function log($mensaje) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $mensaje\n";
        error_log($logEntry);
        file_put_contents(__DIR__ . '/../debug_categorias.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Inicializar tablas y datos si no existen
     */
    public function inicializarTablasYDatos() {
        $this->crearTablasNecesarias();
        $this->insertarDatosIniciales();
    }
    
    /**
     * Crear las tablas categorias y oficios si no existen
     */
    private function crearTablasNecesarias() {
        try {
            // Crear tabla categorias
            $sqlCategorias = "
                CREATE TABLE IF NOT EXISTS categorias (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    icono VARCHAR(10) NOT NULL,
                    descripcion TEXT,
                    orden INT DEFAULT 0,
                    activa BOOLEAN DEFAULT TRUE,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_nombre (nombre)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $this->conexion->query($sqlCategorias);
            
            // Crear tabla oficios (subcategorías)
            $sqlOficios = "
                CREATE TABLE IF NOT EXISTS oficios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    categoria_id INT NOT NULL,
                    nombre VARCHAR(100) NOT NULL,
                    descripcion TEXT,
                    orden INT DEFAULT 0,
                    activo BOOLEAN DEFAULT TRUE,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_categoria_nombre (categoria_id, nombre),
                    INDEX idx_categoria_activo (categoria_id, activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $this->conexion->query($sqlOficios);
            
        } catch (Exception $e) {
            error_log("Error creando tablas: " . $e->getMessage());
        }
    }
    
    /**
     * Insertar datos iniciales si las tablas están vacías
     */
    private function insertarDatosIniciales() {
        try {
            // Verificar si ya hay categorías
            $result = $this->conexion->query("SELECT COUNT(*) as total FROM categorias");
            $count = $result->fetch_assoc()['total'];
            
            if ($count == 0) {
                $this->insertarCategoriasIniciales();
            }
        } catch (Exception $e) {
            error_log("Error insertando datos iniciales: " . $e->getMessage());
        }
    }
    
    /**
     * Insertar las categorías y oficios iniciales
     */
    private function insertarCategoriasIniciales() {
        $categoriasData = [
            [
                'nombre' => 'Servicios para el hogar',
                'icono' => '🏠',
                'orden' => 1,
                'oficios' => [
                    'Plomería',
                    'Electricidad',
                    'Pintura',
                    'Carpintería',
                    'Instalación de puertas/ventanas',
                    'Reparación de electrodomésticos',
                    'Desinfección / Fumigación',
                    'Arreglo de techos y goteras'
                ]
            ],
            [
                'nombre' => 'Aseo, limpieza y cuidado',
                'icono' => '🧹',
                'orden' => 2,
                'oficios' => [
                    'Empleadas domésticas',
                    'Niñeras',
                    'Cuidadores de adulto mayor',
                    'Personal de aseo para oficinas o conjuntos',
                    'Lavado de muebles / alfombras',
                    'Lavado de carros a domicilio'
                ]
            ],
            [
                'nombre' => 'Belleza y cuidado personal',
                'icono' => '✂️',
                'orden' => 3,
                'oficios' => [
                    'Peluquería y barbería',
                    'Manicuristas y pedicuristas',
                    'Maquillaje',
                    'Depilación',
                    'Masajes relajantes o terapéuticos',
                    'Cejas y pestañas'
                ]
            ],
            [
                'nombre' => 'Servicios logísticos y transporte',
                'icono' => '🧳',
                'orden' => 4,
                'oficios' => [
                    'Trasteos y mudanzas',
                    'Mototaxi o transporte alternativo',
                    'Servicios de mensajería',
                    'Ayudantes de bodega / cargue y descargue',
                    'Conductor elegido',
                    'Repartidores con moto o bici'
                ]
            ],
            [
                'nombre' => 'Reparaciones, técnica y mantenimiento',
                'icono' => '🧰',
                'orden' => 5,
                'oficios' => [
                    'Técnicos celulares, televisores, PC',
                    'Reparación de electrodomésticos',
                    'Mantenimiento de motos o bicicletas',
                    'Cerrajería',
                    'Instalación de cámaras de seguridad',
                    'Soporte técnico básico'
                ]
            ],
            [
                'nombre' => 'Educación y servicios personales',
                'icono' => '📚',
                'orden' => 6,
                'oficios' => [
                    'Clases particulares (básica/secundaria)',
                    'Tutorías universitarias',
                    'Refuerzos escolares',
                    'Psicólogos / coaches',
                    'Traductores / intérpretes',
                    'Diseñadores / publicistas'
                ]
            ],
            [
                'nombre' => 'Cocina y alimentación',
                'icono' => '🍲',
                'orden' => 7,
                'oficios' => [
                    'Cocineras por días',
                    'Almuerzos por encargo',
                    'Repostería y pastelería',
                    'Preparación de alimentos para eventos',
                    'Venta de productos caseros'
                ]
            ],
            [
                'nombre' => 'Oficios varios y "hago de todo"',
                'icono' => '📦',
                'orden' => 8,
                'oficios' => [
                    'Toderos',
                    'Ayudas ocasionales',
                    'Servicios rurales o de campo',
                    'Acompañantes para trámites o citas',
                    'Vigilancia informal / cuidadores de finca'
                ]
            ],
            [
                'nombre' => 'Trabajo remoto / digital',
                'icono' => '🌐',
                'orden' => 9,
                'oficios' => [
                    'Asistentes virtuales',
                    'Freelancers (diseño, video, copywriting)',
                    'Soporte al cliente digital',
                    'Community managers',
                    'Marketing por redes sociales'
                ]
            ],
            [
                'nombre' => 'Eventos y creativos',
                'icono' => '📸',
                'orden' => 10,
                'oficios' => [
                    'Fotografía de eventos',
                    'Edición digital y retoque',
                    'Organizadores de eventos',
                    'Decoradores y ambientadores de fiestas'
                ]
            ]
        ];
        
        foreach ($categoriasData as $categoria) {
            // Insertar categoría
            $stmt = $this->conexion->prepare("
                INSERT INTO categorias (nombre, icono, orden) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ssi", $categoria['nombre'], $categoria['icono'], $categoria['orden']);
            $stmt->execute();
            
            $categoria_id = $this->conexion->insert_id;
            
            // Insertar oficios de esta categoría
            $orden_oficio = 1;
            foreach ($categoria['oficios'] as $oficio) {
                $stmtOficio = $this->conexion->prepare("
                    INSERT INTO oficios (categoria_id, nombre, orden) 
                    VALUES (?, ?, ?)
                ");
                $stmtOficio->bind_param("isi", $categoria_id, $oficio, $orden_oficio);
                $stmtOficio->execute();
                $orden_oficio++;
            }
        }
    }
    
    /**
     * Obtener todas las categorías activas con sus oficios
     */
    public function obtenerCategoriasConOficios() {
        try {
            $sql = "
                SELECT 
                    c.id as categoria_id,
                    c.nombre as categoria_nombre,
                    c.icono as categoria_icono,
                    c.orden as categoria_orden,
                    o.id as oficio_id,
                    o.nombre as oficio_nombre,
                    o.orden as oficio_orden
                FROM categorias c
                LEFT JOIN oficios o ON c.id = o.categoria_id AND o.activo = TRUE
                WHERE c.activa = TRUE
                ORDER BY c.orden ASC, o.orden ASC
            ";
            
            $result = $this->conexion->query($sql);
            
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $cat_id = $row['categoria_id'];
                
                if (!isset($categorias[$cat_id])) {
                    $categorias[$cat_id] = [
                        'id' => $row['categoria_id'],
                        'nombre' => $row['categoria_nombre'],
                        'icono' => $row['categoria_icono'],
                        'orden' => $row['categoria_orden'],
                        'oficios' => []
                    ];
                }
                
                if ($row['oficio_id']) {
                    $categorias[$cat_id]['oficios'][] = [
                        'id' => $row['oficio_id'],
                        'nombre' => $row['oficio_nombre'],
                        'orden' => $row['oficio_orden']
                    ];
                }
            }
            
            return array_values($categorias);
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener categorías para dropdown/select
     */
    public function obtenerCategoriasSimple() {
        try {
            $sql = "SELECT id, nombre, icono FROM categorias WHERE activa = TRUE ORDER BY orden ASC";
            $result = $this->conexion->query($sql);
            
            $categorias = [];
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row;
            }
            
            return $categorias;
            
        } catch (Exception $e) {
            error_log("Error obteniendo categorías simples: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener oficios de una categoría específica
     */
    public function obtenerOficiosPorCategoria($categoria_id) {
        try {
            $stmt = $this->conexion->prepare("
                SELECT id, nombre, orden 
                FROM oficios 
                WHERE categoria_id = ? AND activo = TRUE 
                ORDER BY orden ASC
            ");
            $stmt->bind_param("i", $categoria_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $oficios = [];
            while ($row = $result->fetch_assoc()) {
                $oficios[] = $row;
            }
            
            return $oficios;
            
        } catch (Exception $e) {
            error_log("Error obteniendo oficios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Agregar nueva categoría
     */
    public function agregarCategoria($nombre, $icono, $orden = 0) {
        try {
            $stmt = $this->conexion->prepare("
                INSERT INTO categorias (nombre, icono, orden) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ssi", $nombre, $icono, $orden);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error agregando categoría: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Agregar nuevo oficio
     */
    public function agregarOficio($categoria_id, $nombre, $orden = 0) {
        try {
            $stmt = $this->conexion->prepare("
                INSERT INTO oficios (categoria_id, nombre, orden) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("isi", $categoria_id, $nombre, $orden);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error agregando oficio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una categoría existente con verificación completa y logging
     */
    public function actualizarCategoria($id, $nombre, $icono = null) {
        $this->log("🔄 INICIO actualizarCategoria - ID: $id, Nombre: '$nombre', Ícono: '$icono'");
        
        try {
            // 1. Verificar que la categoría existe antes de actualizar
            $this->log("🔍 Verificando existencia de categoría ID: $id");
            $checkSql = "SELECT id, nombre, icono FROM categorias WHERE id = ?";
            $checkStmt = $this->conexion->prepare($checkSql);
            
            if (!$checkStmt) {
                $this->log("❌ Error en prepare() para verificación: " . $this->conexion->error);
                return [
                    'status' => 'error',
                    'message' => 'Error de base de datos en verificación: ' . $this->conexion->error
                ];
            }
            
            $checkStmt->bind_param("i", $id);
            $checkResult = $checkStmt->execute();
            
            if (!$checkResult) {
                $this->log("❌ Error en execute() para verificación: " . $checkStmt->error);
                $checkStmt->close();
                return [
                    'status' => 'error',
                    'message' => 'Error ejecutando verificación: ' . $checkStmt->error
                ];
            }
            
            $result = $checkStmt->get_result();
            $categoriaActual = $result->fetch_assoc();
            $checkStmt->close();
            
            if (!$categoriaActual) {
                $this->log("❌ Categoría ID $id no encontrada");
                return [
                    'status' => 'error',
                    'message' => "Categoría con ID $id no encontrada"
                ];
            }
            
            $this->log("✅ Categoría encontrada: " . json_encode($categoriaActual));
            
            // 2. Verificar si realmente hay cambios
            $nombreActual = $categoriaActual['nombre'];
            $iconoActual = $categoriaActual['icono'];
            $haycambios = false;
            
            if ($nombreActual !== $nombre) {
                $this->log("📝 Cambio detectado en nombre: '$nombreActual' -> '$nombre'");
                $haycambios = true;
            }
            
            if ($icono !== null && $icono !== '' && $iconoActual !== $icono) {
                $this->log("🎨 Cambio detectado en ícono: '$iconoActual' -> '$icono'");
                $haycambios = true;
            }
            
            if (!$haycambios) {
                $this->log("ℹ️ No se detectaron cambios en los datos");
                return [
                    'status' => 'warning',
                    'message' => 'No se realizaron cambios (los datos eran idénticos)'
                ];
            }
            
            // 3. Verificar que la columna icono existe
            $this->log("🔍 Verificando existencia de columna 'icono'");
            $columnCheck = $this->conexion->query("SHOW COLUMNS FROM categorias LIKE 'icono'");
            
            if ($columnCheck->num_rows === 0) {
                $this->log("❌ Error: la columna 'icono' no existe en la tabla 'categorias'");
                return [
                    'status' => 'error',
                    'message' => "Error de estructura: columna 'icono' no existe"
                ];
            }
            
            $this->log("✅ Columna 'icono' verificada correctamente");
            
            // 4. Construir la consulta de actualización
            $sql = "UPDATE categorias SET nombre = ?";
            $params = [$nombre];
            $types = "s";
            
            if ($icono !== null && $icono !== '') {
                $sql .= ", icono = ?";
                $params[] = $icono;
                $types .= "s";
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            $types .= "i";
            
            $this->log("📋 SQL Query: $sql");
            $this->log("📋 Parámetros: " . json_encode($params));
            $this->log("📋 Types: $types");
            
            // 5. Preparar la sentencia
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                $this->log("❌ Error en prepare(): " . $this->conexion->error);
                return [
                    'status' => 'error',
                    'message' => 'Error preparando consulta: ' . $this->conexion->error
                ];
            }
            
            $this->log("✅ Statement preparado correctamente");
            
            // 6. Bind parameters
            $stmt->bind_param($types, ...$params);
            
            // 7. Ejecutar la consulta
            $resultado = $stmt->execute();
            
            if (!$resultado) {
                $this->log("❌ Error en execute(): " . $stmt->error);
                $stmt->close();
                return [
                    'status' => 'error',
                    'message' => 'Error ejecutando actualización: ' . $stmt->error
                ];
            }
            
            // 8. Verificar filas afectadas
            $affectedRows = $stmt->affected_rows;
            $this->log("ℹ️ affected_rows: " . $affectedRows);
            
            $stmt->close();
            
            if ($affectedRows === 0) {
                $this->log("⚠️ No se actualizaron filas (affected_rows = 0)");
                return [
                    'status' => 'warning',
                    'message' => 'No se realizaron cambios en la base de datos'
                ];
            }
            
            $this->log("✅ Categoría actualizada correctamente para ID $id");
            
            return [
                'status' => 'success',
                'message' => 'Categoría actualizada exitosamente',
                'affected_rows' => $affectedRows,
                'categoria' => [
                    'id' => $id,
                    'nombre' => $nombre,
                    'icono' => $icono ?: $iconoActual
                ]
            ];
            
        } catch (Exception $e) {
            $this->log("❌ Exception capturada: " . $e->getMessage());
            $this->log("❌ Stack trace: " . $e->getTraceAsString());
            return [
                'status' => 'error',
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si las tablas existen y tienen datos
     */
    public function verificarEstadoTablasYDatos() {
        try {
            $estado = [
                'tablas_existen' => false,
                'datos_inicializados' => false,
                'total_categorias' => 0,
                'total_oficios' => 0
            ];
            
            // Verificar si las tablas existen
            $result = $this->conexion->query("SHOW TABLES LIKE 'categorias'");
            if ($result->num_rows > 0) {
                $result2 = $this->conexion->query("SHOW TABLES LIKE 'oficios'");
                if ($result2->num_rows > 0) {
                    $estado['tablas_existen'] = true;
                    
                    // Contar registros
                    $countCat = $this->conexion->query("SELECT COUNT(*) as total FROM categorias");
                    $estado['total_categorias'] = $countCat->fetch_assoc()['total'];
                    
                    $countOfi = $this->conexion->query("SELECT COUNT(*) as total FROM oficios");
                    $estado['total_oficios'] = $countOfi->fetch_assoc()['total'];
                    
                    $estado['datos_inicializados'] = ($estado['total_categorias'] > 0);
                }
            }
            
            return $estado;
        } catch (Exception $e) {
            error_log("Error verificando estado: " . $e->getMessage());
            return [
                'tablas_existen' => false,
                'datos_inicializados' => false,
                'total_categorias' => 0,
                'total_oficios' => 0
            ];
        }
    }
    
    public function __destruct() {
        if ($this->conexion) {
            cerrarBD($this->conexion);
        }
    }
}
?>