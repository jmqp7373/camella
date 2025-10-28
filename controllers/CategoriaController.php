<?php
/**
 * Controlador para gestión de categorías
 * Maneja operaciones CRUD y listado de categorías
 */
class CategoriaController {
    private $categoriaModel;
    
    public function __construct() {
        require_once __DIR__ . '/../models/Categorias.php';
        $this->categoriaModel = new Categorias();
    }
    
    /**
     * Procesar las acciones del controlador
     */
    public function processAction() {
        $action = $_GET['action'] ?? '';
        
        switch($action) {
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'updateNombre':
                $this->updateNombre();
                break;
            case 'updateIcono':
                $this->updateIcono();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'getById':
                $this->getById();
                break;
            case 'list':
                $this->listAll();
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    }

    /**
     * Crear nueva categoría (CRUD)
     */
    public function create() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $nombre = $_POST['titulo'] ?? ''; // Frontend envía 'titulo' pero BD usa 'nombre'
            $descripcion = $_POST['descripcion'] ?? '';
            $icono = $_POST['icono'] ?? '';
            $activo = isset($_POST['activo']) ? 1 : 0;

            if (empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                return;
            }

            // Verificar si ya existe una categoría con el mismo nombre
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ?");
            $stmt->execute([$nombre]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre']);
                return;
            }

            $resultado = $this->categoriaModel->crear([
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'icono' => $icono,
                'activo' => $activo
            ]);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'id' => $resultado]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear la categoría']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar categoría existente (CRUD)
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
            $nombre = $_POST['titulo'] ?? ''; // Frontend envía 'titulo' pero BD usa 'nombre'
            $descripcion = $_POST['descripcion'] ?? '';
            $icono = $_POST['icono'] ?? '';
            $activo = isset($_POST['activo']) ? 1 : 0;

            if ($id <= 0 || empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'ID y nombre son requeridos']);
                return;
            }

            // Verificar si ya existe otra categoría con el mismo nombre
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ya existe otra categoría con ese nombre']);
                return;
            }

            $resultado = $this->categoriaModel->actualizar($id, [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'icono' => $icono,
                'activo' => $activo
            ]);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar solo el nombre de una categoría (AJAX inline edit)
     */
    public function updateNombre() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');

            if ($id <= 0 || empty($nombre)) {
                echo json_encode(['success' => false, 'message' => 'ID y nombre son requeridos']);
                return;
            }

            // Verificar si ya existe otra categoría con el mismo nombre
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nombre = ? AND id != ?");
            $stmt->execute([$nombre, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ya existe otra categoría con ese nombre']);
                return;
            }

            // Actualizar solo el nombre
            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
            $resultado = $stmt->execute([$nombre, $id]);

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Categoría actualizada exitosamente',
                    'nombre' => $nombre
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la categoría']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar solo el ícono de una categoría (AJAX inline edit)
     */
    public function updateIcono() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $id = (int)($_POST['id'] ?? 0);
            $icono = trim($_POST['icono'] ?? '');

            if ($id <= 0 || empty($icono)) {
                echo json_encode(['success' => false, 'message' => 'ID e ícono son requeridos']);
                return;
            }

            // Actualizar solo el ícono
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            $stmt = $pdo->prepare("UPDATE categorias SET icono = ? WHERE id = ?");
            $resultado = $stmt->execute([$icono, $id]);

            if ($resultado) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Ícono actualizado exitosamente',
                    'icono' => $icono
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el ícono']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Eliminar categoría (CRUD)
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

            // Verificar si la categoría tiene oficios asociados
            require_once __DIR__ . '/../config/database.php';
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM oficios WHERE categoria_id = ?");
            $stmt->execute([$id]);
            $totalOficios = $stmt->fetchColumn();

            if ($totalOficios > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => "No se puede eliminar la categoría porque tiene $totalOficios oficio(s) asociado(s)"
                ]);
                return;
            }

            $resultado = $this->categoriaModel->eliminar($id);

            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la categoría']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener categoría por ID
     */
    public function getById() {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_GET['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                return;
            }

            $categoria = $this->categoriaModel->obtenerPorId($id);

            if ($categoria) {
                echo json_encode(['success' => true, 'data' => $categoria]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Listar todas las categorías
     */
    public function listAll() {
        header('Content-Type: application/json');
        
        try {
            $categorias = $this->categoriaModel->obtenerTodas();
            
            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}

// Ejecutar el controlador si se accede directamente
if (basename($_SERVER['PHP_SELF']) === 'CategoriaController.php') {
    $controller = new CategoriaController();
    $controller->processAction();
}
?>