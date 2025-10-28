# 📸 Sistema de Subida de Imágenes para Anuncios

## 📋 Descripción General

Sistema completo para permitir a los usuarios subir hasta **5 imágenes** por anuncio con validación de tipo, tamaño y seguridad integrada.

---

## 🗂️ Estructura de Archivos

### 1. Base de Datos

#### Tabla: `anuncio_imagenes`
```sql
CREATE TABLE anuncio_imagenes (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  anuncio_id INT(11) NOT NULL,
  ruta VARCHAR(255) NOT NULL,
  orden TINYINT UNSIGNED DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (anuncio_id) REFERENCES anuncios(id) ON DELETE CASCADE
);
```

**Características:**
- Relación 1:N con `anuncios`
- Eliminación en cascada (si se elimina anuncio, se eliminan sus imágenes)
- Campo `orden` para ordenar las imágenes

---

### 2. Controlador: `ImageUploadController.php`

**Ubicación:** `controllers/ImageUploadController.php`

**Métodos públicos:**

#### `upload()`
- **Descripción:** Sube una o más imágenes para un anuncio
- **Parámetros POST:**
  - `anuncio_id` (int): ID del anuncio
  - `images[]` (files): Array de archivos
  
- **Validaciones:**
  - Usuario autenticado
  - Anuncio pertenece al usuario
  - Máximo 5 imágenes por anuncio
  - Tipo de archivo: JPG, JPEG, PNG, GIF, WEBP
  - Tamaño máximo: 5MB por imagen
  
- **Respuesta JSON:**
```json
{
  "success": true,
  "uploaded": [
    {"id": 1, "ruta": "/assets/images/anuncios/anuncio_1_123456_abc.jpg", "orden": 1}
  ],
  "errors": [],
  "message": "1 imagen(es) subida(s) exitosamente"
}
```

#### `delete()`
- **Descripción:** Elimina una imagen específica
- **Parámetros POST:**
  - `imagen_id` (int): ID de la imagen a eliminar
  
- **Acciones:**
  - Verifica pertenencia al usuario
  - Elimina archivo físico del servidor
  - Elimina registro de BD
  - Reordena las imágenes restantes

- **Respuesta JSON:**
```json
{
  "success": true,
  "message": "Imagen eliminada exitosamente"
}
```

#### `getImages()`
- **Descripción:** Obtiene todas las imágenes de un anuncio
- **Parámetros GET:**
  - `anuncio_id` (int): ID del anuncio
  
- **Respuesta JSON:**
```json
{
  "success": true,
  "images": [
    {"id": 1, "ruta": "/path/to/image.jpg", "orden": 1, "created_at": "2025-10-16 12:00:00"}
  ],
  "total": 1,
  "remaining": 4
}
```

---

### 3. Endpoint API: `api.php`

**Ubicación:** `api.php` (raíz del proyecto)

**Rutas disponibles:**

| Acción | Método | URL | Descripción |
|--------|--------|-----|-------------|
| `uploadImage` | POST | `api.php?action=uploadImage` | Subir imágenes |
| `deleteImage` | POST | `api.php?action=deleteImage` | Eliminar imagen |
| `getImages` | GET | `api.php?action=getImages` | Listar imágenes |

**Ejemplo de uso con JavaScript:**
```javascript
// Subir imágenes
const formData = new FormData();
formData.append('anuncio_id', 123);
formData.append('images[]', fileInput.files[0]);

const response = await fetch('/api.php?action=uploadImage', {
    method: 'POST',
    body: formData
});

const data = await response.json();
```

---

### 4. Vista de Usuario: `publicar.php`

**Ubicación:** `views/publicante/publicar.php`

**Características:**

✅ Formulario completo para crear/editar anuncios  
✅ Upload con **drag & drop**  
✅ Preview de imágenes en tiempo real  
✅ Contador de imágenes (X de 5)  
✅ Botón para eliminar imágenes  
✅ Validación del lado del cliente  
✅ Mensajes de error/éxito con alertas  

**Funcionalidades JavaScript:**

| Función | Descripción |
|---------|-------------|
| `loadExistingImages()` | Carga imágenes ya subidas al editar |
| `handleFiles(files)` | Procesa archivos y los sube al servidor |
| `deleteImage(imagenId)` | Elimina una imagen específica |
| `renderImages()` | Renderiza la galería de imágenes |
| `showAlert(message, type)` | Muestra alertas al usuario |

---

### 5. Seguridad: `.htaccess`

