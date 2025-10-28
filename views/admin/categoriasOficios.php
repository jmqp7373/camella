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
    $stmt = $pdo->prepare("SELECT id, titulo as nombre, popular, activo FROM oficios WHERE categoria_id = ? ORDER BY activo DESC, popular DESC, titulo ASC");
    $stmt->execute([$categoria['id']]);
    $oficios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Mostrar TODOS los oficios (activos e inactivos)
    $oficiosPorCategoria[$categoria['id']] = $oficios;
    
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

<!-- Hero Section RESTAURADO -->
<div class="home-hero">
    <h1 class="page-title text-azul" style="margin-bottom: 10px;">
        <i class="fas fa-layer-group"></i> 
        Gestión de Categorías y Oficios
    </h1>
    <p class="page-subtitle" style="margin-bottom: 1rem; line-height: 1.5;">
        Administra los oficios y marca cuáles están en alta demanda
    </p>
    <a id="btnBackToDashboard" class="btn btn-outline-light btn-sm" href="/camella.com.co/views/admin/dashboard.php">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>

<!-- BLOQUE: TOTALES -->
<section class="container mb-4">
    <div class="admin-block">
        <h2 class="admin-block-title">
            <i class="fas fa-chart-line"></i> Totales
        </h2>
        <div class="admin-block-content">
            <div class="stats-horizontal">
                <div class="stat-item stat-item-primary">
                    <div class="stat-icon-compact">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label-compact">Total Categorías</div>
                        <div id="statTotalCategorias" class="stat-value-compact"><?= $totalCategorias ?></div>
                    </div>
                </div>
                
                <div class="stat-item stat-item-info">
                    <div class="stat-icon-compact">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label-compact">Total Oficios</div>
                        <div id="statTotalOficios" class="stat-value-compact"><?= $totalOficios ?></div>
                    </div>
                </div>
                
                <div class="stat-item stat-item-success">
                    <div class="stat-icon-compact">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label-compact">Oficios Populares</div>
                        <div id="statPopulares" class="stat-value-compact"><?= $oficiosPopulares ?></div>
                    </div>
                </div>
                
                <div class="stat-item stat-item-warning">
                    <div class="stat-icon-compact">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label-compact">Oficios Inactivos</div>
                        <div id="statInactivos" class="stat-value-compact"><?= $oficiosInactivos ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BLOQUE: BÚSQUEDA -->
<section class="container mb-4">
    <div class="admin-block">
        <h2 class="admin-block-title">
            <i class="fas fa-search"></i> Búsqueda y Filtros
        </h2>
        <div class="admin-block-content">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input id="searchInput" type="search" class="form-control search-input"
                           placeholder="Buscar categorías u oficios…" autocomplete="off"
                           aria-label="Buscar">
                    <button id="clearSearch" class="btn-clear-search" style="display: none;" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="filter-wrapper">
                    <label for="filterPopular" class="filter-label">
                        <i class="fas fa-filter"></i> Filtrar:
                    </label>
                    <select id="filterPopular" class="form-select filter-select" aria-label="Filtrar por popularidad">
                        <option value="all">📋 Todos</option>
                        <option value="popular">🔥 Solo populares</option>
                        <option value="nopopular">⭕ No populares</option>
                    </select>
                </div>
            </div>
            
            <!-- Contador de resultados -->
            <div id="searchResults" class="search-results-info" style="display: none;">
                <i class="fas fa-info-circle"></i>
                <span id="resultsText">Mostrando todos los resultados</span>
            </div>
        </div>
    </div>
</section>

