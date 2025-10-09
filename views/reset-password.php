<?php
/**
 * Vista: Reset Password con verificación HMAC
 * 
 * PROPÓSITO: Permitir al usuario cambiar su contraseña usando un token
 * de recuperación con verificación HMAC segura
 * 
 * FLUJO TÉCNICO:
 * 1. Lee token desde $_GET['token']
 * 2. Genera hash HMAC: hash_hmac('sha256', $token, APP_KEY)
 * 3. Verifica en password_resets que el hash exista, no haya expirado y no esté usado
 * 4. Si válido: muestra formulario para nueva contraseña
 * 5. En POST: actualiza contraseña, marca token como usado, redirige a éxito
 * 
 * CARACTERÍSTICAS DE SEGURIDAD:
 * - Tokens HMAC impossibles de falsificar
 * - Validación de expiración (30 minutos)
 * - Protección CSRF
 * - Validación de fortaleza de contraseña
 * - Token de un solo uso
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-09
 */

// Bootstrap del sistema
require_once __DIR__ . '/../bootstrap.php';

// ============================================
// PROCESAMIENTO POST (Cambio de contraseña)
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar CSRF
        $csrfSession = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
        $csrfPost = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        
        if (!$csrfPost || !hash_equals($csrfSession, $csrfPost)) {
            throw new Exception('Token de seguridad inválido');
        }
        
        $token = isset($_POST['token']) ? trim($_POST['token']) : '';
        $nueva_password = isset($_POST['nueva_password']) ? $_POST['nueva_password'] : '';
        $confirmar_password = isset($_POST['confirmar_password']) ? $_POST['confirmar_password'] : '';
        
        // Validaciones básicas
        if (empty($token)) {
            throw new Exception('Token requerido');
        }
        
        if (empty($nueva_password) || strlen($nueva_password) < 8) {
            throw new Exception('La contraseña debe tener al menos 8 caracteres');
        }
        
        if ($nueva_password !== $confirmar_password) {
            throw new Exception('Las contraseñas no coinciden');
        }
        
        // Generar hash HMAC del token
        if (!defined('APP_KEY')) {
            require_once __DIR__ . '/../config/config.php';
        }
        $token_hash = hash_hmac('sha256', $token, APP_KEY);
        
        // Conectar a BD
        $pdo = getPDO();
        
        // Verificar token en BD: existe, no expirado, no usado
        $stmt = $pdo->prepare("
            SELECT email, used_at 
            FROM password_resets 
            WHERE token_hash = ? 
            AND expires_at > NOW() 
            LIMIT 1
        ");
        $stmt->execute([$token_hash]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resetData) {
            throw new Exception('Token inválido o expirado');
        }
        
        if ($resetData['used_at']) {
            throw new Exception('Este enlace ya fue utilizado');
        }
        
        $email = $resetData['email'];
        
        // Verificar que el usuario existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            throw new Exception('Usuario no encontrado');
        }
        
        // Hashear nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Actualizar contraseña del usuario
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $updateSuccess = $stmt->execute([$password_hash, $email]);
            
            if (!$updateSuccess || $stmt->rowCount() === 0) {
                throw new Exception('Error actualizando contraseña');
            }
            
            // Marcar token como usado
            $stmt = $pdo->prepare("
                UPDATE password_resets 
                SET used_at = NOW() 
                WHERE token_hash = ?
            ");
            $stmt->execute([$token_hash]);
            
            // Confirmar transacción
            $pdo->commit();
            
            // Log de seguridad
            error_log("[RESET_SUCCESS] Contraseña cambiada para email: {$email}");
            
            // Limpiar sesión y redirigir con mensaje de éxito
            unset($_SESSION['csrf_token']);
            $_SESSION['mensaje'] = 'Contraseña cambiada exitosamente. Ya puedes iniciar sesión con tu nueva contraseña.';
            $_SESSION['tipo_mensaje'] = 'success';
            
            header('Location: index.php?view=login');
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("[RESET_ERROR] " . $e->getMessage());
        $error_mensaje = $e->getMessage();
    }
}

// ============================================
// PROCESAMIENTO GET (Mostrar formulario)
// ============================================

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$token_valido = false;
$email_asociado = '';

if (empty($token)) {
    // No hay token - redirigir a solicitar recuperación
    $_SESSION['mensaje'] = 'Necesitas un enlace válido enviado por correo para cambiar tu contraseña.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: index.php?view=recuperar-password');
    exit;
}

try {
    // Generar hash HMAC del token
    if (!defined('APP_KEY')) {
        require_once __DIR__ . '/../config/config.php';
    }
    $token_hash = hash_hmac('sha256', $token, APP_KEY);
    
    // Conectar a BD
    $pdo = getPDO();
    
    // Verificar token válido
    $stmt = $pdo->prepare("
        SELECT email, used_at, expires_at
        FROM password_resets 
        WHERE token_hash = ? 
        AND expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token_hash]);
    $resetData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resetData && !$resetData['used_at']) {
        $token_valido = true;
        $email_asociado = $resetData['email'];
    }
    
} catch (Exception $e) {
    error_log("[RESET_VALIDATION_ERROR] " . $e->getMessage());
    $token_valido = false;
}

if (!$token_valido) {
    // Token inválido - redirigir con mensaje
    $_SESSION['mensaje'] = 'El enlace es inválido, ha expirado o ya fue utilizado. Solicita uno nuevo.';
    $_SESSION['tipo_mensaje'] = 'error';
    header('Location: index.php?view=recuperar-password');
    exit;
}

// Generar token CSRF para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Preparar variables para la vista
$pageTitle = "Cambiar Contraseña";
$mensaje = isset($error_mensaje) ? $error_mensaje : null;
$tipo_mensaje = $mensaje ? 'error' : null;

