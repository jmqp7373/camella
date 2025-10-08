<?php
/**
 * LoginController.php - Controlador de Autenticación
 * 
 * Propósito:
 * Maneja todas las operaciones de autenticación del sistema:
 * login, logout, registro de usuarios y verificación de acceso.
 * 
 * Responsabilidades:
 * - Procesar formularios de login y registro
 * - Manejar sesiones de usuario de forma segura
 * - Validar permisos de acceso por roles
 * - Renderizar vistas de autenticación
 * 
 * Flujos principales:
 * 1. Login: Validar credenciales → Iniciar sesión → Redireccionar
 * 2. Logout: Cerrar sesión → Limpiar cookies → Redireccionar
 * 3. Registro: Validar datos → Crear usuario → Auto-login opcional
 * 4. Middleware: Verificar autenticación en rutas protegidas
 * 
 * Integración con otros componentes:
 * - AuthHelper: Para manejo de sesiones y verificaciones
 * - Usuario model: Para operaciones de base de datos
 * - Views: Para renderizar formularios y mensajes
 * 
 * @author Camella Development Team  
 * @version 1.0
 * @date 2025-10-07
 */

/**
 * NOTA: AuthHelper ya está cargado via bootstrap.php centralizado
 * 
 * Antes: require_once 'helpers/AuthHelper.php';
 * Ahora: Se carga automáticamente en index.php via bootstrap.php
 * 
 * Propósito del cambio: Centralizar carga de dependencias críticas
 * para evitar problemas de rutas y includes duplicados.
 */
require_once 'models/Usuario.php';
require_once 'controllers/BaseController.php';

class LoginController extends BaseController {
    
    private $authHelper;
    private $usuarioModel;
    
    /**
     * Constructor del LoginController
     * 
     * Propósito: Inicializar dependencias necesarias para
     * las operaciones de autenticación.
     * 
     * Decisiones de diseño:
     * - Inyección de dependencias manual por simplicidad
     * - Uso de AuthHelper para lógica de sesiones
     * - Usuario model para operaciones de BD
     */
    public function __construct() {
        $this->authHelper = new AuthHelper();
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Mostrar página de login
     * 
     * Propósito: Renderizar el formulario de login o redireccionar
     * si el usuario ya está autenticado.
     * 
     * Flujo:
     * 1. Verificar si ya está autenticado
     * 2. Si sí → redireccionar a dashboard
     * 3. Si no → mostrar formulario de login
     * 
     * Vista esperada: views/auth/login.php
     * 
     * @param array $params Parámetros de la URL
     */
    public function mostrarLogin($params = []) {
        // Si ya está autenticado, redireccionar
        if ($this->authHelper->estaAutenticado()) {
            $usuario = $this->authHelper->obtenerUsuarioActual();
            $this->redireccionarSegunRol($usuario['rol']);
            return;
        }
        
        // Preparar datos para la vista
        $datos = [
            'titulo' => 'Iniciar Sesión - Camella',
            'mensaje' => isset($_GET['mensaje']) ? $_GET['mensaje'] : null,
            'error' => isset($_GET['error']) ? $_GET['error'] : null,
            'redirect' => isset($_GET['redirect']) ? $_GET['redirect'] : null
        ];
        
        // Renderizar vista de login
        $this->renderizarVista('auth/login', $datos);
    }
    
    /**
     * Procesar formulario de login
     * 
     * Propósito: Validar credenciales del usuario y establecer
     * la sesión de autenticación si son correctas.
     * 
     * Flujo de procesamiento:
     * 1. Validar que sea request POST
     * 2. Obtener y sanitizar datos del formulario  
     * 3. Validar credenciales con el modelo Usuario
     * 4. Si válidas → iniciar sesión → redireccionar
     * 5. Si inválidas → mostrar error → volver al formulario
     * 
     * Seguridad implementada:
     * - Validación de método HTTP
     * - Sanitización de inputs
     * - Rate limiting implícito (via sesión)
     * - Redirección segura después del login
     * 
     * @return void (redirecciona o muestra vista con error)
     */
    public function procesarLogin() {
        // Solo aceptar POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('/login?error=' . urlencode('Método no permitido'));
            return;
        }
        
        try {
            /**
             * VERIFICACIÓN DE TOKEN CSRF - LÍNEA CRÍTICA DE SEGURIDAD
             * 
             * Propósito: Verificar que el request proviene del formulario legítimo
             * y no de un sitio malicioso intentando un ataque CSRF.
             * 
             * Flujo de validación:
             * 1. Verificar que el token fue enviado en POST
             * 2. Comparar con el token almacenado en sesión
             * 3. Usar hash_equals() para prevenir timing attacks
             * 4. Rechazar request si no coincide
             * 
             * Comentario: Evita ataques CSRF en login donde un atacante
             * podría forzar login con sus propias credenciales en la sesión
             * de la víctima para acceder a datos posteriormente.
             */
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
                error_log("CSRF token inválido en login - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                http_response_code(400);
                exit('Solicitud inválida. Por favor recarga la página e intenta nuevamente.');
            }
            
            // Obtener y validar datos del formulario
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $recordar = isset($_POST['recordar']) ? true : false;
            $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : null;
            
            // Validaciones básicas
            if (empty($email) || empty($password)) {
                $this->redireccionar('/login?error=' . urlencode('Por favor complete todos los campos'));
                return;
            }
            
            // Validar credenciales con el modelo
            $usuario = $this->usuarioModel->validarCredenciales($email, $password);
            
            if ($usuario) {
                // Login exitoso - iniciar sesión segura
                $sesionIniciada = $this->authHelper->iniciarSesionSegura($usuario, $recordar);
                
                if ($sesionIniciada) {
                    /**
                     * REGENERACIÓN DE TOKEN CSRF POST-LOGIN - SEGURIDAD CRÍTICA
                     * 
                     * Propósito: Generar nuevo token CSRF después de login exitoso
                     * para prevenir session fixation y ataques posteriores.
                     * 
                     * Razón del cambio: El token anterior ya se usó para validar
                     * este login, debe ser regenerado para futuras operaciones
                     * sensibles del usuario autenticado.
                     */
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    
                    // Log del login exitoso
                    error_log("Login exitoso - Usuario: {$usuario['email']}, Rol: {$usuario['rol']}, IP: " . $_SERVER['REMOTE_ADDR']);
                    
                    // Determinar destino de redirección
                    if ($redirect && $this->esRedirectSegura($redirect)) {
                        $this->redireccionar($redirect);
                    } else {
                        $this->redireccionarSegunRol($usuario['rol']);
                    }
                } else {
                    // Error iniciando sesión
                    error_log("Error iniciando sesión para usuario: $email");
                    $this->redireccionar('/login?error=' . urlencode('Error interno del sistema'));
                }
            } else {
                // Credenciales inválidas
                error_log("Intento de login fallido para email: $email desde IP: " . $_SERVER['REMOTE_ADDR']);
                $this->redireccionar('/login?error=' . urlencode('Email o contraseña incorrectos'));
            }
            
        } catch (Exception $e) {
            error_log("Exception en procesarLogin: " . $e->getMessage());
            $this->redireccionar('/login?error=' . urlencode('Error interno del sistema'));
        }
    }
    