<!-- BLOQUE: CATEGORÍAS Y OFICIOS -->
<section class="container mb-4">
    <div class="admin-block">
        <h2 class="admin-block-title">
            <i class="fas fa-layer-group"></i> Categorías y Oficios
        </h2>
        <div class="admin-block-content">
            <div class="categories-tree">
                <?php if (!empty($categorias)): ?>
            <?php foreach ($categorias as $categoria): ?>
                <div class="category-card" data-categoria-id="<?= $categoria['id'] ?>">
                    <h3 class="category-title">
                        <!-- 1. Viñeta (icono de categoría) -->
                        <span class="category-icon">
                            <i class="<?= htmlspecialchars($categoria['icono'] ?: 'fas fa-briefcase') ?>"></i>
                        </span>
                        
                        <!-- 2. Input editable inline para categoría -->
                        <input type="text" 
                               class="categoria-nombre-input"
                               data-categoria-id="<?= $categoria['id'] ?>"
                               value="<?= htmlspecialchars($categoria['nombre']) ?>"
                               title="Haz clic para editar. Presiona Enter o pierde el foco para guardar"
                               style="border: none; background: transparent; font-size: inherit; font-weight: inherit; padding: 2px 8px; flex: 1; min-width: 200px; max-width: 400px; color: inherit;">
                        
                        <!-- Acciones de admin inline -->
                        <span style="margin-left: auto; display: flex; gap: 0.5rem; align-items: center; font-size: 0.85rem;">
                            <!-- 5. Eliminar -->
                            <img src="assets/images/app/delete-icon.svg" 
                                 alt="Eliminar" 
                                 onclick="deleteCategoria(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nombre']) ?>')" 
                                 title="Eliminar categoría"
                                 style="width: 20px; height: 20px; cursor: pointer;">
                        </span>
                    </h3>
                    
                    <?php 
                    $oficios = $oficiosPorCategoria[$categoria['id']] ?? [];
                    if (!empty($oficios)): 
                    ?>
                        <ul class="subcategories">
                            <?php foreach ($oficios as $oficio): ?>
                                <li class="oficio-item <?= $oficio['activo'] == 0 ? 'oficio-inactivo' : '' ?>" 
                                    data-oficio-id="<?= $oficio['id'] ?>" 
                                    data-name="<?= strtolower($oficio['nombre']) ?>"
                                    data-popular="<?= $oficio['popular'] ?>"
                                    data-activo="<?= $oficio['activo'] ?>">
                                    
                                    <!-- Wrapper completo con todos los elementos -->
                                    <div style="display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.5rem; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef;">
                                        <!-- 1. Viñeta (flechita a la derecha) -->
                                        <i class="fas fa-chevron-right" style="font-size: 0.7rem; color: #6c757d;"></i>
                                        
                                        <!-- 2. Input editable inline (Texto del oficio) -->
                                        <input type="text" 
                                               class="oficio-nombre-input <?= $oficio['activo'] == 0 ? 'nombre-tachado' : '' ?>"
                                               data-oficio-id="<?= $oficio['id'] ?>"
                                               value="<?= htmlspecialchars($oficio['nombre']) ?>"
                                               title="Haz clic para editar. Presiona Enter o pierde el foco para guardar"
                                               style="border: none; background: transparent; font-size: inherit; padding: 2px 4px; flex: 1; min-width: 150px; max-width: 300px;">
                                        
                                        <!-- Controles a la derecha -->
                                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-left: auto;">
                                            <!-- 3. Toggle Switch Activo/Inactivo -->
                                            <label class="toggle-switch" title="<?= $oficio['activo'] == 1 ? 'Activo - Clic para desactivar' : 'Inactivo - Clic para activar' ?>">
                                                <input type="checkbox" 
                                                       class="toggle-checkbox"
                                                       data-toggle-activo="<?= $oficio['id'] ?>"
                                                       <?= $oficio['activo'] == 1 ? 'checked' : '' ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            
                                            <!-- 4. Popular (candelita) -->
                                            <?php if ($oficio['popular'] == 1): ?>
                                                <img src="<?= app_url('assets/images/app/candela1.png') ?>" 
                                                     alt="Popular" 
                                                     class="flamita-popular"
                                                     data-toggle-popular="<?= $oficio['id'] ?>"
                                                     title="Clic para quitar popularidad"
                                                     style="cursor: pointer; width: 20px; height: 20px; object-fit: contain;">
                                            <?php else: ?>
                                                <img src="<?= app_url('assets/images/app/candela1.png') ?>" 
                                                     alt="No popular" 
                                                     class="flamita-no-popular"
                                                     data-toggle-popular="<?= $oficio['id'] ?>"
                                                     title="Clic para marcar popular"
                                                     style="cursor: pointer; width: 20px; height: 20px; object-fit: contain; opacity: 0.3; filter: grayscale(100%);">
                                            <?php endif; ?>
                                            
                                            <!-- 5. Eliminar -->
                                            <img src="<?= app_url('assets/images/app/delete-icon.svg') ?>" 
                                                 alt="Eliminar" 
                                                 onclick="deleteOficio(<?= $oficio['id'] ?>, '<?= htmlspecialchars($oficio['nombre']) ?>')" 
                                                 title="Eliminar"
                                                 style="width: 20px; height: 20px; cursor: pointer;">
                                        </div>
                                    </div>
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
        <?php else: ?>
            <div class="category-card">
                <h3 class="category-title">
                    <span class="category-icon"><i class="fas fa-cog"></i></span>
                    No hay categorías
                </h3>
                <ul class="subcategories">
                    <li style="font-style: italic; color: #666;">
                        No se encontraron categorías. <a href="javascript:location.reload()">Actualizar</a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CAMELLA: MODAL CATEGORIA -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formCategoria" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva / Editar categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="catId">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="catNombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icono (FontAwesome)</label>
                    <input type="text" name="icono" id="catIcono" class="form-control" placeholder="fa-solid fa-broom">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveCategoria">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- CAMELLA: MODAL OFICIO -->
<div class="modal fade" id="modalOficio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formOficio" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo / Editar oficio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="ofId">
                <input type="hidden" name="categoria_id" id="ofCatId">
                <div class="mb-3">
                    <label class="form-label">Nombre del oficio</label>
                    <input type="text" name="titulo" id="ofTitulo" class="form-control" required>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="ofPopular" name="popular" value="1">
                    <label class="form-check-label" for="ofPopular">Marcar como popular</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ofActivo" name="activo" value="1" checked>
                    <label class="form-check-label" for="ofActivo">Oficio activo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSaveOficio">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// Helper para construir URLs correctas según el entorno
function getControllerUrl(path) {
    const baseUrl = window.location.origin;
    const isLocalhost = baseUrl.includes('localhost') || baseUrl.includes('127.0.0.1');
    
    if (isLocalhost) {
        return `${baseUrl}/camella.com.co/${path}`;
    } else {
        // En producción, usar ruta relativa desde la raíz
        return `${baseUrl}/${path}`;
    }
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('=== Iniciando Admin Categorías y Oficios ===');
    
    try {
        fixBackButton();
        wireSearchAndFilters();
        wirePopularToggle();
        wireActivoToggle();
        wireInlineEdit();
        wireCRUDButtons();
        loadStats();
        
        console.log('✓ Todas las funciones inicializadas correctamente');
    } catch (error) {
        console.error('Error durante la inicialización:', error);
    }
});

