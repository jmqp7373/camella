-- =============================================
-- AGREGAR COLUMNA ICONO A CATEGORIAS
-- =============================================
-- Este script agrega la columna 'icono' a la tabla categorias
-- para almacenar las clases de Font Awesome
-- Ejecutar ANTES de insert_categorias_v1_1.sql
-- =============================================

-- Agregar columna icono si no existe
ALTER TABLE categorias 
ADD COLUMN IF NOT EXISTS icono VARCHAR(100) DEFAULT NULL AFTER nombre;

-- Actualizar categorías existentes con iconos apropiados
UPDATE categorias SET icono = 'fas fa-broom' WHERE nombre = 'Aseo y Limpieza';
UPDATE categorias SET icono = 'fas fa-utensils' WHERE nombre = 'Cocina y Preparación de Alimentos';
UPDATE categorias SET icono = 'fas fa-hand-holding-heart' WHERE nombre = 'Cuidados y Acompañamiento';
UPDATE categorias SET icono = 'fas fa-tools' WHERE nombre = 'Mantenimiento y Reparaciones';
UPDATE categorias SET icono = 'fas fa-hard-hat' WHERE nombre = 'Construcción y Obras';
UPDATE categorias SET icono = 'fas fa-truck' WHERE nombre = 'Servicios Logísticos y Transporte';
UPDATE categorias SET icono = 'fas fa-paint-brush' WHERE nombre = 'Belleza y Cuidado Personal';
UPDATE categorias SET icono = 'fas fa-shopping-cart' WHERE nombre = 'Ventas y Atención al Cliente';
UPDATE categorias SET icono = 'fas fa-briefcase' WHERE nombre = 'Oficios Generales / Multiservicios';

-- Verificar resultado
SELECT id, nombre, icono FROM categorias ORDER BY id;
