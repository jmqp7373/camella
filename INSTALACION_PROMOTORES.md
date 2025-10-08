# Módulo de Promotores - Camella.com.co

## 📋 Resumen del Sistema Implementado

El módulo de promotores es un sistema completo de referidos con QR codes, tracking de conversiones y gestión de comisiones.

### ✅ Componentes Completados

#### Base de Datos
- ✅ `db/promotores_schema.sql` - Schema completo con 4 tablas
- ✅ `db/install_promotores.php` - Instalador web con interfaz admin

#### Modelos (Backend)
- ✅ `models/Promotor.php` - Gestión de promotores y códigos únicos
- ✅ `models/Referidos.php` - Tracking de visitas con anti-fraude
- ✅ `models/Comisiones.php` - Sistema de comisiones y aprobaciones

#### Controladores
- ✅ `controllers/PromotorController.php` - API completa de referidos
- ✅ `controllers/AdminController.php` - Gestión administrativa (extendido)

#### Herramientas
- ✅ `tools/qr.php` - Generador de QR con cache y rate limiting
- ✅ `vendor-libs/phpqrcode.php` - Librería QR optimizada para GoDaddy

#### Vistas Usuario
- ✅ `views/promotor/panel.php` - Panel del promotor con estadísticas
- ✅ `views/promotor/comisiones.php` - Historial de comisiones

#### Vistas Admin
- ✅ `views/admin/promotores/lista.php` - Gestión de promotores
- ✅ `views/admin/promotores/comisiones.php` - Aprobación de comisiones

#### Integración
- ✅ `index.php` - Rutas completas configuradas
- ✅ `partials/header.php` - Enlace a panel de promotor
- ✅ `assets/css/header.css` - Estilos para botones de promotor
- ✅ `views/home.php` - Tracking automático de referidos

## 🚀 Instrucciones de Instalación

### 1. Verificar Archivos
Confirma que todos estos archivos estén en el servidor:

```
📁 db/
   ├── promotores_schema.sql
   └── install_promotores.php

📁 models/
   ├── Promotor.php
   ├── Referidos.php
   └── Comisiones.php

📁 controllers/
   ├── PromotorController.php (actualizado)
   └── AdminController.php (extendido)

📁 views/
   ├── promotor/
   │   ├── panel.php
   │   └── comisiones.php
   └── admin/promotores/
       ├── lista.php
       └── comisiones.php

📁 tools/
   └── qr.php

📁 vendor-libs/
   └── phpqrcode.php

📄 index.php (actualizado con rutas)
📄 partials/header.php (actualizado)
📄 assets/css/header.css (actualizado)
📄 views/home.php (actualizado)
```

### 2. Instalar Base de Datos

**Opción A: Instalador Web (Recomendado)**
1. Ve a: `https://tudominio.com/db/install_promotores.php`
2. Ingresa como administrador
3. Sigue las instrucciones del instalador

**Opción B: Manual**
```sql
-- Ejecutar en phpMyAdmin o consola MySQL
SOURCE db/promotores_schema.sql;
```

### 3. Configurar Permisos
Asegurar que la carpeta `tools/` tenga permisos de escritura para el cache:
```bash
chmod 755 tools/
chmod 644 tools/qr.php
```

### 4. Probar Funcionalidades

#### Como Usuario:
1. **Acceder al Panel**: Login → "Promotor" en header
2. **Generar Enlaces**: Copiar URL de referido del panel
3. **Compartir**: Usar botón WhatsApp o copiar mensaje
4. **Ver QR**: Descargar código QR desde el panel

#### Como Admin:
1. **Gestionar Promotores**: Admin → "Promotores"
2. **Aprobar Comisiones**: Admin → "Comisiones" → Aprobar/Rechazar
3. **Ver Estadísticas**: Dashboard con métricas del sistema

## 🔧 Características Técnicas

