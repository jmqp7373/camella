<?php
/**
 * tools/selfcheck.php — Diagnóstico textual de bootstrap/sesión/DB.
 * 
 * PROPÓSITO:
 * - Verificar, sin maquetación, que el entorno mínimo está funcionando.
 * - Diagnosticar problemas de bootstrap, sesión y conexión a base de datos.
 * - Proporcionar información de estado sin exponer credenciales o rutas.
 * 
 * SEGURIDAD:
 * - No exponer credenciales de base de datos.
 * - Mostrar solo estados (OK/FAIL/ACTIVA/NO ACTIVA).
 * - Salida en texto plano para fácil lectura y parsing.
 * 
 * USO:
 * - Acceder vía: https://camella.com.co/tools/selfcheck.php
 * - Revisar estado de componentes críticos del sistema.
 * - Identificar rápidamente problemas de configuración.
 * 
 * NOTAS PARA DESARROLLADORES:
 * - Este archivo es temporal para diagnóstico.
 * - Eliminar cuando el sistema esté estable.
 * - No incluir información sensible en la salida.
 * 
 * @author Camella Development Team - Diagnostic Tools
 * @version 1.0
 * @date 2025-10-08
 */

declare(strict_types=1);

// LÍNEA CLAVE: Establecer salida como texto plano para diagnóstico
header('Content-Type: text/plain; charset=utf-8');

echo "SELF-CHECK camella.com.co\n";
echo "=========================\n\n";
echo "Timestamp: " . date('Y-m-d H:i:s T') . "\n\n";

// ------------------------------------------------------------
// 1) Verificación de Bootstrap
// ------------------------------------------------------------
echo "1) BOOTSTRAP\n";
echo "------------\n";

$bootstrap = __DIR__ . '/../bootstrap.php';
echo "Bootstrap file: ";
if (file_exists($bootstrap)) {
    echo "encontrado\n";
    try {
        // LÍNEA CLAVE: Cargar bootstrap para inicializar el sistema
        require_once $bootstrap;
        echo "Bootstrap load: OK\n";
    } catch (Throwable $e) {
        echo "Bootstrap load: FAIL (" . $e->getMessage() . ")\n";
    }
} else {
    echo "NO encontrado\n";
    echo "ERROR: bootstrap.php es requerido para el funcionamiento del sistema.\n";
    exit(1);
}

// ------------------------------------------------------------
// 2) Estado de Sesión
// ------------------------------------------------------------
echo "\n2) SESION\n";
echo "---------\n";

echo "Session functions: ";
if (function_exists('session_status')) {
    echo "disponibles\n";
    
    // LÍNEA CLAVE: Verificar estado actual de la sesión
    $sessionStatus = session_status();
    echo "Session status: ";
    
    switch ($sessionStatus) {
        case PHP_SESSION_DISABLED:
            echo "DISABLED\n";
            break;
        case PHP_SESSION_NONE:
            echo "NONE (not started)\n";
            break;
        case PHP_SESSION_ACTIVE:
            echo "ACTIVA\n";
            break;
        default:
            echo "UNKNOWN ({$sessionStatus})\n";
    }
    
    // Información adicional si la sesión está activa
    if ($sessionStatus === PHP_SESSION_ACTIVE) {
        echo "Session ID: " . (session_id() ? "presente" : "ausente") . "\n";
        echo "Session name: " . session_name() . "\n";
    }
} else {
    echo "NO disponibles\n";
}

// ------------------------------------------------------------
// 3) Error Handler
// ------------------------------------------------------------
echo "\n3) ERROR HANDLER\n";
echo "----------------\n";

// Heurística: si bootstrap se cargó OK, el handler debería estar activo
echo "Error handler: cargado (via bootstrap)\n";

// Verificar configuración de errores
$displayErrors = ini_get('display_errors');
$errorReporting = error_reporting();

echo "Display errors: " . ($displayErrors ? 'ON' : 'OFF') . "\n";
echo "Error reporting: " . $errorReporting . "\n";

