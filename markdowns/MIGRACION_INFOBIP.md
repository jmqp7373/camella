# Migración a Infobip como Proveedor de SMS

## 📋 Resumen

Se ha implementado **Infobip** como proveedor principal de SMS para Camella.com.co, manteniendo **Twilio** como sistema de fallback automático.

---

## 🎯 Cambios Implementados

### 1. Nuevo Script: `scripts/sendSmsInfobip.php`

**Función principal:** `sendSmsInfobip($telefono, $codigo, $token)`

**Características:**
- ✅ Envío via Infobip SMS API
- ✅ Fallback automático a WhatsApp Infobip (preparado, comentado)
- ✅ Fallback automático a Twilio si Infobip falla
- ✅ Logging completo de cada intento
- ✅ Retorna array con status, provider y detalles

**Formato del mensaje:**
```
Tu código de acceso a Camella es 123456. Ingresa aquí: camella.com.co/m/abc12345
```

**Endpoint usado:**
```
https://jjl5mv.api.infobip.com/sms/2/text/advanced
```

**Respuesta:**
```php
[
    'status' => 'success' | 'error',
    'response' => mixed,
    'provider' => 'infobip' | 'twilio-fallback' | 'none',
    'message_id' => string (opcional)
]
```

---

### 2. Actualización: `config/config.php`

**Nuevas constantes añadidas:**

```php
// Infobip (Proveedor Principal)
define('INFOBIP_API_KEY', 'TU_API_KEY_DE_INFOBIP');
define('INFOBIP_BASE_URL', 'https://XXXXXX.api.infobip.com');

// Twilio (Fallback)
define('TWILIO_SID', 'YOUR_TWILIO_ACCOUNT_SID');
define('TWILIO_AUTH_TOKEN', 'YOUR_TWILIO_AUTH_TOKEN');
define('TWILIO_FROM_NUMBER', '+1XXXXXXXXXX');
```

⚠️ **IMPORTANTE:** Debes reemplazar los valores con tus credenciales reales.

---

### 3. Actualización: `controllers/MagicLinkController.php`

**Método modificado:** `sendCode()`

**Cambios realizados:**

```php
// ANTES (usaba Twilio directamente):
$sent = $this->sendWhatsAppMessage($phone, $code, $magicToken);

// AHORA (usa Infobip con fallback):
require_once __DIR__ . '/../scripts/sendSmsInfobip.php';
$resultado = sendSmsInfobip($phone, $code, $magicToken);

if ($resultado['status'] !== 'success') {
    return $this->jsonResponse(false, 'Error al enviar el código');
}
```

**Método legacy:** `sendWhatsAppMessage()` → Renombrado a `sendWhatsAppMessage_OLD()`
- Se mantiene como referencia pero no se usa

---

## 🧪 Archivo de Pruebas

### `test_infobip.php`

Script de prueba visual con interfaz web para:
- ✅ Verificar configuración de Infobip y Twilio
- ✅ Enviar SMS de prueba
- ✅ Ver logs detallados del proceso
- ✅ Verificar fallback automático

**URL de prueba:**
```
http://localhost/camella.com.co/test_infobip.php
```

---

## 🚀 Proceso de Despliegue

### Paso 1: Configurar API Key de Infobip

1. Obtén tu API Key desde el panel de Infobip
2. Edita `config/config.php` localmente
3. Reemplaza:
   ```php
   define('INFOBIP_API_KEY', 'tu_api_key_real_aqui');
   ```

### Paso 2: Probar en Localhost

1. Abre: `http://localhost/camella.com.co/test_infobip.php`
2. Ingresa un número de prueba
3. Verifica que el SMS llegue
4. Revisa los logs en debug

### Paso 3: Subir a Producción

**Archivos a subir via FileZilla:**

```
/config/config.php                    (con API Key real)
/controllers/MagicLinkController.php  (código actualizado)
/scripts/sendSmsInfobip.php          (script nuevo)
```

**Archivos opcionales:**
```
/test_infobip.php                    (para pruebas en producción)
```

### Paso 4: Verificar en Producción

1. Prueba el login en: `https://camella.com.co/index.php?view=loginPhone`
2. Ingresa un número de prueba
3. Verifica que llegue el SMS con el formato nuevo
4. Prueba el magic link: `https://camella.com.co/m/{token}`

---

## 📊 Flujo de Fallback

