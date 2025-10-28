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

<div style="margin-top: 40px;">
<!-- Hero Section RESTAURADO -->
<div class="home-hero">
    <h1 class="page-title text-azul" style="margin-bottom: 10px;">
        <i class="fas fa-layer-group"></i> 
        Gestión de Categorías y Oficios
    </h1>
    <p class="page-subtitle" style="margin-bottom: 1rem; line-height: 1.5;">
        Administra los oficios y marca cuáles están en alta demanda
    </p>
    <a id="btnBackToDashboard" class="btn btn-outline-light btn-sm" href="<?= app_url('views/admin/dashboard.php') ?>">
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
                        <!-- 1. Select de icono estilizado -->
                        <span class="category-icon-wrapper" style="position: relative; display: inline-flex; align-items: center; cursor: pointer;" 
                              data-categoria-id="<?= $categoria['id'] ?>"
                              data-current-icon="<?= htmlspecialchars($categoria['icono'] ?: 'fas fa-briefcase') ?>">
                            <i class="category-icon-display <?= htmlspecialchars($categoria['icono'] ?: 'fas fa-briefcase') ?>"></i>
                            <i class="fas fa-chevron-down" style="font-size: 0.6rem; margin-left: 4px; color: #6c757d;"></i>
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
                                               style="border: none; background: transparent; font-size: inherit; padding: 2px 4px;">
                                        
                                        <!-- Controles a la derecha -->
                                        <div style="display: flex; gap: 0.5rem; align-items: center; margin-left: auto; flex-shrink: 0;">
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

<!-- BLOQUE: NUEVA CATEGORÍA U OFICIO -->
<section class="container mb-4">
    <div class="admin-block">
        <h2 class="admin-block-title">
            <i class="fas fa-plus-circle"></i> Nueva Categoría u Oficio
        </h2>
        <div class="admin-block-content">
            <div class="row g-4">
                <!-- FORMULARIO: CATEGORÍA -->
                <div class="col-md-6">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-folder-plus"></i> Categoría
                        </h3>
                        <form id="formCategoria">
                            <input type="hidden" id="catId" name="id" value="">
                            <input type="hidden" name="activo" value="1">
                            
                            <div class="mb-3">
                                <label for="catTitulo" class="form-label fw-semibold">Nombre</label>
                                <input type="text" class="form-control" id="catTitulo" name="titulo" 
                                       placeholder="Ej: Aseo y Limpieza" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="catIcono" class="form-label fw-semibold">Icono</label>
                                <input type="hidden" id="catIcono" name="icono" required>
                                <div id="catIconoDisplay" class="form-control" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;" onclick="openFormIconPicker()">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i id="catIconoIcon" class="fas fa-question" style="font-size: 1.5rem;"></i>
                                        <span id="catIconoLabel">Seleccionar icono...</span>
                                    </div>
                                    <i class="fas fa-chevron-down" style="color: #6c757d;"></i>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Haz clic para seleccionar un icono
                                </small>
                            </div>
                            
                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                        onclick="document.getElementById('formCategoria').reset(); document.getElementById('catId').value = '';">
                                    <i class="fas fa-times" style="margin-right: 8px;"></i>Cancelar
                                </button>
                                <button type="button" id="btnSaveCategoria" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save" style="margin-right: 8px;"></i>Guardar Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- FORMULARIO: OFICIO -->
                <div class="col-md-6">
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <i class="fas fa-briefcase"></i> Oficio
                        </h3>
                        <form id="formOficio">
                            <input type="hidden" id="ofId" name="id" value="">
                            <input type="hidden" id="ofActivo" name="activo" value="1">
                            
                            <div class="mb-3">
                                <label for="ofTitulo" class="form-label fw-semibold">Nombre del oficio</label>
                                <input type="text" class="form-control" id="ofTitulo" name="titulo" 
                                       placeholder="Ej: Electricista básico" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ofCatId" class="form-label fw-semibold">Categoría</label>
                                <select class="form-select" id="ofCatId" name="categoria_id" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="d-flex gap-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="ofPopular" name="popular" value="1">
                                    <label class="form-check-label" for="ofPopular">
                                        <i class="fas fa-fire" style="color: #ffc107;"></i> Popular
                                    </label>
                                </div>
                                
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="ofActivo" name="activo" value="1" checked>
                                    <label class="form-check-label" for="ofActivo">
                                        <i class="fas fa-check-circle" style="color: #28a745;"></i> Activo
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                        onclick="document.getElementById('formOficio').reset(); document.getElementById('ofId').value = ''; document.getElementById('ofActivo').checked = true;">
                                    <i class="fas fa-times" style="margin-right: 8px;"></i>Cancelar
                                </button>
                                <button type="button" id="btnSaveOficio" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save" style="margin-right: 8px;"></i>Guardar Oficio
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

