# 🚀 DEPLOYMENT - Sistema de Imágenes a Producción

## ⚠️ Pre-requisitos

- [x] Local funcionando correctamente
- [x] Git commit realizado (2a9741d, 5aea63e)
- [x] Acceso a cPanel de Hostinger
- [x] FileZilla o acceso FTP

---

## 📋 Paso a Paso

### 1️⃣ Crear Tabla en Base de Datos de Producción

**Opción A: Desde cPanel → phpMyAdmin**

1. Acceder a cPanel de Hostinger
2. Ir a **phpMyAdmin**
3. Seleccionar base de datos: `u179023609_camella_db`
4. Click en pestaña **SQL**
5. Copiar y pegar el siguiente código:

```sql
CREATE TABLE IF NOT EXISTS anuncio_imagenes (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  anuncio_id INT(11) NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  orden TINYINT UNSIGNED DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE,
  INDEX idx_anuncio_id (anuncio_id),
  INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

6. Click **Ejecutar**
7. Verificar mensaje: "1 fila afectada"

**Opción B: Vía SSH (si tienes acceso)**

```bash
ssh usuario@camella.com.co
cd public_html
php tests/ejecutar_anuncio_imagenes.php
```

---

### 2️⃣ Verificar Permisos de Carpetas

**Vía FileZilla:**

1. Conectar al servidor
2. Navegar a: `public_html/assets/images/`
3. Verificar que existe carpeta `anuncios/`
4. Click derecho → **Permisos de archivo**
5. Establecer: **755** (rwxr-xr-x)
6. ✅ Marcar: "Recurse into subdirectories"
7. Click **OK**

**Vía SSH:**

```bash
cd public_html/assets/images
chmod 755 anuncios
chmod 644 anuncios/.htaccess
```

---

### 3️⃣ Verificar Archivos Subidos

Los siguientes archivos deben estar en producción (se suben automáticamente con el webhook de GitHub):

```
✅ api.php
✅ controllers/ImageUploadController.php
✅ views/publicante/publicar.php
✅ tests/create_anuncio_imagenes_table.sql
✅ tests/ejecutar_anuncio_imagenes.php
✅ assets/images/anuncios/.htaccess
✅ assets/images/anuncios/README.md
```

**Verificar vía FileZilla o SSH:**

```bash
ls -la public_html/api.php
ls -la public_html/controllers/ImageUploadController.php
ls -la public_html/views/publicante/publicar.php
ls -la public_html/assets/images/anuncios/.htaccess
```

---

### 4️⃣ Testing en Producción

#### Test 1: Verificar tabla creada

**Desde phpMyAdmin:**

```sql
SHOW TABLES LIKE 'anuncio_imagenes';
DESC anuncio_imagenes;
SELECT COUNT(*) FROM anuncio_imagenes;
```

**Resultado esperado:**
- ✅ Tabla existe
- ✅ 6 columnas (id, anuncio_id, ruta, orden, created_at, + índices)
- ✅ 0 registros (o más si ya hay datos)

---

#### Test 2: Acceder a la vista de publicar

**URL:**
```
https://camella.com.co/views/publicante/publicar.php
```

**Verificar:**
- ✅ Página carga sin errores
- ✅ Formulario visible
- ✅ Sección de upload de imágenes presente
- ✅ Contador "0 de 5 imágenes"

---

#### Test 3: Subir una imagen de prueba

1. **Login** como usuario publicante
2. Ir a **dashboard** → "Publicar nuevo anuncio"
3. Llenar título y descripción
4. **Subir 1 imagen** mediante drag & drop
5. Verificar:
   - ✅ Imagen se sube correctamente
   - ✅ Preview aparece
   - ✅ Contador cambia a "1 de 5 imágenes"
   - ✅ Alerta de éxito

---

#### Test 4: Verificar API endpoints

**Test con cURL (desde terminal):**

```bash
# Test getImages (debe retornar JSON)
curl "https://camella.com.co/api.php?action=getImages&anuncio_id=1"

# Resultado esperado:
{
  "success": true,
  "images": [...],
  "total": 0,
  "remaining": 5
}
```

---

#### Test 5: Eliminar imagen

1. En la misma vista de publicar
2. Click en botón **X** de una imagen
3. Confirmar eliminación
4. Verificar:
   - ✅ Imagen desaparece del preview
   - ✅ Contador se actualiza
   - ✅ Archivo se elimina del servidor
   - ✅ Registro se elimina de BD

---

#### Test 6: Límite de 5 imágenes

1. Subir 5 imágenes
2. Intentar subir una 6ta imagen
3. Verificar:
   - ✅ Mensaje: "Este anuncio ya tiene el máximo de 5 imágenes"
   - ✅ Upload se deshabilita visualmente

---

### 5️⃣ Verificación de Seguridad

#### Test 1: Intentar subir archivo PHP

1. Renombrar un archivo `.txt` a `.php`
2. Intentar subirlo como imagen
3. **Resultado esperado:**
   - ❌ Rechazo: "Tipo de archivo no permitido"

---

#### Test 2: Acceso directo a imágenes

```bash
# Debe permitir acceso
curl -I https://camella.com.co/assets/images/anuncios/anuncio_1_123.jpg

