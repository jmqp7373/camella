<?php
/**
 * Instalador del Módulo de Promotores
 * 
 * Propósito: Ejecutar de forma segura el esquema de base de datos
 * del módulo de promotores solo cuando un administrador lo autorice.
 * 
 * IMPORTANTE: Este script NO se ejecuta automáticamente.
 * Solo debe ejecutarse bajo supervisión administrativa.
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

// Verificar acceso administrativo antes de proceder
require_once dirname(__DIR__) . '/bootstrap.php';

// Solo usuarios admin pueden ejecutar instalaciones de BD
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    die('Acceso denegado - Solo administradores pueden instalar esquemas de BD');
}

/**
 * Ejecutar instalación del esquema de promotores
 * 
 * Propósito: Crear tablas y configuraciones iniciales del módulo
 * de promotores de forma segura y con rollback en caso de error.
 * 
 * @return array Resultado de la instalación con detalles
 */
function instalarEsquemaPromotores(): array {
    try {
        // Cargar configuración de base de datos
        require_once dirname(__DIR__) . '/config/config.php';
        global $host, $usuario, $contrasena, $basedatos, $charset;
        
        // Establecer conexión PDO con transacciones
        $dsn = "mysql:host=$host;dbname=$basedatos;charset=$charset";
        $pdo = new PDO($dsn, $usuario, $contrasena, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Iniciar transacción para rollback seguro
        $pdo->beginTransaction();
        
        // Leer archivo SQL del esquema
        $sqlFile = __DIR__ . '/promotores_schema.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Archivo de esquema no encontrado: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Limpiar comentarios y dividir en statements
        $statements = [];
        $lines = explode("\n", $sql);
        $currentStatement = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Saltar líneas vacías y comentarios
            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }
            
            $currentStatement .= $line . "\n";
            
            // Si termina en ;, es un statement completo
            if (substr($line, -1) === ';') {
                $statements[] = trim($currentStatement);
                $currentStatement = '';
            }
        }
        
        // Ejecutar cada statement por separado
        $executed = 0;
        $results = [];
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                    
                    // Identificar tipo de statement para logging
                    if (stripos($statement, 'CREATE TABLE') !== false) {
                        preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(\w+)/i', $statement, $matches);
                        $table = $matches[1] ?? 'unknown';
                        $results[] = "✅ Tabla '$table' creada/verificada";
                    } elseif (stripos($statement, 'INSERT') !== false) {
                        $results[] = "✅ Datos iniciales insertados";
                    } else {
                        $results[] = "✅ Statement ejecutado";
                    }
                    
                } catch (PDOException $e) {
                    // Log del error pero continuar con otros statements
                    $error = "⚠️ Warning en statement: " . $e->getMessage();
                    $results[] = $error;
                    error_log("[PROMOTORES_INSTALL] $error");
                }
            }
        }
        
        // Verificar que las tablas principales se crearon
        $tablesRequired = ['promotores', 'referidos', 'comisiones', 'promotor_config'];
        $tablesFound = [];
        
        foreach ($tablesRequired as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->rowCount() > 0) {
                $tablesFound[] = $table;
            }
        }
        
        if (count($tablesFound) === count($tablesRequired)) {
            // Todo OK - commit de la transacción
            $pdo->commit();
            
            $result = [
                'success' => true,
                'message' => 'Esquema de promotores instalado exitosamente',
                'details' => [
                    'statements_executed' => $executed,
                    'tables_created' => $tablesFound,
                    'execution_log' => $results
                ]
            ];
            
        } else {
            // Faltan tablas - rollback
            $pdo->rollback();
            
            $missing = array_diff($tablesRequired, $tablesFound);
            $result = [
                'success' => false,
                'message' => 'Instalación incompleta - faltan tablas: ' . implode(', ', $missing),
                'details' => [
                    'statements_executed' => $executed,
                    'tables_found' => $tablesFound,
                    'tables_missing' => $missing,
                    'execution_log' => $results
                ]
            ];
        }
        
        // Log del resultado completo
        error_log("[PROMOTORES_INSTALL] " . json_encode($result));
        
        return $result;
        
    } catch (Exception $e) {
        // Rollback en caso de error crítico
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollback();
        }
        
        $result = [
            'success' => false,
            'message' => 'Error instalando esquema: ' . $e->getMessage(),
            'details' => [
                'error_type' => get_class($e),
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile()
            ]
        ];
        
        error_log("[PROMOTORES_INSTALL ERROR] " . json_encode($result));
        return $result;
    }
}

