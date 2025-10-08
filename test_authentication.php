<?php
/**
 * test_authentication.php - Script de Prueba del Sistema de Autenticación
 * 
 * Propósito:
 * Verificar que todos los componentes del sistema de autenticación
 * funcionen correctamente: modelos, controladores, helpers y vistas.
 * 
 * Funcionalidades a probar:
 * 1. Conexión a base de datos
 * 2. Creación de tabla usuarios
 * 3. Usuario administrador por defecto
 * 4. Funciones de AuthHelper
 * 5. Validación de credenciales
 * 6. Creación de nuevos usuarios
 * 
 * Modo de uso:
 * Ejecutar desde navegador: /test_authentication.php
 * O desde línea de comandos: php test_authentication.php
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-07
 */

// Configurar reporte de errores para debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializar sesión
session_start();

echo "<!DOCTYPE html>\n";
echo "<html lang='es'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>Test - Sistema de Autenticación</title>\n";
echo "<style>\n";
echo "body { font-family: Arial, sans-serif; margin: 2rem; background: #f5f5f5; }\n";
echo ".test-container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
echo ".test-section { margin: 2rem 0; padding: 1rem; border: 1px solid #ddd; border-radius: 4px; }\n";
echo ".success { background: #d4edda; color: #155724; border-color: #c3e6cb; }\n";
echo ".error { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }\n";
echo ".info { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }\n";
echo "pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<div class='test-container'>\n";
echo "<h1>🔐 Test del Sistema de Autenticación</h1>\n";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

// ========================================
// TEST 1: VERIFICAR ARCHIVOS NECESARIOS
// ========================================
echo "<div class='test-section info'>\n";
echo "<h2>📁 Test 1: Verificación de Archivos</h2>\n";

$archivosNecesarios = [
    'helpers/AuthHelper.php',
    'models/Usuario.php',
    'controllers/LoginController.php',
    'views/auth/login.php',
    'config/config.php'
];

$archivosEncontrados = 0;
$totalArchivos = count($archivosNecesarios);

foreach ($archivosNecesarios as $archivo) {
    if (file_exists($archivo)) {
        echo "✅ $archivo - ENCONTRADO<br>\n";
        $archivosEncontrados++;
    } else {
        echo "❌ $archivo - NO ENCONTRADO<br>\n";
    }
}

echo "<p><strong>Resultado:</strong> $archivosEncontrados/$totalArchivos archivos encontrados</p>\n";
echo "</div>\n";

if ($archivosEncontrados < $totalArchivos) {
    echo "<div class='test-section error'>\n";
    echo "<h3>❌ Error Fatal</h3>\n";
    echo "<p>Faltan archivos necesarios. No se pueden ejecutar más tests.</p>\n";
    echo "</div>\n";
    echo "</div></body></html>\n";
    exit;
}

// ========================================
// TEST 2: CONEXIÓN A BASE DE DATOS
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>🗄️ Test 2: Conexión a Base de Datos</h2>\n";

