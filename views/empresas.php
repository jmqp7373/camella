<?php 
$pageTitle = "Empresas Registradas"; 
?>

<div class="empresas-header">
    <h1 class="page-title">
        <i class="fas fa-building"></i> 
        Empresas Registradas
    </h1>
    <p class="page-subtitle">
        Conoce las empresas líderes que confían en Camella.com.co para encontrar el mejor talento
    </p>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <div class="filter-group">
        <input type="text" id="search-company" placeholder="Buscar empresa..." class="search-input">
        <select id="sector-filter" class="filter-select">
            <option value="">Todos los sectores</option>
            <option value="tecnologia">Tecnología</option>
            <option value="salud">Salud</option>
            <option value="finanzas">Finanzas</option>
            <option value="educacion">Educación</option>
            <option value="retail">Retail</option>
            <option value="manufactura">Manufactura</option>
        </select>
        <select id="size-filter" class="filter-select">
            <option value="">Todos los tamaños</option>
            <option value="startup">Startup (1-10)</option>
            <option value="pequena">Pequeña (11-50)</option>
            <option value="mediana">Mediana (51-200)</option>
            <option value="grande">Grande (200+)</option>
        </select>
        <button class="btn btn-primary" onclick="filterCompanies()">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </div>
</div>

<!-- Grid de empresas -->
<div class="companies-grid" id="companies-container">
    <!-- Empresa 1 -->
    <div class="company-card" data-sector="tecnologia" data-size="grande">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-laptop-code" style="font-size: 3rem; color: var(--color-azul);"></i>
            </div>
            <div class="company-info">
                <h3>TechSolutions Colombia</h3>
                <p class="company-sector">Tecnología e Informática</p>
                <p class="company-size"><i class="fas fa-users"></i> 500+ empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Líder en desarrollo de software empresarial y soluciones tecnológicas innovadoras para el mercado colombiano.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">25</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.8</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>

    <!-- Empresa 2 -->
    <div class="company-card" data-sector="salud" data-size="grande">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-heartbeat" style="font-size: 3rem; color: var(--color-rojo);"></i>
            </div>
            <div class="company-info">
                <h3>Salud Integral S.A.</h3>
                <p class="company-sector">Salud y Medicina</p>
                <p class="company-size"><i class="fas fa-users"></i> 1200+ empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Red hospitalaria con presencia nacional, comprometida con la excelencia en atención médica y cuidado integral.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">18</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.7</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>

    <!-- Empresa 3 -->
    <div class="company-card" data-sector="finanzas" data-size="mediana">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-chart-line" style="font-size: 3rem; color: var(--success);"></i>
            </div>
            <div class="company-info">
                <h3>Inversiones del Caribe</h3>
                <p class="company-sector">Finanzas e Inversiones</p>
                <p class="company-size"><i class="fas fa-users"></i> 150 empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Firma de inversiones especializada en gestión de portafolios y asesoría financiera empresarial en la región Caribe.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">12</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.6</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>

    <!-- Empresa 4 -->
    <div class="company-card" data-sector="educacion" data-size="mediana">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-graduation-cap" style="font-size: 3rem; color: var(--color-amarillo);"></i>
            </div>
            <div class="company-info">
                <h3>Universidad Innovadora</h3>
                <p class="company-sector">Educación Superior</p>
                <p class="company-size"><i class="fas fa-users"></i> 300 empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Institución educativa líder en programas de pregrado y postgrado con enfoque en innovación y tecnología.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">8</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.9</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>

    <!-- Empresa 5 -->
    <div class="company-card" data-sector="retail" data-size="grande">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--color-azul);"></i>
            </div>
            <div class="company-info">
                <h3>MegaRetail Colombia</h3>
                <p class="company-sector">Comercio y Retail</p>
                <p class="company-size"><i class="fas fa-users"></i> 2500+ empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Cadena de retail líder con presencia en todo el territorio nacional, especializada en productos de consumo masivo.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">45</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.5</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>

    <!-- Empresa 6 -->
    <div class="company-card" data-sector="manufactura" data-size="grande">
        <div class="company-header">
            <div class="company-logo">
                <i class="fas fa-industry" style="font-size: 3rem; color: var(--color-rojo);"></i>
            </div>
            <div class="company-info">
                <h3>Manufactura Industrial</h3>
                <p class="company-sector">Manufactura e Industria</p>
                <p class="company-size"><i class="fas fa-users"></i> 800 empleados</p>
            </div>
        </div>
        <div class="company-description">
            <p>Empresa manufacturera con 40 años de experiencia en producción industrial y exportación a mercados internacionales.</p>
        </div>
        <div class="company-stats">
            <div class="stat">
                <span class="stat-number">22</span>
                <span class="stat-label">Ofertas Activas</span>
            </div>
            <div class="stat">
                <span class="stat-number">4.4</span>
                <span class="stat-label">Rating</span>
            </div>
        </div>
        <div class="company-actions">
            <button class="btn btn-primary">Ver Ofertas</button>
            <button class="btn btn-outline">Seguir</button>
        </div>
    </div>
