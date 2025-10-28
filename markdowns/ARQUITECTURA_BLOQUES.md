# 🧱 Arquitectura de Bloques Modulares

## 📋 Resumen

Hemos implementado un sistema de bloques reutilizables para los dashboards que respeta la jerarquía de roles y evita duplicación de código.

---

## 🎯 Jerarquía de Roles

```
┌─────────────────────────────────────────────┐
│  ADMIN (ve todo)                            │
│  ├─ Bloque Admin                            │
│  │  └─ Estadísticas Twilio                  │
│  │  └─ Gestión del Sistema                  │
│  ├─ Bloque Promotor                         │
│  │  └─ Herramientas de Promoción            │
│  └─ Bloque Publicante                       │
│     └─ Mis Anuncios                         │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  PROMOTOR (ve 2 bloques)                    │
│  ├─ Bloque Promotor                         │
│  │  └─ Herramientas de Promoción            │
│  └─ Bloque Publicante                       │
│     └─ Mis Anuncios                         │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│  PUBLICANTE (ve 1 bloque)                   │
│  └─ Bloque Publicante                       │
│     └─ Mis Anuncios                         │
└─────────────────────────────────────────────┘
```

---

## 📁 Estructura de Archivos

```
views/
├── bloques/                          (NUEVO)
│   ├── bloque_admin.php              ← Solo admin
│   ├── bloque_promotor.php           ← Admin + Promotor
│   ├── bloque_publicante.php         ← Todos
│   └── estilos_bloques.css           ← Estilos compartidos
│
├── admin/
│   ├── dashboard.php                 ← Original (mantener)
│   └── dashboard_modular.php         ← NUEVO modular
│
├── promotor/
│   ├── dashboard.php                 ← Original
│   └── dashboard_modular.php         ← NUEVO modular
│
└── publicante/
    ├── dashboard.php                 ← Original
    └── dashboard_modular.php         ← NUEVO modular
```

---

## 🔧 Cómo Funciona

### 1. Bloques con Validación de Roles

Cada bloque verifica el rol antes de mostrarse:

**bloque_admin.php:**
```php
<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    return; // Solo admin puede ver esto
}
?>
<!-- Contenido del bloque admin -->
```

**bloque_promotor.php:**
```php
<?php
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'promotor'])) {
    return; // Admin y promotor pueden ver esto
}
?>
<!-- Contenido del bloque promotor -->
```

**bloque_publicante.php:**
```php
<?php
// Visible para TODOS los roles
?>
<!-- Contenido del bloque publicante -->
```

### 2. Dashboards que Incluyen Bloques

**dashboard_modular.php (Admin):**
```php
<?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>
<?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
<?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
```

**dashboard_modular.php (Promotor):**
```php
<?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
<?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
```

**dashboard_modular.php (Publicante):**
```php
<?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
```

---

## 🎨 Estilos Compartidos

Todos los bloques usan el mismo archivo CSS:

```html
<link rel="stylesheet" href="../../views/bloques/estilos_bloques.css">
```

**Clases disponibles:**
- `.dashboard-container` - Contenedor principal
- `.dashboard-grid` - Grid responsive
- `.stat-card` - Tarjetas de estadísticas
- `.stat-card-promotor` - Variante azul
- `.stat-card-publicante` - Variante verde
- `.admin-only-section` - Sección exclusiva admin
- `.promotor-section` - Sección promotor
- `.publicante-section` - Sección publicante

---

## 📊 Contenido de Cada Bloque

### Bloque Admin
- ✅ Estadísticas de Twilio (24h, 7d, 30d)
- ✅ Gestión de Usuarios
- ✅ Roles y Permisos
- ✅ Estado de Base de Datos

### Bloque Promotor
- ✅ Anuncios Promovidos
- ✅ Vistas Generadas
- ✅ Contactos Recibidos
- ✅ Inversión Activa
- ✅ Características Premium
- ✅ Acciones Rápidas

### Bloque Publicante
- ✅ Anuncios Activos
- ✅ Vistas Totales
- ✅ Contactos Recibidos
- ✅ Calificación
- ✅ CTA para crear anuncio
- ✅ Lista de anuncios
- ✅ Tips para mejores resultados
- ✅ Acciones rápidas básicas

---

## 🚀 Migración de Dashboards Existentes

### Opción 1: Reemplazar (Recomendado)
```bash
# Renombrar archivos actuales como backup
mv dashboard.php dashboard_legacy.php

# Usar la versión modular como principal
mv dashboard_modular.php dashboard.php
```

### Opción 2: Convivencia
```bash
# Mantener ambos dashboards disponibles
# dashboard.php - Original con todo el código
# dashboard_modular.php - Modular con bloques
```

### Opción 3: Migración Gradual
```php
// En dashboard.php original, agregar al final:
<?php include __DIR__ . '/../bloques/bloque_admin.php'; ?>
<?php include __DIR__ . '/../bloques/bloque_promotor.php'; ?>
<?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>
```

---

## ✅ Ventajas de Esta Arquitectura

1. **Sin Duplicación de Código**
   - Un solo archivo por bloque
   - Cambios se reflejan en todos los dashboards

2. **Mantenimiento Simple**
   - Actualizar bloque = actualizar todos los dashboards
   - No hay desincronización de contenido

3. **Escalabilidad**
   - Fácil agregar nuevos bloques
   - Fácil agregar nuevos roles

4. **Consistencia Visual**
   - Estilos compartidos
   - Diseño uniforme en todos los dashboards

5. **Seguridad**
   - Validación de roles en cada bloque
   - No depende solo del dashboard

---

## 🧪 Pruebas Locales

### 1. Probar como Admin
```php
// En la BD o session:
$_SESSION['role'] = 'admin';
```
**URL:** `http://localhost/camella.com.co/views/admin/dashboard_modular.php`

**Debe ver:**
- ✅ Bloque Admin (Twilio + Gestión)
- ✅ Bloque Promotor (Herramientas)
- ✅ Bloque Publicante (Anuncios)

### 2. Probar como Promotor
```php
$_SESSION['role'] = 'promotor';
```
**URL:** `http://localhost/camella.com.co/views/promotor/dashboard_modular.php`

**Debe ver:**
- ❌ Bloque Admin (oculto)
- ✅ Bloque Promotor (Herramientas)
- ✅ Bloque Publicante (Anuncios)

### 3. Probar como Publicante
```php
$_SESSION['role'] = 'publicante';
```
**URL:** `http://localhost/camella.com.co/views/publicante/dashboard_modular.php`

**Debe ver:**
- ❌ Bloque Admin (oculto)
- ❌ Bloque Promotor (oculto)
- ✅ Bloque Publicante (Anuncios)

---

## 📝 Próximos Pasos

1. **Probar los dashboards modulares localmente**
2. **Migrar gradualmente del dashboard original al modular**
3. **Agregar contenido dinámico desde la BD**
4. **Implementar funcionalidades de cada bloque**
5. **Deploy a producción cuando esté probado**

---

## 🔄 Actualización de loginPhone.php

Los dashboards modulares mantienen las mismas rutas, por lo que el redirect en `loginPhone.php` sigue funcionando:

```javascript
// Cambiar dashboard.php a dashboard_modular.php (opcional)
if (role === 'admin') {
    window.location.href = 'views/admin/dashboard_modular.php';
} else if (role === 'promotor') {
    window.location.href = 'views/promotor/dashboard_modular.php';
} else {
    window.location.href = 'views/publicante/dashboard_modular.php';
}
```

---

**Fecha:** Octubre 14, 2025
**Sistema:** Camella.com.co - Dashboards Modulares
