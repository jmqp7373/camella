<?php
/**
 * PromotorController.php - Controlador para usuarios Promotor
 * 
 * Propósito: 
 * Gestiona las funcionalidades específicas para usuarios con rol 'promotor'
 * y administradores que pueden acceder a estas funciones.
 * 
 * PROTECCIÓN POR ROL: Solo usuarios 'admin' y 'promotor' pueden acceder
 * 
 * Responsabilidades:
 * - Panel de control para promotores
 * - Gestión de promociones y campañas
 * - Estadísticas de promoción
 * - Herramientas específicas de marketing
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require_once __DIR__ . '/../helpers/AuthHelper.php';

/**
 * Función auxiliar para verificar acceso de promotor
 * 
 * Propósito: Verificar que el usuario tenga rol 'promotor' o 'admin'
 * Los administradores pueden acceder a todas las funciones de promotor
 * para supervisión y soporte.
 * 
 * @param array $rolesPermitidos Lista de roles permitidos ['admin', 'promotor']
 * @throws void Redirecciona si no tiene permisos, no retorna si falla
 */
function verificarAccesoPromotor($rolesPermitidos = ['admin', 'promotor']) {
    $authHelper = new AuthHelper();
    
    // Verificar si está autenticado
    if (!$authHelper->estaAutenticado()) {
        // Redirigir a login con la URL actual para regresar después
        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    // Verificar si tiene el rol adecuado (admin o promotor)
    $tieneAcceso = false;
    foreach ($rolesPermitidos as $rol) {
        if ($authHelper->verificarAcceso($rol)) {
            $tieneAcceso = true;
            break;
        }
    }
    
    if (!$tieneAcceso) {
        // Log del intento de acceso no autorizado
        $usuario = $authHelper->obtenerUsuarioActual();
        error_log("Acceso denegado a promotor - Usuario: {$usuario['email']}, Rol: {$usuario['rol']}, Intentó: " . $_SERVER['REQUEST_URI']);
        
        // Redirigir a página principal con mensaje de error
        header('Location: /?error=' . urlencode('No tiene permisos para acceder a esta sección'));
        exit;
    }
}

class PromotorController {
    
    /**
     * Constructor del PromotorController
     */
    public function __construct() {
        // Por ahora no necesita inicialización especial
    }
    
    /**
     * Panel principal de promotor
     * 
     * Propósito: Mostrar el dashboard principal para usuarios promotor
     * con herramientas y estadísticas específicas de promoción.
     * 
     * PROTECCIÓN: Solo usuarios 'admin' y 'promotor' pueden acceder
     */
    public function index() {
        // Verificar permisos de promotor antes de proceder
        verificarAccesoPromotor(['admin', 'promotor']);
        
        // Obtener información del usuario actual para personalización
        $authHelper = new AuthHelper();
        $usuarioActual = $authHelper->obtenerUsuarioActual();
        
        // Variables para la vista
        $pageTitle = "Panel de Promotor";
        $data = [
            'usuario' => $usuarioActual,
            'mensaje' => 'Bienvenido al panel de PROMOTOR'
        ];
        
        // Cargar vista del panel de promotor
        include 'promotor/panel.php';
    }
    
    /**
     * Gestión de campañas promocionales
     * 
     * TODO: Implementar funcionalidades de:
     * - Crear campañas promocionales
     * - Gestionar anuncios destacados
     * - Ver estadísticas de promoción
     * - Configurar precios y ofertas especiales
     */
    public function campanas() {
        // Verificar permisos antes de proceder
        verificarAccesoPromotor(['admin', 'promotor']);
        
        // Por ahora redirigir al panel principal
        header('Location: /promotor');
        exit;
    }
    
    /**
     * Estadísticas de promoción
     * 
     * TODO: Implementar dashboard con:
     * - Métricas de rendimiento de anuncios
     * - Gráficos de conversión
     * - ROI de campañas
     * - Análisis de audiencia
     */
    public function estadisticas() {
        // Verificar permisos antes de proceder
        verificarAccesoPromotor(['admin', 'promotor']);
        
        // Por ahora redirigir al panel principal
        header('Location: /promotor');
        exit;
    }
    
    /**
     * Configuración del perfil de promotor
     * 
     * TODO: Implementar:
     * - Configuración de comisiones
     * - Datos bancarios para pagos
     * - Preferencias de notificación
     * - Configuración de territorio/zona
     */
    public function configuracion() {
        // Verificar permisos antes de proceder
        verificarAccesoPromotor(['admin', 'promotor']);
        
        // Por ahora redirigir al panel principal
        header('Location: /promotor');
        exit;
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * Cómo agregar un nuevo rol en el futuro:
 * 
 * 1. ACTUALIZAR ENUM en modelo Usuario.php:
 *    - Modificar la definición de la tabla para incluir el nuevo rol
 *    - Ejemplo: ENUM('admin', 'promotor', 'publicante', 'nuevo_rol')
 * 
 * 2. AJUSTAR verificarAcceso en AuthHelper.php:
 *    - Asegurar que el nuevo rol esté contemplado en la lógica
 *    - Definir jerarquía si es necesario (ej: admin > promotor > publicante)
 * 
 * 3. CREAR CONTROLADOR específico:
 *    - Copiar este archivo como plantilla
 *    - Cambiar nombres de función de verificarAccesoPromotor a verificarAccesoNuevoRol
 *    - Ajustar roles permitidos en la función
 * 
 * 4. CREAR PANEL Y RUTAS:
 *    - Crear directorio nuevo_rol/
 *    - Crear panel.php con contenido básico
 *    - Actualizar index.php para manejar la nueva ruta
 * 
 * 5. ACTUALIZAR REDIRECCIÓN en LoginController.php:
 *    - Añadir case para el nuevo rol en redireccionarSegunRol()
 * 
 * Ejemplo de implementación para rol 'supervisor':
 * - verificarAccesoSupervisor(['admin', 'supervisor'])
 * - supervisor/panel.php
 * - Route: /supervisor
 * - Botón en header para usuarios supervisor
 */

?>