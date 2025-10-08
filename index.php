<?php
/**
 * CAMELLA.COM.CO - Portal de Empleo
 * Archivo principal del sitio web con estructura MVC
 * 
 * @author Camella Development Team
 * @version 2.0
 * @date 2025
 * 
 * HOTFIX: Safe Router + Bootstrap centralizado para prevenir errores 500
 */

// --- Safe Router Bootstrap (no altera maquetación) ---
/**
 * Output buffering para prevenir "headers already sent"
 * 
 * PROPÓSITO: Capturar cualquier salida temprana que pueda causar
 * el error "headers already sent" cuando se establezcan cookies
 * o se redireccione. Esto es especialmente importante para el
 * tracking de referidos que establece cookies.
 */
if (function_exists('ob_start')) {
    ob_start(); // LÍNEA CLAVE: Evita "headers already sent" por salidas tempranas
}

/**
 * BOOTSTRAP CENTRALIZADO - LÍNEA CRÍTICA
 * 
 * Propósito: Inicializar sesión y cargar helpers de forma centralizada
 * para evitar "headers already sent" y includes duplicados.
 * 
 * DEBE ser la primera línea de lógica PHP para evitar problemas de sesión.
 */
require_once __DIR__ . '/bootstrap.php';

/**
 * Reforzar configuración de errores
 * 
 * PROPÓSITO: Asegurar que notices/warnings no se muestren al usuario
 * final, complementando el error handler ya cargado por bootstrap.
 */
if (!headers_sent()) {
    // LÍNEA CLAVE: Mantener ocultos los errores al usuario final
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}

/**
 * ENRUTADO PROTEGIDO CON FALLBACK
 * 
 * ESTRATEGIA: Envolver todo el router actual en try/catch para que
 * cualquier excepción o error fatal no tumbe completamente el sitio.
 * 
 * COMPORTAMIENTO:
 * - Si todo va bien: funciona normalmente
 * - Si hay excepción: loggea el error y carga home como fallback
 * - Mantiene UX fluida sin exponer errores técnicos
 * 
 * NOTA IMPORTANTE: No se cambia la maquetación existente, solo se
 * envuelve la lógica de enrutado para mayor robustez.
 */
try {
    // ====== ROUTER ACTUAL (NO MODIFICADO) ======

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

// Manejar acciones POST antes de cargar vistas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    session_start();
    $action = sanitize_input($_POST['action']);
    
    switch($action) {
        case 'agregarCategoria':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->agregarCategoria();
            break;
            
        case 'editarCategoria':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->editarCategoria();
            break;
            
        case 'agregarOficio':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->agregarOficio();
            break;
            
        // ========== ACCIONES DE PROMOTORES ==========
        case 'rastrear_visita':
            require_once 'controllers/PromotorController.php';
            $controller = new PromotorController();
            $controller->rastrearVisita();
            break;
            
        case 'atribuir_registro':
            require_once 'controllers/PromotorController.php';
            $controller = new PromotorController();
            $controller->atribuirRegistro();
            break;
            
        // Admin - Promotores
        case 'admin_cambiar_estado_promotor':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->cambiarEstadoPromotor();
            break;
            
        case 'admin_procesar_comision':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->procesarComision();
            break;
            
        case 'admin_marcar_comision_pagada':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->marcarComisionPagada();
            break;
    }
    exit;
}

// Obtener la vista solicitada (default: home)
$view = isset($_GET['view']) ? sanitize_input($_GET['view']) : 'home';

// Lista de vistas permitidas (seguridad)
$allowed_views = [
    'home',
    'contacto',
    'publicar-oferta',
    'buscar-empleo',
    'registro-empresa',
    'registro-talento',
    'login',
    'registro',
    'recuperar-password',
    'privacidad',
    'terminos',
    'ayuda',
    'admin'
];

// Verificar que la vista sea válida
if (!in_array($view, $allowed_views)) {
    $view = 'home';
}

// Manejar rutas especiales (admin, promotor y APIs)
if ($view === 'admin') {
    // Inicializar sesión para admin
    session_start();
    
    $action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'index';
    
    require_once 'controllers/AdminController.php';
    $controller = new AdminController();
    
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
    exit;
}

