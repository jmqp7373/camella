# 📦 Archivos para Desplegar - Fix Imágenes Rotas

## 🎯 Fecha: 17 de octubre de 2025
## 🔧 Tipo: Bug Fix - Sistema de imágenes

---

## 📁 ARCHIVOS A SUBIR VÍA FILEZILLA/FTP

### 1️⃣ CONTROLADORES
```
controllers/ImageUploadController.php
```
**Ubicación destino:** `/public_html/controllers/ImageUploadController.php`
**Cambio:** Línea 138 - Guardar rutas sin `/` inicial

---

### 2️⃣ VISTAS
```
views/bloques/publicar.php
```
**Ubicación destino:** `/public_html/views/bloques/publicar.php`
**Cambios:** 
- Agregar variable JavaScript `baseUrl`
- Mejorar función `renderImages()` para manejar ambos formatos de ruta

---

## ⚠️ ARCHIVOS QUE **NO** SE DEBEN MODIFICAR EN PRODUCCIÓN

### ❌ NO subir este archivo:
```
config/config.php
```
**Razón:** Este archivo tiene configuraciones diferentes en local vs producción:
- **Local:** `SITE_URL = 'http://localhost/camella.com.co'`
- **Producción:** `SITE_URL = 'https://camella.com.co'` (ya correcto)

---

## 💾 SCRIPT SQL A EJECUTAR EN PRODUCCIÓN

### 📋 Pasos:
1. Acceder a **phpMyAdmin** en Hostinger
2. Seleccionar base de datos: `u179023609_camella_db`
3. Ir a pestaña **SQL**
4. Copiar y ejecutar este script:

```sql
-- ============================================
-- SCRIPT: Corregir rutas de imágenes
-- FUNCIÓN: Eliminar barra inicial de las rutas
-- ============================================

-- PASO 1: Verificar rutas actuales (antes del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%'
LIMIT 20;

-- PASO 2: Actualizar rutas (eliminar / inicial)
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';

-- PASO 3: Verificar cambios (después del cambio)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
LIMIT 20;

-- RESULTADO ESPERADO:
-- ANTES:  /assets/images/anuncios/anuncio_2_1729123456.jpg
-- DESPUÉS: assets/images/anuncios/anuncio_2_1729123456.jpg
```

---

## ✅ CHECKLIST DE DESPLIEGUE

### Preparación:
- [ ] Hacer **backup completo** de la base de datos
- [ ] Hacer backup de carpeta `/public_html/controllers/`
- [ ] Hacer backup de carpeta `/public_html/views/bloques/`

### Subida de archivos:
- [ ] Conectar a Hostinger vía FileZilla
- [ ] Subir `controllers/ImageUploadController.php`
- [ ] Subir `views/bloques/publicar.php`
- [ ] Verificar que los archivos se subieron correctamente

### Base de datos:
- [ ] Acceder a phpMyAdmin
- [ ] Ejecutar PASO 1 del script SQL (verificar rutas actuales)
- [ ] Ejecutar PASO 2 del script SQL (actualizar rutas)
- [ ] Ejecutar PASO 3 del script SQL (verificar cambios)
- [ ] Confirmar que las rutas ahora están sin `/` inicial

### Pruebas post-despliegue:
- [ ] Abrir https://camella.com.co/views/bloques/publicar.php?modo=editar&id=X
- [ ] Verificar que las imágenes existentes se muestran
- [ ] Subir una nueva imagen de prueba
- [ ] Verificar que la nueva imagen se guarda y muestra correctamente
- [ ] Eliminar una imagen de prueba
- [ ] Abrir consola del navegador (F12) - verificar que no hay errores 404
- [ ] Probar con modo `ver` (solo lectura)

---

## 🚨 ROLLBACK (si algo sale mal)

### Si las imágenes siguen rotas después del despliegue:

#### Opción 1: Revertir script SQL
```sql
-- Volver a agregar / inicial a las rutas
UPDATE anuncio_imagenes 
SET ruta = CONCAT('/', ruta) 
WHERE ruta NOT LIKE '/%' AND ruta LIKE 'assets/%';
```

#### Opción 2: Restaurar archivos desde backup
1. Restaurar `controllers/ImageUploadController.php` desde backup
2. Restaurar `views/bloques/publicar.php` desde backup
3. Limpiar caché del navegador (Ctrl+Shift+Supr)

#### Opción 3: Restaurar base de datos desde backup
1. Ir a phpMyAdmin → Import
2. Seleccionar archivo de backup `.sql`
3. Ejecutar importación

---

## 🔍 DEBUGGING POST-DESPLIEGUE

### Si hay problemas con las imágenes:

1. **Abrir consola del navegador (F12)**
   - Buscar errores 404 en pestaña Network
   - Buscar mensajes "Error cargando imagen:" en Console

2. **Verificar URLs generadas:**
   - Deben ser: `https://camella.com.co/assets/images/anuncios/archivo.jpg`
   - NO deben ser: `https://camella.com.co//assets/...` (doble barra)
   - NO deben ser: `http://localhost/camella/...` (URL local)

3. **Verificar permisos de carpeta:**
   ```bash
   # Vía terminal SSH en Hostinger:
   chmod 755 public_html/assets/images/anuncios/
   chmod 644 public_html/assets/images/anuncios/*
   ```

4. **Verificar rutas en BD:**
   ```sql
   SELECT id, anuncio_id, ruta 
   FROM anuncio_imagenes 
   ORDER BY id DESC 
   LIMIT 10;
   ```
   - Las rutas deben empezar con `assets/` (sin `/` inicial)

---

## 📊 RESUMEN TÉCNICO

### Antes del fix:
```
❌ SITE_URL: http://localhost/camella (incorrecto local)
❌ Rutas BD: /assets/images/anuncios/file.jpg (con / inicial)
❌ URL final: http://localhost/camella/assets/... (malformada)
❌ Resultado: Imágenes rotas
```

### Después del fix:
```
✅ SITE_URL: 
   - Local: http://localhost/camella.com.co
   - Producción: https://camella.com.co
✅ Rutas BD: assets/images/anuncios/file.jpg (sin / inicial)
✅ JavaScript: Agrega / al construir URL
✅ URL final: https://camella.com.co/assets/images/anuncios/file.jpg
✅ Resultado: Imágenes funcionando correctamente
```

---

## 📞 CONTACTO EN CASO DE PROBLEMAS

Si después del despliegue hay problemas:
1. Revisar esta documentación completa
2. Ejecutar pasos de debugging
3. Contactar al desarrollador con:
   - Capturas de pantalla de consola del navegador
   - URL exacta donde falla
   - Mensajes de error específicos

---

## ✨ RESULTADO ESPERADO

Después del despliegue exitoso:
- ✅ Todas las imágenes existentes se muestran correctamente
- ✅ Se pueden subir nuevas imágenes sin problemas
- ✅ Las nuevas imágenes se guardan con formato de ruta estandarizado
- ✅ Se pueden eliminar imágenes correctamente
- ✅ Modo solo lectura muestra imágenes sin botones de eliminar
- ✅ No hay errores 404 en la consola del navegador

---

**Última actualización:** 17 de octubre de 2025  
**Commit:** a62a6f4  
**Estado:** ✅ Listo para despliegue
