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
                                <select class="form-select" id="catIcono" name="icono" required>
                                    <option value="">Seleccionar icono...</option>
                                    
                                    <optgroup label="🧹 LIMPIEZA Y ASEO">
                                    <option value="fa-solid fa-broom">🧹 Escoba - Limpieza</option>
                                    <option value="fa-solid fa-spray-can">🎨 Spray - Limpieza profunda</option>
                                    <option value="fa-solid fa-pump-soap">🧴 Jabón - Productos limpieza</option>
                                    <option value="fa-solid fa-bath">� Bañera - Limpieza baños</option>
                                    <option value="fa-solid fa-toilet">🚽 Sanitario - Limpieza sanitaria</option>
                                    <option value="fa-solid fa-dumpster">🗑️ Contenedor - Recolección basuras</option>
                                    <option value="fa-solid fa-bucket">🪣 Balde - Limpieza</option>
                                    <option value="fa-solid fa-sponge">🧽 Esponja - Limpieza</option>
                                    <option value="fa-solid fa-hand-sparkles">✨ Desinfección - Higiene</option>
                                    <option value="fa-solid fa-wind">💨 Ventilación - Aire limpio</option>
                                    </optgroup>
                                    
                                    <optgroup label="🏗️ CONSTRUCCIÓN Y ALBAÑILERÍA">
                                    <option value="fa-solid fa-hammer">🔨 Martillo - Construcción</option>
                                    <option value="fa-solid fa-hard-hat">⛑️ Casco - Obra</option>
                                    <option value="fa-solid fa-building">🏢 Edificio - Construcción</option>
                                    <option value="fa-solid fa-trowel">🧱 Llana - Albañilería</option>
                                    <option value="fa-solid fa-ruler-combined">📐 Escuadra - Medición</option>
                                    <option value="fa-solid fa-ruler">📏 Regla - Medición</option>
                                    <option value="fa-solid fa-level">📏 Nivel - Nivelación</option>
                                    <option value="fa-solid fa-person-digging">⛏️ Excavación - Movimiento tierras</option>
                                    <option value="fa-solid fa-trowel-bricks">🧱 Mampostería - Obra</option>
                                    <option value="fa-solid fa-warehouse">🏭 Bodega - Almacén</option>
                                    <option value="fa-solid fa-industry">🏭 Industrial - Planta</option>
                                    <option value="fa-solid fa-city">🏙️ Urbano - Desarrollo</option>
                                    </optgroup>
                                    
                                    <optgroup label="🔧 REPARACIONES Y MANTENIMIENTO">
                                    <option value="fa-solid fa-wrench">🔧 Llave - Mecánica</option>
                                    <option value="fa-solid fa-screwdriver">🪛 Destornillador - Reparaciones</option>
                                    <option value="fa-solid fa-toolbox">🧰 Caja herramientas - Multiservicios</option>
                                    <option value="fa-solid fa-tools">🛠️ Herramientas - Mantenimiento</option>
                                    <option value="fa-solid fa-gear">⚙️ Engranaje - Mecánica</option>
                                    <option value="fa-solid fa-gears">⚙️ Engranajes - Mantenimiento</option>
                                    <option value="fa-solid fa-screwdriver-wrench">🔧 Herramientas - Reparación</option>
                                    <option value="fa-solid fa-oil-can">🛢️ Aceite - Lubricación</option>
                                    <option value="fa-solid fa-hammer">🔨 Martillo - Reparación</option>
                                    <option value="fa-solid fa-wrench-simple">� Ajustes - Mantenimiento</option>
                                    <option value="fa-solid fa-kit-medical">🧰 Kit reparación - Emergencia</option>
                                    <option value="fa-solid fa-file-contract">📋 Mantenimiento - Contrato</option>
                                    </optgroup>
                                    
                                    <optgroup label="🎨 PINTURA Y DECORACIÓN">
                                    <option value="fa-solid fa-paintbrush">🖌️ Pincel - Pintura</option>
                                    <option value="fa-solid fa-paint-roller">🎨 Rodillo - Pintura paredes</option>
                                    <option value="fa-solid fa-palette">🎨 Paleta - Decoración</option>
                                    <option value="fa-solid fa-fill-drip">💧 Bote pintura - Pintura</option>
                                    <option value="fa-solid fa-spray-can">🎨 Aerosol - Pintura spray</option>
                                    <option value="fa-solid fa-brush">🖌️ Brocha - Pintura</option>
                                    <option value="fa-solid fa-tape">📏 Cinta - Pintura</option>
                                    <option value="fa-solid fa-bezier-curve">〰️ Diseño - Decoración</option>
                                    </optgroup>
                                    
                                    <optgroup label="⚡ ELECTRICIDAD">
                                    <option value="fa-solid fa-plug">🔌 Enchufe - Electricidad</option>
                                    <option value="fa-solid fa-bolt">⚡ Rayo - Electricidad</option>
                                    <option value="fa-solid fa-lightbulb">💡 Bombilla - Iluminación</option>
                                    <option value="fa-solid fa-battery-full">🔋 Batería - Energía</option>
                                    <option value="fa-solid fa-plug-circle-bolt">⚡ Instalación eléctrica</option>
                                    <option value="fa-solid fa-solar-panel">☀️ Panel solar - Energía</option>
                                    <option value="fa-solid fa-lightbulb-on">� Iluminación LED</option>
                                    <option value="fa-solid fa-bolt-lightning">⚡ Alta tensión - Electricidad</option>
                                    <option value="fa-solid fa-tower-cell">📡 Torre - Telecomunicaciones</option>
                                    <option value="fa-solid fa-cable-car">🚡 Cableado - Instalaciones</option>
                                    </optgroup>
                                    
                                    <optgroup label="🚰 PLOMERÍA">
                                    <option value="fa-solid fa-faucet">🚰 Grifo - Plomería</option>
                                    <option value="fa-solid fa-shower">🚿 Ducha - Instalación</option>
                                    <option value="fa-solid fa-toilet">🚽 Sanitario - Plomería</option>
                                    <option value="fa-solid fa-sink">🚰 Lavamanos - Instalación</option>
                                    <option value="fa-solid fa-droplet">💧 Gota - Agua</option>
                                    <option value="fa-solid fa-pipe">🚰 Tubería - Instalación</option>
                                    <option value="fa-solid fa-faucet-drip">💧 Fugas - Reparación</option>
                                    <option value="fa-solid fa-water">🌊 Agua - Fontanería</option>
                                    <option value="fa-solid fa-toilet-paper">🧻 Sanitarios - Baño</option>
                                    <option value="fa-solid fa-pump">💨 Bomba - Agua</option>
                                    </optgroup>
                                    
                                    <optgroup label="🪚 CARPINTERÍA">
                                    <option value="fa-solid fa-saw">🪚 Sierra - Carpintería</option>
                                    <option value="fa-solid fa-ruler">📏 Regla - Carpintería</option>
                                    <option value="fa-solid fa-pencil">✏️ Lápiz - Carpintería</option>
                                    <option value="fa-solid fa-tree">🌳 Madera - Carpintería</option>
                                    <option value="fa-solid fa-hammer">🔨 Martillo - Carpintería</option>
                                    <option value="fa-solid fa-screwdriver">🪛 Destornillador - Ensamble</option>
                                    <option value="fa-solid fa-table">🪑 Mesa - Muebles</option>
                                    <option value="fa-solid fa-door-open">🚪 Puerta - Carpintería</option>
                                    <option value="fa-solid fa-stairs">🪜 Escaleras - Carpintería</option>
                                    <option value="fa-solid fa-cubes">📦 Muebles - Ensamble</option>
                                    </optgroup>
                                    
                                    <optgroup label="🛋️ MUEBLES Y HOGAR">
                                    <option value="fa-solid fa-couch">🛋️ Sofá - Muebles</option>
                                    <option value="fa-solid fa-chair">🪑 Silla - Muebles</option>
                                    <option value="fa-solid fa-bed">🛏️ Cama - Muebles</option>
                                    <option value="fa-solid fa-door-open">🚪 Puerta - Instalación</option>
                                    <option value="fa-solid fa-window-maximize">🪟 Ventana - Vidrios</option>
                                    <option value="fa-solid fa-house">🏠 Casa - Hogar</option>
                                    <option value="fa-solid fa-home">🏠 Hogar - Residencial</option>
                                    <option value="fa-solid fa-table">🪑 Mesa - Comedor</option>
                                    <option value="fa-solid fa-kitchen-set">🍳 Cocina - Muebles</option>
                                    <option value="fa-solid fa-tv">📺 TV - Entretenimiento</option>
                                    <option value="fa-solid fa-lamp">💡 Lámpara - Iluminación</option>
                                    <option value="fa-solid fa-loveseat">🛋️ Love seat - Muebles</option>
                                    </optgroup>
                                    
                                    <optgroup label="🚚 TRANSPORTE Y MUDANZAS">
                                    <option value="fa-solid fa-truck">🚚 Camión - Mudanzas</option>
                                    <option value="fa-solid fa-van-shuttle">🚐 Van - Transporte</option>
                                    <option value="fa-solid fa-car">🚗 Auto - Transporte</option>
                                    <option value="fa-solid fa-motorcycle">🏍️ Moto - Mensajería</option>
                                    <option value="fa-solid fa-bicycle">🚲 Bicicleta - Domicilios</option>
                                    <option value="fa-solid fa-box">📦 Caja - Empaque</option>
                                    <option value="fa-solid fa-boxes-stacked">📦 Cajas - Mudanzas</option>
                                    <option value="fa-solid fa-dolly">� Carretilla - Carga</option>
                                    <option value="fa-solid fa-truck-moving">🚚 Mudanzas - Transporte</option>
                                    <option value="fa-solid fa-truck-fast">🚚 Envío rápido - Express</option>
                                    <option value="fa-solid fa-pallet">📦 Pallet - Carga</option>
                                    <option value="fa-solid fa-shipping-fast">📦 Envío - Logística</option>
                                    </optgroup>
                                    
                                    <optgroup label="🌳 JARDINERÍA Y PAISAJISMO">
                                    <option value="fa-solid fa-tree">🌳 Árbol - Jardinería</option>
                                    <option value="fa-solid fa-seedling">🌱 Planta - Siembra</option>
                                    <option value="fa-solid fa-leaf">🍃 Hoja - Jardinería</option>
                                    <option value="fa-solid fa-scissors">✂️ Tijeras - Poda</option>
                                    <option value="fa-solid fa-flower">🌸 Flor - Jardinería</option>
                                    <option value="fa-solid fa-clover">🍀 Trébol - Jardín</option>
                                    <option value="fa-solid fa-sun-plant-wilt">🌱 Riego - Plantas</option>
                                    <option value="fa-solid fa-trowel">🧱 Pala - Jardinería</option>
                                    <option value="fa-solid fa-mountain">⛰️ Paisajismo - Terreno</option>
                                    <option value="fa-solid fa-grass">🌿 Césped - Jardín</option>
                                    </optgroup>
                                    
                                    <optgroup label="🍳 GASTRONOMÍA Y COCINA">
                                    <option value="fa-solid fa-kitchen-set">🍳 Cocina - Gastronomía</option>
                                    <option value="fa-solid fa-utensils">🍴 Cubiertos - Restaurante</option>
                                    <option value="fa-solid fa-pizza-slice">🍕 Pizza - Comida</option>
                                    <option value="fa-solid fa-burger">🍔 Hamburguesa - Fast food</option>
                                    <option value="fa-solid fa-cake-candles">🎂 Pastel - Repostería</option>
                                    <option value="fa-solid fa-mug-hot">☕ Café - Bebidas</option>
                                    <option value="fa-solid fa-champagne-glasses">🥂 Copas - Eventos</option>
                                    <option value="fa-solid fa-bowl-food">🍲 Bowl - Comida</option>
                                    <option value="fa-solid fa-cookie">🍪 Galleta - Panadería</option>
                                    <option value="fa-solid fa-cheese">🧀 Queso - Gastronomía</option>
                                    <option value="fa-solid fa-bacon">🥓 Bacon - Cocina</option>
                                    <option value="fa-solid fa-ice-cream">🍦 Helado - Postres</option>
                                    </optgroup>
                                    
                                    <optgroup label="💻 TECNOLOGÍA">
                                    <option value="fa-solid fa-computer">💻 Computadora - Informática</option>
                                    <option value="fa-solid fa-laptop">💻 Laptop - Reparación</option>
                                    <option value="fa-solid fa-mobile">📱 Móvil - Tecnología</option>
                                    <option value="fa-solid fa-wifi">📶 WiFi - Internet</option>
                                    <option value="fa-solid fa-network-wired">🌐 Red - Redes</option>
                                    <option value="fa-solid fa-camera">📷 Cámara - Fotografía</option>
                                    <option value="fa-solid fa-video">📹 Video - Audiovisual</option>
                                    <option value="fa-solid fa-server">🖥️ Servidor - IT</option>
                                    <option value="fa-solid fa-microchip">💾 Chip - Hardware</option>
                                    <option value="fa-solid fa-keyboard">⌨️ Teclado - Informática</option>
                                    <option value="fa-solid fa-mouse">🖱️ Mouse - Periféricos</option>
                                    <option value="fa-solid fa-headset">🎧 Audífonos - Audio</option>
                                    </optgroup>
                                    
                                    <optgroup label="💅 BELLEZA Y CUIDADO PERSONAL">
                                    <option value="fa-solid fa-scissors">✂️ Tijeras - Peluquería</option>
                                    <option value="fa-solid fa-cut">✂️ Corte - Estilista</option>
                                    <option value="fa-solid fa-spray-can-sparkles">💅 Spray - Belleza</option>
                                    <option value="fa-solid fa-hand-sparkles">✨ Manicure - Uñas</option>
                                    <option value="fa-solid fa-face-smile">😊 Facial - Spa</option>
                                    <option value="fa-solid fa-spa">� Spa - Relajación</option>
                                    <option value="fa-solid fa-wand-magic-sparkles">✨ Maquillaje - Belleza</option>
                                    <option value="fa-solid fa-gem">💎 Premium - Lujo</option>
                                    </optgroup>
                                    
                                    <optgroup label="👕 ROPA Y LAVANDERÍA">
                                    <option value="fa-solid fa-shirt">👕 Camisa - Ropa</option>
                                    <option value="fa-solid fa-jug-detergent">🧴 Detergente - Lavandería</option>
                                    <option value="fa-solid fa-sock">🧦 Calcetín - Ropa</option>
                                    <option value="fa-solid fa-tshirt">👕 Camiseta - Ropa</option>
                                    <option value="fa-solid fa-mitten">🧤 Guante - Ropa</option>
                                    <option value="fa-solid fa-vest">🦺 Chaleco - Ropa</option>
                                    <option value="fa-solid fa-iron">🔥 Plancha - Lavandería</option>
                                    <option value="fa-solid fa-tape">📏 Costura - Arreglos</option>
                                    </optgroup>
                                    
                                    <optgroup label="🐾 MASCOTAS">
                                    <option value="fa-solid fa-paw">🐾 Huella - Mascotas</option>
                                    <option value="fa-solid fa-dog">🐕 Perro - Veterinaria</option>
                                    <option value="fa-solid fa-cat">🐈 Gato - Veterinaria</option>
                                    <option value="fa-solid fa-fish">🐟 Pez - Acuarios</option>
                                    <option value="fa-solid fa-bone">🦴 Hueso - Veterinaria</option>
                                    <option value="fa-solid fa-horse">🐴 Caballo - Veterinaria</option>
                                    <option value="fa-solid fa-dove">🕊️ Ave - Veterinaria</option>
                                    <option value="fa-solid fa-shield-dog">🐕 Protección - Mascotas</option>
                                    <option value="fa-solid fa-syringe">� Veterinaria - Salud</option>
                                    <option value="fa-solid fa-bowl-rice">🍚 Alimento - Mascotas</option>
                                    </optgroup>
                                    
                                    <optgroup label="❤️ SALUD Y CUIDADO">
                                    <option value="fa-solid fa-heart-pulse">❤️ Pulso - Salud</option>
                                    <option value="fa-solid fa-suitcase-medical">💼 Médico - Emergencia</option>
                                    <option value="fa-solid fa-stethoscope">🩺 Estetoscopio - Consulta</option>
                                    <option value="fa-solid fa-user-nurse">👩‍⚕️ Enfermera - Cuidado</option>
                                    <option value="fa-solid fa-wheelchair">♿ Silla ruedas - Movilidad</option>
                                    <option value="fa-solid fa-hand-holding-heart">💝 Cuidado - Asistencia</option>
                                    <option value="fa-solid fa-hospital">🏥 Hospital - Salud</option>
                                    <option value="fa-solid fa-pills">💊 Medicinas - Farmacia</option>
                                    <option value="fa-solid fa-thermometer">�️ Termómetro - Salud</option>
                                    <option value="fa-solid fa-briefcase-medical">💼 Paramédico - Emergencia</option>
                                    </optgroup>
                                    
                                    <optgroup label="🎓 EDUCACIÓN">
                                    <option value="fa-solid fa-graduation-cap">🎓 Graduación - Educación</option>
                                    <option value="fa-solid fa-book">📖 Libro - Enseñanza</option>
                                    <option value="fa-solid fa-chalkboard-user">👨‍� Profesor - Clases</option>
                                    <option value="fa-solid fa-pen">🖊️ Pluma - Escritura</option>
                                    <option value="fa-solid fa-school">🏫 Escuela - Educación</option>
                                    <option value="fa-solid fa-user-graduate">🎓 Estudiante - Educación</option>
                                    <option value="fa-solid fa-book-open">📖 Lectura - Educación</option>
                                    <option value="fa-solid fa-apple-whole">🍎 Manzana - Educación</option>
                                    </optgroup>
                                    
                                    <optgroup label="🛡️ SEGURIDAD">
                                    <option value="fa-solid fa-shield">�️ Escudo - Seguridad</option>
                                    <option value="fa-solid fa-lock">🔒 Candado - Seguridad</option>
                                    <option value="fa-solid fa-key">🔑 Llave - Cerrajería</option>
                                    <option value="fa-solid fa-video">📹 Cámara - Vigilancia</option>
                                    <option value="fa-solid fa-shield-halved">🛡️ Protección - Seguridad</option>
                                    <option value="fa-solid fa-user-shield">👮 Guardia - Vigilancia</option>
                                    <option value="fa-solid fa-bell">🔔 Alarma - Seguridad</option>
                                    <option value="fa-solid fa-fire-extinguisher">🧯 Extintor - Seguridad</option>
                                    </optgroup>
                                    
                                    <optgroup label="🎵 EVENTOS Y ENTRETENIMIENTO">
                                    <option value="fa-solid fa-music">🎵 Música - Eventos</option>
                                    <option value="fa-solid fa-microphone">🎤 Micrófono - Audio</option>
                                    <option value="fa-solid fa-gifts">🎁 Regalos - Eventos</option>
                                    <option value="fa-solid fa-cake-candles">🎂 Pastel - Celebración</option>
                                    <option value="fa-solid fa-champagne-glasses">🥂 Brindis - Fiesta</option>
                                    <option value="fa-solid fa-guitar">🎸 Guitarra - Música</option>
                                    <option value="fa-solid fa-drum">🥁 Batería - Música</option>
                                    <option value="fa-solid fa-theater-masks">� Teatro - Arte</option>
                                    <option value="fa-solid fa-camera-retro">📷 Fotografía - Eventos</option>
                                    <option value="fa-solid fa-wand-magic-sparkles">✨ Decoración - Eventos</option>
                                    </optgroup>
                                    
                                    <optgroup label="💼 OFICINA Y NEGOCIOS">
                                    <option value="fa-solid fa-briefcase">💼 Maletín - Negocios</option>
                                    <option value="fa-solid fa-file-invoice">� Factura - Contabilidad</option>
                                    <option value="fa-solid fa-calculator">🧮 Calculadora - Finanzas</option>
                                    <option value="fa-solid fa-print">🖨️ Impresora - Oficina</option>
                                    <option value="fa-solid fa-warehouse">🏭 Almacén - Logística</option>
                                    <option value="fa-solid fa-chart-line">📈 Gráfica - Análisis</option>
                                    <option value="fa-solid fa-money-bill">💵 Dinero - Finanzas</option>
                                    <option value="fa-solid fa-clipboard">📋 Clipboard - Administración</option>
                                    <option value="fa-solid fa-folder">📁 Carpeta - Archivo</option>
                                    <option value="fa-solid fa-phone">📞 Teléfono - Atención</option>
                                    </optgroup>
                                    
                                    <optgroup label="⭐ VARIOS">
                                    <option value="fa-solid fa-fire">🔥 Fuego - Popular</option>
                                    <option value="fa-solid fa-star">⭐ Estrella - Destacado</option>
                                    <option value="fa-solid fa-circle-check">✅ Check - Verificado</option>
                                    <option value="fa-solid fa-users">👥 Usuarios - Comunidad</option>
                                    <option value="fa-solid fa-handshake">🤝 Acuerdo - Servicios</option>
                                    <option value="fa-solid fa-medal">🏅 Medalla - Excelencia</option>
                                    <option value="fa-solid fa-trophy">🏆 Trofeo - Premium</option>
                                    <option value="fa-solid fa-crown">👑 Corona - VIP</option>
                                    </optgroup>
                                </select>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Selecciona un icono que represente la categoría
                                </small>
                            </div>
                            
                            <div class="d-flex gap-2 justify-content-end">
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
                            
                            <div class="d-flex gap-2 justify-content-end">
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
    overflow: hidden;
}

/* Wrapper interno del oficio */
.oficio-item > div {
    max-width: 100%;
    overflow: hidden;
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
    margin-bottom: 0.5rem;
}

.form-section .form-control,
.form-section .form-select {
    border-radius: 6px;
    border: 1px solid #dee2e6;
    transition: all 0.2s ease;
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
        padding-left: 15px !important;
        padding-right: 15px !important;
        max-width: 100% !important;
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
        margin-bottom: 1rem !important;
    }
    
    .form-section-title {
        font-size: 1rem !important;
    }
    
    /* FIX: Oficios en móvil */
    .oficio-item {
        overflow-x: hidden;
    }
    
    .oficio-item > div {
        flex-wrap: nowrap !important;
        padding: 0.75rem 0.5rem !important;
        max-width: 100% !important;
        overflow-x: hidden;
    }
    
    .oficio-nombre-input {
        max-width: 180px !important;
        min-width: 120px !important;
        font-size: 0.9rem !important;
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