// Asegura que el botón vuelve al dashboard correcto
function fixBackButton(){
    const btn = document.getElementById('btnBackToDashboard');
    if (!btn) return;
    const href = btn.getAttribute('href');
    if (!href || href === '#' || href.startsWith('javascript')) {
        btn.setAttribute('href','/camella.com.co/views/admin/dashboard.php');
    }
}

// Búsqueda + filtro popular sin tocar la maquetación
function wireSearchAndFilters(){
    const q = document.getElementById('searchInput');
    const filter = document.getElementById('filterPopular');
    
    if (!q || !filter) {
        console.error('Elementos de búsqueda no encontrados');
        alert('ERROR: No se encontraron los elementos de búsqueda. Revisa la consola.');
        return;
    }

    const apply = () => {
        const text = q.value.toLowerCase().trim();
        const mode = filter.value;
        
        console.log('🔍 Buscando:', text, 'Filtro:', mode);
        
        // Buscar todas las tarjetas de categorías
        const cards = document.querySelectorAll('.category-card');
        console.log('📦 Tarjetas encontradas:', cards.length);
        
        let totalVisible = 0;
        let totalOficios = 0;
        
        cards.forEach(card => {
            // Obtener nombre de la categoría (primer hijo del h3)
            const categoryTitle = card.querySelector('.category-title');
            const categoryText = categoryTitle ? categoryTitle.childNodes[2]?.textContent?.trim().toLowerCase() : '';
            const matchCategory = !text || categoryText.includes(text);
            
            // Buscar todos los oficios dentro de esta categoría
            const oficios = card.querySelectorAll('.oficio-item');
            let visibleOficios = 0;
            
            oficios.forEach(oficio => {
                totalOficios++;
                // Obtener el nombre del oficio (primer span)
                const nombreSpan = oficio.querySelector('span:first-child');
                const oficioName = oficio.dataset.name || '';
                const oficioPopular = oficio.dataset.popular === '1';
                
                // Verificar si coincide con la búsqueda de texto
                const matchText = !text || oficioName.includes(text) || matchCategory;
                
                // Verificar si coincide con el filtro de popularidad
                let matchFilter = false;
                if (mode === 'all') {
                    matchFilter = true;
                } else if (mode === 'popular') {
                    matchFilter = oficioPopular;
                } else if (mode === 'nopopular') {
                    matchFilter = !oficioPopular;
                }
                
                const shouldShow = matchText && matchFilter;
                oficio.style.display = shouldShow ? '' : 'none';
                
                if (shouldShow) visibleOficios++;
            });
            
            // Mostrar/ocultar la tarjeta completa
            const shouldShowCard = visibleOficios > 0 || (matchCategory && text && oficios.length === 0);
            card.style.display = shouldShowCard ? '' : 'none';
            
            if (shouldShowCard) totalVisible++;
        });
        
        console.log('✅ Categorías visibles:', totalVisible, '/ Total oficios:', totalOficios);
        
        // Actualizar contador de resultados
        updateSearchResults(text, mode, totalVisible, cards.length);
        
        // Mostrar/ocultar botón de limpiar
        const clearBtn = document.getElementById('clearSearch');
        if (clearBtn) {
            clearBtn.style.display = text ? 'block' : 'none';
        }
        
        showNoResultsMessage(totalVisible === 0 && (text || mode !== 'all'));
    };
    
    // Event listeners
    q.addEventListener('input', () => {
        console.log('📝 Input detectado');
        apply();
    });
    
    filter.addEventListener('change', () => {
        console.log('🔄 Filtro cambiado');
        apply();
    });
    
    // Botón limpiar búsqueda
    const clearBtn = document.getElementById('clearSearch');
    clearBtn?.addEventListener('click', () => {
        q.value = '';
        filter.value = 'all';
        apply();
        q.focus();
    });
    
    // Limpiar búsqueda con Escape
    q.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            q.value = '';
            filter.value = 'all';
            apply();
        }
    });
    
    console.log('✓ Búsqueda y filtros inicializados correctamente');
}

function updateSearchResults(searchText, filterMode, visible, total) {
    const resultsDiv = document.getElementById('searchResults');
    const resultsText = document.getElementById('resultsText');
    
    if (!resultsDiv || !resultsText) return;
    
    let message = '';
    let showResults = false;
    
    if (searchText || filterMode !== 'all') {
        showResults = true;
        if (searchText && filterMode !== 'all') {
            const filterName = filterMode === 'popular' ? 'populares' : 'no populares';
            message = `🔍 Buscando "${searchText}" en oficios ${filterName}: ${visible} de ${total} categorías`;
        } else if (searchText) {
            message = `🔍 Resultados para "${searchText}": ${visible} de ${total} categorías`;
        } else if (filterMode !== 'all') {
            const filterName = filterMode === 'popular' ? 'populares' : 'no populares';
            message = `🔥 Mostrando solo oficios ${filterName}: ${visible} categorías`;
        }
    }
    
    resultsText.textContent = message;
    resultsDiv.style.display = showResults ? 'flex' : 'none';
}

function showNoResultsMessage(show) {
    let msg = document.getElementById('noResultsMessage');
    if (show && !msg) {
        msg = document.createElement('div');
        msg.id = 'noResultsMessage';
        msg.className = 'alert alert-info text-center my-4';
        msg.innerHTML = '<i class="fas fa-search"></i> No se encontraron resultados. Intenta con otros términos de búsqueda.';
        document.querySelector('.categories-tree')?.prepend(msg);
    } else if (!show && msg) {
        msg.remove();
    }
}

