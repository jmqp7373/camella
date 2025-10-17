<?php 
/**
 * Panel de Promotor - Dashboard
 * Vista principal para promotores
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'promotor') {
    header('Location: ../../index.php');
    exit;
}

$pageTitle = "Panel de Promotor";
include '../../partials/header.php';
?>

<style>
.promotor-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.promotor-header {
    background: linear-gradient(135deg, var(--color-azul), var(--color-azul-oscuro));
    color: white;
    padding: 30px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    box-shadow: var(--shadow-card);
}

.promotor-title {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 600;
}

.promotor-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 16px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-card);
    border-left: 4px solid var(--color-azul);
}

.stat-card h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: var(--color-gris-oscuro);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: var(--color-azul);
    margin: 10px 0;
}

.stat-icon {
    float: right;
    font-size: 40px;
    opacity: 0.2;
    color: var(--color-azul);
}

.welcome-card {
    background: white;
    padding: 30px;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-card);
    margin-bottom: 30px;
}

.welcome-card h2 {
    color: var(--color-azul);
    margin-bottom: 15px;
}

.features-list {
    list-style: none;
    padding: 0;
}

.features-list li {
    padding: 10px 0;
    border-bottom: 1px solid var(--color-gris-claro);
}

.features-list li:last-child {
    border-bottom: none;
}

.features-list i {
    color: var(--color-verde);
    margin-right: 10px;
    width: 20px;
}
</style>

<div class="promotor-container">
    <div class="promotor-header">
        <h1 class="promotor-title">
            <i class="fas fa-bullhorn"></i>
            Panel de Promotor
        </h1>
        <p class="promotor-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['phone'] ?? '') ?></p>
    </div>

    <!-- Estadísticas -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <i class="fas fa-ad stat-icon"></i>
            <h3>Anuncios Promovidos</h3>
            <div class="stat-number">0</div>
            <p>Este mes</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-eye stat-icon"></i>
            <h3>Vistas Totales</h3>
            <div class="stat-number">0</div>
            <p>Últimos 30 días</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-phone stat-icon"></i>
            <h3>Contactos Generados</h3>
            <div class="stat-number">0</div>
            <p>Últimos 30 días</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-dollar-sign stat-icon"></i>
            <h3>Inversión Activa</h3>
            <div class="stat-number">$0</div>
            <p>En campañas activas</p>
        </div>
    </div>

    <!-- Información de bienvenida -->
    <div class="welcome-card">
        <h2><i class="fas fa-rocket"></i> Promociona tus servicios</h2>
        <p>Como promotor, puedes destacar tus anuncios y llegar a más clientes potenciales. Aquí están las características disponibles:</p>
        
        <ul class="features-list">
            <li>
                <i class="fas fa-star"></i>
                <strong>Anuncios Destacados:</strong> Aparece en la parte superior de las búsquedas
            </li>
            <li>
                <i class="fas fa-chart-line"></i>
                <strong>Estadísticas Avanzadas:</strong> Conoce el rendimiento de tus anuncios
            </li>
            <li>
                <i class="fas fa-badge-check"></i>
                <strong>Insignia de Verificado:</strong> Genera más confianza con los clientes
            </li>
            <li>
                <i class="fas fa-images"></i>
                <strong>Más Fotos:</strong> Hasta 10 fotos por anuncio
            </li>
            <li>
                <i class="fas fa-headset"></i>
                <strong>Soporte Prioritario:</strong> Atención preferencial para tus consultas
            </li>
        </ul>
    </div>

    <!-- Sección de acciones rápidas -->
    <div class="welcome-card">
        <h2><i class="fas fa-bolt"></i> Acciones Rápidas</h2>
        <div class="dashboard-grid">
            <a href="#" class="btn btn-primary" style="text-decoration: none; display: block; text-align: center; padding: 15px;">
                <i class="fas fa-plus-circle"></i> Crear Anuncio
            </a>
            <a href="#" class="btn btn-secondary" style="text-decoration: none; display: block; text-align: center; padding: 15px;">
                <i class="fas fa-ad"></i> Ver Mis Anuncios
            </a>
            <a href="#" class="btn btn-secondary" style="text-decoration: none; display: block; text-align: center; padding: 15px;">
                <i class="fas fa-chart-bar"></i> Ver Estadísticas
            </a>
        </div>
    </div>
</div>

<!-- Bloque: Crear Anuncio -->
<section class="crear-anuncio-section" style="margin: 2rem auto; padding: 0 1rem; max-width: 1200px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="font-size: 4rem; margin-bottom: 1.5rem;">
                <i class="fas fa-bullhorn" style="color: white; opacity: 0.9;"></i>
            </div>
            <h2 style="color: white; font-size: 2rem; margin-bottom: 1rem; font-weight: 700;">
                ¿Tienes un servicio para ofrecer?
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.6;">
                Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
            </p>
            <a href="<?= app_url('views/bloques/publicar.php') ?>" 
               style="display: inline-block; padding: 1rem 3rem; background: white; color: #667eea; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)';">
                <i class="fas fa-plus-circle"></i> Crear mi anuncio ahora
            </a>
        </div>
    </div>
</section>

<?php include '../../partials/footer.php'; ?>
