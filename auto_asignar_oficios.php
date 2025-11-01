<?php
/**
 * Script RÁPIDO para asignar oficio_id automáticamente según palabras clave
 * TEMPORAL - ejecutar una sola vez
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h1>Asignación Automática de Oficios</h1>";
echo "<pre>";

try {
    $pdo = getPDO();
    
    echo "1. Obteniendo anuncios sin oficio_id...\n";
    $stmt = $pdo->query("SELECT id, titulo FROM anuncios WHERE oficio_id IS NULL");
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Encontrados: " . count($anuncios) . "\n\n";
    
    if (empty($anuncios)) {
        echo "✅ Todos los anuncios ya tienen oficio_id asignado\n";
        echo "</pre>";
        echo "<p><a href='index.php'>Volver al inicio</a></p>";
        exit;
    }
    
    echo "2. Buscando oficios para asignación inteligente...\n\n";
    
    // Mapeo de palabras clave a oficio_id
    $asignaciones = [
        'electricista' => null,
        'plomero' => null,
        'carpintero' => null,
        'administra' => null,
        'lider' => null,
        'mensajero' => null
    ];
    
    // Obtener IDs de oficios según título
    $stmt = $pdo->query("SELECT id, titulo FROM oficios WHERE activo = 1");
    $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($oficios as $oficio) {
        $titulo = strtolower($oficio['titulo']);
        if (strpos($titulo, 'electricista') !== false) {
            $asignaciones['electricista'] = $oficio['id'];
        } elseif (strpos($titulo, 'plomero') !== false || strpos($titulo, 'plomería') !== false) {
            $asignaciones['plomero'] = $oficio['id'];
        } elseif (strpos($titulo, 'carpintero') !== false || strpos($titulo, 'carpintería') !== false) {
            $asignaciones['carpintero'] = $oficio['id'];
        } elseif (strpos($titulo, 'mensajero') !== false) {
            $asignaciones['mensajero'] = $oficio['id'];
        } elseif (strpos($titulo, 'administra') !== false || strpos($titulo, 'asistente') !== false) {
            $asignaciones['administra'] = $oficio['id'];
        }
    }
    
    echo "3. Asignaciones detectadas:\n";
    foreach ($asignaciones as $key => $id) {
        echo "   $key => " . ($id ?? 'No encontrado') . "\n";
    }
    echo "\n";
    
    // Obtener un oficio genérico como fallback
    $stmt = $pdo->query("SELECT id FROM oficios WHERE activo = 1 LIMIT 1");
    $oficioGenerico = $stmt->fetch();
    $fallbackId = $oficioGenerico['id'];
    
    echo "4. Asignando oficios a anuncios...\n";
    $pdo->beginTransaction();
    
    $actualizados = 0;
    foreach ($anuncios as $anuncio) {
        $titulo = strtolower($anuncio['titulo']);
        $oficioId = $fallbackId; // Por defecto
        
        // Buscar coincidencia
        if (strpos($titulo, 'electricista') !== false && $asignaciones['electricista']) {
            $oficioId = $asignaciones['electricista'];
        } elseif (strpos($titulo, 'plomero') !== false && $asignaciones['plomero']) {
            $oficioId = $asignaciones['plomero'];
        } elseif (strpos($titulo, 'carpintero') !== false && $asignaciones['carpintero']) {
            $oficioId = $asignaciones['carpintero'];
        } elseif ((strpos($titulo, 'lider') !== false || strpos($titulo, 'administra') !== false) && $asignaciones['administra']) {
            $oficioId = $asignaciones['administra'];
        } elseif (strpos($titulo, 'mensajero') !== false && $asignaciones['mensajero']) {
            $oficioId = $asignaciones['mensajero'];
        }
        
        $stmt = $pdo->prepare("UPDATE anuncios SET oficio_id = ? WHERE id = ?");
        $stmt->execute([$oficioId, $anuncio['id']]);
        
        echo "   ✅ Anuncio #{$anuncio['id']} '{$anuncio['titulo']}' -> Oficio ID: $oficioId\n";
        $actualizados++;
    }
    
    $pdo->commit();
    
    echo "\n5. RESULTADO:\n";
    echo "   ✅ Se actualizaron $actualizados anuncios exitosamente\n\n";
    
    // Verificar el resultado
    echo "6. Verificación final:\n";
    $stmt = $pdo->query("
        SELECT 
            c.nombre,
            COUNT(a.id) as total_anuncios
        FROM categorias c
        LEFT JOIN oficios o ON o.categoria_id = c.id
        LEFT JOIN anuncios a ON a.oficio_id = o.id AND a.status = 'activo'
        WHERE c.activo = 1
        GROUP BY c.id, c.nombre
        HAVING total_anuncios > 0
        ORDER BY total_anuncios DESC
    ");
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($resultado)) {
        echo "   Categorías con anuncios:\n";
        foreach ($resultado as $row) {
            echo "   - {$row['nombre']}: {$row['total_anuncios']} anuncios\n";
        }
    }
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><strong>¿Qué hacer ahora?</strong></p>";
echo "<ul>";
echo "<li><a href='test_anuncios_estado.php'>Ver estado de anuncios</a></li>";
echo "<li><a href='index.php'>Volver al inicio</a></li>";
echo "</ul>";
?>
