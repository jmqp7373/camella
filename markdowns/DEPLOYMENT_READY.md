# 🚀 DESPLIEGUE A PRODUCCIÓN - 17 de octubre de 2025

## 📦 ARCHIVOS A SUBIR VÍA FILEZILLA/FTP

### ⚠️ CRÍTICO - Subir estos 4 archivos:

```
1. api.php                                →  /public_html/api.php
2. controllers/ImageUploadController.php  →  /public_html/controllers/ImageUploadController.php
3. views/bloques/publicar.php            →  /public_html/views/bloques/publicar.php
4. views/bloques/bloque_anuncios.php     →  /public_html/views/bloques/bloque_anuncios.php
```

---

## 📋 CHECKLIST DE DESPLIEGUE

### PASO 1: Conectar a Hostinger
- [ ] Abrir FileZilla
- [ ] Conectar al servidor de Hostinger
- [ ] Navegar a `/public_html/`

### PASO 2: Backup (IMPORTANTE)
- [ ] Descargar backup de `api.php` actual (si existe)
- [ ] Descargar backup de `controllers/ImageUploadController.php`
- [ ] Descargar backup de `views/bloques/publicar.php`
- [ ] Descargar backup de `views/bloques/bloque_anuncios.php`

### PASO 3: Subir archivos actualizados
- [ ] Subir `api.php` a `/public_html/`
- [ ] Subir `ImageUploadController.php` a `/public_html/controllers/`
- [ ] Subir `publicar.php` a `/public_html/views/bloques/`
- [ ] Subir `bloque_anuncios.php` a `/public_html/views/bloques/`

### PASO 4: Verificar permisos
- [ ] `api.php` → permisos 644
- [ ] `ImageUploadController.php` → permisos 644
- [ ] `publicar.php` → permisos 644
- [ ] `bloque_anuncios.php` → permisos 644

---

## 💾 PASO 5: EJECUTAR SCRIPT SQL

### Base de datos: `u179023609_camella_db`

**⚠️ IMPORTANTE: Hacer backup de la base de datos primero**

1. Acceder a phpMyAdmin en Hostinger
2. Seleccionar base de datos `u179023609_camella_db`
3. Ir a pestaña **SQL**
4. Ejecutar este script:

```sql
-- ============================================
-- PASO 1: Verificar rutas con formato incorrecto
-- ============================================
SELECT 
    COUNT(*) as total_rutas_con_barra,
    'Rutas con / inicial (formato incorrecto)' as descripcion
FROM anuncio_imagenes 
WHERE ruta LIKE '/%';

-- Ver ejemplos de rutas incorrectas
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%'
LIMIT 10;

-- ============================================
-- PASO 2: Corregir rutas (eliminar / inicial)
-- ============================================
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';

-- ============================================
-- PASO 3: Verificar corrección
-- ============================================
SELECT 
    COUNT(*) as total_rutas_corregidas,
    'Rutas sin / inicial (formato correcto)' as descripcion
FROM anuncio_imagenes 
WHERE ruta NOT LIKE '/%' AND ruta LIKE 'assets/%';

-- Ver ejemplos de rutas corregidas
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
ORDER BY id DESC
LIMIT 10;

-- ============================================
-- PASO 4: Verificar que no quedan rutas incorrectas
-- ============================================
SELECT COUNT(*) as rutas_incorrectas_restantes
FROM anuncio_imagenes 
WHERE ruta LIKE '/%';
-- Debe retornar 0

-- ============================================
-- RESULTADO ESPERADO:
-- ANTES:  /assets/images/anuncios/anuncio_5_1760690791.png
-- DESPUÉS: assets/images/anuncios/anuncio_5_1760690791.png
-- ============================================
```

---

## 🧪 PASO 6: PRUEBAS POST-DESPLIEGUE

### Test 1: Verificar endpoint de API
```
URL: https://camella.com.co/api.php?action=getImages&anuncio_id=1
Resultado esperado: JSON con lista de imágenes o mensaje de error (NO "Endpoint no encontrado")
```

### Test 2: Dashboard - Verificar imágenes en tarjetas
- [ ] Abrir: https://camella.com.co/views/publicante/dashboard.php
- [ ] **Verificar:** Las imágenes de los anuncios se muestran (no broken images)
- [ ] **Verificar:** No hay errores en consola (F12)

### Test 3: Crear nuevo anuncio
- [ ] Abrir: https://camella.com.co/views/bloques/publicar.php
- [ ] Llenar título, descripción, precio
- [ ] Hacer clic en "Publicar anuncio"
- [ ] **Verificar:** El anuncio se crea sin errores
- [ ] **Verificar:** La página cambia a modo editar automáticamente
- [ ] **Verificar:** La sección de fotos se activa
- [ ] **Verificar:** Aparece mensaje "¡Anuncio creado! Ahora puedes agregar fotos"

### Test 4: Subir imágenes
- [ ] En el anuncio recién creado, hacer clic en área de fotos
- [ ] Seleccionar 2-3 imágenes (JPG, PNG)
- [ ] **Verificar:** Las imágenes se suben correctamente
- [ ] **Verificar:** Las imágenes se muestran en la galería
- [ ] **Verificar:** El contador muestra "X de 5 fotos subidas"

### Test 5: Editar anuncio existente
- [ ] Abrir: https://camella.com.co/views/bloques/publicar.php?modo=editar&id=5
- [ ] **Verificar:** Las imágenes existentes se muestran
- [ ] **Verificar:** Se pueden subir nuevas imágenes
- [ ] **Verificar:** Se pueden eliminar imágenes
- [ ] Hacer cambios y guardar
- [ ] **Verificar:** Los cambios se guardan correctamente

