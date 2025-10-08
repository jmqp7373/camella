<?php
/**
 * errors/handler.php — Manejo controlado de errores/fatales en producción
 * 
 * Propósito: Interceptar errores fatales, excepciones no manejadas y errores
 * de PHP para mostrar mensajes controlados al usuario y registrar detalles
 * técnicos en el error_log para debugging.
 * 
 * Comportamiento:
 * - Usuarios ven mensaje genérico y limpio (sin exponer rutas del sistema)
 * - Desarrolladores tienen logging detallado en error_log
 * - Se mantiene la maquetación existente (no se cargan vistas complejas)
 * - Response codes HTTP apropiados para SEO y herramientas
 * 
 * Cómo desactivar para debugging local:
 * 1. Comentar require_once de este archivo en bootstrap.php
 * 2. O definir ENVIRONMENT=development antes de incluirlo
 * 3. O usar ini_set('display_errors', 1) en desarrollo
 * 
 * @author Camella Development Team - Security Hardening
 * @version 1.0
 * @date 2025-10-08
 */

/**
 * MANEJADOR DE EXCEPCIONES NO CAPTURADAS
 * 
 * Propósito: Interceptar excepciones que no fueron manejadas por try/catch
 * en el código de la aplicación.
 * 
 * Flujo:
 * 1. Registrar detalles completos en error_log
 * 2. Mostrar mensaje genérico al usuario
 * 3. Enviar HTTP 500 para indicar error del servidor
 * 4. Terminar ejecución para evitar outputs adicionales
 * 
 * @param Exception $exception La excepción no manejada
 * @return void Termina la ejecución
 */
set_exception_handler(function($exception) {
    // Log detallado para desarrolladores
    error_log("[EXCEPTION HANDLER] " . get_class($exception) . ": {$exception->getMessage()} @ {$exception->getFile()}:{$exception->getLine()}");
    error_log("[EXCEPTION TRACE] " . $exception->getTraceAsString());
    
    // Limpiar output buffer si existe para evitar contenido parcial
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Respuesta HTTP apropiada
    http_response_code(500);
    
    // Headers para evitar cache de páginas de error
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Mensaje controlado para usuarios (sin exponer detalles técnicos)
    exit('Ha ocurrido un error interno. Por favor intenta nuevamente.');
});

/**
 * MANEJADOR DE ERRORES DE PHP
 * 
 * Propósito: Interceptar errores de PHP (warnings, notices, errors)
 * y convertirlos en respuestas controladas.
 * 
 * Parámetros del error handler estándar de PHP:
 * @param int $severity Nivel de severidad del error
 * @param string $message Mensaje del error
 * @param string $file Archivo donde ocurrió el error
 * @param int $line Línea donde ocurrió el error
 * @return bool True para prevenir el handler por defecto de PHP
 */
set_error_handler(function($severity, $message, $file, $line) {
    // Mapear severidades a texto legible
    $severityNames = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING', 
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE'
    ];
    
    $severityName = $severityNames[$severity] ?? 'UNKNOWN';
    
    // Log del error para desarrolladores
    error_log("[ERROR HANDLER] {$severityName}: {$message} @ {$file}:{$line}");
    
    // Solo terminar ejecución en errores críticos
    if (in_array($severity, [E_ERROR, E_CORE_ERROR, E_USER_ERROR, E_PARSE])) {
        // Limpiar output si existe
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code(500);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        exit('Ha ocurrido un error. Por favor intenta nuevamente.');
    }
    
    // Para warnings y notices, continuar ejecución pero logear
    return true; // Prevenir el handler por defecto de PHP
});

/**
 * MANEJADOR DE ERRORES FATALES
 * 
 * Propósito: Interceptar errores fatales que ocurren durante la ejecución
 * y que normalmente terminarían el script sin control.
 * 
 * Se ejecuta al final del script (shutdown) para verificar si hubo
 * algún error fatal no manejado.
 * 
 * Tipos de errores fatales que maneja:
 * - E_ERROR: Errores fatales de runtime
 * - E_PARSE: Errores de sintaxis de PHP
 * - E_CORE_ERROR: Errores fatales del núcleo de PHP
 * - E_COMPILE_ERROR: Errores de compilación
 * 
 * @return void
 */
register_shutdown_function(function() {
    // Obtener el último error que ocurrió
    $lastError = error_get_last();
    
    // Verificar si fue un error fatal
    if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log detallado del error fatal
        error_log("[FATAL ERROR HANDLER] {$lastError['message']} @ {$lastError['file']}:{$lastError['line']}");
        
        // Si no se han enviado headers aún, enviar respuesta controlada
        if (!headers_sent()) {
            http_response_code(500);
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Content-Type: text/html; charset=utf-8');
            
            // Mensaje limpio para usuarios
            echo 'Ha ocurrido un error fatal. Por favor intenta nuevamente.';
        }
    }
});

/**
 * LOGGING DE INICIALIZACIÓN DEL HANDLER
 * 
 * Registrar que el sistema de manejo de errores está activo
 * para confirmar en logs que las funciones están funcionando.
 */
error_log("[ERROR HANDLER] Sistema de manejo de errores inicializado en " . date('Y-m-d H:i:s'));

/**
 * NOTAS PARA DESARROLLADORES:
 * 
 * CUÁNDO SE ACTIVA CADA HANDLER:
 * 
 * 1. set_exception_handler():
 *    - Excepciones lanzadas con 'throw' que no son capturadas
 *    - Errores de conexión a BD sin try/catch
 *    - Llamadas a métodos inexistentes
 * 
 * 2. set_error_handler(): 
 *    - include/require de archivos inexistentes
 *    - Divisiones por cero
 *    - Variables indefinidas (notices)
 *    - Funciones deprecated (warnings)
 * 
 * 3. register_shutdown_function():
 *    - Errores de sintaxis PHP
 *    - Memory limit exceeded 
 *    - Maximum execution time exceeded
 *    - Parse errors en archivos incluidos
 * 
 * DEBUGGING EN DESARROLLO:
 * 
 * Para ver errores completos en desarrollo local:
 * 1. Comentar este archivo en bootstrap.php
 * 2. O agregar al inicio de index.php:
 *    ini_set('display_errors', 1);
 *    error_reporting(E_ALL);
 * 
 * EXTENDER EL SISTEMA:
 * 
 * Para agregar notificaciones por email en errores críticos:
 * 1. Agregar mail() en los handlers de errores fatales
 * 2. Verificar que no se envíen emails duplicados
 * 3. Usar rate limiting para evitar spam de emails
 * 
 * Para logging a archivos específicos:
 * 1. Usar error_log($mensaje, 3, '/path/to/custom.log')
 * 2. Rotar logs periódicamente
 * 3. Verificar permisos de escritura
 */

?>