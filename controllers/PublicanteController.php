<?php
/**
 * PublicanteController.php - Controlador para usuarios Publicante
 * 
 * Propósito: 
 * Gestiona las funcionalidades específicas para usuarios con rol 'publicante'
 * (usuarios regulares que publican ofertas de trabajo) y administradores.
 * 
 * PROTECCIÓN POR ROL: Solo usuarios 'admin' y 'publicante' pueden acceder
 * 
 * Responsabilidades:
 * - Panel de control para publicantes
 * - Gestión de ofertas de trabajo publicadas
 * - Estadísticas de publicaciones
 * - Herramientas de promoción básica
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require_once __DIR__ . '/../helpers/AuthHelper.php';

/**
 * Función auxiliar para verificar acceso de publicante
 * 
 * Propósito: Verificar que el usuario tenga rol 'publicante' o 'admin'
 * Los administradores pueden acceder a todas las funciones de publicante
 * para supervisión y soporte técnico.
 * 
 * Nota importante: Los publicantes son usuarios regulares que pueden crear
 * ofertas de trabajo. Es el rol más básico después del acceso anónimo.
 * 
 * @param array $rolesPermitidos Lista de roles permitidos ['admin', 'publicante']
 * @throws void Redirecciona si no tiene permisos, no retorna si falla
 */
function verificarAccesoPublicante($rolesPermitidos = ['admin', 'publicante']) {
    $authHelper = new AuthHelper();
    
    // Verificar si está autenticado
    if (!$authHelper->estaAutenticado()) {
        // Redirigir a login con la URL actual para regresar después
        header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    
    // Verificar si tiene el rol adecuado (admin o publicante)
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
        error_log("Acceso denegado a publicante - Usuario: {$usuario['email']}, Rol: {$usuario['rol']}, Intentó: " . $_SERVER['REQUEST_URI']);
        
        // Redirigir a página principal con mensaje de error
        header('Location: /?error=' . urlencode('No tiene permisos para acceder a esta sección'));
        exit;
    }
}

class PublicanteController {
    
    /**
     * Constructor del PublicanteController
     */
    public function __construct() {
        // Por ahora no necesita inicialización especial
    }
    
    /**
     * Panel principal de publicante
     * 
     * Propósito: Mostrar el dashboard principal para usuarios publicante
     * con herramientas para gestionar sus ofertas de trabajo.
     * 
     * PROTECCIÓN: Solo usuarios 'admin' y 'publicante' pueden acceder
     */
    public function index() {
        // Verificar permisos de publicante antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Obtener información del usuario actual para personalización
        $authHelper = new AuthHelper();
        $usuarioActual = $authHelper->obtenerUsuarioActual();
        
        // Variables para la vista
        $pageTitle = "Panel de Publicante";
        $data = [
            'usuario' => $usuarioActual,
            'mensaje' => 'Bienvenido al panel de PUBLICANTE'
        ];
        
        // Cargar vista del panel de publicante
        include 'publicante/panel.php';
    }
    
    /**
     * Gestión de ofertas de trabajo
     * 
     * TODO: Implementar funcionalidades de:
     * - Crear nuevas ofertas de trabajo
     * - Editar ofertas existentes
     * - Ver candidatos aplicados
     * - Gestionar estado de ofertas (activa/inactiva)
     */
    public function ofertas() {
        // Verificar permisos antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Por ahora redirigir al panel principal
        header('Location: /publicante');
        exit;
    }
    
    /**
     * Ver candidatos y aplicaciones
     * 
     * TODO: Implementar:
     * - Lista de candidatos por oferta
     * - Filtros y búsqueda de candidatos
     * - Sistema de calificación
     * - Comunicación con candidatos
     */
    public function candidatos() {
        // Verificar permisos antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Por ahora redirigir al panel principal
        header('Location: /publicante');
        exit;
    }
    
    /**
     * Estadísticas de publicaciones
     * 
     * TODO: Implementar dashboard con:
     * - Número de vistas por oferta
     * - Cantidad de aplicaciones recibidas
     * - Tasa de conversión
     * - Rendimiento de ofertas
     */
    public function estadisticas() {
        // Verificar permisos antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Por ahora redirigir al panel principal
        header('Location: /publicante');
        exit;
    }
    
    /**
     * Configuración del perfil de publicante
     * 
     * TODO: Implementar:
     * - Datos de la empresa/empleador
     * - Información de contacto
     * - Preferencias de notificación
     * - Configuración de privacidad
     */
    public function configuracion() {
        // Verificar permisos antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Por ahora redirigir al panel principal
        header('Location: /publicante');
        exit;
    }
    
    /**
     * Promocionar ofertas
     * 
     * Propósito: Permitir a publicantes acceder a herramientas básicas de promoción
     * como destacar ofertas o aumentar su visibilidad.
     * 
     * TODO: Implementar:
     * - Opciones de promoción disponibles
     * - Precios y paquetes
     * - Integración con sistema de pagos
     * - Estadísticas de promoción
     */
    public function promocionar() {
        // Verificar permisos antes de proceder
        verificarAccesoPublicante(['admin', 'publicante']);
        
        // Por ahora redirigir al panel principal
        header('Location: /publicante');
        exit;
    }
}

/**
 * DOCUMENTACIÓN PARA DESARROLLADORES:
 * 
 * El rol 'publicante' es el rol por defecto para usuarios regulares.
 * Representa empresas, reclutadores o personas que buscan contratar talento.
 * 
 * Jerarquía de roles (de mayor a menor privilegio):
 * 1. admin - Acceso total al sistema
 * 2. promotor - Gestión de campañas y promociones
 * 3. publicante - Gestión de ofertas de trabajo (rol básico)
 * 
 * Motivo de la jerarquía:
 * - Admin puede hacer todo (supervisión total)
 * - Promotor maneja aspectos comerciales y marketing
 * - Publicante es el usuario final básico del sistema
 * 
 * Lista blanca de redirecciones para este controlador:
 * - /publicante (panel principal)
 * - /publicante/ofertas (gestión de ofertas)
 * - /publicante/candidatos (ver aplicaciones)
 * - /publicante/estadisticas (métricas)
 * - /publicante/configuracion (perfil)
 * - /publicante/promocionar (herramientas de promoción)
 * 
 * Todas las redirecciones fuera de estos paths deben ser validadas
 * para prevenir open redirect attacks.
 */

?>