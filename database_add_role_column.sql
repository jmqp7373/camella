-- Agregar columna 'role' a la tabla users
-- Para sistema de roles: admin, promotor, publicante

ALTER TABLE users 
ADD COLUMN role ENUM('publicante', 'promotor', 'admin') DEFAULT 'publicante' 
AFTER phone;

-- Agregar índice para búsquedas por rol
ALTER TABLE users ADD INDEX idx_role (role);

-- Actualizar usuarios existentes (opcional)
-- UPDATE users SET role = 'publicante' WHERE role IS NULL;
