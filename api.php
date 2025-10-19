<?php
/**
 * API Endpoints para Camella.com.co
 * Maneja las peticiones AJAX y operaciones del sistema
 */

// Configuraciones básicas
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Headers para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Obtener la acción solicitada
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Enrutamiento de acciones
switch ($action) {
    
    // ========================================
    // UPLOAD DE IMÁGENES
    // ========================================
    case 'uploadImage':
        require_once 'controllers/ImageUploadController.php';
        $controller = new ImageUploadController();
        $controller->upload();
        break;
    
    case 'deleteImage':
        require_once 'controllers/ImageUploadController.php';
        $controller = new ImageUploadController();
        $controller->delete();
        break;
    
    case 'getImages':
        require_once 'controllers/ImageUploadController.php';
        $controller = new ImageUploadController();
        $controller->getImages();
        break;
    
    // ========================================
    // GUARDAR/ACTUALIZAR ANUNCIO
    // ========================================
    case 'saveAnuncio':
        session_start();
        
        // Verificar sesión activa
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No estás autenticado'
            ]);
            exit;
        }
        
        // Obtener datos del formulario
        $anuncioId = isset($_POST['anuncio_id']) && !empty($_POST['anuncio_id']) ? (int)$_POST['anuncio_id'] : null;
        $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
        $precio = isset($_POST['precio']) && !empty($_POST['precio']) ? (float)$_POST['precio'] : null;
        $userId = $_SESSION['user_id'];
        
        // Validaciones
        if (empty($titulo)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El título es obligatorio'
            ]);
            exit;
        }
        
        if (empty($descripcion)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La descripción es obligatoria'
            ]);
            exit;
        }
        
        try {
            require_once 'config/database.php';
            $db = getPDO();
            
            if ($anuncioId) {
                // MODO EDITAR: Actualizar anuncio existente
                
                // Verificar que el anuncio pertenece al usuario
                $stmt = $db->prepare("SELECT user_id FROM anuncios WHERE id = ?");
                $stmt->execute([$anuncioId]);
                $anuncio = $stmt->fetch();
                
                if (!$anuncio) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Anuncio no encontrado'
                    ]);
                    exit;
                }
                
                if ($anuncio['user_id'] != $userId && $_SESSION['role'] !== 'admin') {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No tienes permiso para editar este anuncio'
                    ]);
                    exit;
                }
                
                // Actualizar el anuncio
                $stmt = $db->prepare("
                    UPDATE anuncios 
                    SET titulo = ?, descripcion = ?, precio = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$titulo, $descripcion, $precio, $anuncioId]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Anuncio actualizado exitosamente',
                    'anuncio_id' => $anuncioId,
                    'mode' => 'edit'
                ]);
                
            } else {
                // MODO NUEVO: Crear nuevo anuncio
                
                $stmt = $db->prepare("
                    INSERT INTO anuncios (user_id, titulo, descripcion, precio, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 'activo', NOW(), NOW())
                ");
                $stmt->execute([$userId, $titulo, $descripcion, $precio]);
                
                $nuevoAnuncioId = $db->lastInsertId();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Anuncio creado exitosamente',
                    'anuncio_id' => $nuevoAnuncioId,
                    'mode' => 'create'
                ]);
            }
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar el anuncio: ' . $e->getMessage()
            ]);
        }
        break;
    
    // ========================================
    // ELIMINAR ANUNCIO
    // ========================================
    case 'deleteAnuncio':
        session_start();
        
        // Verificar sesión activa
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No estás autenticado'
            ]);
            exit;
        }
        
        // Verificar que se recibió el ID del anuncio
        if (!isset($_POST['anuncio_id']) || empty($_POST['anuncio_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID del anuncio no proporcionado'
            ]);
            exit;
        }
        
        $anuncioId = (int)$_POST['anuncio_id'];
        $userId = $_SESSION['user_id'];
        
        try {
            require_once 'config/database.php';
            $db = getPDO();
            
            // Verificar que el anuncio pertenece al usuario (seguridad)
            $stmt = $db->prepare("
                SELECT id, titulo 
                FROM anuncios 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$anuncioId, $userId]);
            $anuncio = $stmt->fetch();
            
            if (!$anuncio) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este anuncio'
                ]);
                exit;
            }
            
            // Eliminar imágenes asociadas primero
            $stmt = $db->prepare("SELECT ruta FROM anuncio_imagenes WHERE anuncio_id = ?");
            $stmt->execute([$anuncioId]);
            $imagenes = $stmt->fetchAll();
            
            foreach ($imagenes as $imagen) {
                $rutaCompleta = __DIR__ . '/' . $imagen['ruta'];
                if (file_exists($rutaCompleta)) {
                    unlink($rutaCompleta);
                }
            }
            
            // Eliminar registros de imágenes de la base de datos
            $stmt = $db->prepare("DELETE FROM anuncio_imagenes WHERE anuncio_id = ?");
            $stmt->execute([$anuncioId]);
            
            // Eliminar el anuncio
            $stmt = $db->prepare("DELETE FROM anuncios WHERE id = ?");
            $stmt->execute([$anuncioId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Anuncio eliminado correctamente',
                'anuncio_id' => $anuncioId
            ]);
            
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar el anuncio: ' . $e->getMessage()
            ]);
        }
        break;
    
    // ========================================
    // ENDPOINT NO ENCONTRADO
    // ========================================
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint no encontrado',
            'action' => $action
        ]);
        break;
}
