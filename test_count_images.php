<?php
/**
 * Script de prueba para verificar el conteo de imágenes por anuncio
 */

require_once __DIR__ . '/config/database.php';

try {
    echo "<h2>Verificación de conteo de imágenes por anuncio</h2>";
    
    // Obtener todos los anuncios activos
    $stmt = $pdo->query("
        SELECT 
            a.id,
            a.titulo,
            (SELECT COUNT(*) FROM anuncio_imagenes ai WHERE ai.anuncio_id = a.id) as total_imagenes_subquery
        FROM anuncios a
        WHERE a.status = 'activo'
        ORDER BY a.created_at DESC
        LIMIT 20
    ");
    
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Anuncio</th><th>Título</th><th>Total Imágenes (Subquery)</th><th>Total Imágenes (COUNT directo)</th><th>Rutas de imágenes</th></tr>";
    
    foreach ($anuncios as $anuncio) {
        // Contar directamente con una consulta separada
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM anuncio_imagenes WHERE anuncio_id = ?");
        $stmtCount->execute([$anuncio['id']]);
        $countDirecto = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Obtener las rutas de las imágenes
        $stmtImages = $pdo->prepare("SELECT ruta FROM anuncio_imagenes WHERE anuncio_id = ? ORDER BY orden");
        $stmtImages->execute([$anuncio['id']]);
        $imagenes = $stmtImages->fetchAll(PDO::FETCH_COLUMN);
        
        $imagenesStr = implode(', ', array_map(function($img) {
            return basename($img);
        }, $imagenes));
        
        echo "<tr>";
        echo "<td>{$anuncio['id']}</td>";
        echo "<td>" . htmlspecialchars(substr($anuncio['titulo'], 0, 50)) . "...</td>";
        echo "<td style='text-align:center; font-weight:bold;'>{$anuncio['total_imagenes_subquery']}</td>";
        echo "<td style='text-align:center; font-weight:bold; color:blue;'>{$countDirecto}</td>";
        echo "<td><small>{$imagenesStr}</small></td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Estadísticas generales
    echo "<h3>Estadísticas:</h3>";
    
    $stmtStats = $pdo->query("
        SELECT 
            COUNT(DISTINCT a.id) as total_anuncios,
            COUNT(ai.id) as total_imagenes,
            ROUND(COUNT(ai.id) / COUNT(DISTINCT a.id), 2) as promedio_imagenes
        FROM anuncios a
        LEFT JOIN anuncio_imagenes ai ON a.anuncio_id = ai.anuncio_id
        WHERE a.status = 'activo'
    ");
    
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    echo "<li>Total anuncios activos: {$stats['total_anuncios']}</li>";
    echo "<li>Total imágenes: {$stats['total_imagenes']}</li>";
    echo "<li>Promedio de imágenes por anuncio: {$stats['promedio_imagenes']}</li>";
    echo "</ul>";
    
    // Verificar estructura de la tabla
    echo "<h3>Estructura de tabla anuncio_imagenes:</h3>";
    $stmtDesc = $pdo->query("DESCRIBE anuncio_imagenes");
    $columns = $stmtDesc->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
