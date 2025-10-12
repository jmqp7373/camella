<?php 
/**
 * HOME SIMPLE - Sin modelo de categorías
 */

$pageTitle = "Inicio";

// Categorías hardcoded para evitar errores
$categorias = [
    ['id' => 1, 'nombre' => 'Tecnología', 'icono' => 'fas fa-laptop-code', 'total_oficios' => 5],
    ['id' => 2, 'nombre' => 'Salud', 'icono' => 'fas fa-heartbeat', 'total_oficios' => 3],
    ['id' => 3, 'nombre' => 'Educación', 'icono' => 'fas fa-graduation-cap', 'total_oficios' => 4],
    ['id' => 4, 'nombre' => 'Ventas', 'icono' => 'fas fa-chart-line', 'total_oficios' => 6],
];
?>

<div class="home-hero">
    <h1 class="page-title text-azul" style="margin-bottom: 10px;">
        <i class="fas fa-briefcase"></i> 
        Bienvenido a Camella.com.co
    </h1>
    <p class="page-subtitle" style="margin-bottom: 6px; line-height: 1.5;">
        Camella.com.co es la bolsa de empleo que conecta a Colombia.
    </p>
    <p class="page-subtitle" style="margin-bottom: 4px; line-height: 1.5;">
        Si necesitas algo, aquí hay quien te ayude.
    </p>
    <p class="page-subtitle" style="margin-bottom: 20px; line-height: 1.5;">
        Si puedes hacer algo, aquí hay quien lo necesite.
    </p>

    <div class="cta-buttons">
        <a href="?view=buscar-empleo" class="btn btn-primary btn-lg">
            <i class="fas fa-search"></i> Buscar Empleo
        </a>
        <a href="?view=publicar-oferta" class="btn btn-secondary btn-lg">
            <i class="fas fa-plus-circle"></i> Publicar Oferta
        </a>
    </div>
</div>

<section class="categorias-section">
    <h2 class="section-title">
        <i class="fas fa-list"></i> Explora por Categorías
    </h2>
    
    <div class="categorias-grid">
        <?php foreach ($categorias as $categoria): ?>
            <div class="categoria-card">
                <div class="categoria-icon">
                    <i class="<?php echo htmlspecialchars($categoria['icono']); ?>"></i>
                </div>
                <h3><?php echo htmlspecialchars($categoria['nombre']); ?></h3>
                <p class="categoria-count">
                    <?php echo $categoria['total_oficios']; ?> oficios disponibles
                </p>
                <a href="?view=categoria&id=<?php echo $categoria['id']; ?>" class="btn btn-outline">
                    Ver Ofertas <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">1,247</div>
            <div class="stat-label">Ofertas Activas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">856</div>
            <div class="stat-label">Empresas Registradas</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">3,421</div>
            <div class="stat-label">Candidatos</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">2,890</div>
            <div class="stat-label">Empleos Conseguidos</div>
        </div>
    </div>
</section>