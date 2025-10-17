# 🔍 TABLAS DE BASE DE DATOS - Sistema de Anuncios

## 📊 TABLAS INVOLUCRADAS EN PUBLICAR ANUNCIO

### Endpoint: `https://camella.com.co/views/bloques/publicar.php`
### APIs: `api.php?action=saveAnuncio`, `api.php?action=uploadImage`, `api.php?action=deleteImage`, `api.php?action=getImages`

---

## 1️⃣ Tabla: `anuncios`

### Descripción:
Almacena la información principal de cada anuncio (título, descripción, precio).

### Columnas requeridas:
```sql
CREATE TABLE anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(20) DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Operaciones:
- **INSERT:** Al crear nuevo anuncio (modo=nuevo)
- **UPDATE:** Al editar anuncio existente (modo=editar)
- **SELECT:** Al cargar datos en modo editar/ver
- **DELETE:** Al eliminar un anuncio

### Campos críticos:
- ✅ `id` - PRIMARY KEY
- ✅ `user_id` - Foreign key a tabla users
- ✅ `titulo` - VARCHAR(255) NOT NULL
- ✅ `descripcion` - TEXT NOT NULL
- ✅ `precio` - DECIMAL(10,2)
- ✅ `status` - VARCHAR(20) DEFAULT 'activo'
- ✅ `created_at` - DATETIME NOT NULL
- ✅ `updated_at` - DATETIME NOT NULL

---

## 2️⃣ Tabla: `anuncio_imagenes`

### Descripción:
Almacena las rutas de las imágenes asociadas a cada anuncio (hasta 5 por anuncio).

### Columnas requeridas:
```sql
CREATE TABLE anuncio_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    orden INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
    INDEX idx_anuncio_id (anuncio_id),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Operaciones:
- **INSERT:** Al subir una nueva imagen
- **SELECT:** Al cargar imágenes existentes del anuncio
- **DELETE:** Al eliminar una imagen específica
- **UPDATE:** Al reordenar imágenes después de eliminar una

### Campos críticos:
- ✅ `id` - PRIMARY KEY
- ✅ `anuncio_id` - Foreign key a tabla anuncios
- ✅ `ruta` - VARCHAR(500) NOT NULL (formato: `assets/images/anuncios/archivo.jpg`)
- ✅ `orden` - INT (1-5 para ordenar las imágenes)
- ✅ `created_at` - TIMESTAMP

### ⚠️ IMPORTANTE - Formato de rutas:
```
❌ INCORRECTO: /assets/images/anuncios/archivo.jpg  (con / inicial)
✅ CORRECTO:   assets/images/anuncios/archivo.jpg   (sin / inicial)
```

---

## 3️⃣ Tabla: `users` (Referencia indirecta)

### Descripción:
Tabla de usuarios. Se usa para validar que el `user_id` existe.

### Campos usados:
- ✅ `id` - Referenciado por `anuncios.user_id`
- ✅ `role` - Usado para verificar permisos (admin, promotor, publicante)

### Operaciones:
- **SELECT:** Validación implícita por foreign key

---

## 📋 QUERIES SQL PARA VERIFICAR EN PRODUCCIÓN

### 1. Verificar que existe la tabla `anuncios`:
```sql
SHOW TABLES LIKE 'anuncios';
```

### 2. Verificar estructura de tabla `anuncios`:
```sql
DESCRIBE anuncios;
```

### 3. Verificar que existe la tabla `anuncio_imagenes`:
```sql
SHOW TABLES LIKE 'anuncio_imagenes';
```

### 4. Verificar estructura de tabla `anuncio_imagenes`:
```sql
DESCRIBE anuncio_imagenes;
```

### 5. Verificar que las columnas necesarias existen:
```sql
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u179023609_camella_db' 
  AND TABLE_NAME = 'anuncios'
ORDER BY ORDINAL_POSITION;
```

```sql
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'u179023609_camella_db' 
  AND TABLE_NAME = 'anuncio_imagenes'
ORDER BY ORDINAL_POSITION;
```