### Seguridad Implementada
- ✅ **Anti-fraude**: Prevención de auto-referidos
- ✅ **Fingerprinting**: Tracking único por dispositivo
- ✅ **Rate limiting**: Límites en QR y APIs
- ✅ **Validación CSRF**: Protección en formularios
- ✅ **Sanitización**: Limpieza de inputs

### Optimizaciones
- ✅ **Cache de QR**: Archivos QR reutilizables
- ✅ **Queries optimizadas**: Índices en tablas críticas
- ✅ **Responsive design**: Adaptado a móviles
- ✅ **Error handling**: Manejo robusto de errores

### APIs Disponibles

#### Tracking (POST)
```php
POST index.php
action=rastrear_visita&codigo=ABC123
```

#### Atribución de Registro (POST)
```php
POST index.php  
action=atribuir_registro&usuario_id=123
```

#### Admin - Cambiar Estado (POST)
```php
POST index.php
action=admin_cambiar_estado_promotor&promotor_id=1&estado=activo
```

## 📊 Flujo de Funcionamiento

### 1. Usuario Visita con Referido
```
Usuario hace clic: https://sitio.com?ref=ABC123
↓
home.php detecta parámetro 'ref'
↓
JavaScript rastrea visita asíncronamente
↓
Se guarda: IP, fingerprint, timestamp, código
```

### 2. Usuario se Registra
```
Usuario completa registro exitosamente
↓ 
Sistema llama atribuir_registro()
↓
Se verifica cookie de referido
↓
Se genera comisión automáticamente
```

### 3. Admin Gestiona Comisiones
```
Admin ve comisiones pendientes
↓
Aprueba/rechaza con notas
↓
Marca como pagada con referencia
↓
Promotor ve actualización en su panel
```

## 🎯 URLs del Sistema

### Usuario Promotor
- Panel: `index.php?action=promotor_panel`
- Comisiones: `index.php?action=promotor_comisiones`

### Admin
- Promotores: `index.php?action=admin_promotores`
- Comisiones: `index.php?action=admin_comisiones`
- Detalle: `index.php?action=admin_detalle_promotor&id=N`

### Herramientas
- QR Generator: `tools/qr.php?url=https://ejemplo.com`
- Instalador: `db/install_promotores.php`

## ⚠️ Consideraciones Importantes

### Para Producción
1. **Configurar comisiones** en base de datos (tabla `promotor_config`)
2. **Ajustar rate limits** según tráfico esperado
3. **Monitorear logs** en `tools/qr.php` y controladores
4. **Backup regular** de tablas de promotores

### QR Code Library
- Implementación actual es **funcional pero básica**
- Para **alta demanda**, considerar librería completa como:
  - `chillerlan/php-qr-code`
  - `endroid/qr-code`

### Hosting GoDaddy
- ✅ **Sin Composer**: Todo funciona sin dependencias externas
- ✅ **Optimizado**: Cache de archivos, queries eficientes
- ✅ **Compatible**: PHP 7.4+ y MySQL 5.7+

## 🆘 Troubleshooting

### Error: "Tabla no existe"
```bash
# Verificar instalación de BD
mysql -u usuario -p -e "SHOW TABLES LIKE 'promotores%';"
```

### Error: "QR no se genera"
```bash
# Verificar permisos de escritura
ls -la tools/
chmod 755 tools/
```

### Error: "No se rastrea visita"
```bash
# Verificar JavaScript en navegador
# Revisar logs en controllers/PromotorController.php
```

## ✨ Próximas Mejoras Opcionales

- [ ] Dashboard con gráficos de conversión
- [ ] Notificaciones por email de nuevas comisiones
- [ ] Integración con sistemas de pago automático  
- [ ] App móvil para promotores
- [ ] Analytics avanzados de referidos

---

**🎉 ¡Sistema Listo para Producción!**

El módulo de promotores está completamente funcional y optimizado para hosting GoDaddy. Todas las características solicitadas han sido implementadas sin modificar el diseño existente del sitio.