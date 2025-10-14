-- Tabla de historial de códigos de verificación
-- Guarda todos los códigos generados y usados para auditoría

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
