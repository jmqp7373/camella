# 📋 MIGRACIÓN A LABSMOBILE OTP

**Fecha:** 2025-10-20  
**Estado:** ✅ Script creado - ⚙️ Pendiente configuración de credenciales

## 🎯 ARCHIVO CREADO

### ✅ `/scripts/sendSmsLabsMobile.php`
- ✅ Estructura completa implementada
- ✅ Compatible con `MagicLinkController.php`
- ✅ Generación de código de 6 dígitos
- ✅ Token de 6 caracteres para magic link
- ✅ Validación de números colombianos
- ✅ Logging detallado para debugging
- ✅ Manejo de errores robusto
- ✅ API LabsMobile OTP integrada

## 🔧 CONFIGURACIÓN REQUERIDA

### 1. **Credenciales LabsMobile**
```php
// Actualizar en /scripts/sendSmsLabsMobile.php líneas 21-22:
$LABSMOBILE_USER = 'TU_USUARIO_REAL@dominio.com';
$LABSMOBILE_TOKEN = 'TU_TOKEN_REAL_DEL_PANEL';
```

### 2. **Obtener credenciales**
- **Panel LabsMobile:** https://www.labsmobile.com/
- **Ubicación del token:** Dashboard → API → Configuración
- **Usuario:** El email de tu cuenta LabsMobile
- **Token:** Código alfanumérico del panel de API

## 🔄 COMPARACIÓN CON MESSAGEBIRD

### MessageBird (Actual)
```
❌ API Key expirada/inválida
❌ Errores 401/404 constantes
🔧 Endpoint: https://rest.messagebird.com/messages
📋 Formato: URL-encoded
```

### LabsMobile OTP (Nuevo)
```
✅ API OTP especializada
✅ Credenciales por configurar
🔧 Endpoint: https://api.labsmobile.com/otp/sendCode
📋 Formato: Query parameters + Basic Auth
```

## 📊 ESTRUCTURA DE RESPUESTA

### ✅ Formato de Salida (Compatible con MagicLinkController)
```json
{
  "success": true,
  "code": "123456",
  "token": "abc123",
  "link": "https://camella.com.co/m/abc123",
  "status": "sent",
  "provider": "labsmobile"
}
```

### ❌ Formato de Error
```json
{
  "success": false,
  "error": "Descripción del error específico"
}
```

## 🧪 ARCHIVOS DE PRUEBA CREADOS

### 🌐 `test_labsmobile_otp.php`
- Interfaz web para pruebas
- Validación de formato de números
- Visualización de respuestas detallada
- **No subir a producción**

### 📝 `test_directo_labsmobile.php`
- Test directo por CLI
- Debugging de configuración
- **No subir a producción**

## 🚀 PASOS PARA ACTIVAR LABSMOBILE

### 1. **Configurar Credenciales**
```bash
# Editar el archivo:
# /scripts/sendSmsLabsMobile.php

# Actualizar líneas 21-22:
$LABSMOBILE_USER = 'tu_usuario_real@dominio.com';
$LABSMOBILE_TOKEN = 'tu_token_real_del_panel';
```

### 2. **Probar Localmente**
```bash
# Método 1: Test web
http://localhost/camella.com.co/test_labsmobile_otp.php

# Método 2: Test CLI
php test_directo_labsmobile.php
```

### 3. **Actualizar MagicLinkController (Opcional)**
Si quieres cambiar el proveedor predeterminado:

```php
// En controllers/MagicLinkController.php línea 81:
// CAMBIAR DE:
$ch = curl_init('http..../scripts/sendSmsMessageBird.php');

// A:
$ch = curl_init('http..../scripts/sendSmsLabsMobile.php');
```

### 4. **Desplegar a Producción**
```bash
# Subir via FileZilla:
✅ /scripts/sendSmsLabsMobile.php (con credenciales reales)
❌ NO subir archivos test_*.php
```

## 🔍 DETALLES TÉCNICOS

### API LabsMobile OTP
```
Método: GET
URL: https://api.labsmobile.com/otp/sendCode
Autenticación: Basic Auth (Base64)
Parámetros:
  - env: 'CamellaApp'
  - sender: 'Camella' 
  - phone_number: '573123456789'
  - message: 'Código: 123456\nhttps://camella.com.co/m/abc123'
  - digits: 6
```

### Respuesta LabsMobile
```json
{
  "status": 1,           // 1 = éxito, 0 = error
  "message": "Sent"      // Mensaje descriptivo
}
```

## ✅ VENTAJAS DE LABSMOBILE

1. **API OTP Especializada** - Diseñada específicamente para códigos
2. **Confiabilidad** - Proveedor establecido en España/Latam  
3. **Documentación Clara** - API bien documentada
4. **Soporte Local** - Soporte en español
5. **Pricing Competitivo** - Tarifas accesibles para SMS

## 🚨 CHECKLIST PRE-PRODUCCIÓN

```
□ Obtener credenciales reales de LabsMobile
□ Actualizar $LABSMOBILE_USER y $LABSMOBILE_TOKEN
□ Probar envío de SMS real con test_labsmobile_otp.php
□ Verificar recepción del SMS en teléfono real
□ Confirmar que el magic link funciona
□ Subir archivo a producción vía FileZilla
□ Probar desde el sitio real de Camella
□ Verificar logs de error del servidor
```

---

**Próximo paso:** Configurar las credenciales reales de LabsMobile y probar el envío