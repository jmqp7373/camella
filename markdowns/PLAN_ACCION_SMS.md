# 🎯 PLAN DE ACCIÓN: Solucionar SMS que no llegan

## ✅ Archivos Listos para Subir al Servidor

Los siguientes archivos ya están corregidos y listos para subir a producción:

1. **diagnostico_sms.php** - Verificación completa del sistema
2. **test_sms_directo.php** - Prueba de envío SMS directa
3. **verificar_cuenta_twilio.php** - Verifica estado de cuenta Twilio (NUEVO)
4. **setup_magic_links_production.php** - Crea tabla magic_links

## 📝 PASO A PASO - EJECUTA EN ESTE ORDEN

### Paso 1: Subir Archivos al Servidor ⬆️

Usando FileZilla o File Manager de Hostinger, sube estos archivos a la raíz:
- `diagnostico_sms.php`
- `test_sms_directo.php`
- `verificar_cuenta_twilio.php`
- `setup_magic_links_production.php`

### Paso 2: Crear Tabla Magic Links 🗄️

Acceder a:
```
https://camella.com.co/setup_magic_links_production.php
```

**Resultado esperado**: ✅ Tabla creada exitosamente

### Paso 3: Verificar Estado de Cuenta Twilio 🔍

Acceder a:
```
https://camella.com.co/verificar_cuenta_twilio.php
```

**Esto te dirá**:
- ✅ Si la cuenta es Trial o Paid
- ✅ Si +573103951529 está en la lista de verificados
- ✅ Si el número FROM (+14783959907) es válido
- ✅ Estado de las credenciales

**SI LA CUENTA ES TRIAL Y TU NÚMERO NO ESTÁ VERIFICADO**:
1. Ir a: https://console.twilio.com/us1/develop/phone-numbers/manage/verified
2. Click "Verify a number"
3. Ingresar: `+573103951529`
4. Recibirás una llamada con código
5. Ingresar código y listo

### Paso 4: Diagnóstico Completo 🧪

Acceder a:
```
https://camella.com.co/diagnostico_sms.php
```

**Verifica que TODO esté en verde**:
- ✅ Archivos de configuración
- ✅ Conexión a base de datos
- ✅ Tablas existen
- ✅ Credenciales Twilio definidas
- ✅ Composer instalado
- ✅ SDK Twilio cargado

### Paso 5: Prueba Directa de SMS 📱

Acceder a:
```
https://camella.com.co/test_sms_directo.php
```

1. El número +573103951529 ya viene pre-cargado
2. Click en "Enviar SMS de Prueba"
3. Espera la respuesta

**SI RECIBES EL SMS** ✅:
→ El problema NO es Twilio, es el formulario loginPhone

**SI NO RECIBES EL SMS** ❌:
→ Ver el error específico y aplicar solución correspondiente

## 🔧 Soluciones a Problemas Comunes

### Error: "The number +573103951529 is unverified"
**Causa**: Cuenta Trial, número no verificado  
**Solución**: Verificar número en Twilio Console (ver Paso 3)

### Error: "Unable to create record: Authenticate"
**Causa**: Credenciales incorrectas  
**Solución**: Verificar TWILIO_SID, TWILIO_AUTH_TOKEN en config/config.php

### Error: "Class 'Twilio\Rest\Client' not found"
**Causa**: Composer no instalado  
**Solución**: Conectar por SSH:
```bash
cd /home/u179023609/public_html
composer install
```

### Error: "Table 'magic_links' doesn't exist"
**Causa**: Tabla no creada  
**Solución**: Ejecutar setup_magic_links_production.php (Paso 2)

## 📊 Checklist Final

```
ANTES:
□ Subir 4 archivos PHP al servidor
□ Ejecutar setup_magic_links_production.php
□ Ejecutar verificar_cuenta_twilio.php
□ Si es Trial: Verificar +573103951529 en Twilio Console

DIAGNÓSTICO:
□ Ejecutar diagnostico_sms.php - Todo en verde
□ Ejecutar test_sms_directo.php - SMS recibido

PRUEBA REAL:
□ Ir a https://camella.com.co/index.php?view=loginPhone
□ Ingresar: +573103951529
□ Recibir SMS con código y magic link

LIMPIEZA:
□ Eliminar diagnostico_sms.php
□ Eliminar test_sms_directo.php
□ Eliminar verificar_cuenta_twilio.php
□ Eliminar setup_magic_links_production.php
```

## 🎯 Resultado Esperado

Después de seguir estos pasos, deberías recibir un SMS como este:

```
Camella.com.co
Codigo: 123456
https://camella.com.co/m/a1b2c3d4
Valido 5 min.
```

Y podrás:
1. **Opción A**: Ingresar el código 123456 en el formulario
2. **Opción B**: Hacer click directo en el link y acceder

## 💡 Información Importante

### Credenciales Actuales (en config/config.php):
Las credenciales están correctamente configuradas en `config/config.php`:
- ✅ TWILIO_SID (empieza con AC)
- ✅ TWILIO_AUTH_TOKEN (32 caracteres)
- ✅ TWILIO_FROM_NUMBER (+14783959907)

### Problema Más Probable:
**Cuenta Twilio en modo Trial** sin el número +573103951529 verificado.

### Solución Definitiva (Recomendado):
Actualizar a cuenta Twilio Paid ($20 USD mínimo):
- https://console.twilio.com/us1/billing/manage-billing/upgrade
- Elimina todas las restricciones
- Puedes enviar a cualquier número
- Sin límites de mensajes

---

**Fecha**: Octubre 19, 2025  
**Archivos listos**: ✅ 4 scripts de diagnóstico  
**Estado**: Listo para deployment  
**Próximo paso**: Subir archivos y ejecutar Paso 2