// Toggle popular usando el markup con imágenes - CON AJAX SIN RECARGAR
function wirePopularToggle(){
    document.body.addEventListener('click', async (ev) => {
        const t = ev.target.closest('[data-toggle-popular]');
        if (!t) return;
        ev.preventDefault();
        ev.stopPropagation();
        
        const id = t.getAttribute('data-toggle-popular');
        
        console.log('🔥 Toggling popular para oficio:', id);
        
        // Deshabilitar temporalmente para evitar clicks múltiples
        const wasDisabled = t.style.pointerEvents;
        t.style.pointerEvents = 'none';
        
        // Animación de "pulsación" al hacer click
        t.style.transform = 'scale(0.85)';
        setTimeout(() => {
            t.style.transform = '';
        }, 150);
        
        try {
            // Construir URL usando helper
            const url = getControllerUrl(`controllers/OficioController.php?action=togglePopular&id=${encodeURIComponent(id)}`);
            console.log('📡 Enviando petición a:', url);
            
            const r = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            });
            
            console.log('📥 Respuesta recibida. Status:', r.status);
            
            if (!r.ok) {
                const errorText = await r.text();
                console.error('❌ Error del servidor:', errorText);
                throw new Error('Error en la petición: ' + r.status);
            }
            
            const data = await r.json();
            console.log('📦 Data recibida:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Error al cambiar estado');
            }
            
            const popular = !!(data?.newState == 1);
            
            console.log('✅ Estado popular actualizado:', popular ? 'ENCENDIDA 🔥' : 'APAGADA ⚪');
            
            // Actualizar estilos de la imagen con transición suave
            if (t.tagName === 'IMG') {
                // Transición suave
                t.style.transition = 'all 0.4s ease';
                
                if (popular) {
                    // 🔥 Flamita ENCENDIDA (popular)
                    t.style.opacity = '1';
                    t.style.filter = 'none';
                    t.className = 'flamita-popular';
                    t.title = '🔥 Popular - Clic para apagar';
                    
                    // Mini animación de "encendido"
                    t.style.transform = 'scale(1.3) rotate(10deg)';
                    setTimeout(() => {
                        t.style.transform = '';
                    }, 300);
                    
                } else {
                    // ⚪ Flamita APAGADA (no popular)
                    t.style.opacity = '0.3';
                    t.style.filter = 'grayscale(100%)';
                    t.className = 'flamita-no-popular';
                    t.title = '⚪ No popular - Clic para encender';
                    
                    // Mini animación de "apagado"
                    t.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        t.style.transform = '';
                    }, 300);
                }
            }
            
            // Actualizar data-attribute del li
            const li = t.closest('.oficio-item, [data-role="oficio-item"]');
            if (li) {
                li.dataset.popular = popular ? '1' : '0';
                li.classList.toggle('is-popular', popular);
            }
            
            // Recargar estadísticas SIN recargar la página
            loadStats();
            
            // Mostrar notificación visual sutil
            showToast(popular ? '🔥 Oficio marcado como popular' : '⚪ Oficio desmarcado como popular');
            
        } catch(e){ 
            console.error('❌ togglePopular failed', e);
            alert('Error al cambiar el estado: ' + e.message);
        } finally {
            // Rehabilitar el click después de un momento
            setTimeout(() => {
                t.style.pointerEvents = wasDisabled;
            }, 500);
        }
    });
}

// Mostrar notificación toast sutil
function showToast(message) {
    // Eliminar toast anterior si existe
    const oldToast = document.getElementById('toggleToast');
    if (oldToast) oldToast.remove();
    
    const toast = document.createElement('div');
    toast.id = 'toggleToast';
    toast.className = 'toggle-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Mostrar
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Ocultar y eliminar
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Toggle activo/inactivo con AJAX
function wireActivoToggle(){
    document.body.addEventListener('change', async (ev) => {
        const t = ev.target;
        if (!t.matches('[data-toggle-activo]')) return;
        
        const id = t.getAttribute('data-toggle-activo');
        const nuevoEstado = t.checked ? 1 : 0;
        
        console.log('🔄 Toggling activo para oficio:', id, '→', nuevoEstado);
        
        // Deshabilitar temporalmente
        const wasDisabled = t.disabled;
        t.disabled = true;
        
        try {
            const url = getControllerUrl(`controllers/OficioController.php?action=toggleActivo&id=${encodeURIComponent(id)}`);
            console.log('📡 Enviando petición a:', url);
            
            const r = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            });
            
            console.log('📥 Respuesta recibida. Status:', r.status);
            
            if (!r.ok) {
                const errorText = await r.text();
                console.error('❌ Error del servidor:', errorText);
                throw new Error('Error en la petición: ' + r.status);
            }
            
            const data = await r.json();
            console.log('📦 Data recibida:', data);
            
            if (!data.success) {
                throw new Error(data.message || 'Error al cambiar estado');
            }
            
            const activo = !!(data?.newState == 1);
            
            console.log('✅ Estado activo actualizado:', activo ? 'ACTIVO ✓' : 'INACTIVO ✕');
            
            // Actualizar el li con la clase oficio-inactivo
            const li = t.closest('.oficio-item');
            if (li) {
                li.dataset.activo = activo ? '1' : '0';
                
                // Toggle clase oficio-inactivo
                if (activo) {
                    li.classList.remove('oficio-inactivo');
                } else {
                    li.classList.add('oficio-inactivo');
                }
                
                // Actualizar el nombre (tachado o no)
                const nombreInput = li.querySelector('.oficio-nombre-input');
                if (nombreInput) {
                    if (activo) {
                        nombreInput.classList.remove('nombre-tachado');
                    } else {
                        nombreInput.classList.add('nombre-tachado');
                    }
                }
            }
            
            // Actualizar título del toggle
            const label = t.closest('.toggle-switch');
            if (label) {
                label.title = activo ? 'Activo - Clic para desactivar' : 'Inactivo - Clic para activar';
            }
            
            // Recargar estadísticas
            loadStats();
            
            // Mostrar notificación
            showToast(activo ? '✅ Oficio activado' : '🔴 Oficio desactivado');
            
        } catch(e){ 
            console.error('❌ toggleActivo failed', e);
            // Revertir el checkbox al estado anterior
            t.checked = !t.checked;
            alert('Error al cambiar el estado: ' + e.message);
        } finally {
            // Rehabilitar el toggle
            setTimeout(() => {
                t.disabled = wasDisabled;
            }, 500);
        }
    });
}