// Manejar acciones GET generales
if (isset($_GET['action'])) {
    session_start();
    $action = sanitize_input($_GET['action']);
    
    switch($action) {
        // ========== VISTAS DE PROMOTORES ==========
        case 'promotor_panel':
            require_once 'controllers/PromotorController.php';
            $controller = new PromotorController();
            $controller->panel();
            break;
            
        case 'promotor_comisiones':
            require_once 'controllers/PromotorController.php';
            $controller = new PromotorController();
            $controller->comisiones();
            break;
            
        // ========== VISTAS ADMIN - PROMOTORES ==========
        case 'admin_promotores':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->promotores();
            break;
            
        case 'admin_comisiones':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->comisiones();
            break;
            
        case 'admin_detalle_promotor':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->detallePromotor();
            break;
            
        case 'admin_config_promotores':
            require_once 'controllers/AdminController.php';
            $controller = new AdminController();
            $controller->configuracionPromotores();
            break;
            
        // Otras acciones...
        default:
            // Redirigir a home si la acción no existe
            header('Location: index.php');
            exit;
    }
    exit;
}

// Verificar si es una llamada API
if (isset($_GET['api'])) {
    header('Content-Type: application/json');
    $api = sanitize_input($_GET['api']);
    
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
    exit;
}

// Definir la ruta del archivo de vista
$viewPath = VIEWS_PATH . $view . '.php';

// Incluir header
include 'partials/header.php';

// Cargar la vista correspondiente
if (file_exists($viewPath)) {
    include $viewPath;
} else {
    // Vista de error 404 personalizada
    echo '<div style="text-align: center; padding: 4rem;">';
    echo '<h2><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Vista no encontrada</h2>';
    echo '<p>La página <strong>"' . htmlspecialchars($view) . '"</strong> no existe o está en desarrollo.</p>';
    echo '<a href="index.php" class="btn btn-primary"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
}

// Incluir footer
include 'partials/footer.php';

    // ====== FIN ROUTER ACTUAL ======

} catch (Throwable $e) {
    /**
     * MANEJO DE EXCEPCIONES DEL ROUTER
     * 
     * PROPÓSITO: Si cualquier parte del enrutado falla (controladores,
     * vistas, includes, etc.), capturar la excepción y continuar con
     * un fallback graceful en lugar de mostrar error 500 al usuario.
     * 
     * COMPORTAMIENTO:
     * - Loggea detalles técnicos completos para debugging
     * - Muestra home como página de fallback (UX fluida)
     * - Mantiene HTTP 200 para no afectar SEO
     * - No expone información sensible al usuario
     */
    
    // LÍNEA CLAVE: Log detallado para desarrolladores (no tumbar el sitio)
    error_log('[router throwable] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    error_log('[router trace] ' . $e->getTraceAsString());

    // LÍNEA CLAVE: Fallback a home con 200 OK (mantener UX fluida)
    http_response_code(200);
    
    // Limpiar cualquier salida parcial que pueda haber ocurrido
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Cargar home como página de seguridad
    try {
        include 'partials/header.php';
        include 'views/home.php';
        include 'partials/footer.php';
    } catch (Throwable $fallbackError) {
        // Si incluso el fallback falla, mostrar mensaje mínimo
        error_log('[router fallback error] ' . $fallbackError->getMessage());
        echo '<!DOCTYPE html><html><head><title>Camella.com.co</title></head><body>';
        echo '<h1>Bienvenido a Camella.com.co</h1>';
        echo '<p>Sitio temporalmente en mantenimiento. Por favor intenta más tarde.</p>';
        echo '</body></html>';
    }
}

/**
 * FINALIZACIÓN DEL SAFE ROUTER
 * 
 * Vaciar y finalizar el buffer de salida si fue iniciado.
 * Esto asegura que todo el contenido capturado se envíe al navegador.
 */
// LÍNEA CLAVE: Vaciar buffer (si hubo)
if (function_exists('ob_get_length') && ob_get_length()) {
    @ob_end_flush();
}
?>
