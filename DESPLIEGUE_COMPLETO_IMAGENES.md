# 📦 DESPLIEGUE COMPLETO - Fix Sistema de Imágenes

## 🎯 Fecha: 17 de octubre de 2025
## 🔧 Commits: a62a6f4, 072ee74, 6a78cad

---

## 📁 ARCHIVOS A SUBIR VÍA FILEZILLA/FTP

### PASO 1: Subir archivos PHP actualizados

Subir los siguientes archivos a sus respectivas ubicaciones en `/public_html/`:

```
✅ controllers/ImageUploadController.php  →  /public_html/controllers/
✅ views/bloques/publicar.php            →  /public_html/views/bloques/
✅ views/bloques/bloque_anuncios.php     →  /public_html/views/bloques/
```

### ⚠️ NO SUBIR:
```
❌ config/config.php  (ya está correctamente configurado en producción)
```

---

## 💾 PASO 2: SCRIPT SQL EN PRODUCCIÓN

### 📋 Instrucciones:
1. Acceder a **phpMyAdmin** en Hostinger
2. Seleccionar base de datos: `u179023609_camella_db`
3. Ir a pestaña **SQL**
4. **IMPORTANTE: Hacer backup de la base de datos ANTES de ejecutar**
5. Copiar y ejecutar este script:

```sql
-- ============================================
-- SCRIPT: Corregir rutas de imágenes
-- FUNCIÓN: Eliminar barra inicial de las rutas
-- FECHA: 17 de octubre de 2025
-- ============================================

-- PASO 1: Verificar cuántas rutas tienen / inicial
SELECT COUNT(*) as total_con_barra
FROM anuncio_imagenes 
WHERE ruta LIKE '/%';

-- PASO 2: Ver ejemplos de rutas actuales (antes del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%'
LIMIT 10;

-- PASO 3: Actualizar rutas (eliminar / inicial)
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';

-- PASO 4: Verificar cambios (después del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
ORDER BY id DESC
LIMIT 10;

-- PASO 5: Verificar que no queden rutas con / inicial
SELECT COUNT(*) as rutas_con_barra_restantes
FROM anuncio_imagenes 
WHERE ruta LIKE '/%';

-- RESULTADO ESPERADO:
-- ANTES:  /assets/images/anuncios/anuncio_2_1729123456.jpg
-- DESPUÉS: assets/images/anuncios/anuncio_2_1729123456.jpg
-- PASO 5: Debe retornar 0 rutas con barra
```

---

## 📝 RESUMEN DE CAMBIOS

### 1️⃣ **ImageUploadController.php**
**Cambio:** Línea 138
```php
// ANTES:
$relativePath = '/assets/images/anuncios/';

// DESPUÉS:
$relativePath = 'assets/images/anuncios/';
```
**Impacto:** Nuevas imágenes se guardan sin `/` inicial

---

### 2️⃣ **publicar.php** (Formulario de edición)
**Cambios:**
- Agregada variable JavaScript: `const baseUrl = '<?= SITE_URL ?>';`
- Mejorada función `renderImages()` para construir URLs correctamente
- Maneja rutas con y sin `/` inicial
- Agregado logging de errores: `onerror="console.error(...)"`

**Impacto:** Imágenes se muestran correctamente en formulario de edición

---

### 3️⃣ **bloque_anuncios.php** (Dashboard)
**Cambios:**
- Agregado: `require_once __DIR__ . '/../../config/config.php';`
- Query SQL actualizada para obtener imagen desde `anuncio_imagenes`:
  ```sql
  (SELECT ai.ruta FROM anuncio_imagenes ai 
   WHERE ai.anuncio_id = a.id 
   ORDER BY ai.orden LIMIT 1) as imagen_principal
  ```
- URL de imagen construida con `SITE_URL`:
  ```php
  $imagePath = $anuncio['imagen_principal'];
  if (strpos($imagePath, '/') !== 0) {
      $imagePath = '/' . $imagePath;
  }
  $imageUrl = SITE_URL . $imagePath;
  ```

**Impacto:** Imágenes se muestran correctamente en tarjetas del dashboard