// Edición inline de oficios
function wireInlineEdit(){
    document.querySelectorAll('.oficio-nombre-input').forEach(input => {
        let valorOriginal = input.value;
        
        // Guardar al presionar Enter
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur(); // Disparar el evento blur para guardar
            }
            if (e.key === 'Escape') {
                input.value = valorOriginal;
                input.blur();
            }
        });
        
        // Guardar al perder el foco
        input.addEventListener('blur', async () => {
            const nuevoNombre = input.value.trim();
            const id = input.getAttribute('data-oficio-id');
            
            // Si no cambió, no hacer nada
            if (nuevoNombre === valorOriginal) return;
            
            // Validar que no esté vacío
            if (!nuevoNombre) {
                alert('El nombre del oficio no puede estar vacío');
                input.value = valorOriginal;
                return;
            }
            
            // Mostrar estado de guardando
            input.classList.add('saving');
            
            try {
                const url = getControllerUrl('controllers/OficioController.php?action=updateNombre');
                
                const formData = new URLSearchParams();
                formData.append('id', id);
                formData.append('nombre', nuevoNombre);
                
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                if (!r.ok) {
                    throw new Error('Error en la petición: ' + r.status);
                }
                
                const data = await r.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Error al guardar');
                }
                
                // Actualizar valor original y mostrar éxito
                valorOriginal = nuevoNombre;
                input.classList.remove('saving');
                input.classList.add('saved');
                
                // Actualizar data-name del li para búsquedas
                const li = input.closest('.oficio-item');
                if (li) {
                    li.setAttribute('data-name', nuevoNombre.toLowerCase());
                }
                
                // Mostrar toast
                showToast('✅ Oficio actualizado: ' + nuevoNombre);
                
                // Quitar clase de guardado después de 1 segundo
                setTimeout(() => {
                    input.classList.remove('saved');
                }, 1000);
                
            } catch(e) {
                console.error('❌ Error al guardar nombre:', e);
                input.classList.remove('saving');
                alert('Error al guardar: ' + e.message);
                input.value = valorOriginal; // Revertir
            }
        });
        
        // Actualizar valor original cuando se hace foco
        input.addEventListener('focus', () => {
            valorOriginal = input.value;
        });
    });
    
    // Edición inline para CATEGORÍAS
    document.querySelectorAll('.categoria-nombre-input').forEach(input => {
        let valorOriginal = input.value;
        
        // Guardar al presionar Enter
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                input.blur(); // Disparar el evento blur para guardar
            }
            if (e.key === 'Escape') {
                input.value = valorOriginal;
                input.blur();
            }
        });
        
        // Guardar al perder el foco
        input.addEventListener('blur', async () => {
            const nuevoNombre = input.value.trim();
            const id = input.getAttribute('data-categoria-id');
            
            // Si no cambió, no hacer nada
            if (nuevoNombre === valorOriginal) return;
            
            // Validar que no esté vacío
            if (!nuevoNombre) {
                alert('El nombre de la categoría no puede estar vacío');
                input.value = valorOriginal;
                return;
            }
            
            // Mostrar estado de guardando
            input.classList.add('saving');
            
            try {
                const url = getControllerUrl('controllers/CategoriaController.php?action=updateNombre');
                
                const formData = new URLSearchParams();
                formData.append('id', id);
                formData.append('nombre', nuevoNombre);
                
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                if (!r.ok) {
                    throw new Error('Error en la petición: ' + r.status);
                }
                
                const data = await r.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Error al guardar');
                }
                
                // Actualizar valor original y mostrar éxito
                valorOriginal = nuevoNombre;
                input.classList.remove('saving');
                input.classList.add('saved');
                
                // Mostrar toast
                showToast('✅ Categoría actualizada: ' + nuevoNombre);
                
                // Quitar clase de guardado después de 1 segundo
                setTimeout(() => {
                    input.classList.remove('saved');
                }, 1000);
                
            } catch(e) {
                console.error('❌ Error al guardar categoría:', e);
                input.classList.remove('saving');
                alert('Error al guardar: ' + e.message);
                input.value = valorOriginal; // Revertir
            }
        });
        
        // Actualizar valor original cuando se hace foco
        input.addEventListener('focus', () => {
            valorOriginal = input.value;
        });
    });
}

