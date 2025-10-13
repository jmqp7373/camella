<?php
/**
 * Vista de Login con Teléfono - Camella.com.co
 * Página de acceso mediante Magic Link y código de 6 dígitos
 */
$pageTitle = "Acceso con Teléfono";

// Detectar entorno y configurar URL del controlador
$isLocalhost = (
    $_SERVER['HTTP_HOST'] === 'localhost' ||
    strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
    $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1:') === 0
);

if ($isLocalhost) {
    // Entorno local - usar ngrok
    $magicLinkControllerUrl = 'https://nonlugubriously-subglobosely-anabel.ngrok.io/controllers/MagicLinkController.php';
} else {
    // Entorno de producción - usar dominio real
    $magicLinkControllerUrl = 'https://camella.com.co/controllers/MagicLinkController.php';
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1><i class="fas fa-mobile-alt"></i> Acceso con tu celular</h1>
            <p>Ingresa tu número para recibir un enlace mágico y código de acceso</p>
        </div>

        <form class="login-form" method="POST" action="<?= $magicLinkControllerUrl ?>" id="phoneLoginForm">
            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Número de celular
                </label>
                <div class="phone-input-container">
                    <span class="country-prefix">+57</span>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        required 
                        placeholder="300 123 4567"
                        class="form-input phone-input"
                        pattern="[0-9]{10}"
                        maxlength="10"
                        minlength="10"
                    >
                </div>
                <small class="form-help">Ingresa tu número sin el +57 (ej: 3001234567)</small>
            </div>

            <div class="form-group" id="codeGroup">
                <label for="verification_code">
                    <i class="fas fa-key"></i> Código de verificación
                </label>
                <input 
                    type="text" 
                    id="verification_code" 
                    name="verification_code" 
                    placeholder="123456"
                    class="form-input code-input"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                >
                <small class="form-help">Ingresa el código de 6 dígitos que recibiste</small>
            </div>

            <button type="submit" class="btn-login-submit" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Enviar código / Verificar
            </button>

            <div class="magic-link-info" style="display: none;" id="linkSentInfo">
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <p><strong>¡Código enviado!</strong></p>
                    <p>Revisa tu WhatsApp o SMS. También puedes usar el enlace mágico que te enviamos.</p>
                </div>
                <button type="button" class="btn-resend" onclick="resendCode()" id="resendBtn" disabled>
                    <i class="fas fa-redo"></i> Reenviar código (<span id="countdown">60</span>s)
                </button>
            </div>
        </form>

        <div class="login-footer">
                        
            <div class="login-benefits">
                <h4><i class="fas fa-shield-alt"></i> ¿Por qué usar acceso con celular?</h4>
                <ul class="benefits-list">
                    <li><i class="fas fa-check"></i> Más seguro que las contraseñas</li>
                    <li><i class="fas fa-check"></i> No necesitas recordar credenciales</li>
                    <li><i class="fas fa-check"></i> Acceso instantáneo con Magic Link</li>
                    <li><i class="fas fa-check"></i> Válido por 24 horas</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para el login con teléfono */
.phone-input-container {
    display: flex;
    align-items: center;
    border: 2px solid var(--color-gris-claro);
    border-radius: var(--border-radius);
    background: var(--color-blanco);
    overflow: hidden;
    transition: var(--transition-normal);
}

.phone-input-container:focus-within {
    border-color: var(--color-azul);
    box-shadow: 0 0 0 3px rgba(var(--color-azul-rgb), 0.1);
}

.country-prefix {
    background: var(--color-gris-claro);
    padding: 12px 15px;
    font-weight: 600;
    color: var(--color-azul);
    border-right: 1px solid var(--color-gris);
    white-space: nowrap;
}

.phone-input {
    border: none !important;
    flex: 1;
    padding: 12px 15px;
    font-size: 16px;
}

.phone-input:focus {
    outline: none;
    box-shadow: none;
}

.code-input {
    font-family: 'Courier New', monospace;
    font-size: 20px;
    letter-spacing: 4px;
    text-align: center;
    font-weight: bold;
}

.form-help {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: var(--color-gris-oscuro);
    font-style: italic;
}

.magic-link-info {
    margin-top: 20px;
    padding: 20px;
    background: var(--color-verde-claro);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--color-verde);
}

.success-message {
    text-align: center;
    margin-bottom: 15px;
}

