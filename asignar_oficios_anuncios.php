<?php
/**
 * Script para asignar oficio_id a anuncios existentes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$mensaje = '';
$error = '';

// Procesar asignación si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignaciones'])) {
    try {
        $pdo = getPDO();
        $pdo->beginTransaction();
        
        $actualizados = 0;
        foreach ($_POST['asignaciones'] as $anuncioId => $oficioId) {
            if (!empty($oficioId) && $oficioId !== 'null') {
                $stmt = $pdo->prepare("UPDATE anuncios SET oficio_id = ? WHERE id = ?");
                $stmt->execute([$oficioId, $anuncioId]);
                $actualizados++;
            }
        }
        
        $pdo->commit();
        $mensaje = "✅ Se actualizaron $actualizados anuncios exitosamente";
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $error = "❌ Error: " . $e->getMessage();
    }
}

// Obtener datos
try {
    $pdo = getPDO();
    
    // Obtener anuncios sin oficio_id
    $stmt = $pdo->query("
        SELECT id, titulo, descripcion, status 
        FROM anuncios 
        WHERE oficio_id IS NULL 
        ORDER BY id
    ");
    $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener todas las categorías y sus oficios
    $stmt = $pdo->query("
        SELECT 
            c.id as categoria_id,
            c.nombre as categoria_nombre,
            o.id as oficio_id,
            o.titulo as oficio_titulo
        FROM categorias c
        INNER JOIN oficios o ON o.categoria_id = c.id
        WHERE c.activo = 1 AND o.activo = 1
        ORDER BY c.nombre, o.titulo
    ");
    $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar oficios por categoría
    $oficiosPorCategoria = [];
    foreach ($oficios as $oficio) {
        $catId = $oficio['categoria_id'];
        if (!isset($oficiosPorCategoria[$catId])) {
            $oficiosPorCategoria[$catId] = [
                'nombre' => $oficio['categoria_nombre'],
                'oficios' => []
            ];
        }
        $oficiosPorCategoria[$catId]['oficios'][] = [
            'id' => $oficio['oficio_id'],
            'titulo' => $oficio['oficio_titulo']
        ];
    }
    
} catch (Exception $e) {
    $error = "Error cargando datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Oficios a Anuncios</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .mensaje { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .mensaje.exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .mensaje.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .anuncio-card { background: #f8f9fa; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
        .anuncio-card h3 { color: #007bff; margin-bottom: 10px; }
        .anuncio-card p { color: #666; margin-bottom: 15px; font-size: 14px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; }
        select { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 14px; }
        select:focus { outline: none; border-color: #007bff; }
        .btn { display: inline-block; padding: 12px 30px; margin: 10px 5px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        .actions { margin-top: 30px; text-align: center; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Asignar Oficios a Anuncios</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="info">
            <strong>ℹ️ Instrucciones:</strong><br>
            Selecciona el oficio correcto para cada anuncio. Esto permitirá que las categorías muestren el conteo correcto de anuncios.
        </div>
        
        <?php if (!empty($anuncios)): ?>
            <form method="POST" action="">
                <?php foreach ($anuncios as $anuncio): ?>
                    <div class="anuncio-card">
                        <h3>📢 <?= htmlspecialchars($anuncio['titulo']) ?></h3>
                        <p><?= htmlspecialchars(substr($anuncio['descripcion'] ?? 'Sin descripción', 0, 150)) ?>...</p>
                        
                        <label for="oficio_<?= $anuncio['id'] ?>">Seleccionar Oficio:</label>
                        <select name="asignaciones[<?= $anuncio['id'] ?>]" id="oficio_<?= $anuncio['id'] ?>" required>
                            <option value="">-- Selecciona un oficio --</option>
                            <?php foreach ($oficiosPorCategoria as $categoria): ?>
                                <optgroup label="<?= htmlspecialchars($categoria['nombre']) ?>">
                                    <?php foreach ($categoria['oficios'] as $oficio): ?>
                                        <option value="<?= $oficio['id'] ?>">
                                            <?= htmlspecialchars($oficio['titulo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
                
                <div class="actions">
                    <button type="submit" class="btn btn-primary">✅ Asignar Oficios</button>
                    <a href="index.php" class="btn btn-secondary">← Volver al Inicio</a>
                </div>
            </form>
        <?php else: ?>
            <div class="mensaje exito">
                ✅ Todos los anuncios ya tienen un oficio asignado.
            </div>
            <div class="actions">
                <a href="index.php" class="btn btn-primary">← Volver al Inicio</a>
                <a href="test_anuncios_estado.php" class="btn btn-secondary">Ver Estado</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
