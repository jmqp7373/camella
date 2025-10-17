<?php 
/**
 * Vista principal - Home con maquetación original y contenido dinámico
 * Mantiene la estructura visual original pero carga datos desde BD
 */

$pageTitle = "Inicio";

// Mostrar mensaje de logout exitoso si viene del logout
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    echo '<div class="alert alert-success" style="max-width: 600px; margin: 20px auto; padding: 15px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 8px; text-align: center;">';
    echo '<i class="fas fa-check-circle"></i> Sesión cerrada exitosamente. ¡Hasta pronto!';
    echo '</div>';
}

// Intentar cargar categorías desde la base de datos
$categorias = []; // Inicializar vacío, se llenará desde BD

try {
    // DEBUG: Mostrar información del directorio actual
    echo "<!-- DEBUG INICIO -->";
    echo "<!-- DEBUG: __DIR__ = " . __DIR__ . " -->";
    
    // Cargar el modelo usando ruta relativa al archivo actual
    $projectRoot = realpath(__DIR__ . '/..');
    $modelPath = $projectRoot . '/models/Categorias.php';
    
    // DEBUG: Mostrar rutas calculadas
    echo "<!-- DEBUG: projectRoot = " . $projectRoot . " -->";
    echo "<!-- DEBUG: modelPath = " . $modelPath . " -->";
    echo "<!-- DEBUG: file_exists(modelPath) = " . (file_exists($modelPath) ? 'TRUE' : 'FALSE') . " -->";

    if (file_exists($modelPath)) {
        echo "<!-- DEBUG: Archivo modelo encontrado, intentando cargar... -->";
        
        require_once $modelPath;
        
        echo "<!-- DEBUG: Modelo cargado, creando instancia... -->";
        $categoriasModel = new Categorias();
        
        echo "<!-- DEBUG: Instancia creada, llamando obtenerCategoriasConOficios()... -->";
        $categoriasDB = $categoriasModel->obtenerCategoriasConOficios();
        
        // DEBUG: Ver qué devuelve la BD
        echo "<!-- DEBUG: categoriasDB type = " . gettype($categoriasDB) . " -->";
        echo "<!-- DEBUG: categoriasDB count = " . (is_array($categoriasDB) ? count($categoriasDB) : 'N/A') . " -->";
        echo "<!-- DEBUG: categoriasDB empty = " . (empty($categoriasDB) ? 'TRUE' : 'FALSE') . " -->";
        
        if (is_array($categoriasDB) && count($categoriasDB) > 0) {
            echo "<!-- DEBUG: Primera categoría: " . json_encode($categoriasDB[0]) . " -->";
        }
        
        // Mapa de íconos por nombre de categoría (si la BD no trae "icono")
        $iconMap = [
            'Aseo y Limpieza' => 'fas fa-broom',
            'Cocina y Preparación de Alimentos' => 'fas fa-utensils',
            'Cuidados y Acompañamiento' => 'fas fa-heart',
            'Mantenimiento y Reparaciones' => 'fas fa-tools',
            'Construcción y Obras' => 'fas fa-hard-hat',
            'Servicios Logísticos y Transporte' => 'fas fa-truck',
            'Belleza y Cuidado Personal' => 'fas fa-spa',
            'Ventas y Atención al Cliente' => 'fas fa-handshake',
            'Oficios Generales / Multiservicios' => 'fas fa-briefcase',
            'Cuidado de Animales' => 'fas fa-paw',
            'Producción y Manufactura' => 'fas fa-industry',
            'Eventos y Actividades Especiales' => 'fas fa-calendar-check',
            // Categorías v1.1
            'Servicios Digitales y Contenidos' => 'fas fa-photo-video',
            'Educación y Formación' => 'fas fa-chalkboard-teacher',
            'Tecnología y Soporte Digital' => 'fas fa-desktop',
            'Agricultura y Medio Ambiente' => 'fas fa-seedling',
            'Administración y Servicios Empresariales' => 'fas fa-folder-open',
            'Moda y Confección' => 'fas fa-cut',
        ];

        if (is_array($categoriasDB) && !empty($categoriasDB)) {
            echo "<!-- DEBUG: Procesando categorías de BD... -->";
            foreach ($categoriasDB as &$c) {
                if (empty($c['icono'])) {
                    $c['icono'] = $iconMap[$c['nombre']] ?? 'fas fa-briefcase';
                }
                if (!isset($c['total_oficios'])) {
                    $c['total_oficios'] = 0; // fallback si el modelo no trae conteo
                }
            }
            unset($c);
            $categorias = $categoriasDB;

            // Asegurar oficios por categoría (BD real)
            if (isset($categoriasModel) && is_array($categorias)) {
                foreach ($categorias as &$cat) {
                    $cat['oficios'] = $categoriasModel->obtenerOficiosPorCategoria((int)$cat['id']);
                }
                unset($cat);
            }

            echo "<!-- DEBUG: Categorías BD asignadas exitosamente -->";
        } else {
            echo "<!-- DEBUG: categoriasDB vacío o inválido, usando fallback -->";
        }
    } else {
        echo "<!-- DEBUG: Archivo modelo NO encontrado -->";
        echo "<!-- DEBUG: Directorio models existe? " . (is_dir($projectRoot . '/models') ? 'TRUE' : 'FALSE') . " -->";
        if (is_dir($projectRoot . '/models')) {
            $files = scandir($projectRoot . '/models');
            echo "<!-- DEBUG: Archivos en /models: " . implode(', ', $files) . " -->";
        }
    }
    
    echo "<!-- DEBUG FIN -->";
    
} catch (Exception $e) {
    // Si hay error, dejar categorías vacías (BD real)
    echo "<!-- DEBUG: EXCEPCION CAPTURADA: " . $e->getMessage() . " -->";
    echo "<!-- DEBUG: Archivo: " . $e->getFile() . " -->";
    echo "<!-- DEBUG: Línea: " . $e->getLine() . " -->";
    error_log("Error cargando categorías dinámicas: " . $e->getMessage());
    $categorias = []; // Asegurar que esté vacío si hay error
}

