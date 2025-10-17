-- =============================================
-- INSERT CATEGORIAS V1.1 - CAMELLA.COM.CO
-- =============================================
-- Este archivo inserta nuevas categorías y oficios
-- complementarios a la versión 1.0 existente.
-- No duplica categorías existentes.
-- 
-- IMPORTANTE: Este script verifica si la categoría existe
-- antes de insertar. Solo ejecutar UNA VEZ.
-- =============================================

-- 💻 SERVICIOS DIGITALES Y CONTENIDOS
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Servicios Digitales y Contenidos', 'Diseño gráfico, edición de video, fotografía, contenido digital y redes sociales', 'fas fa-photo-video', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Servicios Digitales y Contenidos'
) LIMIT 1;

SET @cat_digitales = (SELECT id FROM categorias WHERE nombre = 'Servicios Digitales y Contenidos');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_digitales, 'Diseñador(a) gráfico', 1),
(@cat_digitales, 'Community Manager', 1),
(@cat_digitales, 'Editor(a) de video', 1),
(@cat_digitales, 'Fotógrafo(a) profesional', 1),
(@cat_digitales, 'Monitor u operador de estudio webcam', 1),
(@cat_digitales, 'Creador(a) de contenido digital', 1),
(@cat_digitales, 'Redactor(a) de publicaciones o guiones', 1),
(@cat_digitales, 'Gestor(a) de redes sociales', 1),
(@cat_digitales, 'Ilustrador(a) digital', 1),
(@cat_digitales, 'Productor(a) audiovisual', 1),
(@cat_digitales, 'Animador(a) o diseñador 3D', 1);

-- 🎓 EDUCACIÓN Y FORMACIÓN
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Educación y Formación', 'Tutorías, enseñanza de idiomas, arte, música y formación académica', 'fas fa-chalkboard-teacher', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Educación y Formación'
) LIMIT 1;

SET @cat_educacion = (SELECT id FROM categorias WHERE nombre = 'Educación y Formación');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_educacion, 'Tutor(a) particular', 1),
(@cat_educacion, 'Profesor(a) de idiomas', 1),
(@cat_educacion, 'Instructor(a) de oficios', 1),
(@cat_educacion, 'Profesor(a) de arte o música', 1),
(@cat_educacion, 'Asistente académico o pedagógico', 1);

-- 🧑‍💻 TECNOLOGÍA Y SOPORTE DIGITAL
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Tecnología y Soporte Digital', 'Soporte técnico, instalación de redes, programación y ciberseguridad', 'fas fa-desktop', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Tecnología y Soporte Digital'
) LIMIT 1;

SET @cat_tecnologia = (SELECT id FROM categorias WHERE nombre = 'Tecnología y Soporte Digital');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_tecnologia, 'Técnico de computadores', 1),
(@cat_tecnologia, 'Instalador(a) de redes o cámaras', 1),
(@cat_tecnologia, 'Asesor(a) en ciberseguridad', 1),
(@cat_tecnologia, 'Programador(a) básico/a', 1),
(@cat_tecnologia, 'Soporte técnico remoto', 1);

-- 🌱 AGRICULTURA Y MEDIO AMBIENTE
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Agricultura y Medio Ambiente', 'Jardinería, huertas, cosechas, técnica agropecuaria y reciclaje', 'fas fa-seedling', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Agricultura y Medio Ambiente'
) LIMIT 1;

SET @cat_agricultura = (SELECT id FROM categorias WHERE nombre = 'Agricultura y Medio Ambiente');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_agricultura, 'Jardinero(a) ecológico', 1),
(@cat_agricultura, 'Cuidador(a) de huertas o plantas', 1),
(@cat_agricultura, 'Recolector(a) de cosecha', 1),
(@cat_agricultura, 'Técnico(a) agropecuario/a', 1),
(@cat_agricultura, 'Operario(a) de reciclaje o compostaje', 1);

-- 🧾 ADMINISTRACIÓN Y SERVICIOS EMPRESARIALES
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Administración y Servicios Empresariales', 'Asistencia administrativa, contabilidad, recepción y mensajería', 'fas fa-folder-open', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Administración y Servicios Empresariales'
) LIMIT 1;

SET @cat_admin = (SELECT id FROM categorias WHERE nombre = 'Administración y Servicios Empresariales');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_admin, 'Asistente administrativo', 1),
(@cat_admin, 'Digitador(a)', 1),
(@cat_admin, 'Contador(a) auxiliar', 1),
(@cat_admin, 'Mensajero(a) interno', 1),
(@cat_admin, 'Recepcionista', 1);

-- 👗 MODA Y CONFECCIÓN
INSERT INTO categorias (nombre, descripcion, icono, activo) 
SELECT * FROM (SELECT 'Moda y Confección', 'Modistería, diseño de modas, arreglos, bordados y zapatería', 'fas fa-cut', 1) AS tmp
WHERE NOT EXISTS (
    SELECT nombre FROM categorias WHERE nombre = 'Moda y Confección'
) LIMIT 1;

SET @cat_moda = (SELECT id FROM categorias WHERE nombre = 'Moda y Confección');

INSERT IGNORE INTO oficios (categoria_id, titulo, activo) VALUES
(@cat_moda, 'Modista o sastre', 1),
(@cat_moda, 'Diseñador(a) de modas', 1),
(@cat_moda, 'Arreglos de ropa', 1),
(@cat_moda, 'Bordador(a)', 1),
(@cat_moda, 'Zapatero(a)', 1);

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
