# Sistema de Cache Busting - Camella.com.co

## 🚀 Descripción
Este proyecto implementa un sistema automático de **cache busting** para evitar que los navegadores utilicen versiones en caché desactualizadas de los archivos CSS.

## 🔧 Implementación

### Función Principal
```php
function getCacheBuster($filepath) {
    if (file_exists($filepath)) {
        return '?v=' . filemtime($filepath);
    }
    return '?v=' . time();
}
```

### Funcionamiento
1. **Si el archivo existe**: Usa `filemtime()` para obtener la fecha de última modificación
2. **Si no existe**: Usa `time()` como fallback para generar un timestamp actual
3. **Resultado**: Agrega un parámetro único `?v=TIMESTAMP` a cada CSS

### Archivos CSS con Cache Busting

#### ✅ Archivos Locales (CON cache busting)
- `assets/css/colors.css` → `colors.css?v=1696723890`
- `assets/css/style.css` → `style.css?v=1696723891`

#### ⏭️ Archivos Externos (SIN cache busting)
- Font Awesome CDN (no necesario, mantenido por CDN)

## 📁 Ubicación del Código

### Archivo Principal
- **`partials/header.php`**: Contiene la función y las referencias CSS

### Función Cache Buster
```php
<?php
function getCacheBuster($filepath) {
    if (file_exists($filepath)) {
        return '?v=' . filemtime($filepath);
    }
    return '?v=' . time();
}
?>
```

### Uso en Enlaces CSS
```html
<!-- ANTES -->
<link rel="stylesheet" href="assets/css/style.css">

<!-- DESPUÉS -->
<link rel="stylesheet" href="assets/css/style.css<?= getCacheBuster('assets/css/style.css'); ?>">
```

## 🎯 Beneficios

### ✅ Ventajas Implementadas
1. **Cache Inteligente**: Usa fecha de modificación real del archivo
2. **Fallback Robusto**: Time() si el archivo no existe
3. **Global**: Se aplica a todas las vistas que usen header.php
4. **Automático**: No requiere intervención manual
5. **Eficiente**: Solo actualiza cuando el archivo cambia realmente

### 🔄 Comportamiento
- **Archivo sin cambios**: Mantiene el mismo timestamp (permite cache)
- **Archivo modificado**: Genera nuevo timestamp (fuerza actualización)
- **Desarrollo**: Siempre carga la versión más reciente
- **Producción**: Optimiza el cache según cambios reales

## 📋 Archivos Afectados

### Modificados
- ✅ `partials/header.php` - Función y referencias CSS actualizadas
- ✅ `assets/css/colors.css` - Se carga con cache busting
- ✅ `assets/css/style.css` - Se carga con cache busting

### Vistas que Heredan el Sistema
Todas las vistas que incluyen `partials/header.php`:
- ✅ `views/home.php`
- ✅ `views/empresas.php`
- ✅ `views/talentos.php`
- ✅ `views/contacto.php`
- ✅ `views/privacidad.php`
- ✅ `views/terminos.php`
- ✅ `views/ayuda.php`

## 🚀 Deploy
El sistema se despliega automáticamente via GitHub Actions a GoDaddy cuando se hace push a main.

## 📊 Ejemplo de URLs Generadas
```
Desarrollo:
assets/css/colors.css?v=1696723890
assets/css/style.css?v=1696723891

Producción (después de modificar colors.css):
assets/css/colors.css?v=1696725234  ← Nuevo timestamp
assets/css/style.css?v=1696723891   ← Sin cambios, mismo timestamp
```

## 🔍 Verificación
Para verificar que funciona:
1. Inspecciona el código fuente de cualquier página
2. Busca las etiquetas `<link rel="stylesheet">`
3. Confirma que terminen con `?v=TIMESTAMP`
4. Modifica un CSS y recarga: debe cambiar el timestamp

---
**Implementado el:** Octubre 7, 2025  
**Versión:** 1.0  
**Estado:** ✅ Activo en producción