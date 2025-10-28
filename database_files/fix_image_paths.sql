-- Script para corregir las rutas de imágenes en la base de datos
-- Problema: Las rutas están guardadas como /assets/... en lugar de assets/...
-- Solución: Eliminar la barra inicial

-- Ver las rutas actuales (antes del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
LIMIT 10;

-- Actualizar todas las rutas que empiezan con /
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';

-- Verificar los cambios (después del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
LIMIT 10;

-- Resultado esperado:
-- ANTES: /assets/images/anuncios/anuncio_2_1729123456.jpg
-- DESPUÉS: assets/images/anuncios/anuncio_2_1729123456.jpg
