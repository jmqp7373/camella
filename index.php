<?php
/**
 * CAMELLA.COM.CO - Portal de Empleo
 * Archivo principal del sitio web con estructura MVC
 * 
 * @author Camella Development Team
 * @version 2.0
 * @date 2025
 */

// Configuraciones globales
error_reporting(E_ALL);
ini_set('display_errors', 0); // En producción debe ser 0

// Definir rutas principales
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views/');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers/');
define('MODELS_PATH', BASE_PATH . '/models/');

// Función para sanitizar entradas
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Inicializar sesión
session_start();

// Obtener parámetros de la URL
$view = isset($_GET['view']) ? sanitize_input($_GET['view']) : 'home';
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'index';

// Rutas y controladores
$routes = [
    'home' => 'HomeController',
    'admin' => 'AdminController',
    'contacto' => 'ContactoController',
    'publicar-oferta' => 'OfertasController',
    'buscar-empleo' => 'BusquedaController',
    'registro-empresa' => 'RegistroController',
    'registro-talento' => 'RegistroController',
    'login' => 'AuthController',
    'registro' => 'AuthController',
    'recuperar-password' => 'AuthController',
    'privacidad' => 'LegalController',
    'terminos' => 'LegalController',
    'ayuda' => 'SoporteController'
];

// Verificar si es una llamada API
if (isset($_GET['api'])) {
    $api = sanitize_input($_GET['api']);
    handleAPI($api);
    exit;
}

try {
    // Determinar el controlador
    if (isset($routes[$view])) {
        $controllerName = $routes[$view];
        $controllerFile = CONTROLLERS_PATH . $controllerName . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                
                // Verificar si el método existe
                if (method_exists($controller, $action)) {
                    $controller->$action();
                } else {
                    // Método por defecto
                    $controller->index();
                }
            } else {
                loadStaticView($view);
            }
        } else {
            loadStaticView($view);
        }
    } else {
        loadStaticView($view);
    }
    
} catch (Exception $e) {
    error_log("Error en index.php: " . $e->getMessage());
    loadErrorView();
}

/**
 * Cargar vista estática (sin controlador)
 */
function loadStaticView($view) {
    $allowed_static_views = [
        'contacto', 'privacidad', 'terminos', 'ayuda',
        'publicar-oferta', 'buscar-empleo', 'registro-empresa',
        'registro-talento', 'login', 'registro', 'recuperar-password'
    ];
    
    if (in_array($view, $allowed_static_views)) {
        $viewPath = VIEWS_PATH . $view . '.php';
        
        include 'partials/header.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            loadErrorView();
        }
        
        include 'partials/footer.php';
    } else {
        loadErrorView();
    }
}

/**
 * Cargar vista de error
 */
function loadErrorView() {
    include 'partials/header.php';
    echo '<div style="text-align: center; padding: 4rem;">';
    echo '<h2><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Vista no encontrada</h2>';
    echo '<p>La página solicitada no existe o está en desarrollo.</p>';
    echo '<a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
    include 'partials/footer.php';
}

/**
 * Manejar llamadas API
 */
function handleAPI($api) {
    header('Content-Type: application/json');
    
    switch($api) {
        case 'categorias':
            require_once 'controllers/HomeController.php';
            $controller = new HomeController();
            $controller->apiCategorias();
            break;
            
        case 'oficios':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->apiOficios();
            break;
            
        case 'sistema':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->verificarSistema();
            break;
            
        default:
            echo json_encode([
                'exito' => false,
                'mensaje' => 'API no encontrada'
            ]);
    }
}
?>
