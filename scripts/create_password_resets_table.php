<?php
/**
 * Script para crear tabla password_resets
 * 
 * PROPÓSITO: Crear la tabla de tokens de reset de contraseña de forma idempotente
 * 
 * CARACTERÍSTICAS:
 * - token_hash: Guardamos hash SHA-256 del token (no token plano por seguridad)
 * - expires_at: Tiempo de expiración (30 minutos por defecto)
 * - used_at: Marca si el token ya fue usado (un solo uso)
 * - ip/user_agent: Para auditoría y detección de uso sospechoso
 * 
 * MANTENIMIENTO FUTURO:
 * - Crear CRON para eliminar registros viejos: DELETE FROM password_resets WHERE expires_at < NOW() - INTERVAL 24 HOUR
 * - Considerar índice compuesto (email, expires_at) si el volumen crece
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require_once __DIR__ . '/../bootstrap.php';

try {
    $pdo = getPDO();
    
    // LÍNEA CLAVE: Crear tabla de forma idempotente (IF NOT EXISTS)
    $sql = "
    CREATE TABLE IF NOT EXISTS password_resets (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      email VARCHAR(190) NOT NULL,
      token_hash CHAR(64) NOT NULL,           -- hash SHA-256 del token
      expires_at DATETIME NOT NULL,           -- ahora() + 30 minutos
      used_at DATETIME NULL,
      ip VARBINARY(16) NULL,
      user_agent VARCHAR(255) NULL,
      creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX (email),
      INDEX (token_hash)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    
    echo "Tabla password_resets creada/verificada correctamente.\n";
    
    // Verificar estructura
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_resets'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla password_resets existe en la base de datos.\n";
    } else {
        echo "✗ Error: Tabla password_resets no fue creada.\n";
    }
    
} catch (Exception $e) {
    echo "Error creando tabla: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Script completado.\n";
?>