.success-message i {
    color: var(--color-verde);
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
}

.success-message p {
    margin: 5px 0;
}

.btn-resend {
    width: 100%;
    padding: 10px;
    background: var(--color-gris);
    color: var(--color-blanco);
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition-normal);
}

.btn-resend:enabled {
    background: var(--color-azul);
}

.btn-resend:enabled:hover {
    background: var(--color-azul-oscuro);
}

.login-benefits {
    margin-top: 20px;
    padding: 15px;
    background: rgba(var(--color-azul-rgb), 0.05);
    border-radius: var(--border-radius);
}

.login-benefits h4 {
    margin: 0 0 10px 0;
    color: var(--color-azul);
    font-size: 14px;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.benefits-list li {
    padding: 3px 0;
    font-size: 12px;
    color: var(--color-gris-oscuro);
}

.benefits-list i {
    color: var(--color-verde);
    margin-right: 8px;
    font-size: 10px;
}
</style>

<script>
// URL del controlador basada en el entorno
const magicLinkControllerUrl = '<?= $magicLinkControllerUrl ?>';

let codeSent = false;
let countdownTimer = null;

document.getElementById('phoneLoginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const phone = document.getElementById('phone').value;
    const code = document.getElementById('verification_code').value;
    
    if (!code || code.length !== 6) {
        // Si no hay código o no es válido - solicitar código
        if (validatePhone(phone)) {
            sendMagicLinkAndCode(phone);
        }
    } else {
        // Si hay código - verificar código
        if (validatePhone(phone) && validateCode(code)) {
            verifyCodeAndLogin(phone, code);
        }
    }
});

function validatePhone(phone) {
    const phonePattern = /^[3][0-9]{9}$/;
    
    if (!phonePattern.test(phone)) {
        alert('Por favor ingresa un número de celular válido (debe empezar con 3 y tener 10 dígitos)');
        return false;
    }
    
    return true;
}

function validateCode(code) {
    const codePattern = /^[0-9]{6}$/;
    
    if (!codePattern.test(code)) {
        alert('Por favor ingresa el código de 6 dígitos que recibiste');
        return false;
    }
    
    return true;
}

function sendMagicLinkAndCode(phone) {
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Mostrar loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    submitBtn.disabled = true;
    
    // Enviar código al controlador
    fetch(magicLinkControllerUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=enviarCodigo&phone=+57${phone}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mostrar interfaz de código
            showCodeInterface();
            startCountdown();
            codeSent = true;
        } else {
            alert('Error al enviar el código: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión. Por favor intenta nuevamente.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showCodeInterface() {
    document.getElementById('linkSentInfo').style.display = 'block';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sign-in-alt"></i> Verificar código e ingresar';
    document.getElementById('verification_code').focus();
}

function verifyCodeAndLogin(phone, code) {
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    submitBtn.disabled = true;
    
    fetch(magicLinkControllerUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=validarCodigo&phone=+57${phone}&code=${code}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Éxito - redirigir o mostrar mensaje
            alert('¡Acceso exitoso! Bienvenido a Camella.com.co');
            window.location.href = 'index.php'; // o dashboard
        } else {
            alert('Código incorrecto. Por favor verifica e intenta nuevamente.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión. Por favor intenta nuevamente.');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function startCountdown() {
    let seconds = 60;
    const countdownEl = document.getElementById('countdown');
    const resendBtn = document.getElementById('resendBtn');
    
    countdownTimer = setInterval(() => {
        seconds--;
        countdownEl.textContent = seconds;
        
        if (seconds <= 0) {
            clearInterval(countdownTimer);
            resendBtn.disabled = false;
            resendBtn.innerHTML = '<i class="fas fa-redo"></i> Reenviar código';
        }
    }, 1000);
}

function resendCode() {
    const phone = document.getElementById('phone').value;
    if (validatePhone(phone)) {
        sendMagicLinkAndCode(phone);
        document.getElementById('resendBtn').disabled = true;
        document.getElementById('resendBtn').innerHTML = '<i class="fas fa-redo"></i> Reenviar código (<span id="countdown">60</span>s)';
    }
}

// Formatear número mientras se escribe
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    e.target.value = value;
});

// Formatear código mientras se escribe
document.getElementById('verification_code').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 6) {
        value = value.slice(0, 6);
    }
    e.target.value = value;
});
</script>