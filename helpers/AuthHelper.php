<?php
/**
 * AuthHelper.php - Helper de Autenticación y Control de Acceso
 * 
 * Propósito:
 * Este archivo contiene funciones globales para el manejo de autenticación,
 * sesiones y control de acceso basado en roles de usuario.
 * 
 * Roles soportados:
 * - admin: Acceso completo al sistema, panel de administración
 * - promotor: Usuarios que pueden promover ofertas y gestionar contenido
 * - publicante: Usuarios que pueden crear y gestionar sus propias ofertas
 * 
 * Decisiones de diseño:
 * - Sesiones PHP nativas para simplicidad y compatibilidad
 * - Validación de roles mediante array para facilidad de extensión
 * - Redirección automática a login.php en caso de acceso no autorizado
 * 
 * Supuestos del sistema:
 * - Tabla 'usuarios' existe con campos: id, nombre, rol, email, password
 * - Las sesiones están iniciadas antes de llamar estas funciones
 * - Los roles se almacenan como strings en la base de datos
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-07
 */

/**
 * Inicializar sesión de forma segura
 * 
 * Propósito: Iniciar sesión PHP con configuraciones de seguridad
 * y verificar que no haya conflictos de sesiones activas.
 * 
 * Efectos colaterales:
 * - Inicia session_start() si no está activa
 * - Configura parámetros de seguridad de sesión
 * 
 * Notas de mantenimiento:
 * - Esta función debe llamarse antes de cualquier operación con $_SESSION
 * - Los parámetros de seguridad pueden ajustarse según requirements del servidor
 * 
 * @return void
 */
function iniciarSesionSegura() {
    // Verificar si la sesión ya está iniciada para evitar warnings
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar parámetros de seguridad de sesión antes de iniciar
        ini_set('session.cookie_httponly', 1); // Prevenir acceso XSS a cookies de sesión
        ini_set('session.use_only_cookies', 1); // Solo usar cookies para ID de sesión
        ini_set('session.cookie_secure', 0);    // Cambiar a 1 cuando se implemente HTTPS completo
        
        session_start();
    }
}

/**
 * Verificar acceso basado en roles de usuario
 * 
 * Propósito: Control de acceso granular que permite proteger vistas y funcionalidades
 * según el rol del usuario autenticado. Redirige automáticamente si no tiene permisos.
 * 
 * Flujo de validación:
 * 1. Verifica que existe sesión activa con usuario_id
 * 2. Verifica que el rol del usuario está en la lista de roles permitidos
 * 3. Si falla cualquier validación, redirige a login
 * 
 * Casos de uso:
 * - verificarAcceso(['admin']) -> Solo administradores
 * - verificarAcceso(['admin', 'promotor']) -> Administradores y promotores
 * - verificarAcceso(['admin', 'promotor', 'publicante']) -> Todos los usuarios autenticados
 * 
 * Notas de extensión:
 * - Para agregar nuevos roles, simplemente incluirlos en $rolesPermitidos
 * - La tabla usuarios debe mantener consistencia en los valores del campo 'rol'
 * - Considerar crear constantes para los roles si el sistema crece
 * 
 * @param array $rolesPermitidos Array de strings con los roles que pueden acceder
 * @return void Redirige a login.php si no tiene acceso, continúa ejecución si sí tiene
 */
function verificarAcceso($rolesPermitidos) {
    // Asegurar que la sesión esté iniciada antes de verificar
    iniciarSesionSegura();
    
    // Validación 1: Verificar que existe una sesión de usuario activa
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        // Usuario no autenticado - redirigir a login
        header('Location: index.php?view=login');
        exit;
    }
    
    // Validación 2: Verificar que el usuario tiene un rol válido en la sesión
    if (!isset($_SESSION['rol']) || empty($_SESSION['rol'])) {
        // Rol no definido o corrupto - cerrar sesión y redirigir
        cerrarSesion();
        header('Location: index.php?view=login');
        exit;
    }
    
    // Validación 3: Verificar que el rol del usuario está en la lista de permitidos
    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        // Acceso denegado - redirigir con mensaje de error
        $_SESSION['mensaje_error'] = 'No tienes permisos para acceder a esta sección';
        header('Location: index.php?view=login');
        exit;
    }
    
    // Si llegamos aquí, el acceso está autorizado
    // Continuar con la ejecución normal
}

/**
 * Verificar si un usuario está autenticado
 * 
 * Propósito: Función de conveniencia para verificar rápidamente si existe
 * una sesión de usuario válida sin forzar redirecciones.
 * 
 * Uso típico: Mostrar/ocultar elementos UI según estado de autenticación
 * 
 * @return bool True si el usuario está autenticado, false en caso contrario
 */
function estaAutenticado() {
    iniciarSesionSegura();
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']) && 
           isset($_SESSION['rol']) && !empty($_SESSION['rol']);
}

/**
 * Obtener información del usuario autenticado
 * 
 * Propósito: Proporcionar acceso seguro a los datos del usuario en sesión
 * para uso en vistas y lógica de negocio.
 * 
 * @return array|null Array con datos del usuario o null si no está autenticado
 */
function obtenerUsuarioActual() {
    if (!estaAutenticado()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['nombre'] ?? '',
        'rol' => $_SESSION['rol'],
        'email' => $_SESSION['email'] ?? ''
    ];
}

/**
 * Cerrar sesión de usuario de forma segura
 * 
 * Propósito: Limpiar completamente la sesión del usuario y datos relacionados
 * de forma segura para prevenir session hijacking.
 * 
 * Proceso de limpieza:
 * 1. Limpiar variables de sesión específicas del usuario
 * 2. Destruir la sesión PHP
 * 3. Regenerar ID de sesión para seguridad
 * 
 * Efectos colaterales:
 * - Todas las variables $_SESSION relacionadas con el usuario se eliminan
 * - La sesión PHP se destruye completamente
 * 
 * @return void
 */
function cerrarSesion() {
    iniciarSesionSegura();
    
    // Limpiar variables específicas del usuario
    unset($_SESSION['usuario_id']);
    unset($_SESSION['nombre']);
    unset($_SESSION['rol']);
    unset($_SESSION['email']);
    
    // Destruir toda la sesión
    session_destroy();
    
    // Regenerar ID de sesión por seguridad
    session_start();
    session_regenerate_id(true);
}

/**
 * Verificar si el usuario tiene un rol específico
 * 
 * Propósito: Función de conveniencia para verificar roles específicos
 * sin forzar redirecciones, útil para lógica condicional.
 * 
 * @param string $rol Rol a verificar
 * @return bool True si el usuario tiene el rol especificado
 */
function tieneRol($rol) {
    return estaAutenticado() && $_SESSION['rol'] === $rol;
}

/**
 * Verificar si el usuario es administrador
 * 
 * @return bool True si el usuario es administrador
 */
function esAdmin() {
    return tieneRol('admin');
}

/**
 * Verificar si el usuario es promotor
 * 
 * @return bool True si el usuario es promotor
 */
function esPromotor() {
    return tieneRol('promotor');
}

/**
 * Verificar si el usuario es publicante
 * 
 * @return bool True si el usuario es publicante
 */
function esPublicante() {
    return tieneRol('publicante');
}

?>