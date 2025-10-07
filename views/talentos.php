<?php 
$pageTitle = "Talentos Profesionales"; 
?>

<div class="talentos-header">
    <h1 class="page-title">
        <i class="fas fa-users"></i> 
        Talentos Profesionales
    </h1>
    <p class="page-subtitle">
        Descubre profesionales excepcionales listos para impulsar tu empresa al siguiente nivel
    </p>
</div>

<!-- Filtros de búsqueda -->
<div class="search-filters">
    <div class="filter-group">
        <input type="text" id="search-talent" placeholder="Buscar por nombre o habilidad..." class="search-input">
        <select id="profession-filter" class="filter-select">
            <option value="">Todas las profesiones</option>
            <option value="desarrollador">Desarrollador</option>
            <option value="disenador">Diseñador</option>
            <option value="marketing">Marketing</option>
            <option value="contabilidad">Contabilidad</option>
            <option value="ingenieria">Ingeniería</option>
            <option value="medicina">Medicina</option>
        </select>
        <select id="experience-filter" class="filter-select">
            <option value="">Toda experiencia</option>
            <option value="junior">Junior (0-2 años)</option>
            <option value="mid">Mid (3-5 años)</option>
            <option value="senior">Senior (6+ años)</option>
        </select>
        <select id="availability-filter" class="filter-select">
            <option value="">Disponibilidad</option>
            <option value="inmediata">Inmediata</option>
            <option value="1mes">En 1 mes</option>
            <option value="3meses">En 3 meses</option>
        </select>
        <button class="btn btn-primary" onclick="filterTalents()">
            <i class="fas fa-search"></i> Filtrar
        </button>
    </div>
</div>

