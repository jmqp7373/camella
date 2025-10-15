<!-- BLOQUE PUBLICANTE: Herramientas básicas para todos los usuarios -->
<?php
// Este bloque es visible para TODOS los roles (admin, promotor, publicante)
?>

<!-- Mis Anuncios (Todos los usuarios) -->
<section class="publicante-section">
    <h2><i class="fas fa-briefcase"></i> Mis Anuncios</h2>
    
    <div class="dashboard-grid">
        <div class="stat-card stat-card-publicante">
            <i class="fas fa-list stat-icon" style="color: var(--color-verde);"></i>
            <h3>Anuncios Activos</h3>
            <div class="stat-number">0</div>
            <p>Publicados actualmente</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-eye stat-icon" style="color: var(--color-azul);"></i>
            <h3>Vistas Totales</h3>
            <div class="stat-number">0</div>
            <p>En todos tus anuncios</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-phone-alt stat-icon" style="color: var(--color-verde);"></i>
            <h3>Contactos</h3>
            <div class="stat-number">0</div>
            <p>Personas interesadas</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-star stat-icon" style="color: var(--color-naranja);"></i>
            <h3>Calificación</h3>
            <div class="stat-number">0.0</div>
            <p>De 5.0 estrellas</p>
        </div>
    </div>

    <!-- Lista de Anuncios -->
    <div class="anuncios-list">
        <div class="list-header">
            <h3>Tus Anuncios Publicados</h3>
            <a href="#" class="btn-small">Ver todos</a>
        </div>
        
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Aún no tienes anuncios publicados</p>
            <a href="#" class="link-create">
                <i class="fas fa-plus-circle"></i> Crear tu primer anuncio
            </a>
        </div>
    </div>

    <!-- Tips para Mejores Resultados -->
    <div class="tips-section">
        <h3><i class="fas fa-lightbulb"></i> Tips para mejores resultados</h3>
        <div class="tips-grid">
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Agrega fotos de calidad</strong>
                    <p>Los anuncios con fotos reciben 10x más contactos</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Completa tu perfil</strong>
                    <p>Los clientes confían más en perfiles completos</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Describe tus servicios</strong>
                    <p>Sé específico sobre lo que ofreces</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Responde rápido</strong>
                    <p>Contesta los mensajes en menos de 24 horas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="quick-actions-basic">
        <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
        <div class="actions-grid">
            <a href="#" class="action-card">
                <i class="fas fa-plus-circle" style="color: var(--color-verde);"></i>
                <h4>Crear Anuncio</h4>
                <p>Publica un nuevo servicio</p>
            </a>
            <a href="#" class="action-card">
                <i class="fas fa-user-edit" style="color: var(--color-azul);"></i>
                <h4>Editar Perfil</h4>
                <p>Actualiza tu información</p>
            </a>
            <a href="#" class="action-card">
                <i class="fas fa-chart-line" style="color: var(--color-naranja);"></i>
                <h4>Ver Estadísticas</h4>
                <p>Revisa tu rendimiento</p>
            </a>
        </div>
    </div>
</section>
