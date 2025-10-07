<?php
/**
 * Página principal del sitio web camella.com.co
 * Versión inicial para pruebas de GitHub
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camella.com.co - Página Principal</title>
    <link rel="icon" type="image/x-icon" href="assets/images/logo/favicon.ico">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 1rem;
        }
        .info {
            color: #666;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/images/logo/logo_horizontal.png" alt="Logo Camella" class="logo">
        
        <h1><?php echo "¡Hola Mundo!"; ?></h1>
        
        <p>Bienvenido al sitio web de <strong>Camella.com.co</strong></p>
        
        <div class="info">
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            <p><strong>Servidor:</strong> <?php echo $_SERVER['HTTP_HOST']; ?></p>
            <p><strong>IP del Cliente:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?></p>
        </div>
        
        <p style="margin-top: 2rem; color: #28a745; font-weight: bold;">
            ✅ Sitio funcionando correctamente
        </p>
    </div>
</body>
</html>