<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEliminar');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    const anuncioTituloModal = document.getElementById('anuncioTituloModal');
    let anuncioIdSeleccionado = null;
    
    // Abrir modal al hacer clic en eliminar
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            anuncioIdSeleccionado = this.getAttribute('data-anuncio-id');
            const titulo = this.getAttribute('data-anuncio-titulo');
            
            anuncioTituloModal.textContent = titulo;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Bloquear scroll
        });
    });
    
    // Cerrar modal al cancelar
    btnCancelar.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        anuncioIdSeleccionado = null;
    });
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            anuncioIdSeleccionado = null;
        }
    });
    
    // Confirmar eliminación
    btnConfirmarEliminar.addEventListener('click', async function() {
        if (!anuncioIdSeleccionado) return;
        
        // Deshabilitar botón mientras procesa
        this.disabled = true;
        this.style.opacity = '0.6';
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        
        try {
            const formData = new FormData();
            formData.append('anuncio_id', anuncioIdSeleccionado);
            
            const response = await fetch('<?= app_url("api.php") ?>?action=deleteAnuncio', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 99999; animation: slideInRight 0.3s ease;';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                document.body.appendChild(toast);
                
                // Cerrar modal
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // Recargar página después de 1 segundo
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar el anuncio'));
                this.disabled = false;
                this.style.opacity = '1';
                this.innerHTML = '<i class="fas fa-trash-alt"></i> Eliminar';
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Error de conexión. Por favor intenta de nuevo.');
            this.disabled = false;
            this.style.opacity = '1';
            this.innerHTML = '<i class="fas fa-trash-alt"></i> Eliminar';
        }
    });
});
</script>