### 6. Verificar foreign keys:
```sql
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'u179023609_camella_db'
  AND REFERENCED_TABLE_NAME = 'anuncios';
```

### 7. Verificar anuncios existentes:
```sql
SELECT id, user_id, titulo, status, created_at 
FROM anuncios 
ORDER BY id DESC 
LIMIT 10;
```

### 8. Verificar imágenes existentes:
```sql
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
ORDER BY id DESC 
LIMIT 10;
```

### 9. Verificar formato de rutas (deben estar sin / inicial):
```sql
-- Ver rutas con formato incorrecto (con / inicial)
SELECT id, anuncio_id, ruta 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%'
LIMIT 20;

-- Ver rutas con formato correcto (sin / inicial)
SELECT id, anuncio_id, ruta 
FROM anuncio_imagenes 
WHERE ruta NOT LIKE '/%'
LIMIT 20;
```

---

## 🚨 POSIBLES ERRORES EN PRODUCCIÓN

### Error 1: Tabla `anuncios` no existe
**Síntoma:** Error al intentar crear anuncio
**Solución:** Ejecutar script de creación de tablas
```sql
-- Ver archivo: database_structure.sql o database_production_complete.sql
```

### Error 2: Tabla `anuncio_imagenes` no existe
**Síntoma:** Error al subir imágenes
**Solución:** Ejecutar script de creación:
```sql
CREATE TABLE anuncio_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anuncio_id INT NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    orden INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Error 3: Columna `updated_at` no existe en `anuncios`
**Síntoma:** Error SQL al guardar anuncio
**Solución:** Agregar columna:
```sql
ALTER TABLE anuncios 
ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
```

### Error 4: Columna `status` no existe en `anuncios`
**Síntoma:** Error SQL al crear anuncio
**Solución:** Agregar columna:
```sql
ALTER TABLE anuncios 
ADD COLUMN status VARCHAR(20) DEFAULT 'activo' AFTER precio;
```

### Error 5: Formato de rutas incorrecto
**Síntoma:** Imágenes no se muestran (broken images)
**Solución:** Ejecutar script de corrección:
```sql
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';
```

### Error 6: Carpeta de imágenes no existe o sin permisos
**Síntoma:** Error al subir imágenes
**Solución (vía SSH):**
```bash
mkdir -p /home/u179023609/public_html/assets/images/anuncios
chmod 755 /home/u179023609/public_html/assets/images/anuncios
```

---

## 📝 SCRIPT COMPLETO DE VERIFICACIÓN

Ejecutar en phpMyAdmin (base de datos: `u179023609_camella_db`):

```sql
-- ============================================
-- SCRIPT DE VERIFICACIÓN COMPLETA
-- Base de datos: u179023609_camella_db
-- ============================================

-- 1. Verificar que existen las tablas
SELECT 'Tabla anuncios:' as verificacion, 
       COUNT(*) as existe 
FROM information_schema.tables 
WHERE table_schema = 'u179023609_camella_db' 
  AND table_name = 'anuncios';

SELECT 'Tabla anuncio_imagenes:' as verificacion, 
       COUNT(*) as existe 
FROM information_schema.tables 
WHERE table_schema = 'u179023609_camella_db' 
  AND table_name = 'anuncio_imagenes';

-- 2. Verificar estructura de anuncios
SELECT 'Columnas de anuncios:' as info;
DESCRIBE anuncios;

-- 3. Verificar estructura de anuncio_imagenes
SELECT 'Columnas de anuncio_imagenes:' as info;
DESCRIBE anuncio_imagenes;

-- 4. Verificar datos de prueba
SELECT 'Total de anuncios:' as info, COUNT(*) as total FROM anuncios;
SELECT 'Total de imágenes:' as info, COUNT(*) as total FROM anuncio_imagenes;

-- 5. Ver últimos anuncios creados
SELECT 'Últimos 5 anuncios:' as info;
SELECT id, user_id, titulo, status, created_at 
FROM anuncios 
ORDER BY id DESC 
LIMIT 5;

-- 6. Ver últimas imágenes subidas
SELECT 'Últimas 5 imágenes:' as info;
SELECT id, anuncio_id, 
       SUBSTRING(ruta, 1, 50) as ruta_corta, 
       orden, created_at 
