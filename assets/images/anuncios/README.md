# Carpeta de Imágenes de Anuncios

## 📋 Descripción

Esta carpeta almacena todas las imágenes subidas por los usuarios para sus anuncios.

## 📁 Estructura

```
assets/images/anuncios/
├── .htaccess                    # Configuración de seguridad
├── README.md                    # Este archivo
├── ejemplos/                    # Imágenes de ejemplo
│   ├── carpintero.jpg
│   ├── electricista.jpg
│   └── plomero.jpg
└── [user_id]/                   # Carpetas por usuario
    └── [anuncio_id]/            # Carpetas por anuncio
        ├── imagen_1.jpg         # Imagen principal
        ├── imagen_2.jpg
        ├── imagen_3.jpg
        ├── imagen_4.jpg
        └── imagen_5.jpg         # Máximo 5 imágenes
```

## 🔒 Seguridad

- ✅ Solo se permiten archivos de imagen (jpg, jpeg, png, gif, webp)
- ✅ No se permite la ejecución de scripts PHP
- ✅ Listado de directorios deshabilitado
- ✅ Protección mediante .htaccess

## 📏 Límites

- **Máximo de imágenes por anuncio:** 5
- **Tamaño máximo por imagen:** 5MB (configurable)
- **Formatos permitidos:** JPG, JPEG, PNG, GIF, WEBP
- **Dimensiones recomendadas:** 1200x800 px
- **Dimensiones mínimas:** 800x600 px

## 📝 Nomenclatura de Archivos

Los archivos se nombran automáticamente siguiendo el patrón:
```
anuncio_[id]_[timestamp]_[numero].jpg
```

Ejemplo:
```
anuncio_123_1697500000_1.jpg
anuncio_123_1697500000_2.jpg
```

## 🗑️ Eliminación de Imágenes

Cuando se elimina un anuncio, todas sus imágenes asociadas también se eliminan automáticamente.

## 💾 Backup

Se recomienda hacer respaldo periódico de esta carpeta.

## ⚙️ Configuración

Ver archivo: `controllers/ImageUploadController.php` para configuración de subida.
