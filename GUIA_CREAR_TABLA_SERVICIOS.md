# 📋 Guía para Crear la Tabla Servicios

## ✅ Opción 1: Usando phpMyAdmin (Recomendado)

### Pasos:

1. **Abrir phpMyAdmin**
   - URL: http://localhost/phpmyadmin
   - Usuario: root (sin contraseña por defecto en XAMPP)

2. **Seleccionar la base de datos**
   - Click en `camella_db` en el menú izquierdo

3. **Importar el archivo SQL**
   - Click en la pestaña "SQL" en la parte superior
   - Copia y pega el contenido del archivo `tests/create_servicios_table.sql`
   - O usa el botón "Importar" y selecciona el archivo
   - Click en "Continuar" o "Go"

4. **Verificar creación**
   - Verás el mensaje: "MySQL ha devuelto un resultado vacío"
   - En el menú izquierdo aparecerá la tabla `servicios`
   - Click en ella para ver los 3 registros de ejemplo

---

## ⚡ Opción 2: Por línea de comandos (Rápido)

### Desde PowerShell en la carpeta del proyecto:

```powershell
# Opción A: Usando mysql directamente
C:\xampp\mysql\bin\mysql.exe -u camella_user -pReylondres7373 camella_db < tests\create_servicios_table.sql

# Opción B: Si no funciona, prueba sin contraseña
C:\xampp\mysql\bin\mysql.exe -u root camella_db < tests\create_servicios_table.sql
```

### Desde CMD:

```cmd
cd C:\xampp\htdocs\camella.com.co
C:\xampp\mysql\bin\mysql.exe -u camella_user -pReylondres7373 camella_db < tests\create_servicios_table.sql
```

---

## 🐘 Opción 3: Usando PHP (Automático)

Puedes crear un script PHP temporal:

```php
<?php
// Archivo: tests/ejecutar_sql.php

$mysqli = new mysqli('localhost', 'camella_user', 'Reylondres7373', 'camella_db');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$sql = file_get_contents(__DIR__ . '/create_servicios_table.sql');

if ($mysqli->multi_query($sql)) {
    echo "✅ Tabla 'servicios' creada exitosamente con datos de ejemplo.\n";
    
    // Limpiar resultados
    while ($mysqli->next_result()) {;}
    
    // Verificar
    $result = $mysqli->query("SELECT COUNT(*) as total FROM servicios");
    $row = $result->fetch_assoc();
    echo "✅ Total de anuncios insertados: " . $row['total'] . "\n";
} else {
    echo "❌ Error: " . $mysqli->error;
}

$mysqli->close();
?>
```

Luego ejecutar:
```powershell
php tests\ejecutar_sql.php
```

---

## 🔍 Verificar que la tabla se creó correctamente

### Desde PowerShell:

```powershell
php -r "$pdo = new PDO('mysql:host=localhost;dbname=camella_db', 'camella_user', 'Reylondres7373'); $result = $pdo->query('SELECT * FROM servicios'); while($row = $result->fetch(PDO::FETCH_ASSOC)) { print_r($row); }"
```

### O usar el script de verificación:

```powershell
php tests\check_tables.php
```

---

## 📊 Qué hace el script SQL

1. **Crea la tabla `servicios`** con los siguientes campos:
   - id (PRIMARY KEY)
   - user_id (FK al usuario propietario)
   - titulo
   - descripcion
   - precio
   - imagen_principal
   - status (enum: 'activo', 'inactivo', 'pausado')
   - created_at
   - updated_at

2. **Inserta 3 anuncios de ejemplo** para el usuario ID 1:
   - Plomero profesional ($50,000)
   - Electricista certificado ($60,000)
   - Servicio de carpintería ($80,000)

---

## 🎯 Resultado Esperado

Después de ejecutar el SQL, deberías ver:

```
✅ Tabla creada: servicios
✅ Registros insertados: 3
✅ El bloque "Tus Anuncios Publicados" mostrará las 3 tarjetas
```

---

## ⚠️ Solución de Problemas

### Error: "Table already exists"
```sql
-- Primero eliminar la tabla si existe
DROP TABLE IF EXISTS servicios;
-- Luego ejecutar el script completo
```

### Error: "Access denied"
```sql
-- Verificar credenciales en config/config.php
-- Usuario: camella_user
-- Password: Reylondres7373
-- Base de datos: camella_db
```

### Error: "Unknown database"
```sql
-- Crear la base de datos primero
CREATE DATABASE IF NOT EXISTS camella_db DEFAULT CHARACTER SET utf8mb4;
```

---

## 📝 Comandos Útiles

### Ver estructura de la tabla:
```sql
DESCRIBE servicios;
```

### Ver datos insertados:
```sql
SELECT * FROM servicios;
```

### Contar registros:
```sql
SELECT COUNT(*) FROM servicios WHERE user_id = 1;
```

### Eliminar todos los datos (mantener estructura):
```sql
TRUNCATE TABLE servicios;
```

### Eliminar la tabla completamente:
```sql
DROP TABLE servicios;
```

---

¡Eso es todo! Elige la opción que te resulte más cómoda. 🚀
