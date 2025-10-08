<?php
/**
 * health.php — Diagnóstico temporal para identificar errores 500.
 * 
 * Propósito: Localizar la causa exacta del error 500 en producción
 * mediante habilitación temporal de error reporting y revisión de logs.
 * 
 * IMPORTANTE: NO forma parte de la maquetación; se debe borrar al finalizar.
 * 
 * Efectos: 
 * - Habilita error_reporting SOLO en esta ruta específica
 * - Lee el error_log de GoDaddy si existe
 * - Proporciona información de estado del servidor
 * 
 * @author Camella Development Team - Hotfix
 * @version 1.0
 * @date 2025-10-08
 * @temporary true - ELIMINAR después del diagnóstico
 */

// Habilitar reporte de errores SOLO para este archivo de diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar headers para diagnóstico
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>\n";
echo "<html lang='es'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Diagnóstico Temporal - Camella.com.co</title>\n";
echo "<style>\n";
echo "body { font-family: monospace; background: #f5f5f5; margin: 2rem; }\n";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; }\n";
echo ".status-ok { color: #28a745; font-weight: bold; }\n";
echo ".status-error { color: #dc3545; font-weight: bold; }\n";
echo ".status-warning { color: #ffc107; font-weight: bold; }\n";
echo ".log-section { background: #f8f9fa; padding: 1rem; border-radius: 4px; margin: 1rem 0; }\n";
echo "pre { background: #e9ecef; padding: 1rem; border-radius: 4px; overflow-x: auto; max-height: 400px; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='container'>\n";
echo "<h1>🔍 Diagnóstico Temporal - Error 500</h1>\n";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

// Estado básico de PHP
echo "<div class='log-section'>\n";
echo "<h2>✅ Estado Básico</h2>\n";
echo "<span class='status-ok'>OK - PHP health check</span><br>\n";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>\n";
echo "<strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "<br>\n";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No disponible') . "<br>\n";
echo "<strong>Script Path:</strong> " . __FILE__ . "<br>\n";
echo "</div>\n";

// Verificar archivos críticos
echo "<div class='log-section'>\n";
echo "<h2>📁 Verificación de Archivos Críticos</h2>\n";

$archivosCriticos = [
    'index.php' => __DIR__ . '/../index.php',
    'config/config.php' => __DIR__ . '/../config/config.php',
    'helpers/AuthHelper.php' => __DIR__ . '/../helpers/AuthHelper.php',
    'models/Usuario.php' => __DIR__ . '/../models/Usuario.php',
    'partials/header.php' => __DIR__ . '/../partials/header.php'
];

foreach ($archivosCriticos as $nombre => $ruta) {
    if (file_exists($ruta)) {
        echo "<span class='status-ok'>✅ $nombre</span> - Existe<br>\n";
    } else {
        echo "<span class='status-error'>❌ $nombre</span> - NO ENCONTRADO<br>\n";
    }
}
echo "</div>\n";

// Verificar permisos y directorios
echo "<div class='log-section'>\n";
echo "<h2>🔐 Permisos y Directorios</h2>\n";

$directorios = [
    'Raíz' => __DIR__ . '/..',
    'Controllers' => __DIR__ . '/../controllers',
    'Models' => __DIR__ . '/../models', 
    'Views' => __DIR__ . '/../views',
    'Helpers' => __DIR__ . '/../helpers'
];

foreach ($directorios as $nombre => $ruta) {
    if (is_dir($ruta)) {
        $permisos = substr(sprintf('%o', fileperms($ruta)), -4);
        echo "<span class='status-ok'>📁 $nombre</span> - Permisos: $permisos<br>\n";
    } else {
        echo "<span class='status-error'>❌ $nombre</span> - Directorio no existe<br>\n";
    }
}
echo "</div>\n";

// Buscar y mostrar error_log
echo "<div class='log-section'>\n";
echo "<h2>📋 Registro de Errores</h2>\n";

// Ubicaciones comunes de error_log en GoDaddy
$posiblesLogs = [
    __DIR__ . '/../error_log',           // Raíz del sitio
    __DIR__ . '/../../error_log',        // Un nivel arriba
    __DIR__ . '/../logs/error_log',      // Directorio logs
    '/tmp/error_log',                    // Temporal del sistema
    ini_get('error_log')                 // Configuración PHP
];

$logEncontrado = false;

