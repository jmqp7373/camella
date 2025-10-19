# Unificación de Vistas de Anuncios

## 📋 Resumen
Se ha unificado la creación, edición y visualización de anuncios en una sola vista (`views/bloques/publicar.php`), eliminando redundancias y simplificando el mantenimiento del código.

---

## ✅ Cambios Realizados

### 1. **views/bloques/publicar.php** - Vista Unificada
**Cambios principales:**
- ✅ Soporte para tres modos: `nuevo`, `editar`, `ver`
- ✅ Compatibilidad con parámetro antiguo `anuncio_id` (redirige automáticamente a modo editar)
- ✅ Variable `$soloLectura` para deshabilitar inputs en modo ver
- ✅ Títulos dinámicos según el modo:
  - **Nuevo:** "Publicar Anuncio" con icono `fa-plus-circle`
  - **Editar:** "Editar Anuncio" con icono `fa-edit`
  - **Ver:** "Ver Anuncio" con icono `fa-eye`
- ✅ Inputs marcados como `readonly` en modo ver
- ✅ Botón submit oculto en modo ver (solo muestra "Volver")
- ✅ Página 404 amigable cuando el anuncio no existe
- ✅ JavaScript actualizado para respetar modo de solo lectura:
  - Deshabilita drag & drop en modo ver
  - Oculta botones de eliminar imagen en modo ver
  - Deshabilita área de upload en modo ver

### 2. **views/bloques/bloque_anuncios.php** - Tarjetas de Anuncios
**Cambios en los botones:**
```php
// ANTES:
href="views/bloques/publicar.php?anuncio_id=<?= $anuncio['id'] ?>"  // Editar
href="views/ver_anuncio.php?id=<?= $anuncio['id'] ?>"                // Ver

// DESPUÉS:
href="views/bloques/publicar.php?modo=editar&id=<?= (int)$anuncio['id'] ?>"  // Editar
href="views/bloques/publicar.php?modo=ver&id=<?= (int)$anuncio['id'] ?>"     // Ver
```
- ✅ Casting explícito a `int` para mayor seguridad
- ✅ Uso de nuevos parámetros estandarizados

### 3. **views/ver_anuncio.php** - Redirect
**Antes:** 194 líneas con HTML completo y lógica duplicada  
**Después:** 8 líneas con redirect simple

```php
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header("Location: /views/bloques/publicar.php?modo=ver&id={$id}");
exit;
```
- ✅ Mantiene compatibilidad con enlaces antiguos
- ✅ Elimina el error HTTP 500
- ✅ Reduce código duplicado

---

## 🔗 URLs Disponibles

### ✨ Crear Nuevo Anuncio
```
/views/bloques/publicar.php
/views/bloques/publicar.php?modo=nuevo
```

### ✏️ Editar Anuncio
```
/views/bloques/publicar.php?modo=editar&id=2
/views/bloques/publicar.php?anuncio_id=2    ← Compatible (redirige automáticamente)
```

### 👁️ Ver Anuncio (Solo Lectura)
```
/views/bloques/publicar.php?modo=ver&id=2
/views/ver_anuncio.php?id=2                 ← Compatible (redirige automáticamente)
```

---

## 🔒 Validaciones Implementadas

1. **Modo válido:** Si `$modo` no es `nuevo|editar|ver`, se fuerza a `nuevo`
2. **ID seguro:** Casting explícito a `int` en todas las consultas
3. **Compatibilidad:** Si llega `anuncio_id` sin `modo`, asume `modo=editar`
4. **Anuncio no encontrado:** Página 404 amigable con botón "Volver al Dashboard"
5. **Permisos:** Solo el dueño o admin pueden editar (en modo ver todos pueden acceder)

---

## 🎨 Experiencia de Usuario

### Modo Nuevo
- Formulario vacío listo para completar
- Botón: "Publicar anuncio"
- Sin sección de imágenes (se agrega después de crear)

### Modo Editar
- Formulario pre-llenado con datos existentes
- Botón: "Guardar cambios"
- Upload de imágenes habilitado
- Botones para eliminar imágenes existentes

### Modo Ver
- Todos los campos en `readonly`
- Sin botón submit
- Solo botón "Volver"
- Imágenes sin botones de eliminar
- Upload de imágenes deshabilitado

---

## 📦 Archivos Modificados

```
✏️ views/bloques/publicar.php       (unificación de modos)
✏️ views/bloques/bloque_anuncios.php (actualización de botones)
✏️ views/ver_anuncio.php             (convertido a redirect)
```

**Archivos creados:** Ninguno  
**Archivos eliminados:** Ninguno

---

## 🧪 Pruebas Recomendadas

1. **Crear anuncio nuevo:**
   - Ir a `/views/bloques/publicar.php`
   - Verificar que el título sea "Publicar Anuncio"
   - Completar formulario y guardar

2. **Editar anuncio existente:**
   - Desde dashboard, hacer clic en "Editar" de una tarjeta
   - Verificar que URL sea `?modo=editar&id=X`
   - Verificar que campos estén pre-llenados
   - Verificar que se pueda subir/eliminar imágenes

3. **Ver anuncio (solo lectura):**
   - Desde dashboard, hacer clic en "Ver" de una tarjeta
   - Verificar que URL sea `?modo=ver&id=X`
   - Verificar que todos los campos estén en `readonly`
   - Verificar que no aparezca botón "Guardar"
   - Verificar que no aparezcan botones de eliminar imagen

4. **Compatibilidad con URLs antiguas:**
   - Probar `/views/ver_anuncio.php?id=2` (debe redirigir)
   - Probar `?anuncio_id=2` (debe asumir modo editar)

5. **Manejo de errores:**
   - Probar con ID inexistente (debe mostrar página 404)
   - Probar con modo inválido (debe forzar a "nuevo")

---

## ✨ Beneficios

1. ✅ **Código unificado:** Una sola vista en lugar de múltiples archivos duplicados
2. ✅ **Mantenimiento simplificado:** Cambios en un solo lugar
3. ✅ **Sin error 500:** El redirect elimina el problema de `ver_anuncio.php`
4. ✅ **Compatibilidad:** URLs antiguas siguen funcionando
5. ✅ **Mejor UX:** Modo de solo lectura claramente diferenciado
6. ✅ **Seguridad mejorada:** Validaciones y casting explícito de IDs
7. ✅ **Consistencia:** Mismos estilos en todos los modos

---

## 🚀 Despliegue

**Estado:** ✅ Listo para verificación local

**Pasos siguientes:**
1. Verificar localmente las tres rutas
2. Confirmar que desaparece el error 500
3. Verificar que los estilos y maquetación se mantienen intactos
4. Una vez validado, proceder con despliegue a producción

---

**Fecha:** 17 de octubre de 2025  
**Archivos modificados:** 3  
**Líneas de código eliminadas:** ~186 (por reducción de duplicación)  
**Compatibilidad:** ✅ Retrocompatible con URLs antiguas
