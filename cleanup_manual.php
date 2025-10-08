<?php
/**
 * Script de limpieza manual para producción
 * Elimina archivos de prueba que no deben estar en producción
 * 
 * Uso: Acceder a este archivo vía web una sola vez después del deploy
 * Se auto-elimina después de ejecutarse
 */

// Solo ejecutar si estamos en producción (verificar dominio)
$isProduction = (
    isset($_SERVER['HTTP_HOST']) && 
    (strpos($_SERVER['HTTP_HOST'], 'camella.com.co') !== false || 
     strpos($_SERVER['HTTP_HOST'], 'www.camella.com.co') !== false)
);

if (!$isProduction) {
    die('Este script solo se ejecuta en producción.');
}

// Lista de archivos de prueba a eliminar
$testFiles = [
    'database_test.php',
    'test_db.php',
    'phpinfo.php',
    'info.php',
    'test.php',
    'debug.php'
];

$removed = [];
$notFound = [];

foreach ($testFiles as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            $removed[] = $file;
        }
    } else {
        $notFound[] = $file;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpieza de Producción - Camella</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #002b47 0%, #004d7a 100%);
            color: white;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 600px;
            text-align: center;
        }
        h1 { color: #FFD200; margin-bottom: 20px; }
        .success { color: #4CAF50; }
        .info { color: #2196F3; }
        .list { text-align: left; margin: 20px 0; }
        .list li { padding: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpieza de Producción Completada</h1>
        
        <?php if (count($removed) > 0): ?>
            <div class="success">
                <h3>✅ Archivos eliminados exitosamente:</h3>
                <ul class="list">
                    <?php foreach ($removed as $file): ?>
                        <li>🗑️ <?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (count($notFound) > 0): ?>
            <div class="info">
                <h3>ℹ️ Archivos no encontrados (ya eliminados):</h3>
                <ul class="list">
                    <?php foreach ($notFound as $file): ?>
                        <li>📄 <?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <p>La limpieza de producción se ha completado correctamente.</p>
        <p><strong>Este script se auto-eliminará en 3 segundos...</strong></p>
        
        <script>
            // Auto-eliminar este script después de 3 segundos
            setTimeout(function() {
                fetch(window.location.href + '?action=self_delete', {method: 'POST'})
                    .then(() => {
                        document.body.innerHTML = '<div class="container"><h1>✅ Limpieza Completada</h1><p>El script se ha eliminado automáticamente.</p><p><a href="/" style="color: #FFD200;">Volver al inicio</a></p></div>';
                    });
            }, 3000);
        </script>
    </div>
</body>
</html>

<?php
// Auto-eliminar este script si se solicita
if (isset($_POST['action']) && $_POST['action'] === 'self_delete') {
    if (file_exists(__FILE__)) {
        unlink(__FILE__);
    }
    exit('deleted');
}
?>