-- =============================================
-- ELIMINAR CATEGORÍAS Y OFICIOS DUPLICADOS
-- =============================================
-- Este script elimina las categorías duplicadas v1.1
-- y sus oficios asociados.
-- 
-- EJECUTAR SOLO UNA VEZ en phpMyAdmin
-- =============================================

-- Ver duplicados antes de eliminar
SELECT id, nombre FROM categorias WHERE nombre IN (
    'Servicios Digitales y Contenidos',
    'Educación y Formación',
    'Tecnología y Soporte Digital',
    'Agricultura y Medio Ambiente',
    'Administración y Servicios Empresariales',
    'Moda y Confección'
) ORDER BY nombre, id;

-- Eliminar oficios de las categorías duplicadas (IDs mayores)
DELETE o FROM oficios o
INNER JOIN categorias c ON o.categoria_id = c.id
WHERE c.nombre IN (
    'Servicios Digitales y Contenidos',
    'Educación y Formación',
    'Tecnología y Soporte Digital',
    'Agricultura y Medio Ambiente',
    'Administración y Servicios Empresariales',
    'Moda y Confección'
)
AND c.id NOT IN (
    -- Mantener solo el ID más bajo de cada categoría (la primera creada)
    SELECT MIN(id) FROM (
        SELECT id, nombre FROM categorias
    ) AS sub
    WHERE sub.nombre IN (
        'Servicios Digitales y Contenidos',
        'Educación y Formación',
        'Tecnología y Soporte Digital',
        'Agricultura y Medio Ambiente',
        'Administración y Servicios Empresariales',
        'Moda y Confección'
    )
    GROUP BY sub.nombre
);

-- Eliminar categorías duplicadas (mantener solo la primera de cada una)
DELETE FROM categorias
WHERE nombre IN (
    'Servicios Digitales y Contenidos',
    'Educación y Formación',
    'Tecnología y Soporte Digital',
    'Agricultura y Medio Ambiente',
    'Administración y Servicios Empresariales',
    'Moda y Confección'
)
AND id NOT IN (
    -- Mantener solo el ID más bajo de cada categoría
    SELECT * FROM (
        SELECT MIN(id) FROM categorias
        WHERE nombre IN (
            'Servicios Digitales y Contenidos',
            'Educación y Formación',
            'Tecnología y Soporte Digital',
            'Agricultura y Medio Ambiente',
            'Administración y Servicios Empresariales',
            'Moda y Confección'
        )
        GROUP BY nombre
    ) AS keep_ids
);

-- Verificar resultado final
SELECT id, nombre, icono FROM categorias ORDER BY id;
SELECT COUNT(*) as total_categorias FROM categorias;
SELECT COUNT(*) as total_oficios FROM oficios;

-- =============================================
-- RESULTADO ESPERADO
-- =============================================
-- Debe quedar solo 1 registro de cada categoría v1.1
-- con todos sus oficios asociados.
-- =============================================
