<?php
/**
 * Controlador de Oficios
 * Gestiona las operaciones relacionadas con los oficios
 */

require_once __DIR__ . '/../models/OficioModel.php';

// Manejo de acciones AJAX
if (isset($_GET['action'])) {
    $controller = new OficioController();
    
    switch ($_GET['action']) {
        case 'togglePopular':
            $controller->togglePopular();
            exit;
        case 'obtener':
            $controller->obtenerOficio();
            exit;
        case 'listarPorCategoria':
            $controller->listarPorCategoria();
            exit;
        case 'listarPopulares':
            $controller->listarPopulares();
            exit;
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit;
    }
}

class OficioController {
    private $oficioModel;
    
    public function __construct() {
        $this->oficioModel = new OficioModel();
    }
    
    /**
     * Toggle popularidad de un oficio (AJAX)
     * URL: OficioController.php?action=togglePopular&id=7
     */
    public function togglePopular() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de oficio no proporcionado o inválido'
            ]);
            return;
        }

        $id = (int)$_GET['id'];

        try {
            $resultado = $this->oficioModel->togglePopular($id);
            
            if ($resultado['success']) {
                echo json_encode([
                    'success' => true,
                    'newState' => $resultado['newState'],
                    'message' => $resultado['message']
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => $resultado['message']
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener datos de un oficio específico (AJAX)
     * URL: OficioController.php?action=obtener&id=7
     */
    public function obtenerOficio() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de oficio no proporcionado o inválido'
            ]);
            return;
        }

        $id = (int)$_GET['id'];

        try {
            $oficio = $this->oficioModel->obtenerPorId($id);
            
            if ($oficio) {
                echo json_encode([
                    'success' => true,
                    'data' => $oficio
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Oficio no encontrado'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener el oficio: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Listar oficios de una categoría (AJAX)
     * URL: OficioController.php?action=listarPorCategoria&categoria_id=3
     */
    public function listarPorCategoria() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['categoria_id']) || !is_numeric($_GET['categoria_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de categoría no proporcionado o inválido'
            ]);
            return;
        }

        $categoriaId = (int)$_GET['categoria_id'];

        try {
            $oficios = $this->oficioModel->obtenerPorCategoria($categoriaId);
            
            echo json_encode([
                'success' => true,
                'data' => $oficios,
                'total' => count($oficios)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar oficios: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Listar oficios populares (AJAX)
     * URL: OficioController.php?action=listarPopulares
     */
    public function listarPopulares() {
        header('Content-Type: application/json');

        try {
            $oficios = $this->oficioModel->obtenerPopulares();
            
            echo json_encode([
                'success' => true,
                'data' => $oficios,
                'total' => count($oficios)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al listar oficios populares: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Crear nuevo oficio (AJAX/POST)
     * URL: OficioController.php?action=crear
     */
    public function crearOficio() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido. Use POST.'
            ]);
            return;
        }

        $categoriaId = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : 0;
        $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
        $popular = isset($_POST['popular']) ? (int)$_POST['popular'] : 0;

        if (!$categoriaId || empty($titulo)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Categoría y título son obligatorios'
            ]);
            return;
        }

        try {
            $nuevoId = $this->oficioModel->crear($categoriaId, $titulo, $popular);
            
            if ($nuevoId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Oficio creado exitosamente',
                    'id' => $nuevoId
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el oficio'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear el oficio: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualizar oficio existente (AJAX/POST)
     * URL: OficioController.php?action=actualizar
     */
    public function actualizarOficio() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido. Use POST.'
            ]);
            return;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de oficio es obligatorio'
            ]);
            return;
        }

        $datos = [];
        if (isset($_POST['titulo'])) {
            $datos['titulo'] = $_POST['titulo'];
        }
        if (isset($_POST['popular'])) {
            $datos['popular'] = (int)$_POST['popular'];
        }
        if (isset($_POST['activo'])) {
            $datos['activo'] = (int)$_POST['activo'];
        }
        if (isset($_POST['categoria_id'])) {
            $datos['categoria_id'] = (int)$_POST['categoria_id'];
        }

        if (empty($datos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No se proporcionaron datos para actualizar'
            ]);
            return;
        }

        try {
            $exito = $this->oficioModel->actualizar($id, $datos);
            
            if ($exito) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Oficio actualizado exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el oficio'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el oficio: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar oficio (soft delete)
     * URL: OficioController.php?action=eliminar&id=7
     */
    public function eliminarOficio() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de oficio no proporcionado o inválido'
            ]);
            return;
        }

        $id = (int)$_GET['id'];

        try {
            $exito = $this->oficioModel->eliminar($id);
            
            if ($exito) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Oficio eliminado exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el oficio'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el oficio: ' . $e->getMessage()
            ]);
        }
    }
}
?>
