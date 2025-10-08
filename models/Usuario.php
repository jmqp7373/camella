<?php
/**
 * Usuario.php - Modelo de Usuario para Autenticación
 * 
 * Propósito:
 * Maneja todas las operaciones relacionadas con usuarios en la base de datos,
 * incluyendo autenticación, registro y gestión de perfiles de usuario.
 * 
 * Responsabilidades:
 * - Validación de credenciales de login
 * - Hash seguro de contraseñas
 * - Creación y actualización de usuarios
 * - Consultas de información de usuario
 * 
 * Estructura de tabla esperada (usuarios):
 * - id: INT AUTO_INCREMENT PRIMARY KEY
 * - nombre: VARCHAR(100) NOT NULL
 * - email: VARCHAR(255) UNIQUE NOT NULL
 * - password: VARCHAR(255) NOT NULL (hash bcrypt)
 * - rol: ENUM('admin', 'promotor', 'publicante') DEFAULT 'publicante'
 * - activo: BOOLEAN DEFAULT TRUE
 * - fecha_creacion: TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * - fecha_actualizacion: TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-07
 */

require_once 'config/config.php';

class Usuario {
    private $conexion;
    
    /**
     * Constructor de la clase Usuario
     * 
     * Propósito: Inicializar la conexión a base de datos y crear
     * la tabla usuarios si no existe.
     * 
     * Decisiones de diseño:
     * - Auto-inicialización de tabla para facilitar deployment
     * - Uso de MySQLi por consistencia con el resto del sistema
     */
    public function __construct() {
        $this->conexion = conectarBD();
        $this->crearTablaUsuarios();
    }
    
