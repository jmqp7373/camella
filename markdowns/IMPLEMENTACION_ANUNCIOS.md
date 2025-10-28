# Implementación de Anuncios Dinámicos en Dashboard

## 📋 Resumen de Cambios

Se ha implementado la funcionalidad para mostrar los anuncios publicados por el usuario en el dashboard de publicante, conectando con la base de datos.

## 🔧 Archivos Modificados

### 1. `views/bloques/bloque_publicante.php`

**Cambios realizados:**

- ✅ **Consulta a la base de datos**: Se agregó lógica PHP al inicio del bloque para consultar los anuncios del usuario actual desde la tabla `servicios`.

- ✅ **Manejo de sesión**: Se utiliza `$_SESSION['user_id']` para identificar al usuario logueado.

- ✅ **Verificación de tabla**: Se verifica que la tabla `servicios` exista antes de intentar consultarla (evita errores si la tabla no está creada).

- ✅ **Vista dinámica de anuncios**: 
  - Si hay anuncios: Se muestran en un grid responsive con tarjetas
  - Si no hay anuncios: Se muestra el mensaje "Aún no tienes anuncios publicados"

**Estructura de la tarjeta de anuncio:**
```html
- Imagen principal (con fallback a imagen por defecto)
- Título del anuncio
- Descripción (truncada a 80 caracteres)
- Precio formateado (o "A convenir" si no tiene precio)
- Fecha de publicación
- Botones: Editar | Ver
```

## 🗄️ Estructura de Base de Datos Requerida

### Tabla: `servicios`

```sql
CREATE TABLE `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `status` enum('activo','inactivo','pausado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
```

**Campos utilizados:**
- `user_id`: ID del usuario propietario del anuncio
- `titulo`: Título del servicio/anuncio
- `descripcion`: Descripción detallada (opcional)
- `precio`: Precio del servicio (opcional)
- `imagen_principal`: Nombre del archivo de imagen (ruta: `/uploads/`)
- `status`: Estado del anuncio ('activo', 'inactivo', 'pausado')
- `created_at`: Fecha de creación

## 🎨 Características Implementadas

### ✅ Responsive Design
- Grid adaptable con `minmax(280px, 1fr)`
- Funciona en móviles, tablets y desktop

### ✅ Manejo de Imágenes
- Ruta de imágenes: `/uploads/nombre_archivo.jpg`
- Imagen por defecto si no existe: `/assets/images/default-service.jpg`
- Atributo `onerror` para fallback automático

### ✅ Seguridad
- Uso de `htmlspecialchars()` para prevenir XSS
- Preparación de consultas con PDO (previene SQL injection)
- Validación de sesión antes de consultar

### ✅ UX/UI
- Tarjetas con hover effect
- Formato de precio con separadores de miles
- Fechas en formato legible (dd MMM YYYY)
- Truncamiento de texto largo
- Botones de acción (Editar/Ver)

## 📝 Variables de Sesión Utilizadas

```php
$_SESSION['user_id']  // ID numérico del usuario (requerido)
$_SESSION['usuario']  // Alias del user_id (validación)
$_SESSION['phone']    // Teléfono del usuario
$_SESSION['role']     // Rol: 'publicante', 'promotor', 'admin'
```

## 🚀 Cómo Probar

### Opción 1: Con datos reales

1. Crear la tabla `servicios` ejecutando el SQL en phpMyAdmin:
   ```bash
   # Ubicación del script
   tests/create_servicios_table.sql
   ```

2. Acceder al dashboard como usuario publicante:
   ```
   http://localhost/camella.com.co/views/publicante/dashboard_modular.php
   ```

3. Los anuncios del usuario logueado se mostrarán automáticamente

### Opción 2: Sin tabla servicios

- Si la tabla no existe, simplemente se muestra el mensaje vacío
- No genera errores ni advertencias
- La funcionalidad está preparada para cuando se cree la tabla

## 🔄 Flujo de Datos

```
Usuario logueado
    ↓
$_SESSION['user_id'] disponible
    ↓
Consulta: SELECT * FROM servicios WHERE user_id = ? AND status = 'activo'
    ↓
┌─────────────┬──────────────┐
│ Hay datos   │ No hay datos │
├─────────────┼──────────────┤
│ Mostrar     │ Mostrar      │
│ grid de     │ estado       │
│ tarjetas    │ vacío        │
└─────────────┴──────────────┘
```

## 📦 Próximos Pasos (Recomendaciones)

1. **Crear endpoints funcionales**:
   - `/views/publicante/crear_anuncio.php` - Formulario de creación
   - `/views/publicante/editar_anuncio.php?id=X` - Edición
   - `/views/publicante/ver_anuncio.php?id=X` - Vista detallada

2. **Implementar estadísticas reales**:
   - Vistas totales (requiere tabla `visitas`)
   - Contactos recibidos (requiere tabla `contactos`)
   - Calificaciones (requiere tabla `ratings`)

3. **Sistema de carga de imágenes**:
   - Upload de múltiples fotos
   - Redimensionamiento automático
   - Validación de formatos

4. **Filtros y búsqueda**:
   - Por categoría
   - Por fecha
   - Por estado

## 🐛 Manejo de Errores

- ✅ Tabla no existe: No genera error, muestra estado vacío
- ✅ Usuario sin sesión: No consulta base de datos
- ✅ Imagen no encontrada: Muestra imagen por defecto
- ✅ Errores de BD: Se registran en error_log

## 📌 Notas Importantes

- Los estilos están en línea para mantener consistencia visual
- Se utiliza la variable `APP_SUBDIR` para rutas correctas en local/producción
- Límite de 6 anuncios en vista previa (botón "Ver todos" para listado completo)
- El código es retrocompatible: funciona con y sin la tabla servicios