// Procesar solicitud de instalación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    
    // Verificación CSRF básica
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        die('Token CSRF inválido');
    }
    
    $result = instalarEsquemaPromotores();
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Generar token CSRF para el formulario
$_SESSION['csrf_token'] = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - Módulo Promotores</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .log { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>🔧 Instalador del Módulo de Promotores</h1>
    
    <div class="warning">
        <h3>⚠️ ATENCIÓN - INSTALACIÓN DE BASE DE DATOS</h3>
        <p><strong>Este proceso creará las tablas necesarias para el módulo de promotores:</strong></p>
        <ul>
            <li><code>promotores</code> - Códigos únicos y datos de promotores</li>
            <li><code>referidos</code> - Seguimiento de visitas y registros</li>
            <li><code>comisiones</code> - Gestión de pagos y estados</li>
            <li><code>promotor_config</code> - Configuraciones del módulo</li>
        </ul>
        
        <p><strong>Características de seguridad:</strong></p>
        <ul>
            <li>✅ Usa <code>IF NOT EXISTS</code> - no rompe si ya existen</li>
            <li>✅ Transacciones con rollback automático en caso de error</li>
            <li>✅ Logging completo de todos los pasos ejecutados</li>
            <li>✅ Verificación post-instalación de integridad</li>
        </ul>
        
        <p><strong>Solo ejecutar si:</strong> Eres administrador y necesitas activar el módulo de promotores.</p>
    </div>
    
    <div id="result" style="display: none;"></div>
    
    <form id="installForm" method="POST">
        <input type="hidden" name="action" value="install">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <button type="submit" class="btn" id="installBtn">
            🚀 Instalar Esquema de Promotores
        </button>
        
        <button type="button" class="btn-danger" onclick="history.back()">
            ❌ Cancelar
        </button>
    </form>
    
    <div style="margin-top: 30px; font-size: 0.9rem; color: #666;">
        <h4>📋 Información Técnica</h4>
        <p><strong>Usuario:</strong> <?= htmlspecialchars($_SESSION['usuario']['email']) ?></p>
        <p><strong>Rol:</strong> <?= strtoupper($_SESSION['usuario']['rol']) ?></p>
        <p><strong>Archivo:</strong> db/install_promotores.php</p>
        <p><strong>Esquema:</strong> db/promotores_schema.sql</p>
    </div>

    <script>
        document.getElementById('installForm').onsubmit = function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('installBtn');
            const result = document.getElementById('result');
            
            // Deshabilitar botón durante instalación
            btn.disabled = true;
            btn.textContent = '⏳ Instalando...';
            
            // Ocultar resultados anteriores
            result.style.display = 'none';
            
            // Enviar solicitud AJAX
            fetch('', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                result.style.display = 'block';
                
                if (data.success) {
                    result.className = 'success';
                    result.innerHTML = `
                        <h3>✅ Instalación Exitosa</h3>
                        <p>${data.message}</p>
                        <div class="log">${JSON.stringify(data.details, null, 2)}</div>
                    `;
                } else {
                    result.className = 'error';
                    result.innerHTML = `
                        <h3>❌ Error en Instalación</h3>
                        <p>${data.message}</p>
                        <div class="log">${JSON.stringify(data.details, null, 2)}</div>
                    `;
                }
                
                // Rehabilitar botón
                btn.disabled = false;
                btn.textContent = '🚀 Instalar Esquema de Promotores';
            })
            .catch(error => {
                result.style.display = 'block';
                result.className = 'error';
                result.innerHTML = `
                    <h3>❌ Error de Comunicación</h3>
                    <p>Error ejecutando instalación: ${error.message}</p>
                `;
                
                btn.disabled = false;
                btn.textContent = '🚀 Instalar Esquema de Promotores';
            });
        };
    </script>
</body>
</html>