---

## ✅ CHECKLIST DE DESPLIEGUE

### ⚙️ Preparación (CRÍTICO):
- [ ] **HACER BACKUP COMPLETO de la base de datos**
- [ ] Hacer backup de `/public_html/controllers/ImageUploadController.php`
- [ ] Hacer backup de `/public_html/views/bloques/publicar.php`
- [ ] Hacer backup de `/public_html/views/bloques/bloque_anuncios.php`

### 📤 Subida de archivos:
- [ ] Conectar a Hostinger vía **FileZilla**
- [ ] Navegar a `/public_html/`
- [ ] Subir `controllers/ImageUploadController.php`
- [ ] Subir `views/bloques/publicar.php`
- [ ] Subir `views/bloques/bloque_anuncios.php`
- [ ] Verificar permisos (644 para archivos PHP)

### 💾 Base de datos:
- [ ] Acceder a **phpMyAdmin** en Hostinger
- [ ] Seleccionar base de datos `u179023609_camella_db`
- [ ] Ejecutar **PASO 1** del script SQL (contar rutas con /)
- [ ] Ejecutar **PASO 2** del script SQL (ver ejemplos)
- [ ] Ejecutar **PASO 3** del script SQL (actualizar rutas) ⚠️
- [ ] Ejecutar **PASO 4** del script SQL (verificar cambios)
- [ ] Ejecutar **PASO 5** del script SQL (verificar que no queden rutas con /)

### 🧪 Pruebas post-despliegue (OBLIGATORIAS):
- [ ] **Dashboard:** https://camella.com.co/views/publicante/dashboard.php
  - [ ] Verificar que las imágenes de los anuncios se muestran
  - [ ] No debe haber iconos de imagen rota
  
- [ ] **Formulario Edición:** https://camella.com.co/views/bloques/publicar.php?modo=editar&id=X
  - [ ] Verificar que las imágenes existentes se muestran
  - [ ] Subir una nueva imagen de prueba
  - [ ] Verificar que la nueva imagen se muestra inmediatamente
  - [ ] Eliminar una imagen de prueba
  
- [ ] **Formulario Solo Lectura:** https://camella.com.co/views/bloques/publicar.php?modo=ver&id=X
  - [ ] Verificar que las imágenes se muestran
  - [ ] Verificar que NO aparecen botones de eliminar
  
- [ ] **Consola del navegador (F12):**
  - [ ] No debe haber errores 404 de imágenes
  - [ ] No debe haber errores JavaScript
  - [ ] URLs de imágenes deben ser: `https://camella.com.co/assets/images/anuncios/...`

### 📊 Validación de datos:
- [ ] Verificar en phpMyAdmin que las rutas están sin `/` inicial:
  ```sql
  SELECT ruta FROM anuncio_imagenes LIMIT 5;
  ```
  - Debe retornar: `assets/images/anuncios/archivo.jpg`
  - NO debe retornar: `/assets/images/anuncios/archivo.jpg`

---

## 🚨 PLAN DE ROLLBACK (si algo falla)

### Si las imágenes NO se muestran después del despliegue:

#### Opción A: Revertir SQL solamente
```sql
-- Volver a agregar / inicial a las rutas
UPDATE anuncio_imagenes 
SET ruta = CONCAT('/', ruta) 
WHERE ruta NOT LIKE '/%' AND ruta LIKE 'assets/%';
```

#### Opción B: Restaurar archivos PHP desde backup
1. Conectar a FileZilla
2. Restaurar los 3 archivos PHP desde backup local
3. Limpiar caché del navegador (Ctrl+Shift+Supr)
4. Recargar páginas

#### Opción C: Restaurar base de datos completa
1. phpMyAdmin → Import
2. Seleccionar archivo de backup `.sql`
3. Ejecutar importación
4. Esperar confirmación

---

## 🔍 DEBUGGING EN PRODUCCIÓN

### Si hay problemas después del despliegue:

#### 1. Verificar configuración de SITE_URL
```bash
# Conectar vía SSH a Hostinger
cat public_html/config/config.php | grep SITE_URL
```
Debe retornar: `define('SITE_URL', 'https://camella.com.co');`

