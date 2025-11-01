<?php
/**
 * Script para verificar el estado de los anuncios y su relación con oficios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h1>Estado de Anuncios y Relación con Oficios</h1>";
echo "<pre>";

try {
    $pdo = getPDO();
    
    // 1. Verificar total de anuncios
    echo "=== 1. Total de anuncios ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios");
    $total = $stmt->fetch();
    echo "Total anuncios: {$total['total']}\n\n";
    
    // 2. Anuncios por status
    echo "=== 2. Anuncios por status ===\n";
    $stmt = $pdo->query("SELECT status, COUNT(*) as total FROM anuncios GROUP BY status");
    while ($row = $stmt->fetch()) {
        echo "Status '{$row['status']}': {$row['total']}\n";
    }
    echo "\n";
    
    // 3. Anuncios con y sin oficio_id
    echo "=== 3. Anuncios con oficio_id ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE oficio_id IS NOT NULL");
    $conOficio = $stmt->fetch();
    echo "Con oficio_id: {$conOficio['total']}\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE oficio_id IS NULL");
    $sinOficio = $stmt->fetch();
    echo "Sin oficio_id (NULL): {$sinOficio['total']}\n\n";
    
    // 4. Anuncios activos con oficio_id
    echo "=== 4. Anuncios ACTIVOS con oficio_id ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE status = 'activo' AND oficio_id IS NOT NULL");
    $activosConOficio = $stmt->fetch();
    echo "Activos con oficio_id: {$activosConOficio['total']}\n\n";
    
    // 5. Listar todos los anuncios
    echo "=== 5. Listado de anuncios ===\n";
    $stmt = $pdo->query("SELECT id, titulo, status, oficio_id, user_id FROM anuncios LIMIT 10");
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($anuncios)) {
        foreach ($anuncios as $anuncio) {
            echo "ID: {$anuncio['id']} | Título: {$anuncio['titulo']} | Status: {$anuncio['status']} | Oficio ID: " . ($anuncio['oficio_id'] ?? 'NULL') . "\n";
        }
    } else {
        echo "No hay anuncios en la tabla\n";
    }
    echo "\n";
    
    // 6. Probar la consulta de conteo por categoría
    echo "=== 6. Consulta de conteo por categoría (TEST) ===\n";
    $sql = "
        SELECT 
            c.id,
            c.nombre,
            COALESCE(COUNT(o.id), 0) AS total_oficios,
            (SELECT COUNT(*) 
             FROM anuncios a 
             INNER JOIN oficios o2 ON a.oficio_id = o2.id 
             WHERE o2.categoria_id = c.id 
             AND a.status = 'activo') AS total_anuncios
        FROM categorias c
        LEFT JOIN oficios o 
            ON o.categoria_id = c.id 
           AND o.activo = 1
        WHERE c.activo = 1
        GROUP BY c.id, c.nombre
        ORDER BY total_anuncios DESC
        LIMIT 5
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Primeras 5 categorías con conteo:\n";
    foreach ($categorias as $cat) {
        echo "- {$cat['nombre']}: {$cat['total_oficios']} oficios, {$cat['total_anuncios']} anuncios\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
