-- =============================================
-- AGREGAR COLUMNA POPULAR A OFICIOS
-- =============================================
-- Esta columna permite marcar oficios destacados
-- para mostrarlos con el ícono 🔥 en la interfaz
-- 
-- Ejecutar en phpMyAdmin o desde ejecutar_sql.php
-- =============================================

-- Agregar columna 'popular' a la tabla oficios
ALTER TABLE oficios 
ADD COLUMN IF NOT EXISTS popular TINYINT(1) DEFAULT 0 AFTER activo;

-- Marcar oficios destacados como populares (🔥)
-- Nota: Se usa 'titulo' en vez de 'nombre' según la estructura real de la tabla

-- Servicios Digitales y Contenidos
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Community Manager%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Editor%video%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Fotógrafo%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Monitor%webcam%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Creador%contenido%';

-- Tecnología y Soporte Digital
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Soporte técnico remoto%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Programador%';

-- Educación y Formación
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Profesor%idiomas%';
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Tutor%particular%';

-- Administración y Servicios Empresariales
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Asistente administrativo%';

-- Moda y Confección
UPDATE oficios SET popular = 1 WHERE titulo LIKE '%Diseñador%modas%';

-- Verificar oficios marcados como populares
SELECT id, titulo, popular 
FROM oficios 
WHERE popular = 1 
ORDER BY titulo;

-- =============================================
-- RESULTADO ESPERADO
-- =============================================
-- Aproximadamente 11 oficios marcados como populares
-- distribuidos en diferentes categorías
-- =============================================
