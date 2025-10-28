-- Tabla para Magic Links de login automático
CREATE TABLE IF NOT EXISTS `magic_links` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `token` VARCHAR(64) NOT NULL UNIQUE COMMENT 'Token único del magic link',
  `phone` VARCHAR(20) NOT NULL COMMENT 'Teléfono del usuario',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `usos` INT(11) DEFAULT 0 COMMENT 'Contador de usos del link',
  PRIMARY KEY (`id`),
  INDEX `idx_token` (`token`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Magic links para login automático';