</div>

<!-- Call to action para empresas -->
<div class="cta-empresas">
    <div class="cta-content">
        <h3><i class="fas fa-handshake"></i> ¿Tu empresa no está aquí?</h3>
        <p>Únete a las empresas líderes que ya están encontrando el mejor talento en Camella.com.co</p>
        <a href="index.php?view=registro-empresa" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Registrar mi Empresa
        </a>
    </div>
</div>

<style>
/* Estilos específicos para la página de empresas */
.empresas-header {
    text-align: center;
    margin-bottom: 3rem;
}

.search-filters {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 3rem;
}

.filter-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.search-input, .filter-select {
    padding: 0.8rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    min-width: 200px;
}

.search-input:focus, .filter-select:focus {
    outline: none;
    border-color: #3a8be8;
}

.companies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.company-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.company-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    border-color: #3a8be8;
}

.company-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.company-logo {
    flex-shrink: 0;
}

.company-info h3 {
    color: #333;
    margin-bottom: 0.5rem;
    font-size: 1.4rem;
}

.company-sector {
    color: #3a8be8;
    font-weight: 600;
    margin-bottom: 0.3rem;
}

.company-size {
    color: #6c757d;
    font-size: 0.9rem;
}

.company-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.company-stats {
    display: flex;
    gap: 2rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #3a8be8;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
}

.company-actions {
    display: flex;
    gap: 1rem;
}

.btn-outline {
    background: transparent;
    border: 2px solid #3a8be8;
    color: #3a8be8;
}

.btn-outline:hover {
    background: #3a8be8;
    color: white;
}

.cta-empresas {
    background: linear-gradient(135deg, #3a8be8 0%, #3a8be8 100%);
    color: white;
    padding: 3rem;
    border-radius: 15px;
    text-align: center;
    margin: 3rem 0;
}

.cta-content h3 {
    font-size: 1.8rem;
    margin-bottom: 1rem;
}

.cta-content p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
    opacity: 0.9;
}

@media (max-width: 768px) {
    .filter-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input, .filter-select {
        min-width: auto;
        width: 100%;
    }
    
    .companies-grid {
        grid-template-columns: 1fr;
    }
    
    .company-stats {
        justify-content: center;
    }
    
    .company-actions {
        flex-direction: column;
    }
}
</style>

<script>
function filterCompanies() {
    const searchTerm = document.getElementById('search-company').value.toLowerCase();
    const sectorFilter = document.getElementById('sector-filter').value;
    const sizeFilter = document.getElementById('size-filter').value;
    const companies = document.querySelectorAll('.company-card');
    
    companies.forEach(company => {
        const companyName = company.querySelector('h3').textContent.toLowerCase();
        const companySector = company.getAttribute('data-sector');
        const companySize = company.getAttribute('data-size');
        
        const matchesSearch = companyName.includes(searchTerm);
        const matchesSector = !sectorFilter || companySector === sectorFilter;
        const matchesSize = !sizeFilter || companySize === sizeFilter;
        
        if (matchesSearch && matchesSector && matchesSize) {
            company.style.display = 'block';
            company.style.animation = 'fadeIn 0.5s ease';
        } else {
            company.style.display = 'none';
        }
    });
}

// Filtrado en tiempo real para el campo de búsqueda
document.getElementById('search-company').addEventListener('input', filterCompanies);
document.getElementById('sector-filter').addEventListener('change', filterCompanies);
document.getElementById('size-filter').addEventListener('change', filterCompanies);

// Animación para las tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.company-card');
    cards.forEach((card, index) => {
        card.style.animation = `slideInUp 0.6s ease ${index * 0.1}s both`;
    });
});
</script>
