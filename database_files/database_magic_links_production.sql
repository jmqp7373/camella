-- =====================================================
-- SCRIPT DE PRODUCCIÓN: Crear tabla magic_links
-- =====================================================
-- Este script debe ejecutarse en la base de datos de PRODUCCIÓN
-- Base de datos: u179023609_camella_db (Hostinger)
-- =====================================================

-- Verificar si la tabla existe
SELECT 'Verificando existencia de tabla magic_links...' as status;

-- Crear tabla magic_links si no existe
CREATE TABLE IF NOT EXISTS magic_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usos INT DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_phone (phone),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Verificar que la tabla se creó correctamente
SELECT 'Tabla magic_links creada exitosamente' as status;

-- Mostrar estructura de la tabla
DESCRIBE magic_links;

-- Verificar datos existentes (debe estar vacía)
SELECT COUNT(*) as total_registros FROM magic_links;

SELECT '¡Script ejecutado correctamente!' as final_status;
