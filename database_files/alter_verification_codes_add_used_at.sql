-- Agregar columna used_at para rastrear cuándo se usó un código
ALTER TABLE verification_codes
ADD COLUMN used_at TIMESTAMP NULL DEFAULT NULL AFTER expires_at,
ADD INDEX idx_used_at (used_at);
