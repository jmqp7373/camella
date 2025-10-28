# 🐛 Corrección de Errores - Formulario Publicar

## Problemas Identificados

### 1. ❌ **Código CSS Visible en la Página**
**Síntoma:** CSS aparecía como texto plano en la parte superior de la página

**Causa:** Había código CSS duplicado y mal cerrado después de la etiqueta `</style>`:
```css
</style>
    background: #c82333;
}

.image-counter {
    text-align: center;
    ...
}
...más CSS duplicado...
</style>
```

**Solución:** ✅ Eliminado todo el código CSS residual duplicado (líneas 373-430)

---

### 2. ❌ **Sección de Fotos No Aparecía**
**Síntoma:** La sección "Fotos" no se mostraba en modo crear nuevo anuncio

**Causa:** La condición `<?php if ($isEdit): ?>` ocultaba toda la sección en modo nuevo

**Solución:** ✅ La sección ahora siempre aparece, pero con comportamiento diferente según el modo:

---

## ✅ Soluciones Implementadas

### 1. **CSS Limpiado**
```php
// ANTES:
</style>
    background: #c82333;    ← Código CSS visible
}
... 60 líneas de CSS duplicado ...
</style>

// DESPUÉS:
</style>

<div class="publicar-container">  ← HTML limpio
```

**Resultado:**
- ✅ Ya no aparece código CSS en la página
- ✅ Estilos se aplican correctamente
- ✅ Página renderiza limpiamente

---

### 2. **Sección de Fotos Siempre Visible**

#### **Modo NUEVO** (crear anuncio)
```html
┌────────────────────────────────┐
│ Fotos:                         │
│ Los anuncios con fotos...      │
│                                │
│ ┌──────────────────────────┐  │
│ │       📷                 │  │
│ │ Puedes añadir hasta 5... │  │
│ │ Primero debes publicar...│  │  ← Mensaje informativo
│ └──────────────────────────┘  │
└────────────────────────────────┘
```
**Características:**
- ✅ Área de upload **deshabilitada** (borde sólido, opacidad reducida)
- ✅ Cursor: `not-allowed`
- ✅ Mensaje: "Primero debes publicar el anuncio para poder subir fotos"
- ❌ No funcional (no se puede hacer clic)

#### **Modo EDITAR** (anuncio existente)
```html
┌────────────────────────────────┐
│ Fotos:                         │
│ Los anuncios con fotos...      │
│                                │
│ ┌──────────────────────────┐  │
│ │       📷                 │  │  ← Clickeable
│ │ Puedes añadir hasta 5... │  │
│ │ JPG, PNG, GIF, WEBP...   │  │
│ └──────────────────────────┘  │
│                                │
│ [Grid de miniaturas]           │  ← Fotos existentes
│                                │
│ ℹ️ 0 de 5 fotos subidas        │  ← Contador
└────────────────────────────────┘
```
**Características:**
- ✅ Área de upload **activa** (borde punteado, hover azul)
- ✅ Drag & drop funcional
- ✅ Grid de miniaturas visible
- ✅ Contador de fotos
- ✅ Botones para eliminar fotos

#### **Modo VER** (solo lectura)
```html
┌────────────────────────────────┐
│ Fotos:                         │
│ Los anuncios con fotos...      │
│                                │
│ [Grid de miniaturas]           │  ← Solo vista
│                                │
│ ℹ️ 3 de 5 fotos subidas        │
└────────────────────────────────┘
```
**Características:**
- ❌ Sin área de upload
- ✅ Grid de miniaturas visible
- ❌ Sin botones de eliminar
- ✅ Contador de fotos (informativo)

---

## 🔍 Código Actualizado

### Lógica de Visualización
```php
<!-- Sección: Fotos -->
<div class="form-section">
    <div class="upload-section">
        <span class="section-title">Fotos:</span>
        <span class="section-subtitle">Los anuncios con fotos obtienen más vistas y contactos.</span>
        
        <?php if ($modo === 'nuevo'): ?>
            <!-- Área deshabilitada con mensaje -->
            <div class="upload-area" style="border-style: solid; opacity: 0.6; cursor: not-allowed;">
                <i class="fas fa-camera"></i>
                <p>Puedes añadir hasta 5 fotos</p>
                <small>Primero debes publicar el anuncio para poder subir fotos</small>
            </div>
            
        <?php elseif (!$soloLectura): ?>
            <!-- Upload activo (modo editar) -->
            <input type="file" id="imageInput" ... />
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-camera"></i>
                <p>Puedes añadir hasta 5 fotos</p>
                <small>JPG, PNG, GIF, WEBP - Máximo 5MB cada una</small>
            </div>
        <?php endif; ?>
        
        <?php if ($isEdit): ?>
            <!-- Grid y contador solo si hay ID -->
            <div class="images-preview" id="imagesPreview"></div>
            <div class="image-counter" id="imageCounter">
                <i class="fas fa-info-circle"></i> 
                <span id="currentCount">0</span> de 5 fotos subidas
            </div>
        <?php endif; ?>
    </div>
</div>
```

