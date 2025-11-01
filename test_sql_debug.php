<?php
/**
 * Script de depuración SQL para diagnosticar problema con categorías
 * TEMPORAL - Eliminar después de resolver
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico SQL - Categorías</h1>";
echo "<pre>";

// Cargar conexión
require_once __DIR__ . '/config/database.php';
$pdo = getPDO();

// Test 1: Consulta simple de categorías
echo "=== TEST 1: Consulta simple de categorías ===\n";
try {
    $sql1 = "SELECT id, nombre, descripcion, icono, activo FROM categorias WHERE activo = 1";
    $stmt = $pdo->query($sql1);
    $result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Resultado: " . count($result1) . " categorías encontradas\n";
    if (!empty($result1)) {
        echo "Primera categoría: ID={$result1[0]['id']}, Nombre={$result1[0]['nombre']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Verificar si existe tabla oficios
echo "=== TEST 2: Verificar tabla oficios ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'oficios'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "Tabla oficios: ✅ EXISTE\n";
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM oficios WHERE activo = 1");
        $count = $stmt->fetch();
        echo "Oficios activos: " . $count['total'] . "\n";
    } else {
        echo "Tabla oficios: ❌ NO EXISTE\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Verificar si existe tabla anuncios
echo "=== TEST 3: Verificar tabla anuncios ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'anuncios'");
    $exists = $stmt->fetch();
    if ($exists) {
        echo "Tabla anuncios: ✅ EXISTE\n";
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM anuncios WHERE status = 'activo'");
        $count = $stmt->fetch();
        echo "Anuncios activos: " . $count['total'] . "\n";
    } else {
        echo "Tabla anuncios: ❌ NO EXISTE\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Consulta sin subquery (más simple)
echo "=== TEST 4: Consulta sin subquery ===\n";
try {
    $sql2 = "
        SELECT 
            c.id,
            c.nombre,
            c.descripcion,
            c.icono,
            c.activo,
            COALESCE(COUNT(o.id), 0) AS total_oficios
        FROM categorias c
        LEFT JOIN oficios o 
            ON o.categoria_id = c.id 
           AND o.activo = 1
        WHERE c.activo = 1
        GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
        ORDER BY c.nombre ASC
    ";
    $stmt = $pdo->prepare($sql2);
    $stmt->execute();
    $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Resultado: " . count($result2) . " categorías encontradas\n";
    if (!empty($result2)) {
        echo "Primera categoría: ID={$result2[0]['id']}, Nombre={$result2[0]['nombre']}, Oficios={$result2[0]['total_oficios']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "SQL: " . $sql2 . "\n";
}
echo "\n";

// Test 5: Consulta completa (la original)
echo "=== TEST 5: Consulta completa con subquery ===\n";
try {
    $sql3 = "
        SELECT 
            c.id,
            c.nombre,
            c.descripcion,
            c.icono,
            c.activo,
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
        GROUP BY c.id, c.nombre, c.descripcion, c.icono, c.activo
        ORDER BY total_anuncios DESC, c.nombre ASC
    ";
    $stmt = $pdo->prepare($sql3);
    $stmt->execute();
    $result3 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Resultado: " . count($result3) . " categorías encontradas\n";
    if (!empty($result3)) {
        echo "Primera categoría: ID={$result3[0]['id']}, Nombre={$result3[0]['nombre']}, Oficios={$result3[0]['total_oficios']}, Anuncios={$result3[0]['total_anuncios']}\n";
    } else {
        echo "⚠️ La consulta no devolvió resultados\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "SQL: " . $sql3 . "\n";
}
echo "\n";

// Test 6: Verificar modo SQL
echo "=== TEST 6: Verificar configuración MySQL ===\n";
try {
    $stmt = $pdo->query("SELECT @@sql_mode AS sql_mode");
    $mode = $stmt->fetch();
    echo "SQL Mode: " . $mode['sql_mode'] . "\n";
    
    // Verificar si ONLY_FULL_GROUP_BY está activo
    if (strpos($mode['sql_mode'], 'ONLY_FULL_GROUP_BY') !== false) {
        echo "⚠️ ONLY_FULL_GROUP_BY está activo - puede causar problemas con GROUP BY\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<hr>";
echo "<p><a href='test_categorias_debug.php'>Volver al test principal</a> | ";
echo "<a href='index.php'>Ir al inicio</a></p>";
?>
