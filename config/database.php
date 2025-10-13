<?php
/**
 * Gestor de Conexión PDO - Sistema Reutilizable
 * Este archivo SÍ se versiona en Git
 */

// Cargar configuraciones si no están definidas
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Obtiene una instancia PDO singleton para la base de datos
 * @return PDO Instancia de conexión a la base de datos
 * @throws PDOException Si hay error en la conexión
 */
function getPDO(): PDO {
    static $pdo = null;

    // Si ya tenemos una conexión, la reutilizamos
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Construir DSN (Data Source Name) usando charset definido en config
    $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;

    // Usar opciones PDO definidas en config.php o fallback
    $options = defined('PDO_OPTIONS') ? PDO_OPTIONS : [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Log de conexión exitosa (solo en modo debug)
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("✅ Conexión PDO exitosa a: " . DB_NAME);
        }
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log del error
        error_log("❌ Error PDO: " . $e->getMessage());
        
        // Lanzar excepción con mensaje personalizado
        throw new PDOException('Error conectando a BD: ' . $e->getMessage());
    }
}

/**
 * Función auxiliar para ejecutar consultas preparadas
 * @param string $sql Consulta SQL
 * @param array $params Parámetros para la consulta
 * @return PDOStatement
 */
function executeQuery(string $sql, array $params = []): PDOStatement {
    $pdo = getPDO();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Función para obtener un solo registro
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array|false
 */
function fetchOne(string $sql, array $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Función para obtener múltiples registros
 * @param string $sql Consulta SQL
 * @param array $params Parámetros
 * @return array
 */
function fetchAll(string $sql, array $params = []): array {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}
?>