<!-- Modal de confirmación para eliminar anuncio -->
<div id="modalEliminar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; padding: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); animation: slideDown 0.3s ease;">
        <!-- Header del modal -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; background: #fee; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; color: #dc3545;"></i>
            </div>
            <h3 style="color: #333; font-size: 1.5rem; margin-bottom: 0.5rem; font-weight: 600;">
                ¿Eliminar anuncio?
            </h3>
            <p style="color: #666; font-size: 0.95rem; margin: 0;">
                Esta acción no se puede deshacer
            </p>
        </div>
        
        <!-- Información del anuncio -->
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
            <p style="margin: 0; color: #333; font-weight: 500;">
                <i class="fas fa-file-alt" style="color: #dc3545; margin-right: 0.5rem;"></i>
                <span id="anuncioTituloModal">Título del anuncio</span>
            </p>
        </div>
        
        <!-- Botones de acción -->
        <div style="display: flex; gap: 1rem;">
            <button 
                id="btnCancelar" 
                style="flex: 1; padding: 0.75rem; background: #6c757d; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#5a6268'"
                onmouseout="this.style.background='#6c757d'">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button 
                id="btnConfirmarEliminar" 
                style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#c82333'"
                onmouseout="this.style.background='#dc3545'">
                <i class="fas fa-trash-alt"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
