<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? $titulo : 'Iniciar Sesión - Camella'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/logo/favicon.ico" type="image/x-icon">
    
    <!-- Meta tags para SEO -->
    <meta name="description" content="Inicie sesión en Camella.com.co para acceder a su cuenta y gestionar sus publicaciones.">
    <meta name="keywords" content="login, iniciar sesión, Camella, trabajo Colombia">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Estilos CSS -->
    <style>
        :root {
            /* Paleta de colores colombiana */
            --amarillo-colombia: #FDE047;
            --azul-colombia: #1E40AF;
            --rojo-colombia: #DC2626;
            --verde-colombia: #059669;
            --naranja-colombia: #EA580C;
            --violeta-colombia: #7C3AED;
            --rosa-colombia: #EC4899;
            --turquesa-colombia: #0891B2;
            
            /* Colores base */
            --blanco: #FFFFFF;
            --gris-claro: #F3F4F6;
            --gris-medio: #6B7280;
            --gris-oscuro: #374151;
            --negro: #111827;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--amarillo-colombia), var(--azul-colombia));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gris-oscuro);
        }
        
        .login-container {
            background: var(--blanco);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 90%;
            margin: 20px;
        }
        
        .login-header {
            background: var(--azul-colombia);
            color: var(--blanco);
            text-align: center;
            padding: 2rem 1.5rem;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--blanco);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo img {
            width: 40px;
            height: 40px;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .login-form {
            padding: 2rem 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--gris-oscuro);
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gris-claro);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--azul-colombia);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }
        
        .form-group input:invalid {
            border-color: var(--rojo-colombia);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .checkbox-group label {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: var(--gris-medio);
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--amarillo-colombia), var(--naranja-colombia));
            color: var(--negro);
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            margin-bottom: 1.5rem;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background: #FEF2F2;
            color: var(--rojo-colombia);
            border: 1px solid #FECACA;
        }
        
        .alert-success {
            background: #F0FDF4;
            color: var(--verde-colombia);
            border: 1px solid #BBF7D0;
        }
        
        .alert-info {
            background: #EFF6FF;
            color: var(--azul-colombia);
            border: 1px solid #DBEAFE;
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem;
            background: var(--gris-claro);
            border-top: 1px solid #E5E7EB;
        }
        
        .login-footer a {
            color: var(--azul-colombia);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
                width: 95%;
            }
            
            .login-form {
                padding: 1.5rem 1rem;
            }
            
            .login-header {
                padding: 1.5rem 1rem;
            }
        }
        
        /* Loading state */
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Animation para el logo */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .logo {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header con logo y título -->
        <div class="login-header">
            <div class="logo">
                <img src="assets/images/logo/favicon.ico" alt="Camella Logo" onerror="this.style.display='none';">
            </div>
            <h1>Camella.com.co</h1>
            <p>Inicia sesión para continuar</p>
        </div>
        
        <!-- Formulario de login -->
        <form method="POST" action="/login" class="login-form" id="loginForm">
            
            <?php
            /**
             * GENERACIÓN DE TOKEN CSRF PARA PROTECCIÓN DE FORMULARIO
             * 
             * Propósito: Prevenir ataques Cross-Site Request Forgery (CSRF)
             * donde un sitio malicioso podría enviar requests al formulario
             * de login usando la sesión del usuario.
             * 
             * Flujo:
             * 1. Generar token único por sesión si no existe
             * 2. Incluir token en formulario como campo oculto
             * 3. Validar token en controlador antes de procesar login
             * 4. Regenerar token después de login exitoso
             * 
             * Seguridad: Usa random_bytes() criptográficamente seguro
             */
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            ?>
            
            <!-- Token CSRF para prevenir ataques -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <!-- Mensajes de estado -->
            <?php if (isset($error) && $error): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($mensaje) && $mensaje): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <!-- Campo de email -->
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="email"
                    placeholder="usuario@ejemplo.com"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            
            <!-- Campo de contraseña -->
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="••••••••"
                    minlength="6"
                >
            </div>
            
            <!-- Checkbox recordar sesión -->
            <div class="checkbox-group">
                <input type="checkbox" id="recordar" name="recordar" value="1">
                <label for="recordar">Mantener sesión iniciada</label>
            </div>
            
            <!-- Campo oculto para redirección -->
            <?php if (isset($redirect) && $redirect): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <?php endif; ?>
            
            <!-- Botón de envío -->
            <button type="submit" class="btn-login" id="btnLogin">
                Iniciar Sesión
            </button>
        </form>
        
        <!-- Footer con enlaces -->
        <div class="login-footer">
            <p><a href="/" title="Volver al inicio">← Volver al inicio</a></p>
            <p style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--gris-medio);">
                ¿Necesitas una cuenta? Contacta al administrador
            </p>
        </div>
    </div>
    
    <script>
        // JavaScript para mejorar la experiencia del usuario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const btnLogin = document.getElementById('btnLogin');
            const originalText = btnLogin.textContent;
            
            // Manejar envío del formulario
            form.addEventListener('submit', function(e) {
                // Validaciones adicionales del lado cliente
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Por favor complete todos los campos');
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('La contraseña debe tener al menos 6 caracteres');
                    return;
                }
                
                // Mostrar estado de carga
                btnLogin.disabled = true;
                btnLogin.textContent = 'Iniciando sesión...';
                
                // Restaurar botón después de un tiempo si hay error
                setTimeout(function() {
                    if (btnLogin.disabled) {
                        btnLogin.disabled = false;
                        btnLogin.textContent = originalText;
                    }
                }, 5000);
            });
            
            // Focus automático en el primer campo
            const emailField = document.getElementById('email');
            if (emailField && !emailField.value) {
                emailField.focus();
            }
            
            // Manejar Enter en los campos
            document.getElementById('email').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('password').focus();
                    e.preventDefault();
                }
            });
            
            // Auto-ocultar mensajes después de 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
        
        // Funciones de utilidad
        function mostrarError(mensaje) {
            const alertsContainer = document.querySelector('.login-form');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-error';
            errorDiv.innerHTML = '<strong>Error:</strong> ' + mensaje;
            alertsContainer.insertBefore(errorDiv, alertsContainer.firstChild);
        }
        
        // Prevenir ataques de fuerza bruta básicos
        let intentosFallidos = parseInt(localStorage.getItem('loginAttempts') || '0');
        if (intentosFallidos >= 5) {
            const ultimoIntento = parseInt(localStorage.getItem('lastAttempt') || '0');
            const ahora = Date.now();
            const tiempoEspera = 15 * 60 * 1000; // 15 minutos
            
            if (ahora - ultimoIntento < tiempoEspera) {
                const tiempoRestante = Math.ceil((tiempoEspera - (ahora - ultimoIntento)) / 60000);
                mostrarError(`Demasiados intentos fallidos. Espere ${tiempoRestante} minutos.`);
                document.getElementById('btnLogin').disabled = true;
            } else {
                localStorage.removeItem('loginAttempts');
                localStorage.removeItem('lastAttempt');
            }
        }
        
        // Registrar intentos fallidos (si hay error de login)
        <?php if (isset($error) && $error): ?>
        intentosFallidos++;
        localStorage.setItem('loginAttempts', intentosFallidos);
        localStorage.setItem('lastAttempt', Date.now());
        <?php endif; ?>
    </script>
</body>
</html>