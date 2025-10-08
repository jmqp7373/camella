<?php
/**
 * Controlador de Usuario para Camella.com.co
 * Maneja operaciones relacionadas con el perfil del usuario autenticado
 * Incluye cambio de contraseña con verificación CSRF y bcrypt
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/BaseController.php';

class UsuarioController extends BaseController {

    /**
     * Mostrar formulario para cambiar contraseña
     * 
     * Propósito: Renderizar la vista del formulario de cambio de contraseña
     * con token CSRF generado. Solo usuarios autenticados pueden acceder.
     * 
     * Requisitos:
     * - Usuario debe estar autenticado
     * - Rol válido: admin, promotor, o publicante
     * 
     * @return void Renderiza la vista cambiar_password
     */
    public function mostrarCambiarPassword() {
        // Verificar acceso: solo usuarios autenticados con roles válidos
        $this->verificarAcceso(['admin', 'promotor', 'publicante']);
        
        // Generar token CSRF si no existe
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        // Preparar datos para la vista
        $pageTitle = "Cambiar Contraseña";
        $mensaje = $_SESSION['mensaje'] ?? null;
        $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? null;
        
        // Limpiar mensajes de sesión después de mostrarlos
        unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
        
        // Renderizar vista
        include __DIR__ . '/../views/usuario/cambiar_password.php';
    }

    /**
     * Procesar cambio de contraseña (POST)
     * 
     * Propósito: Validar y procesar el cambio de contraseña del usuario.
     * Incluye verificación CSRF, validación de contraseña actual, 
     * validación de fortaleza de nueva contraseña y actualización con bcrypt.
     * 
     * Validaciones:
     * - Token CSRF válido
     * - Contraseña actual correcta (password_verify)
     * - Nueva contraseña fuerte (min 10 chars, may/min/num)
     * - Confirmación de contraseña coincide
     * 
     * @return void Redirige con mensaje de éxito/error
     */
    public function procesarCambiarPassword() {
        // Verificar acceso
        $this->verificarAcceso(['admin', 'promotor', 'publicante']);
        
        // Verificar método POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Método no permitido.');
        }
        
        // Verificación CSRF - línea clave: validar token para prevenir ataques
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            http_response_code(400);
            exit('Solicitud inválida.');
        }
        
        // Obtener datos del formulario
        $actualPassword = $_POST['actual_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
        
        try {
            // Obtener PDO
            $pdo = getPDO();
            
            // Obtener usuario actual de la base de datos
            $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }
            
            // Verificación password_verify - línea clave: validar contraseña actual
            if (!password_verify($actualPassword, $usuario['password'])) {
                throw new Exception("La contraseña actual es incorrecta");
            }
            
            // Validar fortaleza de nueva contraseña
            // Nota para devs novatos: Para ampliar reglas sin romper UX,
            // modifica esta validación y agrega mensajes informativos en el frontend
            if (!$this->esPasswordFuerte($newPassword)) {
                throw new Exception("La nueva contraseña debe tener al menos 10 caracteres, incluyendo mayúsculas, minúsculas y números");
            }
            
            // Validar confirmación de contraseña
            if ($newPassword !== $newPasswordConfirm) {
                throw new Exception("Las contraseñas nuevas no coinciden");
            }
            
            // password_hash - línea clave: generar hash bcrypt seguro
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // UPDATE - línea clave: actualizar contraseña en base de datos
            $stmt = $pdo->prepare("UPDATE usuarios SET password = :hash, actualizado_en = NOW() WHERE id = :id");
            $resultado = $stmt->execute([
                'hash' => $hash,
                'id' => $_SESSION['usuario_id']
            ]);
            
            if (!$resultado) {
                throw new Exception("Error al actualizar la contraseña");
            }
            
            // Logging discreto de evento de cambio de contraseña (sin loggear contraseñas)
            error_log("[password_change] user={$_SESSION['usuario_id']} ok");
            
            // Mensaje de éxito
            $_SESSION['mensaje'] = 'Contraseña cambiada exitosamente';
            $_SESSION['tipo_mensaje'] = 'success';
            
        } catch (Exception $e) {
            // Logging de error
            error_log("[password_change] user={$_SESSION['usuario_id']} error: " . $e->getMessage());
            
            // Mensaje de error
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        // Redirigir de vuelta al formulario
        header('Location: index.php?view=cambiar-password');
        exit();
    }

    /**
     * Verificar si la contraseña es fuerte
     * 
     * @param string $password Contraseña a validar
     * @return bool True si cumple criterios de fortaleza
     */
    private function esPasswordFuerte($password) {
        // Validación: mínimo 10 caracteres, al menos mayúscula, minúscula, número
        return strlen($password) >= 10 && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/\d/', $password);
    }

    /**
     * Verificar acceso del usuario según roles permitidos
     * 
     * @param array $rolesPermitidos Array de roles que pueden acceder
     * @throws Exception Si el usuario no tiene acceso
     */
    private function verificarAcceso($rolesPermitidos) {
        // Verificar si hay sesión activa
        if (empty($_SESSION['usuario_id'])) {
            // Setear aviso no intrusivo antes de redirigir
            $_SESSION['flash_error'] = 'Inicia sesión para continuar.';
            header('Location: index.php?view=login');
            exit();
        }
        
        // Verificar rol del usuario
        $rolUsuario = $_SESSION['rol'] ?? null;
        if (!in_array($rolUsuario, $rolesPermitidos)) {
            http_response_code(403);
            exit('Acceso denegado.');
        }
    }
}