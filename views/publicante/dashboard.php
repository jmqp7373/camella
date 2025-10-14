<?php 
/**
 * Panel de Publicante - Dashboard
 * Vista principal para usuarios publicantes
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'publicante') {
    header('Location: ../../index.php');
    exit;
}

$pageTitle = "Mi Panel";
include '../../partials/header.php';
?>

<style>
.publicante-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

.publicante-header {
    background: linear-gradient(135deg, var(--color-verde), #27ae60);
    color: white;
    padding: 30px;
    border-radius: var(--border-radius);
    margin-bottom: 30px;
    box-shadow: var(--shadow-card);
}

.publicante-title {
    margin: 0 0 10px 0;
    font-size: 28px;
    font-weight: 600;
}

.publicante-subtitle {
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
    border-left: 4px solid var(--color-verde);
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
    color: var(--color-verde);
    margin: 10px 0;
}

.stat-icon {
    float: right;
    font-size: 40px;
    opacity: 0.2;
    color: var(--color-verde);
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

.cta-card {
    background: linear-gradient(135deg, rgba(var(--color-verde-rgb), 0.1), rgba(var(--color-azul-rgb), 0.1));
    padding: 30px;
    border-radius: var(--border-radius);
    text-align: center;
    margin-bottom: 30px;
}

.cta-card h2 {
    color: var(--color-azul);
    margin-bottom: 15px;
}

.cta-card p {
    font-size: 16px;
    margin-bottom: 20px;
}

.btn-large {
    display: inline-block;
    padding: 15px 40px;
    background: var(--color-verde);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    font-size: 18px;
    font-weight: 600;
    transition: var(--transition);
}

.btn-large:hover {
    background: var(--color-verde-oscuro);
    transform: translateY(-2px);
    box-shadow: var(--shadow-card);
}

.tips-list {
    list-style: none;
    padding: 0;
}

.tips-list li {
    padding: 12px 0;
    border-bottom: 1px solid var(--color-gris-claro);
}

.tips-list li:last-child {
    border-bottom: none;
}

.tips-list i {
    color: var(--color-verde);
    margin-right: 10px;
    width: 20px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--color-gris-oscuro);
}

.empty-state i {
    font-size: 60px;
    color: var(--color-gris-claro);
    margin-bottom: 20px;
}
</style>

<div class="publicante-container">
    <div class="publicante-header">
        <h1 class="publicante-title">
            <i class="fas fa-user-circle"></i>
            Mi Panel
        </h1>
        <p class="publicante-subtitle">Bienvenido, <?= htmlspecialchars($_SESSION['phone'] ?? '') ?></p>
    </div>

    <!-- Estadísticas -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <i class="fas fa-briefcase stat-icon"></i>
            <h3>Mis Anuncios</h3>
            <div class="stat-number">0</div>
            <p>Anuncios publicados</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-eye stat-icon"></i>
            <h3>Vistas</h3>
            <div class="stat-number">0</div>
            <p>Últimos 30 días</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-phone stat-icon"></i>
            <h3>Contactos</h3>
            <div class="stat-number">0</div>
            <p>Personas interesadas</p>
        </div>

        <div class="stat-card">
            <i class="fas fa-star stat-icon"></i>
            <h3>Calificación</h3>
            <div class="stat-number">0.0</div>
            <p>De 5.0 estrellas</p>
        </div>
    </div>

    <!-- CTA Principal -->
    <div class="cta-card">
        <h2><i class="fas fa-plus-circle"></i> ¡Publica tu primer anuncio!</h2>
        <p>Comienza a ofrecer tus servicios y conecta con miles de clientes potenciales</p>
        <a href="#" class="btn-large">
            <i class="fas fa-plus"></i> Crear Anuncio
        </a>
    </div>

    <!-- Sección de mis anuncios -->
    <div class="welcome-card">
        <h2><i class="fas fa-list"></i> Mis Anuncios</h2>
        
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Aún no tienes anuncios publicados</p>
            <a href="#" style="color: var(--color-verde); text-decoration: none;">
                <i class="fas fa-plus-circle"></i> Crear mi primer anuncio
            </a>
        </div>
    </div>

    <!-- Tips para mejores resultados -->
    <div class="welcome-card">
        <h2><i class="fas fa-lightbulb"></i> Tips para mejores resultados</h2>
        <ul class="tips-list">
            <li>
                <i class="fas fa-check-circle"></i>
                <strong>Agrega fotos de calidad:</strong> Los anuncios con fotos reciben 10x más contactos
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <strong>Completa tu perfil:</strong> Los clientes confían más en perfiles completos
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <strong>Describe tus servicios:</strong> Sé específico sobre lo que ofreces
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <strong>Responde rápido:</strong> Contesta los mensajes en menos de 24 horas
            </li>
            <li>
                <i class="fas fa-check-circle"></i>
                <strong>Mantén tus precios actualizados:</strong> Revisa tus tarifas regularmente
            </li>
        </ul>
    </div>

    <!-- Acciones rápidas -->
    <div class="dashboard-grid">
        <a href="#" class="stat-card" style="text-decoration: none; cursor: pointer; transition: var(--transition);">
            <i class="fas fa-plus-circle" style="color: var(--color-verde); font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="color: var(--color-azul);">Crear Anuncio</h3>
            <p>Publica un nuevo servicio</p>
        </a>

        <a href="#" class="stat-card" style="text-decoration: none; cursor: pointer; transition: var(--transition);">
            <i class="fas fa-user-edit" style="color: var(--color-azul); font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="color: var(--color-azul);">Editar Perfil</h3>
            <p>Actualiza tu información</p>
        </a>

        <a href="#" class="stat-card" style="text-decoration: none; cursor: pointer; transition: var(--transition);">
            <i class="fas fa-chart-line" style="color: var(--color-naranja); font-size: 40px; margin-bottom: 10px;"></i>
            <h3 style="color: var(--color-azul);">Ver Estadísticas</h3>
            <p>Revisa tu rendimiento</p>
        </a>
    </div>
</div>

<?php include '../../partials/footer.php'; ?>