foreach ($posiblesLogs as $logPath) {
    if ($logPath && file_exists($logPath) && is_readable($logPath)) {
        echo "<span class='status-ok'>✅ Error log encontrado:</span> $logPath<br>\n";
        
        try {
            $lines = file($logPath);
            if ($lines !== false) {
                $ultimasLineas = array_slice($lines, -200); // Últimas 200 líneas
                echo "<h3>📖 Últimas " . count($ultimasLineas) . " líneas del error_log:</h3>\n";
                echo "<pre>\n";
                foreach ($ultimasLineas as $linea) {
                    echo htmlspecialchars($linea);
                }
                echo "</pre>\n";
                $logEncontrado = true;
                break;
            }
        } catch (Exception $e) {
            echo "<span class='status-warning'>⚠️ Error leyendo log:</span> " . htmlspecialchars($e->getMessage()) . "<br>\n";
        }
    }
}

if (!$logEncontrado) {
    echo "<span class='status-warning'>⚠️ No se encontró error_log en ubicaciones comunes.</span><br>\n";
    echo "Ubicaciones revisadas:<br>\n";
    foreach ($posiblesLogs as $logPath) {
        if ($logPath) {
            echo "- " . htmlspecialchars($logPath) . "<br>\n";
        }
    }
}
echo "</div>\n";

// Test de inclusión de archivos críticos
echo "<div class='log-section'>\n";
echo "<h2>🧪 Test de Inclusión</h2>\n";

try {
    // Test de config
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        echo "<span class='status-ok'>✅ config/config.php</span> - Accesible para inclusión<br>\n";
    } else {
        echo "<span class='status-error'>❌ config/config.php</span> - No encontrado<br>\n";
    }
    
    // Test de AuthHelper
    $authPath = __DIR__ . '/../helpers/AuthHelper.php';
    if (file_exists($authPath)) {
        echo "<span class='status-ok'>✅ helpers/AuthHelper.php</span> - Accesible para inclusión<br>\n";
    } else {
        echo "<span class='status-error'>❌ helpers/AuthHelper.php</span> - No encontrado<br>\n";
    }
    
} catch (Exception $e) {
    echo "<span class='status-error'>❌ Error en test de inclusión:</span> " . htmlspecialchars($e->getMessage()) . "<br>\n";
}

echo "</div>\n";

// Información del servidor web
echo "<div class='log-section'>\n";
echo "<h2>🌐 Información del Servidor</h2>\n";
echo "<strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No disponible') . "<br>\n";
echo "<strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No disponible') . "<br>\n";
echo "<strong>Request Method:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'No disponible') . "<br>\n";
echo "<strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'No disponible') . "<br>\n";
echo "<strong>Remote Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'No disponible') . "<br>\n";
echo "</div>\n";

// Test de sesión
echo "<div class='log-section'>\n";
echo "<h2>🔐 Test de Sesión</h2>\n";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        echo "<span class='status-ok'>✅ Sesión iniciada correctamente</span><br>\n";
    } else {
        echo "<span class='status-ok'>✅ Sesión ya estaba activa</span><br>\n";
    }
    echo "<strong>Session ID:</strong> " . session_id() . "<br>\n";
    echo "<strong>Session Status:</strong> " . session_status() . "<br>\n";
} catch (Exception $e) {
    echo "<span class='status-error'>❌ Error en sesión:</span> " . htmlspecialchars($e->getMessage()) . "<br>\n";
}
echo "</div>\n";

echo "<div style='margin-top: 2rem; padding: 1rem; background: #fff3cd; border-radius: 4px;'>\n";
echo "<h3>⚠️ IMPORTANTE</h3>\n";
echo "<p>Este archivo de diagnóstico debe ser <strong>ELIMINADO</strong> después de resolver el error 500.</p>\n";
echo "<p>No debe permanecer en producción por razones de seguridad.</p>\n";
echo "<p><strong>Comando para eliminar:</strong> <code>rm diagnostics/health.php</code></p>\n";
echo "</div>\n";

echo "</div>\n"; // Cerrar container
echo "</body></html>\n";

// Log de acceso a diagnóstico
error_log("[DIAGNOSTICS] health.php accedido desde " . ($_SERVER['REMOTE_ADDR'] ?? 'IP desconocida') . " en " . date('Y-m-d H:i:s'));

?>