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
require_once __DIR__ . '/../helpers/AuthHelper.php';

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
}
?>