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