<!-- Grid de talentos -->
<div class="talents-grid" id="talents-container">
    <!-- Talento 1 -->
    <div class="talent-card" data-profession="desarrollador" data-experience="senior" data-availability="inmediata">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #667eea;"></i>
            </div>
            <div class="talent-info">
                <h3>Carlos Mendoza</h3>
                <p class="talent-title">Full Stack Developer</p>
                <div class="talent-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-number">(4.9)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Medellín, Colombia</p>
            </div>
            <div class="talent-status available">
                <span>Disponible</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Desarrollador Full Stack con 8+ años de experiencia en React, Node.js, y arquitecturas cloud. Especializado en aplicaciones escalables y optimización de rendimiento.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">React</span>
            <span class="skill-tag">Node.js</span>
            <span class="skill-tag">AWS</span>
            <span class="skill-tag">MongoDB</span>
            <span class="skill-tag">Python</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">15</span>
                <span class="stat-label">Proyectos</span>
            </div>
            <div class="stat">
                <span class="stat-number">8+</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$2.5M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>

    <!-- Talento 2 -->
    <div class="talent-card" data-profession="disenador" data-experience="mid" data-availability="inmediata">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #e74c3c;"></i>
            </div>
            <div class="talent-info">
                <h3>Ana Rodríguez</h3>
                <p class="talent-title">UX/UI Designer</p>
                <div class="talent-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-number">(4.8)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Medellín, Colombia</p>
            </div>
            <div class="talent-status available">
                <span>Disponible</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Diseñadora UX/UI con 5 años creando experiencias digitales excepcionales. Experta en design thinking y prototipado interactivo.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">Figma</span>
            <span class="skill-tag">Adobe XD</span>
            <span class="skill-tag">Sketch</span>
            <span class="skill-tag">Prototyping</span>
            <span class="skill-tag">User Research</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">25</span>
                <span class="stat-label">Proyectos</span>
            </div>
            <div class="stat">
                <span class="stat-number">5</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$1.8M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>

    <!-- Talento 3 -->
    <div class="talent-card" data-profession="marketing" data-experience="senior" data-availability="1mes">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #28a745;"></i>
            </div>
            <div class="talent-info">
                <h3>Luis García</h3>
                <p class="talent-title">Digital Marketing Manager</p>
                <div class="talent-rating">
                    <span class="stars">★★★★☆</span>
                    <span class="rating-number">(4.7)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Cali, Colombia</p>
            </div>
            <div class="talent-status busy">
                <span>En 1 mes</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Especialista en marketing digital con 7+ años liderando estrategias exitosas. ROI comprobado en campañas de +$500K en presupuesto.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">Google Ads</span>
            <span class="skill-tag">Facebook Ads</span>
            <span class="skill-tag">SEO/SEM</span>
            <span class="skill-tag">Analytics</span>
            <span class="skill-tag">Email Marketing</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">30+</span>
                <span class="stat-label">Campañas</span>
            </div>
            <div class="stat">
                <span class="stat-number">7+</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$2.2M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>

    <!-- Talento 4 -->
    <div class="talent-card" data-profession="contabilidad" data-experience="mid" data-availability="inmediata">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #f39c12;"></i>
            </div>
            <div class="talent-info">
                <h3>María Fernández</h3>
                <p class="talent-title">Contadora Pública</p>
                <div class="talent-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-number">(4.9)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Barranquilla, Colombia</p>
            </div>
            <div class="talent-status available">
                <span>Disponible</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Contadora Pública certificada con 6 años de experiencia en auditoría, tributaria y consultoría financiera para PyMEs y grandes empresas.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">Auditoría</span>
            <span class="skill-tag">NIIF</span>
            <span class="skill-tag">SAP</span>
            <span class="skill-tag">Excel Avanzado</span>
            <span class="skill-tag">Tributaria</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">40+</span>
                <span class="stat-label">Empresas</span>
            </div>
            <div class="stat">
                <span class="stat-number">6</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$1.5M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>

    <!-- Talento 5 -->
    <div class="talent-card" data-profession="ingenieria" data-experience="senior" data-availability="3meses">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #9b59b6;"></i>
            </div>
            <div class="talent-info">
                <h3>Roberto Silva</h3>
                <p class="talent-title">Ingeniero Industrial</p>
                <div class="talent-rating">
                    <span class="stars">★★★★☆</span>
                    <span class="rating-number">(4.6)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Bucaramanga, Colombia</p>
            </div>
            <div class="talent-status unavailable">
                <span>En 3 meses</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Ingeniero Industrial con maestría en optimización de procesos. 10+ años mejorando eficiencia operativa y reduciendo costos.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">Lean Manufacturing</span>
            <span class="skill-tag">Six Sigma</span>
            <span class="skill-tag">AutoCAD</span>
            <span class="skill-tag">Project Management</span>
            <span class="skill-tag">ERP Systems</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">20+</span>
                <span class="stat-label">Proyectos</span>
            </div>
            <div class="stat">
                <span class="stat-number">10+</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$2.8M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>

    <!-- Talento 6 -->
    <div class="talent-card" data-profession="medicina" data-experience="mid" data-availability="inmediata">
        <div class="talent-header">
            <div class="talent-avatar">
                <i class="fas fa-user-circle" style="font-size: 4rem; color: #e67e22;"></i>
            </div>
            <div class="talent-info">
                <h3>Dra. Laura Martín</h3>
                <p class="talent-title">Médica Especialista</p>
                <div class="talent-rating">
                    <span class="stars">★★★★★</span>
                    <span class="rating-number">(5.0)</span>
                </div>
                <p class="talent-location"><i class="fas fa-map-marker-alt"></i> Cartagena, Colombia</p>
            </div>
            <div class="talent-status available">
                <span>Disponible</span>
            </div>
        </div>
        
        <div class="talent-summary">
            <p>Médica especialista en medicina interna con 4 años de experiencia. Certificaciones internacionales y amplia experiencia en telemedicina.</p>
        </div>
        
        <div class="talent-skills">
            <span class="skill-tag">Medicina Interna</span>
            <span class="skill-tag">Telemedicina</span>
            <span class="skill-tag">Urgencias</span>
            <span class="skill-tag">Investigación</span>
            <span class="skill-tag">Inglés Fluido</span>
        </div>
        
        <div class="talent-stats">
            <div class="stat">
                <span class="stat-number">500+</span>
                <span class="stat-label">Pacientes</span>
            </div>
            <div class="stat">
                <span class="stat-number">4</span>
                <span class="stat-label">Años Exp.</span>
            </div>
            <div class="stat">
                <span class="stat-number">$3.5M</span>
                <span class="stat-label">Por hora</span>
            </div>
        </div>
        
        <div class="talent-actions">
            <button class="btn btn-primary">Ver Perfil</button>
            <button class="btn btn-success">Contactar</button>
        </div>
    </div>
</div>

