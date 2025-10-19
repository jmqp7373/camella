<?php 
/**
 * Gestión de Categorías y Oficios - Panel de Administración
 * Vista administrativa con controles para editar, eliminar y gestionar oficios populares
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Cargar configuración y modelos
require_once __DIR__ . '/../../config/app_paths.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Categorias.php';

$pageTitle = "Gestión de Categorías y Oficios";
$categoriasModel = new Categorias();
$categorias = $categoriasModel->obtenerCategoriasConOficios();

// Obtener oficios por categoría
$pdo = getPDO();
$oficiosPorCategoria = [];
foreach ($categorias as $categoria) {
    $stmt = $pdo->prepare("SELECT id, titulo as nombre, popular FROM oficios WHERE categoria_id = ? AND activo = 1 ORDER BY popular DESC, titulo ASC");
    $stmt->execute([$categoria['id']]);
    $oficiosPorCategoria[$categoria['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

require_once __DIR__ . '/../../partials/header.php';
?>

<!-- Hero Section para Admin -->
<div class="home-hero" style="background: linear-gradient(135deg, #002b47 0%, #005580 100%); padding: 2rem 1.5rem; text-align: center; margin-bottom: 2rem;">
    <h1 class="page-title" style="color: #ffd700; margin-bottom: 10px;">
        <i class="fas fa-layer-group"></i> 
        Gestión de Categorías y Oficios
    </h1>
    <p class="page-subtitle" style="color: #fff; margin-bottom: 1rem; line-height: 1.5;">
        Administra los oficios y marca cuáles están en alta demanda
    </p>
    <a href="dashboard.php" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 0.5rem 1.5rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
    </a>
</div>

<!-- Sección de Categorías con diseño similar a index.php -->
<section class="categories-section" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem 3rem;">
    <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Instrucciones:</strong> Haz clic en la flamita 🔥 para marcar/desmarcar oficios como populares. 
        Los oficios populares aparecerán destacados con la flamita encendida en la página principal.
    </div>

    <div class="categories-tree">
        <?php foreach ($categorias as $categoria): ?>
            <div class="category-card admin-category-card">
                <h3 class="category-title">
                    <span class="category-icon">
                        <?php if (!empty($categoria['icono'])): ?>
                            <i class="<?= htmlspecialchars($categoria['icono']) ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-briefcase"></i>
                        <?php endif; ?>
                    </span>
                    <?= htmlspecialchars($categoria['nombre']) ?>
                    <span class="badge" style="background: #ffd700; color: #002b47; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 12px; margin-left: auto;">
                        <?= count($oficiosPorCategoria[$categoria['id']]) ?> oficios
                    </span>
                </h3>
                
                <?php if (!empty($oficiosPorCategoria[$categoria['id']])): ?>
                    <ul class="subcategories list-unstyled mb-0">
                        <?php foreach ($oficiosPorCategoria[$categoria['id']] as $oficio): ?>
                            <li style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                                <span style="color: #333; font-size: 0.95rem;">
                                    <?= htmlspecialchars($oficio['nombre']) ?>
                                </span>
                                <img 
                                    src="<?= defined('SITE_URL') ? SITE_URL : '' ?>/assets/images/app/<?= $oficio['popular'] == 1 ? 'candela1.png' : 'candela0.png' ?>" 
                                    alt="<?= $oficio['popular'] == 1 ? 'Popular' : 'No popular' ?>"
                                    title="Clic para cambiar"
                                    class="candela-toggle"
                                    data-id="<?= $oficio['id'] ?>"
                                    data-popular="<?= $oficio['popular'] ?>"
                                    style="width: 22px; height: 22px; cursor: pointer; transition: all 0.2s ease; <?= $oficio['popular'] == 0 ? 'opacity: 0.5;' : 'opacity: 1;' ?>"
                                    onmouseover="this.style.transform='scale(1.2)'; this.style.filter='drop-shadow(0 2px 4px rgba(255,215,0,0.6))';"
                                    onmouseout="this.style.transform='scale(1)'; this.style.filter='none';">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul class="subcategories">
                        <li style="font-style: italic; color: #999;">No hay oficios registrados en esta categoría</li>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>


<script>
// Toggle estado popular de oficio - versión robusta con rutas relativas
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔥 Iniciando sistema de candelas...');
    console.log('Candelas encontradas:', document.querySelectorAll('.candela-toggle').length);
    
    document.querySelectorAll('.candela-toggle').forEach(flama => {
        flama.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = this.dataset.id;
            const popularActual = this.dataset.popular;
            
            console.log('🔥 Clic en oficio ID:', id, 'Estado actual:', popularActual);
            
            // Feedback visual inmediato
            const opacidadOriginal = this.style.opacity;
            this.style.opacity = '0.5';
            this.style.pointerEvents = 'none';
            this.style.cursor = 'wait';

            try {
                // Ruta absoluta desde la raíz del sitio
                const baseUrl = window.location.origin;
                const url = `${baseUrl}/camella.com.co/controllers/OficioController.php?action=togglePopular&id=${id}`;
                
                console.log('📡 Enviando request a:', url);
                
                const response = await fetch(url);
                
                console.log('📥 Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error('Error HTTP: ' + response.status);
                }
                
                const data = await response.json();
                console.log('📦 Data recibida:', data);

                if (data.success) {
                    // Actualizar imagen según nuevo estado
                    const nuevaImagen = data.newState == 1 
                        ? `${baseUrl}/camella.com.co/assets/images/app/candela1.png`
                        : `${baseUrl}/camella.com.co/assets/images/app/candela0.png`;
                    
                        const nuevaOpacidad = data.newState == 1 ? '1' : '0.5';
                    
                    // Actualizar dataset
                    this.dataset.popular = data.newState;
                    
                    console.log('✅ Actualizando imagen a:', nuevaImagen);
                    
                    // Aplicar cambios con transición suave
                    this.style.transition = 'opacity 0.3s ease';
                    this.style.opacity = '0';
                    
                    setTimeout(() => {
                        this.src = nuevaImagen;
                        this.style.opacity = nuevaOpacidad;
                        this.style.pointerEvents = 'auto';
                        this.style.cursor = 'pointer';
                        
                        // Efecto de brillo al cambiar
                        this.animate([
                            { filter: 'brightness(2) drop-shadow(0 0 8px #ffd700)' }, 
                            { filter: 'brightness(1) drop-shadow(0 0 0 transparent)' }
                        ], { 
                            duration: 500,
                            easing: 'ease-out'
                        });
                    }, 150);

                    // Notificación
                    showNotification(
                        data.newState == 1 
                            ? '✅ Marcado como popular' 
                            : '⚪ Desmarcado', 
                        'success'
                    );
                } else {
                    console.error('❌ Error en respuesta:', data.message);
                    // Revertir
                    this.style.opacity = opacidadOriginal;
                    this.style.pointerEvents = 'auto';
                    this.style.cursor = 'pointer';
                    showNotification('❌ Error: ' + (data.message || 'No se pudo actualizar'), 'danger');
                }
            } catch (error) {
                console.error('❌ Error en togglePopular:', error);
                // Revertir cambios
                this.style.opacity = opacidadOriginal;
                this.style.pointerEvents = 'auto';
                this.style.cursor = 'pointer';
                showNotification('❌ Error de conexión', 'danger');
            }
        });
    });
});

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = `
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        min-width: 300px;
        max-width: 500px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideDown 0.3s ease-out;
    `;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}
</script>

<style>
/* Animaciones para notificaciones */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
    to {
        opacity: 1;
        transform: translate(-50%, 0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translate(-50%, 0);
    }
    to {
        opacity: 0;
        transform: translate(-50%, -20px);
    }
}

/* Estilos específicos para vista admin */
.admin-category-card {
    transition: all 0.3s ease;
}

.admin-category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0, 43, 71, 0.15);
}

.subcategories li {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s;
}

.subcategories li:last-child {
    border-bottom: none;
}

.subcategories li:hover {
    background-color: #f8f9fa;
    padding-left: 0.75rem !important;
    border-radius: 6px;
}

.candela-toggle {
    transition: all 0.3s ease;
    filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.1));
}

.candela-toggle:hover {
    filter: drop-shadow(0 3px 8px rgba(255, 215, 0, 0.6)) brightness(1.1);
}

.candela-toggle:active {
    transform: scale(0.9) !important;
}

.category-title .badge {
    font-weight: 600;
    letter-spacing: 0.5px;
}

/* Ajustes responsivos */
@media (max-width: 768px) {
    .categories-tree {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .home-hero h1 {
        font-size: 1.5rem;
    }
    
    .category-title {
        font-size: 1.1rem;
    }
}
</style>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
