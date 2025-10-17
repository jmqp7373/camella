-- Script para renombrar la tabla servicios a anuncios
-- Ejecutar en phpMyAdmin o por línea de comandos

-- Renombrar la tabla
RENAME TABLE `servicios` TO `anuncios`;

-- Verificar
SHOW TABLES LIKE 'anuncios';
