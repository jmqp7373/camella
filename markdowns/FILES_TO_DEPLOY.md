# Archivos para subir a producción

## ✅ Archivos modificados/nuevos

### Controladores
- controllers/MagicLinkController.php

### Vistas
- views/loginPhone.php
- views/home.php (mensaje de logout)

### Parciales
- partials/header.php

### CSS
- assets/css/header.css

### Scripts de logout
- logout.php

### SQL
- database_history_table.sql

### Documentación
- DEPLOYMENT_AUTH.md
- AUTHENTICATION.md

## ⚠️ NO subir (ya están en servidor o son locales)
- config/config.php (editar directamente en servidor)
- vendor/ (ejecutar composer install en servidor)
- test*.php
- ver_*.php
- monitor_*.php
- create_*.php

## 📝 Comandos Git

```bash
# 1. Verificar archivos modificados
git status

# 2. Agregar archivos
git add controllers/MagicLinkController.php
git add views/loginPhone.php
git add views/home.php
git add partials/header.php
git add assets/css/header.css
git add logout.php
git add database_history_table.sql
git add DEPLOYMENT_AUTH.md
git add AUTHENTICATION.md

# 3. Commit
git commit -m "feat: Sistema completo de autenticación SMS con historial de auditoría

- Implementado login con código SMS vía Twilio
- Agregado sistema de logout con destrucción de sesión
- Creada tabla verification_codes_history para auditoría
- Agregados estilos responsive para header con usuario logueado
- Documentación completa de deployment y autenticación
- Registro de IP, User-Agent y SID de Twilio en historial
- Estados de código: created, used, expired, failed"

# 4. Push
git push origin main
```

## 🗄️ SQL a ejecutar en producción

Ver archivo: `database_history_table.sql`

Ejecutar en phpMyAdmin de Hostinger después del deployment.
