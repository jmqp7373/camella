<?php
/**
 * Vista de Login con Teléfono - Camella.com.co
 * Página de acceso mediante Magic Link y código de 6 dígitos
 */
$pageTitle = "Acceso con Teléfono";

// Detectar entorno y configurar URL del controlador
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalhost = (
    $httpHost === 'localhost' ||
    strpos($httpHost, 'localhost:') === 0 ||
    $httpHost === '127.0.0.1' ||
    strpos($httpHost, '127.0.0.1:') === 0 ||
    strpos($httpHost, '.ngrok') !== false || // Detecta .ngrok.io y .ngrok-free.app
    strpos($httpHost, 'ngrok') !== false
);

if ($isLocalhost) {
    // Entorno local/ngrok - usar ruta relativa desde index.php (raíz del proyecto)
    $verificationControllerUrl = 'controllers/sendTwiliosVerificationCode.php';
    $verifyCodeControllerUrl = 'controllers/verifyTwilioCode.php';
} else {
    // Entorno de producción - usar URL absoluta
    $verificationControllerUrl = 'https://camella.com.co/controllers/sendTwiliosVerificationCode.php';
    $verifyCodeControllerUrl = 'https://camella.com.co/controllers/verifyTwilioCode.php';
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1><i class="fas fa-mobile-alt"></i> Acceso con tu celular</h1>
            <p>Ingresa tu número para recibir un enlace mágico y código de acceso</p>
        </div>

        <?php if (isset($_GET['error']) && !empty($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?= htmlspecialchars($_GET['error']) ?></strong>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="<?= $verificationControllerUrl ?>" id="phoneLoginForm">
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

/* Estilo de alerta de error */
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    line-height: 1.5;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-error {
    background: #fee;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert-error i {
    color: #dc3545;
    font-size: 20px;
    flex-shrink: 0;
}

.alert-error strong {
    font-weight: 600;
}
</style>

<script>
// URLs de controladores basadas en el entorno (inyectadas desde PHP arriba)
const verificationControllerUrl = '<?= $verificationControllerUrl ?>';
const verifyCodeControllerUrl = '<?= $verifyCodeControllerUrl ?>';

// Debug: Verificar que las URLs se cargaron correctamente
console.log('🎯 URL envío código:', verificationControllerUrl);
console.log('🎯 URL verificar código:', verifyCodeControllerUrl);
if (!verificationControllerUrl || verificationControllerUrl === '') {
    console.error('❌ ERROR: verificationControllerUrl está vacía o indefinida');
    alert('Error de configuración: URL del controlador no definida');
}

let codeSent = false;
let countdownTimer = null;

// Helper robusto para POST x-www-form-urlencoded que espera JSON
async function postForm(url, params) {
    console.log('📤 Enviando request a:', url);
    console.log('📦 Parámetros:', params);
    
    const resp = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        },
        body: new URLSearchParams(params)
    });

    console.log('📥 Respuesta recibida:', resp.status, resp.statusText);
    
    const contentType = resp.headers.get('content-type') || '';
    console.log('📄 Content-Type:', contentType);
    
    const text = await resp.text();
    console.log('📝 Respuesta texto:', text.slice(0, 500));

    if (!resp.ok) {
        console.error('❌ Error HTTP:', resp.status);
        throw new Error(`HTTP ${resp.status} - ${text.slice(0, 200)}`);
    }
    if (!contentType.includes('application/json')) {
        console.error('❌ Respuesta no es JSON:', text.slice(0, 200));
        throw new Error(`Respuesta no-JSON del backend: ${text.slice(0, 200)}`);
    }
    try {
        const data = JSON.parse(text);
        console.log('✅ JSON parseado:', data);
        return data;
    } catch (e) {
        console.error('❌ Error parseando JSON:', e);
        throw new Error('JSON inválido del backend.');
    }
}

document.getElementById('phoneLoginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const phone = document.getElementById('phone').value;
    const code = document.getElementById('verification_code').value;

    if (!code || code.length !== 6) {
        // Solicitar código
        if (validatePhone(phone)) {
            sendMagicLinkAndCode(phone);
        }
    } else {
        // Verificar código
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

    postForm(verificationControllerUrl, {
        phone: '+57' + phone
    })
    .then((data) => {
        if (data.ok) {
            showCodeInterface();
            startCountdown();
            codeSent = true;
            alert('Código enviado correctamente. Revisa tu SMS.');
        } else {
            alert('Error al enviar el código: ' + (data.message || data.msg || 'Sin detalle'));
        }
    })
    .catch((error) => {
        console.error('Error enviarCodigo:', error);
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

    postForm(verifyCodeControllerUrl, {
        phone: '+57' + phone,
        code: code
    })
    .then((data) => {
        if (data.ok && data.redirect) {
            console.log('✅ Login exitoso. Redirigiendo a:', data.redirect);
            window.location.href = data.redirect;
        } else {
            alert(data.msg || 'Código incorrecto o expirado.');
        }
    })
    .catch((error) => {
        console.error('Error validarCodigo:', error);
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
