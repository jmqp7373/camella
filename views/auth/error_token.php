<?php
/**
 * Vista: Error de token inválido
 * 
 * PROPÓSITO: Mostrar mensaje cuando el enlace de reset es inválido o expirado
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
                    <h2><i class="fas fa-exclamation-triangle"></i> Enlace inválido</h2>
                </div>
                <div class="card-body text-center">
                    
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i>
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    
                    <p class="mb-4">
                        Esto puede ocurrir si:
                    </p>
                    
                    <ul class="text-left mb-4">
                        <li>El enlace ha expirado (válido por 30 minutos)</li>
                        <li>Ya has usado este enlace anteriormente</li>
                        <li>El enlace está incompleto o dañado</li>
                    </ul>
                    
                    <div class="mt-4">
                        <a href="index.php?view=recuperar-password" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Solicitar nuevo enlace
                        </a>
                        
                        <a href="index.php?view=login" class="btn btn-secondary ms-2">
                            <i class="fas fa-sign-in-alt"></i> Ir al login
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>