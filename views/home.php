<?php 
/**
 * Vista principal - Home con maquetación original y contenido dinámico
 * Mantiene la estructura visual original pero carga datos desde BD
 */

$pageTitle = "Inicio";

// Cargar categorías dinámicas desde la base de datos
$categorias = [];
try {
    require_once 'models/Categorias.php';
    $categoriasModel = new Categorias();
    $categorias = $categoriasModel->obtenerCategoriasConOficios();
} catch (Exception $e) {
    error_log("Error cargando categorías en home: " . $e->getMessage());
    $categorias = [];
}
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

<!-- Árbol de Categorías de Empleo - Contenido Dinámico con Maquetación Original -->
<section class="categories-section">
    <h2 class="section-title text-azul">
        <i class="fas fa-th-large"></i> 
        Categorías de Oficios y Servicios
    </h2>

    <div class="categories-tree">
        <?php if (!empty($categorias)): ?>
            <?php foreach ($categorias as $categoria): ?>
                <div class="category-card" data-categoria-id="<?= $categoria['id'] ?>">
                    <h3 class="category-title">
                        <span class="category-icon"><?= htmlspecialchars($categoria['icono']) ?></span>
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </h3>
                    
                    <?php if (!empty($categoria['oficios'])): ?>
                        <ul class="subcategories">
                            <?php foreach ($categoria['oficios'] as $oficio): ?>
                                <li class="oficio-item" data-oficio-id="<?= $oficio['id'] ?>"><?= htmlspecialchars($oficio['nombre']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="subcategories">
                            <li style="font-style: italic; color: #999;">No hay oficios registrados</li>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback: mostrar mensaje de carga o error -->
            <div class="category-card">
                <h3 class="category-title">
                    <span class="category-icon">⚙️</span>
                    Sistema inicializándose...
                </h3>
                <ul class="subcategories">
                    <li style="font-style: italic; color: #666;">
                        Las categorías se están cargando. 
                        <a href="javascript:location.reload()" style="color: #007bff;">Actualizar página</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Llamadas a la acción -->
<section class="cta-section">
    <div class="cta-grid">
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-user-tie"></i></div>
            <h3>¿Buscas Talento?</h3>
            <p>Encuentra profesionales calificados para tu empresa o proyecto.</p>
            <a href="index.php?view=publicar-oferta" class="btn btn-primary">Publicar Oferta</a>
        </div>
        
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-search"></i></div>
            <h3>¿Buscas Trabajo?</h3>
            <p>Explora miles de ofertas laborales en toda Colombia.</p>
            <a href="index.php?view=buscar-empleo" class="btn btn-secondary">Buscar Empleo</a>
        </div>
        
        <div class="cta-card">
            <div class="cta-icon"><i class="fas fa-users"></i></div>
            <h3>Únete a Nuestra Red</h3>
            <p>Forma parte de la comunidad laboral más grande de Colombia.</p>
            <a href="index.php?view=registro" class="btn btn-accent">Registrarse Gratis</a>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number">
                <span class="counter" data-target="1250">0</span>+
            </div>
            <div class="stat-label">Ofertas Activas</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-number">
                <span class="counter" data-target="3500">0</span>+
            </div>
            <div class="stat-label">Profesionales Registrados</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-number">
                <span class="counter" data-target="890">0</span>+
            </div>
            <div class="stat-label">Empresas Confían en Nosotros</div>
        </div>
        
        <div class="stat-item">
            <div class="stat-number">
                <span class="counter" data-target="2100">0</span>+
            </div>
            <div class="stat-label">Conexiones Exitosas</div>
        </div>
    </div>
</section>

<!-- JavaScript para interactividad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar clic en categorías dinámicas
    const categoryCards = document.querySelectorAll('.category-card[data-categoria-id]');
    
    categoryCards.forEach(card => {
        const categoriaId = card.dataset.categoriaId;
        
        // Clic en la categoría completa
        card.addEventListener('click', function(e) {
            // No procesar si se hizo clic en un oficio específico
            if (e.target.classList.contains('oficio-item')) {
                return;
            }
            
            console.log('Categoría seleccionada:', categoriaId);
            
            // Aquí se puede agregar lógica para navegar o filtrar
            // Por ejemplo: window.location.href = `index.php?view=buscar-empleo&categoria=${categoriaId}`;
        });
    });
    
    // Manejar clic en oficios específicos
    const oficios = document.querySelectorAll('.oficio-item');
    oficios.forEach(oficio => {
        oficio.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar que se dispare el evento de la categoría
            
            const oficioId = this.dataset.oficioId;
            const categoriaId = this.closest('.category-card').dataset.categoriaId;
            
            console.log('Oficio seleccionado:', oficioId, 'de categoría:', categoriaId);
            
            // Navegar a búsqueda específica
            // window.location.href = `index.php?view=buscar-empleo&categoria=${categoriaId}&oficio=${oficioId}`;
        });
    });
    
    // Animación de contadores
    const counters = document.querySelectorAll('.counter');
    const observerOptions = {
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.target);
                
                animateCounter(counter, 0, target, 2000);
                observer.unobserve(counter);
            }
        });
    }, observerOptions);
    
    counters.forEach(counter => observer.observe(counter));
    
    function animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        
        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const current = Math.floor(progress * (end - start) + start);
            element.textContent = current;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            }
        }
        
        requestAnimationFrame(updateCounter);
    }
    
    console.log('Sistema de categorías dinámicas inicializado');
    console.log('Categorías cargadas:', <?= count($categorias) ?>);
});

// Función para cargar categorías vía API (si es necesario)
function cargarCategoriasAPI() {
    fetch('index.php?api=categorias')
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                console.log('Categorías cargadas:', data.datos);
                // Aquí se puede actualizar el DOM si es necesario
            } else {
                console.error('Error cargando categorías:', data.mensaje);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}
</script>

<style>
/* Estilos adicionales para elementos dinámicos manteniendo maquetación original */
.category-card {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.oficio-item {
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.oficio-item:hover {
    background-color: var(--amarillo-colombia);
    color: var(--azul-fondo);
    font-weight: 500;
}

.no-categories,
.no-oficios {
    text-align: center;
    padding: 2rem;
    color: var(--color-gris);
    font-style: italic;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    border-left: 4px solid;
}

.alert-info {
    background-color: rgba(52, 144, 220, 0.1);
    border-left-color: var(--azul-fondo);
    color: var(--azul-fondo);
}

.alert i {
    margin-right: 0.5rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .category-card {
        margin-bottom: 1rem;
    }
    
    .oficio-item {
        padding: 0.5rem;
        margin: 0.25rem 0;
        border-radius: 6px;
        background-color: rgba(255, 255, 255, 0.5);
    }
}
</style>