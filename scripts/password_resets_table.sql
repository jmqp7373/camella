-- Script SQL para crear tabla password_resets
-- Ejecutar en phpMyAdmin o cliente MySQL de GoDaddy

CREATE TABLE IF NOT EXISTS password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  ip VARBINARY(16) NULL,
  user_agent VARCHAR(255) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (email),
  INDEX (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;