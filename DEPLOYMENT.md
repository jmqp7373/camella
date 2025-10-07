# 🔐 Configuración de Secretos para Deployment

Este archivo te guía para configurar los secretos necesarios en GitHub para el deployment automático.

## 📋 Secretos Requeridos

Ve a tu repositorio en GitHub → Settings → Secrets and variables → Actions → New repository secret

### 1. FTP_SERVER
**Nombre:** `FTP_SERVER`  
**Valor:** El servidor FTP de GoDaddy (ejemplo: `ftp.camella.com.co` o IP del servidor)

### 2. FTP_USERNAME  
**Nombre:** `FTP_USERNAME`  
**Valor:** Tu usuario FTP de GoDaddy (generalmente tu email o usuario de cPanel)

### 3. FTP_PASSWORD
**Nombre:** `FTP_PASSWORD`  
**Valor:** Tu contraseña FTP de GoDaddy

### 4. FTP_SERVER_DIR
**Nombre:** `FTP_SERVER_DIR`  
**Valor:** El directorio en el servidor (ejemplo: `/public_html/` o `/httpdocs/`)

## 🔍 Cómo obtener los datos FTP de GoDaddy

1. **Ingresa a tu cuenta de GoDaddy**
2. **Ve a "Hosting" → "Administrar"**
3. **Busca "Administrador de archivos" o "FTP"**
4. **Crea o usa credenciales FTP existentes**

### Ejemplo de valores típicos:
```
FTP_SERVER: ftp.camella.com.co
FTP_USERNAME: usuario@camella.com.co  
FTP_PASSWORD: tu_contraseña_segura
FTP_SERVER_DIR: /public_html/
```

## 🚀 Cómo funciona el deployment

1. **Trigger:** Cada vez que hagas `git push` a la rama `main`
2. **Proceso:** GitHub Actions toma los archivos y los sube por FTP
3. **Resultado:** Tu sitio se actualiza automáticamente en camella.com.co

## ✅ Verificación

Después de configurar los secretos:
1. Haz un push a main
2. Ve a Actions en GitHub para ver el progreso
3. Verifica que tu sitio se actualizó en https://camella.com.co

## 🛡️ Seguridad

- ✅ Los secretos están encriptados en GitHub
- ✅ Solo se usan durante el deployment
- ✅ No aparecen en los logs públicos
- ✅ Solo usuarios con acceso al repo pueden verlos