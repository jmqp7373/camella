<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Básico - Camella.com.co</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Test de Conectividad - Camella.com.co</h1>
    <hr>
    
    <h2>Verificaciones:</h2>
    <ul>
        <li class="success">✅ Servidor web funcionando</li>
        <li class="success">✅ Archivo HTML cargando correctamente</li>
        <li class="success">✅ Fecha actual: <?php echo date('Y-m-d H:i:s'); ?></li>
        <li class="success">✅ PHP versión: <?php echo phpversion(); ?></li>
    </ul>
    
    <h2>Links de prueba:</h2>
    <ul>
        <li><a href="/">← Página principal (index.php)</a></li>
        <li><a href="/test_simple.php">← Test PHP simple</a></li>
        <li><a href="/diagnostico_simple.php">← Diagnóstico completo</a></li>
    </ul>
    
    <hr>
    <p><strong>Si ves este contenido, el servidor está funcionando correctamente.</strong></p>
    
</body>
</html>