### Test 6: Ver anuncio (solo lectura)
- [ ] Abrir: https://camella.com.co/views/bloques/publicar.php?modo=ver&id=5
- [ ] **Verificar:** Las imágenes se muestran
- [ ] **Verificar:** NO aparecen botones de eliminar
- [ ] **Verificar:** Todos los campos están deshabilitados

### Test 7: Consola del navegador
- [ ] Abrir cualquier página con anuncios
- [ ] Presionar F12 → pestaña Console
- [ ] **Verificar:** No hay errores 404 de imágenes
- [ ] **Verificar:** No hay errores de "Endpoint no encontrado"
- [ ] Ir a pestaña Network → filtrar por "images"
- [ ] **Verificar:** Todas las imágenes cargan con código 200

---

## 📊 RESUMEN DE CAMBIOS

### 1. **api.php** - Nuevos endpoints
- ✅ `saveAnuncio` - Crear/actualizar anuncio
- ✅ `uploadImage` - Subir imagen
- ✅ `deleteImage` - Eliminar imagen
- ✅ `getImages` - Obtener imágenes del anuncio
- ✅ `deleteAnuncio` - Eliminar anuncio completo

### 2. **ImageUploadController.php** - Formato de rutas
- ✅ Guarda rutas sin `/` inicial: `assets/images/anuncios/file.jpg`
- ✅ Validaciones de tipo y tamaño de archivo
- ✅ Máximo 5 imágenes por anuncio

### 3. **publicar.php** - Mejoras UX
- ✅ Modo nuevo: Permite crear anuncio y luego subir fotos
- ✅ Transición automática a modo editar después de crear
- ✅ URL se actualiza sin recargar página
- ✅ Sección de fotos se activa automáticamente
- ✅ Renderizado correcto de imágenes con SITE_URL

### 4. **bloque_anuncios.php** - Fix imágenes dashboard
- ✅ Obtiene imagen desde tabla `anuncio_imagenes`
- ✅ Construye URL correctamente con SITE_URL
- ✅ Maneja rutas con y sin `/` inicial

---

## 🚨 PROBLEMAS CONOCIDOS Y SOLUCIONES

### Si "Endpoint no encontrado":
**Causa:** `api.php` no se subió correctamente  
**Solución:** Verificar que `api.php` esté en `/public_html/api.php`

### Si imágenes aparecen rotas:
**Causa:** Rutas en BD con formato incorrecto  
**Solución:** Ejecutar script SQL de corrección (Paso 5)

### Si no se pueden subir imágenes:
**Causa:** Carpeta sin permisos o no existe  
**Solución (vía SSH):**
```bash
mkdir -p /home/u179023609/public_html/assets/images/anuncios
chmod 755 /home/u179023609/public_html/assets/images/anuncios
```

### Si error al crear anuncio:
**Causa:** Tabla `anuncios` sin columna `updated_at` o `status`  
**Solución:** Ejecutar en phpMyAdmin:
```sql
-- Verificar columnas
DESCRIBE anuncios;

-- Si falta updated_at:
ALTER TABLE anuncios 
ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Si falta status:
ALTER TABLE anuncios 
ADD COLUMN status VARCHAR(20) DEFAULT 'activo' AFTER precio;
```

---

## 🔄 ROLLBACK (si algo sale mal)

### Opción A: Restaurar archivos desde backup
1. Conectar a FileZilla
2. Subir archivos de backup guardados en Paso 2
3. Limpiar caché del navegador (Ctrl+Shift+Supr)

### Opción B: Revertir SQL
```sql
-- Volver a agregar / inicial a las rutas
UPDATE anuncio_imagenes 
SET ruta = CONCAT('/', ruta) 
WHERE ruta NOT LIKE '/%' AND ruta LIKE 'assets/%';
```

### Opción C: Restaurar base de datos completa
1. phpMyAdmin → Import
2. Seleccionar archivo de backup `.sql`
3. Ejecutar importación

---

## ✅ CHECKLIST FINAL

### Pre-despliegue:
- [x] Código commiteado a GitHub
- [x] Documentación actualizada
- [ ] Backup de archivos actuales descargado
- [ ] Backup de base de datos descargado

### Durante despliegue:
- [ ] 4 archivos PHP subidos correctamente
- [ ] Permisos verificados (644)
- [ ] Script SQL ejecutado
- [ ] Rutas de imágenes corregidas

### Post-despliegue:
- [ ] Test 1: API funciona (no "Endpoint no encontrado")
- [ ] Test 2: Dashboard muestra imágenes
- [ ] Test 3: Crear anuncio funciona
- [ ] Test 4: Subir imágenes funciona
- [ ] Test 5: Editar anuncio funciona
- [ ] Test 6: Modo ver funciona
- [ ] Test 7: No hay errores en consola

---

## 📞 CONTACTO EN CASO DE PROBLEMAS

Si después del despliegue hay problemas:
1. ✅ Revisar todos los tests post-despliegue
2. ✅ Capturar evidencia:
   - Screenshots de errores
   - Logs de consola del navegador (F12)
   - URLs exactas donde falla
3. ✅ Ejecutar queries de verificación en phpMyAdmin
4. ✅ Si es necesario, ejecutar rollback

---

**Commits incluidos en este despliegue:**
- `a62a6f4` - Fix imágenes formulario
- `6a78cad` - Fix imágenes dashboard
- `c058b05` - Documentación api.php
- `feea79a` - Feature nuevo flujo de fotos

**Fecha:** 17 de octubre de 2025  
**Prioridad:** 🔴 Alta  
**Tiempo estimado:** 20-30 minutos  
**Estado:** ✅ Listo para despliegue