</section>

<!-- Modal para seleccionar icono de categoría -->
<div id="iconPickerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; max-width: 800px; max-height: 80vh; overflow-y: auto; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 2px solid #e9ecef; padding-bottom: 0.75rem;">
            <h4 style="margin: 0; color: #002b47;"><i class="fas fa-icons"></i> Seleccionar Icono</h4>
            <button onclick="closeIconPicker()" style="border: none; background: none; font-size: 1.5rem; cursor: pointer; color: #6c757d;">&times;</button>
        </div>
        <div id="iconPickerGrid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
            <!-- Los íconos se cargarán aquí dinámicamente -->
        </div>
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

// Función para aplicar Title Case
function toTitleCase(str) {
    if (!str) return '';
    return str.toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// ======== SELECTOR DE ÍCONOS ========
let currentCategoriaIdForIcon = null;

const iconosDisponibles = [
    { categoria: 'LIMPIEZA Y ASEO', iconos: [
        { icon: 'fa-solid fa-broom', label: 'broom - Escoba' },
        { icon: 'fa-solid fa-spray-can', label: 'spray-can - Spray' },
        { icon: 'fa-solid fa-pump-soap', label: 'pump-soap - Jabon' },
        { icon: 'fa-solid fa-hand-sparkles', label: 'hand-sparkles - Desinfeccion' },
        { icon: 'fa-solid fa-wind', label: 'wind - Ventilacion' },
        { icon: 'fa-solid fa-toilet', label: 'toilet - Sanitario' },
        { icon: 'fa-solid fa-dumpster', label: 'dumpster - Contenedor' },
        { icon: 'fa-solid fa-sponge', label: 'sponge - Esponja' }
    ]},
    
    { categoria: 'CONSTRUCCION Y ALBANILERIA', iconos: [
        { icon: 'fa-solid fa-hammer', label: 'hammer - Martillo' },
        { icon: 'fa-solid fa-hard-hat', label: 'hard-hat - Casco' },
        { icon: 'fa-solid fa-building', label: 'building - Edificio' },
        { icon: 'fa-solid fa-trowel', label: 'trowel - Llana' },
        { icon: 'fa-solid fa-ruler-combined', label: 'ruler-combined - Escuadra' },
        { icon: 'fa-solid fa-ruler', label: 'ruler - Regla' },
        { icon: 'fa-solid fa-person-digging', label: 'person-digging - Excavacion' },
        { icon: 'fa-solid fa-trowel-bricks', label: 'trowel-bricks - Mamposteria' },
        { icon: 'fa-solid fa-warehouse', label: 'warehouse - Bodega' },
        { icon: 'fa-solid fa-industry', label: 'industry - Industrial' },
        { icon: 'fa-solid fa-city', label: 'city - Urbano' }
    ]},
    
    { categoria: 'REPARACIONES Y MANTENIMIENTO', iconos: [
        { icon: 'fa-solid fa-wrench', label: 'wrench - Llave' },
        { icon: 'fa-solid fa-screwdriver', label: 'screwdriver - Destornillador' },
        { icon: 'fa-solid fa-toolbox', label: 'toolbox - Caja Herramientas' },
        { icon: 'fa-solid fa-gears', label: 'gears - Engranajes' },
        { icon: 'fa-solid fa-screwdriver-wrench', label: 'screwdriver-wrench - Herramientas' },
        { icon: 'fa-solid fa-oil-can', label: 'oil-can - Aceite' }
    ]},
    
    { categoria: 'PINTURA Y DECORACION', iconos: [
        { icon: 'fa-solid fa-paintbrush', label: 'paintbrush - Pincel' },
        { icon: 'fa-solid fa-paint-roller', label: 'paint-roller - Rodillo' },
        { icon: 'fa-solid fa-palette', label: 'palette - Paleta' },
        { icon: 'fa-solid fa-fill-drip', label: 'fill-drip - Bote Pintura' },
        { icon: 'fa-solid fa-brush', label: 'brush - Brocha' }
    ]},
    
    { categoria: 'ELECTRICIDAD', iconos: [
        { icon: 'fa-solid fa-plug', label: 'plug - Enchufe' },
        { icon: 'fa-solid fa-bolt', label: 'bolt - Rayo' },
        { icon: 'fa-solid fa-lightbulb', label: 'lightbulb - Bombilla' },
        { icon: 'fa-solid fa-battery-full', label: 'battery-full - Bateria' },
        { icon: 'fa-solid fa-plug-circle-bolt', label: 'plug-circle-bolt - Instalacion' },
        { icon: 'fa-solid fa-solar-panel', label: 'solar-panel - Panel Solar' },
        { icon: 'fa-solid fa-bolt-lightning', label: 'bolt-lightning - Alta Tension' },
        { icon: 'fa-solid fa-tower-cell', label: 'tower-cell - Torre' }
    ]},
    
    { categoria: 'PLOMERIA', iconos: [
        { icon: 'fa-solid fa-faucet', label: 'faucet - Grifo' },
        { icon: 'fa-solid fa-shower', label: 'shower - Ducha' },
        { icon: 'fa-solid fa-sink', label: 'sink - Lavamanos' },
        { icon: 'fa-solid fa-droplet', label: 'droplet - Gota' },
        { icon: 'fa-solid fa-faucet-drip', label: 'faucet-drip - Fugas' },
        { icon: 'fa-solid fa-water', label: 'water - Agua' },
        { icon: 'fa-solid fa-toilet-paper', label: 'toilet-paper - Sanitarios' }
    ]},
    
    { categoria: 'CARPINTERIA', iconos: [
        { icon: 'fa-solid fa-hammer', label: 'hammer - Martillo' },
        { icon: 'fa-solid fa-screwdriver', label: 'screwdriver - Destornillador' },
        { icon: 'fa-solid fa-ruler', label: 'ruler - Regla' },
        { icon: 'fa-solid fa-table', label: 'table - Mesa' },
        { icon: 'fa-solid fa-door-open', label: 'door-open - Puerta' },
        { icon: 'fa-solid fa-stairs', label: 'stairs - Escaleras' }
    ]},
    
    { categoria: 'MUEBLES Y HOGAR', iconos: [
        { icon: 'fa-solid fa-couch', label: 'couch - Sofa' },
        { icon: 'fa-solid fa-chair', label: 'chair - Silla' },
        { icon: 'fa-solid fa-bed', label: 'bed - Cama' },
        { icon: 'fa-solid fa-house', label: 'house - Casa' },
        { icon: 'fa-solid fa-kitchen-set', label: 'kitchen-set - Cocina' },
        { icon: 'fa-solid fa-tv', label: 'tv - Televisor' }
    ]},
    
    { categoria: 'TRANSPORTE Y MUDANZAS', iconos: [
        { icon: 'fa-solid fa-truck', label: 'truck - Camion' },
        { icon: 'fa-solid fa-van-shuttle', label: 'van-shuttle - Van' },
        { icon: 'fa-solid fa-car', label: 'car - Auto' },
        { icon: 'fa-solid fa-motorcycle', label: 'motorcycle - Moto' },
        { icon: 'fa-solid fa-bicycle', label: 'bicycle - Bicicleta' },
        { icon: 'fa-solid fa-box', label: 'box - Caja' },
        { icon: 'fa-solid fa-boxes-stacked', label: 'boxes-stacked - Cajas' },
        { icon: 'fa-solid fa-truck-moving', label: 'truck-moving - Mudanzas' },
        { icon: 'fa-solid fa-truck-fast', label: 'truck-fast - Envio Rapido' }
    ]},
    
    { categoria: 'JARDINERIA Y PAISAJISMO', iconos: [
        { icon: 'fa-solid fa-tree', label: 'tree - Arbol' },
        { icon: 'fa-solid fa-seedling', label: 'seedling - Planta' },
        { icon: 'fa-solid fa-leaf', label: 'leaf - Hoja' },
        { icon: 'fa-solid fa-scissors', label: 'scissors - Tijeras' },
        { icon: 'fa-solid fa-mountain', label: 'mountain - Paisajismo' }
    ]},
    
    { categoria: 'GASTRONOMIA Y COCINA', iconos: [
        { icon: 'fa-solid fa-utensils', label: 'utensils - Cubiertos' },
        { icon: 'fa-solid fa-pizza-slice', label: 'pizza-slice - Pizza' },
        { icon: 'fa-solid fa-burger', label: 'burger - Hamburguesa' },
        { icon: 'fa-solid fa-mug-hot', label: 'mug-hot - Cafe' },
        { icon: 'fa-solid fa-bowl-food', label: 'bowl-food - Bowl' },
        { icon: 'fa-solid fa-cookie', label: 'cookie - Galleta' },
        { icon: 'fa-solid fa-cheese', label: 'cheese - Queso' },
        { icon: 'fa-solid fa-bacon', label: 'bacon - Bacon' },
        { icon: 'fa-solid fa-ice-cream', label: 'ice-cream - Helado' }
    ]},
    
    { categoria: 'TECNOLOGIA', iconos: [
        { icon: 'fa-solid fa-computer', label: 'computer - Computadora' },
        { icon: 'fa-solid fa-laptop', label: 'laptop - Laptop' },
        { icon: 'fa-solid fa-mobile', label: 'mobile - Movil' },
        { icon: 'fa-solid fa-wifi', label: 'wifi - WiFi' },
        { icon: 'fa-solid fa-network-wired', label: 'network-wired - Red' },
        { icon: 'fa-solid fa-camera', label: 'camera - Camara' },
        { icon: 'fa-solid fa-video', label: 'video - Video' },
        { icon: 'fa-solid fa-server', label: 'server - Servidor' },
        { icon: 'fa-solid fa-microchip', label: 'microchip - Chip' },
        { icon: 'fa-solid fa-keyboard', label: 'keyboard - Teclado' },
        { icon: 'fa-solid fa-headphones', label: 'headphones - Audifonos' }
    ]},
    
    { categoria: 'BELLEZA Y CUIDADO PERSONAL', iconos: [
        { icon: 'fa-solid fa-scissors', label: 'scissors - Corte' },
        { icon: 'fa-solid fa-spray-can-sparkles', label: 'spray-can-sparkles - Spray' },
        { icon: 'fa-solid fa-face-smile', label: 'face-smile - Facial' },
        { icon: 'fa-solid fa-wand-magic-sparkles', label: 'wand-magic-sparkles - Maquillaje' },
        { icon: 'fa-solid fa-gem', label: 'gem - Premium' }
    ]},
    
    { categoria: 'ROPA Y LAVANDERIA', iconos: [
        { icon: 'fa-solid fa-shirt', label: 'shirt - Camisa' },
        { icon: 'fa-solid fa-jug-detergent', label: 'jug-detergent - Detergente' },
        { icon: 'fa-solid fa-vest', label: 'vest - Chaleco' }
    ]},
    
    { categoria: 'MASCOTAS', iconos: [
        { icon: 'fa-solid fa-paw', label: 'paw - Huella' },
        { icon: 'fa-solid fa-dog', label: 'dog - Perro' },
        { icon: 'fa-solid fa-cat', label: 'cat - Gato' },
        { icon: 'fa-solid fa-fish', label: 'fish - Pez' },
        { icon: 'fa-solid fa-bone', label: 'bone - Hueso' },
        { icon: 'fa-solid fa-horse', label: 'horse - Caballo' },
        { icon: 'fa-solid fa-dove', label: 'dove - Ave' },
        { icon: 'fa-solid fa-shield-dog', label: 'shield-dog - Proteccion' }
    ]},
    
    { categoria: 'SALUD Y CUIDADO', iconos: [
        { icon: 'fa-solid fa-heart-pulse', label: 'heart-pulse - Pulso' },
        { icon: 'fa-solid fa-suitcase-medical', label: 'suitcase-medical - Medico' },
        { icon: 'fa-solid fa-stethoscope', label: 'stethoscope - Estetoscopio' },
        { icon: 'fa-solid fa-user-nurse', label: 'user-nurse - Enfermera' },
        { icon: 'fa-solid fa-wheelchair', label: 'wheelchair - Silla Ruedas' },
        { icon: 'fa-solid fa-hand-holding-heart', label: 'hand-holding-heart - Cuidado' },
        { icon: 'fa-solid fa-hospital', label: 'hospital - Hospital' },
        { icon: 'fa-solid fa-pills', label: 'pills - Medicinas' }
    ]},
    
    { categoria: 'EDUCACION', iconos: [
        { icon: 'fa-solid fa-graduation-cap', label: 'graduation-cap - Graduacion' },
        { icon: 'fa-solid fa-book', label: 'book - Libro' },
        { icon: 'fa-solid fa-chalkboard-user', label: 'chalkboard-user - Profesor' },
        { icon: 'fa-solid fa-pen', label: 'pen - Pluma' },
        { icon: 'fa-solid fa-school', label: 'school - Escuela' },
        { icon: 'fa-solid fa-user-graduate', label: 'user-graduate - Estudiante' },
        { icon: 'fa-solid fa-book-open', label: 'book-open - Lectura' },
        { icon: 'fa-solid fa-apple-whole', label: 'apple-whole - Manzana' }
    ]},
    
    { categoria: 'SEGURIDAD', iconos: [
        { icon: 'fa-solid fa-shield', label: 'shield - Escudo' },
        { icon: 'fa-solid fa-lock', label: 'lock - Candado' },
        { icon: 'fa-solid fa-key', label: 'key - Llave' },
        { icon: 'fa-solid fa-shield-halved', label: 'shield-halved - Proteccion' },
        { icon: 'fa-solid fa-user-shield', label: 'user-shield - Guardia' },
        { icon: 'fa-solid fa-bell', label: 'bell - Alarma' },
        { icon: 'fa-solid fa-fire-extinguisher', label: 'fire-extinguisher - Extintor' }
    ]},
    
    { categoria: 'EVENTOS Y ENTRETENIMIENTO', iconos: [
        { icon: 'fa-solid fa-music', label: 'music - Musica' },
        { icon: 'fa-solid fa-microphone', label: 'microphone - Microfono' },
        { icon: 'fa-solid fa-gifts', label: 'gifts - Regalos' },
        { icon: 'fa-solid fa-cake-candles', label: 'cake-candles - Pastel' },
        { icon: 'fa-solid fa-champagne-glasses', label: 'champagne-glasses - Brindis' },
        { icon: 'fa-solid fa-guitar', label: 'guitar - Guitarra' },
        { icon: 'fa-solid fa-drum', label: 'drum - Bateria' },
        { icon: 'fa-solid fa-camera-retro', label: 'camera-retro - Fotografia' }
    ]},
    
    { categoria: 'OFICINA Y NEGOCIOS', iconos: [
        { icon: 'fa-solid fa-briefcase', label: 'briefcase - Maletin' },
        { icon: 'fa-solid fa-calculator', label: 'calculator - Calculadora' },
        { icon: 'fa-solid fa-print', label: 'print - Impresora' },
        { icon: 'fa-solid fa-chart-line', label: 'chart-line - Grafica' },
        { icon: 'fa-solid fa-money-bill', label: 'money-bill - Dinero' },
        { icon: 'fa-solid fa-clipboard', label: 'clipboard - Clipboard' },
        { icon: 'fa-solid fa-folder', label: 'folder - Carpeta' },
        { icon: 'fa-solid fa-phone', label: 'phone - Telefono' }
    ]},
    
    { categoria: 'VARIOS', iconos: [
        { icon: 'fa-solid fa-fire', label: 'fire - Fuego' },
        { icon: 'fa-solid fa-star', label: 'star - Estrella' },
        { icon: 'fa-solid fa-circle-check', label: 'circle-check - Check' },
        { icon: 'fa-solid fa-users', label: 'users - Usuarios' },
        { icon: 'fa-solid fa-handshake', label: 'handshake - Acuerdo' },
        { icon: 'fa-solid fa-medal', label: 'medal - Medalla' },
        { icon: 'fa-solid fa-trophy', label: 'trophy - Trofeo' },
        { icon: 'fa-solid fa-crown', label: 'crown - Corona' },
        { icon: 'fa-solid fa-user-tie', label: 'user-tie - Profesional' }
    ]}
];

function openIconPicker(categoriaId, currentIcon) {
    currentCategoriaIdForIcon = categoriaId;
    const modal = document.getElementById('iconPickerModal');
    const grid = document.getElementById('iconPickerGrid');
    
    // Limpiar y llenar grid con categorías
    grid.innerHTML = '';
    
    iconosDisponibles.forEach(grupo => {
        // Crear encabezado de categoría
        const header = document.createElement('div');
        header.style.gridColumn = '1 / -1';
        header.style.marginTop = '1rem';
        header.style.marginBottom = '0.5rem';
        header.style.paddingBottom = '0.5rem';
        header.style.borderBottom = '2px solid #002b47';
        header.style.fontWeight = 'bold';
        header.style.color = '#002b47';
        header.style.fontSize = '0.9rem';
        header.textContent = grupo.categoria;
        grid.appendChild(header);
        
        // Agregar iconos de la categoría
        grupo.iconos.forEach(item => {
            const btn = document.createElement('button');
            btn.className = 'icon-picker-btn';
            btn.innerHTML = `<i class="${item.icon}"></i>`;
            btn.title = item.label;
            btn.onclick = () => selectIcon(item.icon);
            if (item.icon === currentIcon) {
                btn.style.background = '#002b47';
                btn.style.color = 'white';
            }
            grid.appendChild(btn);
        });
    });
    
    modal.style.display = 'flex';
}

function closeIconPicker() {
    document.getElementById('iconPickerModal').style.display = 'none';
    currentCategoriaIdForIcon = null;
}

async function selectIcon(iconClass) {
    if (!currentCategoriaIdForIcon) return;
    
    // Si es desde el formulario, solo actualizar UI (no AJAX)
    if (currentCategoriaIdForIcon === 'form') {
        // Buscar el label en la nueva estructura de categorías
        let label = iconClass;
        for (const grupo of iconosDisponibles) {
            const found = grupo.iconos.find(i => i.icon === iconClass);
            if (found) {
                label = found.label;
                break;
            }
        }
        selectFormIcon(iconClass, label);
        return;
    }
    
    try {
        const formData = new URLSearchParams();
        formData.append('id', currentCategoriaIdForIcon);
        formData.append('icono', iconClass);
        
        const r = await fetch(getControllerUrl('controllers/CategoriaController.php?action=updateIcono'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });
        
        const data = await r.json();
        
        if (data.success) {
            // Actualizar el ícono en la UI
            const wrapper = document.querySelector(`.category-icon-wrapper[data-categoria-id="${currentCategoriaIdForIcon}"]`);
            if (wrapper) {
                const iconDisplay = wrapper.querySelector('.category-icon-display');
                iconDisplay.className = `category-icon-display ${iconClass}`;
                wrapper.setAttribute('data-current-icon', iconClass);
            }
            closeIconPicker();
        } else {
            alert(data.message || 'Error al actualizar ícono');
        }
    } catch (e) {
        console.error('Error:', e);
        alert('Error al actualizar ícono');
    }
}

// Función para abrir selector desde el formulario de nueva categoría
function openFormIconPicker() {
    currentCategoriaIdForIcon = 'form';  // Marcador especial para formulario
    const modal = document.getElementById('iconPickerModal');
    const grid = document.getElementById('iconPickerGrid');
    const currentIcon = document.getElementById('catIcono').value;
    
    // Limpiar y llenar grid
    grid.innerHTML = '';
    iconosDisponibles.forEach(item => {
        const btn = document.createElement('button');
        btn.className = 'icon-picker-btn';
        btn.innerHTML = `<i class="${item.icon}"></i>`;
        btn.title = item.label;
        btn.onclick = () => selectFormIcon(item.icon, item.label);
        if (item.icon === currentIcon) {
            btn.style.background = '#002b47';
            btn.style.color = 'white';
        }
        grid.appendChild(btn);
    });
    
    modal.style.display = 'flex';
}

// Seleccionar ícono para el formulario (no usa AJAX)
function selectFormIcon(iconClass, label) {
    document.getElementById('catIcono').value = iconClass;
    document.getElementById('catIconoIcon').className = iconClass;
    document.getElementById('catIconoLabel').textContent = label;
    closeIconPicker();
}

function wireIconPicker() {
    document.querySelectorAll('.category-icon-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', (e) => {
            e.stopPropagation();
            const categoriaId = wrapper.getAttribute('data-categoria-id');
            const currentIcon = wrapper.getAttribute('data-current-icon');
            openIconPicker(categoriaId, currentIcon);
        });
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('iconPickerModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'iconPickerModal') {
            closeIconPicker();
        }
    });
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
        wireIconPicker();
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
            let nuevoNombre = input.value.trim();
            const id = input.getAttribute('data-oficio-id');
            
            // Aplicar Title Case
            if (nuevoNombre) {
                nuevoNombre = toTitleCase(nuevoNombre);
                input.value = nuevoNombre;
            }
            
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
            let nuevoNombre = input.value.trim();
            const id = input.getAttribute('data-categoria-id');
            
            // Aplicar Title Case
            if (nuevoNombre) {
                nuevoNombre = toTitleCase(nuevoNombre);
                input.value = nuevoNombre;
            }
            
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

