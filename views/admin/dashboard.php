<?php 
/**
 * Panel de Administración - Dashboard
 * Vista principal para gestión de categorías y oficios
 */

if (!isset($pageTitle)) $pageTitle = "Panel de Administración";
include 'partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">
            <i class="fas fa-tachometer-alt"></i>
            Panel de Administración
        </h1>
        <p class="admin-subtitle">Gestión de Categorías y Oficios</p>
    </div>
    
    <!-- Estado del Sistema -->
    <section class="system-status">
        <h2><i class="fas fa-server"></i> Estado del Sistema</h2>
        
        <?php if (isset($estado)): ?>
            <div class="status-grid">
                <div class="status-item <?= $estado['tablas_existen'] ? 'success' : 'error' ?>">
                    <div class="status-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="status-info">
                        <h3>Tablas de Base de Datos</h3>
                        <p><?= $estado['tablas_existen'] ? 'Creadas correctamente' : 'No existen' ?></p>
                    </div>
                </div>
                
                <div class="status-item <?= $estado['datos_inicializados'] ? 'success' : 'warning' ?>">
                    <div class="status-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="status-info">
                        <h3>Datos Iniciales</h3>
                        <p><?= $estado['datos_inicializados'] ? 'Inicializados' : 'Pendientes' ?></p>
                    </div>
                </div>
                
                <div class="status-item info">
                    <div class="status-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="status-info">
                        <h3>Categorías</h3>
                        <p><?= $estado['total_categorias'] ?> registradas</p>
                    </div>
                </div>
                
                <div class="status-item info">
                    <div class="status-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="status-info">
                        <h3>Oficios</h3>
                        <p><?= $estado['total_oficios'] ?> registrados</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
    
    <!-- Gestión de Categorías -->
    <section class="admin-section">
        <div class="section-header">
            <h2><i class="fas fa-th-large"></i> Gestión de Categorías</h2>
            <div class="section-actions">
                <button id="btn-nueva-categoria" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
                <button id="btn-verificar-sistema" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Verificar Sistema
                </button>
            </div>
        </div>
        
        <?php if (isset($categorias) && !empty($categorias)): ?>
            <div class="categories-admin-grid">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="admin-category-card">
                        <div class="category-header">
                            <span class="category-icon"><?= htmlspecialchars($categoria['icono']) ?></span>
                            <h3><?= htmlspecialchars($categoria['nombre']) ?></h3>
                            <div class="category-actions">
                                <button class="btn-icon btn-edit-categoria" 
                                        data-categoria-id="<?= $categoria['id'] ?>" 
                                        data-categoria-nombre="<?= htmlspecialchars($categoria['nombre']) ?>"
                                        data-categoria-icono="<?= htmlspecialchars($categoria['icono']) ?>"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-add-oficio" data-categoria-id="<?= $categoria['id'] ?>" title="Agregar Oficio">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="oficios-list">
                            <?php if (!empty($categoria['oficios'])): ?>
                                <?php foreach ($categoria['oficios'] as $oficio): ?>
                                    <div class="oficio-item">
                                        <span><?= htmlspecialchars($oficio['nombre']) ?></span>
                                        <button class="btn-icon btn-small" title="Editar oficio">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="no-oficios">No hay oficios registrados</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No hay categorías registradas</h3>
                <p>El sistema se inicializará automáticamente la primera vez.</p>
                <button id="btn-inicializar" class="btn btn-primary">
                    <i class="fas fa-magic"></i> Inicializar Sistema
                </button>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Modales -->
<div id="modal-nueva-categoria" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nueva Categoría</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="form-nueva-categoria" action="index.php" method="POST">
            <input type="hidden" name="action" value="agregarCategoria">
            <div class="form-group">
                <label for="nombre">Nombre de la Categoría</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="icono">Ícono (Emoji)</label>
                <input type="text" id="icono" name="icono" placeholder="🏠" required>
            </div>
            <div class="form-group">
                <label for="orden">Orden (opcional)</label>
                <input type="number" id="orden" name="orden" min="0" value="0">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Categoría</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-editar-categoria" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Categoría</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="form-editar-categoria" action="index.php" method="POST">
            <input type="hidden" name="action" value="editarCategoria">
            <input type="hidden" id="edit_categoria_id" name="id">
            <div class="form-group">
                <label for="edit_nombre">Nombre de la Categoría</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="edit_icono">Ícono (Emoji)</label>
                <input type="text" id="edit_icono" name="icono" placeholder="🏠">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-nuevo-oficio" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Nuevo Oficio</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="form-nuevo-oficio" action="index.php" method="POST">
            <input type="hidden" name="action" value="agregarOficio">
            <input type="hidden" id="categoria_id" name="categoria_id">
            <div class="form-group">
                <label for="nombre_oficio">Nombre del Oficio</label>
                <input type="text" id="nombre_oficio" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="orden_oficio">Orden (opcional)</label>
                <input type="number" id="orden_oficio" name="orden" min="0" value="0">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary modal-close">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Oficio</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Estilos del panel de administración */
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    text-align: center;
    margin-bottom: 3rem;
}

