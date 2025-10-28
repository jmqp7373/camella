# ⚠️ CRÍTICO - FALTA SUBIR API.PHP

## 🚨 ERROR DETECTADO

**Error en producción:** "Endpoint no encontrado"  
**Causa:** El archivo `api.php` NO fue subido a producción  
**Solución:** Subir `api.php` inmediatamente

---

## 📦 ARCHIVO FALTANTE

### ❌ Archivo que NO se ha subido:
```
api.php  →  /public_html/api.php
```

Este archivo contiene los endpoints críticos:
- ✅ `saveAnuncio` - Guardar/actualizar anuncio
- ✅ `uploadImage` - Subir imagen
- ✅ `deleteImage` - Eliminar imagen  
- ✅ `getImages` - Obtener imágenes del anuncio
- ✅ `deleteAnuncio` - Eliminar anuncio completo

---

## 🔧 SOLUCIÓN INMEDIATA

### Paso 1: Subir api.php
1. Conectar a Hostinger vía FileZilla
2. Navegar a `/public_html/`
3. Subir el archivo `api.php` (raíz del proyecto)
4. Verificar permisos: 644

### Paso 2: Verificar que funciona
1. Abrir: https://camella.com.co/api.php?action=getImages&anuncio_id=1
2. Debe retornar JSON (no "Endpoint no encontrado")

---

## 📋 LISTA COMPLETA DE ARCHIVOS A SUBIR

```
✅ api.php                                →  /public_html/
✅ controllers/ImageUploadController.php  →  /public_html/controllers/
✅ views/bloques/publicar.php            →  /public_html/views/bloques/
✅ views/bloques/bloque_anuncios.php     →  /public_html/views/bloques/
```

---

## 🧪 VERIFICACIÓN POST-DESPLIEGUE

### Test 1: Verificar endpoints disponibles
```bash
# Debe retornar: {"success":false,"message":"No estás autenticado"}
curl https://camella.com.co/api.php?action=saveAnuncio

# Debe retornar: {"success":false,"message":"ID del anuncio no proporcionado"}
curl https://camella.com.co/api.php?action=getImages
```

### Test 2: Probar desde navegador
1. Acceder a: https://camella.com.co/views/bloques/publicar.php
2. Abrir consola del navegador (F12)
3. El error "Endpoint no encontrado" debe desaparecer

---

**Fecha:** 17 de octubre de 2025  
**Prioridad:** 🔴 CRÍTICA  
**Estado:** ⚠️ PENDIENTE DE SUBIR api.php
