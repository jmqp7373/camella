<?php
/**
 * Controlador Base para Camella.com.co
 * Contiene funciones comunes para todos los controladores
 */

class BaseController {
    
    /**
     * Renderizar una vista con datos
     */
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = VIEWS_PATH . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("Vista no encontrada: " . $view);
        }
    }
    
    /**
     * Redireccionar a otra página
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit();
    }
    
    /**
     * Respuesta JSON para AJAX
     */
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Validar datos POST
     */
    protected function validatePost($required_fields) {
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $errors[] = "El campo {$field} es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar datos de entrada
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generar token CSRF
     */
    protected function generateCSRFToken() {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
    
    /**
     * Verificar token CSRF
     */
    protected function verifyCSRFToken($token) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
