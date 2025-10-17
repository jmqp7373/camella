<?php
/**
 * Partial: Role Switcher
 * Selector de rol para administradores (impersonación)
 * Solo visible si el usuario real es admin
 */

// Verificar si el usuario real es admin
$isRealAdmin = isset($_SESSION['original_role']) && $_SESSION['original_role'] === 'admin';

if (!$isRealAdmin) {
    return; // No mostrar nada si no es admin
}

// Determinar rol actual para renderizar
$currentRole = $_SESSION['impersonate_role'] ?? $_SESSION['role'] ?? 'admin';
?>

<!-- Role Switcher (Solo Admin) -->
<div class="role-switcher" style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 25px; backdrop-filter: blur(10px);">
    <label for="roleSwitcher" style="color: white; font-size: 0.85rem; margin: 0; opacity: 0.9; display: flex; align-items: center; gap: 0.3rem;">
        <i class="fas fa-user-shield"></i> 
        <span>Ver como:</span>
    </label>
    <select id="roleSwitcher" style="padding: 0.4rem 0.8rem; border: 2px solid rgba(255,255,255,0.3); border-radius: 20px; background: white; color: #003d7a; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.3s;">
        <option value="admin" <?= $currentRole === 'admin' ? 'selected' : '' ?>>👑 Administrador</option>
        <option value="promotor" <?= $currentRole === 'promotor' ? 'selected' : '' ?>>📢 Promotor</option>
        <option value="publicante" <?= $currentRole === 'publicante' ? 'selected' : '' ?>>💼 Publicante</option>
    </select>
</div>

<!-- Script para cambio de rol -->
<script>
window.currentRole = <?= json_encode($currentRole) ?>;

(function() {
    const roleSwitcher = document.getElementById('roleSwitcher');
    if (!roleSwitcher) return;
    
    // Establecer valor actual
    roleSwitcher.value = window.currentRole;
    
    // Manejar cambio de rol
    roleSwitcher.addEventListener('change', async function() {
        const newRole = this.value;
        
        // Prevenir cambios si es el mismo rol
        if (newRole === window.currentRole) return;
        
        // Deshabilitar mientras procesa
        this.disabled = true;
        this.style.opacity = '0.6';
        
        try {
            const formData = new URLSearchParams();
            formData.append('role', newRole);
            
            const response = await fetch('<?= app_url("controllers/RoleSwitcherController.php") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.ok) {
                // Mostrar notificación de éxito
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 99999; animation: slideInRight 0.3s ease;';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                document.body.appendChild(toast);
                
                // Determinar URL de redirección según el nuevo rol
                const dashboardUrls = {
                    'admin': '<?= app_url("views/admin/dashboard.php") ?>',
                    'promotor': '<?= app_url("views/promotor/dashboard.php") ?>',
                    'publicante': '<?= app_url("views/publicante/dashboard.php") ?>'
                };
                
                // Redirigir después de un breve delay
                setTimeout(() => {
                    window.location.href = dashboardUrls[newRole] || dashboardUrls['admin'];
                }, 600);
            } else {
                alert('Error: ' + (data.error || 'No se pudo cambiar el rol'));
                this.disabled = false;
                this.style.opacity = '1';
                this.value = window.currentRole; // Revertir selección
            }
            
        } catch (error) {
            console.error('Error al cambiar de rol:', error);
            alert('Error de conexión. Por favor intenta de nuevo.');
            this.disabled = false;
            this.style.opacity = '1';
            this.value = window.currentRole; // Revertir selección
        }
    });
})();
</script>

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

#roleSwitcher:hover {
    border-color: rgba(255,255,255,0.6);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

#roleSwitcher:focus {
    outline: none;
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.2);
}
</style>
