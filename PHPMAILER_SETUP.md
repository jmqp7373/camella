# INSTALACIÓN DE PHPMAILER PARA CAMELLA.COM.CO

## 📧 **PHPMailer no detectado**

El sistema `MailHelper.php` está configurado para usar PHPMailer, pero no se encontró en el proyecto.

### **🔧 Opciones de instalación:**

#### **Opción 1: Composer (Recomendado)**
```bash
composer require phpmailer/phpmailer
```

#### **Opción 2: Descarga directa**
1. Descargar desde: https://github.com/PHPMailer/PHPMailer/releases
2. Extraer en `vendor/phpmailer/phpmailer/`
3. Incluir manualmente en `MailHelper.php`

#### **Opción 3: Sin PHPMailer (Actual)**
El sistema usa fallback a `mail()` básico de PHP si PHPMailer no está disponible.

### **⚠️ Estado actual:**
- ✅ Fallback SMTP implementado
- ✅ Logging seguro funcionando
- ⚠️ PHPMailer no instalado (usar mail() básico)

### **📋 Para producción:**
1. Instalar PHPMailer o usar mail() básico
2. Configurar SMTP_PASS en config.php
3. Probar con: `/scripts/mail_ping.php?t=CAMELLA-PING-2025`

**El sistema funcionará sin PHPMailer, pero tendrá menos funcionalidades SMTP.**