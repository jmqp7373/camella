<!-- BLOQUE PROMOTOR: Estadísticas y herramientas de promoción -->
<?php
// Este bloque es visible para usuarios con rol 'promotor' o 'admin'
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'promotor'])) {
    return; // No mostrar si no es promotor o admin
}
?>

<!-- Estadísticas de Promoción -->
<section class="promotor-section">
    <h2><i class="fas fa-bullhorn"></i> Herramientas de Promoción</h2>
    
    <div class="dashboard-grid">
        <div class="stat-card stat-card-promotor">
            <i class="fas fa-ad stat-icon" style="color: var(--color-azul);"></i>
            <h3>Anuncios Promovidos</h3>
            <div class="stat-number">0</div>
            <p>Anuncios destacados activos</p>
        </div>

        <div class="stat-card stat-card-promotor">
            <i class="fas fa-eye stat-icon" style="color: var(--color-azul);"></i>
            <h3>Vistas Generadas</h3>
            <div class="stat-number">0</div>
            <p>Últimos 30 días</p>
        </div>

        <div class="stat-card stat-card-promotor">
            <i class="fas fa-phone stat-icon" style="color: var(--color-verde);"></i>
            <h3>Contactos Recibidos</h3>
            <div class="stat-number">0</div>
            <p>Leads generados este mes</p>
        </div>

        <div class="stat-card stat-card-promotor">
            <i class="fas fa-dollar-sign stat-icon" style="color: var(--color-naranja);"></i>
            <h3>Inversión Activa</h3>
            <div class="stat-number">$0</div>
            <p>En campañas activas</p>
        </div>
    </div>

    <!-- Características Premium para Promotores -->
    <div class="premium-features">
        <h3><i class="fas fa-crown"></i> Características Premium</h3>
        <div class="features-grid">
            <div class="feature-item">
                <i class="fas fa-star"></i>
                <h4>Anuncios Destacados</h4>
                <p>Aparece en la parte superior de las búsquedas</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-chart-line"></i>
                <h4>Estadísticas Avanzadas</h4>
                <p>Analítica detallada de rendimiento</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-badge-check"></i>
                <h4>Insignia Verificado</h4>
                <p>Mayor confianza con los clientes</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-images"></i>
                <h4>Más Fotos</h4>
                <p>Hasta 10 fotos por anuncio</p>
            </div>
        </div>
    </div>
</section>
