<?php
/**
 * Controlador de Administración
 * Gestiona las funciones administrativas y la inicialización de datos
 */

require_once 'models/Categorias.php';

class AdminController {
    private $categoriasModel;
    
    public function __construct() {
        $this->categoriasModel = new Categorias();
    }
    
    /**
     * Vista principal de administración
     */
    public function index() {
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
     * Gestión de categorías
     */
    public function categorias() {
        $this->inicializarSistema();
        
        $categorias = $this->categoriasModel->obtenerCategoriasConOficios();
        $pageTitle = "Gestión de Categorías";
        
        include 'views/admin/categorias.php';
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