function deleteCategoria(id, nombre) {
    if (confirm(`¿Eliminar la categoría "${nombre}"? Se eliminarán también todos sus oficios.`)) {
        executeCategoriaDelete(id);
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
    
    // Aplicar Title Case al nombre
    const nombreInput = form.querySelector('input[name="nombre"]');
    if (nombreInput?.value) {
        const titleCased = toTitleCase(nombreInput.value);
        nombreInput.value = titleCased;
        formData.set('nombre', titleCased);
    }
    
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
    
    // Aplicar Title Case al nombre
    const nombreInput = form.querySelector('input[name="nombre"]');
    if (nombreInput?.value) {
        const titleCased = toTitleCase(nombreInput.value);
        nombreInput.value = titleCased;
        formData.set('nombre', titleCased);
    }
    
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
/* ======== SELECTOR DE ÍCONOS ======== */
.category-icon-wrapper:hover {
    opacity: 0.7;
    transform: scale(1.05);
    transition: all 0.2s ease;
}

.icon-picker-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.5rem;
}

.icon-picker-btn:hover {
    background: #002b47;
    color: white;
    border-color: #002b47;
    transform: scale(1.1);
}

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
    flex-wrap: nowrap;
    overflow: visible; /* Cambiado para permitir ver dropdown/focus */
    position: relative;
}

