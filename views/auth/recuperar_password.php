<?php 
/**
 * Vista: Formulario de recuperación de contraseña
 * 
 * PROPÓSITO: Permitir al usuario solicitar un enlace de reset por email
 * 
 * CARACTERÍSTICAS:
 * - Formulario con email y protección CSRF
 * - Mensajes discretos sin revelar existencia de cuentas
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
                    <h2><i class="fas fa-key"></i> Recuperar contraseña</h2>
                </div>
                <div class="card-body">
                    
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $tipo_mensaje === 'success' ? 'success' : ($tipo_mensaje === 'error' ? 'danger' : 'info') ?>" role="alert">
                            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <p class="text-muted mb-4">
                        Ingresa tu dirección de correo electrónico y te enviaremos 
                        un enlace para recuperar tu contraseña.
                    </p>

                    <form method="POST" action="index.php?view=enviar-reset">
                        
                        <!-- Token CSRF (campo oculto) -->
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Correo electrónico
                            </label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="tu@ejemplo.com"
                                maxlength="190"
                            >
                        </div>

                        <div class="form-group text-center mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Enviar enlace de recuperación
                            </button>
                        </div>

                    </form>

                    <div class="text-center">
                        <p class="text-muted small mb-2">
                            Te enviaremos instrucciones si existe una cuenta con ese correo.
                        </p>
                        
                        <div class="mt-3">
                            <a href="index.php?view=login" class="btn btn-secondary btn-sm">
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

<?php require __DIR__ . '/../../partials/footer.php'; ?>