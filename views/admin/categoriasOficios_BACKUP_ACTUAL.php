<?php 
/**
 * Gestión de Categorías y Oficios - Panel de Administración
 * Vista administrativa con CRUD completo, filtros y estadísticas
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

// Obtener oficios por categoría y estadísticas
$pdo = getPDO();
$oficiosPorCategoria = [];
$totalOficios = 0;
$oficiosPopulares = 0;
$oficiosInactivos = 0;

foreach ($categorias as $categoria) {
    $stmt = $pdo->prepare("SELECT id, titulo as nombre, popular, activo FROM oficios WHERE categoria_id = ? ORDER BY popular DESC, titulo ASC");
    $stmt->execute([$categoria['id']]);
    $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $oficiosPorCategoria[$categoria['id']] = array_filter($oficios, fn($o) => $o['activo'] == 1);
    
    // Contar estadísticas
    foreach ($oficios as $oficio) {
        if ($oficio['activo'] == 1) {
            $totalOficios++;
            if ($oficio['popular'] == 1) $oficiosPopulares++;
        } else {
            $oficiosInactivos++;
        }
    }
}

$totalCategorias = count($categorias);

require_once __DIR__ . '/../../partials/header.php';
?>

<!-- Hero Section para Admin -->
<div class="home-hero" style="background: linear-gradient(135deg, #002b47 0%, #005580 100%); padding: 2rem 1.5rem; text-align: center; margin-bottom: 2rem;">
    <h1 class="page-title" style="color: #ffd700; margin-bottom: 10px;">
        <i class="fas fa-layer-group"></i> 
        Gestión de Categorías y Oficios
    </h1>
    <p class="page-subtitle" style="color: #fff; margin-bottom: 1rem; line-height: 1.5;">
        CRUD completo con búsqueda, filtros y estadísticas en tiempo real
    </p>
    <a href="dashboard.php" class="btn btn-secondary" style="background: #6c757d; border: none; padding: 0.5rem 1.5rem;">
        <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
    </a>
</div>

<!-- Panel de Estadísticas -->
<section class="stats-section" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem 2rem;">
    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: #f8f9fa; border-radius: 10px; padding: 1.5rem; text-align: center; border-left: 4px solid #007bff;">
                <h3 style="color: #007bff; font-size: 2rem; margin-bottom: 0.5rem;"><?= $totalCategorias ?></h3>
                <p style="margin: 0; color: #6c757d; font-weight: 500;">Total Categorías</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: #f8f9fa; border-radius: 10px; padding: 1.5rem; text-align: center; border-left: 4px solid #28a745;">
                <h3 style="color: #28a745; font-size: 2rem; margin-bottom: 0.5rem;"><?= $totalOficios ?></h3>
                <p style="margin: 0; color: #6c757d; font-weight: 500;">Total Oficios</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: #f8f9fa; border-radius: 10px; padding: 1.5rem; text-align: center; border-left: 4px solid #ffc107;">
                <h3 style="color: #ffc107; font-size: 2rem; margin-bottom: 0.5rem;"><?= $oficiosPopulares ?></h3>
                <p style="margin: 0; color: #6c757d; font-weight: 500;">Oficios Populares</p>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card" style="background: #f8f9fa; border-radius: 10px; padding: 1.5rem; text-align: center; border-left: 4px solid #dc3545;">
                <h3 style="color: #dc3545; font-size: 2rem; margin-bottom: 0.5rem;"><?= $oficiosInactivos ?></h3>
                <p style="margin: 0; color: #6c757d; font-weight: 500;">Oficios Inactivos</p>
            </div>
        </div>
    </div>
</section>

<!-- Controles de Búsqueda y Filtros -->
<section class="controls-section" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem 1rem;">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="search-box">
                <input type="text" id="searchInput" class="form-control" placeholder="🔍 Buscar categorías u oficios..." style="border-radius: 25px; padding: 0.75rem 1.5rem; border: 2px solid #e9ecef; font-size: 0.95rem;">
            </div>
        </div>
        <div class="col-md-3">
            <select id="filterPopular" class="form-select" style="border-radius: 20px; padding: 0.75rem 1rem; border: 2px solid #e9ecef;">
                <option value="todos">📊 Todos los oficios</option>
                <option value="populares">🔥 Solo populares</option>
                <option value="no-populares">⚪ No populares</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="d-flex gap-2">
                <button id="reloadBtn" class="btn btn-outline-primary" style="border-radius: 20px; padding: 0.75rem 1rem; white-space: nowrap;">
                    <i class="fas fa-sync-alt"></i> Recargar
                </button>
                <button id="newCategoryBtn" class="btn btn-success" style="border-radius: 20px; padding: 0.75rem 1rem; white-space: nowrap;" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="fas fa-plus"></i> Nueva
                </button>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Controles:</strong> 🔥 = Popular | ✏️ = Editar | �️ = Eliminar | ➕ = Nuevo oficio. Usa los filtros para encontrar rápidamente lo que necesitas.
    </div>
</section>

<!-- Sección de Categorías con diseño similar a index.php -->
<section class="categories-section" style="max-width: 1400px; margin: 0 auto; padding: 0 1.5rem 3rem;">
    <div id="categoriesContainer"></div>

    <!-- El contenido se carga dinámicamente aquí -->
</section>

<!-- Modal para Categoría -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-folder"></i> <span id="categoryModalTitle">Nueva Categoría</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Nombre de la categoría</label>
                        <input type="text" class="form-control" id="categoryName" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryIcon" class="form-label">Icono (FontAwesome)</label>
                        <input type="text" class="form-control" id="categoryIcon" name="icono" placeholder="ej: fas fa-briefcase">
                        <div class="form-text">Usa clases de FontAwesome como fas fa-briefcase</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Oficio -->
<div class="modal fade" id="oficioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-briefcase"></i> <span id="oficioModalTitle">Nuevo Oficio</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="oficioForm">
                    <input type="hidden" id="oficioId" name="id">
                    <input type="hidden" id="oficioCategoriaId" name="categoria_id">
                    <div class="mb-3">
                        <label for="oficioName" class="form-label">Nombre del oficio</label>
                        <input type="text" class="form-control" id="oficioName" name="titulo" required>
                    </div>
                    <div class="mb-3">
                        <label for="oficioDescription" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="oficioDescription" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="oficioPopular" name="popular">
                            <label class="form-check-label" for="oficioPopular">
                                🔥 Marcar como popular
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="oficioActivo" name="activo" checked>
                            <label class="form-check-label" for="oficioActivo">
                                ✅ Oficio activo
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveOficioBtn">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage">¿Estás seguro de realizar esta acción?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let categorias = <?= json_encode($categorias) ?>;
let oficiosPorCategoria = <?= json_encode($oficiosPorCategoria) ?>;
let confirmAction = null;

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', async function() {
    console.log('🚀 Iniciando CRUD de Categorías y Oficios...');
    
    // Event listeners
    document.getElementById('searchInput').addEventListener('input', filterCategories);
    document.getElementById('filterPopular').addEventListener('change', filterCategories);
    document.getElementById('reloadBtn').addEventListener('click', reloadData);
    document.getElementById('saveCategoryBtn').addEventListener('click', saveCategory);
    document.getElementById('saveOficioBtn').addEventListener('click', saveOficio);
    document.getElementById('confirmActionBtn').addEventListener('click', executeConfirmAction);
    
    // Cargar datos iniciales
    await loadStats();
    await reloadData();
});

// Cargar y renderizar categorías y oficios
function loadCategoriasOficios() {
    const container = document.getElementById('categoriesContainer');
    let html = '<div class="categories-tree">';
    
    categorias.forEach(categoria => {
        const oficios = oficiosPorCategoria[categoria.id] || [];
        const oficiosCount = oficios.length;
        
        html += `
        <div class="category-card admin-category-card" data-category-id="${categoria.id}" data-category-name="${categoria.nombre.toLowerCase()}">
            <h3 class="category-title">
                <span class="category-icon">
                    ${categoria.icono ? `<i class="${categoria.icono}"></i>` : '<i class="fas fa-briefcase"></i>'}
                </span>
                ${categoria.nombre}
                <div class="category-actions" style="margin-left: auto; display: flex; gap: 0.5rem; align-items: center;">
                    <span class="badge" style="background: #ffd700; color: #002b47; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 12px;">
                        ${oficiosCount} oficios
                    </span>
                    <button class="btn btn-success btn-sm" onclick="newOficio(${categoria.id})" title="Nuevo oficio">
                        <i class="fas fa-plus"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editCategory(${categoria.id}, '${categoria.nombre}', '${categoria.icono || ''}')" title="Editar categoría">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteCategory(${categoria.id}, '${categoria.nombre}')" title="Eliminar categoría">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </h3>`;
            
        if (oficios.length > 0) {
            html += '<ul class="subcategories list-unstyled mb-0">';
            oficios.forEach(oficio => {
                html += `
                <li class="oficio-item" data-oficio-name="${oficio.nombre.toLowerCase()}" data-oficio-popular="${oficio.popular}" style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0;">
                    <span style="color: #333; font-size: 0.95rem; flex: 1;">
                        ${oficio.nombre}
                    </span>
                    <div class="oficio-actions" style="display: flex; align-items: center; gap: 0.5rem;">
                        <img 
                            src="${window.location.origin}/camella.com.co/assets/images/app/${oficio.popular == 1 ? 'candela1.png' : 'candela0.png'}" 
                            alt="${oficio.popular == 1 ? 'Popular' : 'No popular'}"
                            title="Clic para cambiar popularidad"
                            class="candela-toggle"
                            data-id="${oficio.id}"
                            data-popular="${oficio.popular}"
                            style="width: 22px; height: 22px; cursor: pointer; transition: all 0.2s ease; ${oficio.popular == 0 ? 'opacity: 0.5;' : 'opacity: 1;'}"
                            onclick="togglePopular(this)">
                        <button class="btn btn-outline-primary btn-sm" onclick="editOficio(${oficio.id}, '${oficio.nombre}', ${categoria.id})" title="Editar oficio">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteOficio(${oficio.id}, '${oficio.nombre}')" title="Eliminar oficio">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>`;
            });
            html += '</ul>';
        } else {
            html += '<ul class="subcategories"><li style="font-style: italic; color: #999;">No hay oficios registrados en esta categoría</li></ul>';
        }
        
        html += '</div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Filtrar categorías y oficios
function filterCategories() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const popularFilter = document.getElementById('filterPopular').value;
    
    document.querySelectorAll('.category-card').forEach(card => {
        const categoryName = card.dataset.categoryName;
        const oficios = card.querySelectorAll('.oficio-item');
        let showCategory = false;
        
        // Filtrar por nombre de categoría
        const categoryMatch = categoryName.includes(searchTerm);
        
        // Filtrar oficios
        oficios.forEach(oficio => {
            const oficioName = oficio.dataset.oficioName;
            const oficioPopular = oficio.dataset.oficioPopular;
            
            let showOficio = oficioName.includes(searchTerm) || categoryMatch;
            
            // Aplicar filtro de popularidad
            if (popularFilter === 'populares') {
                showOficio = showOficio && oficioPopular == '1';
            } else if (popularFilter === 'no-populares') {
                showOficio = showOficio && oficioPopular == '0';
            }
            
            oficio.style.display = showOficio ? 'flex' : 'none';
            
            if (showOficio) {
                showCategory = true;
            }
        });
        
        // Mostrar categoría si coincide el nombre o tiene oficios visibles
        if (categoryMatch || showCategory) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Recargar datos
async function reloadData() {
    const btn = document.getElementById('reloadBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
    btn.disabled = true;
    
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/OficioController.php?action=listAll`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Actualizar datos globales
            categorias = data.data.categorias || [];
            oficiosPorCategoria = data.data.oficios || {};
            
            // Recargar estadísticas y vista
            await loadStats();
            loadCategoriasOficios();
            
            showNotification('✅ Datos actualizados correctamente', 'success');
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
        
    } catch (error) {
        console.error('Error al recargar datos:', error);
        showNotification(`❌ Error al recargar datos: ${error.message}`, 'danger');
    } finally {
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Recargar';
        btn.disabled = false;
    }
}

// Cargar estadísticas
async function loadStats() {
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/OficioController.php?action=stats`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalCategorias').textContent = data.data.totalCategorias || 0;
            document.getElementById('totalOficios').textContent = data.data.totalOficios || 0;
            document.getElementById('oficiosPopulares').textContent = data.data.oficiosPopulares || 0;
            document.getElementById('oficiosInactivos').textContent = data.data.oficiosInactivos || 0;
        } else {
            console.error('Error cargando estadísticas:', data.message);
        }
        
    } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Valores por defecto en caso de error
        document.getElementById('totalCategorias').textContent = '0';
        document.getElementById('totalOficios').textContent = '0';
        document.getElementById('oficiosPopulares').textContent = '0';
        document.getElementById('oficiosInactivos').textContent = '0';
    }
}

// CRUD Categorías
function editCategory(id, nombre, icono) {
    document.getElementById('categoryModalTitle').textContent = 'Editar Categoría';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = nombre;
    document.getElementById('categoryIcon').value = icono;
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}

function deleteCategory(id, nombre) {
    confirmAction = () => executeCategoryDelete(id);
    document.getElementById('confirmMessage').textContent = `¿Eliminar la categoría "${nombre}"? Se eliminarán también todos sus oficios.`;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

async function saveCategory() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    const isEdit = formData.get('id') !== '';
    
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const action = isEdit ? 'update' : 'create';
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/CategoriaController.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`✅ Categoría ${isEdit ? 'actualizada' : 'creada'} correctamente`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
            form.reset();
            await reloadData(); // Recargar datos
        } else {
            showNotification(`❌ ${data.message}`, 'danger');
        }
        
    } catch (error) {
        console.error('Error al guardar categoría:', error);
        showNotification('❌ Error al guardar la categoría', 'danger');
    }
}

async function executeCategoryDelete(id) {
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/CategoriaController.php?action=delete&id=${id}`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('✅ Categoría eliminada correctamente', 'success');
            await reloadData(); // Recargar datos
        } else {
            showNotification(`❌ ${data.message}`, 'danger');
        }
        
    } catch (error) {
        console.error('Error al eliminar categoría:', error);
        showNotification('❌ Error al eliminar la categoría', 'danger');
    }
}

// CRUD Oficios
function newOficio(categoriaId) {
    document.getElementById('oficioModalTitle').textContent = 'Nuevo Oficio';
    document.getElementById('oficioForm').reset();
    document.getElementById('oficioCategoriaId').value = categoriaId;
    document.getElementById('oficioActivo').checked = true;
    new bootstrap.Modal(document.getElementById('oficioModal')).show();
}

function editOficio(id, nombre, categoriaId) {
    document.getElementById('oficioModalTitle').textContent = 'Editar Oficio';
    document.getElementById('oficioId').value = id;
    document.getElementById('oficioName').value = nombre;
    document.getElementById('oficioCategoriaId').value = categoriaId;
    // Aquí cargarías los datos completos del oficio
    new bootstrap.Modal(document.getElementById('oficioModal')).show();
}

function deleteOficio(id, nombre) {
    confirmAction = () => executeOficioDelete(id);
    document.getElementById('confirmMessage').textContent = `¿Eliminar el oficio "${nombre}"?`;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

async function saveOficio() {
    const form = document.getElementById('oficioForm');
    const formData = new FormData(form);
    const isEdit = formData.get('id') !== '';
    
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const action = isEdit ? 'update' : 'create';
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/OficioController.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(`✅ Oficio ${isEdit ? 'actualizado' : 'creado'} correctamente`, 'success');
            bootstrap.Modal.getInstance(document.getElementById('oficioModal')).hide();
            form.reset();
            await reloadData(); // Recargar datos
        } else {
            showNotification(`❌ ${data.message}`, 'danger');
        }
        
    } catch (error) {
        console.error('Error al guardar oficio:', error);
        showNotification('❌ Error al guardar el oficio', 'danger');
    }
}

async function executeOficioDelete(id) {
    try {
        const baseUrl = window.location.protocol + '//' + window.location.hostname;
        const response = await fetch(`${baseUrl}/camella.com.co/controllers/OficioController.php?action=delete&id=${id}`, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('✅ Oficio eliminado correctamente', 'success');
            await reloadData(); // Recargar datos
        } else {
            showNotification(`❌ ${data.message}`, 'danger');
        }
        
    } catch (error) {
        console.error('Error al eliminar oficio:', error);
        showNotification('❌ Error al eliminar el oficio', 'danger');
    }
}

function executeConfirmAction() {
    if (confirmAction) {
        confirmAction();
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    }
}

// Toggle popularidad (funcionalidad existente mejorada)
async function togglePopular(element) {
    const id = element.dataset.id;
    const popularActual = element.dataset.popular;
    
    console.log('🔥 Toggle popular - ID:', id, 'Estado actual:', popularActual);
    
    // Feedback visual
    const opacidadOriginal = element.style.opacity;
    element.style.opacity = '0.5';
    element.style.pointerEvents = 'none';

    try {
        const baseUrl = window.location.origin;
        const url = `${baseUrl}/camella.com.co/controllers/OficioController.php?action=togglePopular&id=${id}`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }
        
        const data = await response.json();

        if (data.success) {
            // Actualizar imagen y estado
            const nuevaImagen = data.newState == 1 
                ? `${baseUrl}/camella.com.co/assets/images/app/candela1.png`
                : `${baseUrl}/camella.com.co/assets/images/app/candela0.png`;
            
            const nuevaOpacidad = data.newState == 1 ? '1' : '0.5';
            
            element.dataset.popular = data.newState;
            element.parentElement.parentElement.dataset.oficioPopular = data.newState;
            
            // Animación suave
            element.style.transition = 'opacity 0.3s ease';
            element.style.opacity = '0';
            
            setTimeout(() => {
                element.src = nuevaImagen;
                element.style.opacity = nuevaOpacidad;
                element.style.pointerEvents = 'auto';
                
                // Efecto de brillo
                element.animate([
                    { filter: 'brightness(2) drop-shadow(0 0 8px #ffd700)' }, 
                    { filter: 'brightness(1) drop-shadow(0 0 0 transparent)' }
                ], { 
                    duration: 500,
                    easing: 'ease-out'
                });
            }, 150);

            showNotification(
                data.newState == 1 ? '✅ Marcado como popular' : '⚪ Desmarcado', 
                'success'
            );
        } else {
            element.style.opacity = opacidadOriginal;
            element.style.pointerEvents = 'auto';
            showNotification('❌ Error: ' + (data.message || 'No se pudo actualizar'), 'danger');
        }
    } catch (error) {
        console.error('❌ Error en togglePopular:', error);
        element.style.opacity = opacidadOriginal;
        element.style.pointerEvents = 'auto';
        showNotification('❌ Error de conexión', 'danger');
    }
}
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

/* Estilos adicionales para CRUD */
.category-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.category-card:hover .category-actions {
    opacity: 1;
}

.oficio-actions {
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.oficio-item:hover .oficio-actions {
    opacity: 1;
}

.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}

.search-box input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
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
        flex-direction: column;
        align-items: flex-start;
    }
    
    .category-actions {
        opacity: 1;
        margin-top: 0.5rem;
        margin-left: 0 !important;
    }
    
    .stats-section .row {
        margin: 0 -0.5rem;
    }
    
    .stats-section .col-6 {
        padding: 0 0.5rem;
        margin-bottom: 1rem;
    }
    
    .controls-section .col-md-3 {
        margin-bottom: 1rem;
    }
}
</style>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>

<!-- Última actualización: CRUD + filtros + stats + AJAX | Camella.com.co -->