// Incluir header
require __DIR__ . '/../partials/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-key"></i> Cambiar contraseña</h2>
                </div>
                <div class="card-body">
                    
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?>" role="alert">
                            <i class="fas fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Cuenta:</strong> <?= htmlspecialchars(substr($email_asociado, 0, 3) . '***@' . substr(strrchr($email_asociado, '@'), 1), ENT_QUOTES, 'UTF-8') ?><br>
                        <small>Crea una nueva contraseña segura para proteger tu cuenta.</small>
                    </div>

                    <form method="POST" action="index.php?view=reset-password" id="resetPasswordForm">
                        
                        <!-- Token CSRF (campo oculto) -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        
                        <!-- Token de verificación (campo oculto) -->
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="form-group mb-3">
                            <label for="nueva_password" class="form-label">
                                <i class="fas fa-lock"></i> Nueva contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="nueva_password" 
                                name="nueva_password" 
                                required 
                                minlength="8"
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password"
                            >
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i>
                                    Recomendado: mayúsculas, minúsculas, números y símbolos
                                </small>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="confirmar_password" class="form-label">
                                <i class="fas fa-check-double"></i> Confirmar contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirmar_password" 
                                name="confirmar_password" 
                                required 
                                minlength="8"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                            >
                            <div class="invalid-feedback" id="password-mismatch" style="display: none;">
                                Las contraseñas no coinciden
                            </div>
                        </div>

                        <!-- Indicador de fortaleza de contraseña -->
                        <div class="mb-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" id="password-strength-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="password-strength-text" class="form-text text-muted">Introduce una contraseña para evaluar su fortaleza</small>
                        </div>

                        <div class="form-group text-center mb-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-save"></i> Cambiar contraseña
                            </button>
                        </div>

                    </form>
                    
                    <div class="text-center mt-4">
                        <div class="border-top pt-3">
                            <a href="index.php?view=login" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Volver al login
                            </a>
                            
                            <a href="index.php" class="btn btn-outline-secondary btn-sm ms-2">
                                <i class="fas fa-home"></i> Ir al inicio
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para validación de contraseñas -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nuevaPassword = document.getElementById('nueva_password');
    const confirmarPassword = document.getElementById('confirmar_password');
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    const mismatchFeedback = document.getElementById('password-mismatch');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('resetPasswordForm');

    // Función para evaluar fortaleza de contraseña
    function evaluatePasswordStrength(password) {
        let strength = 0;
        let feedback = [];

        // Longitud mínima
        if (password.length >= 8) {
            strength += 25;
        } else {
            feedback.push('mínimo 8 caracteres');
        }

        // Mayúsculas
        if (/[A-Z]/.test(password)) {
            strength += 25;
        } else {
            feedback.push('una mayúscula');
        }

        // Minúsculas  
        if (/[a-z]/.test(password)) {
            strength += 25;
        } else {
            feedback.push('una minúscula');
        }

        // Números o símbolos
        if (/[\d\W]/.test(password)) {
            strength += 25;
        } else {
            feedback.push('números o símbolos');
        }

        return { strength, feedback };
    }

    // Actualizar indicador de fortaleza
    function updateStrengthIndicator() {
        const password = nuevaPassword.value;
        const { strength, feedback } = evaluatePasswordStrength(password);

        strengthBar.style.width = strength + '%';
        strengthBar.setAttribute('aria-valuenow', strength);

        // Colores según fortaleza
        strengthBar.className = 'progress-bar';
        if (strength < 50) {
            strengthBar.classList.add('bg-danger');
            strengthText.textContent = 'Débil - Falta: ' + feedback.join(', ');
            strengthText.className = 'form-text text-danger';
        } else if (strength < 75) {
            strengthBar.classList.add('bg-warning');
            strengthText.textContent = 'Regular - Falta: ' + feedback.join(', ');
            strengthText.className = 'form-text text-warning';
        } else if (strength < 100) {
            strengthBar.classList.add('bg-info');
            strengthText.textContent = 'Buena - Falta: ' + feedback.join(', ');
            strengthText.className = 'form-text text-info';
        } else {
            strengthBar.classList.add('bg-success');
            strengthText.textContent = 'Excelente - Contraseña muy segura';
            strengthText.className = 'form-text text-success';
        }
    }

    // Validar coincidencia de contraseñas
    function validatePasswordMatch() {
        const password = nuevaPassword.value;
        const confirm = confirmarPassword.value;

        if (confirm && password !== confirm) {
            confirmarPassword.classList.add('is-invalid');
            mismatchFeedback.style.display = 'block';
            return false;
        } else {
            confirmarPassword.classList.remove('is-invalid');
            mismatchFeedback.style.display = 'none';
            return true;
        }
    }

    // Event listeners
    nuevaPassword.addEventListener('input', function() {
        updateStrengthIndicator();
        if (confirmarPassword.value) {
            validatePasswordMatch();
        }
    });

    confirmarPassword.addEventListener('input', validatePasswordMatch);

    // Validación del formulario antes de enviar
    form.addEventListener('submit', function(e) {
        const password = nuevaPassword.value;
        const { strength } = evaluatePasswordStrength(password);
        
        if (strength < 50) {
            e.preventDefault();
            alert('Por favor, crea una contraseña más segura. Debe tener al menos 8 caracteres con mayúsculas, minúsculas y números.');
            nuevaPassword.focus();
            return false;
        }

        if (!validatePasswordMatch()) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            confirmarPassword.focus();
            return false;
        }

        // Deshabilitar botón para evitar doble envío
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    });
});
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>