<?php 
$pageTitle = "Inicio"; 
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
    <p class="page-subtitle" style="margin-bottom: 10px; line-height: 1.5;">
        Si sabes hacer algo, aquí puedes camellar.
    </p>
    
    <!-- Lema con corazón colombiano -->
    <div class="lema-container">
        <p class="lema-camella">
            <i class="fas fa-heart heart-colombia"></i>
            "La forma más fácil de camellar en Colombia"
        </p>
    </div>
</div>

<!-- Árbol de Categorías de Empleo -->
<section class="categories-section">
    <h2 class="section-title">
        <i class="fas fa-sitemap"></i> 
        Explora prestadores de servicios por Categoría
    </h2>
    
    <div class="categories-tree">
        <!-- Tecnología e Informática -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">💻</span>
                Tecnología e Informática
            </h3>
            <ul class="subcategories">
                <li>Desarrollo de Software</li>
                <li>Análisis de Datos</li>
                <li>Ciberseguridad</li>
                <li>Diseño UX/UI</li>
                <li>Administración de Sistemas</li>
                <li>Inteligencia Artificial</li>
                <li>DevOps</li>
                <li>Soporte Técnico</li>
            </ul>
        </div>

        <!-- Ingeniería -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">⚙️</span>
                Ingeniería
            </h3>
            <ul class="subcategories">
                <li>Ingeniería Civil</li>
                <li>Ingeniería Industrial</li>
                <li>Ingeniería Mecánica</li>
                <li>Ingeniería Eléctrica</li>
                <li>Ingeniería Ambiental</li>
                <li>Ingeniería de Sistemas</li>
                <li>Ingeniería Química</li>
                <li>Control de Calidad</li>
            </ul>
        </div>

        <!-- Salud y Medicina -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">🏥</span>
                Salud y Medicina
            </h3>
            <ul class="subcategories">
                <li>Medicina General</li>
                <li>Enfermería</li>
                <li>Odontología</li>
                <li>Fisioterapia</li>
                <li>Psicología</li>
                <li>Farmacia</li>
                <li>Laboratorio Clínico</li>
                <li>Administración Hospitalaria</li>
            </ul>
        </div>

        <!-- Educación -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">🎓</span>
                Educación
            </h3>
            <ul class="subcategories">
                <li>Educación Preescolar</li>
                <li>Educación Primaria</li>
                <li>Educación Secundaria</li>
                <li>Educación Superior</li>
                <li>Educación Especial</li>
                <li>Capacitación Corporativa</li>
                <li>Investigación Educativa</li>
                <li>Administración Educativa</li>
            </ul>
        </div>

        <!-- Finanzas y Contabilidad -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">📊</span>
                Finanzas y Contabilidad
            </h3>
            <ul class="subcategories">
                <li>Contabilidad General</li>
                <li>Auditoría</li>
                <li>Análisis Financiero</li>
                <li>Banca e Inversiones</li>
                <li>Seguros</li>
                <li>Tesorería</li>
                <li>Costos y Presupuestos</li>
                <li>Tributaria</li>
            </ul>
        </div>

        <!-- Marketing y Ventas -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">📈</span>
                Marketing y Ventas
            </h3>
            <ul class="subcategories">
                <li>Marketing Digital</li>
                <li>Ventas Directas</li>
                <li>Publicidad</li>
                <li>Community Management</li>
                <li>SEO/SEM</li>
                <li>E-commerce</li>
                <li>Relaciones Públicas</li>
                <li>Trade Marketing</li>
            </ul>
        </div>

        <!-- Recursos Humanos -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">👥</span>
                Recursos Humanos
            </h3>
            <ul class="subcategories">
                <li>Reclutamiento y Selección</li>
                <li>Capacitación y Desarrollo</li>
                <li>Compensación y Beneficios</li>
                <li>Relaciones Laborales</li>
                <li>Seguridad y Salud Ocupacional</li>
                <li>Gestión del Talento</li>
                <li>HR Analytics</li>
                <li>Cultura Organizacional</li>
            </ul>
        </div>

        <!-- Administración y Dirección -->
        <div class="category-card">
            <h3 class="category-title">
                <span class="category-icon">🏢</span>
                Administración y Dirección
            </h3>
            <ul class="subcategories">
                <li>Gerencia General</li>
                <li>Administración</li>
                <li>Coordinación</li>
                <li>Asistencia Ejecutiva</li>
                <li>Gestión de Proyectos</li>
                <li>Consultoría</li>
                <li>Planeación Estratégica</li>
                <li>Operaciones</li>
            </ul>
        </div>
    </div>
</section>

<!-- Llamadas a la acción -->
<section class="cta-section">
    <div class="cta-grid">
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-user-tie"></i></div>
            <h3>¿Buscas Empleo?</h3>
            <p>Encuentra tu próxima oportunidad profesional entre miles de ofertas de las mejores empresas.</p>
            <a href="index.php?view=buscar-empleo" class="btn btn-primary">
                <i class="fas fa-search"></i> Buscar Empleos
            </a>
        </div>
        
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-building"></i></div>
            <h3>¿Eres una Empresa?</h3>
            <p>Conecta con los mejores talentos y encuentra al candidato perfecto para tu organización.</p>
            <a href="index.php?view=publicar-oferta" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Publicar Oferta
            </a>
        </div>
        
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-star"></i></div>
            <h3>Freelancers</h3>
            <p>Ofrece tus servicios profesionales y conecta con empresas que buscan tu expertise.</p>
            <a href="index.php?view=talentos" class="btn btn-info">
                <i class="fas fa-rocket"></i> Ver Talentos
            </a>
        </div>
    </div>
</section>

<style>
/* Estilos específicos para la página de inicio */
.home-hero {
    text-align: center;
    margin-bottom: 3rem;
}

.stats-section {
    margin: 3rem 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: var(--gradiente-header);
    color: var(--color-blanco);
    text-align: center;
    padding: var(--spacing-xl);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--sombra-azul);
    transition: var(--transition-normal);
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1rem;
    opacity: 0.9;
}

.section-title {
    text-align: center;
    color: var(--color-azul);
    font-size: 2rem;
    margin-bottom: var(--spacing-xl);
    background: var(--gradiente-header);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.categories-section {
    margin: 4rem 0;
}

.cta-section {
    margin: 4rem 0 2rem;
}

.cta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.cta-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.cta-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
    transition: left 0.5s;
}

.cta-card:hover::before {
    left: 100%;
}

.cta-card:hover {
    border-color: var(--color-amarillo);
    box-shadow: var(--sombra-amarillo);
    transform: translateY(-5px);
}

.cta-icon {
    font-size: 3rem;
    color: var(--color-azul);
    margin-bottom: var(--spacing-md);
}

.cta-card h3 {
    color: var(--color-azul);
    margin-bottom: var(--spacing-md);
    font-size: 1.5rem;
}

.cta-card p {
    color: var(--color-gris);
    margin-bottom: var(--spacing-lg);
    line-height: 1.6;
}

.btn-primary {
    background: var(--color-azul);
}

.btn-primary:hover {
    background: var(--azul-claro);
}

.btn-info {
    background: #17a2b8;
}

.btn-info:hover {
    background: #138496;
}
</style>
