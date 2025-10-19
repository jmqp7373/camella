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
                    <ul class="subcategories">
                        <?php foreach ($oficiosPorCategoria[$categoria['id']] as $oficio): ?>
                            <li class="oficio-admin-item" style="margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                                <span style="color: #333; display: flex; align-items: center; gap: 5px;">
                                    <?= htmlspecialchars($oficio['nombre']) ?>
                                    <?php if ($oficio['popular'] == 1): ?>
                                        <img src="<?= SITE_URL ?>/assets/images/app/candela1.png" 
                                             alt="Alta demanda" 
                                             title="Oficio popular"
                                             class="candela-icon"
                                             style="width: 18px; height: 18px;">
                                    <?php else: ?>
                                        <img src="<?= SITE_URL ?>/assets/images/app/candela0.png" 
                                             alt="No destacada" 
                                             title="Oficio no popular"
                                             class="candela-icon"
                                             style="width: 18px; height: 18px; opacity: 0.4;">
                                    <?php endif; ?>
                                </span>
                                <button 
                                    class="btn-toggle-candela" 
                                    data-id="<?= $oficio['id'] ?>" 
                                    data-popular="<?= $oficio['popular'] ?>"
                                    title="Clic para cambiar popularidad"
                                    style="background: none; border: 2px solid #ffd700; border-radius: 6px; cursor: pointer; padding: 0.25rem 0.5rem; transition: all 0.3s; font-size: 1.2rem;">
                                    🔥
                                </button>
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
// Toggle estado popular de oficio con actualización visual mejorada
document.querySelectorAll('.btn-toggle-candela').forEach(btn => {
    btn.addEventListener('click', function() {
        const oficioId = this.getAttribute('data-id');
        const listItem = this.closest('li');
        const candelaImg = listItem.querySelector('.candela-icon');
        
        // Mostrar estado de carga
        btn.style.opacity = '0.5';
        btn.disabled = true;
        
        fetch('../../controllers/AdminController.php?action=togglePopular&id=' + oficioId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar atributo data-popular
                btn.setAttribute('data-popular', data.newState);
                
                // Actualizar imagen de candela con animación
                if (candelaImg) {
                    candelaImg.style.transition = 'opacity 0.3s, transform 0.3s';
                    candelaImg.style.opacity = '0';
                    
                    setTimeout(() => {
                        if (data.newState == 1) {
                            candelaImg.src = '<?= SITE_URL ?>/assets/images/app/candela1.png';
                            candelaImg.alt = 'Alta demanda';
                            candelaImg.title = 'Oficio popular';
                            candelaImg.style.opacity = '1';
                        } else {
                            candelaImg.src = '<?= SITE_URL ?>/assets/images/app/candela0.png';
                            candelaImg.alt = 'No destacada';
                            candelaImg.title = 'Oficio no popular';
                            candelaImg.style.opacity = '0.4';
                        }
                        
                        // Pequeña animación de escala
                        candelaImg.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            candelaImg.style.transform = 'scale(1)';
                        }, 200);
                    }, 150);
                }
                
                // Mostrar notificación
                showNotification(
                    data.newState == 1 
                        ? '✅ Oficio marcado como popular' 
                        : '⚪ Oficio desmarcado como popular', 
                    'success'
                );
            } else {
                showNotification('❌ Error al actualizar: ' + (data.message || 'Desconocido'), 'danger');
            }
            
            // Restaurar botón
            btn.style.opacity = '1';
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('❌ Error de conexión', 'danger');
            btn.style.opacity = '1';
            btn.disabled = false;
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

.oficio-admin-item {
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.2s;
}

.oficio-admin-item:last-child {
    border-bottom: none;
}

.oficio-admin-item:hover {
    background-color: #f8f9fa;
    padding-left: 0.75rem !important;
    border-radius: 6px;
}

.btn-toggle-candela {
    transition: all 0.3s ease;
}

.btn-toggle-candela:hover {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    transform: scale(1.1);
    box-shadow: 0 3px 8px rgba(255, 215, 0, 0.4);
}

.btn-toggle-candela:active {
    transform: scale(0.95);
}

.candela-icon {
    transition: all 0.3s ease;
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