<!-- Call to action para talentos -->
<div class="cta-talentos">
    <div class="cta-content">
        <h3><i class="fas fa-star"></i> ¿Eres un profesional talentoso?</h3>
        <p>Únete a nuestra comunidad de talentos y conecta con las mejores oportunidades laborales</p>
        <a href="index.php?view=registro-talento" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Registrar mi Perfil
        </a>
    </div>
</div>

<style>
/* Estilos específicos para la página de talentos */
.talentos-header {
    text-align: center;
    margin-bottom: 3rem;
}

.talents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.talent-card {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    position: relative;
    overflow: hidden;
}

.talent-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.05), transparent);
    transition: left 0.5s;
}

.talent-card:hover::before {
    left: 100%;
}

.talent-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: #667eea;
}

.talent-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.talent-avatar {
    flex-shrink: 0;
}

.talent-info {
    flex-grow: 1;
}

.talent-info h3 {
    color: #333;
    margin-bottom: 0.3rem;
    font-size: 1.4rem;
}

.talent-title {
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.talent-rating {
    margin-bottom: 0.5rem;
}

.stars {
    color: #ffc107;
    font-size: 1.1rem;
    margin-right: 0.5rem;
}

.rating-number {
    color: #6c757d;
    font-size: 0.9rem;
}

.talent-location {
    color: #6c757d;
    font-size: 0.9rem;
}

.talent-status {
    position: absolute;
    top: 0;
    right: 0;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
}

.talent-status.available {
    background: #d4edda;
    color: #155724;
}

.talent-status.busy {
    background: #fff3cd;
    color: #856404;
}

.talent-status.unavailable {
    background: #f8d7da;
    color: #721c24;
}

.talent-summary {
    color: #666;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.talent-skills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.skill-tag {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.talent-stats {
    display: flex;
    justify-content: space-around;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.talent-stats .stat {
    text-align: center;
}

.talent-stats .stat-number {
    display: block;
    font-size: 1.3rem;
    font-weight: bold;
    color: #667eea;
}

.talent-stats .stat-label {
    font-size: 0.8rem;
    color: #6c757d;
}

.talent-actions {
    display: flex;
    gap: 1rem;
}

.talent-actions .btn {
    flex: 1;
}

.cta-talentos {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    .talents-grid {
        grid-template-columns: 1fr;
    }
    
    .talent-header {
        flex-direction: column;
        text-align: center;
    }
    
    .talent-status {
        position: static;
        align-self: center;
        margin-top: 0.5rem;
    }
    
    .talent-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .talent-actions {
        flex-direction: column;
    }
}
</style>

<script>
function filterTalents() {
    const searchTerm = document.getElementById('search-talent').value.toLowerCase();
    const professionFilter = document.getElementById('profession-filter').value;
    const experienceFilter = document.getElementById('experience-filter').value;
    const availabilityFilter = document.getElementById('availability-filter').value;
    const talents = document.querySelectorAll('.talent-card');
    
    talents.forEach(talent => {
        const talentName = talent.querySelector('h3').textContent.toLowerCase();
        const talentTitle = talent.querySelector('.talent-title').textContent.toLowerCase();
        const talentSkills = talent.querySelector('.talent-skills').textContent.toLowerCase();
        const talentProfession = talent.getAttribute('data-profession');
        const talentExperience = talent.getAttribute('data-experience');
        const talentAvailability = talent.getAttribute('data-availability');
        
        const matchesSearch = talentName.includes(searchTerm) || 
                            talentTitle.includes(searchTerm) || 
                            talentSkills.includes(searchTerm);
        const matchesProfession = !professionFilter || talentProfession === professionFilter;
        const matchesExperience = !experienceFilter || talentExperience === experienceFilter;
        const matchesAvailability = !availabilityFilter || talentAvailability === availabilityFilter;
        
        if (matchesSearch && matchesProfession && matchesExperience && matchesAvailability) {
            talent.style.display = 'block';
            talent.style.animation = 'fadeIn 0.5s ease';
        } else {
            talent.style.display = 'none';
        }
    });
}

// Filtrado en tiempo real
document.getElementById('search-talent').addEventListener('input', filterTalents);
document.getElementById('profession-filter').addEventListener('change', filterTalents);
document.getElementById('experience-filter').addEventListener('change', filterTalents);
document.getElementById('availability-filter').addEventListener('change', filterTalents);

// Animaciones de entrada
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.talent-card');
    cards.forEach((card, index) => {
        card.style.animation = `slideInUp 0.6s ease ${index * 0.1}s both`;
    });
});
</script>