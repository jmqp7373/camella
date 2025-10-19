# 🚨 FIX URGENTE: Error "Error interno del servidor" en Producción

## 📋 Problema Identificado

Al solicitar código de acceso en producción (`https://camella.com.co/index.php?view=loginPhone`), se recibe el error:
```
Error al enviar el código: error interno del server
```

## 🔍 Causa Raíz

La tabla `magic_links` **NO EXISTE en el servidor de producción**. El sistema intenta guardar el magic token en esta tabla pero falla, causando que todo el proceso de envío de código falle.

## ✅ Solución (2 Opciones)

### Opción 1: Usar Script PHP Automático (RECOMENDADO)

1. **Subir archivo al servidor**:
   - Archivo: `setup_magic_links_production.php`
   - Ubicación: Raíz del proyecto en Hostinger
   - Método: FileZilla o File Manager de Hostinger

2. **Ejecutar el script**:
   ```
   https://camella.com.co/setup_magic_links_production.php
   ```

3. **Verificar**:
   - El script mostrará si la tabla se creó correctamente
   - Mostrará la estructura de la tabla
   - Confirmará que todo está listo

4. **🔒 ELIMINAR el archivo** por seguridad después de ejecutarlo

### Opción 2: Ejecutar SQL Manual (phpMyAdmin)

1. **Acceder a phpMyAdmin** en Hostinger
2. **Seleccionar base de datos**: `u179023609_camella_db`
3. **Ejecutar este SQL**:

```sql
CREATE TABLE IF NOT EXISTS magic_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usos INT DEFAULT 0,
    INDEX idx_token (token),
    INDEX idx_phone (phone),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

4. **Verificar** que la tabla se creó:
```sql
DESCRIBE magic_links;
```

## 🔧 Archivos Actualizados con Fix Temporal

El archivo `MagicLinkController.php` ahora tiene manejo de errores mejorado:

- Si `magic_links` no existe, solo mostrará una advertencia en logs
- El sistema seguirá funcionando SOLO con código de 6 dígitos
- Una vez creada la tabla, el magic link funcionará automáticamente

**Código del fix**:
```php
try {
    // Intentar guardar magic token
    $stmt->execute([$magicToken, $phone]);
} catch (Exception $e) {
    // No fallar si magic_links no existe (compatibilidad)
    error_log("ADVERTENCIA: No se pudo guardar en magic_links");
}
```

## 📊 Verificación Post-Fix

Después de crear la tabla, verifica:

1. **Solicitar código nuevo**:
   ```
   https://camella.com.co/index.php?view=loginPhone
   ```

2. **Revisar SMS recibido**, debe contener:
   ```
   Camella.com.co
   Codigo: 123456
   https://camella.com.co/m/a1b2c3d4
   Valido 5 min.
   ```

3. **Probar magic link**:
   - Click en el link del SMS
   - Debe llevarte directo al dashboard
   - Sin necesidad de ingresar código

4. **Verificar en base de datos**:
   ```sql
   SELECT * FROM magic_links ORDER BY created_at DESC LIMIT 5;
   ```

## 🔍 Logs para Debug

Si persiste el error, revisar logs de error de PHP en Hostinger:

```bash
# Ubicación típica
/home/u179023609/logs/error_log
```

Buscar líneas como:
```
ERROR saveVerificationCode: ...
ADVERTENCIA: No se pudo guardar en magic_links: ...
```

## 📁 Archivos Creados para el Fix

1. **`setup_magic_links_production.php`** 
   - Script PHP visual para crear tabla
   - Incluye verificaciones y estructura detallada
   - ⚠️ Eliminar después de usar

2. **`database_magic_links_production.sql`**
   - SQL puro para ejecutar en phpMyAdmin
   - Alternativa al script PHP

3. **`MagicLinkController.php`** (actualizado)
   - Mejor manejo de errores
   - Logs detallados
   - Compatibilidad si tabla no existe

## 🚀 Deployment

### Archivos que DEBEN subirse a producción:

```
✅ controllers/MagicLinkController.php  (actualizado con fix)
✅ setup_magic_links_production.php     (temporal, eliminar después)
📄 database_magic_links_production.sql (referencia, no es necesario subir)
```

### Orden de acciones:

1. Subir `MagicLinkController.php` actualizado
2. Subir `setup_magic_links_production.php`
3. Ejecutar script en navegador
4. Verificar que tabla existe
5. Probar login con SMS
6. **Eliminar** `setup_magic_links_production.php`

## ✅ Checklist de Validación

- [ ] Tabla `magic_links` existe en producción
- [ ] Tabla tiene estructura correcta (5 columnas)
- [ ] MagicLinkController.php actualizado en servidor
- [ ] Solicitud de código funciona sin error
- [ ] SMS contiene magic link
- [ ] Magic link es clickeable
- [ ] Click en link lleva al dashboard
- [ ] Script setup eliminado del servidor

## 🆘 Si Aún Falla

1. **Revisar logs de error** de PHP
2. **Verificar permisos** de base de datos del usuario
3. **Confirmar** que tabla `verification_codes` existe
4. **Probar** crear registro manual:
   ```sql
   INSERT INTO magic_links (token, phone, created_at, usos)
   VALUES ('test1234', '+573001234567', NOW(), 0);
   ```

---

**Fecha**: Octubre 19, 2025  
**Prioridad**: 🔴 CRÍTICA  
**Estado**: Pendiente de deployment  
**Archivos listos**: ✅ Todos creados