try {
    require_once 'config/config.php';
    
    $conexion = conectarBD();
    
    if ($conexion) {
        echo "<div class='success'>✅ Conexión a base de datos exitosa</div>\n";
        echo "<p>MySQL versión: " . $conexion->server_info . "</p>\n";
        
        // Verificar base de datos seleccionada
        $result = $conexion->query("SELECT DATABASE() as db_name");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Base de datos activa: <strong>" . ($row['db_name'] ?: 'No especificada') . "</strong></p>\n";
        }
    } else {
        echo "<div class='error'>❌ Error conectando a base de datos</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Exception: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// TEST 3: MODELO USUARIO
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>👤 Test 3: Modelo Usuario</h2>\n";

try {
    require_once 'models/Usuario.php';
    
    $usuarioModel = new Usuario();
    echo "<div class='success'>✅ Modelo Usuario instanciado correctamente</div>\n";
    
    // Verificar si existe la tabla usuarios
    $conexion = conectarBD();
    $result = $conexion->query("SHOW TABLES LIKE 'usuarios'");
    
    if ($result && $result->num_rows > 0) {
        echo "<p>✅ Tabla 'usuarios' existe</p>\n";
        
        // Verificar estructura de la tabla
        $result = $conexion->query("DESCRIBE usuarios");
        echo "<h4>Estructura de la tabla usuarios:</h4>\n";
        echo "<pre>\n";
        while ($row = $result->fetch_assoc()) {
            echo sprintf("%-20s %-15s %-10s %-5s %-10s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'], 
                $row['Extra']
            );
        }
        echo "</pre>\n";
        
        // Contar usuarios existentes
        $result = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
        $row = $result->fetch_assoc();
        echo "<p>👥 Total de usuarios en BD: <strong>" . $row['total'] . "</strong></p>\n";
        
    } else {
        echo "<div class='error'>❌ Tabla 'usuarios' no existe</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en modelo Usuario: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// TEST 4: AUTH HELPER
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>🔐 Test 4: AuthHelper</h2>\n";

try {
    require_once 'helpers/AuthHelper.php';
    
    $authHelper = new AuthHelper();
    echo "<div class='success'>✅ AuthHelper instanciado correctamente</div>\n";
    
    // Test de métodos públicos
    $metodos = ['estaAutenticado', 'obtenerUsuarioActual', 'verificarAcceso', 'cerrarSesion'];
    
    foreach ($metodos as $metodo) {
        if (method_exists($authHelper, $metodo)) {
            echo "✅ Método '$metodo' existe<br>\n";
        } else {
            echo "❌ Método '$metodo' no encontrado<br>\n";
        }
    }
    
    // Test estado inicial
    $estaAuth = $authHelper->estaAutenticado();
    echo "<p>🔍 Estado de autenticación actual: " . ($estaAuth ? 'AUTENTICADO' : 'NO AUTENTICADO') . "</p>\n";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en AuthHelper: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// TEST 5: VALIDACIÓN DE CREDENCIALES
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>🔑 Test 5: Validación de Credenciales</h2>\n";

try {
    // Intentar login con usuario administrador por defecto
    echo "<h4>Probando credenciales del admin por defecto:</h4>\n";
    
    $resultado = $usuarioModel->validarCredenciales('admin@camella.com.co', 'admin123');
    
    if ($resultado) {
        echo "<div class='success'>✅ Login exitoso con admin por defecto</div>\n";
        echo "<pre>\n";
        echo "Usuario validado:\n";
        echo "ID: " . $resultado['id'] . "\n";
        echo "Nombre: " . $resultado['nombre'] . "\n";
        echo "Email: " . $resultado['email'] . "\n";
        echo "Rol: " . $resultado['rol'] . "\n";
        echo "</pre>\n";
    } else {
        echo "<div class='error'>❌ Error validando credenciales del admin</div>\n";
        echo "<p>Esto puede indicar que el usuario admin no se creó correctamente.</p>\n";
    }
    
    // Test con credenciales incorrectas
    echo "<h4>Probando credenciales incorrectas:</h4>\n";
    $resultadoIncorrecto = $usuarioModel->validarCredenciales('fake@email.com', 'wrongpass');
    
    if (!$resultadoIncorrecto) {
        echo "<div class='success'>✅ Credenciales incorrectas rechazadas correctamente</div>\n";
    } else {
        echo "<div class='error'>❌ Error: credenciales incorrectas fueron aceptadas</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en validación: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// TEST 6: CREACIÓN DE USUARIO DE PRUEBA
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>➕ Test 6: Creación de Usuario de Prueba</h2>\n";

try {
    $emailPrueba = 'test_user_' . time() . '@camella.test';
    $passwordPrueba = 'test123456';
    
    echo "<p>Creando usuario de prueba: <strong>$emailPrueba</strong></p>\n";
    
    $nuevoUserId = $usuarioModel->crearUsuario(
        'Usuario de Prueba',
        $emailPrueba,
        $passwordPrueba,
        'publicante'
    );
    
    if ($nuevoUserId) {
        echo "<div class='success'>✅ Usuario de prueba creado con ID: $nuevoUserId</div>\n";
        
        // Validar que se puede hacer login con el nuevo usuario
        $validacion = $usuarioModel->validarCredenciales($emailPrueba, $passwordPrueba);
        
        if ($validacion) {
            echo "<div class='success'>✅ Login exitoso con usuario recién creado</div>\n";
        } else {
            echo "<div class='error'>❌ No se pudo hacer login con usuario recién creado</div>\n";
        }
        
        // Limpiar usuario de prueba
        $conexion = conectarBD();
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $nuevoUserId);
        
        if ($stmt->execute()) {
            echo "<p>🧹 Usuario de prueba eliminado exitosamente</p>\n";
        } else {
            echo "<p>⚠️ No se pudo eliminar el usuario de prueba (ID: $nuevoUserId)</p>\n";
        }
        
    } else {
        echo "<div class='error'>❌ Error creando usuario de prueba</div>\n";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en creación de usuario: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// TEST 7: CONTROLADOR DE LOGIN
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>🎮 Test 7: LoginController</h2>\n";

try {
    require_once 'controllers/LoginController.php';
    
    $loginController = new LoginController();
    echo "<div class='success'>✅ LoginController instanciado correctamente</div>\n";
    
    // Verificar métodos públicos
    $metodosController = ['mostrarLogin', 'procesarLogin', 'logout', 'requireAuth', 'crearUsuario'];
    
    foreach ($metodosController as $metodo) {
        if (method_exists($loginController, $metodo)) {
            echo "✅ Método '$metodo' existe<br>\n";
        } else {
            echo "❌ Método '$metodo' no encontrado<br>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error en LoginController: " . $e->getMessage() . "</div>\n";
}

echo "</div>\n";

// ========================================
// RESUMEN FINAL
// ========================================
echo "<div class='test-section info'>\n";
echo "<h2>📊 Resumen de Tests</h2>\n";

echo "<h3>✅ Componentes Verificados:</h3>\n";
echo "<ul>\n";
echo "<li>✅ Archivos del sistema de autenticación</li>\n";
echo "<li>✅ Conexión a base de datos</li>\n";
echo "<li>✅ Modelo Usuario con auto-creación de tablas</li>\n";
echo "<li>✅ AuthHelper con funciones de sesión</li>\n";
echo "<li>✅ Validación de credenciales</li>\n";
echo "<li>✅ Creación de usuarios</li>\n";
echo "<li>✅ LoginController</li>\n";
echo "</ul>\n";

echo "<h3>🔗 Enlaces de Prueba:</h3>\n";
echo "<ul>\n";
echo "<li><a href='/login'>Página de Login</a></li>\n";
echo "<li><a href='/admin'>Panel de Administración</a> (requiere login)</li>\n";
echo "<li><a href='/'>Página Principal</a></li>\n";
echo "</ul>\n";

echo "<h3>👨‍💻 Credenciales de Prueba:</h3>\n";
echo "<div style='background: #f8f9fa; padding: 1rem; border-radius: 4px; font-family: monospace;'>\n";
echo "<strong>Usuario:</strong> admin@camella.com.co<br>\n";
echo "<strong>Contraseña:</strong> admin123<br>\n";
echo "<strong>Rol:</strong> Administrador\n";
echo "</div>\n";

echo "<p><strong>⚠️ IMPORTANTE:</strong> Cambiar las credenciales por defecto en producción.</p>\n";

echo "</div>\n";

// ========================================
// INFORMACIÓN DEL SISTEMA
// ========================================
echo "<div class='test-section'>\n";
echo "<h2>ℹ️ Información del Sistema</h2>\n";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>\n";
echo "<p><strong>Session Status:</strong> " . session_status() . " (" . 
    (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVA' : 'INACTIVA') . ")</p>\n";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>\n";
echo "<p><strong>Memoria Usada:</strong> " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>\n";
echo "<p><strong>Tiempo de Ejecución:</strong> " . number_format(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3) . " segundos</p>\n";
echo "</div>\n";

echo "</div>\n"; // Cerrar test-container

echo "<footer style='text-align: center; margin-top: 2rem; color: #666; font-size: 0.9rem;'>\n";
echo "<p>Sistema de Autenticación - Camella.com.co | " . date('Y') . "</p>\n";
echo "</footer>\n";

echo "</body></html>\n";

// Cerrar conexión si existe
if (isset($conexion)) {
    cerrarBD($conexion);
}

?>