**Ubicación:** `assets/images/anuncios/.htaccess`

```apache
# Permitir acceso a imágenes
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Denegar acceso a scripts PHP
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Prevenir listado de directorios
Options -Indexes
```

**Protección contra:**
- ❌ Ejecución de scripts PHP subidos
- ❌ Listado de directorios
- ❌ Acceso a archivos no autorizados

---

## 🔒 Validaciones Implementadas

### Backend (PHP)

| Validación | Descripción |
|------------|-------------|
| **Autenticación** | Verifica sesión activa del usuario |
| **Pertenencia** | Valida que el anuncio pertenezca al usuario |
| **Límite de imágenes** | Máximo 5 imágenes por anuncio |
| **Tipo MIME** | Solo: image/jpeg, image/png, image/gif, image/webp |
| **Extensión** | Solo: jpg, jpeg, png, gif, webp |
| **Tamaño** | Máximo 5MB por imagen |
| **Nombre seguro** | Genera nombres únicos con timestamp y hash |
| **Path traversal** | Usa `basename()` para prevenir ataques |

### Frontend (JavaScript)

| Validación | Descripción |
|------------|-------------|
| **Contador** | Muestra cuántas imágenes quedan disponibles |
| **Preview** | Muestra las imágenes antes de subir |
| **Drag & Drop** | Interface moderna para subir archivos |
| **Feedback** | Alertas de éxito/error en tiempo real |

---

## 📁 Estructura de Carpetas

```
assets/images/anuncios/
├── .htaccess                          # Seguridad
├── README.md                          # Documentación
├── ejemplos/                          # Imágenes de ejemplo
│   ├── plomero.jpg
│   ├── electricista.jpg
│   └── carpintero.jpg
└── anuncio_1_1697500000_abc123.jpg   # Imágenes de usuarios
```

**Nomenclatura de archivos:**
```
anuncio_[id]_[timestamp]_[hash].jpg

Ejemplo:
anuncio_123_1697500000_a1b2c3d4e5f6g7h8.jpg
       │    │            │
       │    │            └─ Hash aleatorio (16 caracteres)
       │    └─────────────── Timestamp Unix
       └──────────────────── ID del anuncio
```

---

## 🚀 Flujo de Uso

### 1. Usuario crea un anuncio
1. Accede a `views/publicante/publicar.php`
2. Llena título, descripción y precio
3. Sube imágenes mediante drag & drop o clic
4. Las imágenes se suben automáticamente vía AJAX
5. Ve preview de las imágenes subidas
6. Puede eliminar imágenes antes de finalizar
7. Guarda el anuncio

### 2. Sistema procesa las imágenes
1. `ImageUploadController` valida cada archivo
2. Genera nombre único y seguro
3. Mueve el archivo a `assets/images/anuncios/`
4. Inserta registro en tabla `anuncio_imagenes`
5. Retorna JSON con resultado

### 3. Usuario ve sus imágenes
1. Al editar el anuncio, se cargan las imágenes existentes
2. `getImages()` obtiene todas las imágenes de la BD
3. Se renderizan en la galería con opción de eliminar

---

## 🧪 Testing

### Crear tabla en la base de datos

```bash
php tests/ejecutar_anuncio_imagenes.php
```

**Output esperado:**
```
========================================
CREAR TABLA: anuncio_imagenes
========================================

✅ Conexión exitosa a la base de datos
✅ Tabla 'anuncio_imagenes' creada exitosamente
✅ Imagen insertada: /assets/images/anuncios/ejemplos/plomero.jpg
✅ Imagen insertada: /assets/images/anuncios/ejemplos/electricista.jpg
✅ Imagen insertada: /assets/images/anuncios/ejemplos/carpintero.jpg

📊 Total imágenes insertadas: 3
📊 Total de imágenes registradas: 3

✅ Proceso completado exitosamente!
```

### Validar sintaxis PHP

```bash
php -l controllers/ImageUploadController.php
php -l api.php
php -l views/publicante/publicar.php
```

---

## 📊 Base de Datos - Queries Útiles

### Ver todas las imágenes de un anuncio
```sql
SELECT * FROM anuncio_imagenes WHERE anuncio_id = 1 ORDER BY orden;
```

### Contar imágenes por anuncio
```sql
SELECT anuncio_id, COUNT(*) as total_imagenes
FROM anuncio_imagenes
GROUP BY anuncio_id;
```

