# Sistema de Autenticación y Logout - Camella.com.co

## 📋 Archivos modificados/creados

### 1. **partials/header.php**
- ✅ Agregado lógica condicional para mostrar botones según estado de autenticación
- ✅ Muestra información del usuario (últimos 4 dígitos del teléfono)
- ✅ Botón de logout solo visible cuando el usuario está autenticado

### 2. **logout.php** (NUEVO)
- ✅ Destruye todas las variables de sesión
- ✅ Elimina la cookie de sesión
- ✅ Destruye la sesión completamente
- ✅ Redirige al home con mensaje de confirmación

### 3. **assets/css/header.css**
- ✅ Estilos para `.user-info` (información del usuario)
- ✅ Estilos para `.btn-logout` (botón de cerrar sesión)
- ✅ Responsive design para pantallas pequeñas

### 4. **views/home.php**
- ✅ Mensaje de confirmación cuando el usuario cierra sesión

## 🔐 Flujo de autenticación

### **Login (con SMS)**
1. Usuario ingresa su número de teléfono
2. Sistema envía código de 6 dígitos por SMS (Twilio)
3. Usuario ingresa el código
4. Sistema valida el código
5. Sistema crea/actualiza usuario en BD
6. Se establece la sesión:
   - `$_SESSION['usuario']` = ID del usuario
   - `$_SESSION['user_id']` = ID del usuario
   - `$_SESSION['phone']` = Teléfono del usuario
   - `$_SESSION['login_time']` = Timestamp del login
   - `$_SESSION['login_expires']` = Expira en 24 horas

### **Estado autenticado**
- Header muestra:
  - Info del usuario: `***1529` (últimos 4 dígitos)
  - Botón "+ Publícate" (lleva a publicar-oferta)
  - Botón "Salir" (cierra sesión)

### **Logout**
1. Usuario hace clic en "Salir"
2. Se ejecuta `logout.php`
3. Destruye todas las variables de sesión
4. Elimina cookie de sesión
5. Redirige al home con mensaje: "Sesión cerrada exitosamente"

## 📱 Visualización según estado

### **Usuario NO autenticado:**
```
[Logo Camella]                    [+ Publícate]
```

### **Usuario autenticado:**
```
[Logo Camella]    [👤 ***1529]  [+ Publícate]  [Salir]
```

## 🎨 Estilos aplicados

### **Información del usuario (.user-info)**
- Fondo semi-transparente blanco
- Icono de usuario (Font Awesome)
- Últimos 4 dígitos del teléfono enmascarado
- Border-radius redondeado

### **Botón de logout (.btn-logout)**
- Fondo transparente con borde blanco
- Efecto hover: fondo blanco, texto azul
- Animación de elevación al pasar el cursor
- Icono de "sign-out" (Font Awesome)

## 🔒 Seguridad implementada

1. ✅ Validación de sesión en cada página
2. ✅ Sesión expira en 24 horas automáticamente
3. ✅ Códigos SMS se eliminan después de usarse (no reutilizables)
4. ✅ Códigos SMS expiran en 5 minutos
5. ✅ Cookie de sesión se elimina completamente en logout
6. ✅ Todas las variables de sesión se destruyen

## 🧪 Pruebas

### **Para probar el sistema completo:**

1. **Login:**
   - Ir a: `https://localhost/camella.com.co/index.php?view=loginPhone`
   - Ingresar número: `3103951529`
   - Recibir SMS con código
   - Ingresar código de 6 dígitos
   - Verificar que aparezca la info del usuario y botón "Salir"

2. **Navegación autenticada:**
   - Verificar que el header muestra los datos correctos
   - Navegar por diferentes páginas
   - Confirmar que la sesión persiste

3. **Logout:**
   - Hacer clic en "Salir"
   - Verificar redirección al home
   - Verificar mensaje de confirmación
   - Verificar que el header vuelve a mostrar solo "+ Publícate"

4. **Intentar acceder después del logout:**
   - Confirmar que ya no hay sesión activa
   - Confirmar que se debe volver a hacer login

## 📊 Base de datos

### **Tabla: users**
```sql
id              INT (Primary Key)
phone           VARCHAR(20) UNIQUE
created_at      TIMESTAMP
last_login      TIMESTAMP
```

### **Tabla: verification_codes**
```sql
id              INT (Primary Key)
phone           VARCHAR(20)
code            VARCHAR(6)
magic_token     VARCHAR(64)
created_at      TIMESTAMP
expires_at      TIMESTAMP
```

## 🚀 Próximos pasos sugeridos

1. Agregar dashboard de usuario
2. Implementar perfil de usuario
3. Agregar cambio de número de teléfono
4. Implementar sistema de notificaciones
5. Agregar "Recordarme" con token persistente
6. Implementar middleware de autenticación para rutas protegidas

---

**Última actualización:** 2025-10-13  
**Versión:** 1.0.0  
**Estado:** ✅ Funcional en producción
