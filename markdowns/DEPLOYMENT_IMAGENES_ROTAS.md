# Despliegue: Corrección de Imágenes Rotas

**Fecha:** 17 de octubre de 2025
**Tipo:** Bug Fix - Sistema de imágenes
**Prioridad:** Alta

## 🐛 Problema Identificado

Las imágenes de los anuncios aparecían rotas (broken images) en el formulario de edición porque:

1. **Error de configuración de SITE_URL**: 
   - Local: configurado como `http://localhost/camella` 
   - Real: debía ser `http://localhost/camella.com.co`

2. **Inconsistencia en rutas de imágenes**:
   - Algunas rutas guardadas con `/` inicial: `/assets/images/anuncios/file.jpg`
   - Otras sin `/` inicial: `assets/images/anuncios/file.jpg`

3. **PHP en JavaScript no interpolaba**:
   - `<?= SITE_URL ?>` dentro de template strings no se procesaba correctamente
   - Las URLs resultantes eran malformadas

## ✅ Archivos Modificados

### 1. `config/config.php`
**Cambio:** Actualización de SITE_URL para desarrollo local
```php
// ANTES:
define('SITE_URL', 'http://localhost/camella');

// DESPUÉS:
define('SITE_URL', 'http://localhost/camella.com.co');
```
**Razón:** Coincidir con la estructura real de carpetas en XAMPP

---

### 2. `controllers/ImageUploadController.php`
**Cambio:** Línea 138 - Eliminar barra inicial de ruta relativa
```php
// ANTES:
$relativePath = '/assets/images/anuncios/';

// DESPUÉS:
$relativePath = 'assets/images/anuncios/';
```
**Razón:** Estandarizar formato de rutas guardadas en BD (sin `/` inicial)

---

### 3. `views/bloques/publicar.php`
**Cambios:**

#### a) Agregar variable JavaScript baseUrl (línea ~496)
```javascript
const baseUrl = '<?= SITE_URL ?>'; // URL base del sitio
```

#### b) Función renderImages() mejorada (línea ~640)
```javascript
function renderImages() {
    const container = document.getElementById('imagesPreview');
    const counter = document.getElementById('currentCount');
    
    if (!container || !counter) return;
    
    container.innerHTML = currentImages.map(img => {
        // Construir URL correctamente - agregar / si la ruta no empieza con /
        const imagePath = img.ruta.startsWith('/') ? img.ruta : '/' + img.ruta;
        const imageUrl = baseUrl + imagePath;
        
        return `
        <div class="image-item">
            <img src="${imageUrl}" alt="Imagen ${img.orden}" 
                 onerror="console.error('Error cargando imagen:', '${imageUrl}')">
            ${!soloLectura ? `
            <button type="button" class="delete-btn" data-image-id="${img.id}">
                <i class="fas fa-times"></i>
            </button>
            ` : ''}
        </div>
        `;
    }).join('');
    
    // ... resto del código
}
```
**Razón:** 
- Pasar SITE_URL de PHP a JavaScript correctamente
- Manejar ambos formatos de ruta (con y sin `/` inicial)
- Agregar logging de errores para debugging

---

### 4. `fix_image_paths.sql` (NUEVO)
Script SQL para actualizar rutas existentes en la base de datos:
```sql
-- Actualizar todas las rutas que empiezan con /
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';
```

---

## 📋 Instrucciones de Despliegue

### A. Subir Archivos (vía FileZilla/FTP)

