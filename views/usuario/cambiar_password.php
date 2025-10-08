<?php
/**
 * Vista: Cambiar Contraseña
 * Formulario seguro para que el usuario autenticado cambie su contraseña
 * Incluye validación CSRF y usa estilos existentes del proyecto
 * 
 * @var string $pageTitle Título de la página
 * @var string|null $mensaje Mensaje de éxito/error
 * @var string|null $tipo_mensaje Tipo de mensaje (success/error)
 */

// Incluir header con estilos existentes
include __DIR__ . '/../../partials/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-key"></i> Cambiar Contraseña</h3>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : 'danger' ?>" role="alert">
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?view=procesar-cambiar-password">
                        
                        <!-- Token CSRF (campo oculto) -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="form-group mb-3">
                            <label for="actual_password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña Actual
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="actual_password" 
                                name="actual_password" 
                                required 
                                placeholder="Ingresa tu contraseña actual"
                            >
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-key"></i> Nueva Contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="new_password" 
                                name="new_password" 
                                required 
                                minlength="10"
                                placeholder="Mínimo 10 caracteres"
                            >
                            <small class="form-text text-muted">
                                Debe incluir: al menos 10 caracteres, mayúsculas, minúsculas y números
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="new_password_confirm" class="form-label">
                                <i class="fas fa-check-double"></i> Confirmar Nueva Contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="new_password_confirm" 
                                name="new_password_confirm" 
                                required 
                                minlength="10"
                                placeholder="Confirma tu nueva contraseña"
                            >
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cambiar Contraseña
                            </button>
                            <a href="index.php" class="btn btn-secondary ms-2">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>

                    </form>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Recomendaciones de Seguridad:</h6>
                            <ul class="mb-0 small">
                                <li>Usa una contraseña única que no hayas usado en otros sitios</li>
                                <li>Incluye una mezcla de letras mayúsculas, minúsculas, números y símbolos</li>
                                <li>Evita información personal como nombres o fechas</li>
                                <li>Cambia tu contraseña regularmente</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación adicional en el frontend (opcional, mejora UX)
document.getElementById('new_password_confirm').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword && confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('new_password').addEventListener('input', function() {
    const password = this.value;
    const isStrong = password.length >= 10 && 
                    /[A-Z]/.test(password) && 
                    /[a-z]/.test(password) && 
                    /\d/.test(password);
    
    if (password && !isStrong) {
        this.setCustomValidity('La contraseña debe incluir mayúsculas, minúsculas y números');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php
// Incluir footer con estilos existentes
include __DIR__ . '/../../partials/footer.php';
?>