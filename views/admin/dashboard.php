<?php 
/**
 * Panel de Administración - Dashboard
 * Vista principal para gestión de categorías y oficios
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}

// Cargar estadísticas de Twilio
require_once __DIR__ . '/../../controllers/TwilioStatsHelper.php';
$twilioStats = getTwilioStatistics();

if (!isset($pageTitle)) $pageTitle = "Panel de Administración";
include __DIR__ . '/../../partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="admin-title">
                    <i class="fas fa-tachometer-alt"></i>
                    Panel de Administración
                </h1>
                <p class="admin-subtitle">Gestión de Categorías y Oficios</p>
            </div>
            
            <!-- Selector de Rol (Solo para Admin) -->
            <?php if (isset($_SESSION['original_role']) && $_SESSION['original_role'] === 'admin'): ?>
            <div class="role-switcher" style="background: rgba(255,255,255,0.1); padding: 0.75rem 1.5rem; border-radius: 50px; backdrop-filter: blur(10px);">
                <label style="color: white; font-size: 0.85rem; margin-right: 0.5rem; opacity: 0.9;">
                    <i class="fas fa-user-shield"></i> Ver como:
                </label>
                <select id="roleSwitcher" style="padding: 0.5rem 1rem; border: 2px solid rgba(255,255,255,0.3); border-radius: 25px; background: white; color: #003d7a; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                    <option value="admin" <?= $_SESSION['role'] === 'admin' ? 'selected' : '' ?>>👑 Administrador</option>
                    <option value="promotor" <?= $_SESSION['role'] === 'promotor' ? 'selected' : '' ?>>📢 Promotor</option>
                    <option value="publicante" <?= $_SESSION['role'] === 'publicante' ? 'selected' : '' ?>>💼 Publicante</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
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

    <!-- Estadísticas de Twilio (Solo visible para Admin) -->
    <section class="twilio-stats-section">
        <h2><i class="fas fa-sms"></i> Estadísticas de SMS (Twilio)</h2>
        
        <div class="stats-grid">
            <!-- Últimas 24 horas -->
            <div class="stats-card">
                <div class="stats-card-header">
                    <h3><i class="fas fa-clock"></i> Últimas 24 horas</h3>
                </div>
                <div class="stats-card-body">
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-paper-plane"></i> Enviados:
                        </span>
                        <span class="stat-value"><?= $twilioStats['24h']['total_enviados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-check-circle"></i> Entregados:
                        </span>
                        <span class="stat-value success"><?= $twilioStats['24h']['entregas_exitosas'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-times-circle"></i> Fallidos:
                        </span>
                        <span class="stat-value error"><?= $twilioStats['24h']['fallidos'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-hourglass-end"></i> Expirados:
                        </span>
                        <span class="stat-value warning"><?= $twilioStats['24h']['expirados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-dollar-sign"></i> Costo estimado:
                        </span>
                        <span class="stat-value">$<?= $twilioStats['24h']['costo_estimado'] ?? '0.00' ?> USD</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-percent"></i> Tasa de éxito:
                        </span>
                        <span class="stat-value"><?= $twilioStats['24h']['tasa_exito'] ?? 0 ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Última semana -->
            <div class="stats-card">
                <div class="stats-card-header">
                    <h3><i class="fas fa-calendar-week"></i> Última semana</h3>
                </div>
                <div class="stats-card-body">
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-paper-plane"></i> Enviados:
                        </span>
                        <span class="stat-value"><?= $twilioStats['7d']['total_enviados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-check-circle"></i> Entregados:
                        </span>
                        <span class="stat-value success"><?= $twilioStats['7d']['entregas_exitosas'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-times-circle"></i> Fallidos:
                        </span>
                        <span class="stat-value error"><?= $twilioStats['7d']['fallidos'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-hourglass-end"></i> Expirados:
                        </span>
                        <span class="stat-value warning"><?= $twilioStats['7d']['expirados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-dollar-sign"></i> Costo estimado:
                        </span>
                        <span class="stat-value">$<?= $twilioStats['7d']['costo_estimado'] ?? '0.00' ?> USD</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-percent"></i> Tasa de éxito:
                        </span>
                        <span class="stat-value"><?= $twilioStats['7d']['tasa_exito'] ?? 0 ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Último mes -->
            <div class="stats-card">
                <div class="stats-card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Último mes</h3>
                </div>
                <div class="stats-card-body">
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-paper-plane"></i> Enviados:
                        </span>
                        <span class="stat-value"><?= $twilioStats['30d']['total_enviados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-check-circle"></i> Entregados:
                        </span>
                        <span class="stat-value success"><?= $twilioStats['30d']['entregas_exitosas'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-times-circle"></i> Fallidos:
                        </span>
                        <span class="stat-value error"><?= $twilioStats['30d']['fallidos'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">
                            <i class="fas fa-hourglass-end"></i> Expirados:
                        </span>
                        <span class="stat-value warning"><?= $twilioStats['30d']['expirados'] ?? 0 ?></span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-dollar-sign"></i> Costo estimado:
                        </span>
                        <span class="stat-value">$<?= $twilioStats['30d']['costo_estimado'] ?? '0.00' ?> USD</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-label">
                            <i class="fas fa-percent"></i> Tasa de éxito:
                        </span>
                        <span class="stat-value"><?= $twilioStats['30d']['tasa_exito'] ?? 0 ?>%</span>
                    </div>
                </div>
            </div>
        </div>
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

/* Animaciones para mensajes de feedback */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Estilos para estadísticas de Twilio */
.twilio-stats-section {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.twilio-stats-section h2 {
    color: var(--azul-fondo);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.stats-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stats-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.stats-card-header {
    background: linear-gradient(135deg, var(--azul-fondo), var(--color-azul-oscuro));
    color: white;
    padding: 1rem;
}

.stats-card-header h3 {
    margin: 0;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-card-body {
    padding: 1.5rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.stat-row:last-child {
    border-bottom: none;
}

.stat-row.highlight {
    background-color: rgba(var(--color-azul-rgb), 0.05);
    padding: 0.75rem;
    border-radius: 6px;
    margin-top: 0.5rem;
    font-weight: 600;
}

.stat-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-gris-oscuro);
    font-size: 0.9rem;
}

.stat-label i {
    width: 20px;
    text-align: center;
}

.stat-value {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--azul-fondo);
}

.stat-value.success {
    color: var(--color-verde);
}

.stat-value.error {
    color: #dc3545;
}

.stat-value.warning {
    color: var(--color-naranja);
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
    
    // Manejo del formulario de editar categoría con AJAX
    document.getElementById('form-editar-categoria')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Mostrar estado de carga
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        submitBtn.disabled = true;
        
        fetch('index.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                // Actualizar la categoría en la interfaz sin recargar
                actualizarCategoriaEnVista(data.categoria);
                
                // Cerrar modal
                document.getElementById('modal-editar-categoria').style.display = 'none';
                
                // Mostrar mensaje de éxito
                mostrarMensaje('Categoría actualizada exitosamente', 'success');
            } else {
                mostrarMensaje('Error: ' + data.mensaje, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
        })
        .finally(() => {
            // Restaurar botón
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
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
    
    // Función para actualizar categoría en la vista sin recargar
    function actualizarCategoriaEnVista(categoria) {
        // Buscar la tarjeta de categoría por ID
        const categoriaCard = document.querySelector(`[data-categoria-id="${categoria.id}"]`).closest('.admin-category-card');
        
        if (categoriaCard) {
            // Actualizar el nombre en la tarjeta
            const nombreElement = categoriaCard.querySelector('.category-header h3');
            const iconoElement = categoriaCard.querySelector('.category-icon');
            
            if (nombreElement) nombreElement.textContent = categoria.nombre;
            if (iconoElement) iconoElement.textContent = categoria.icono;
            
            // Actualizar los data-attributes del botón de editar
            const editBtn = categoriaCard.querySelector('.btn-edit-categoria');
            if (editBtn) {
                editBtn.dataset.categoriaNombre = categoria.nombre;
                editBtn.dataset.categoriaIcono = categoria.icono;
            }
        }
    }
    
    // Función para mostrar mensajes de feedback
    function mostrarMensaje(mensaje, tipo) {
        // Crear elemento de mensaje
        const messageEl = document.createElement('div');
        messageEl.className = `alert alert-${tipo}`;
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            max-width: 400px;
            animation: slideIn 0.3s ease;
        `;
        
        if (tipo === 'success') {
            messageEl.style.backgroundColor = '#28a745';
            messageEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + mensaje;
        } else {
            messageEl.style.backgroundColor = '#dc3545';
            messageEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + mensaje;
        }
        
        // Agregar al DOM
        document.body.appendChild(messageEl);
        
        // Remover después de 4 segundos
        setTimeout(() => {
            messageEl.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageEl.remove(), 300);
        }, 4000);
    }
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
?>

<!-- Bloque: Crear Anuncio -->
<section class="crear-anuncio-section" style="margin: 2rem 0; padding: 0 1rem;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="font-size: 4rem; margin-bottom: 1.5rem;">
                <i class="fas fa-bullhorn" style="color: white; opacity: 0.9;"></i>
            </div>
            <h2 style="color: white; font-size: 2rem; margin-bottom: 1rem; font-weight: 700;">
                ¿Tienes un servicio para ofrecer?
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.6;">
                Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
            </p>
            <a href="<?= app_url('views/bloques/publicar.php') ?>" 
               style="display: inline-block; padding: 1rem 3rem; background: white; color: #667eea; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)';">
                <i class="fas fa-plus-circle"></i> Crear mi anuncio ahora
            </a>
        </div>
    </div>
</section>

<!-- Script: Cambio de Rol -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSwitcher = document.getElementById('roleSwitcher');
    
    if (roleSwitcher) {
        roleSwitcher.addEventListener('change', async function() {
            const nuevoRol = this.value;
            const rolActual = '<?= $_SESSION['role'] ?>';
            
            // Si es el mismo rol, no hacer nada
            if (nuevoRol === rolActual) {
                return;
            }
            
            // Mostrar indicador de carga
            this.disabled = true;
            this.style.opacity = '0.6';
            
            try {
                const response = await fetch('<?= app_url('controllers/cambiar_rol.php') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'role=' + encodeURIComponent(nuevoRol)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar mensaje de éxito
                    const mensaje = document.createElement('div');
                    mensaje.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 1rem 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 9999; animation: slideIn 0.3s ease;';
                    mensaje.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    document.body.appendChild(mensaje);
                    
                    // Redirigir al dashboard correspondiente
                    setTimeout(() => {
                        window.location.href = data.redirectUrl;
                    }, 800);
                } else {
                    alert('Error: ' + data.message);
                    this.disabled = false;
                    this.style.opacity = '1';
                }
                
            } catch (error) {
                console.error('Error al cambiar de rol:', error);
                alert('Error al cambiar de rol. Por favor intenta de nuevo.');
                this.disabled = false;
                this.style.opacity = '1';
            }
        });
    }
});
</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

#roleSwitcher:hover {
    border-color: rgba(255,255,255,0.5);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

#roleSwitcher:focus {
    outline: none;
    border-color: #27ae60;
    box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.2);
}
</style>

<?php
include __DIR__ . '/../../partials/footer.php'; 
?>