<?php
/**
 * errors/handler.php — Manejador global de errores y excepciones.
 *
 * PROPÓSITO:
 * - Capturar todos los errores, warnings y excepciones de PHP.
 * - Mostrar al usuario solo un mensaje genérico, sin exponer detalles.
 * - Permitir que el sitio siga funcionando ante warnings/notices leves.
 * - Registrar todo en error_log para análisis posterior.
 *
 * ESTRATEGIA:
 * - Las excepciones no capturadas lanzan HTTP 500.
 * - Los errores fatales (E_ERROR, E_PARSE, etc.) también lanzan 500.
 * - Los warnings/notices se registran pero **no interrumpen** la ejecución.
 *
 * NOTAS:
 * - El archivo debe cargarse en bootstrap.php **después** de session_start().
 * - Los mensajes de usuario nunca deben revelar rutas o detalles técnicos.
 * - Este comportamiento es ideal para producción (GoDaddy / hosting compartido).
 * 
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * - Warnings se registran en error_log pero NO interrumpen la ejecución.
 * - Solo errores fatales (E_ERROR, E_PARSE) causan HTTP 500 y exit().
 * - Para debugging local: comentar require de este archivo en bootstrap.php.
 * - Usar tools/peek_log.php para ver errores registrados sin interrumpir.
 *
 * @author Camella Development Team - Error Handling v2
 * @version 2.0 - Tolerante a warnings
 * @date 2025-10-08
 */

declare(strict_types=1);

// ------------------------------------------------------------
// 1) Captura de excepciones no manejadas
// ------------------------------------------------------------
/**
 * Manejador de excepciones no capturadas
 * 
 * LÍNEA CLAVE: Solo las excepciones no manejadas causan HTTP 500
 * Efectos: Logging detallado + mensaje genérico + terminación
 */
set_exception_handler(function ($e) {
    // LÍNEA CLAVE: Logging detallado con archivo y línea
    error_log("[EXCEPTION] {$e->getMessage()} @ {$e->getFile()}:{$e->getLine()}");
    error_log("[EXCEPTION TRACE] " . $e->getTraceAsString());

    // Limpiar buffer de salida para evitar contenido parcial
    if (ob_get_level()) {
        ob_end_clean();
    }

    // LÍNEA CLAVE: Respuesta genérica al usuario (sin detalles técnicos)
    http_response_code(500);
    exit('Ha ocurrido un error interno. Por favor intenta nuevamente.');
});

// ------------------------------------------------------------
// 2) Captura de errores PHP en ejecución
// ------------------------------------------------------------
/**
 * Manejador de errores PHP (warnings, notices, fatales)
 * 
 * COMPORTAMIENTO CLAVE:
 * - Warnings/notices: se registran pero NO interrumpen ejecución
 * - Errores fatales: se registran Y terminan ejecución con HTTP 500
 * - Return true: evita el handler por defecto de PHP
 */
set_error_handler(function ($severity, $message, $file, $line) {
    // LÍNEA CLAVE: Registrar todo tipo de error para análisis posterior
    $severityName = [
        E_ERROR => 'ERROR', E_WARNING => 'WARNING', E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE', E_CORE_ERROR => 'CORE_ERROR', 
        E_CORE_WARNING => 'CORE_WARNING', E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING', E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING', E_USER_NOTICE => 'USER_NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR', E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ][$severity] ?? "UNKNOWN_{$severity}";
    
    error_log("[{$severityName}] {$message} @ {$file}:{$line}");

    // LÍNEA CLAVE: Solo interrumpir si el error es realmente crítico
    $fatalTypes = [
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        E_USER_ERROR, E_RECOVERABLE_ERROR
    ];

    if (in_array($severity, $fatalTypes, true)) {
        // Limpiar buffer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // LÍNEA CLAVE: Solo errores fatales causan HTTP 500 y exit()
        http_response_code(500);
        exit('Ha ocurrido un error interno. Por favor intenta nuevamente.');
    }

    // LÍNEA CLAVE: En warnings/notices/deprecated - continuar ejecución normal
    return true; // evita el handler por defecto de PHP
});

// ------------------------------------------------------------
// 3) Captura de errores fatales en shutdown
// ------------------------------------------------------------
/**
 * Manejador de shutdown para errores fatales no capturados
 * 
 * PROPÓSITO: Capturar errores que ocurren durante el shutdown de PHP
 * como parse errors, memory exhausted, etc.
 */
register_shutdown_function(function () {
    $e = error_get_last();

    // LÍNEA CLAVE: Solo actuar si hay error fatal real
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("[FATAL] {$e['message']} @ {$e['file']}:{$e['line']}");
        
        // Solo enviar respuesta si no se ha enviado ya
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        // LÍNEA CLAVE: Mensaje genérico sin detalles técnicos
        echo 'Ha ocurrido un error interno. Por favor intenta nuevamente.';
        exit();
    }
});

/**
 * NOTAS PARA MANTENIMIENTO FUTURO:
 * 
 * DEBUGGING EN DESARROLLO:
 * - Para ver errores detallados: comentar require de este archivo en bootstrap.php
 * - O definir ini_set('display_errors', 1) antes de incluir este handler
 * - O usar ENVIRONMENT=development en configuración
 * 
 * MONITOREO EN PRODUCCIÓN:
 * - Usar tools/peek_log.php para revisar errores registrados
 * - Warnings/notices no interrumpen pero se registran para análisis
 * - Solo errores fatales causan HTTP 500 visible al usuario
 * 
 * PERSONALIZACIÓN:
 * - Para cambiar mensaje de error: modificar texto en exit()
 * - Para agregar notificación por email: usar error_log() con mail()
 * - Para logging personalizado: reemplazar error_log() con custom logger
 * 
 * SEGURIDAD:
 * - Nunca mostrar rutas de archivos al usuario final
 * - Mantener logs accesibles solo para administradores
 * - Considerar rotación de logs si crecen mucho
 * 
 * CÓMO REVERTIR:
 * - Comentar require_once de este archivo en bootstrap.php
 * - O restaurar handler anterior desde git history
 * - O usar ini_set('display_errors', 1) para mostrar errores PHP nativos
 */
?>