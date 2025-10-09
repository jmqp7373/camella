# NOTAS DE DESARROLLO - CAMELLA.COM.CO

## 🔧 **MANTENIMIENTO DE ARCHIVOS**

### ⚠️ **Scripts Temporales Eliminados:**

#### **scripts/verify_config.php** - ❌ **ELIMINAR DEL SERVIDOR**
- **Estado:** Eliminado del repositorio
- **Acción requerida:** Si existe en el servidor de producción, **ELIMINARLO manualmente**
- **Comando en servidor:** `rm scripts/verify_config.php`
- **Razón:** Script temporal de verificación ya no es necesario

---

## 🧪 **HERRAMIENTAS DE TESTING:**

### **scripts/mail_ping.php** - ✅ **Test SMTP**
- **Propósito:** Probar envío de correos SMTP en producción
- **URL:** `https://camella.com.co/scripts/mail_ping.php?t=CAMELLA-PING-2025`
- **Respuesta esperada:** `MAIL_PING: OK`
- **Seguridad:** Protegido por token, no expone credenciales

---

## 📁 **ARCHIVOS SENSIBLES:**

### **config/config.php** - 🔒 **NO EN REPOSITORIO**
- **Estado:** Excluido del repo por seguridad
- **Método de actualización:** FileZilla (FTP/SFTP) solamente
- **Recordatorio:** Cualquier cambio en config.php debe subirse manualmente al servidor

---

## 🎯 **PRÓXIMOS MANTENIMIENTOS:**
1. Eliminar `scripts/verify_config.php` del servidor si existe
2. Probar `scripts/mail_ping.php` en producción
3. Eliminar `scripts/mail_ping.php` del servidor después de verificar SMTP