---

## 🎯 Flujo de Uso

### **Paso 1: Crear Anuncio**
```
1. Usuario va a /views/bloques/publicar.php
2. Ve la sección de fotos DESHABILITADA
3. Llena título, descripción, precio
4. Click "Publicar anuncio"
5. Se crea el anuncio y obtiene ID
6. Redirige al dashboard
```

### **Paso 2: Agregar Fotos**
```
1. Usuario hace clic en "Editar" desde su anuncio
2. URL: ?modo=editar&id=2
3. Ve la sección de fotos HABILITADA
4. Puede subir hasta 5 fotos
5. Fotos se asocian al anuncio_id=2
```

---

## 🧪 Testing

### ✅ Test 1: CSS Visible Eliminado
```
1. Ir a /views/bloques/publicar.php
2. Ver source code (Ctrl+U)
3. Verificar que NO hay CSS fuera de <style>
4. Verificar que la página se ve correctamente
```

### ✅ Test 2: Sección Fotos en Modo Nuevo
```
1. Ir a /views/bloques/publicar.php
2. Scroll hasta "Fotos:"
3. Verificar que la sección SÍ aparece
4. Verificar que está deshabilitada (opacidad baja)
5. Verificar mensaje: "Primero debes publicar..."
6. Intentar hacer clic → No debe pasar nada
```

### ✅ Test 3: Sección Fotos en Modo Editar
```
1. Ir a ?modo=editar&id=2
2. Scroll hasta "Fotos:"
3. Verificar que está HABILITADA
4. Hover sobre el área → Debe cambiar a azul
5. Click → Debe abrir selector de archivos
6. Drag & drop → Debe funcionar
```

### ✅ Test 4: Sección Fotos en Modo Ver
```
1. Ir a ?modo=ver&id=2
2. Scroll hasta "Fotos:"
3. Verificar que NO aparece área de upload
4. Solo se ven las fotos existentes
5. Sin botones de eliminar
```

---

## 📊 Comparación

| Aspecto | Antes | Después |
|---------|-------|---------|
| **CSS visible** | ❌ Sí (60 líneas) | ✅ No |
| **Fotos en modo nuevo** | ❌ No aparece | ✅ Aparece (deshabilitada) |
| **Fotos en modo editar** | ✅ Funcional | ✅ Funcional |
| **Fotos en modo ver** | ✅ Solo vista | ✅ Solo vista |
| **UX en nuevo** | ⚠️ Confuso | ✅ Informativo |

---

## 📁 Archivos Modificados

```
✏️ views/bloques/publicar.php
   - Líneas 373-430: CSS duplicado eliminado
   - Líneas 437-465: Lógica de fotos actualizada
```

---

## 💡 Ventajas de la Nueva Implementación

### Para el Usuario
1. ✅ **Claridad:** Ve desde el inicio que puede agregar fotos
2. ✅ **Guía:** Mensaje claro de cómo proceder
3. ✅ **Expectativa:** Sabe que después de publicar puede subir fotos
4. ✅ **Profesional:** No desaparece sección misteriosamente

### Para el Desarrollador
1. ✅ **Consistencia:** La estructura siempre es la misma
2. ✅ **Mantenible:** Un solo bloque con condiciones claras
3. ✅ **Escalable:** Fácil agregar más estados o funcionalidades
4. ✅ **Limpio:** Sin CSS duplicado ni errores de renderizado

---

## 🎨 Estilos Visuales

### Área Deshabilitada (Modo Nuevo)
```css
border-style: solid;      /* Borde sólido (no punteado) */
opacity: 0.6;             /* Semi-transparente */
cursor: not-allowed;      /* Cursor prohibido */
```

### Área Habilitada (Modo Editar)
```css
border-style: dashed;     /* Borde punteado */
opacity: 1.0;             /* Completamente opaco */
cursor: pointer;          /* Cursor de mano */

:hover {
    border-color: #1877f2; /* Azul al pasar mouse */
    background: #e7f3ff;   /* Fondo azul claro */
}
```

---

**Fecha:** 17 de octubre de 2025  
**Archivo modificado:** 1  
**Líneas eliminadas:** ~60 (CSS duplicado)  
**Líneas modificadas:** ~30 (lógica de fotos)  
**Estado:** ✅ Completado y testeado  
**Impacto:** Alto (corrige error crítico de visualización)
