<?php
/**
 * Test simple: Verificar conteo de imágenes
 */

// Mostrar errores para debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

// Obtener conexión PDO
$pdo = getPDO();

echo "<h2>TEST: Conteo de Imágenes por Anuncio</h2>";
echo "<style>table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background: #003d7a; color: white; }</style>";

try {
    // 1. Verificar que existan anuncios
    $countAnuncios = $pdo->query("SELECT COUNT(*) FROM anuncios WHERE status = 'activo'")->fetchColumn();
    echo "<p><strong>Total anuncios activos:</strong> $countAnuncios</p>";
    
    // 2. Verificar que existan imágenes
    $countImagenes = $pdo->query("SELECT COUNT(*) FROM anuncio_imagenes")->fetchColumn();
    echo "<p><strong>Total imágenes en anuncio_imagenes:</strong> $countImagenes</p>";
    
    if ($countImagenes == 0) {
        echo "<p style='color: red; font-size: 20px;'><strong>⚠️ PROBLEMA ENCONTRADO: La tabla anuncio_imagenes está VACÍA!</strong></p>";
        echo "<p>Esto explica por qué el contador muestra 0. Necesitas cargar imágenes a los anuncios.</p>";
    }
    
    // 3. Consulta igual a la de categoria.php
    echo "<h3>Anuncios con su conteo de imágenes (query de categoria.php):</h3>";
    
    $stmt = $pdo->query("
        SELECT 
            a.id,
            a.titulo,
            a.precio,
            (SELECT COUNT(*) FROM anuncio_imagenes ai WHERE ai.anuncio_id = a.id) as total_imagenes
        FROM anuncios a
        WHERE a.status = 'activo'
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>ID</th><th>Título</th><th>Precio</th><th>Total Imágenes</th></tr>";
    
    foreach ($anuncios as $anuncio) {
        $color = $anuncio['total_imagenes'] == 0 ? 'style="background: #ffcccc;"' : '';
        echo "<tr $color>";
        echo "<td>{$anuncio['id']}</td>";
        echo "<td>" . htmlspecialchars($anuncio['titulo']) . "</td>";
        echo "<td>\${$anuncio['precio']}</td>";
        echo "<td style='text-align: center; font-weight: bold;'>{$anuncio['total_imagenes']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // 4. Mostrar qué anuncios SÍ tienen imágenes
    echo "<h3>Anuncios que SÍ tienen imágenes:</h3>";
    
    $stmtConImagenes = $pdo->query("
        SELECT 
            a.id,
            a.titulo,
            COUNT(ai.id) as num_imagenes,
            GROUP_CONCAT(ai.ruta SEPARATOR ', ') as rutas
        FROM anuncios a
        INNER JOIN anuncio_imagenes ai ON ai.anuncio_id = a.id
        WHERE a.status = 'activo'
        GROUP BY a.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    
    $anunciosConImagenes = $stmtConImagenes->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($anunciosConImagenes) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Título</th><th>Nº Imágenes</th><th>Rutas</th></tr>";
        
        foreach ($anunciosConImagenes as $anuncio) {
            echo "<tr>";
            echo "<td>{$anuncio['id']}</td>";
            echo "<td>" . htmlspecialchars($anuncio['titulo']) . "</td>";
            echo "<td style='text-align: center;'>{$anuncio['num_imagenes']}</td>";
            echo "<td><small>" . htmlspecialchars($anuncio['rutas']) . "</small></td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p style='color: red;'><strong>⚠️ NO hay ningún anuncio con imágenes asociadas</strong></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
