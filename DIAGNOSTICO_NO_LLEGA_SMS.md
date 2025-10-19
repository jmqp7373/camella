# 🚨 DIAGNÓSTICO: No se reciben SMS en +573103951529

## 📋 Problema Reportado

- **URL de acceso**: https://camella.com.co/index.php?view=loginPhone
- **Número de teléfono**: +573103951529
- **Síntoma**: No se recibe ningún SMS al solicitar código

## 🔍 Posibles Causas

1. **Tabla `magic_links` no existe en producción** (error que vimos antes)
2. **Credenciales de Twilio incorrectas o faltantes**
3. **SDK de Twilio no instalado** (`composer install` no ejecutado)
4. **Número en lista negra** de Twilio (cuenta trial)
5. **Error en el código** que no se está reportando
6. **Problemas de red/firewall** bloqueando conexión a Twilio API

## ✅ Plan de Diagnóstico (EJECUTAR EN ORDEN)

### Paso 1: Verificar Tabla `magic_links`

Ejecutar en producción:
```
https://camella.com.co/setup_magic_links_production.php
```

**Resultado esperado**: 
- ✅ Tabla creada o ya existente
- ✅ Sin errores de base de datos

Si falla, crear manualmente en phpMyAdmin:
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

### Paso 2: Diagnóstico Completo del Sistema

Ejecutar:
```
https://camella.com.co/diagnostico_sms.php
```

**Verificar que TODO esté en verde (✅)**:
- [x] Archivos de configuración existen
- [x] Conexión a base de datos OK
- [x] Tablas `verification_codes`, `magic_links`, `users` existen
- [x] Credenciales Twilio definidas (TWILIO_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER)
- [x] Composer instalado (`vendor/autoload.php`)
- [x] SDK de Twilio cargado

**Si hay errores**:
- ❌ **Composer no instalado**: Conectar por SSH y ejecutar `composer install`
- ❌ **Credenciales faltantes**: Verificar archivo `config/database.php`
- ❌ **Tablas no existen**: Ejecutar scripts SQL de creación

### Paso 3: Test Directo de Envío de SMS

Ejecutar:
```
https://camella.com.co/test_sms_directo.php
```

**Acciones**:
1. Ingresar número: `+573103951529`
2. Click en "Enviar SMS de Prueba"
3. Verificar respuesta

**Resultados posibles**:

#### ✅ ÉXITO - SMS Enviado
```
✅ SMS Enviado Exitosamente
SID: SM...
Estado: queued / sent / delivered
```
→ **El problema NO es Twilio**, revisar formulario de login

#### ❌ ERROR - Credenciales Inválidas
```
❌ Error: Unable to create record: Authenticate
```
→ **Credenciales Twilio incorrectas**, revisar:
- TWILIO_SID (debe empezar con `AC`)
- TWILIO_AUTH_TOKEN (32 caracteres)
- TWILIO_FROM_NUMBER (formato: `+1234567890` o `whatsapp:+...`)

#### ❌ ERROR - Número no Permitido
```
❌ Error: The number +573103951529 is unverified
```
→ **Cuenta Twilio en modo Trial**:
- Solo puede enviar a números verificados
- Ir a: https://console.twilio.com/us1/develop/phone-numbers/manage/verified
- Agregar +573103951529 a la lista de números verificados

#### ❌ ERROR - Composer no Instalado
```
❌ Error: Class 'Twilio\Rest\Client' not found
```
→ **SDK de Twilio faltante**:
```bash
cd /home/u179023609/public_html
composer install
```

## 🔧 Soluciones Rápidas

### Solución 1: Número No Verificado (Cuenta Trial)

Si Twilio está en modo Trial, debes verificar tu número:

1. **Ir a Twilio Console**:
   ```
   https://console.twilio.com/us1/develop/phone-numbers/manage/verified
   ```

2. **Click en "Verify a number"**

3. **Ingresar**: `+573103951529`

4. **Verificar código** que te llega por llamada

5. **Probar de nuevo**

### Solución 2: Actualizar a Cuenta Paid (Recomendado)

Para enviar SMS a cualquier número sin restricciones:

1. Ir a: https://console.twilio.com/us1/billing/manage-billing/upgrade
2. Agregar tarjeta de crédito (no cobran hasta que uses)
3. Recargar mínimo $20 USD
4. Automáticamente se quitan restricciones

### Solución 3: Verificar Credenciales

En `config/database.php` debe estar:

```php
// Credenciales Twilio
define('TWILIO_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'); // 34 caracteres
define('TWILIO_AUTH_TOKEN', 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'); // 32 caracteres
define('TWILIO_FROM_NUMBER', '+1234567890'); // Número comprado en Twilio
```

Para WhatsApp:
```php
define('TWILIO_FROM_NUMBER', 'whatsapp:+14155238886'); // Twilio Sandbox
```

### Solución 4: Composer Install

Si falta vendor/autoload.php:

**Opción A - Por SSH**:
```bash
ssh u179023609@camella.com.co
cd public_html
composer install
```

**Opción B - Por cPanel**:
1. Ir a "Terminal" en cPanel
2. Ejecutar:
```bash
cd public_html
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer.phar install
```

## 📊 Checklist de Verificación

```
ANTES DE DEPLOYMENT:
□ Ejecutar: setup_magic_links_production.php
□ Verificar tabla magic_links existe
□ Confirmar vendor/autoload.php existe
□ Validar credenciales Twilio en config/database.php

DESPUÉS DE DEPLOYMENT:
□ Ejecutar: diagnostico_sms.php (todo en verde)
□ Ejecutar: test_sms_directo.php (SMS recibido)
□ Probar: index.php?view=loginPhone (código recibido)
□ Verificar: Magic link clickeable en SMS
□ Confirmar: Login exitoso con magic link

LIMPIEZA:
□ Eliminar: setup_magic_links_production.php
□ Eliminar: diagnostico_sms.php
□ Eliminar: test_sms_directo.php
```

## 🆘 Si Nada Funciona

### Revisar Logs de Error

**Ubicación logs en Hostinger**:
```
/home/u179023609/logs/error_log
```

**Buscar líneas como**:
```
MagicLinkController: Error al enviar SMS...
Error guardando código...
Twilio SDK Error...
```

### Activar Debug Temporal

En `controllers/MagicLinkController.php`, agregar al inicio del método `sendWhatsAppMessage`:

```php
error_log("=== DEBUG SMS ===");
error_log("Teléfono: $phone");
error_log("Código: $code");
error_log("Token: $magicToken");
error_log("TWILIO_SID: " . (defined('TWILIO_SID') ? 'DEFINIDO' : 'NO DEFINIDO'));
error_log("TWILIO_AUTH_TOKEN: " . (defined('TWILIO_AUTH_TOKEN') ? 'DEFINIDO' : 'NO DEFINIDO'));
error_log("TWILIO_FROM_NUMBER: " . (defined('TWILIO_FROM_NUMBER') ? TWILIO_FROM_NUMBER : 'NO DEFINIDO'));
```

Luego revisar logs después de intentar enviar.

## 📞 Contacto Twilio

Si el problema es con Twilio:
- **Support**: https://support.twilio.com
- **Console**: https://console.twilio.com
- **Status**: https://status.twilio.com (verificar caídas)

---

**Fecha**: Octubre 19, 2025  
**Prioridad**: 🔴 CRÍTICA  
**Estado**: En diagnóstico  
**Usuario afectado**: +573103951529
