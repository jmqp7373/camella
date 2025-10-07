<?php
/**
 * Vista de Login - Camella.com.co
 * Página de acceso para usuarios registrados
 */
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1><i class="fas fa-user-circle"></i> Acceso a tu cuenta</h1>
            <p>Ingresa tus credenciales para acceder a tu perfil</p>
        </div>

        <form class="login-form" method="POST" action="index.php?view=procesar-login">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Correo electrónico
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="tu@ejemplo.com"
                    class="form-input"
                >
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Contraseña
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    class="form-input"
                >
                <div class="password-toggle">
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordar sesión</label>
                </div>
                <a href="index.php?view=recuperar-password" class="forgot-password">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit" class="btn-login-submit">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <div class="login-footer">
            <p>¿No tienes cuenta? 
                <a href="index.php?view=registro" class="register-link">
                    Regístrate aquí
                </a>
            </p>
            
            <div class="social-login">
                <p>O accede con:</p>
                <div class="social-buttons">
                    <button class="btn-social btn-google" onclick="loginGoogle()">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button class="btn-social btn-linkedin" onclick="loginLinkedin()">
                        <i class="fab fa-linkedin"></i> LinkedIn
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

function loginGoogle() {
    // Implementar login con Google
    console.log('Login con Google - Por implementar');
    alert('Función de login con Google próximamente');
}

function loginLinkedin() {
    // Implementar login con LinkedIn
    console.log('Login con LinkedIn - Por implementar');
    alert('Función de login con LinkedIn próximamente');
}
</script>
