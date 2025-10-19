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

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-layer-group me-2"></i>Gestión de Categorías y Oficios</h2>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
        </a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Instrucciones:</strong> Usa el botón 🔥 para marcar/desmarcar oficios como populares. 
        Los oficios populares aparecerán destacados en la página principal.
    </div>

    <div class="row">
        <?php foreach ($categorias as $categoria): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <?php if (!empty($categoria['icono'])): ?>
                                <i class="<?= htmlspecialchars($categoria['icono']) ?> me-2"></i>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>
                        </div>
                        <span class="badge bg-light text-dark"><?= count($oficiosPorCategoria[$categoria['id']]) ?> oficios</span>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php if (!empty($oficiosPorCategoria[$categoria['id']])): ?>
                                <?php foreach ($oficiosPorCategoria[$categoria['id']] as $oficio): ?>
                                    <li class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <span class="oficio-nombre" style="display: inline-flex; align-items: center;">
                                            <?= htmlspecialchars($oficio['nombre']) ?>
                                            <?php if ($oficio['popular'] == 1): ?>
                                                <img src="<?= SITE_URL ?>/assets/images/app/candela1.png" 
                                                     alt="Alta demanda" 
                                                     title="Oficio popular"
                                                     style="width: 16px; height: 16px; margin-left: 5px;">
                                            <?php else: ?>
                                                <img src="<?= SITE_URL ?>/assets/images/app/candela0.png" 
                                                     alt="Baja demanda" 
                                                     title="Oficio no popular"
                                                     style="width: 16px; height: 16px; margin-left: 5px;">
                                            <?php endif; ?>
                                        </span>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn <?= $oficio['popular'] == 1 ? 'btn-warning' : 'btn-outline-warning' ?> toggle-fuego" 
                                                    data-id="<?= $oficio['id'] ?>" 
                                                    title="Toggle popular">
                                                🔥
                                            </button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-muted fst-italic">No hay oficios en esta categoría</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="confirmMessage">
                ¿Estás seguro de realizar esta acción?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle estado popular de oficio
document.querySelectorAll('.toggle-fuego').forEach(btn => {
    btn.addEventListener('click', function() {
        const oficioId = this.getAttribute('data-id');
        const isPopular = this.classList.contains('btn-warning');
        const listItem = this.closest('li');
        
        fetch('../../controllers/AdminController.php?action=togglePopular&id=' + oficioId, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Toggle clase del botón
                this.classList.toggle('btn-warning');
                this.classList.toggle('btn-outline-warning');
                
                // Toggle ícono fuego en el nombre
                const nombreSpan = listItem.querySelector('.oficio-nombre');
                const fuegoExistente = nombreSpan.querySelector('.fuego');
                
                if (data.newState == 1 && !fuegoExistente) {
                    // Agregar fuego
                    const fuego = document.createElement('span');
                    fuego.className = 'fuego';
                    fuego.title = 'Oficio popular';
                    fuego.textContent = '🔥';
                    nombreSpan.appendChild(fuego);
                } else if (data.newState == 0 && fuegoExistente) {
                    // Quitar fuego
                    fuegoExistente.remove();
                }
                
                // Mostrar notificación
                showNotification(data.newState == 1 ? 'Oficio marcado como popular' : 'Oficio desmarcado como popular', 'success');
            } else {
                showNotification('Error al actualizar: ' + (data.message || 'Desconocido'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'danger');
        });
    });
});

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
</script>

<style>
.fuego {
    margin-left: 4px;
    font-size: 1.1em;
    display: inline-block;
}

.oficio-nombre {
    flex: 1;
    margin-right: 10px;
}

.card-header {
    font-size: 0.95rem;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.list-unstyled li {
    transition: background-color 0.2s;
}

.list-unstyled li:hover {
    background-color: #f8f9fa;
    border-radius: 4px;
    padding: 0.25rem;
    margin: -0.25rem;
}
</style>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
