<?php
/**
 * Test específico para verificar logs [RESET] en tiempo real
 * Ejecutar en navegador para monitorear logs mientras se prueba el formulario
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 MONITOR LOGS [RESET] - TIEMPO REAL</h1>";

// Función para leer últimas líneas del log de errores de PHP
function getTailErrorLog($lines = 50) {
    $logFile = ini_get('error_log');
    if (!$logFile || !file_exists($logFile)) {
        // Try common log locations
        $possibleLogs = [
            'error_log',
            'php_errors.log',
            '/var/log/apache2/error.log',
            '/var/log/php_errors.log'
        ];
        
        foreach ($possibleLogs as $log) {
            if (file_exists($log)) {
                $logFile = $log;
                break;
            }
        }
    }
    
    if ($logFile && file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $allLines = explode("\n", $content);
        $lastLines = array_slice($allLines, -$lines);
        
        // Filter for [RESET] lines
        $resetLines = array_filter($lastLines, function($line) {
            return strpos($line, '[RESET]') !== false;
        });
        
        return [
            'file' => $logFile,
            'resetLines' => array_values($resetLines),
            'totalLines' => count($allLines)
        ];
    }
    
    return ['file' => null, 'resetLines' => [], 'totalLines' => 0];
}

$logData = getTailErrorLog();

echo "<h2>📋 Estado del Sistema</h2>";

try {
    require_once __DIR__ . '/bootstrap.php';
    
    echo "<p>✅ Bootstrap cargado</p>";
    
    if (class_exists('PasswordController')) {
        echo "<p>✅ PasswordController disponible</p>";
    }
    
    if (class_exists('MailHelper')) {
        echo "<p>✅ MailHelper disponible</p>";
    }
    
    $pdo = getPDO();
    echo "<p>✅ Base de datos conectada</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>📝 Log File</h2>";
if ($logData['file']) {
    echo "<p>📂 Leyendo: <code>" . htmlspecialchars($logData['file']) . "</code></p>";
    echo "<p>📊 Total líneas en log: " . $logData['totalLines'] . "</p>";
} else {
    echo "<p>❌ No se encontró archivo de log</p>";
}

echo "<h2>🔍 Últimos logs [RESET]</h2>";

if (empty($logData['resetLines'])) {
    echo "<div style='background:#fff3cd; padding:15px; border-radius:5px; border:1px solid #ffeaa7;'>";
    echo "<p><strong>📭 No hay logs [RESET] aún</strong></p>";
    echo "<p>Para generar logs:</p>";
    echo "<ol>";
    echo "<li>Ve a <a href='/index.php?view=recuperar-password' target='_blank'>/recuperar-password</a></li>";
    echo "<li>Envía el formulario con cualquier email</li>";
    echo "<li>Regresa aquí y actualiza la página</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div style='background:#f8f9fa; padding:15px; border-radius:5px; font-family:monospace; font-size:12px; max-height:300px; overflow-y:scroll; border:1px solid #dee2e6;'>";
    
    foreach ($logData['resetLines'] as $line) {
        $line = htmlspecialchars($line);
        
        // Colorear por tipo de log
        if (strpos($line, '[ERROR]') !== false) {
            echo "<span style='color:#dc3545; font-weight:bold;'>$line</span><br>";
        } elseif (strpos($line, '[EXCEPTION]') !== false) {
            echo "<span style='color:#fd7e14; font-weight:bold;'>$line</span><br>";
        } elseif (strpos($line, 'POST recibido') !== false) {
            echo "<span style='color:#17a2b8; font-weight:bold;'>$line</span><br>";
        } elseif (strpos($line, 'sent=OK') !== false) {
            echo "<span style='color:#28a745; font-weight:bold;'>$line</span><br>";
        } elseif (strpos($line, 'sent=FAIL') !== false) {
            echo "<span style='color:#dc3545; font-weight:bold;'>$line</span><br>";
        } elseif (strpos($line, 'token usado OK') !== false) {
            echo "<span style='color:#28a745; font-weight:bold;'>$line</span><br>";
        } else {
            echo "<span style='color:#6c757d;'>$line</span><br>";
        }
    }
    
    echo "</div>";
    
    echo "<p><strong>Total logs [RESET] encontrados:</strong> " . count($logData['resetLines']) . "</p>";
}

echo "<h2>🎯 Instrucciones de Test</h2>";
echo "<div style='background:#d1ecf1; padding:15px; border-radius:5px; border:1px solid #bee5eb;'>";
echo "<ol style='margin:0;'>";
echo "<li><strong>Formulario de recuperación:</strong> <a href='/index.php?view=recuperar-password' target='_blank'>Abrir en nueva pestaña</a></li>";
echo "<li><strong>Envía el formulario</strong> con cualquier email (ej: test@example.com)</li>";
echo "<li><strong>Actualiza esta página</strong> para ver los nuevos logs [RESET]</li>";
echo "<li><strong>Deberías ver:</strong></li>";
echo "<ul>";
echo "<li><code>[RESET] POST recibido</code> - Controlador ejecutándose</li>";
echo "<li><code>[RESET] email=test@example.com token=a1b2c3d4... sent=OK/FAIL</code> - Resultado</li>";
echo "</ul>";
echo "</ol>";
echo "</div>";

echo "<p style='text-align:center; margin-top:20px;'>";
echo "<a href='?' style='background:#007bff; color:white; padding:8px 16px; border-radius:4px; text-decoration:none;'>🔄 Actualizar Logs</a>";
echo "</p>";

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1 { color: #2c3e50; }
    h2 { color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px; margin-top: 30px; }
    code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-size: 90%; }
</style>