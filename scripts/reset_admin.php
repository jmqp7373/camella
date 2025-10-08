<?php
/**
 * /scripts/reset_admin.php
 *
 * PROPÓSITO:
 * - Crear o resetear de forma segura el usuario administrador del sistema.
 * - Usuario objetivo: admin@camella.com.co (rol: 'admin').
 *
 * SEGURIDAD / USO:
 * - Requiere un token por GET: ?token=XXXXXXXX  (cámbialo abajo en TOKEN_SECRETO).
 * - Debe ejecutarse una sola vez y luego ELIMINARSE del servidor.
 * - No muestra información sensible; solo resultados controlados.
 *
 * EFECTOS:
 * - Si el usuario existe, actualiza su contraseña (bcrypt).
 * - Si no existe, lo crea con rol 'admin'.
 *
 * CÓMO REVERTIR:
 * - El cambio solo afecta al usuario admin@camella.com.co.
 * - Se puede volver a correr con otro password si aún no has borrado el script.
 *
 * NOTA PARA DEVS NOVATOS:
 * - Cambia el valor de TOKEN_SECRETO antes de usar.
 * - Para usar otro email admin, modifica la variable $emailAdmin.
 * - Para reutilizar: solo cambia TOKEN_SECRETO y vuelve a ejecutar.
 * - El script detecta automáticamente la configuración de BD del proyecto.
 */

declare(strict_types=1);

// =======================
// 1) Protección por token
// =======================
// LÍNEA CLAVE: Cambiar este valor por un token único largo y aleatorio
const TOKEN_SECRETO = 'cambia_esto_por_un_token_unico_largo_aleatorio'; // <-- CAMBIA ESTO

if (!isset($_GET['token']) || !hash_equals(TOKEN_SECRETO, (string)$_GET['token'])) {
    http_response_code(403);
    exit('Acceso denegado.');
}

// =====================================
// 2) Generación de contraseña temporal
// =====================================
/**
 * Bloque de generación de contraseña fuerte
 * 
 * Opciones disponibles:
 * A) Contraseña aleatoria (por defecto) - se genera y muestra una vez
 * B) Contraseña fija (descomentar línea abajo para control manual)
 * 
 * Para contraseña fija, usar:
 * $passwordPlano = 'Admin-' . date('Y') . '-Camella!';
 */
function generarPasswordFuerte(int $bytes = 12): string {
    // LÍNEA CLAVE: 12 bytes -> ~16 chars base64url. Ajusta si quieres más largo.
    $raw = random_bytes($bytes);
    // base64url seguro (sin +/ y sin =)
    $b64 = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    // Añadir variedad de clases de caracteres
    return 'A1!' . $b64; // prefijo para garantizar mayúscula, dígito y símbolo
}

$passwordPlano = generarPasswordFuerte(14); // Contraseña aleatoria fuerte
$passwordHash  = password_hash($passwordPlano, PASSWORD_BCRYPT); // Hash bcrypt seguro

// ==============================================================
// 3) Cargar bootstrap/config y obtener PDO del propio proyecto
// ==============================================================
/**
 * Bloque de conexión a base de datos
 * 
 * Estrategia de conexión en cascada:
 * 1. Intentar vía bootstrap.php (conexión del proyecto)
 * 2. Cargar config/config.php directamente si bootstrap falla
 * 3. Error controlado si ninguna opción funciona
 */
$pdo = null;

// a) Intento vía bootstrap (si establece una conexión global o helper)
$bootstrap = __DIR__ . '/../bootstrap.php';
if (file_exists($bootstrap)) {
    require_once $bootstrap;
    // Si tu proyecto expone $pdo o $db, recupéralo:
    if (isset($pdo) && $pdo instanceof PDO) {
        // ok - conexión encontrada vía bootstrap
    } elseif (isset($db) && $db instanceof PDO) {
        $pdo = $db; // usar variable $db si existe
    }
}

// b) Intento directo al config si aún no hay PDO
if (!$pdo) {
    $config = __DIR__ . '/../config/config.php';
    if (file_exists($config)) {
        require_once $config;
        
        // LÍNEA CLAVE: Intentar variables comunes definidas en config
        // Ajustar estos nombres a los que use tu proyecto específico
        $host = $host ?? ($DB_HOST ?? null) ?? 'localhost';
        $dbname = $basedatos ?? ($dbname ?? ($DB_NAME ?? null)) ?? null;
        $username = $usuario ?? ($username ?? ($DB_USER ?? null)) ?? null;
        $password = $contrasena ?? ($password ?? ($DB_PASS ?? null)) ?? null;
        $charset = $charset ?? 'utf8mb4';

        if ($dbname && $username !== null && $password !== null) {
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            
            // LÍNEA CLAVE: Crear PDO con configuración segura
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Seguridad adicional
            ]);
        }
    }
}

// c) Falla controlada si no hay PDO
if (!$pdo) {
    http_response_code(500);
    exit('No se pudo obtener conexión PDO desde la configuración del proyecto.');
}

