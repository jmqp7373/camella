-- Agregar columna oficio_id a la tabla anuncios para relacionar con oficios
-- Esta columna permite que cada anuncio esté asociado a un oficio específico

ALTER TABLE `anuncios` 
ADD COLUMN `oficio_id` INT(11) NULL DEFAULT NULL AFTER `user_id`,
ADD KEY `oficio_id` (`oficio_id`),
ADD CONSTRAINT `fk_anuncios_oficios` 
  FOREIGN KEY (`oficio_id`) 
  REFERENCES `oficios`(`id`) 
  ON DELETE SET NULL 
  ON UPDATE CASCADE;

-- Comentario: 
-- oficio_id puede ser NULL para anuncios genéricos sin categoría específica
-- Si se elimina un oficio, los anuncios asociados mantendrán oficio_id = NULL