// DEBUG FINAL: Mostrar qué categorías se van a usar
echo "<!-- DEBUG FINAL: Usando " . count($categorias) . " categorías -->";
echo "<!-- DEBUG FINAL: Primera categoría a mostrar: " . (isset($categorias[0]) ? $categorias[0]['nombre'] : 'NINGUNA') . " -->";
?>

<div class="home-hero">
    <h1 class="page-title text-azul" style="margin-bottom: 10px;">
        <i class="fas fa-briefcase"></i> 
        Te damos la bienvenida a Camella.com.co
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
                        <span class="category-icon"><i class="<?= htmlspecialchars($categoria['icono']) ?>"></i></span>
                        <?= htmlspecialchars($categoria['nombre']) ?>
                    </h3>
                    
                    <?php if (!empty($categoria['oficios'])): ?>
                        <ul class="subcategories">
                            <?php foreach ($categoria['oficios'] as $oficio): ?>
                                <li class="oficio-item" data-oficio-id="<?= $oficio['id'] ?>">
                                    <?= htmlspecialchars($oficio['titulo']) ?>
                                    <?php if (!empty($oficio['popular']) && $oficio['popular'] == 1): ?>
                                        <span class="fuego" title="Oficio popular">🔥</span>
                                    <?php endif; ?>
                                </li>
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
                    <span class="category-icon"><i class="fas fa-cog"></i></span>
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
            <a href="index.php?view=registro" class="btn btn-info">
                <i class="fas fa-rocket"></i> Registrarse
            </a>
        </div>
    </div>
</section>

<!-- Estadísticas -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="stat-number">
                <span class="counter" data-target="1250">0</span>+
            </div>
            <div class="stat-label">Ofertas Activas</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number">
                <span class="counter" data-target="3500">0</span>+
            </div>
            <div class="stat-label">Profesionales Registrados</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-number">
                <span class="counter" data-target="890">0</span>+
            </div>
            <div class="stat-label">Empresas Confían en Nosotros</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-handshake"></i>
            </div>
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
/* Estilos específicos para la página de inicio - MAQUETACIÓN ORIGINAL COMPLETA */
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

/* Interactividad para elementos dinámicos manteniendo el estilo original */
.category-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.category-card[data-categoria-id] .subcategories li {
    cursor: pointer;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    transition: background-color 0.2s ease;
}

.category-card[data-categoria-id] .subcategories li:hover {
    background-color: rgba(255, 210, 0, 0.2);
    font-weight: 500;
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
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .cta-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .stat-card {
        padding: 1.5rem;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .cta-icon {
        font-size: 2.5rem;
    }
}
</style>