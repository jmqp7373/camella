<?php
/**
 * promotor/panel.php - Panel Principal de Promotor
 * 
 * Propósito: Panel de control principal para usuarios promotores
 * Contenido mínimo para verificar acceso y funcionalidad de redirección.
 * 
 * PROTECCIÓN: Solo usuarios con rol 'promotor' o 'admin' pueden acceder
 * La verificación se realiza en el controlador antes de llegar aquí.
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

// Verificar que se llegó aquí de forma apropiada
if (!isset($_SESSION)) {
    session_start();
}

require_once __DIR__ . '/../helpers/AuthHelper.php';

$authHelper = new AuthHelper();

// Doble verificación de seguridad
if (!$authHelper->estaAutenticado() || 
    (!$authHelper->verificarAcceso('promotor') && !$authHelper->verificarAcceso('admin'))) {
    header('Location: /login?error=' . urlencode('Acceso no autorizado'));
    exit;
}

$usuario = $authHelper->obtenerUsuarioActual();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Promotor - Camella.com.co</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    
    <!-- CSS básico sin modificar maquetación -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f8ff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #059669;
        }
        .welcome-message {
            background: #d1fae5;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid #059669;
            margin-bottom: 2rem;
        }
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .action-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            background-color: #059669;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #047857;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .user-info {
            background: #fff3cd;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📢 Panel de Promotor</h1>
            <p>Herramientas de Marketing y Promoción</p>
        </div>
        
        <!-- Información del usuario actual -->
        <div class="user-info">
            <strong>👤 Usuario:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?><br>
            <strong>📧 Email:</strong> <?php echo htmlspecialchars($usuario['email']); ?><br>
            <strong>🔑 Rol:</strong> <?php echo strtoupper($usuario['rol']); ?><br>
            <strong>🕐 Sesión iniciada:</strong> <?php echo date('d/m/Y H:i:s'); ?>
            
            <?php if ($usuario['rol'] === 'admin'): ?>
                <br><strong>ℹ️ Modo:</strong> Accediendo como Admin con permisos de Promotor
            <?php endif; ?>
        </div>
        
        <!-- Mensaje de bienvenida principal -->
        <div class="welcome-message">
            <h2>✅ Bienvenido al panel de PROMOTOR</h2>
            <p>
                <strong>¡Acceso exitoso!</strong> Has iniciado sesión correctamente como promotor.
                Desde aquí puedes gestionar campañas promocionales, estadísticas de marketing
                y herramientas para promocionar ofertas en Camella.com.co.
            </p>
            <p>
                <em>Nota:</em> Este es el contenido mínimo del panel para verificar 
                la funcionalidad de protección por roles y redirección automática.
                En el futuro se implementarán herramientas de promoción avanzadas.
            </p>
        </div>
        
        <!-- Acciones disponibles -->
        <div class="actions">
            <div class="action-card">
                <h3>🏠 Navegación</h3>
                <a href="/" class="btn">Inicio</a>
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <a href="/admin/dashboard.php" class="btn btn-secondary">Admin Panel</a>
                <?php endif; ?>
            </div>
            
            <div class="action-card">
                <h3>📊 Campañas</h3>
                <a href="#" class="btn">Nueva Campaña</a>
                <a href="#" class="btn">Mis Campañas</a>
            </div>
            
            <div class="action-card">
                <h3>📈 Estadísticas</h3>
                <a href="#" class="btn">Rendimiento</a>
                <a href="#" class="btn">Analytics</a>
            </div>
            
            <div class="action-card">
                <h3>🔧 Herramientas</h3>
                <a href="#" class="btn">Promociones</a>
                <a href="/logout" class="btn btn-danger">Cerrar Sesión</a>
            </div>
        </div>
        
        <!-- Información de desarrollo -->
        <div style="background: #e2e3e5; padding: 1rem; border-radius: 4px; margin-top: 2rem;">
            <h3>🚧 Funcionalidades en Desarrollo</h3>
            <ul>
                <li>✅ Protección por rol implementada</li>
                <li>✅ Redirección automática post-login</li>
                <li>⏳ Sistema de campañas promocionales</li>
                <li>⏳ Dashboard de métricas</li>
                <li>⏳ Herramientas de marketing</li>
                <li>⏳ Gestión de comisiones</li>
            </ul>
        </div>
        
        <!-- Información técnica -->
        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6; font-size: 0.9rem; color: #666;">
            <strong>📍 Ubicación:</strong> /promotor/panel.php<br>
            <strong>🔒 Protección:</strong> Verificación de rol 'promotor' o 'admin' activa<br>
            <strong>🚀 Estado:</strong> Sistema operativo y funcional<br>
            <strong>📅 Versión:</strong> 1.0 - <?php echo date('Y-m-d'); ?>
        </div>
    </div>
    
    <script>
        // Log de acceso exitoso (solo para debugging)
        console.log('✅ Panel Promotor cargado exitosamente');
        console.log('👤 Usuario:', <?php echo json_encode($usuario['email']); ?>);
        console.log('🔑 Rol:', <?php echo json_encode($usuario['rol']); ?>);
        
        // Confirmación antes de cerrar sesión
        document.querySelector('a[href="/logout"]').addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro que deseas cerrar la sesión?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>