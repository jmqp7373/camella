<?php
/**
 * CONFIGURACIÓN DE BASE DE DATOS PDO
 * 
 * Función centralizada para obtener conexión PDO
 * Compatible con hosting GoDaddy y desarrollo local
 * 
 * @version 2.0 - Magic Link System
 * @date 2025-10-11
 */

// Cargar configuración si no está cargada
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Obtener conexión PDO centralizada
 * 
 * @return PDO Instancia de conexión PDO
 * @throws PDOException Si falla la conexión
 */
function getPDO(): PDO {
    static $pdo = null;
    
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    
    // Las credenciales ya están validadas - proceder con conexión
    
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        throw new PDOException('Error conectando a BD: ' . $e->getMessage());
    }
}
?>