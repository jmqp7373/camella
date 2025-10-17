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