    /**
     * Crear tabla de usuarios si no existe
     * 
     * Propósito: Garantizar que la estructura de base de datos esté disponible
     * para el sistema de autenticación, con campos optimizados para seguridad.
     * 
     * Características de la tabla:
     * - Email único para prevenir duplicados
     * - Password con longitud para hash bcrypt (255 chars)
     * - Rol con valores controlados mediante ENUM
     * - Campos de auditoría (fecha_creacion, fecha_actualizacion)
     * - Campo activo para soft delete
     * 
     * Notas de mantenimiento:
     * - Si se agregan nuevos roles, actualizar el ENUM
     * - Considerar índices en email y rol para queries frecuentes
     * 
     * @return bool True si la tabla se creó/existe correctamente
     */
    private function crearTablaUsuarios() {
        try {
            $sql = "
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(100) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    rol ENUM('admin', 'promotor', 'publicante') DEFAULT 'publicante',
                    activo BOOLEAN DEFAULT TRUE,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    INDEX idx_email (email),
                    INDEX idx_rol (rol),
                    INDEX idx_activo (activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            
            $resultado = $this->conexion->query($sql);
            
            if ($resultado) {
                // Crear usuario administrador por defecto si no existe
                $this->crearUsuarioAdminPorDefecto();
                return true;
            } else {
                error_log("Error creando tabla usuarios: " . $this->conexion->error);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Exception creando tabla usuarios: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear usuario administrador por defecto
     * 
     * Propósito: Garantizar que siempre exista al menos un usuario administrador
     * para poder acceder al sistema recién instalado.
     * 
     * Credenciales por defecto:
     * - Email: admin@camella.com.co
     * - Password: admin123 (debe cambiarse en producción)
     * - Rol: admin
     * 
     * Notas de seguridad:
     * - Estas credenciales deben cambiarse inmediatamente en producción
     * - Considerar generar password aleatorio y mostrarlo en logs iniciales
     * 
     * @return bool True si se creó o ya existía el usuario admin
     */
    private function crearUsuarioAdminPorDefecto() {
        try {
            // Verificar si ya existe un usuario admin
            $checkSql = "SELECT id FROM usuarios WHERE rol = 'admin' LIMIT 1";
            $result = $this->conexion->query($checkSql);
            
            if ($result && $result->num_rows > 0) {
                // Ya existe al menos un admin, no crear otro
                return true;
            }
            
            // Crear usuario admin por defecto
            $email = 'admin@camella.com.co';
            $password = 'admin123'; // TODO: Cambiar en producción
            $nombre = 'Administrador';
            $rol = 'admin';
            
            return $this->crearUsuario($nombre, $email, $password, $rol);
            
        } catch (Exception $e) {
            error_log("Error creando usuario admin por defecto: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar credenciales de usuario para login
     * 
     * Propósito: Verificar que las credenciales proporcionadas corresponden
     * a un usuario válido y activo en el sistema.
     * 
     * Flujo de validación:
     * 1. Buscar usuario por email
     * 2. Verificar que está activo
     * 3. Validar password con hash bcrypt
     * 4. Retornar datos del usuario si todo es correcto
     * 
     * Consideraciones de seguridad:
     * - Uso de password_verify() para validación segura de hash
     * - No revelar información específica sobre qué falló (email vs password)
     * - Verificación de estado activo para permitir desactivación de usuarios
     * 
     * @param string $email Email del usuario
     * @param string $password Password en texto plano
     * @return array|false Array con datos del usuario si login exitoso, false si falla
     */
    public function validarCredenciales($email, $password) {
        try {
            // Sanitizar input del email
            $email = trim(strtolower($email));
            
            if (empty($email) || empty($password)) {
                return false;
            }
            
            // Buscar usuario por email
            $sql = "SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? AND activo = 1";
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                error_log("Error preparando query de validación: " . $this->conexion->error);
                return false;
            }
            
            $stmt->bind_param("s", $email);
            $resultado = $stmt->execute();
            
            if (!$resultado) {
                error_log("Error ejecutando query de validación: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();
            
            // Verificar si se encontró el usuario
            if (!$usuario) {
                return false; // Usuario no encontrado o inactivo
            }
            
            // Validar password con hash
            if (password_verify($password, $usuario['password'])) {
                // Login exitoso - remover password del array de retorno por seguridad
                unset($usuario['password']);
                return $usuario;
            } else {
                return false; // Password incorrecto
            }
            
        } catch (Exception $e) {
            error_log("Exception en validarCredenciales: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear nuevo usuario en el sistema
     * 
     * Propósito: Registrar un nuevo usuario con validaciones de integridad
     * y seguridad en el manejo de contraseñas.
     * 
     * Validaciones implementadas:
     * - Email único en el sistema
     * - Formato válido de email
     * - Longitud mínima de contraseña
     * - Rol válido según ENUM definido
     * 
     * Proceso de creación:
     * 1. Validar datos de entrada
     * 2. Verificar que email no exista
     * 3. Hashear contraseña con bcrypt
     * 4. Insertar en base de datos
     * 5. Retornar ID del nuevo usuario
     * 
     * @param string $nombre Nombre completo del usuario
     * @param string $email Email único del usuario
     * @param string $password Contraseña en texto plano (se hasheará)
     * @param string $rol Rol del usuario (admin|promotor|publicante)
     * @return int|false ID del usuario creado o false si falla
     */
    public function crearUsuario($nombre, $email, $password, $rol = 'publicante') {
        try {
            // Validaciones de entrada
            $nombre = trim($nombre);
            $email = trim(strtolower($email));
            $rol = trim($rol);
            
            if (empty($nombre) || empty($email) || empty($password)) {
                error_log("Datos incompletos para crear usuario");
                return false;
            }
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("Formato de email inválido: $email");
                return false;
            }
            
            // Validar longitud mínima de contraseña
            if (strlen($password) < 6) {
                error_log("Contraseña muy corta para usuario: $email");
                return false;
            }
            
            // Validar que el rol sea válido
            $rolesValidos = ['admin', 'promotor', 'publicante'];
            if (!in_array($rol, $rolesValidos)) {
                error_log("Rol inválido: $rol");
                return false;
            }
            
            // Verificar que el email no exista
            $checkSql = "SELECT id FROM usuarios WHERE email = ?";
            $checkStmt = $this->conexion->prepare($checkSql);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                error_log("Email ya existe: $email");
                $checkStmt->close();
                return false;
            }
            $checkStmt->close();
            
            // Hashear contraseña de forma segura
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = $this->conexion->prepare($sql);
            
            if (!$stmt) {
                error_log("Error preparando insert de usuario: " . $this->conexion->error);
                return false;
            }
            
            $stmt->bind_param("ssss", $nombre, $email, $passwordHash, $rol);
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $userId = $this->conexion->insert_id;
                error_log("Usuario creado exitosamente: ID=$userId, Email=$email, Rol=$rol");
                $stmt->close();
                return $userId;
            } else {
                error_log("Error ejecutando insert de usuario: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Exception en crearUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener información de usuario por ID
     * 
     * @param int $userId ID del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorId($userId) {
        try {
            $sql = "SELECT id, nombre, email, rol, activo, fecha_creacion FROM usuarios WHERE id = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();
            
            return $usuario ?: false;
            
        } catch (Exception $e) {
            error_log("Error obteniendo usuario por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destructor de la clase
     * 
     * Propósito: Cerrar la conexión a base de datos de forma limpia
     * cuando el objeto Usuario ya no se necesite.
     */
    public function __destruct() {
        if ($this->conexion) {
            cerrarBD($this->conexion);
        }
    }
}

?>