// =======================================================
// 4) Upsert del usuario admin (crear o actualizar password)
// =======================================================
/**
 * Bloque de operación UPSERT (UPDATE or INSERT)
 * 
 * Notas para devs novatos:
 * - Asumimos tabla `usuarios` con columnas: id, email, password, rol, nombre.
 * - Si tu esquema difiere, ajusta los nombres de columnas abajo.
 * - Todas las consultas son preparadas para evitar SQL injection.
 * - Se usa transacción para garantizar consistencia.
 */

// Variables de configuración del admin
$emailAdmin = 'admin@camella.com.co'; // <-- Cambiar si necesitas otro email
$rolAdmin   = 'admin';
$nombreAdmin = 'Administrador';

// LÍNEA CLAVE: Iniciar transacción para operación atómica
$pdo->beginTransaction();

try {
    // Verificar si el usuario admin ya existe
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$emailAdmin]);
    $row = $stmt->fetch();

    if ($row) {
        // LÍNEA CLAVE: Usuario existe - actualizar contraseña y rol
        $stmt = $pdo->prepare('UPDATE usuarios SET password = ?, rol = ? WHERE id = ?');
        $stmt->execute([$passwordHash, $rolAdmin, $row['id']]);
        $accion = 'actualizado';
        $adminId = (int)$row['id'];
    } else {
        // LÍNEA CLAVE: Usuario no existe - crear nuevo admin
        $stmt = $pdo->prepare('
            INSERT INTO usuarios (email, password, rol, nombre, fecha_registro)
            VALUES (?, ?, ?, ?, NOW())
        ');
        $stmt->execute([$emailAdmin, $passwordHash, $rolAdmin, $nombreAdmin]);
        $accion = 'creado';
        $adminId = (int)$pdo->lastInsertId();
    }

    // LÍNEA CLAVE: Confirmar transacción
    $pdo->commit();
    
} catch (Throwable $e) {
    // LÍNEA CLAVE: Revertir transacción en caso de error
    $pdo->rollBack();
    error_log('[reset_admin] Error: ' . $e->getMessage());
    http_response_code(500);
    exit('Ocurrió un error al intentar crear/actualizar el usuario admin.');
}

// ===================================================
// 5) (Opcional) Auto-borrado del script tras ejecutarse
// ===================================================
/**
 * Bloque de auto-eliminación del script
 * 
 * Descomenta la línea siguiente si quieres que el script se elimine
 * automáticamente después de ejecutarse con éxito.
 * 
 * RECOMENDACIÓN: Mantenerlo comentado durante pruebas, activar en producción.
 */
// LÍNEA CLAVE: Auto-eliminación del script (descomenta para activar)
// @unlink(__FILE__);

// ===============================================
// 6) Salida controlada (no exponer información)
// ===============================================
/**
 * Bloque de respuesta final
 * 
 * Importante: mostrar la nueva contraseña SOLO UNA VEZ en pantalla.
 * El usuario debe capturarla y guardarla inmediatamente en un lugar seguro.
 * No se almacena en logs ni se envía por email por seguridad.
 */
header('Content-Type: text/plain; charset=utf-8');

// Salida estructurada y clara
echo "=== RESET ADMIN CAMELLA.COM.CO ===\n\n";
echo "✓ Usuario admin {$accion} con éxito.\n\n";
echo "DATOS DE ACCESO:\n";
echo "Email: {$emailAdmin}\n";
echo "Nueva contraseña: {$passwordPlano}\n";
echo "Rol: {$rolAdmin}\n";
echo "Admin ID: {$adminId}\n\n";
echo "INSTRUCCIONES IMPORTANTES:\n";
echo "1. GUARDA la contraseña AHORA en un lugar seguro.\n";
echo "2. Esta contraseña NO se volverá a mostrar.\n";
echo "3. ELIMINA este archivo (/scripts/reset_admin.php) inmediatamente.\n";
echo "4. Accede al panel en: https://camella.com.co/index.php?view=login\n\n";
echo "NOTAS DE SEGURIDAD:\n";
echo "- La contraseña está hasheada con bcrypt en la base de datos.\n";
echo "- Este script debe ejecutarse solo UNA VEZ.\n";
echo "- Elimina el archivo del servidor tras usar.\n\n";
echo "Timestamp: " . date('Y-m-d H:i:s T') . "\n";
echo "=== FIN RESET ADMIN ===\n";

/**
 * NOTAS PARA MANTENIMIENTO FUTURO:
 * 
 * REUTILIZACIÓN:
 * - Para usar nuevamente: cambiar TOKEN_SECRETO y volver a ejecutar
 * - Para otro email: cambiar variable $emailAdmin
 * - Para personalizar tabla: ajustar queries en bloque UPSERT
 * 
 * SEGURIDAD:
 * - Nunca dejar este archivo en producción tras usar
 * - Token debe ser único por cada uso
 * - Contraseña se muestra solo una vez y no se loguea
 * 
 * TROUBLESHOOTING:
 * - Si falla la conexión: verificar config/config.php o bootstrap.php
 * - Si falla el insert: verificar estructura de tabla usuarios
 * - Si error de permisos: verificar que el directorio scripts/ sea escribible
 */
?>