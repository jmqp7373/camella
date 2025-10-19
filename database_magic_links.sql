-- Tabla para Magic Links de login automático
CREATE TABLE IF NOT EXISTS `magic_links` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(20) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `code` VARCHAR(6) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `usos` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`token`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Magic links para login automático';