/* Wrapper interno del oficio */
.oficio-item > div {
    max-width: 100%;
    width: 100%;
    overflow: visible; /* Cambiado para permitir ver focus states */
    box-sizing: border-box;
}

/* Input de nombre del oficio */
.oficio-nombre-input {
    flex: 1 1 auto;
    min-width: 0 !important;
    max-width: none !important;
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
   SECCIONES DE FORMULARIOS
   ======================================== */
.form-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    height: 100%;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #002b47;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section-title i {
    font-size: 1.2rem;
}

.form-section .form-label {
    color: #495057;
    margin-bottom: 0.4rem;
}

.form-section .mb-3 {
    margin-bottom: 1rem !important;
}

.form-section .form-control,
.form-section .form-select {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
    margin-bottom: 0.15rem;
}

/* Estilos especiales para inputs de texto (nombre, título) */
.form-section input[type="text"].form-control {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    background: #f8f9fa;
    height: 48px;
    transition: all 0.3s ease;
}

.form-section input[type="text"].form-control:focus {
    background: white;
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
    transform: translateY(-1px);
}

/* Estilos especiales para selects */
.form-section select.form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    font-weight: 500;
    height: 48px;
    padding: 0.75rem 2.5rem 0.75rem 0.75rem;
    font-size: 1rem;
}

