<?php 
/**
 * Vista esqueleto: Recuperar contraseña
 * 
 * NOTA PARA DESARROLLADORES:
 * Esta es una vista temporal/esqueleto para evitar errores 404.
 * 
 * PENDIENTE DE IMPLEMENTAR:
 * - Formulario para solicitar reset por email
 * - Generación y envío de token seguro por correo
 * - Validación de token con expiración
 * - Formulario para nueva contraseña
 * - Protección CSRF en todo el flujo
 * - Rate limiting para evitar spam de emails
 * 
 * @author Camella Development Team
 * @version 0.1 (esqueleto)
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
                <div class="card-body text-center">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Función en desarrollo</strong>
                    </div>
                    
                    <p class="mb-4">Esta función estará disponible próximamente.</p>
                    
                    <p class="text-muted small">
                        Mientras tanto, contacta al administrador si necesitas 
                        recuperar el acceso a tu cuenta.
                    </p>
                    
                    <div class="mt-4">
                        <a href="index.php?view=login" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver al login
                        </a>
                        
                        <a href="index.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-home"></i> Ir al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>