-- =============================================
-- LIMPIEZA COMPLETA DE DUPLICADOS V1.1
-- =============================================
-- Este script elimina TODOS los registros de categorías
-- y oficios v1.1 para empezar limpio.
-- =============================================

-- PASO 1: Eliminar TODOS los oficios de las categorías v1.1
DELETE FROM oficios 
WHERE categoria_id IN (
    SELECT id FROM categorias WHERE nombre IN (
        'Servicios Digitales y Contenidos',
        'Educación y Formación',
        'Tecnología y Soporte Digital',
        'Agricultura y Medio Ambiente',
        'Administración y Servicios Empresariales',
        'Moda y Confección'
    )
);

-- PASO 2: Eliminar TODAS las categorías v1.1
DELETE FROM categorias WHERE nombre IN (
    'Servicios Digitales y Contenidos',
    'Educación y Formación',
    'Tecnología y Soporte Digital',
    'Agricultura y Medio Ambiente',
    'Administración y Servicios Empresariales',
    'Moda y Confección'
);

-- PASO 3: Verificar que se eliminaron
SELECT 'Categorías restantes:' as resultado;
SELECT id, nombre, icono FROM categorias ORDER BY id;

SELECT 'Total oficios por categoría:' as resultado;
SELECT c.nombre, COUNT(o.id) as total_oficios
FROM categorias c
LEFT JOIN oficios o ON c.id = o.categoria_id
GROUP BY c.id, c.nombre
ORDER BY c.id;

-- =============================================
-- AHORA SÍ PUEDES EJECUTAR insert_categorias_v1_1.sql
-- =============================================