FROM anuncio_imagenes 
ORDER BY id DESC 
LIMIT 5;

-- 7. Verificar formato de rutas
SELECT 'Imágenes con formato INCORRECTO (con /):' as info, 
       COUNT(*) as total 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%';

SELECT 'Imágenes con formato CORRECTO (sin /):' as info, 
       COUNT(*) as total 
FROM anuncio_imagenes 
WHERE ruta NOT LIKE '/%';

-- 8. Verificar foreign keys
SELECT 'Foreign Keys:' as info;
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, 
       REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'u179023609_camella_db'
  AND (TABLE_NAME = 'anuncios' OR TABLE_NAME = 'anuncio_imagenes')
  AND REFERENCED_TABLE_NAME IS NOT NULL;
```

---

## 🔄 FLUJO COMPLETO DE CREACIÓN DE ANUNCIO

### 1. Usuario carga formulario:
```
GET /views/bloques/publicar.php?modo=nuevo
↓
SELECT * FROM anuncios WHERE id = ? (si modo=editar)
```

### 2. Usuario llena formulario y hace clic en "Publicar anuncio":
```javascript
// JavaScript en publicar.php
fetch('api.php?action=saveAnuncio', ...)
```

### 3. Backend guarda anuncio:
```sql
-- api.php - caso saveAnuncio
INSERT INTO anuncios 
(user_id, titulo, descripcion, precio, status, created_at, updated_at)
VALUES (?, ?, ?, ?, 'activo', NOW(), NOW());

-- Retorna: nuevo anuncio_id
```

### 4. Usuario sube imágenes (hasta 5):
```javascript
// JavaScript en publicar.php
fetch('api.php?action=uploadImage', ...)
```

### 5. Backend guarda cada imagen:
```sql
-- controllers/ImageUploadController.php
-- 1. Verificar que anuncio existe
SELECT id FROM anuncios WHERE id = ? AND user_id = ?;

-- 2. Guardar archivo físico en: 
--    /public_html/assets/images/anuncios/anuncio_X_timestamp_hash.jpg

-- 3. Guardar ruta en BD
INSERT INTO anuncio_imagenes (anuncio_id, ruta, orden)
VALUES (?, 'assets/images/anuncios/archivo.jpg', ?);
```

### 6. Usuario puede eliminar imágenes:
```javascript
fetch('api.php?action=deleteImage', ...)
```

### 7. Backend elimina imagen:
```sql
-- controllers/ImageUploadController.php
-- 1. Obtener ruta de imagen
SELECT ruta FROM anuncio_imagenes WHERE id = ? AND anuncio_id = ?;

-- 2. Eliminar archivo físico del servidor

-- 3. Eliminar registro de BD
DELETE FROM anuncio_imagenes WHERE id = ?;

-- 4. Reordenar imágenes restantes
UPDATE anuncio_imagenes SET orden = ? WHERE id = ?;
```

---

## 📊 RESUMEN DE DEPENDENCIAS

```
users (tabla de usuarios)
  ↓ (user_id)
anuncios (tabla principal)
  ↓ (anuncio_id)
anuncio_imagenes (tabla de imágenes)
```

---

## ✅ CHECKLIST DE VERIFICACIÓN EN PRODUCCIÓN

- [ ] Tabla `anuncios` existe
- [ ] Tabla `anuncio_imagenes` existe
- [ ] Columna `anuncios.updated_at` existe
- [ ] Columna `anuncios.status` existe
- [ ] Foreign key `anuncio_imagenes.anuncio_id → anuncios.id` existe
- [ ] Carpeta `/public_html/assets/images/anuncios/` existe
- [ ] Carpeta tiene permisos 755
- [ ] Archivos de imagen tienen permisos 644
- [ ] Rutas en BD están sin `/` inicial
- [ ] Campo `anuncio_imagenes.ruta` es VARCHAR(500) o mayor

---

**Última actualización:** 17 de octubre de 2025  
**Base de datos producción:** `u179023609_camella_db`  
**Servidor:** Hostinger