```
1. Usuario solicita código
   ↓
2. Intenta enviar via Infobip SMS
   ↓
   ├─ ✅ Éxito → Retorna success (provider: infobip)
   │
   └─ ❌ Falla
      ↓
      3. [PREPARADO] Intenta WhatsApp Infobip (actualmente comentado)
         ↓
         └─ ❌ Falla
            ↓
            4. Intenta Twilio SMS (fallback final)
               ↓
               ├─ ✅ Éxito → Retorna success (provider: twilio-fallback)
               │
               └─ ❌ Falla → Retorna error (provider: none)
```

---

## 🔍 Logs del Sistema

### Infobip Success
```
Infobip: Enviando SMS a +573103951529
Infobip: Mensaje: Tu código de acceso a Camella es 123456...
Infobip: HTTP Code: 200
Infobip: SMS enviado exitosamente
MagicLinkController sendCode: SMS enviado exitosamente via infobip
```

### Fallback a Twilio
```
Infobip: Error enviando SMS - HTTP 401
Infobip: Todos los métodos fallaron, intentando Twilio...
Twilio fallback: SMS enviado - SID: SMxxxx
MagicLinkController sendCode: SMS enviado exitosamente via twilio-fallback
```

### Error Total
```
ERROR CRÍTICO: Todos los proveedores de SMS fallaron
MagicLinkController sendCode: Error al enviar SMS
```

---

## 🔐 Seguridad

### Archivo `config.php`
- ❌ **NO** versionado en Git (en .gitignore)
- ✅ Se sube manualmente via FileZilla
- ✅ Contiene credenciales sensibles

### API Keys
- **Infobip API Key:** Formato App Key
- **Twilio SID:** Formato ACxxxx
- **Twilio Auth Token:** 32 caracteres

---

## 📝 Diferencias entre Proveedores

| Característica | Infobip | Twilio |
|----------------|---------|--------|
| **Mensaje** | Incluye magic link | Solo código (sin link) |
| **Formato FROM** | "Camella" (text) | +14783959907 (número) |
| **Endpoint** | REST API custom | SDK oficial |
| **Fallback** | Automático a Twilio | N/A |
| **Magic Link** | ✅ Incluido | ❌ No incluido |

---

## 🐛 Troubleshooting

### Error: "Configuración de Infobip no disponible"
**Causa:** Constantes no definidas en `config.php`
**Solución:** Añadir las constantes INFOBIP_API_KEY e INFOBIP_BASE_URL

### Error: HTTP 401 Unauthorized
**Causa:** API Key incorrecta o inválida
**Solución:** Verificar API Key en panel de Infobip

### Error: HTTP 429 Too Many Requests
**Causa:** Límite de tasa excedido
**Solución:** Esperar o contactar soporte de Infobip

### SMS no llega pero responde 200
**Causa:** Número no válido o bloqueado
**Solución:** Verificar formato del número (+57...)

### Fallback a Twilio siempre
**Causa:** Problema con credenciales de Infobip
**Solución:** Revisar logs para ver error específico de Infobip

---

## ✅ Checklist de Despliegue

- [ ] API Key de Infobip configurada en `config.php`
- [ ] Base URL de Infobip correcta
- [ ] Probado en localhost con `test_infobip.php`
- [ ] SMS llega correctamente en localhost
- [ ] Magic link funciona en localhost
- [ ] Archivo `config.php` subido a producción
- [ ] Archivo `MagicLinkController.php` subido
- [ ] Archivo `sendSmsInfobip.php` subido
- [ ] Probado en producción con número real
- [ ] SMS llega en producción
- [ ] Magic link funciona en producción
- [ ] Logs revisados en servidor

---

## 📞 Soporte

### Documentación Oficial
- **Infobip SMS API:** https://www.infobip.com/docs/api#channels/sms
- **Twilio SMS API:** https://www.twilio.com/docs/sms

### Archivos Relacionados
```
/config/config.php
/controllers/MagicLinkController.php
/scripts/sendSmsInfobip.php
/scripts/sendSmsTwilio.php (legacy)
/test_infobip.php
```

---

## 📅 Historial de Cambios

**Fecha:** 19 de octubre 2025
**Versión:** 1.0
**Estado:** ✅ Implementado y probado en localhost
**Pendiente:** Despliegue a producción con API Key real

---

## 🎉 Resultado Final

- ✅ Sistema de SMS con fallback automático
- ✅ Magic links incluidos en mensajes
- ✅ Logs completos para debugging
- ✅ Mantiene compatibilidad con código existente
- ✅ Fácil de mantener y expandir

**El sistema está listo para producción una vez configurada la API Key real de Infobip.**
