<?php
/**
 * CAMELLA.COM.CO - PRUEBA DE CONEXIÓN A BASE DE DATOS
 * 
 * Este archivo verifica que la configuración de base de datos
 * funcione correctamente y muestra información de conexión.
 * 
 * IMPORTANTE: Eliminar este archivo en producción por seguridad.
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025
 */

// ========================================
// CONFIGURACIÓN DE DEPURACIÓN
// ========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Capturar cualquier error fatal
function fatalErrorHandler() {
    $error = error_get_last();
    if ($error && $error['type'] === E_ERROR) {
        echo "<div style='color: red; padding: 20px; background: #fee;'>";
        echo "<h2>Error Fatal PHP:</h2>";
        echo "<strong>Mensaje:</strong> " . $error['message'] . "<br>";
        echo "<strong>Archivo:</strong> " . $error['file'] . "<br>";
        echo "<strong>Línea:</strong> " . $error['line'];
        echo "</div>";
    }
}
register_shutdown_function('fatalErrorHandler');

// Intentar incluir configuración con manejo de errores
try {
    if (!file_exists(__DIR__ . '/config/config.php')) {
        throw new Exception("El archivo config/config.php no existe. Verifica que esté creado.");
    }
    require_once __DIR__ . '/config/config.php';
} catch (Exception $e) {
    echo "<div style='color: red; padding: 20px; background: #fee;'>";
    echo "<h2>Error al cargar configuración:</h2>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - <?= APP_NAME ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: bold;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
            font-size: 14px;
            margin-top: 20px;
        }
        .config-item {
            background: #f8f9fa;
            padding: 10px;
            margin: 8px 0;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Test de Conexión a Base de Datos</h1>
        <h2><?= APP_NAME ?> v<?= APP_VERSION ?></h2>

        <?php
        // Información de configuración (sin mostrar credenciales)
        echo '<h3>📋 Configuración Detectada:</h3>';
        echo '<div class="config-item"><strong>Host:</strong> ' . DB_HOST . '</div>';
        echo '<div class="config-item"><strong>Base de Datos:</strong> ' . DB_NAME . '</div>';
        echo '<div class="config-item"><strong>Usuario:</strong> ' . DB_USER . '</div>';
        echo '<div class="config-item"><strong>Charset:</strong> ' . DB_CHARSET . '</div>';
        echo '<div class="config-item"><strong>Entorno:</strong> ' . APP_ENV . '</div>';
        echo '<div class="config-item"><strong>Debug Mode:</strong> ' . (DEBUG_MODE ? 'Activado' : 'Desactivado') . '</div>';

        // Intentar conexión
        echo '<h3>🚀 Resultado de la Prueba:</h3>';
        
        try {
            // Verificar que las constantes estén definidas
            if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
                throw new Exception("Las constantes de base de datos no están definidas en config.php");
            }

            // Probar conexión manual primero (más control de errores)
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            // Verificar conexión adicional
            if ($pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS)) {
                echo '<div class="status success">';
                echo '✅ <strong>¡CONEXIÓN EXITOSA!</strong><br>';
                echo 'La base de datos está correctamente configurada y accesible.';
                echo '</div>';
                
                // Información adicional de la conexión
                try {
                    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                    $charset = $pdo->query("SELECT @@character_set_database")->fetchColumn();
                    
                    echo '<div class="info">';
                    echo '<strong>Información del Servidor:</strong><br>';
                    echo '🗄️ Versión MySQL/MariaDB: ' . htmlspecialchars($version) . '<br>';
                    echo '� Charset de BD: ' . htmlspecialchars($charset) . '<br>';
                    echo '�📊 Estado de conexión: Activa<br>';
                    echo '🔒 Modo de error PDO: Exception<br>';
                    echo '⚡ Conexión establecida: ' . date('Y-m-d H:i:s');
                    echo '</div>';
                } catch (Exception $infoError) {
                    echo '<div class="info">ℹ️ Conexión exitosa pero no se pudo obtener información adicional del servidor.</div>';
                }
            }
            
        } catch (PDOException $e) {
            // Error de conexión
            echo '<div class="status error">';
            echo '❌ <strong>ERROR DE CONEXIÓN</strong><br>';
            echo 'No se pudo conectar a la base de datos.<br><br>';
            
            if (DEBUG_MODE) {
                echo '<strong>Detalles del error:</strong><br>';
                echo htmlspecialchars($e->getMessage());
            } else {
                echo 'Contacta al administrador del sistema.';
            }
            echo '</div>';
            
            // Sugerencias de solución
            echo '<div class="info">';
            echo '<strong>💡 Posibles soluciones:</strong><br>';
            echo '• Verificar que el servidor de base de datos esté ejecutándose<br>';
            echo '• Comprobar las credenciales en config/config.php<br>';
            echo '• Validar que la base de datos exista<br>';
            echo '• Revisar permisos del usuario de base de datos';
            echo '</div>';
            
        } catch (Exception $e) {
            // Error general
            echo '<div class="status error">';
            echo '❌ <strong>ERROR GENERAL</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>

        <div class="warning">
            ⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo (database_test.php) en el servidor de producción por seguridad.
        </div>

        <div class="info" style="text-align: center; margin-top: 30px;">
            <strong>Camella.com.co</strong> - Portal de Empleo Colombiano<br>
            <small>Desarrollado con ❤️ para Colombia</small>
        </div>
    </div>
</body>
</html>