Subir los siguientes archivos a **public_html/**:

1. ✅ `config/config.php` - **NO MODIFICAR en producción**, solo local
2. ✅ `controllers/ImageUploadController.php`
3. ✅ `views/bloques/publicar.php`

### B. Ejecutar Script SQL (vía phpMyAdmin)

**⚠️ IMPORTANTE: Hacer backup de la base de datos antes de ejecutar**

1. Acceder a phpMyAdmin en Hostinger
2. Seleccionar base de datos `u179023609_camella_db`
3. Ir a pestaña **SQL**
4. Ejecutar el siguiente script:

```sql
-- Ver rutas actuales (verificación previa)
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
WHERE ruta LIKE '/%'
LIMIT 10;

-- Actualizar rutas (eliminar / inicial)
UPDATE anuncio_imagenes 
SET ruta = SUBSTRING(ruta, 2) 
WHERE ruta LIKE '/%';

-- Verificar cambios
SELECT id, anuncio_id, ruta, orden 
FROM anuncio_imagenes 
LIMIT 10;
```

5. Verificar que las rutas ahora sean: `assets/images/anuncios/archivo.jpg`

### C. Consideraciones Importantes

#### ❌ NO modificar en producción:
- `config/config.php` ya tiene SITE_URL correcto: `https://camella.com.co`
- Solo se modifica localmente para coincidir con `/camella.com.co`

#### ✅ Archivos a actualizar en producción:
- `controllers/ImageUploadController.php` - Nuevas imágenes sin `/` inicial
- `views/bloques/publicar.php` - Renderizado correcto de URLs

---

## 🧪 Pruebas Post-Despliegue

### 1. Verificar imágenes existentes
- [ ] Abrir un anuncio en modo edición
- [ ] Verificar que las imágenes existentes se muestran correctamente
- [ ] Revisar consola del navegador (F12) - no debe haber errores 404

### 2. Subir nuevas imágenes
- [ ] Subir una nueva imagen a un anuncio
- [ ] Verificar que la imagen se muestra inmediatamente
- [ ] Verificar en BD que la ruta se guardó sin `/` inicial

### 3. Eliminar imágenes
- [ ] Verificar que el botón X elimina imágenes correctamente
- [ ] Confirmar que el archivo físico se elimina del servidor

### 4. Modo solo lectura
- [ ] Abrir anuncio con `modo=ver`
- [ ] Confirmar que las imágenes se muestran
- [ ] Confirmar que no aparecen botones de eliminar

---

## 🔄 Rollback (si hay problemas)

### Si las imágenes siguen rotas después del despliegue:

1. **Revisar configuración de SITE_URL:**
```php
// En producción debe ser:
define('SITE_URL', 'https://camella.com.co');
```

2. **Revertir script SQL:**
```sql
-- Volver a agregar / inicial si es necesario
UPDATE anuncio_imagenes 
SET ruta = CONCAT('/', ruta) 
WHERE ruta NOT LIKE '/%';
```

3. **Verificar permisos de carpeta:**
```bash
chmod 755 /public_html/assets/images/anuncios/
```

---

## 📊 Impacto del Cambio

- **Usuarios afectados:** Todos los usuarios que editan anuncios
- **Funcionalidad crítica:** Sí (visualización y gestión de imágenes)
- **Tiempo estimado de despliegue:** 10-15 minutos
- **Requiere mantenimiento:** Sí (ejecutar script SQL)
- **Compatibilidad hacia atrás:** Sí (maneja ambos formatos de ruta)

---

## 📝 Notas Técnicas

### Formato de rutas estandarizado:
- **Base de datos:** `assets/images/anuncios/archivo.jpg` (sin `/` inicial)
- **JavaScript:** Agrega `/` automáticamente al construir URL
- **URL final:** `https://camella.com.co/assets/images/anuncios/archivo.jpg`

### Ventajas del nuevo formato:
1. ✅ Más flexible para cambios de dominio/subdirectorio
2. ✅ Consistente con estructura de paths relativas
3. ✅ Facilita migración entre entornos (local/producción)
4. ✅ Reduce errores de doble barra (`//`)

---

## ✅ Checklist Final

### Antes del despliegue:
- [x] Commit de cambios en Git
- [x] Documentación completa
- [x] Script SQL preparado
- [ ] Backup de base de datos producción

### Durante el despliegue:
- [ ] Subir archivos PHP actualizados
- [ ] Ejecutar script SQL
- [ ] Verificar logs de errores

### Después del despliegue:
- [ ] Probar subida de imágenes
- [ ] Probar visualización de imágenes
- [ ] Probar eliminación de imágenes
- [ ] Verificar consola del navegador
- [ ] Confirmar con usuario final

---

## 🔗 Archivos Relacionados

- `GUIA_SISTEMA_IMAGENES.md` - Documentación del sistema de imágenes
- `RESUMEN_SISTEMA_IMAGENES.md` - Arquitectura general
- `BUGFIX_ELIMINAR_IMAGENES.md` - Fix previo de eliminación
- `api.php` - Endpoints de upload/delete/getImages

---

**Estado:** ✅ Listo para despliegue
**Revisado por:** GitHub Copilot
**Aprobado por:** [Pendiente]
