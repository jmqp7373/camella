<?php
/**
 * bootstrap.php — Punto único para iniciar sesión y cargar helpers
 * 
 * Propósito: Evitar "headers already sent", includes rotos y carga duplicada
 * centralizando la inicialización de sesión y carga de helpers críticos.
 * 
 * Problemas que resuelve:
 * - Headers already sent por múltiples session_start()
 * - Includes duplicados de AuthHelper.php
 * - Rutas relativas inconsistentes entre controladores
 * - Falta de control centralizado de dependencias
 * 
 * Efectos: 
 * - Inicia sesión si no está iniciada
 * - Carga AuthHelper una sola vez de forma segura
 * - Proporciona base sólida para todos los controladores
 * 
 * Cómo revertir si fuera necesario:
 * 1. Eliminar require_once bootstrap.php de index.php
 * 2. Restaurar session_start() en cada controlador
 * 3. Restaurar require_once AuthHelper.php en cada archivo
 * 
 * @author Camella Development Team - Hotfix
 * @version 1.0
 * @date 2025-10-08
 */

/**
 * INICIALIZACIÓN SEGURA DE SESIÓN
 * 
 * Verifica el estado actual de la sesión antes de iniciarla
 * para evitar errores de "headers already sent".
 */
if (session_status() === PHP_SESSION_NONE) {
    // Línea clave: Verificar si los headers ya fueron enviados
    if (headers_sent($file, $line)) {
        // Comentario crítico: Si llegamos aquí, hay salida previa problemática
        error_log("[BOOTSTRAP ERROR] Headers ya enviados desde $file línea $line - no se puede iniciar sesión");
        
        // No detener ejecución aquí ya que algunos casos podrían funcionar sin sesión
        // pero registrar el problema para debugging
    } else {
        // Sesión segura: headers no enviados, podemos iniciar
        session_start();
        error_log("[BOOTSTRAP] Sesión iniciada correctamente en " . date('Y-m-d H:i:s'));
    }
} else {
    // Sesión ya estaba activa, no hay problema
    error_log("[BOOTSTRAP] Sesión ya estaba activa (status: " . session_status() . ")");
}

/**
 * INICIALIZACIÓN DEL MANEJADOR DE ERRORES GLOBAL
 * 
 * Propósito: Activar el sistema de manejo controlado de errores
 * después de inicializar la sesión pero antes de cargar dependencias.
 * 
 * Se carga aquí para:
 * - Interceptar errores en AuthHelper y otras dependencias
 * - Tener control completo sobre respuestas de error
 * - Registrar problemas técnicos sin exponer detalles al usuario
 * 
 * Orden crítico: Después de session_start(), antes de includes
 */
require_once __DIR__ . '/errors/handler.php';

/**
 * CARGA SEGURA DE AUTHHELPER
 * 
 * Incluye AuthHelper de forma centralizada con verificación de existencia
 * y manejo de errores controlado.
 */
$helperPath = __DIR__ . '/helpers/AuthHelper.php';

// Línea clave: Verificar existencia antes de incluir
if (file_exists($helperPath)) {
    // Verificar si ya fue incluido para evitar redefinición
    if (!class_exists('AuthHelper')) {
        require_once $helperPath;
        error_log("[BOOTSTRAP] AuthHelper cargado exitosamente");
    } else {
        error_log("[BOOTSTRAP] AuthHelper ya estaba cargado");
    }
} else {
    // Comentario crítico: Si no existe AuthHelper, el sistema no puede funcionar
    error_log("[BOOTSTRAP FATAL] No se encontró helpers/AuthHelper.php en: $helperPath");
    
    // Detener aquí con mensaje controlado para evitar errores fatales posteriores
    http_response_code(500);
    
    // En desarrollo mostrar mensaje detallado, en producción genérico
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        exit('Bootstrap error: AuthHelper no encontrado en ' . $helperPath);
    } else {
        exit('Error interno del sistema. Contacte al administrador.');
    }
}

/**
 * CARGA DE DEPENDENCIAS ADICIONALES (FUTURAS)
 * 
 * Cómo agregar nuevos helpers en el futuro:
 * 
 * 1. Definir ruta del helper:
 *    $nuevoHelperPath = __DIR__ . '/helpers/NuevoHelper.php';
 * 
 * 2. Verificar existencia y cargar:
 *    if (file_exists($nuevoHelperPath) && !class_exists('NuevoHelper')) {
 *        require_once $nuevoHelperPath;
 *        error_log("[BOOTSTRAP] NuevoHelper cargado");
 *    }
 * 
 * 3. Manejar errores si es crítico:
 *    if (!class_exists('NuevoHelper')) {
 *        error_log("[BOOTSTRAP ERROR] NuevoHelper crítico no disponible");
 *    }
 */

/**
 * CONFIGURACIÓN GLOBAL (SI ES NECESARIA)
 * 
 * Aquí se puede agregar configuración global que necesiten todos los controladores
 */

// Definir zona horaria si no está definida
if (!ini_get('date.timezone')) {
    date_default_timezone_set('America/Bogota');
}

// Configurar charset para evitar problemas de codificación
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

/**
 * LOGGING DE BOOTSTRAP COMPLETADO
 */
error_log("[BOOTSTRAP] Inicialización completada - Sesión: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVA' : 'INACTIVA') . 
          " - AuthHelper: " . (class_exists('AuthHelper') ? 'CARGADO' : 'NO DISPONIBLE'));

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * REGLAS IMPORTANTES:
 * 
 * 1. NUNCA iniciar sesión en múltiples archivos
 *    - Usar solo este bootstrap.php para session_start()
 *    - Todos los demás archivos deben confiar en que ya está iniciada
 * 
 * 2. NUNCA incluir AuthHelper directamente en controladores
 *    - Este bootstrap ya lo carga una vez
 *    - Los controladores solo deben usar new AuthHelper()
 * 
 * 3. EVITAR salida antes de session_start()
 *    - No echo, print, o espacios en blanco antes de bootstrap
 *    - No includes de vistas que generen HTML antes de bootstrap
 * 
 * 4. RUTAS RELATIVAS consistentes
 *    - Este archivo está en la raíz, usar __DIR__ como referencia
 *    - Para controladores en subdirectorios: dirname(__DIR__) si es necesario
 * 
 * 5. MANEJO DE ERRORES centralizado
 *    - Usar error_log() para debugging
 *    - Mensajes de error controlados para usuarios
 *    - No mostrar rutas del sistema en producción
 */

?>