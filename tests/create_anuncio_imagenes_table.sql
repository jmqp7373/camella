-- =====================================================
-- Script: Crear tabla anuncio_imagenes
-- Propósito: Almacenar las imágenes de cada anuncio
-- Máximo: 5 imágenes por anuncio
-- =====================================================

CREATE TABLE IF NOT EXISTS anuncio_imagenes (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  anuncio_id INT(11) NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  orden TINYINT UNSIGNED DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- Relación con tabla anuncios
  FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
  
  -- Índices para mejorar rendimiento
  INDEX idx_anuncio_id (anuncio_id),
  INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insertar imágenes de ejemplo para los 3 anuncios
-- =====================================================

-- Anuncio 1: Plomero (id=1)
INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden) VALUES
(1, '/assets/images/anuncios/ejemplos/plomero.jpg', 1);

-- Anuncio 2: Electricista (id=2)
INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden) VALUES
(2, '/assets/images/anuncios/ejemplos/electricista.jpg', 1);

-- Anuncio 3: Carpintero (id=3)
INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden) VALUES
(3, '/assets/images/anuncios/ejemplos/carpintero.jpg', 1);

-- =====================================================
-- Verificación
-- =====================================================
SELECT 
    ai.id,
    ai.anuncio_id,
    a.titulo,
    ai.ruta,
    ai.orden,
    ai.created_at
FROM anuncio_imagenes ai
INNER JOIN anuncios a ON ai.anuncio_id = a.id
ORDER BY ai.anuncio_id, ai.orden;