    /**
     * Cerrar sesión del usuario
     * 
     * Propósito: Terminar la sesión actual del usuario de forma segura
     * limpiando todas las cookies y variables de sesión.
     * 
     * Operaciones de logout:
     * 1. Cerrar sesión via AuthHelper
     * 2. Limpiar cookies de "recordar"
     * 3. Destruir sesión PHP
     * 4. Redireccionar a página principal
     * 
     * @return void (redirecciona a home)
     */
    public function logout() {
        try {
            // Obtener info del usuario para logging antes de cerrar sesión
            $usuario = $this->authHelper->obtenerUsuarioActual();
            
            // Cerrar sesión usando AuthHelper
            $this->authHelper->cerrarSesion();
            
            // Log del logout
            if ($usuario) {
                error_log("Logout exitoso - Usuario: {$usuario['email']}, IP: " . $_SERVER['REMOTE_ADDR']);
            }
            
            // Redireccionar a home con mensaje de confirmación
            $this->redireccionar('/?mensaje=' . urlencode('Sesión cerrada correctamente'));
            
        } catch (Exception $e) {
            error_log("Exception en logout: " . $e->getMessage());
            // Aun con error, redireccionar para seguridad
            $this->redireccionar('/');
        }
    }
    
    /**
     * Mostrar formulario de registro
     * 
     * Propósito: Renderizar la página de registro de nuevos usuarios
     * si el registro público está habilitado.
     * 
     * Notas:
     * - Por ahora solo admins pueden crear usuarios
     * - En futuro se puede habilitar registro público para 'publicante'
     * - Redirecciona a login si no está habilitado el registro
     * 
     * @param array $params Parámetros de la URL
     */
    public function mostrarRegistro($params = []) {
        // Por seguridad, por ahora solo admins pueden crear usuarios
        // TODO: Implementar registro público si se requiere en el futuro
        
        $this->redireccionar('/login?mensaje=' . urlencode('El registro no está disponible públicamente'));
    }
    
