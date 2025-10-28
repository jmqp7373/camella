# 🐛 Corrección: Guardar cambios en anuncios

## Problema Identificado
El botón "Guardar cambios" en el modo editar (`?modo=editar&id=X`) no guardaba los cambios en la base de datos. El formulario solo mostraba un mensaje de éxito pero no enviaba los datos al servidor.

**URL afectada:** `views/bloques/publicar.php?modo=editar&id=2`

---

## ✅ Solución Implementada

### 1. **Nuevo Endpoint en `api.php`**
Se agregó el endpoint `saveAnuncio` que maneja tanto la creación como la actualización de anuncios.

**Características:**
- ✅ Modo CREAR: Inserta nuevo anuncio con `user_id`, `titulo`, `descripcion`, `precio`
- ✅ Modo EDITAR: Actualiza anuncio existente verificando permisos
- ✅ Validaciones de seguridad: verifica sesión activa y permisos del usuario
- ✅ Validaciones de datos: título y descripción obligatorios
- ✅ Manejo de errores con códigos HTTP apropiados
- ✅ Respuestas JSON consistentes

**Código agregado en `api.php`:**
```php
case 'saveAnuncio':
    - Valida sesión activa
    - Obtiene datos del formulario (titulo, descripcion, precio)
    - Si hay anuncio_id → MODO EDITAR (UPDATE)
    - Si no hay anuncio_id → MODO CREAR (INSERT)
    - Verifica permisos en modo editar
    - Retorna JSON con success, message, anuncio_id
```

### 2. **JavaScript actualizado en `publicar.php`**
Se reemplazó el código placeholder por una implementación completa con AJAX.

**Antes:**
```javascript
// Aquí iría la lógica para guardar el anuncio
showAlert('Anuncio guardado exitosamente', 'success');
```

**Después:**
```javascript
// Obtiene datos del formulario
// Crea FormData con titulo, descripcion, precio
// Envía POST a api.php?action=saveAnuncio
// Maneja respuesta y muestra alertas
// Deshabilita botón durante el guardado
// Redirige al dashboard en caso de éxito
```

**Mejoras implementadas:**
- ✅ Validación de campos requeridos en frontend
- ✅ Envío de datos via FormData y fetch API
- ✅ Indicador visual de carga (spinner en el botón)
- ✅ Manejo de errores de conexión
- ✅ Feedback visual con alertas de éxito/error
- ✅ Prevención de doble submit

---

## 🔄 Flujo Completo

### Crear Nuevo Anuncio
```
1. Usuario accede a /views/bloques/publicar.php
2. Completa formulario (titulo, descripcion, precio opcional)
3. Click en "Publicar anuncio"
4. JavaScript valida y envía datos a api.php?action=saveAnuncio
5. API inserta en tabla 'anuncios' con status='activo'
6. Retorna anuncio_id del nuevo registro
7. Muestra alerta de éxito y redirige al dashboard
```

### Editar Anuncio Existente
```
1. Usuario accede a ?modo=editar&id=2
2. Formulario se pre-llena con datos existentes
3. Usuario modifica campos (titulo, descripcion, precio)
4. Click en "Guardar cambios"
5. JavaScript valida y envía datos + anuncio_id
6. API verifica permisos del usuario
7. API ejecuta UPDATE en tabla 'anuncios'
8. Actualiza campo 'updated_at' con NOW()
9. Muestra alerta de éxito y redirige al dashboard
```

---

## 🛡️ Validaciones Implementadas

### Backend (api.php)
1. **Sesión activa:** Verifica `$_SESSION['user_id']`
2. **Título obligatorio:** No puede estar vacío
3. **Descripción obligatoria:** No puede estar vacía
4. **Permisos en edición:** Solo el dueño o admin pueden editar
5. **Anuncio existe:** Verifica que el ID sea válido
6. **Casting de tipos:** `(int)` para IDs, `(float)` para precio

### Frontend (publicar.php)
1. **Campos requeridos:** Valida titulo y descripcion antes de enviar
2. **Trim de espacios:** Elimina espacios al inicio/final
3. **Prevención de doble submit:** Deshabilita botón durante el guardado
4. **Feedback visual:** Spinner y mensajes de estado

---

## 🔍 Testing Recomendado

### Test 1: Crear nuevo anuncio
```
1. Ir a /views/bloques/publicar.php
2. Llenar titulo: "Servicio de plomería"
3. Llenar descripcion: "Plomero con 10 años de experiencia"
4. Llenar precio: 50000 (opcional)
5. Click "Publicar anuncio"
6. Verificar mensaje de éxito
7. Verificar que aparece en dashboard
8. Verificar en BD: SELECT * FROM anuncios ORDER BY id DESC LIMIT 1
```

### Test 2: Editar anuncio existente
```
1. Desde dashboard, click "Editar" en una tarjeta
2. Verificar URL: ?modo=editar&id=X
3. Verificar que campos están pre-llenados
4. Modificar titulo: "Servicio de plomería 24/7"
5. Modificar descripcion: agregar texto
6. Modificar precio: 60000
7. Click "Guardar cambios"
8. Verificar mensaje "Anuncio actualizado exitosamente"
9. Verificar en BD: SELECT * FROM anuncios WHERE id=X
10. Verificar que updated_at se actualizó
```

### Test 3: Validaciones
```
1. Intentar guardar con titulo vacío → Error "El título es obligatorio"
2. Intentar guardar con descripcion vacía → Error "La descripción es obligatoria"
3. Intentar editar anuncio de otro usuario → Error 403 Forbidden
4. Intentar editar anuncio inexistente → Error 404 Not Found
```

### Test 4: Conexión
```
1. Desactivar conexión de red
2. Intentar guardar anuncio
3. Verificar mensaje "Error de conexión al guardar el anuncio"
4. Verificar que botón se rehabilita
```

---

## 📊 Estructura de Base de Datos

### Tabla: anuncios
```sql
CREATE TABLE anuncios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NULL,
    imagen_principal VARCHAR(255) NULL,
    status VARCHAR(20) DEFAULT 'activo',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

**Campos actualizables:**
- `titulo` - Título del anuncio
- `descripcion` - Descripción detallada
- `precio` - Precio en COP (opcional)
- `updated_at` - Timestamp de última modificación

---

## 🔧 Archivos Modificados

### api.php
- **Líneas agregadas:** ~130
- **Nuevo endpoint:** `saveAnuncio`
- **Ubicación:** Entre `getImages` y `deleteAnuncio`

### views/bloques/publicar.php
- **Sección modificada:** JavaScript - Submit formulario
- **Cambio:** De código placeholder a implementación completa
- **Funcionalidad:** Envío AJAX con FormData

---

## ✨ Mejoras Adicionales

1. **UX mejorada:**
   - Spinner durante guardado
   - Botón deshabilitado para evitar dobles envíos
   - Mensajes claros de éxito/error

2. **Seguridad:**
   - Validación de permisos en backend
   - Casting de tipos para prevenir inyección SQL
   - Verificación de sesión activa

3. **Mantenibilidad:**
   - Código reutilizable para crear y editar
   - Respuestas JSON consistentes
   - Manejo centralizado de errores

---

## 📝 Notas Técnicas

- **Método HTTP:** POST
- **Formato de datos:** FormData (multipart/form-data)
- **Formato de respuesta:** JSON
- **Redirección:** Automática después de 1.5 segundos
- **Compatibilidad:** Funciona con modo=nuevo y modo=editar

---

**Fecha:** 17 de octubre de 2025  
**Archivos modificados:** 2  
**Estado:** ✅ Implementado y listo para testing  
**Impacto:** Funcionalidad crítica restaurada
