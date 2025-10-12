<?php 
/**
 * Vista de Categoría - Muestra ofertas de una categoría específica
 */

$pageTitle = "Categoría";

// Obtener ID de categoría
$categoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Definir categorías disponibles
$categorias_disponibles = [
    1 => ['nombre' => 'Tecnología', 'icono' => 'fas fa-laptop-code'],
    2 => ['nombre' => 'Salud', 'icono' => 'fas fa-heartbeat'],
    3 => ['nombre' => 'Educación', 'icono' => 'fas fa-graduation-cap'],
    4 => ['nombre' => 'Ventas', 'icono' => 'fas fa-chart-line'],
    5 => ['nombre' => 'Construcción', 'icono' => 'fas fa-hard-hat'],
    6 => ['nombre' => 'Hostelería', 'icono' => 'fas fa-utensils'],
    7 => ['nombre' => 'Marketing', 'icono' => 'fas fa-bullhorn'],
    8 => ['nombre' => 'Finanzas', 'icono' => 'fas fa-coins']
];

// Obtener información de la categoría
$categoria_actual = isset($categorias_disponibles[$categoria_id]) 
    ? $categorias_disponibles[$categoria_id] 
    : $categorias_disponibles[1];

$pageTitle = $categoria_actual['nombre'];

// Ofertas de ejemplo para la categoría
$ofertas_ejemplo = [
    ['titulo' => 'Desarrollador Web Full Stack', 'empresa' => 'TechCorp Colombia', 'ubicacion' => 'Bogotá', 'salario' => '$2,500,000 - $4,000,000'],
    ['titulo' => 'Analista de Sistemas', 'empresa' => 'Innovate Solutions', 'ubicacion' => 'Medellín', 'salario' => '$2,200,000 - $3,500,000'],
    ['titulo' => 'Diseñador UX/UI', 'empresa' => 'Creative Digital', 'ubicacion' => 'Cali', 'salario' => '$1,800,000 - $3,000,000'],
    ['titulo' => 'Especialista en Ciberseguridad', 'empresa' => 'SecureNet SA', 'ubicacion' => 'Barranquilla', 'salario' => '$3,000,000 - $5,000,000'],
    ['titulo' => 'Administrador de Base de Datos', 'empresa' => 'DataMax Solutions', 'ubicacion' => 'Bucaramanga', 'salario' => '$2,800,000 - $4,200,000']
];
?>

<div class="categoria-hero">
    <div class="breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Inicio</a> 
        <span class="separator">/</span>
        <span class="current">
            <i class="<?php echo $categoria_actual['icono']; ?>"></i>
            <?php echo htmlspecialchars($categoria_actual['nombre']); ?>
        </span>
    </div>
    
    <h1 class="page-title text-azul">
        <i class="<?php echo $categoria_actual['icono']; ?>"></i>
        Ofertas de <?php echo htmlspecialchars($categoria_actual['nombre']); ?>
    </h1>
    <p class="page-subtitle">
        Descubre las mejores oportunidades laborales en el sector de <?php echo strtolower($categoria_actual['nombre']); ?>
    </p>
</div>

<div class="filtros-section">
    <div class="filtros-container">
        <div class="filtro-item">
            <label for="ubicacion">Ubicación:</label>
            <select id="ubicacion" class="form-control">
                <option value="">Todas las ciudades</option>
                <option value="bogota">Bogotá</option>
                <option value="medellin">Medellín</option>
                <option value="cali">Cali</option>
                <option value="barranquilla">Barranquilla</option>
                <option value="cartagena">Cartagena</option>
            </select>
        </div>
        
        <div class="filtro-item">
            <label for="experiencia">Experiencia:</label>
            <select id="experiencia" class="form-control">
                <option value="">Cualquier nivel</option>
                <option value="junior">Junior (0-2 años)</option>
                <option value="semi-senior">Semi-Senior (2-5 años)</option>
                <option value="senior">Senior (5+ años)</option>
            </select>
        </div>
        
        <div class="filtro-item">
            <button class="btn btn-primary">
                <i class="fas fa-search"></i> Filtrar
            </button>
        </div>
    </div>
</div>

<section class="ofertas-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-briefcase"></i> 
            Ofertas Disponibles (<?php echo count($ofertas_ejemplo); ?>)
        </h2>
        <div class="ordenar-por">
            <label>Ordenar por:</label>
            <select class="form-control">
                <option value="fecha">Más recientes</option>
                <option value="salario">Mejor salario</option>
                <option value="relevancia">Relevancia</option>
            </select>
        </div>
    </div>
    
    <div class="ofertas-grid">
        <?php foreach ($ofertas_ejemplo as $oferta): ?>
            <div class="oferta-card">
                <div class="oferta-header">
                    <h3 class="oferta-titulo"><?php echo htmlspecialchars($oferta['titulo']); ?></h3>
                    <div class="oferta-empresa"><?php echo htmlspecialchars($oferta['empresa']); ?></div>
                </div>
                
                <div class="oferta-details">
                    <div class="oferta-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($oferta['ubicacion']); ?>
                    </div>
                    <div class="oferta-detail">
                        <i class="fas fa-dollar-sign"></i>
                        <?php echo htmlspecialchars($oferta['salario']); ?>
                    </div>
                    <div class="oferta-detail">
                        <i class="fas fa-clock"></i>
                        Publicado hace 2 días
                    </div>
                </div>
                
                <div class="oferta-actions">
                    <button class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </button>
                    <button class="btn btn-secondary btn-sm">
                        <i class="fas fa-heart"></i> Guardar
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="otras-categorias">
    <h3 class="section-title">
        <i class="fas fa-th-large"></i> 
        Explorar Otras Categorías
    </h3>
    
    <div class="categorias-relacionadas">
        <?php foreach ($categorias_disponibles as $id => $cat): ?>
            <?php if ($id != $categoria_id): ?>
                <a href="?view=categoria&id=<?php echo $id; ?>" class="categoria-relacionada">
                    <i class="<?php echo $cat['icono']; ?>"></i>
                    <?php echo $cat['nombre']; ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<div class="cta-section">
    <h3>¿No encuentras lo que buscas?</h3>
    <p>Publica tu hoja de vida y permite que las empresas te encuentren</p>
    <a href="?view=registro" class="btn btn-secondary btn-lg">
        <i class="fas fa-user-plus"></i> Registrarse como Candidato
    </a>
</div>