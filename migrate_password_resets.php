<?php
/**
 * Script de migración para actualizar tabla password_resets a esquema HMAC
 * 
 * PROPÓSITO: Actualizar la estructura de la tabla password_resets para
 * soportar tokens HMAC seguros con expiración y uso único
 * 
 * CAMBIOS:
 * - token VARCHAR(255) → token_hash VARCHAR(64) 
 * - Agregar expires_at TIMESTAMP
 * - Agregar used_at TIMESTAMP
 * - Limpiar datos existentes (por seguridad)
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-09
 */

require_once __DIR__ . '/bootstrap.php';

try {
    echo "🔄 Iniciando migración de tabla password_resets...\n";
    
    $pdo = getPDO();
    
    // Verificar si la tabla existe y tiene la estructura antigua
    $stmt = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'token'");
    $hasOldToken = $stmt->fetch() !== false;
    
    if ($hasOldToken) {
        echo "📋 Detectada estructura antigua. Migrando...\n";
        
        // Limpiar todos los tokens existentes (por seguridad, ya no son válidos)
        $pdo->exec("DELETE FROM password_resets");
        echo "🗑️  Tokens existentes eliminados (no compatibles con HMAC)\n";
        
        // Eliminar columna antigua
        $pdo->exec("ALTER TABLE password_resets DROP COLUMN token");
        echo "❌ Columna 'token' eliminada\n";
        
        // Agregar nuevas columnas
        $pdo->exec("ALTER TABLE password_resets ADD COLUMN token_hash VARCHAR(64) NOT NULL AFTER email");
        $pdo->exec("ALTER TABLE password_resets ADD COLUMN expires_at TIMESTAMP NULL AFTER token_hash");  
        $pdo->exec("ALTER TABLE password_resets ADD COLUMN used_at TIMESTAMP NULL AFTER expires_at");
        echo "✅ Nuevas columnas agregadas: token_hash, expires_at, used_at\n";
        
        // Agregar índices
        $pdo->exec("CREATE INDEX idx_token_hash ON password_resets (token_hash)");
        echo "🔍 Índice agregado en token_hash\n";
        
        echo "✅ Migración completada exitosamente\n";
        
    } else {
        // Verificar si ya tiene el esquema nuevo
        $stmt = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'token_hash'");
        $hasNewSchema = $stmt->fetch() !== false;
        
        if ($hasNewSchema) {
            echo "✅ La tabla ya tiene el esquema HMAC actualizado\n";
        } else {
            echo "⚠️  Creando tabla password_resets con esquema HMAC...\n";
            
            $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                token_hash VARCHAR(64) NOT NULL,
                expires_at TIMESTAMP NULL,
                used_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (email),
                INDEX (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($sql);
            echo "✅ Tabla creada con esquema HMAC\n";
        }
    }
    
    // Verificar estructura final
    echo "\n📊 Estructura final de la tabla:\n";
    $stmt = $pdo->query("DESCRIBE password_resets");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo sprintf("   %-15s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    
    echo "\n🎉 Sistema de recuperación de contraseñas actualizado con seguridad HMAC\n";
    echo "🔐 Los tokens ahora son imposibles de falsificar\n";
    echo "⏰ Expiración automática después de 30 minutos\n";
    echo "🚫 Tokens de un solo uso para máxima seguridad\n";
    
} catch (Exception $e) {
    echo "❌ Error en la migración: " . $e->getMessage() . "\n";
    exit(1);
}
?>