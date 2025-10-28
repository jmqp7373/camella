-- 1. Eliminar tablas existentes si ya hay estructura previa
DROP TABLE IF EXISTS oficios;
DROP TABLE IF EXISTS categorias;

-- 2. Crear tabla de categorías con descripción y estado
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1
);

-- 3. Crear tabla de oficios con descripción y estado
CREATE TABLE oficios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(100) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  activo TINYINT(1) DEFAULT 1,
  categoria_id INT NOT NULL,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- 4. Insertar categorías
INSERT INTO categorias (nombre) VALUES
('Aseo y Limpieza'),
('Cocina y Preparación de Alimentos'),
('Cuidados y Acompañamiento'),
('Mantenimiento y Reparaciones'),
('Construcción y Obras'),
('Servicios Logísticos y Transporte'),
('Belleza y Cuidado Personal'),
('Ventas y Atención al Cliente'),
('Oficios Generales / Multiservicios'),
('Cuidado de Animales'),
('Producción y Manufactura'),
('Eventos y Actividades Especiales');

-- 5. Insertar oficios (los campos "descripcion" y "activo" usarán los valores por defecto)
INSERT INTO oficios (titulo, categoria_id) VALUES
('Auxiliar de limpieza general', 1),
('Auxiliar de aseo para casas', 1),
('Aseo en oficinas o locales', 1),
('Limpieza en conjuntos residenciales', 1),
('Lavado de vidrios en altura', 1),
('Desinfección de espacios', 1),
('Aseo en clínicas o centros médicos', 1),

('Ayudante de cocina', 2),
('Cocinero(a) en restaurante', 2),
('Cocinero(a) por horas', 2),
('Preparador de alimentos en eventos', 2),
('Repostero(a) o pastelero(a)', 2),
('Auxiliar de cocina en colegios', 2),
('Parrillero(a)', 2),

('Niñera o cuidadora de niños', 3),
('Cuidadora de adultos mayores', 3),
('Acompañante terapéutico', 3),
('Cuidado de personas con discapacidad', 3),
('Enfermera auxiliar a domicilio', 3),

('Técnico en electrodomésticos', 4),
('Plomero o fontanero', 4),
('Electricista básico', 4),
('Técnico en refrigeración', 4),
('Reparación de celulares o tecnología', 4),
('Técnico de lavadoras', 4),
('Servicio técnico de motos o bicis', 4),

('Maestro de obra', 5),
('Ayudante de construcción', 5),
('Pintor de interiores y exteriores', 5),
('Albañil', 5),
('Instalador de drywall', 5),
('Instalador de pisos y enchapes', 5),
('Jardinero para obras', 5),

('Domiciliario en moto o bicicleta', 6),
('Conductor particular', 6),
('Auxiliar de bodega', 6),
('Cargue y descargue de mercancía', 6),
('Mototaxi o transporte alternativo', 6),
('Trasteos y mudanzas', 6),
('Ayudante de logística en eventos', 6),

('Manicurista a domicilio', 7),
('Peluquero(a) o barbero(a)', 7),
('Maquilladora para eventos', 7),
('Técnica en cejas y pestañas', 7),
('Masajista relajante o terapéutico', 7),
('Esteticista facial', 7),

('Vendedor informal (ambulante)', 8),
('Vendedor en ferias o eventos', 8),
('Vendedor(a) puerta a puerta', 8),
('Atención en puntos físicos (locales)', 8),
('Call center desde casa', 8),
('Mercaderista', 8),

('Todo servicio', 9),
('Hombre/mujer para todo', 9),
('Personal de mantenimiento general', 9),
('Mayordomo para fincas o casas grandes', 9),
('Ayudante de finca', 9),

('Paseador de perros', 10),
('Cuidado de mascotas en casa', 10),
('Limpieza de espacios de animales', 10),
('Baño y peluquería canina', 10),
('Alimentación de animales por ausencias', 10),

('Empacador(a)', 11),
('Operario de planta', 11),
('Ensamblador manual', 11),
('Armado de paquetes', 11),
('Etiquetado y embalaje', 11),

('Mesero(a) para eventos', 12),
('Logística de eventos', 12),
('Animador de fiestas', 12),
('Decorador(a) de eventos', 12),
('Ayudante de cocina en eventos', 12);