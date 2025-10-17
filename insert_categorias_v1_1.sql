-- =============================================
-- INSERT CATEGORIAS V1.1 - CAMELLA.COM.CO
-- =============================================
-- Este archivo inserta nuevas categorías y oficios
-- complementarios a la versión 1.0 existente.
-- No duplica categorías existentes.
-- 
-- Ejecutar manualmente desde ejecutar_sql.php
-- =============================================

-- 💻 SERVICIOS DIGITALES Y CONTENIDOS
INSERT INTO categorias (nombre, icono) 
VALUES ('Servicios Digitales y Contenidos', 'fas fa-laptop-code')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_digitales = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_digitales, 'Diseñador(a) gráfico'),
(@cat_digitales, 'Community Manager'),
(@cat_digitales, 'Editor(a) de video'),
(@cat_digitales, 'Fotógrafo(a) profesional'),
(@cat_digitales, 'Monitor u operador de estudio webcam'),
(@cat_digitales, 'Creador(a) de contenido digital'),
(@cat_digitales, 'Redactor(a) de publicaciones o guiones'),
(@cat_digitales, 'Gestor(a) de redes sociales'),
(@cat_digitales, 'Ilustrador(a) digital'),
(@cat_digitales, 'Productor(a) audiovisual'),
(@cat_digitales, 'Animador(a) o diseñador 3D');

-- 🎓 EDUCACIÓN Y FORMACIÓN
INSERT INTO categorias (nombre, icono) 
VALUES ('Educación y Formación', 'fas fa-graduation-cap')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_educacion = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_educacion, 'Tutor(a) particular'),
(@cat_educacion, 'Profesor(a) de idiomas'),
(@cat_educacion, 'Instructor(a) de oficios'),
(@cat_educacion, 'Profesor(a) de arte o música'),
(@cat_educacion, 'Asistente académico o pedagógico');

-- 🧑‍💻 TECNOLOGÍA Y SOPORTE DIGITAL
INSERT INTO categorias (nombre, icono) 
VALUES ('Tecnología y Soporte Digital', 'fas fa-laptop')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_tecnologia = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_tecnologia, 'Técnico de computadores'),
(@cat_tecnologia, 'Instalador(a) de redes o cámaras'),
(@cat_tecnologia, 'Asesor(a) en ciberseguridad'),
(@cat_tecnologia, 'Programador(a) básico/a'),
(@cat_tecnologia, 'Soporte técnico remoto');

-- 🌱 AGRICULTURA Y MEDIO AMBIENTE
INSERT INTO categorias (nombre, icono) 
VALUES ('Agricultura y Medio Ambiente', 'fas fa-leaf')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_agricultura = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_agricultura, 'Jardinero(a) ecológico'),
(@cat_agricultura, 'Cuidador(a) de huertas o plantas'),
(@cat_agricultura, 'Recolector(a) de cosecha'),
(@cat_agricultura, 'Técnico(a) agropecuario/a'),
(@cat_agricultura, 'Operario(a) de reciclaje o compostaje');

-- 🧾 ADMINISTRACIÓN Y SERVICIOS EMPRESARIALES
INSERT INTO categorias (nombre, icono) 
VALUES ('Administración y Servicios Empresariales', 'fas fa-briefcase')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_admin = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_admin, 'Asistente administrativo'),
(@cat_admin, 'Digitador(a)'),
(@cat_admin, 'Contador(a) auxiliar'),
(@cat_admin, 'Mensajero(a) interno'),
(@cat_admin, 'Recepcionista');

-- 👗 MODA Y CONFECCIÓN
INSERT INTO categorias (nombre, icono) 
VALUES ('Moda y Confección', 'fas fa-tshirt')
ON DUPLICATE KEY UPDATE nombre=nombre;

SET @cat_moda = LAST_INSERT_ID();

INSERT INTO oficios (categoria_id, nombre) VALUES
(@cat_moda, 'Modista o sastre'),
(@cat_moda, 'Diseñador(a) de modas'),
(@cat_moda, 'Arreglos de ropa'),
(@cat_moda, 'Bordador(a)'),
(@cat_moda, 'Zapatero(a)');

-- =============================================
-- RESUMEN DE INSERCIONES V1.1
-- =============================================
-- Total categorías nuevas: 6
-- Total oficios nuevos: 37
-- 
-- Categorías:
-- 1. Servicios Digitales y Contenidos (11 oficios)
-- 2. Educación y Formación (5 oficios)
-- 3. Tecnología y Soporte Digital (5 oficios)
-- 4. Agricultura y Medio Ambiente (5 oficios)
-- 5. Administración y Servicios Empresariales (5 oficios)
-- 6. Moda y Confección (5 oficios)
-- =============================================