// Funciones CRUD
function wireCRUDButtons(){
    // Los botones inline ya tienen onclick, solo necesitamos las funciones globales
}

// Carga de estadísticas (contadores)
async function loadStats(){
    try {
        const r = await fetch(getControllerUrl('controllers/OficioController.php?action=stats'));
        if (!r.ok) return;
        const data = await r.json();
        if (!data.success) return;
        const s = data.data;
        setText('statTotalCategorias', s.totalCategorias ?? 0);
        setText('statTotalOficios', s.totalOficios ?? 0);
        setText('statPopulares', s.oficiosPopulares ?? 0);
        setText('statInactivos', s.oficiosInactivos ?? 0);
    } catch(e){ /* silencioso */ }
}
function setText(id, val){ const el = document.getElementById(id); if (el) el.textContent = val; }

// Funciones CRUD globales llamadas desde onclick
function openOficioModal(categoriaId) {
    console.log('🟢 Abriendo modal para nueva oficio, categoría:', categoriaId);
    
    try {
        document.getElementById('ofId').value = '';
        document.getElementById('ofCatId').value = categoriaId;
        document.getElementById('ofTitulo').value = '';
        document.getElementById('ofPopular').checked = false;
        document.getElementById('ofActivo').checked = true;
        
        const modalElement = document.getElementById('modalOficio');
        if (!modalElement) {
            console.error('❌ Modal no encontrado: modalOficio');
            alert('Error: No se encontró el modal de oficios');
            return;
        }
        
        // Verificar si Bootstrap está disponible
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            console.log('✅ Modal abierto con Bootstrap');
        } else {
            console.error('❌ Bootstrap no está disponible');
            // Fallback: mostrar modal manualmente
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            modalElement.setAttribute('aria-modal', 'true');
            modalElement.removeAttribute('aria-hidden');
            
            // Crear backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = 'modalBackdrop';
            document.body.appendChild(backdrop);
            document.body.classList.add('modal-open');
            
            console.log('✅ Modal abierto manualmente (fallback)');
        }
    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
        alert('Error al abrir el modal: ' + error.message);
    }
}

function editCategoria(id, nombre, icono) {
    console.log('✏️ Editando categoría:', id, nombre);
    
    try {
        document.getElementById('catId').value = id;
        document.getElementById('catNombre').value = nombre;
        document.getElementById('catIcono').value = icono;
        
        const modalElement = document.getElementById('modalCategoria');
        if (!modalElement) {
            console.error('❌ Modal no encontrado: modalCategoria');
            return;
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            new bootstrap.Modal(modalElement).show();
        } else {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
        }
    } catch (error) {
        console.error('❌ Error al abrir modal categoría:', error);
    }
}

function openCategoriaModal() {
    console.log('🆕 Abriendo modal para nueva categoría');
    
    try {
        document.getElementById('catId').value = '';
        document.getElementById('catNombre').value = '';
        document.getElementById('catIcono').value = '';
        
        const modalElement = document.getElementById('modalCategoria');
        if (!modalElement) {
            console.error('❌ Modal no encontrado: modalCategoria');
            return;
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            new bootstrap.Modal(modalElement).show();
        } else {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
        }
    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
    }
}

function deleteCategoria(id, nombre) {
    if (confirm(`¿Eliminar la categoría "${nombre}"? Se eliminarán también todos sus oficios.`)) {
        executeCategoriaDelete(id);
    }
}

function editOficio(id, nombre, categoriaId, popular) {
    console.log('✏️ Editando oficio:', id, nombre);
    
    try {
        document.getElementById('ofId').value = id;
        document.getElementById('ofTitulo').value = nombre;
        document.getElementById('ofCatId').value = categoriaId;
        document.getElementById('ofPopular').checked = popular == 1;
        document.getElementById('ofActivo').checked = true;
        
        const modalElement = document.getElementById('modalOficio');
        if (!modalElement) {
            console.error('❌ Modal no encontrado: modalOficio');
            return;
        }
        
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            new bootstrap.Modal(modalElement).show();
        } else {
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
        }
    } catch (error) {
        console.error('❌ Error al editar oficio:', error);
    }
}

function deleteOficio(id, nombre) {
    if (confirm(`¿Eliminar el oficio "${nombre}"?`)) {
        executeOficioDelete(id);
    }
}

// Ejecutar acciones CRUD
async function executeCategoriaDelete(id) {
    try {
        const r = await fetch(getControllerUrl(`controllers/CategoriaController.php?action=delete&id=${id}`), {method: 'POST'});
        const data = await r.json();
        if (data.success) {
            alert('Categoría eliminada');
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar');
        }
    } catch(e){ alert('Error al eliminar categoría'); }
}

async function executeOficioDelete(id) {
    try {
        const r = await fetch(getControllerUrl(`controllers/OficioController.php?action=delete&id=${id}`), {method: 'POST'});
        const data = await r.json();
        if (data.success) {
            alert('Oficio eliminado');
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar');
        }
    } catch(e){ alert('Error al eliminar oficio'); }
}

// Guardar formularios
document.getElementById('btnSaveCategoria')?.addEventListener('click', async (e) => {
    e.preventDefault();
    const form = document.getElementById('formCategoria');
    const formData = new FormData(form);
    const isEdit = formData.get('id') !== '';
    
    try {
        const action = isEdit ? 'update' : 'create';
        const r = await fetch(getControllerUrl(`controllers/CategoriaController.php?action=${action}`), {
            method: 'POST',
            body: formData
        });
        
        const data = await r.json();
        
        if (data.success) {
            alert(isEdit ? 'Categoría actualizada' : 'Categoría creada');
            location.reload();
        } else {
            alert(data.message || 'Error al guardar');
        }
    } catch(e){ alert('Error al guardar categoría'); }
});

