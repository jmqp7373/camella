<?php
/**
 * Controlador de Administración
 * Gestiona las funciones administrativas y la inicialización de datos
 * 
 * PROTECCIÓN POR ROL: Solo usuarios con rol 'admin' pueden acceder
 * Se verifica el acceso al inicio de cada método público
 */

require_once 'models/Categorias.php';
require_once 'debug_logger.php';

/**
 * NOTA: AuthHelper ya está cargado via bootstrap.php centralizado
 * 
 * Antes: require_once __DIR__ . '/../helpers/AuthHelper.php';
 * Ahora: Se carga automáticamente en index.php via bootstrap.php
 * 
 * Propósito del cambio: Evitar includes duplicados y problemas de rutas
 * que pueden causar errores 500 en producción.
 */

/**
 * Función auxiliar para verificar acceso de administrador
 * 
 * Propósito: Centralizar la verificación de permisos admin en una función
 * reutilizable para todos los métodos del controlador.
 * 
 * @param array $rolesPermitidos Lista de roles que pueden acceder ['admin']
 * @throws void Redirecciona si no tiene permisos, no retorna si falla
 */
function verificarAcceso($rolesPermitidos = ['admin']) {
    $authHelper = new AuthHelper();
    
    // Verificar si está autenticado
    if (!$authHelper->estaAutenticado()) {
        // Redirigir a login con la URL actual para regresar después
        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    // Verificar si tiene el rol adecuado
    $tieneAcceso = false;
    foreach ($rolesPermitidos as $rol) {
        if ($authHelper->verificarAcceso($rol)) {
            $tieneAcceso = true;
            break;
        }
    }
    
    if (!$tieneAcceso) {
        // Log del intento de acceso no autorizado
        $usuario = $authHelper->obtenerUsuarioActual();
        error_log("Acceso denegado - Usuario: {$usuario['email']}, Rol: {$usuario['rol']}, Intentó: " . $_SERVER['REQUEST_URI']);
        
        // Redirigir a página principal con mensaje de error
        header('Location: /?error=' . urlencode('No tiene permisos para acceder a esta sección'));
        exit;
    }
}

class AdminController {
    private $categoriasModel;
    
    public function __construct() {
        $this->categoriasModel = new Categorias();
    }
    
    /**
     * Dashboard de estadísticas del sistema
     * 
     * Propósito: Mostrar métricas y contadores principales del sistema
     * de forma segura, tolerante a tablas ausentes y con manejo robusto
     * de errores que garantice disponibilidad del panel administrativo.
     * 
     * Flujo de ejecución:
     * 1. Verificar acceso de rol admin (redirige si no autorizado)
     * 2. Establecer conexión PDO segura usando configuración existente
     * 3. Cargar modelo Stats y obtener contadores del sistema
     * 4. Preparar datos para vista y renderizar dashboard de estadísticas
     * 
     * Posibles errores:
     * - Acceso denegado: Redirige a login o página principal
     * - Error BD: Muestra mensaje controlado, stats en 0
     * - Stats no disponibles: Vista muestra mensaje de estadísticas no disponibles
     * 
     * @return void Renderiza vista o redirige, nunca retorna valor
     */
    public function dashboard() {
        // Bloque 1: Verificación de acceso y permisos
        // Solo usuarios con rol 'admin' pueden acceder al dashboard de estadísticas
        verificarAcceso(['admin']);
        
        // Bloque 2: Obtención de conexión PDO usando configuración del proyecto
        // Usar el mismo mecanismo de BD que el resto del sistema para consistencia
        $db = null;
        try {
            // Cargar configuración de base de datos del proyecto
            require_once dirname(__DIR__) . '/config/config.php';
            
            // Línea clave: Establecer conexión PDO con variables globales del proyecto
            global $host, $usuario, $contrasena, $basedatos, $charset;
            $dsn = "mysql:host=$host;dbname=$basedatos;charset=$charset";
            $db = new PDO($dsn, $usuario, $contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            error_log("[DASHBOARD] Conexión PDO establecida exitosamente");
            
        } catch (Exception $e) {
            // Error crítico: Registrar pero continuar para no romper el dashboard
            error_log("[DASHBOARD ERROR] Error conectando a BD: " . $e->getMessage());
        }
        
        // Bloque 3: Carga de estadísticas usando modelo Stats
        // Siempre inicializar stats, incluso si hay error de BD
        $stats = null;
        
        if ($db !== null) {
            try {
                // Cargar modelo de estadísticas de forma segura
                require_once dirname(__DIR__) . '/models/Stats.php';
                
                $statsModel = new Stats();
                // Línea clave: Obtener contadores del sistema de forma tolerante a errores
                $stats = $statsModel->getCounts($db);
                
                error_log("[DASHBOARD] Estadísticas cargadas: " . json_encode($stats));
                
            } catch (Exception $e) {
                // Error en stats: Registrar y mantener $stats como null
                error_log("[DASHBOARD ERROR] Error cargando estadísticas: " . $e->getMessage());
                $stats = null;
            }
        }
        
        // Bloque 4: Preparación de datos para vista y renderizado
        // Configurar variables necesarias para el template
        $pageTitle = "Dashboard - Estadísticas del Sistema";
        
        // Incluir vista específica del dashboard de estadísticas
        // Nota: Crear nueva vista que no interfiera con dashboard de categorías existente
        include 'views/admin/stats-dashboard.php';
    }

    /**
     * Vista principal de administración
     * 
     * PROTECCIÓN: Solo usuarios admin pueden acceder a esta función
     */
    public function index() {
        // Verificar permisos de administrador antes de proceder
        verificarAcceso(['admin']);
        
        // Inicializar automáticamente las tablas y datos
        $estado = $this->inicializarSistema();
        
        // Obtener datos para la vista
        $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
        $estadoSistema = $this->categoriasModel->verificarEstadoTablasYDatos();
        
        // Variables para la vista
        $pageTitle = "Panel de Administración";
        $data = [
            'categorias' => $categorias,
            'estado' => $estadoSistema,
            'inicializacion' => $estado
        ];
        
        include 'views/admin/dashboard.php';
    }
    
    /**
     * Gestión de categorías (redirección a dashboard)
     */
    public function categorias() {
        header('Location: index.php?view=admin');
        exit;
    }
    
    /**
     * Inicializar sistema (crear tablas y datos si es necesario)
     */
    private function inicializarSistema() {
        try {
            // El constructor del modelo ya se encarga de la inicialización
            $estado = $this->categoriasModel->verificarEstadoTablasYDatos();
            
            return [
                'exito' => true,
                'mensaje' => 'Sistema inicializado correctamente',
                'detalles' => $estado
            ];
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error inicializando sistema: ' . $e->getMessage(),
                'detalles' => null
            ];
        }
    }
    
    /**
     * API para obtener categorías (AJAX)
     */
    public function apiCategorias() {
        header('Content-Type: application/json');
        
        try {
            $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
            echo json_encode([
                'exito' => true,
                'datos' => $categorias
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error obteniendo categorías: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API para obtener oficios de una categoría (AJAX)
     */
    public function apiOficios() {
        header('Content-Type: application/json');
        
        $categoria_id = $_GET['categoria_id'] ?? 0;
        
        if (!$categoria_id) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'ID de categoría requerido'
            ]);
            return;
        }
        
        try {
            $oficios = $this->categoriasModel->obtenerOficiosPorCategoria($categoria_id);
            echo json_encode([
                'exito' => true,
                'datos' => $oficios
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error obteniendo oficios: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Agregar nueva categoría
     */
    public function agregarCategoria() {
        // Verificar permisos admin antes de agregar categoría
        verificarAcceso(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=admin&action=categorias');
            return;
        }
        
        $nombre = trim($_POST['nombre'] ?? '');
        $icono = trim($_POST['icono'] ?? '');
        $orden = intval($_POST['orden'] ?? 0);
        
        if (empty($nombre) || empty($icono)) {
            $_SESSION['mensaje_error'] = 'Nombre e ícono son obligatorios';
            header('Location: index.php?view=admin&action=categorias');
            return;
        }
        
        try {
            $resultado = $this->categoriasModel->agregarCategoria($nombre, $icono, $orden);
            
            if ($resultado) {
                $_SESSION['mensaje_exito'] = 'Categoría agregada exitosamente';
            } else {
                $_SESSION['mensaje_error'] = 'Error al agregar la categoría';
            }
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
        }
        
        header('Location: index.php?view=admin&action=categorias');
    }
    
    /**
     * Agregar nuevo oficio
     */
    public function agregarOficio() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=admin&action=categorias');
            return;
        }
        
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $orden = intval($_POST['orden'] ?? 0);
        
        if (!$categoria_id || empty($nombre)) {
            $_SESSION['mensaje_error'] = 'Categoría y nombre son obligatorios';
            header('Location: index.php?view=admin&action=categorias');
            return;
        }
        
        try {
            $resultado = $this->categoriasModel->agregarOficio($categoria_id, $nombre, $orden);
            
            if ($resultado) {
                $_SESSION['mensaje_exito'] = 'Oficio agregado exitosamente';
            } else {
                $_SESSION['mensaje_error'] = 'Error al agregar el oficio';
            }
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
        }
        
        header('Location: index.php?view=admin&action=categorias');
    }
    
    /**
     * Editar categoría existente
     */
    public function editarCategoria() {
        // Verificar permisos admin antes de editar categoría
        verificarAcceso(['admin']);
        
        // Log detallado para debugging
        logCategoria('INICIO_EDICION', [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'HTTP_X_REQUESTED_WITH' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'No definido',
            'POST_data' => $_POST,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Error: Método no es POST");
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            } else {
                header('Location: index.php?view=admin');
            }
            return;
        }
        
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $icono = trim($_POST['icono'] ?? '');
        
        error_log("Valores parseados - ID: $id, Nombre: '$nombre', Ícono: '$icono'");
        
        if (!$id || empty($nombre)) {
            $error = 'ID y nombre son obligatorios';
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $error]);
            } else {
                $_SESSION['mensaje_error'] = $error;
                header('Location: index.php?view=admin');
            }
            return;
        }
        
        try {
            // Log para debugging con logger
            logCategoria('CONTROLLER_INICIO', [
                'id' => $id,
                'nombre' => $nombre,
                'icono' => $icono,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $resultado = $this->categoriasModel->actualizarCategoria($id, $nombre, $icono ?: null);
            
            logCategoria('CONTROLLER_RESULTADO', $resultado);
            
            // Manejar diferentes tipos de respuesta del modelo
            if (is_array($resultado)) {
                $status = $resultado['status'];
                $mensaje = $resultado['message'];
                
                if ($status === 'success') {
                    // Éxito
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'exito' => true,
                            'mensaje' => $mensaje,
                            'categoria' => $resultado['categoria'] ?? [
                                'id' => $id,
                                'nombre' => $nombre,
                                'icono' => $icono
                            ]
                        ]);
                    } else {
                        $_SESSION['mensaje_exito'] = $mensaje;
                        header('Location: index.php?view=admin');
                    }
                } else if ($status === 'warning') {
                    // Advertencia (sin cambios)
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'exito' => false,
                            'mensaje' => $mensaje,
                            'tipo' => 'warning'
                        ]);
                    } else {
                        $_SESSION['mensaje_error'] = $mensaje;
                        header('Location: index.php?view=admin');
                    }
                } else {
                    // Error
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'exito' => false,
                            'mensaje' => $mensaje,
                            'tipo' => 'error'
                        ]);
                    } else {
                        $_SESSION['mensaje_error'] = $mensaje;
                        header('Location: index.php?view=admin');
                    }
                }
            } else {
                // Respuesta legacy (booleana) - manejo de compatibilidad
                if ($resultado) {
                    $mensaje = 'Categoría actualizada exitosamente';
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'exito' => true,
                            'mensaje' => $mensaje,
                            'categoria' => [
                                'id' => $id,
                                'nombre' => $nombre,
                                'icono' => $icono
                            ]
                        ]);
                    } else {
                        $_SESSION['mensaje_exito'] = $mensaje;
                        header('Location: index.php?view=admin');
                    }
                } else {
                    $error = 'Error al actualizar la categoría';
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['exito' => false, 'mensaje' => $error]);
                    } else {
                        $_SESSION['mensaje_error'] = $error;
                        header('Location: index.php?view=admin');
                    }
                }
            }
        } catch (Exception $e) {
            logCategoria('CONTROLLER_EXCEPTION', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $error = 'Error: ' . $e->getMessage();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'mensaje' => $error]);
            } else {
                $_SESSION['mensaje_error'] = $error;
                header('Location: index.php?view=admin');
            }
        }
    }
    
    /**
     * Verificar estado del sistema
     */
    public function verificarSistema() {
        header('Content-Type: application/json');
        
        try {
            $estado = $this->categoriasModel->verificarEstadoTablasYDatos();
            echo json_encode([
                'exito' => true,
                'datos' => $estado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error verificando sistema: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * ========================================
     * GESTIÓN DE PROMOTORES - ADMINISTRACIÓN
     * ========================================
     */
    
    /**
     * Vista principal de gestión de promotores
     */
    public function promotores() {
        verificarAcceso(['admin']);
        
        try {
            require_once 'models/Promotor.php';
            require_once 'models/Referidos.php';
            require_once 'models/Comisiones.php';
            
            $promotorModel = new Promotor();
            $referidosModel = new Referidos();
            $comisionesModel = new Comisiones();
            
            // Obtener lista de promotores con estadísticas
            $promotores = $promotorModel->listarTodos();
            
            // Enriquecer con estadísticas
            foreach ($promotores as &$promotor) {
                $estadisticas = $referidosModel->getEstadisticasPromotor($promotor['id']);
                $promotor = array_merge($promotor, $estadisticas);
            }
            
            // Estadísticas generales del sistema de referidos
            $estadisticas_sistema = [
                'total_promotores' => count($promotores),
                'promotores_activos' => count(array_filter($promotores, function($p) { 
                    return $p['estado'] === 'activo'; 
                })),
                'total_visitas' => array_sum(array_column($promotores, 'total_visitas')),
                'total_registros' => array_sum(array_column($promotores, 'total_registros')),
                'comision_pendiente' => $comisionesModel->getTotalPendiente(),
                'comision_pagada' => $comisionesModel->getTotalPagada()
            ];
            
            include 'views/admin/promotores/lista.php';
            
        } catch (Exception $e) {
            error_log("Error en admin promotores: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error cargando promotores: ' . $e->getMessage();
            header('Location: index.php?view=admin');
            exit;
        }
    }
    
    /**
     * Cambiar estado de un promotor
     */
    public function cambiarEstadoPromotor() {
        verificarAcceso(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $response = ['exito' => false, 'mensaje' => ''];
        
        try {
            $promotor_id = $_POST['promotor_id'] ?? null;
            $nuevo_estado = $_POST['estado'] ?? null;
            
            if (!$promotor_id || !$nuevo_estado) {
                throw new Exception('Datos incompletos');
            }
            
            if (!in_array($nuevo_estado, ['activo', 'inactivo', 'suspendido'])) {
                throw new Exception('Estado no válido');
            }
            
            require_once 'models/Promotor.php';
            $promotorModel = new Promotor();
            
            $resultado = $promotorModel->cambiarEstado($promotor_id, $nuevo_estado);
            
            if ($resultado) {
                $response['exito'] = true;
                $response['mensaje'] = 'Estado actualizado correctamente';
            } else {
                $response['mensaje'] = 'Error al actualizar el estado';
            }
            
        } catch (Exception $e) {
            $response['mensaje'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Vista de comisiones para administración
     */
    public function comisiones() {
        verificarAcceso(['admin']);
        
        try {
            require_once 'models/Comisiones.php';
            require_once 'models/Promotor.php';
            
            $comisionesModel = new Comisiones();
            $promotorModel = new Promotor();
            
            // Parámetros de filtrado
            $estado = $_GET['estado'] ?? 'pendiente';
            $promotor_id = $_GET['promotor'] ?? null;
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
            $por_pagina = 50;
            
            // Obtener comisiones
            $filtros = [];
            if ($estado !== 'todas') {
                $filtros['estado'] = $estado;
            }
            if ($promotor_id) {
                $filtros['promotor_id'] = $promotor_id;
            }
            
            $comisiones = $comisionesModel->listarParaAdmin($filtros, $por_pagina, ($pagina - 1) * $por_pagina);
            $total_comisiones = $comisionesModel->contarParaAdmin($filtros);
            $total_paginas = ceil($total_comisiones / $por_pagina);
            
            // Lista de promotores para filtro
            $promotores = $promotorModel->listarTodos();
            
            // Estadísticas de comisiones
            $estadisticas = $comisionesModel->getEstadisticasAdmin();
            
            include 'views/admin/promotores/comisiones.php';
            
        } catch (Exception $e) {
            error_log("Error en admin comisiones: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error cargando comisiones: ' . $e->getMessage();
            header('Location: index.php?view=admin');
            exit;
        }
    }
    
    /**
     * Aprobar o rechazar comisión
     */
    public function procesarComision() {
        verificarAcceso(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $response = ['exito' => false, 'mensaje' => ''];
        
        try {
            $comision_id = $_POST['comision_id'] ?? null;
            $accion = $_POST['accion'] ?? null; // 'aprobar' o 'rechazar'
            $notas = $_POST['notas'] ?? '';
            
            if (!$comision_id || !$accion) {
                throw new Exception('Datos incompletos');
            }
            
            if (!in_array($accion, ['aprobar', 'rechazar'])) {
                throw new Exception('Acción no válida');
            }
            
            require_once 'models/Comisiones.php';
            $comisionesModel = new Comisiones();
            
            $authHelper = new AuthHelper();
            $admin = $authHelper->obtenerUsuarioActual();
            
            if ($accion === 'aprobar') {
                $resultado = $comisionesModel->aprobar($comision_id, $admin['id'], $notas);
                $mensaje_exito = 'Comisión aprobada correctamente';
            } else {
                $resultado = $comisionesModel->rechazar($comision_id, $admin['id'], $notas);
                $mensaje_exito = 'Comisión rechazada correctamente';
            }
            
            if ($resultado) {
                $response['exito'] = true;
                $response['mensaje'] = $mensaje_exito;
            } else {
                $response['mensaje'] = 'Error al procesar la comisión';
            }
            
        } catch (Exception $e) {
            $response['mensaje'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Marcar comisión como pagada
     */
    public function marcarComisionPagada() {
        verificarAcceso(['admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }
        
        $response = ['exito' => false, 'mensaje' => ''];
        
        try {
            $comision_id = $_POST['comision_id'] ?? null;
            $referencia_pago = $_POST['referencia'] ?? '';
            $notas = $_POST['notas'] ?? '';
            
            if (!$comision_id) {
                throw new Exception('ID de comisión requerido');
            }
            
            require_once 'models/Comisiones.php';
            $comisionesModel = new Comisiones();
            
            $authHelper = new AuthHelper();
            $admin = $authHelper->obtenerUsuarioActual();
            
            $resultado = $comisionesModel->marcarPagada($comision_id, $admin['id'], $referencia_pago, $notas);
            
            if ($resultado) {
                $response['exito'] = true;
                $response['mensaje'] = 'Comisión marcada como pagada';
            } else {
                $response['mensaje'] = 'Error al marcar como pagada';
            }
            
        } catch (Exception $e) {
            $response['mensaje'] = $e->getMessage();
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    /**
     * Obtener detalles de un promotor específico
     */
    public function detallePromotor() {
        verificarAcceso(['admin']);
        
        $promotor_id = $_GET['id'] ?? null;
        
        if (!$promotor_id) {
            $_SESSION['mensaje_error'] = 'ID de promotor requerido';
            header('Location: index.php?action=admin_promotores');
            exit;
        }
        
        try {
            require_once 'models/Promotor.php';
            require_once 'models/Referidos.php';
            require_once 'models/Comisiones.php';
            
            $promotorModel = new Promotor();
            $referidosModel = new Referidos();
            $comisionesModel = new Comisiones();
            
            $promotor = $promotorModel->getById($promotor_id);
            if (!$promotor) {
                throw new Exception('Promotor no encontrado');
            }
            
            // Estadísticas detalladas
            $estadisticas = $referidosModel->getEstadisticasPromotor($promotor_id);
            
            // Historial de referidos
            $referidos_recientes = $referidosModel->getByPromotorId($promotor_id, 20);
            
            // Comisiones recientes
            $comisiones_recientes = $comisionesModel->getByPromotorId($promotor_id, 'todas', 20);
            
            include 'views/admin/promotores/detalle.php';
            
        } catch (Exception $e) {
            error_log("Error detalle promotor: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?action=admin_promotores');
            exit;
        }
    }
    
    /**
     * Configuración del sistema de promotores
     */
    public function configuracionPromotores() {
        verificarAcceso(['admin']);
        
        try {
            require_once 'models/Comisiones.php';
            $comisionesModel = new Comisiones();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Procesar actualización de configuración
                $config = [
                    'comision_por_referido' => (float)($_POST['comision_por_referido'] ?? 0),
                    'comision_activa' => isset($_POST['comision_activa']) ? 1 : 0,
                    'limite_visitas_diarias' => (int)($_POST['limite_visitas_diarias'] ?? 100),
                    'tiempo_expiracion_cookie' => (int)($_POST['tiempo_expiracion_cookie'] ?? 30),
                    'requiere_aprobacion_manual' => isset($_POST['requiere_aprobacion_manual']) ? 1 : 0
                ];
                
                $resultado = $comisionesModel->actualizarConfiguracion($config);
                
                if ($resultado) {
                    $_SESSION['mensaje_exito'] = 'Configuración actualizada correctamente';
                } else {
                    $_SESSION['mensaje_error'] = 'Error al actualizar configuración';
                }
                
                header('Location: index.php?action=admin_config_promotores');
                exit;
            }
            
            // Cargar configuración actual
            $config_actual = $comisionesModel->getConfiguracion();
            
            include 'views/admin/promotores/configuracion.php';
            
        } catch (Exception $e) {
            error_log("Error config promotores: " . $e->getMessage());
            $_SESSION['mensaje_error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?view=admin');
            exit;
        }
    }
}
?>