#### 2. Verificar permisos de carpeta de imágenes
```bash
ls -la public_html/assets/images/anuncios/
```
- Carpeta debe tener permisos: `755`
- Archivos deben tener permisos: `644`

#### 3. Verificar rutas en base de datos
```sql
-- Ver las últimas 10 rutas guardadas
SELECT id, anuncio_id, ruta, created_at 
FROM anuncio_imagenes 
ORDER BY id DESC 
LIMIT 10;
```
Formato correcto: `assets/images/anuncios/anuncio_X_timestamp.jpg`

#### 4. Verificar URLs en navegador
- Abrir consola del navegador (F12)
- Ir a pestaña **Network**
- Filtrar por "images"
- Buscar peticiones con código 404
- Copiar URL completa que está fallando

#### 5. Verificar que archivos físicos existen
```bash
# Vía SSH en Hostinger
ls -lh public_html/assets/images/anuncios/ | tail -20
```

---

## 📊 MÉTRICAS DE ÉXITO

Después del despliegue exitoso:
- ✅ **0 errores 404** de imágenes en consola del navegador
- ✅ **100% de imágenes** visibles en dashboard
- ✅ **100% de imágenes** visibles en formulario de edición
- ✅ **Subida de imágenes** funcionando correctamente
- ✅ **Eliminación de imágenes** funcionando correctamente
- ✅ **Todas las rutas en BD** sin `/` inicial

---

## 🔗 DOCUMENTACIÓN RELACIONADA

- `DEPLOYMENT_IMAGENES_ROTAS.md` - Documentación técnica detallada
- `FILES_TO_DEPLOY_IMAGENES_FIX.md` - Checklist detallado (versión anterior)
- `GUIA_SISTEMA_IMAGENES.md` - Guía completa del sistema de imágenes
- `fix_image_paths.sql` - Script SQL standalone

---

## 📞 CONTACTO EN CASO DE PROBLEMAS

Si después del despliegue persisten problemas:
1. ✅ Verificar todos los pasos del checklist
2. ✅ Ejecutar pasos de debugging
3. ✅ Revisar logs del servidor (Hostinger → Error Logs)
4. ✅ Capturar evidencia:
   - Screenshots de consola del navegador (F12)
   - URLs exactas donde falla
   - Mensajes de error específicos
   - Resultado de queries SQL de verificación

---

## ✨ RESULTADO ESPERADO FINAL

### URLs de imágenes en producción:
```
https://camella.com.co/assets/images/anuncios/anuncio_1_1760690791_d068b4861dc5e81b.png
https://camella.com.co/assets/images/anuncios/anuncio_2_1760691067_c6a161289b8883bf.png
https://camella.com.co/assets/images/anuncios/anuncio_5_1760691212_0d0ee88e04f2de68.png
```

### Rutas en base de datos:
```
assets/images/anuncios/anuncio_1_1760690791_d068b4861dc5e81b.png
assets/images/anuncios/anuncio_2_1760691067_c6a161289b8883bf.png
assets/images/anuncios/anuncio_5_1760691212_0d0ee88e04f2de68.png
```

### Comportamiento esperado:
- ✅ Dashboard muestra todas las imágenes correctamente
- ✅ Formulario de edición muestra galería de imágenes
- ✅ Se pueden subir nuevas imágenes (máximo 5)
- ✅ Se pueden eliminar imágenes existentes
- ✅ Modo vista solo muestra imágenes sin botones
- ✅ No hay errores en consola del navegador
- ✅ Fallback a imagen por defecto si no hay imagen

---

**Última actualización:** 17 de octubre de 2025  
**Commits incluidos:** 
- `a62a6f4` - Fix imágenes rotas en formulario
- `072ee74` - Docs checklist de despliegue
- `6a78cad` - Fix imágenes rotas en dashboard

**Estado:** ✅ Listo para despliegue a producción  
**Prioridad:** 🔴 Alta  
**Tiempo estimado:** 15-20 minutos