# Resultado esperado: HTTP 200 OK
```

---

#### Test 3: Intentar ejecutar PHP en carpeta de imágenes

```bash
# Debe denegar acceso
curl -I https://camella.com.co/assets/images/anuncios/malicious.php

# Resultado esperado: HTTP 403 Forbidden
```

---

#### Test 4: Listado de directorios

```bash
# Debe denegar listado
curl https://camella.com.co/assets/images/anuncios/

# Resultado esperado: 403 Forbidden
```

---

## 🐛 Troubleshooting

### Error: "La carpeta de anuncios no tiene permisos de escritura"

**Solución:**
```bash
chmod 755 assets/images/anuncios
chown usuario:usuario assets/images/anuncios
```

---

### Error: "Call to undefined function getPDO()"

**Causa:** No se está cargando correctamente `database.php`

**Verificar:**
```bash
# Ver contenido del archivo
cat controllers/ImageUploadController.php | grep "require_once"

# Debe tener:
require_once __DIR__ . '/../config/database.php';
```

---

### Error: "SQLSTATE[HY000]: Foreign key constraint is incorrectly formed"

**Causa:** Tipos de datos no coinciden entre `anuncios.id` y `anuncio_imagenes.anuncio_id`

**Solución:**
```sql
-- Verificar tipo de dato en anuncios
DESC anuncios;

-- Si es INT(11), la tabla anuncio_imagenes debe usar INT(11) también
-- NO usar BIGINT UNSIGNED
```

---

### Las imágenes no se muestran

**Verificar:**

1. **Ruta en BD:**
```sql
SELECT ruta FROM anuncio_imagenes LIMIT 1;
-- Debe ser: /assets/images/anuncios/archivo.jpg
```

2. **Archivo existe:**
```bash
ls -la public_html/assets/images/anuncios/
```

3. **Permisos de lectura:**
```bash
chmod 644 public_html/assets/images/anuncios/*.jpg
```

4. **Constante SITE_URL:**
```php
// En config.php debe estar:
define('SITE_URL', 'https://camella.com.co');
```

---

### Error 500 al subir imágenes

**Posibles causas:**

1. **Límite de tamaño en PHP.ini:**
```ini
; Verificar en cPanel → PHP Configuration
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 10
```

2. **Timeout:**
```ini
max_execution_time = 300
max_input_time = 300
```

3. **Logs de error:**
```bash
tail -f ~/logs/error_log
```

---

## ✅ Checklist Final

### Pre-Deployment
- [x] Código testeado localmente
- [x] Sintaxis PHP validada
- [x] Git commit realizado
- [x] Git push a GitHub
- [x] Documentación completa

### Deployment
- [ ] Tabla creada en producción
- [ ] Permisos de carpetas configurados
- [ ] Archivos verificados en servidor
- [ ] .htaccess en su lugar

### Testing en Producción
- [ ] Tabla existe y es accesible
- [ ] Vista de publicar carga correctamente
- [ ] Subir imagen funciona
- [ ] Eliminar imagen funciona
- [ ] Límite de 5 imágenes se respeta
- [ ] API endpoints responden JSON
- [ ] Seguridad .htaccess funciona
- [ ] No hay errores en logs

### Post-Deployment
- [ ] Notificar a usuarios sobre nueva funcionalidad
- [ ] Monitorear logs por 24 horas
- [ ] Backup de base de datos
- [ ] Documentar cualquier issue encontrado

---

## 📊 Métricas de Éxito

Al finalizar el deployment, deberías tener:

- ✅ Tabla `anuncio_imagenes` en producción
- ✅ 0 errores en logs
- ✅ Usuarios pueden subir hasta 5 imágenes
- ✅ Tiempo de subida < 5 segundos
- ✅ Seguridad .htaccess activa
- ✅ 100% uptime

---

## 📞 Contacto

**Desarrollador:** Juan Manuel  
**Repositorio:** https://github.com/jmqp7373/camella  
**Última actualización:** Octubre 16, 2025

---

## 🎯 Siguiente Funcionalidad

Después de validar que el sistema funciona correctamente en producción, considerar:

1. **Optimización de imágenes:** Redimensionar automáticamente
2. **Galería pública:** Slider en la vista del anuncio
3. **Estadísticas:** Dashboard con uso de espacio
4. **Thumbnails:** Generar versiones pequeñas

---

**Estado actual:** ✅ LISTO PARA DEPLOYMENT  
**Commit principal:** `2a9741d`  
**Commit docs:** `5aea63e`
