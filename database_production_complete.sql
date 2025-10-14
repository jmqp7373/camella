-- ============================================
-- SCRIPT COMPLETO DE TABLAS PARA PRODUCCIÓN
-- Camella.com.co - Sistema de Autenticación SMS
-- ============================================

-- 1. TABLA DE USUARIOS
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA DE CÓDIGOS DE VERIFICACIÓN (temporal)
CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(6) NOT NULL,
    magic_token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_phone_code (phone, code),
    INDEX idx_magic_token (magic_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA DE HISTORIAL DE CÓDIGOS (auditoría)
CREATE TABLE IF NOT EXISTS verification_codes_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(6) NOT NULL,
    magic_token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('created', 'used', 'expired', 'failed') DEFAULT 'created',
    user_id INT NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    sms_sid VARCHAR(100) NULL DEFAULT NULL,
    INDEX idx_phone (phone),
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- VERIFICAR TABLAS CREADAS
-- ============================================
-- Ejecuta esta consulta después para verificar:
-- SHOW TABLES;

-- ============================================
-- VERIFICAR ESTRUCTURA DE CADA TABLA
-- ============================================
-- DESCRIBE users;
-- DESCRIBE verification_codes;
-- DESCRIBE verification_codes_history;
