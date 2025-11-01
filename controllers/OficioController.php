<?php
/**
 * Controlador de Oficios
 * Gestiona las operaciones relacionadas con los oficios
 */

require_once __DIR__ . '/../models/Oficios.php';

// Manejo de acciones AJAX
if (isset($_GET['action'])) {
    $controller = new OficioController();
    
    switch ($_GET['action']) {
        case 'togglePopular':
            $controller->togglePopular();
            exit;
        case 'toggleActivo':
            $controller->toggleActivo();
            exit;
        case 'obtener':
            $controller->obtenerOficio();
            exit;
        case 'listarPorCategoria':
            $controller->listarPorCategoria();
            exit;
        case 'getByCategoria':
            $controller->getByCategoria();
            exit;
        case 'listarPopulares':
            $controller->listarPopulares();
            exit;
        case 'create':
            $controller->create();
            exit;
        case 'update':
            $controller->update();
            exit;
        case 'updateNombre':
            $controller->updateNombre();
            exit;
        case 'delete':
            $controller->delete();
            exit;
        case 'stats':
            $controller->getStats();
            exit;
        case 'listAll':
            $controller->listAll();
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
        $this->oficioModel = new Oficios();
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
     * Toggle estado activo/inactivo de un oficio (AJAX)
     * URL: OficioController.php?action=toggleActivo&id=7
     */
    public function toggleActivo() {
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
            $resultado = $this->oficioModel->toggleActivo($id);
            
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
     * Actualizar solo el nombre de un oficio (AJAX inline edit)
     * URL: OficioController.php?action=updateNombre
     */
    public function updateNombre() {
        header('Content-Type: application/json');
        
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de oficio no proporcionado o inválido'
            ]);
            return;
        }

        if (!isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nombre de oficio no puede estar vacío'
            ]);
            return;
        }

        $id = (int)$_POST['id'];
        $nombre = trim($_POST['nombre']);

        try {
            $resultado = $this->oficioModel->actualizarNombre($id, $nombre);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Nombre actualizado correctamente',
                    'nombre' => $nombre
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el nombre'
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
     * Obtener oficios activos por categoría (para formulario de publicar)
     * URL: OficioController.php?action=getByCategoria&id=<categoria_id>
     */
    public function getByCategoria() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de categoría no proporcionado o inválido'
            ]);
            return;
        }

        $categoriaId = (int)$_GET['id'];

        try {
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            
            // Obtener oficios activos de la categoría
            $stmt = $pdo->prepare("
                SELECT id, titulo as nombre
                FROM oficios
                WHERE categoria_id = ? AND activo = 1
                ORDER BY titulo ASC
            ");
            $stmt->execute([$categoriaId]);
            $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $oficios
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener oficios: ' . $e->getMessage()
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

    /**
     * Crear nuevo oficio (CRUD)
     */
    public function create() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $categoria_id = (int)($_POST['categoria_id'] ?? 0);
            $popular = isset($_POST['popular']) ? 1 : 0;
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (empty($titulo) || $categoria_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Título y categoría son requeridos']);
                return;
            }

            $resultado = $this->oficioModel->crear([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'categoria_id' => $categoria_id,
                'popular' => $popular,
                'activo' => $activo
            ]);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Oficio creado exitosamente', 'id' => $resultado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el oficio']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar oficio existente (CRUD)
     */
    public function update() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $popular = isset($_POST['popular']) ? 1 : 0;
            $activo = isset($_POST['activo']) ? 1 : 0;

            if ($id <= 0 || empty($titulo)) {
                echo json_encode(['success' => false, 'message' => 'ID y título son requeridos']);
                return;
            }

            $resultado = $this->oficioModel->actualizar($id, [
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'popular' => $popular,
                'activo' => $activo
            ]);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Oficio actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el oficio']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar oficio (CRUD)
     */
    public function delete() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                return;
            }

            $resultado = $this->oficioModel->eliminar($id);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Oficio eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el oficio']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener estadísticas de oficios
     */
    public function getStats() {
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();

            // Total de categorías
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE activo = 1");
            $totalCategorias = $stmt->fetchColumn();

            // Total de oficios activos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios WHERE activo = 1");
            $totalOficios = $stmt->fetchColumn();

            // Oficios populares
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios WHERE activo = 1 AND popular = 1");
            $oficiosPopulares = $stmt->fetchColumn();

            // Oficios inactivos
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios WHERE activo = 0");
            $oficiosInactivos = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'data' => [
                    'totalCategorias' => $totalCategorias,
                    'totalOficios' => $totalOficios,
                    'oficiosPopulares' => $oficiosPopulares,
                    'oficiosInactivos' => $oficiosInactivos
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Listar todas las categorías con sus oficios
     */
    public function listAll() {
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../config/database.php';
            require_once __DIR__ . '/../models/Categorias.php';
            
            $pdo = getPDO();
            $categoriasModel = new Categorias();
            $categorias = $categoriasModel->obtenerCategoriasConOficios();

            $oficiosPorCategoria = [];
            foreach ($categorias as $categoria) {
                $stmt = $pdo->prepare("SELECT id, titulo as nombre, popular, activo FROM oficios WHERE categoria_id = ? ORDER BY popular DESC, titulo ASC");
                $stmt->execute([$categoria['id']]);
                $oficiosPorCategoria[$categoria['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'categorias' => $categorias,
                    'oficios' => $oficiosPorCategoria
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
?>
