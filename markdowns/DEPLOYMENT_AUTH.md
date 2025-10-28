# Guía de Despliegue a Producción - Camella.com.co
## Sistema de Autenticación con SMS

### 📦 Archivos a subir al servidor

#### **Archivos principales:**
- ✅ `controllers/MagicLinkController.php` (con historial)
- ✅ `views/loginPhone.php`
- ✅ `partials/header.php` (con logout)
- ✅ `assets/css/header.css` (estilos logout)
- ✅ `logout.php`
- ✅ `database_history_table.sql`

#### **Archivos de configuración (NO subir a Git):**
- ⚠️ `config/config.php` (editar en servidor con credenciales de producción)

---

### 🗄️ Crear tabla en producción

**Ejecutar en phpMyAdmin de Hostinger:**

```sql
CREATE TABLE IF NOT EXISTS verification_codes_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(6) NOT NULL,
    magic_token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    status ENUM('created', 'used', 'expired', 'failed') DEFAULT 'created',
    user_id INT NULL DEFAULT NULL,
    ip_address VARCHAR(45) NULL DEFAULT NULL,
    user_agent TEXT NULL DEFAULT NULL,
    sms_sid VARCHAR(100) NULL DEFAULT NULL,
    INDEX idx_phone (phone),
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_used_at (used_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 🔧 Configuración en producción

#### **1. Verificar config.php en producción:**

```php
// En el servidor, debe tener:
define('TWILIO_SID', 'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_AUTH_TOKEN', 'tu_auth_token_real');
define('TWILIO_FROM_NUMBER', '+1xxxxxxxxxx');
```

#### **2. Verificar que vendor/autoload.php existe:**

Si no existe, ejecutar en el servidor:
```bash
composer install
```

---

### 🚀 Proceso de despliegue

#### **Opción 1: GitHub Actions (Recomendado)**

1. Hacer commit de todos los cambios:
```bash
git add .
git commit -m "feat: Sistema de autenticación con SMS y historial de auditoría"
git push origin main
```

2. GitHub Actions se encargará del despliegue automático

#### **Opción 2: FTP Manual**

1. Conectar por FileZilla/FTP a Hostinger
2. Subir los archivos listados arriba
3. Crear la tabla en phpMyAdmin

---

### ✅ Verificación post-despliegue

1. **Probar login:**
   - Ir a: `https://camella.com.co/index.php?view=loginPhone`
   - Ingresar número colombiano
   - Verificar que llegue SMS
   - Ingresar código
   - Verificar que funcione el login

2. **Verificar historial:**
   - Acceder a phpMyAdmin
   - Ver tabla `verification_codes_history`
   - Debe tener registros con status 'used'

3. **Verificar logout:**
   - Hacer clic en botón "Salir"
   - Verificar que cierre sesión correctamente

---

### 📊 Monitoreo

**Consulta SQL para ver estadísticas:**

```sql
SELECT 
    status,
    COUNT(*) as total,
    DATE(created_at) as fecha
FROM verification_codes_history
GROUP BY status, DATE(created_at)
ORDER BY fecha DESC;
```

**Consulta para ver últimos logins:**

```sql
SELECT 
    h.phone,
    h.code,
    h.created_at as codigo_enviado,
    h.used_at as codigo_usado,
    h.sms_sid,
    u.id as user_id
FROM verification_codes_history h
LEFT JOIN users u ON h.user_id = u.id
WHERE h.status = 'used'
ORDER BY h.used_at DESC
LIMIT 20;
```

---

### 🔒 Seguridad en producción

- ✅ Códigos expiran en 5 minutos
- ✅ Códigos se eliminan después de usarse
- ✅ Historial completo para auditoría
- ✅ IP y User-Agent registrados
- ✅ SMS SID guardado para trazabilidad
- ✅ Sesiones expiran en 24 horas

---

### 📱 Costos de Twilio

- **Balance actual:** $20.00
- **Gastado en octubre:** $2.06
- **Costo por SMS:** ~$0.0075 USD
- **SMS restantes aproximados:** ~2,400 mensajes

---

### 🐛 Troubleshooting

**Si los SMS no llegan:**
1. Verificar credenciales de Twilio en config.php
2. Verificar que vendor/autoload.php exista
3. Revisar logs de Twilio en: https://console.twilio.com
4. Verificar que el número esté en formato +57XXXXXXXXXX

**Si el historial no se llena:**
1. Verificar que la tabla existe: `SHOW TABLES LIKE 'verification_codes_history';`
2. Revisar permisos del usuario de BD
3. Verificar logs de Apache/PHP

**Si el logout no funciona:**
1. Verificar que session_start() esté activo
2. Limpiar cookies del navegador
3. Verificar que logout.php exista

---

**Última actualización:** 2025-10-14
**Versión:** 2.0.0 (con historial)