.admin-title {
    color: var(--azul-fondo);
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.admin-subtitle {
    color: var(--color-gris);
    font-size: 1.1rem;
}

.system-status {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid;
}

.status-item.success {
    background-color: rgba(40, 167, 69, 0.1);
    border-left-color: #28a745;
}

.status-item.error {
    background-color: rgba(220, 53, 69, 0.1);
    border-left-color: #dc3545;
}

.status-item.warning {
    background-color: rgba(255, 193, 7, 0.1);
    border-left-color: #ffc107;
}

.status-item.info {
    background-color: rgba(23, 162, 184, 0.1);
    border-left-color: #17a2b8;
}

.status-icon {
    font-size: 1.5rem;
    margin-right: 1rem;
}

.status-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
    font-weight: 600;
}

.status-info p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.8;
}

.admin-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.section-actions {
    display: flex;
    gap: 0.5rem;
}

.categories-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.admin-category-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.category-header {
    background: var(--gris-claro);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.category-header .category-icon {
    font-size: 1.5rem;
}

.category-header h3 {
    flex: 1;
    margin: 0;
    font-size: 1rem;
}

.category-actions {
    display: flex;
    gap: 0.25rem;
}

.oficios-list {
    padding: 1rem;
    max-height: 200px;
    overflow-y: auto;
}

.oficio-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.oficio-item:last-child {
    border-bottom: none;
}

.btn-icon {
    background: none;
    border: none;
    padding: 0.25rem;
    cursor: pointer;
    color: var(--color-gris);
    transition: color 0.2s ease;
}

.btn-icon:hover {
    color: var(--azul-fondo);
}

.btn-small {
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color-gris);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-oficios {
    text-align: center;
    font-style: italic;
    color: var(--color-gris);
    padding: 1rem;
}

/* Modales */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-gris);
}

.modal form {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--azul-fondo);
}

.form-group input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-container {
        padding: 1rem;
    }
    
    .section-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .section-actions {
        width: 100%;
        justify-content: center;
    }
    
    .categories-admin-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestión de modales
    const modals = document.querySelectorAll('.modal');
    const modalCloses = document.querySelectorAll('.modal-close');
    
    // Abrir modal nueva categoría
    document.getElementById('btn-nueva-categoria')?.addEventListener('click', function() {
        document.getElementById('modal-nueva-categoria').style.display = 'block';
    });
    
    // Abrir modal editar categoría
    document.querySelectorAll('.btn-edit-categoria').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoriaId = this.dataset.categoriaId;
            const categoriaNombre = this.dataset.categoriaNombre;
            const categoriaIcono = this.dataset.categoriaIcono;
            
            document.getElementById('edit_categoria_id').value = categoriaId;
            document.getElementById('edit_nombre').value = categoriaNombre;
            document.getElementById('edit_icono').value = categoriaIcono;
            document.getElementById('modal-editar-categoria').style.display = 'block';
        });
    });
    
    // Abrir modal nuevo oficio
    document.querySelectorAll('.btn-add-oficio').forEach(btn => {
        btn.addEventListener('click', function() {
            const categoriaId = this.dataset.categoriaId;
            document.getElementById('categoria_id').value = categoriaId;
            document.getElementById('modal-nuevo-oficio').style.display = 'block';
        });
    });
    
    // Cerrar modales
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(e) {
        modals.forEach(modal => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Verificar sistema
    document.getElementById('btn-verificar-sistema')?.addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        btn.disabled = true;
        
        fetch('index.php?api=sistema')
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    console.log('Estado del sistema:', data.datos);
                    // Aquí se puede actualizar la interfaz con los nuevos datos
                    location.reload(); // Por simplicidad, recargar la página
                } else {
                    alert('Error verificando el sistema: ' + data.mensaje);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    });
    
    // Inicializar sistema
    document.getElementById('btn-inicializar')?.addEventListener('click', function() {
        if (confirm('¿Está seguro de que desea inicializar el sistema con los datos por defecto?')) {
            location.reload();
        }
    });
});
</script>

<?php 
// Mostrar mensajes de sesión
if (isset($_SESSION['mensaje_exito'])) {
    echo '<script>alert("' . $_SESSION['mensaje_exito'] . '");</script>';
    unset($_SESSION['mensaje_exito']);
}

if (isset($_SESSION['mensaje_error'])) {
    echo '<script>alert("Error: ' . $_SESSION['mensaje_error'] . '");</script>';
    unset($_SESSION['mensaje_error']);
}

include 'partials/footer.php'; 
?>