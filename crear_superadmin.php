<?php
/**
 * Script para crear usuario superadmin@camella.com.co
 * 
 * EJECUTAR UNA SOLA VEZ para crear el usuario administrador principal
 * 
 * Credenciales:
 * - Email: superadmin@camella.com.co
 * - Password: Camella2025*
 * - Rol: admin
 */

require_once __DIR__ . '/config/config.php';

try {
    // Conectar a la base de datos
    $pdo = getPDO();
    
    // Datos del superadmin
    $email = 'superadmin@camella.com.co';
    $password = 'Camella2025*';
    $nombre = 'Super Administrador';
    $rol = 'admin';
    
    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "✅ El usuario superadmin@camella.com.co ya existe en la base de datos.\n";
        
        // Actualizar la contraseña por si acaso
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $updateStmt = $pdo->prepare("UPDATE usuarios SET password = :password, activo = 1 WHERE email = :email");
        $updateStmt->bindParam(':password', $hashedPassword);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->execute();
        
        echo "✅ Contraseña actualizada y usuario activado.\n";
    } else {
        // Crear el usuario superadmin
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $insertStmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, activo) 
            VALUES (:nombre, :email, :password, :rol, 1)
        ");
        
        $insertStmt->bindParam(':nombre', $nombre);
        $insertStmt->bindParam(':email', $email);
        $insertStmt->bindParam(':password', $hashedPassword);
        $insertStmt->bindParam(':rol', $rol);
        
        if ($insertStmt->execute()) {
            $userId = $pdo->lastInsertId();
            echo "✅ Usuario superadmin creado exitosamente!\n";
            echo "   ID: $userId\n";
            echo "   Email: $email\n";
            echo "   Password: $password\n";
            echo "   Rol: $rol\n";
        } else {
            echo "❌ Error creando el usuario superadmin\n";
        }
    }
    
    // Verificar la contraseña
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        echo "✅ Verificación de contraseña exitosa - Login funcionará correctamente\n";
    } else {
        echo "❌ Error en la verificación de contraseña\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

echo "\n=== CREDENCIALES DE LOGIN ===\n";
echo "Email: superadmin@camella.com.co\n";
echo "Password: Camella2025*\n";
echo "URL: /login\n";
?>