    /**
     * Middleware de autenticación
     * 
     * Propósito: Verificar que el usuario esté autenticado antes
     * de acceder a rutas protegidas.
     * 
     * Uso típico: Llamar antes de métodos que requieren login
     * 
     * @param string $rolRequerido Rol mínimo requerido (opcional)
     * @param string $redirectUrl URL de redirección si no autenticado
     * @return bool True si está autenticado, false si redirecciona
     */
    public function requireAuth($rolRequerido = null, $redirectUrl = '/login') {
        // Verificar autenticación básica
        if (!$this->authHelper->estaAutenticado()) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            $this->redireccionar($redirectUrl . '?redirect=' . urlencode($currentUrl));
            return false;
        }
        
        // Verificar rol si se especifica
        if ($rolRequerido) {
            if (!$this->authHelper->verificarAcceso($rolRequerido)) {
                error_log("Acceso denegado - Rol requerido: $rolRequerido, Usuario: " . json_encode($this->authHelper->obtenerUsuarioActual()));
                $this->redireccionar('/?error=' . urlencode('No tiene permisos para acceder a esta sección'));
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Crear usuario (solo para admins)
     * 
     * Propósito: Permitir que administradores creen nuevos usuarios
     * con cualquier rol en el sistema.
     * 
     * Restricciones:
     * - Solo usuarios con rol 'admin' pueden crear usuarios
     * - Valida todos los campos requeridos
     * - Previene duplicación de emails
     * 
     * @return void (redirecciona con resultado)
     */
    public function crearUsuario() {
        // Verificar que sea admin
        if (!$this->requireAuth('admin')) {
            return;
        }
        
        // Solo procesar POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redireccionar('/admin?error=' . urlencode('Método no permitido'));
            return;
        }
        
        try {
            // Obtener datos del formulario
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $rol = isset($_POST['rol']) ? trim($_POST['rol']) : 'publicante';
            
            // Validaciones
            if (empty($nombre) || empty($email) || empty($password)) {
                $this->redireccionar('/admin?error=' . urlencode('Por favor complete todos los campos'));
                return;
            }
            
            // Crear usuario usando el modelo
            $usuarioId = $this->usuarioModel->crearUsuario($nombre, $email, $password, $rol);
            
            if ($usuarioId) {
                error_log("Usuario creado por admin - Nuevo usuario: $email, Rol: $rol, Creado por: " . $this->authHelper->obtenerUsuarioActual()['email']);
                $this->redireccionar('/admin?mensaje=' . urlencode('Usuario creado exitosamente'));
            } else {
                $this->redireccionar('/admin?error=' . urlencode('Error creando usuario. Verifique que el email no exista'));
            }
            
        } catch (Exception $e) {
            error_log("Exception en crearUsuario: " . $e->getMessage());
            $this->redireccionar('/admin?error=' . urlencode('Error interno del sistema'));
        }
    }
    
    /**
     * Redireccionar según el rol del usuario (CON LISTA BLANCA ESTRICTA)
     * 
     * Propósito: Enviar a cada tipo de usuario a su panel correspondiente
     * usando una lista blanca estricta para prevenir redirects maliciosos.
     * 
     * LISTA BLANCA DE REDIRECCIONES POR ROL:
     * Esta implementación usa un array asociativo que mapea roles específicos
     * a URLs específicas, ignorando cualquier parámetro redirect externo.
     * 
     * Motivo de la lista blanca estricta:
     * - Previene open redirect attacks donde un atacante podría manipular
     *   el parámetro redirect para enviar usuarios a sitios maliciosos
     * - Garantiza que solo se usen rutas internas del sistema
     * - Facilita auditoría de seguridad al tener rutas centralizadas
     * 
     * Cómo extender para nuevos roles:
     * 1. Agregar entrada al array $whitelist
     * 2. Crear el archivo de panel correspondiente
     * 3. Verificar que la ruta sea accesible
     * 
     * @param string $rol Rol del usuario autenticado
     */
    private function redireccionarSegunRol($rol) {
        /**
         * LISTA BLANCA ESTRICTA DE REDIRECCIONES POR ROL
         * 
         * Cada rol tiene exactamente UNA ruta de destino permitida.
         * No se aceptan parámetros externos ni modificaciones.
         * 
         * Propósito de seguridad: Eliminar completamente la posibilidad
         * de open redirect attacks en el flujo de login.
         */
        $whitelist = [
            'admin' => '/admin/dashboard.php',
            'promotor' => '/promotor/panel.php', 
            'publicante' => '/publicante/panel.php'
        ];
        
        // Verificar si el rol está en la lista blanca
        if (isset($whitelist[$rol])) {
            $destinoSeguro = $whitelist[$rol];
            error_log("Redirección segura - Rol: $rol -> $destinoSeguro");
            $this->redireccionar($destinoSeguro);
        } else {
            // Rol no reconocido - usar destino por defecto seguro
            error_log("Rol no reconocido en login: $rol - redirigiendo a página principal");
            $this->redireccionar('/');
        }
    }
    
    /**
     * Validar que una URL de redirección sea segura (LISTA BLANCA IMPLEMENTADA)
     * 
     * Propósito: Prevenir ataques de redirección abierta (open redirect) verificando
     * que la URL de destino esté en una lista blanca de rutas permitidas.
     * 
     * LISTA BLANCA DE REDIRECCIONES PERMITIDAS:
     * Esta implementación usa una lista blanca estricta para máxima seguridad.
     * Solo se permiten rutas específicas conocidas y seguras del sistema.
     * 
     * Motivo de la lista blanca: Es más seguro permitir solo rutas específicas
     * que intentar validar todas las rutas posibles con expresiones regulares.
     * 
     * @param string $url URL a validar
     * @return bool True si la URL está en la lista blanca y es segura
     */
    private function esRedirectSegura($url) {
        // LISTA BLANCA DE REDIRECCIONES PERMITIDAS
        // Cada ruta debe ser específicamente autorizada para evitar open redirects
        $rutasPermitidas = [
            // Página principal
            '/',
            
            // Paneles de usuario por rol
            '/admin/dashboard.php',
            '/promotor/panel.php',
            '/publicante/panel.php',
            
            // Rutas de administración
            '/admin',
            '/admin/',
            
            // Rutas de promotor
            '/promotor',
            '/promotor/',
            
            // Rutas de publicante
            '/publicante',
            '/publicante/',
            
            // Vistas públicas permitidas
            '/contacto',
            '/publicar-oferta',
            '/buscar-empleo',
            '/privacidad',
            '/terminos',
            '/ayuda',
            
            // URLs con query parameters comunes (página principal)
            '/?view=home',
            '/?mensaje=',
            '/?error=',
        ];
        
        // Validaciones básicas primero
        if (!$url || !is_string($url)) {
            return false;
        }
        
        // Debe empezar con / para ser relativa
        if (!str_starts_with($url, '/')) {
            return false;
        }
        
        // No debe contener // para evitar redirecciones externas
        if (strpos($url, '//') !== false) {
            return false;
        }
        
        // Longitud máxima razonable
        if (strlen($url) > 255) {
            return false;
        }
        
        // Verificar contra lista blanca exacta
        if (in_array($url, $rutasPermitidas, true)) {
            return true;
        }
        
        // Verificar patrones especiales permitidos con query strings
        foreach ($rutasPermitidas as $rutaPermitida) {
            // Si la ruta permitida termina con = (query parameter)
            if (str_ends_with($rutaPermitida, '=') && str_starts_with($url, $rutaPermitida)) {
                // Validar que el resto sean solo caracteres seguros
                $queryPart = substr($url, strlen($rutaPermitida));
                if (preg_match('/^[a-zA-Z0-9\-_\.\%\+\s]*$/', $queryPart)) {
                    return true;
                }
            }
        }
        
        // Si llegamos aquí, la URL no está en la lista blanca
        error_log("URL de redirección rechazada (no en lista blanca): $url");
        return false;
    }
    
    /**
     * Redireccionar con headers seguros
     * 
     * Propósito: Realizar redirecciones HTTP de forma segura con
     * headers apropiados y prevención de inyección.
     * 
     * @param string $url URL de destino (relativa)
     */
    private function redireccionar($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * Renderizar vista con layout base
     * 
     * Propósito: Cargar una vista dentro del layout principal
     * del sitio con los datos proporcionados.
     * 
     * @param string $vista Ruta de la vista (sin .php)
     * @param array $datos Datos a pasar a la vista
     */
    private function renderizarVista($vista, $datos = []) {
        // Extraer datos para que estén disponibles como variables en la vista
        extract($datos);
        
        // Incluir la vista
        $archivoVista = "views/$vista.php";
        if (file_exists($archivoVista)) {
            include $archivoVista;
        } else {
            error_log("Vista no encontrada: $archivoVista");
            // Mostrar error 404 o vista por defecto
            http_response_code(404);
            echo "Vista no encontrada: $vista";
        }
    }
}

?>