### Ver anuncios con sus imágenes
```sql
SELECT 
    a.id,
    a.titulo,
    COUNT(ai.id) as total_imagenes
FROM anuncios a
LEFT JOIN anuncio_imagenes ai ON a.id = ai.anuncio_id
GROUP BY a.id;
```

### Eliminar imágenes huérfanas (sin anuncio)
```sql
DELETE FROM anuncio_imagenes 
WHERE anuncio_id NOT IN (SELECT id FROM anuncios);
```

---

## 🔧 Configuración

### Cambiar límite de imágenes

**Archivo:** `ImageUploadController.php`

```php
private $maxImages = 5; // Cambiar a 10, 3, etc.
```

**Archivo:** `publicar.php` (JavaScript)

```javascript
const maxImages = 5; // Cambiar al mismo valor
```

### Cambiar tamaño máximo de archivo

**Archivo:** `ImageUploadController.php`

```php
private $maxFileSize = 5242880; // 5MB en bytes
// Para 10MB: 10485760
// Para 2MB:  2097152
```

### Agregar nuevos formatos de imagen

**Archivo:** `ImageUploadController.php`

```php
private $allowedTypes = [
    'image/jpeg',
    'image/jpg', 
    'image/png',
    'image/gif',
    'image/webp',
    'image/svg+xml'  // ← Agregar nuevo formato
];

private $allowedExtensions = [
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' // ← Agregar extensión
];
```

**Archivo:** `.htaccess`

```apache
<FilesMatch "\.(jpg|jpeg|png|gif|webp|svg)$">  <!-- Agregar svg -->
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

---

## 🐛 Troubleshooting

### Error: "La carpeta de anuncios no tiene permisos de escritura"

**Solución:**
```bash
chmod 755 assets/images/anuncios
```

### Error: "Anuncio no encontrado o no autorizado"

**Causa:** El anuncio no pertenece al usuario en sesión  
**Solución:** Verificar que `$_SESSION['user_id']` esté correctamente configurado

### Error: "Solo puedes subir X imagen(es) más"

**Causa:** El anuncio ya tiene imágenes subidas  
**Solución:** Eliminar imágenes existentes antes de subir nuevas

### Las imágenes no se ven

**Causas posibles:**
1. La ruta en BD no es correcta (debe empezar con `/assets/images/anuncios/`)
2. Los archivos no existen físicamente en el servidor
3. Permisos de lectura incorrectos

**Verificar:**
```sql
SELECT ruta FROM anuncio_imagenes WHERE anuncio_id = 1;
```

```bash
ls -la assets/images/anuncios/
```

---

## 🚀 Deployment a Producción

### 1. Verificar archivos localmente
```bash
git status
git add .
git commit -m "feat: Sistema de imágenes"
git push origin main
```

### 2. Crear tabla en producción

**Opción A:** Desde cPanel → phpMyAdmin  
Ejecutar contenido de: `tests/create_anuncio_imagenes_table.sql`

**Opción B:** Subir script y ejecutar vía SSH
```bash
php tests/ejecutar_anuncio_imagenes.php
```

### 3. Verificar permisos de carpeta
```bash
chmod 755 assets/images/anuncios
```

### 4. Subir archivos vía FileZilla

Archivos a subir:
- ✅ `api.php`
- ✅ `controllers/ImageUploadController.php`
- ✅ `views/publicante/publicar.php`
- ✅ `assets/images/anuncios/.htaccess`
- ✅ `assets/images/anuncios/README.md`

---

## 📝 Checklist de Implementación

- [x] Crear tabla `anuncio_imagenes` en BD
- [x] Crear `ImageUploadController.php`
- [x] Crear endpoint `api.php`
- [x] Crear vista `publicar.php`
- [x] Implementar seguridad con `.htaccess`
- [x] Validaciones de tipo, tamaño y cantidad
- [x] Drag & Drop interface
- [x] Preview de imágenes
- [x] Función para eliminar imágenes
- [x] Testing local exitoso
- [x] Commit y push a GitHub
- [ ] Deploy a producción
- [ ] Testing en producción

---

## 📚 Referencias

- **PDO Documentation:** https://www.php.net/manual/es/book.pdo.php
- **File Uploads PHP:** https://www.php.net/manual/es/features.file-upload.php
- **Fetch API:** https://developer.mozilla.org/es/docs/Web/API/Fetch_API

---

## 👤 Autor

**Camella.com.co Development Team**  
Fecha: Octubre 16, 2025  
Commit: `2a9741d`
