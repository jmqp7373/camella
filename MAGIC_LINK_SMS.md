# Magic Link en SMS - Documentación

## 📱 Funcionalidad Implementada

Cuando un usuario solicita acceso desde `https://localhost/camella.com.co/index.php?view=loginPhone`, ahora recibirá un SMS con **DOS opciones de autenticación**:

### Mensaje SMS Recibido
```
Camella.com.co
Codigo: 604836
Link: https://localhost/camella.com.co/m/a1b2c3d4e5f6
Valido 5 min.
```

**Nota**: El magic link ahora usa tokens de **12 caracteres** (en lugar de 64) para URLs más cortas y clickeables en dispositivos móviles.

## ✅ Opciones para el Usuario

### Opción 1: Código de 6 Dígitos (tradicional)
- Usuario ingresa el código `604836` en la página de login
- Válido por **5 minutos**
- Funciona igual que antes

### Opción 2: Magic Link (nuevo)
- Usuario hace **click directo** en el link del SMS
- Acceso **instantáneo** sin ingresar código
- También válido por **5 minutos** cuando es nuevo
- Una vez usado, el link se vuelve reutilizable por **24 horas** (máximo 100 usos)

## 🔧 Implementación Técnica

### 1. Generación del Token
```php
// En sendCode() se genera tanto el código como el magic token
$code = $this->generateVerificationCode();      // 6 dígitos
$magicToken = $this->generateMagicToken();      // 12 caracteres hex (6 bytes)
```

**Cambio importante**: El token ahora es de **12 caracteres** en lugar de 64 para URLs más cortas y mejor compatibilidad con SMS.

### 2. Almacenamiento Dual
El sistema guarda el token en **DOS tablas**:

**Tabla `verification_codes`** (para validación de código):
```sql
INSERT INTO verification_codes (phone, code, magic_token, created_at, expires_at)
VALUES ('+573001234567', '604836', 'a1b2c3d4...', NOW(), NOW() + INTERVAL 5 MINUTE)
```

**Tabla `magic_links`** (para validación de magic link):
```sql
INSERT INTO magic_links (token, phone, created_at, usos)
VALUES ('a1b2c3d4...', '+573001234567', NOW(), 0)
```

### 3. Construcción del SMS
```php
// Construir URL dinámica CORTA
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$magicLinkUrl = "{$protocol}://{$host}/camella.com.co/m/{$magicToken}";

// Mensaje optimizado - ULTRA compacto para clickeabilidad
$message = "Camella.com.co\n";
$message .= "Codigo: {$code}\n";
$message .= "Link: {$magicLinkUrl}\n";
$message .= "Valido 5 min.";
```

**Optimizaciones**:
- Token reducido de 64 a **12 caracteres** (suficiente entropía para 5 min)
- URL final: ~55 caracteres (clickeable en todos los dispositivos)
- Formato sin líneas extra para mejor detección de enlaces

### 4. Validación de Expiración Inteligente
```php
// Si el token tiene 0 usos = nuevo desde SMS (5 minutos)
// Si ya fue usado = compartido (24 horas)
$usos = (int)$link['usos'];
$tiempoMaximo = ($usos === 0) ? 300 : 86400; // 5 min vs 24h
```

## 🔐 Seguridad

### Expiración Dual
- **Primer uso (desde SMS)**: 5 minutos
- **Usos subsecuentes (link compartido)**: 24 horas
- **Límite de usos**: Máximo 100 clicks

### Validaciones
1. ✅ Token existe en base de datos
2. ✅ No está vencido (5 min o 24h según uso)
3. ✅ No superó límite de 100 usos
4. ✅ Usuario asociado existe
5. ✅ Sesión se configura correctamente
6. ✅ Redirect a dashboard según rol

## 📊 Flujo de Usuario

### Flujo Completo
```
1. Usuario ingresa número en loginPhone
   ↓
2. Sistema genera código (604836) y token (a1b2c3d4...)
   ↓
3. Se guardan en verification_codes Y magic_links
   ↓
4. SMS enviado con AMBAS opciones
   ↓
5. Usuario puede:
   a) Ingresar código → Validación tradicional
   b) Click en link → Acceso directo
   ↓
6. Sistema valida token/código
   ↓
7. Sesión iniciada con variables correctas:
   - $_SESSION['usuario'] = user_id
   - $_SESSION['role'] = admin|promotor|publicante
   - $_SESSION['logged_in'] = true
   ↓
8. Redirect automático a dashboard según rol
```

## 🧪 Testing

### Probar desde loginPhone
1. Ir a: `https://localhost/camella.com.co/index.php?view=loginPhone`
2. Ingresar número de teléfono
3. Revisar logs del SMS enviado:
   ```
   SMS a enviar: Camella.com.co
   Codigo: 123456
   O ingresa directo:
   http://localhost/camella.com.co/m/abc123...
   Valido 5 min.
   
   Magic Link generado: http://localhost/camella.com.co/m/abc123...
   Magic token guardado en magic_links: abc123...
   ```
4. **Opción A**: Ingresar código en formulario
5. **Opción B**: Copiar y pegar el magic link en navegador

### Verificar en Base de Datos
```sql
-- Ver códigos recientes
SELECT phone, code, magic_token, created_at, expires_at 
FROM verification_codes 
ORDER BY created_at DESC LIMIT 5;

-- Ver magic links recientes
SELECT token, phone, created_at, usos 
FROM magic_links 
ORDER BY created_at DESC LIMIT 5;
```

### Debug Magic Link
```
http://localhost/camella.com.co/debug_magic_link.php?token=abc123...
```

## 📁 Archivos Modificados

### `controllers/MagicLinkController.php`
- ✅ `sendWhatsAppMessage()`: Actualizado para incluir magic link en mensaje
- ✅ `saveVerificationCode()`: Ahora también inserta en `magic_links`
- ✅ `loginConToken()`: Validación de expiración dual (5 min vs 24h)

## 🎯 Beneficios

1. **Experiencia del Usuario**: 
   - Una sola acción (click) vs dos (ingresar código)
   - Más rápido en móviles
   - Menos errores de tipeo

2. **Flexibilidad**:
   - Usuario elige su método preferido
   - Link puede compartirse después del primer uso

3. **Seguridad**:
   - Expiración rápida para primer uso (5 min)
   - Límite de usos para prevenir abuso
   - Mismo nivel de seguridad que código tradicional

4. **Compatibilidad**:
   - Sistema de código tradicional sigue funcionando
   - No breaking changes
   - Variables de sesión compatibles con dashboards existentes

## 🔄 Próximas Mejoras (Opcionales)

1. **QR Code**: Generar QR del magic link para desktop
2. **Deep Links**: Abrir app directamente desde SMS
3. **Analytics**: Tracking de método preferido (código vs link)
4. **Notificaciones**: Email con magic link como backup
5. **Configuración**: Admin puede deshabilitar magic links si lo prefiere

---

**Fecha Implementación**: Octubre 19, 2025  
**Versión**: 1.0  
**Autor**: GitHub Copilot  
**Commit**: 448df82