.form-section select.form-select:focus {
    background: white;
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
    transform: scale(1.01);
}

.form-section .form-control:focus,
.form-section .form-select:focus {
    border-color: #002b47;
    box-shadow: 0 0 0 0.2rem rgba(0, 43, 71, 0.15);
}

.form-section .btn {
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.form-section .btn-primary {
    background: linear-gradient(135deg, #002b47 0%, #004975 100%);
    border: none;
}

.form-section .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 43, 71, 0.3);
}

.form-section .btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.form-section .btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
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
    /* FIX: Prevenir overflow horizontal */
    body {
        overflow-x: hidden !important;
    }
    
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
    
    /* FIX: Contenedor principal en móvil */
    .container {
        padding-left: 10px !important;
        padding-right: 10px !important;
        max-width: 100vw !important;
        overflow-x: hidden !important;
    }
    
    /* FIX: Bloque admin */
    .admin-block {
        margin-left: 0 !important;
        margin-right: 0 !important;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .admin-block-content {
        padding: 1rem !important;
        overflow-x: hidden !important;
    }
    
    /* FIX: Tarjetas de categoría */
    .category-card {
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding: 1rem !important;
        overflow-x: hidden !important;
    }
    
    /* FIX: Título de categoría */
    .category-title {
        flex-wrap: wrap !important;
        gap: 0.5rem !important;
        font-size: 1rem !important;
    }
    
    .category-icon {
        font-size: 1.2rem !important;
    }
    
    .categoria-nombre-input {
        max-width: 100% !important;
        min-width: 150px !important;
        font-size: 0.95rem !important;
    }
    
    /* FIX: Contenedor de búsqueda */
    .search-container {
        flex-direction: column !important;
        gap: 0.75rem !important;
    }
    
    .search-input-wrapper {
        min-width: 100% !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    
    .filter-wrapper {
        width: 100% !important;
        flex-direction: column !important;
        gap: 0.5rem !important;
    }
    
    .filter-label {
        width: 100% !important;
    }
    
    .filter-select {
        width: 100% !important;
    }
    
    /* FIX: Formularios en móvil */
    .form-section {
        padding: 1rem !important;
        margin-bottom: 2rem !important;
    }
    
    .form-section-title {
        font-size: 1rem !important;
        margin-bottom: 1.25rem !important;
    }
    
    /* FIX: Inputs y selects en formularios */
    .form-control,
    .form-select {
        max-width: 100% !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    
    .mb-3 {
        margin-bottom: 2rem !important;
    }
    
    .form-label {
        margin-bottom: 0.75rem !important;
        display: block !important;
    }
    
    .form-control,
    .form-select {
        margin-bottom: 0.5rem !important;
    }
    
    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
    
    .col-md-6 {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
    
    /* FIX: Oficios en móvil */
    .oficio-item {
        overflow-x: hidden !important;
    }
    
    .oficio-item > div {
        flex-wrap: nowrap !important;
        padding: 0.5rem !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    
    .oficio-nombre-input {
        flex: 1 1 auto !important;
        min-width: 0 !important;
        max-width: none !important;
        font-size: 0.9rem !important;
    }
    
    /* Reducir tamaño de controles en mobile */
    .oficio-item img,
    .oficio-item .toggle-switch {
        transform: scale(0.85);
    }
    
    .oficio-item > div > div {
        gap: 0.25rem !important;
    }
    
    /* FIX: Botones en móvil */
    .category-title .btn-sm,
    .oficio-item .btn-sm {
        padding: 0.25rem 0.4rem !important;
        font-size: 0.75rem !important;
    }
    
    /* FIX: Stats horizontales */
    .stats-horizontal {
        gap: 0.5rem !important;
    }
    
    .stat-item {
        padding: 0.75rem !important;
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

</div><!-- Cierre del contenedor con margin-top -->

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
