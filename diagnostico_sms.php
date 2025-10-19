<?php
/**
 * DIAGNÓSTICO COMPLETO: Sistema de Magic Links y SMS
 * 
 * Este script verifica TODOS los componentes necesarios para que funcione el envío de SMS
 * 
 * INSTRUCCIONES:
 * 1. Subir a: https://camella.com.co/diagnostico_sms.php
 * 2. Acceder desde navegador
 * 3. Revisar cada sección
 * 4. ELIMINAR después de usar (contiene info sensible)
 */

// Seguridad básica - eliminar en producción real
$allowed_ips = ['127.0.0.1', '::1']; // Agregar tu IP si es necesario
// if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
//     die('Acceso denegado');
// }

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico SMS - Camella</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status { 
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status.success { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .status.warning { background: #fff3cd; color: #856404; }
        .status.info { background: #d1ecf1; color: #0c5460; }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.6;
        }
        .detail { 
            margin: 10px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        .detail strong { color: #667eea; }
        .test-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .test-btn:hover { background: #764ba2; transform: translateY(-2px); }
        .icon { font-size: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnóstico Completo del Sistema SMS</h1>
            <p>Verificación de Magic Links, Twilio y Base de Datos</p>
        </div>
        
        <div class="content">
            
            <?php
            $allOk = true;
            
            // ==========================================
            // 1. CONFIGURACIÓN Y ARCHIVOS
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">📁</span> 1. Archivos de Configuración</h2>';
            
            $configFiles = [
                'config/config.php',
                'config/database.php',
                'controllers/MagicLinkController.php'
            ];
            
            foreach ($configFiles as $file) {
                if (file_exists($file)) {
                    echo "<div class='detail'><strong>✅ $file</strong> - Existe</div>";
                } else {
                    echo "<div class='detail'><strong>❌ $file</strong> - NO EXISTE</div>";
                    $allOk = false;
                }
            }
            echo '</div>';
            
            // ==========================================
            // 2. CONEXIÓN A BASE DE DATOS
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">🗄️</span> 2. Base de Datos</h2>';
            
            try {
                require_once __DIR__ . '/config/database.php';
                $pdo = getPDO();
                echo "<div class='detail'><span class='status success'>✅ CONECTADO</span></div>";
                
                // Verificar tablas necesarias
                $tables = ['verification_codes', 'magic_links', 'users'];
                foreach ($tables as $table) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        $stmt2 = $pdo->query("SELECT COUNT(*) as total FROM $table");
                        $count = $stmt2->fetch(PDO::FETCH_ASSOC);
                        echo "<div class='detail'><strong>✅ Tabla: $table</strong> - {$count['total']} registros</div>";
                    } else {
                        echo "<div class='detail'><strong>❌ Tabla: $table</strong> - NO EXISTE</div>";
                        $allOk = false;
                    }
                }
            } catch (Exception $e) {
                echo "<div class='detail'><span class='status error'>❌ ERROR DE CONEXIÓN</span></div>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
                $allOk = false;
            }
            echo '</div>';
            
            // ==========================================
            // 3. CREDENCIALES TWILIO
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">📱</span> 3. Configuración Twilio</h2>';
            
            if (defined('TWILIO_SID')) {
                $sidPreview = substr(TWILIO_SID, 0, 10) . '...' . substr(TWILIO_SID, -4);
                echo "<div class='detail'><strong>TWILIO_SID:</strong> $sidPreview <span class='status success'>✅</span></div>";
            } else {
                echo "<div class='detail'><strong>TWILIO_SID:</strong> <span class='status error'>❌ NO DEFINIDO</span></div>";
                $allOk = false;
            }
            
            if (defined('TWILIO_AUTH_TOKEN')) {
                $tokenPreview = substr(TWILIO_AUTH_TOKEN, 0, 6) . '...' . substr(TWILIO_AUTH_TOKEN, -4);
                echo "<div class='detail'><strong>TWILIO_AUTH_TOKEN:</strong> $tokenPreview <span class='status success'>✅</span></div>";
            } else {
                echo "<div class='detail'><strong>TWILIO_AUTH_TOKEN:</strong> <span class='status error'>❌ NO DEFINIDO</span></div>";
                $allOk = false;
            }
            
            if (defined('TWILIO_FROM_NUMBER')) {
                echo "<div class='detail'><strong>TWILIO_FROM_NUMBER:</strong> " . TWILIO_FROM_NUMBER . " <span class='status success'>✅</span></div>";
            } else {
                echo "<div class='detail'><strong>TWILIO_FROM_NUMBER:</strong> <span class='status error'>❌ NO DEFINIDO</span></div>";
                $allOk = false;
            }
            echo '</div>';
            
            // ==========================================
            // 4. COMPOSER Y DEPENDENCIAS
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">📦</span> 4. Dependencias (Composer)</h2>';
            
            if (file_exists('vendor/autoload.php')) {
                echo "<div class='detail'><strong>✅ vendor/autoload.php</strong> - Existe</div>";
                require_once 'vendor/autoload.php';
                
                if (class_exists('Twilio\Rest\Client')) {
                    echo "<div class='detail'><strong>✅ Twilio SDK</strong> - Cargado correctamente</div>";
                } else {
                    echo "<div class='detail'><strong>❌ Twilio SDK</strong> - NO se pudo cargar</div>";
                    $allOk = false;
                }
            } else {
                echo "<div class='detail'><strong>❌ vendor/autoload.php</strong> - NO EXISTE</div>";
                echo "<div class='detail'><span class='status warning'>⚠️ Ejecuta: composer install</span></div>";
                $allOk = false;
            }
            echo '</div>';
            
            // ==========================================
            // 5. PERMISOS Y LOGS
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">📝</span> 5. Logs y Permisos</h2>';
            
            $logLocations = [
                '/home/u179023609/logs/error_log',
                __DIR__ . '/error_log',
                ini_get('error_log')
            ];
            
            echo "<div class='detail'><strong>PHP Error Log:</strong> " . ini_get('error_log') . "</div>";
            echo "<div class='detail'><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</div>";
            echo "<div class='detail'><strong>Log Errors:</strong> " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</div>";
            echo '</div>';
            
            // ==========================================
            // 6. TEST DE MAGIC LINK CONTROLLER
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">🧪</span> 6. Test MagicLinkController</h2>';
            
            try {
                require_once __DIR__ . '/controllers/MagicLinkController.php';
                echo "<div class='detail'><span class='status success'>✅ MagicLinkController cargado</span></div>";
                
                // Probar instanciación
                $controller = new MagicLinkController();
                echo "<div class='detail'><span class='status success'>✅ Instancia creada correctamente</span></div>";
                
            } catch (Exception $e) {
                echo "<div class='detail'><span class='status error'>❌ ERROR al cargar controller</span></div>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
                $allOk = false;
            }
            echo '</div>';
            
            // ==========================================
            // 7. RESUMEN FINAL
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">📊</span> 7. Resumen Final</h2>';
            
            if ($allOk) {
                echo "<div class='detail' style='text-align: center; padding: 20px;'>";
                echo "<span class='status success' style='font-size: 18px;'>✅ TODOS LOS COMPONENTES OK</span>";
                echo "<p style='margin-top: 15px; color: #155724;'>El sistema debería funcionar correctamente</p>";
                echo "</div>";
            } else {
                echo "<div class='detail' style='text-align: center; padding: 20px;'>";
                echo "<span class='status error' style='font-size: 18px;'>❌ HAY PROBLEMAS</span>";
                echo "<p style='margin-top: 15px; color: #721c24;'>Revisa los errores marcados arriba</p>";
                echo "</div>";
            }
            
            echo '<div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px;">';
            echo '<strong>⚠️ IMPORTANTE:</strong> Elimina este archivo después de usarlo (contiene información sensible)';
            echo '</div>';
            
            echo '</div>';
            
            // ==========================================
            // 8. INFORMACIÓN DEL SISTEMA
            // ==========================================
            echo '<div class="section">';
            echo '<h2><span class="icon">⚙️</span> 8. Información del Sistema</h2>';
            echo "<div class='detail'><strong>PHP Version:</strong> " . phpversion() . "</div>";
            echo "<div class='detail'><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "</div>";
            echo "<div class='detail'><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</div>";
            echo "<div class='detail'><strong>Current Directory:</strong> " . __DIR__ . "</div>";
            echo "<div class='detail'><strong>HTTP Host:</strong> " . $_SERVER['HTTP_HOST'] . "</div>";
            echo '</div>';
            ?>
            
        </div>
    </div>
</body>
</html>