document.getElementById('btnSaveOficio')?.addEventListener('click', async (e) => {
    e.preventDefault();
    const form = document.getElementById('formOficio');
    const formData = new FormData(form);
    const isEdit = formData.get('id') !== '';
    
    try {
        const action = isEdit ? 'update' : 'create';
        const r = await fetch(getControllerUrl(`controllers/OficioController.php?action=${action}`), {
            method: 'POST',
            body: formData
        });
        
        const data = await r.json();
        
        if (data.success) {
            alert(isEdit ? 'Oficio actualizado' : 'Oficio creado');
            location.reload();
        } else {
            alert(data.message || 'Error al guardar');
        }
    } catch(e){ alert('Error al guardar oficio'); }
});
</script>

<style>
/* Bloques administrativos con título */
.admin-block {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem; /* Espacio entre bloques */
}

.admin-block-title {
    background: linear-gradient(135deg, #002b47 0%, #004975 100%);
    color: white;
    padding: 1.25rem 1.5rem; /* Más padding vertical */
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.admin-block-title i {
    font-size: 1.3rem;
}

.admin-block-content {
    padding: 2rem 1.5rem; /* Más padding vertical (antes era 1.5rem) */
}

/* Estadísticas horizontales compactas */
.stats-horizontal {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    border-left: 3px solid;
    background: #f8f9fa;
    flex: 1;
    min-width: 200px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.stat-item-primary {
    border-left-color: #002b47;
}

.stat-item-info {
    border-left-color: #17a2b8;
}

.stat-item-success {
    border-left-color: #ffc107;
}

.stat-item-warning {
    border-left-color: #ff6b6b;
}

.stat-icon-compact {
    font-size: 1.75rem;
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    flex-shrink: 0;
}

.stat-item-primary .stat-icon-compact {
    background: linear-gradient(135deg, #002b47 0%, #004975 100%);
    color: white;
}

.stat-item-info .stat-icon-compact {
    background: linear-gradient(135deg, #17a2b8 0%, #20c5db 100%);
    color: white;
}

.stat-item-success .stat-icon-compact {
    background: linear-gradient(135deg, #ffc107 0%, #ffcd38 100%);
    color: #002b47;
}

.stat-item-warning .stat-icon-compact {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff8787 100%);
    color: white;
}

.stat-info {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.stat-label-compact {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1;
}

.stat-value-compact {
    font-size: 1.5rem;
    font-weight: bold;
    color: #002b47;
    line-height: 1;
}

/* Responsive */
@media (max-width: 992px) {
    .stats-horizontal {
        gap: 0.75rem;
    }
    
    .stat-item {
        min-width: calc(50% - 0.375rem);
    }
}

@media (max-width: 576px) {
    .stat-item {
        min-width: 100%;
        padding: 0.5rem 0.75rem;
    }
    
    .stat-icon-compact {
        font-size: 1.5rem;
        width: 40px;
        height: 40px;
    }
    
    .stat-value-compact {
        font-size: 1.25rem;
    }
}

/* Ajustes para el grid de categorías dentro del bloque */
.admin-block-content .categories-tree {
    margin: 0;
}

/* Corregir desbordamiento de botones en títulos de categorías */
.category-title {
    flex-wrap: wrap;
    width: 100%;
}

.category-title > span:last-child {
    flex-shrink: 0;
}

/* Mejorar responsividad de botones */
@media (max-width: 768px) {
    .category-title {
        font-size: 1.1rem;
    }
    
    .category-title .btn-sm {
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }
}

/* Asegurar que los oficios no se desborden */
.oficio-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ========================================
   TOAST DE NOTIFICACIÓN
   ======================================== */
.toggle-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #002b47 0%, #004975 100%);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    font-weight: 500;
    font-size: 0.95rem;
    z-index: 9999;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.toggle-toast.show {
    opacity: 1;
    transform: translateY(0);
}

/* ========================================
   TOGGLE SWITCH ACTIVO/INACTIVO
   ======================================== */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    cursor: pointer;
    user-select: none;
}

.toggle-checkbox {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #dc3545; /* Rojo cuando está inactivo */
    border-radius: 24px;
    transition: all 0.3s ease;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-slider:before {
    content: "✕";
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    color: #dc3545;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-checkbox:checked + .toggle-slider {
    background-color: #28a745; /* Verde cuando está activo */
}

.toggle-checkbox:checked + .toggle-slider:before {
    content: "✓";
    transform: translateX(20px);
    color: #28a745;
}

.toggle-switch:hover .toggle-slider {
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.3), 0 0 8px rgba(0, 0, 0, 0.2);
}

.toggle-slider:active {
    transform: scale(0.98);
}

/* ========================================
   OFICIOS INACTIVOS - ESTILO ESPECIAL
   ======================================== */
.oficio-inactivo {
    opacity: 0.85;
    transition: all 0.3s ease;
}

.oficio-inactivo:hover {
    opacity: 1;
}

/* Aplicar estilos visuales al wrapper interno cuando el oficio está inactivo */
.oficio-inactivo > div {
    background: linear-gradient(90deg, rgba(255, 235, 238, 0.4) 0%, rgba(255, 205, 210, 0.3) 100%) !important;
    border-left: 3px solid #dc3545 !important;
}

.oficio-inactivo:hover > div {
    background: linear-gradient(90deg, rgba(255, 235, 238, 0.6) 0%, rgba(255, 205, 210, 0.5) 100%) !important;
}

.nombre-tachado {
    text-decoration: line-through !important;
    text-decoration-color: #dc3545 !important;
    text-decoration-thickness: 2px !important;
    color: #6c757d !important;
    font-style: italic;
    opacity: 0.6;
}

/* Input editable inline para oficios */
.oficio-nombre-input {
    border: 1px solid transparent !important;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-weight: 500;
    cursor: text;
}

.oficio-nombre-input:hover {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

.oficio-nombre-input:focus {
    outline: none;
    background: white !important;
    border-color: #002b47 !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.1);
}

.oficio-nombre-input.saving {
    background: #ffffcc !important;
    border-color: #ffc107 !important;
}

.oficio-nombre-input.saved {
    background: #d4edda !important;
    border-color: #28a745 !important;
}

/* Input editable inline para categorías */
.categoria-nombre-input {
    border: 1px solid transparent !important;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-weight: 500;
    cursor: text;
}

.categoria-nombre-input:hover {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

.categoria-nombre-input:focus {
    outline: none;
    background: white !important;
    border-color: #002b47 !important;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.1);
}

.categoria-nombre-input.saving {
    background: #ffffcc !important;
    border-color: #ffc107 !important;
}

.categoria-nombre-input.saved {
    background: #d4edda !important;
    border-color: #28a745 !important;
}

/* Badge de inactivo */
.oficio-inactivo .badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* ========================================
   FLAMITAS CON IMAGEN CANDELA1.PNG - CON AJAX
   ======================================== */
.flamita-popular,
.flamita-no-popular {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Transición suave y elástica */
    cursor: pointer;
    user-select: none;
}

.flamita-popular {
    animation: flicker 2s infinite;
}

.flamita-popular:hover {
    transform: scale(1.2) rotate(5deg);
    filter: brightness(1.2) drop-shadow(0 0 8px rgba(255, 193, 7, 0.8));
}

.flamita-popular:active {
    transform: scale(0.9);
}

.flamita-no-popular {
    transition: all 0.3s ease;
}

.flamita-no-popular:hover {
    opacity: 0.6 !important;
    filter: grayscale(50%) !important;
    transform: scale(1.1);
}

/* Animación de parpadeo sutil para flamitas populares */
@keyframes flicker {
    0%, 100% {
        opacity: 1;
        filter: brightness(1);
    }
    25% {
        opacity: 0.95;
        filter: brightness(1.1);
    }
    50% {
        opacity: 1;
        filter: brightness(0.95);
    }
    75% {
        opacity: 0.98;
        filter: brightness(1.05);
    }
}

/* ========================================
   BLOQUE DE BÚSQUEDA MEJORADO
   ======================================== */
.search-container {
    display: flex;
    gap: 1rem;
    align-items: stretch;
    flex-wrap: wrap;
}

.search-input-wrapper {
    position: relative;
    flex: 1;
    min-width: 450px !important; /* Más ancho para que se vea el placeholder completo */
    max-width: 450px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-size: 1rem;
    pointer-events: none;
    z-index: 1;
}

.search-input {
    width: 100% !important;
    padding: 0.75rem 2.75rem !important; /* Más padding vertical para más altura */
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
    height: 48px; /* Altura fija */
}

.search-input:focus {
    background: white;
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
    transform: translateY(-1px);
}

.btn-clear-search {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #6c757d;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.2s ease;
    z-index: 2;
}

.btn-clear-search:hover {
    background: #dc3545;
    color: white;
}

.filter-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 200px;
    margin-left: auto; /* Flota a la derecha */
}

.filter-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #002b47;
    white-space: nowrap;
    margin: 0;
}

.filter-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    font-weight: 500;
    height: 48px; /* Misma altura que el input de búsqueda */
    padding: 0.75rem 2.5rem 0.75rem 0.75rem; /* Padding consistente */
}

.filter-select:focus {
    background: white;
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
}

.search-results-info {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-left: 4px solid #002b47;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #002b47;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive para búsqueda */
@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
    }
    
    .search-input-wrapper,
    .filter-wrapper {
        width: 100%;
        min-width: auto;
    }
    
    .filter-wrapper {
        flex-direction: column;
        align-items: stretch;
        gap: 0.25rem;
    }
    
    .filter-label {
        font-size: 0.85rem;
    }
}

/* Formularios dentro de bloques */
.admin-block-content .form-control:focus,
.admin-block-content .form-select:focus {
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
}

.admin-block-content .btn {
    white-space: nowrap;
}

#searchInput {
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

#searchInput:focus {
    border-color: #002b47;
    transform: scale(1.01);
}

#filterPopular {
    border: 2px solid #e9ecef;
    cursor: pointer;
}

@media (max-width: 768px) {
    .admin-block-content .d-flex {
        flex-direction: column;
    }
    
    .admin-block-content .flex-grow-1,
    .admin-block-content .form-select,
    .admin-block-content .btn {
        width: 100% !important;
    }
    
    .admin-block-title {
        font-size: 1.1rem;
        padding: 0.875rem 1.25rem;
    }
}

/* Asegurar que elementos ocultos no ocupen espacio */
.oficio-item[style*="display: none"],
.category-card[style*="display: none"] {
    display: none !important;
}

/* Mensaje de no resultados */
#noResultsMessage {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
