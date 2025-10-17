<!-- ============================================
     BLOQUE: Gestión de Categorías y Oficios
     Acceso rápido para administradores
     ============================================ -->

<!-- Wrapper similar al bloque de anuncios -->
<section class="gestion-categorias-section" style="margin-top: 2rem;">
    <h2 style="color: #003d7a; font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-layer-group"></i> Gestión de Categorías y Oficios
    </h2>
    
    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        
        <!-- Contenido del bloque -->
        <div class="row g-3">
            <!-- Tarjeta de acceso principal -->
            <div class="col-md-12">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="info-card-content">
                        <h4 class="info-card-title">Panel de Gestión Completo</h4>
                        <p class="info-card-text">
                            Accede al panel administrativo completo para editar categorías, administrar oficios 
                            y marcar los más demandados como populares.
                        </p>
                        <ul class="info-card-list">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Ver todas las categorías organizadas</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Gestionar oficios por categoría</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Marcar oficios populares con 🔥</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Visualización en tiempo real de cambios</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón centrado -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?= app_url('views/admin/categoriasOficios.php') ?>" 
               class="btn-camella"
               style="display: inline-block; padding: 0.75rem 2rem; background: linear-gradient(135deg, #3a8be8 0%, #2a6bb8 100%); color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: all 0.2s; box-shadow: 0 2px 8px rgba(58, 139, 232, 0.3);"
               onmouseover="this.style.background='linear-gradient(135deg, #2a6bb8 0%, #1a5b98 100%)'; this.style.boxShadow='0 4px 12px rgba(58, 139, 232, 0.4)'; this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='linear-gradient(135deg, #3a8be8 0%, #2a6bb8 100%)'; this.style.boxShadow='0 2px 8px rgba(58, 139, 232, 0.3)'; this.style.transform='translateY(0)';">
                <i class="fas fa-layer-group me-2"></i>
                Ir a gestión de categorías y oficios
            </a>
        </div>
        
    </div>
</section>

<style>
/* Estilos específicos para este bloque */
.info-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.info-card-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #3a8be8 0%, #2a6bb8 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.info-card-content {
    flex: 1;
}

.info-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #002b47;
    margin-bottom: 0.5rem;
}

.info-card-text {
    color: #6c757d;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.info-card-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-card-list li {
    padding: 0.25rem 0;
    color: #495057;
    font-size: 0.95rem;
}

.btn-camella {
    background: linear-gradient(135deg, #3a8be8 0%, #2a6bb8 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(58, 139, 232, 0.3);
}

.btn-camella:hover {
    background: linear-gradient(135deg, #2a6bb8 0%, #1a5b98 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(58, 139, 232, 0.4);
    transform: translateY(-2px);
}

.btn-camella:active {
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .info-card {
        flex-direction: column;
        text-align: center;
    }
    
    .info-card-icon {
        margin: 0 auto;
    }
    
    .info-card-list {
        text-align: left;
    }
}
</style>