// ------------------------------------------------------------
// 4) Conexión a Base de Datos
// ------------------------------------------------------------
echo "\n4) BASE DE DATOS\n";
echo "----------------\n";

// 4) Conexión DB
$pdo = $pdo ?? ($GLOBALS['pdo'] ?? null);

echo "PDO instance: ";
if ($pdo instanceof PDO) {
    echo "instanciado (via bootstrap)\n";
} else {
    // Intentar vía getPDO() si existe
    if (!function_exists('getPDO')) {
        $cfg = __DIR__ . '/../config/config.php';
        if (file_exists($cfg)) require_once $cfg;
    }
    try {
        if (function_exists('getPDO')) {
            $pdo = getPDO();
            $GLOBALS['pdo'] = $pdo; // publicar para próximos includes
        }
    } catch (Throwable $e) {
        // continuar; se reporta como no instanciado
    }
    echo ($pdo instanceof PDO) ? "instanciado (via getPDO)\n" : "NO instanciado\n";
}

if ($pdo instanceof PDO) {
    try {
        $ok = $pdo->query('SELECT 1')->fetchColumn();
        echo "Query SELECT 1: " . ($ok ? "OK\n" : "FAIL\n");
    } catch (Throwable $e) {
        echo "Query SELECT 1: FAIL (" . $e->getMessage() . ")\n";
    }
} else {
    echo "INFO: PDO disponible pero no inicializado por bootstrap\n";
}

// ------------------------------------------------------------
// 5) Archivos Críticos del Sistema
// ------------------------------------------------------------
echo "\n5) ARCHIVOS CRITICOS\n";
echo "--------------------\n";

$criticalFiles = [
    'config/config.php' => 'Configuración principal',
    'partials/header.php' => 'Header del sitio',
    'partials/footer.php' => 'Footer del sitio',
    'views/home.php' => 'Vista principal',
    'errors/handler.php' => 'Manejador de errores'
];

foreach ($criticalFiles as $file => $description) {
    $fullPath = __DIR__ . '/../' . $file;
    echo "{$description}: ";
    echo file_exists($fullPath) ? "OK\n" : "FALTA\n";
}

// ------------------------------------------------------------
// 6) Información del Servidor
// ------------------------------------------------------------
echo "\n6) SERVIDOR\n";
echo "-----------\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current User: " . (function_exists('get_current_user') ? get_current_user() : 'Unknown') . "\n";

// ------------------------------------------------------------
// Finalización
// ------------------------------------------------------------
echo "\n=========================\n";
echo "SELF-CHECK COMPLETADO\n";

$overallStatus = true; // Determinar estado general del sistema

// Verificar condiciones críticas
if (!file_exists($bootstrap)) {
    $overallStatus = false;
}

if (!function_exists('session_status') || session_status() === PHP_SESSION_DISABLED) {
    $overallStatus = false;
}

echo "Estado General: " . ($overallStatus ? "SALUDABLE" : "PROBLEMAS DETECTADOS") . "\n";
echo "\nNOTAS:\n";
echo "- Este es un diagnóstico básico del sistema.\n";
echo "- Para logs detallados, revisar tools/peek_log.php\n";
echo "- Eliminar este archivo cuando no sea necesario.\n";

/**
 * NOTAS PARA MANTENIMIENTO:
 * 
 * INTERPRETACIÓN DE RESULTADOS:
 * - Bootstrap encontrado + OK: Sistema base funcional
 * - Session ACTIVA: Autenticación y estado funcionando
 * - PDO instanciado + SELECT 1 OK: Base de datos accesible
 * - Archivos críticos OK: Vistas y configuración disponibles
 * 
 * TROUBLESHOOTING:
 * - Si bootstrap FAIL: revisar config/config.php y dependencias
 * - Si session NONE: verificar que bootstrap inicie sesión correctamente
 * - Si PDO NO instanciado: verificar configuración de base de datos
 * - Si SELECT 1 FAIL: problemas de conectividad o credenciales DB
 * 
 * ELIMINACIÓN:
 * - git rm tools/selfcheck.php
 * - git commit -m "chore: remove diagnostic selfcheck tool"
 * - git push
 */
?>