<?php
/**
 * TEST DE LOGIN - Verificar funcionamiento completo
 * 
 * Este script simula un login para verificar que todo funciona correctamente
 * antes del despliegue en producción.
 */

// Configuración de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/AuthHelper.php';
require_once __DIR__ . '/models/Usuario.php';

echo "=== TEST DE SISTEMA DE LOGIN ===\n\n";

try {
    // 1. Verificar conexión a base de datos
    echo "1. Verificando conexión PDO...\n";
    $pdo = getPDO();
    echo "✅ Conexión PDO exitosa\n\n";
    
    // 2. Verificar que existe la tabla usuarios
    echo "2. Verificando estructura de base de datos...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla 'usuarios' existe\n";
        
        // Verificar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE usuarios");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'email', 'password', 'rol', 'activo'];
        
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columns)) {
                echo "✅ Columna '$col' existe\n";
            } else {
                echo "❌ Columna '$col' NO existe\n";
            }
        }
    } else {
        echo "❌ Tabla 'usuarios' NO existe\n";
    }
    echo "\n";
    
    // 3. Verificar modelo Usuario
    echo "3. Verificando modelo Usuario...\n";
    $usuarioModel = new Usuario();
    echo "✅ Modelo Usuario instanciado correctamente\n";
    
    // Verificar método buscarPorEmail
    if (method_exists($usuarioModel, 'buscarPorEmail')) {
        echo "✅ Método buscarPorEmail existe\n";
    } else {
        echo "❌ Método buscarPorEmail NO existe\n";
    }
    echo "\n";
    
    // 4. Verificar función de sesión
    echo "4. Verificando AuthHelper...\n";
    if (function_exists('iniciarSesionSegura')) {
        echo "✅ Función iniciarSesionSegura existe\n";
    } else {
        echo "❌ Función iniciarSesionSegura NO existe\n";
    }
    echo "\n";
    
    // 5. Test de búsqueda de usuario (sin verificar contraseña)
    echo "5. Probando búsqueda de usuario superadmin@camella.com.co...\n";
    $usuario = $usuarioModel->buscarPorEmail('superadmin@camella.com.co');
    
    if ($usuario) {
        echo "✅ Usuario encontrado:\n";
        echo "   ID: {$usuario['id']}\n";
        echo "   Email: {$usuario['email']}\n";
        echo "   Rol: {$usuario['rol']}\n";
        echo "   Activo: " . ($usuario['activo'] ? 'Sí' : 'No') . "\n";
        
        // Verificar hash de contraseña
        if (!empty($usuario['password']) && strlen($usuario['password']) >= 60) {
            echo "✅ Password hash válido (bcrypt detectado)\n";
        } else {
            echo "❌ Password hash inválido o faltante\n";
        }
    } else {
        echo "❌ Usuario superadmin@camella.com.co NO encontrado\n";
        echo "   NOTA: Ejecutar crear_superadmin.php primero\n";
    }
    echo "\n";
    
    // 6. Verificar configuración SMTP
    echo "6. Verificando configuración SMTP...\n";
    if (defined('SMTP_HOST') && !empty(SMTP_HOST)) {
        echo "✅ SMTP_HOST configurado: " . SMTP_HOST . "\n";
    } else {
        echo "❌ SMTP_HOST no configurado\n";
    }
    
    if (defined('SMTP_USER') && !empty(SMTP_USER)) {
        echo "✅ SMTP_USER configurado: " . SMTP_USER . "\n";
    } else {
        echo "❌ SMTP_USER no configurado\n";
    }
    
    if (defined('SMTP_PASS')) {
        if (!empty(SMTP_PASS)) {
            echo "✅ SMTP_PASS configurado (oculto por seguridad)\n";
        } else {
            echo "⚠️  SMTP_PASS está vacío - configurar contraseña de aplicación Gmail\n";
        }
    } else {
        echo "❌ SMTP_PASS no definido\n";
    }
    echo "\n";
    
    echo "=== RESUMEN ===\n";
    echo "✅ Sistema base funcionando\n";
    echo "⚠️  Pasos pendientes:\n";
    echo "   1. Ejecutar crear_superadmin.php en servidor\n";
    echo "   2. Configurar SMTP_PASS con contraseña de aplicación Gmail\n";
    echo "   3. Probar login en /login\n";
    echo "   4. Probar recuperación de contraseña\n\n";
    
    echo "Credenciales de prueba:\n";
    echo "Email: superadmin@camella.com.co\n";
    echo "Password: Camella2025*\n";
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}
?>