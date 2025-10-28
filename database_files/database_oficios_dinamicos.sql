-- =====================================================
-- SISTEMA DE GESTIÓN DINÁMICA DE OFICIOS Y CATEGORÍAS
-- Camella.com.co - Base de datos
-- =====================================================

-- =====================================================
-- TABLA: categorias
-- Almacena las categorías principales de oficios
-- =====================================================
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL COMMENT 'Nombre de la categoría (ej: Construcción y Obras)',
  `descripcion` TEXT DEFAULT NULL COMMENT 'Descripción opcional de la categoría',
  `icono` VARCHAR(100) DEFAULT NULL COMMENT 'Clase de Font Awesome (ej: fas fa-hard-hat)',
  `orden` INT(11) DEFAULT 0 COMMENT 'Orden de visualización en el sitio',
  `activo` TINYINT(1) DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_activo` (`activo`),
  INDEX `idx_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de oficios del sistema';

-- =====================================================
-- TABLA: oficios
-- Almacena los oficios individuales dentro de cada categoría
-- =====================================================
CREATE TABLE IF NOT EXISTS `oficios` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` INT(11) NOT NULL COMMENT 'FK a categorias.id',
  `titulo` VARCHAR(255) NOT NULL COMMENT 'Nombre del oficio (ej: Plomero)',
  `popular` TINYINT(1) DEFAULT 0 COMMENT '1 = popular (candela encendida), 0 = no popular',
  `orden` INT(11) DEFAULT 0 COMMENT 'Orden dentro de la categoría',
  `activo` TINYINT(1) DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_categoria` (`categoria_id`),
  INDEX `idx_popular` (`popular`),
  INDEX `idx_activo` (`activo`),
  INDEX `idx_categoria_popular` (`categoria_id`, `popular`),
  CONSTRAINT `fk_oficios_categoria` 
    FOREIGN KEY (`categoria_id`) 
    REFERENCES `categorias` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Oficios individuales dentro de cada categoría';

-- =====================================================
-- VERIFICAR ESTRUCTURA DE LAS TABLAS
-- =====================================================

-- Ver estructura de categorias
DESCRIBE categorias;

-- Ver estructura de oficios
DESCRIBE oficios;

-- =====================================================
-- CONSULTAS ÚTILES PARA VERIFICACIÓN
-- =====================================================

-- Total de categorías activas
SELECT COUNT(*) as total_categorias 
FROM categorias 
WHERE activo = 1;

-- Total de oficios activos
SELECT COUNT(*) as total_oficios 
FROM oficios 
WHERE activo = 1;

-- Oficios por categoría (con conteo)
SELECT 
    c.id,
    c.nombre as categoria,
    c.icono,
    COUNT(o.id) as total_oficios,
    SUM(CASE WHEN o.popular = 1 THEN 1 ELSE 0 END) as oficios_populares
FROM categorias c
LEFT JOIN oficios o ON o.categoria_id = c.id AND o.activo = 1
WHERE c.activo = 1
GROUP BY c.id, c.nombre, c.icono
ORDER BY c.orden ASC, c.nombre ASC;

-- Oficios populares (candelas encendidas)
SELECT 
    o.id,
    o.titulo,
    c.nombre as categoria,
    o.popular,
    o.created_at
FROM oficios o
INNER JOIN categorias c ON o.categoria_id = c.id
WHERE o.popular = 1 
  AND o.activo = 1
ORDER BY c.nombre, o.titulo;

-- Últimos oficios modificados
SELECT 
    o.id,
    o.titulo,
    c.nombre as categoria,
    o.popular,
    o.updated_at
FROM oficios o
INNER JOIN categorias c ON o.categoria_id = c.id
WHERE o.activo = 1
ORDER BY o.updated_at DESC
LIMIT 20;

-- =====================================================
-- EJEMPLO: AGREGAR UNA NUEVA CATEGORÍA
-- =====================================================

INSERT INTO categorias (nombre, descripcion, icono, orden, activo)
VALUES (
    'Servicios de Tecnología',
    'Profesionales en tecnología, programación y soporte técnico',
    'fas fa-laptop-code',
    100,
    1
);

-- =====================================================
-- EJEMPLO: AGREGAR UN NUEVO OFICIO
-- =====================================================

INSERT INTO oficios (categoria_id, titulo, popular, orden, activo)
VALUES (
    1,  -- ID de la categoría (ajustar según tu BD)
    'Técnico en redes',
    0,  -- No popular por defecto
    0,
    1
);

-- =====================================================
-- EJEMPLO: MARCAR UN OFICIO COMO POPULAR
-- =====================================================

UPDATE oficios 
SET popular = 1, updated_at = NOW() 
WHERE id = 5;

-- =====================================================
-- EJEMPLO: DESACTIVAR UN OFICIO (SOFT DELETE)
-- =====================================================

UPDATE oficios 
SET activo = 0, updated_at = NOW() 
WHERE id = 10;

-- =====================================================
-- CONSULTA PARA VISTA ADMIN (categoriasOficios.php)
-- =====================================================

-- Esta es la consulta que usa la vista administrativa
SELECT 
    o.id,
    o.titulo as nombre,
    o.popular,
    o.categoria_id,
    o.activo
FROM oficios o
WHERE o.categoria_id = ? 
  AND o.activo = 1 
ORDER BY o.popular DESC, o.titulo ASC;

-- =====================================================
-- CONSULTA PARA VISTA PÚBLICA (home.php)
-- =====================================================

-- Categorías con sus oficios para la vista pública
SELECT 
    c.id,
    c.nombre,
    c.descripcion,
    c.icono,
    c.activo,
    COALESCE(COUNT(o.id), 0) AS total_oficios
FROM categorias c
LEFT JOIN oficios o 
    ON o.categoria_id = c.id 
   AND o.activo = 1
WHERE c.activo = 1
GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
ORDER BY c.orden ASC, c.id ASC;

-- Oficios de una categoría específica (para vista pública)
SELECT 
    id, 
    titulo, 
    popular
FROM oficios
WHERE categoria_id = ?
  AND activo = 1
ORDER BY popular DESC, titulo ASC;

-- =====================================================
-- MANTENIMIENTO Y LIMPIEZA
-- =====================================================

-- Eliminar oficios inactivos después de 30 días
DELETE FROM oficios 
WHERE activo = 0 
  AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Reiniciar auto_increment (usar con precaución)
-- ALTER TABLE oficios AUTO_INCREMENT = 1;
-- ALTER TABLE categorias AUTO_INCREMENT = 1;

-- =====================================================
-- BACKUP RECOMENDADO
-- =====================================================

/*
Desde terminal/CMD:

mysqldump -u root camella_db categorias oficios > backup_categorias_oficios.sql

Restaurar:
mysql -u root camella_db < backup_categorias_oficios.sql
*/

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =====================================================

-- Verificar índices actuales
SHOW INDEX FROM categorias;
SHOW INDEX FROM oficios;

-- Análisis de consultas lentas (si es necesario)
-- EXPLAIN SELECT ... FROM oficios WHERE ...;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
