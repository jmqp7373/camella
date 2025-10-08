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
 * @version 2.0 - MySQLi
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
    
    // Verificar que las variables estén definidas
    if (!isset($host) || !isset($usuario) || !isset($contrasena) || !isset($basedatos)) {
        throw new Exception("Las variables de conexión no están definidas en config.php");
    }
    
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
    <title>Test MySQLi - Camella.com.co</title>
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
        <h1>🔍 Test de Conexión MySQLi</h1>
        <h2>Camella.com.co v2.0</h2>

        <?php
        // Información de configuración (sin mostrar credenciales)
        echo '<h3>📋 Configuración Detectada:</h3>';
        echo '<div class="config-item"><strong>Host:</strong> ' . htmlspecialchars($host) . '</div>';
        echo '<div class="config-item"><strong>Puerto:</strong> ' . htmlspecialchars($puerto) . '</div>';
        echo '<div class="config-item"><strong>Base de Datos:</strong> ' . htmlspecialchars($basedatos) . '</div>';
        echo '<div class="config-item"><strong>Usuario:</strong> ' . htmlspecialchars($usuario) . '</div>';
        echo '<div class="config-item"><strong>Charset:</strong> ' . htmlspecialchars($charset) . '</div>';
        echo '<div class="config-item"><strong>Tipo de Conexión:</strong> MySQLi</div>';

        // Intentar conexión
        echo '<h3>🚀 Resultado de la Prueba MySQLi:</h3>';
        
        // ========================================
        // PRUEBA DE CONEXIÓN MYSQLI
        // ========================================
        
        // Usar la función del config.php
        $conexion = conectarBD();
        
        if ($conexion) {
            echo '<div class="status success">';
            echo '✅ <strong>¡CONEXIÓN EXITOSA!</strong><br>';
            echo 'La base de datos está correctamente configurada y accesible con MySQLi.';
            echo '</div>';
            
            // Información adicional de la conexión
            try {
                $version_result = $conexion->query("SELECT VERSION()");
                $charset_result = $conexion->query("SELECT @@character_set_database");
                
                if ($version_result && $charset_result) {
                    $version = $version_result->fetch_row()[0];
                    $db_charset = $charset_result->fetch_row()[0];
                    
                    echo '<div class="info">';
                    echo '<strong>Información del Servidor MySQLi:</strong><br>';
                    echo '🗄️ Versión MySQL/MariaDB: ' . htmlspecialchars($version) . '<br>';
                    echo '🔤 Charset de BD: ' . htmlspecialchars($db_charset) . '<br>';
                    echo '📊 Estado de conexión: Activa<br>';
                    echo '🔒 Tipo de conexión: MySQLi<br>';
                    echo '🌐 Host Info: ' . htmlspecialchars($conexion->host_info) . '<br>';
                    echo '📋 Server Info: ' . htmlspecialchars($conexion->server_info) . '<br>';
                    echo '⚡ Conexión establecida: ' . date('Y-m-d H:i:s');
                    echo '</div>';
                } else {
                    echo '<div class="info">ℹ️ Conexión exitosa pero no se pudo obtener información del servidor.</div>';
                }
                
            } catch (Exception $infoError) {
                echo '<div class="info">ℹ️ Conexión exitosa pero error al obtener detalles: ' . htmlspecialchars($infoError->getMessage()) . '</div>';
            }
            
            // Cerrar conexión
            cerrarBD($conexion);
            
        } else {
            // Error de conexión MySQLi
            echo '<div class="status error">';
            echo '❌ <strong>ERROR DE CONEXIÓN MySQLi</strong><br>';
            echo 'No se pudo conectar a la base de datos.<br><br>';
            
            // Mostrar error específico de MySQLi
            if (mysqli_connect_errno()) {
                echo '<strong>Código de Error:</strong> ' . mysqli_connect_errno() . '<br>';
                echo '<strong>Mensaje de Error:</strong> ' . htmlspecialchars(mysqli_connect_error()) . '<br>';
            }
            echo '</div>';
            
            // Sugerencias de solución
            echo '<div class="info">';
            echo '<strong>💡 Posibles soluciones:</strong><br>';
            echo '• Verificar que el servidor MySQL esté ejecutándose<br>';
            echo '• Comprobar las credenciales en config/config.php<br>';
            echo '• Validar que la base de datos "' . htmlspecialchars($basedatos) . '" exista<br>';
            echo '• Revisar permisos del usuario "' . htmlspecialchars($usuario) . '"<br>';
            echo '• Verificar que el puerto ' . $puerto . ' esté abierto';
            echo '</div>';
        }
        ?>

        <div class="warning">
            ⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo (database_test.php) en el servidor de producción por seguridad.
        </div>

        <div class="info" style="text-align: center; margin-top: 30px;">
            <strong>Camella.com.co</strong> - Portal de Empleo Colombiano<br>
            <small>Desarrollado con ❤️ para Colombia - Versión MySQLi</small>
        </div>
    </div>
</body>
</html>