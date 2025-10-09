<?php
/**
 * Vista: Formulario para establecer nueva contraseña
 * 
 * PROPÓSITO: Permitir al usuario establecer una nueva contraseña
 * tras validar el token recibido por email
 * 
 * CARACTERÍSTICAS:
 * - Formulario con nueva contraseña y confirmación
 * - Protección CSRF
 * - Validación de fortaleza en frontend y backend
 * - Usa estilos existentes del proyecto
 * 
 * @author Camella Development Team
 * @version 1.0
 * @date 2025-10-08
 */

require __DIR__ . '/../../partials/header.php'; 
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-lock"></i> Nueva contraseña</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (isset($mensaje) && !empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : ($tipo_mensaje === 'error' ? 'danger' : 'info') ?>" role="alert">
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted mb-4">
                        Crea una nueva contraseña segura para tu cuenta.
                    </p>

                    <form method="POST" action="index.php?view=procesar-reset">
                        
                        <!-- Token CSRF (campo oculto) -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-key"></i> Nueva contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required 
                                minlength="8"
                                placeholder="Mínimo 8 caracteres"
                            >
                            <small class="form-text text-muted">
                                Debe tener al menos 8 caracteres
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="password_confirm" class="form-label">
                                <i class="fas fa-check-double"></i> Confirmar contraseña
                            </label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password_confirm" 
                                name="password_confirm" 
                                required 
                                minlength="8"
                                placeholder="Repite la contraseña"
                            >
                        </div>

                        <div class="form-group text-center mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cambiar contraseña
                            </button>
                        </div>

                    </form>

                    <div class="text-center">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Recomendaciones de seguridad:</h6>
                            <ul class="mb-0 small text-left">
                                <li>Usa una contraseña única que no hayas usado en otros sitios</li>
                                <li>Incluye una mezcla de letras mayúsculas, minúsculas, números</li>
                                <li>Evita información personal como nombres o fechas</li>
                                <li>Guarda tu contraseña en un lugar seguro</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Validación adicional en el frontend (mejora UX)
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

<?php require __DIR__ . '/../../partials/footer.php'; ?>