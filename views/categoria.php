<?php 
/**
 * Vista de Categoría - Muestra todos los anuncios de oficios de una categoría
 * Recibe parámetro 'slug' o 'id' por GET
 */

// Cargar configuración y base de datos
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app_paths.php';

// Obtener el identificador de la categoría (puede ser slug o id)
$categoriaSlug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
$categoriaId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Validar que se recibió al menos un parámetro
if (empty($categoriaSlug) && empty($categoriaId)) {
    http_response_code(404);
    $pageTitle = "Categoría no encontrada";
    echo '<div style="text-align: center; padding: 4rem; background: #f8f9fa; margin: 20px; border-radius: 8px;">';
    echo '<h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Categoría no encontrada</h2>';
    echo '<p>No se especificó una categoría válida.</p>';
    echo '<a href="' . app_url('index.php') . '" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
    return;
}

try {
    $pdo = getPDO();
    
    // Buscar la categoría (por slug o id)
    if ($categoriaSlug) {
        // Crear slug desde el nombre para comparación
        $stmt = $pdo->prepare("
            SELECT id, nombre, icono, descripcion 
            FROM categorias 
            WHERE activo = 1 
            AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nombre, ' ', '-'), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o')) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$categoriaSlug]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, nombre, icono, descripcion 
            FROM categorias 
            WHERE activo = 1 AND id = ?
            LIMIT 1
        ");
        $stmt->execute([$categoriaId]);
    }
    
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no existe la categoría, mostrar error 404
    if (!$categoria) {
        http_response_code(404);
        $pageTitle = "Categoría no encontrada";
        echo '<div style="text-align: center; padding: 4rem; background: #f8f9fa; margin: 20px; border-radius: 8px;">';
        echo '<h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Categoría no encontrada o inactiva</h2>';
        echo '<p>La categoría que buscas no existe o ya no está disponible.</p>';
        echo '<a href="' . app_url('index.php') . '" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Volver al Inicio</a>';
        echo '</div>';
        return;
    }
    
    // Configurar título de página
    $pageTitle = htmlspecialchars($categoria['nombre']) . " | Camella";
    
    // Paginación
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = 12;
    $offset = ($page - 1) * $perPage;
    
    // Contar total de anuncios activos en esta categoría
    $stmtCount = $pdo->prepare("
        SELECT COUNT(DISTINCT a.id) as total
        FROM anuncios a
        INNER JOIN oficios o ON a.oficio_id = o.id
        WHERE o.categoria_id = ?
        AND a.status = 'activo'
        AND o.activo = 1
    ");
    $stmtCount->execute([$categoria['id']]);
    $totalAnuncios = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalAnuncios / $perPage);
    
    // Obtener anuncios de esta categoría con paginación
    $stmtAnuncios = $pdo->prepare("
        SELECT 
            a.id,
            a.titulo,
            a.descripcion,
            a.precio,
            a.created_at,
            o.titulo as oficio_nombre,
            u.phone as usuario_nombre,
            u.phone as usuario_telefono,
            (SELECT ai.ruta FROM anuncio_imagenes ai WHERE ai.anuncio_id = a.id ORDER BY ai.orden LIMIT 1) as imagen_principal,
            (SELECT COUNT(*) FROM anuncio_imagenes ai WHERE ai.anuncio_id = a.id) as total_imagenes
        FROM anuncios a
        INNER JOIN oficios o ON a.oficio_id = o.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE o.categoria_id = ?
        AND a.status = 'activo'
        AND o.activo = 1
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmtAnuncios->bindValue(1, $categoria['id'], PDO::PARAM_INT);
    $stmtAnuncios->bindValue(2, $perPage, PDO::PARAM_INT);
    $stmtAnuncios->bindValue(3, $offset, PDO::PARAM_INT);
    $stmtAnuncios->execute();
    $anuncios = $stmtAnuncios->fetchAll(PDO::FETCH_ASSOC);
    
    // Cargar todas las imágenes de cada anuncio
    foreach ($anuncios as &$anuncio) {
        $stmtImagenes = $pdo->prepare("
            SELECT ruta, orden 
            FROM anuncio_imagenes 
            WHERE anuncio_id = ? 
            ORDER BY orden ASC
        ");
        $stmtImagenes->execute([$anuncio['id']]);
        $anuncio['imagenes'] = $stmtImagenes->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($anuncio); // Romper la referencia
    
} catch (PDOException $e) {
    error_log("Error en categoria.php: " . $e->getMessage());
    http_response_code(500);
    $pageTitle = "Error del servidor";
    echo '<div style="text-align: center; padding: 4rem; background: #f8f9fa; margin: 20px; border-radius: 8px;">';
    echo '<h2 style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Error del servidor</h2>';
    echo '<p>Ocurrió un error al cargar la categoría. Por favor, intenta nuevamente más tarde.</p>';
    echo '<a href="' . app_url('index.php') . '" class="btn btn-primary mt-3"><i class="fas fa-home"></i> Volver al Inicio</a>';
    echo '</div>';
    return;
}
?>

<style>
.breadcrumb-categoria {
    background: transparent;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.breadcrumb-categoria span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.breadcrumb-categoria a {
    color: #007bff;
    text-decoration: none;
    white-space: nowrap;
}

.breadcrumb-categoria a:hover {
    text-decoration: underline;
}

.breadcrumb-categoria strong {
    color: #2c3e50;
}

.categoria-header {
    background: linear-gradient(135deg, #3a8be8 0%, #2870d1 100%);
    color: white;
    padding: 1.5rem 0;
    margin-bottom: 1.5rem;
    border-radius: 0;
    box-shadow: 0 4px 15px rgba(58, 139, 232, 0.2);
    position: relative;
    overflow: hidden;
}

.categoria-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.03)"/></svg>');
    background-size: 80px 80px;
    opacity: 0.5;
}

.categoria-header .container {
    position: relative;
    z-index: 1;
}

.categoria-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
    background: rgba(255, 255, 255, 0.15);
    width: 60px;
    height: 60px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.categoria-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    letter-spacing: -0.5px;
}

.categoria-descripcion {
    font-size: 0.9rem;
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto 0.75rem;
    line-height: 1.4;
}

.categoria-stats {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.4rem 1rem;
    border-radius: 30px;
    backdrop-filter: blur(10px);
    font-weight: 600;
}

.anuncio-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: visible;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.anuncio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    border-color: #007bff;
}

.anuncio-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.anuncio-image-wrapper:active {
    cursor: grabbing;
}

.anuncio-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.anuncio-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
}

.anuncio-image-counter {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.95);
    color: #333;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-transform: uppercase;
    z-index: 10;
    pointer-events: none;
}

.anuncio-image-controls {
    position: absolute;
    bottom: 0.5rem;
    left: 0.5rem;
    display: flex;
    gap: 0.4rem;
    z-index: 10;
}

.anuncio-control-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.75rem;
}

.anuncio-control-btn:hover {
    background: rgba(0, 123, 255, 0.9);
    border-color: rgba(255, 255, 255, 0.6);
    transform: scale(1.1);
}

.anuncio-control-btn:active {
    transform: scale(0.95);
}

.anuncio-control-btn.playing {
    background: rgba(0, 123, 255, 0.9);
}

.anuncio-control-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.anuncio-control-btn:disabled:hover {
    background: rgba(0, 0, 0, 0.7);
    transform: scale(1);
}

.anuncio-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.anuncio-oficio {
    font-size: 0.85rem;
    color: #007bff;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.anuncio-titulo {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.75rem;
    line-height: 1.4;
}

.anuncio-descripcion {
    font-size: 0.95rem;
    color: #6c757d;
    margin-bottom: 1rem;
    line-height: 1.6;
    flex: 1;
}

.anuncio-footer {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    padding-top: 1.25rem;
    border-top: 1px solid #e0e0e0;
    margin-top: auto;
}

.anuncio-info-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.anuncio-botones {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
    width: 100%;
}

.anuncio-precio {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    line-height: 1.2;
    margin: 0;
    padding: 0;
}

.anuncio-fecha {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0;
    padding: 0;
    text-align: right;
    white-space: nowrap;
}

.anuncio-fecha i {
    font-size: 0.75rem;
    opacity: 0.8;
}

.no-anuncios {
    text-align: center;
    padding: 4rem 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    margin: 2rem 0;
}

.no-anuncios i {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 1rem;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin: 3rem 0;
}

.pagination {
    display: flex;
    gap: 0.5rem;
}

.pagination a,
.pagination span {
    padding: 0.5rem 1rem;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    text-decoration: none;
    color: #007bff;
    background: white;
    transition: all 0.2s;
}

.pagination a:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination .active {
    background: #007bff;
    color: white;
    border-color: #007bff;
    font-weight: 600;
}

.pagination .disabled {
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.5;
}

.pagination .disabled:hover {
    background: white;
    color: #6c757d;
}

.btn-reveal-phone {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
    padding: 0.625rem 1rem;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
    font-weight: 500;
    line-height: 1.5;
    text-align: center;
    white-space: nowrap;
    height: 38px;
    width: 100%;
}

.btn-reveal-phone.revealed {
    background: #28a745;
    border-color: #28a745;
    width: 100%;
}

.btn.btn-success.btn-sm {
    padding: 0.625rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    border-radius: 6px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    transition: all 0.2s ease;
    width: 100%;
}

.btn-reveal-phone:hover:not(.revealed) {
    background: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.btn-reveal-phone.revealed:hover {
    background: #218838;
    border-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40,167,69,0.2);
}

.btn-reveal-phone:active {
    transform: translateY(0);
}

.btn-reveal-phone .phone-text {
    transition: all 0.2s ease;
    display: inline-block;
    font-weight: 500;
}

.btn-reveal-phone i {
    margin-right: 0.4rem;
    font-size: 0.9rem;
}

.btn.btn-success.btn-sm:hover {
    background: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40,167,69,0.2);
}

.btn.btn-success.btn-sm:active {
    transform: translateY(0);
}

.btn.btn-success.btn-sm i {
    margin-right: 0.4rem;
    font-size: 0.9rem;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Modal de anuncio */
.anuncio-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    overflow-y: auto;
}

.anuncio-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeInModal 0.3s ease;
}

@keyframes fadeInModal {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    position: relative;
    width: 90%;
    max-width: 900px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    margin: 2rem auto;
}

.modal-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #dc3545;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    cursor: pointer;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #dc3545;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
    transition: all 0.2s ease;
    font-weight: bold;
}

.modal-close:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(220, 53, 69, 0.6);
}

.modal-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #333;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    transition: all 0.2s ease;
    z-index: 10;
}

.modal-nav:hover {
    background: #007bff;
    color: white;
    transform: translateY(-50%) scale(1.1);
}

.modal-nav.prev {
    left: 1rem;
}

.modal-nav.next {
    right: 1rem;
}

.modal-nav:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.modal-nav:disabled:hover {
    background: white;
    color: #333;
    transform: translateY(-50%);
}

.modal-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.modal-images {
    position: relative;
    background: #f8f9fa;
    min-height: 400px;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.modal-images:active {
    cursor: grabbing;
}

.modal-image-main {
    width: 100%;
    height: 500px;
    object-fit: contain;
    background: #f8f9fa;
}

.modal-image-placeholder {
    width: 100%;
    height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e9ecef;
    color: #6c757d;
    font-size: 4rem;
}

.modal-image-counter {
    position: absolute;
    top: 1rem;
    left: 1rem;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    z-index: 100;
    pointer-events: none;
}

.modal-image-controls {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    display: flex;
    gap: 0.5rem;
    z-index: 100;
}

.modal-control-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.modal-control-btn:hover {
    background: rgba(0, 123, 255, 0.95);
    border-color: rgba(255, 255, 255, 0.7);
    transform: scale(1.1);
}

.modal-control-btn:active {
    transform: scale(0.95);
}

.modal-control-btn.playing {
    background: rgba(0, 123, 255, 0.95);
}

.modal-control-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.modal-control-btn:disabled:hover {
    background: rgba(0, 0, 0, 0.8);
    transform: scale(1);
}

.modal-info {
    padding: 2rem;
    overflow-y: auto;
    max-height: 500px;
    -webkit-overflow-scrolling: touch;
}

.modal-oficio {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 1rem;
}

.modal-titulo {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.modal-descripcion {
    font-size: 1rem;
    color: #6c757d;
    line-height: 1.8;
    margin-bottom: 1.5rem;
    white-space: pre-wrap;
}

.modal-precio {
    font-size: 2rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 0.5rem;
}

.modal-fecha {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-botones {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .anuncio-modal {
        align-items: flex-start;
        padding: 0;
        padding-top: 60px;
    }
    
    .modal-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        max-height: calc(100vh - 60px);
        overflow-y: auto;
    }
    
    .modal-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 44px;
        height: 44px;
        font-size: 1.4rem;
        background: rgba(220, 53, 69, 0.95);
        color: white;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.5);
    }
    
    .modal-content {
        grid-template-columns: 1fr;
    }
    
    .modal-images {
        min-height: 200px;
    }
    
    .modal-image-main,
    .modal-image-placeholder {
        height: 200px;
        object-fit: contain;
        background: #f8f9fa;
    }
    
    .modal-image-counter {
        top: 0.75rem;
        left: 0.75rem;
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        z-index: 100;
    }
    
    .modal-info {
        max-height: none;
        padding: 1.25rem;
    }
    
    .modal-oficio {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        margin-bottom: 0.75rem;
    }
    
    .modal-titulo {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
    }
    
    .modal-descripcion {
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .modal-precio {
        font-size: 1.6rem;
        margin-bottom: 0.4rem;
    }
    
    .modal-fecha {
        font-size: 0.8rem;
        margin-bottom: 1rem;
    }
    
    .modal-botones {
        gap: 0.6rem;
        margin-top: 1rem;
    }
    
    .modal-nav {
        position: fixed;
        width: 38px;
        height: 38px;
        font-size: 1.1rem;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .modal-nav:hover {
        transform: translateY(-50%) scale(1.05);
    }
    
    .modal-nav.prev {
        left: 10px;
    }
    
    .modal-nav.next {
        right: 10px;
    }
}

@media (max-width: 768px) {
    .container {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .breadcrumb-categoria {
        padding: 0.75rem 0;
        margin-bottom: 0.75rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
        background: white;
        display: block !important;
        visibility: visible !important;
    }
    
    .breadcrumb-categoria span {
        gap: 0.25rem;
        font-size: 0.75rem;
    }
    
    .breadcrumb-categoria a,
    .breadcrumb-categoria strong {
        font-size: 0.75rem;
        display: inline;
    }
    
    .breadcrumb-categoria a i {
        font-size: 0.7rem;
    }
    
    .categoria-header {
        padding: 1rem 0;
        margin-bottom: 1rem;
    }
    
    .categoria-title {
        font-size: 1.5rem;
    }
    
    .categoria-icon {
        font-size: 1.75rem;
        width: 50px;
        height: 50px;
        margin-bottom: 0.5rem;
    }
    
    .categoria-stats {
        font-size: 0.75rem;
        padding: 0.35rem 0.85rem;
        margin-top: 0.35rem;
    }
    
    .anuncio-info-top {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .anuncio-precio {
        font-size: 1.5rem;
    }
    
    .anuncio-fecha {
        font-size: 0.75rem;
    }
}
</style>

<!-- Breadcrumb -->
<div class="container">
    <nav class="breadcrumb-categoria" aria-label="breadcrumb">
        <span>
            <a href="<?= app_url('index.php') ?>"><i class="fas fa-home"></i> Inicio</a> › 
            <a href="<?= app_url('index.php') ?>">Categorías</a> › 
            <strong><?= htmlspecialchars($categoria['nombre']) ?></strong>
        </span>
    </nav>
</div>

<!-- Header de Categoría -->
<div class="categoria-header">
    <div class="container text-center">
        <div class="categoria-icon">
            <i class="<?= htmlspecialchars($categoria['icono'] ?: 'fas fa-briefcase') ?>"></i>
        </div>
        <h1 class="categoria-title"><?= htmlspecialchars($categoria['nombre']) ?></h1>
        
        <?php if (!empty($categoria['descripcion'])): ?>
            <p class="categoria-descripcion">
                <?= htmlspecialchars($categoria['descripcion']) ?>
            </p>
        <?php endif; ?>
        
        <div class="categoria-stats">
            <i class="fas fa-briefcase"></i> 
            <?= $totalAnuncios ?> <?= $totalAnuncios === 1 ? 'anuncio disponible' : 'anuncios disponibles' ?>
        </div>
    </div>
</div>

<!-- Grid de Anuncios -->
<div class="container-fluid mb-5" style="padding-left: 2rem; padding-right: 2rem;">
    <?php if (empty($anuncios)): ?>
        <!-- Sin anuncios -->
        <div class="no-anuncios">
            <i class="fas fa-inbox"></i>
            <h3>Aún no hay publicaciones en esta categoría</h3>
            <p style="padding-bottom: 20px;">Sé el primero en publicar un anuncio en <strong><?= htmlspecialchars($categoria['nombre']) ?></strong></p>
            <a href="<?= app_url('index.php?view=loginPhone') ?>" class="btn btn-publish mt-3">
                + Publícate
            </a>
        </div>
    <?php else: ?>
        <!-- Grid de anuncios -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
            <?php foreach ($anuncios as $index => $anuncio): ?>
                <div>
                    <div class="anuncio-card" 
                         style="cursor: pointer;"
                         onclick="abrirModal(<?= $index ?>)"
                         data-anuncio-index="<?= $index ?>"
                         data-anuncio="<?= htmlspecialchars(json_encode([
                             'id' => $anuncio['id'],
                             'titulo' => $anuncio['titulo'],
                             'descripcion' => $anuncio['descripcion'] ?? '',
                             'precio' => $anuncio['precio'],
                             'imagen_principal' => $anuncio['imagen_principal'] ?? '',
                             'imagenes' => $anuncio['imagenes'] ?? [],
                             'total_imagenes' => $anuncio['total_imagenes'] ?? 0,
                             'oficio_nombre' => $anuncio['oficio_nombre'] ?? '',
                             'created_at' => $anuncio['created_at'],
                             'usuario_telefono' => $anuncio['usuario_telefono'] ?? ''
                         ]), ENT_QUOTES, 'UTF-8') ?>">
                        <!-- Imagen -->
                        <div class="anuncio-image-wrapper" data-card-index="<?= $index ?>">
                            <?php if (!empty($anuncio['imagen_principal'])): ?>
                                <img src="<?= app_url($anuncio['imagen_principal']) ?>" 
                                     alt="<?= htmlspecialchars($anuncio['titulo']) ?>" 
                                     class="anuncio-image card-image"
                                     data-card-index="<?= $index ?>"
                                     onerror="this.parentElement.innerHTML='<div class=\'anuncio-image-placeholder\'><i class=\'fas fa-image\'></i></div>'">
                            <?php else: ?>
                                <div class="anuncio-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <?php 
                            $total_imgs = isset($anuncio['total_imagenes']) ? intval($anuncio['total_imagenes']) : 0;
                            if ($total_imgs > 0): 
                            ?>
                                <div class="anuncio-image-counter card-counter" data-card-index="<?= $index ?>">
                                    <?= $total_imgs ?> IMÁGENES
                                </div>
                                <?php if ($total_imgs > 1): ?>
                                <div class="anuncio-image-controls card-controls" data-card-index="<?= $index ?>">
                                    <button class="anuncio-control-btn btn-back" title="Anterior" onclick="event.stopPropagation()">
                                        <i class="fas fa-step-backward"></i>
                                    </button>
                                    <button class="anuncio-control-btn btn-play" title="Reproducir" onclick="event.stopPropagation()">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="anuncio-control-btn btn-pause" title="Pausar" style="display: none;" onclick="event.stopPropagation()">
                                        <i class="fas fa-pause"></i>
                                    </button>
                                    <button class="anuncio-control-btn btn-forward" title="Siguiente" onclick="event.stopPropagation()">
                                        <i class="fas fa-step-forward"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contenido -->
                        <div class="anuncio-body">
                            <div class="anuncio-oficio">
                                <i class="fas fa-tag"></i> <?= htmlspecialchars($anuncio['oficio_nombre']) ?>
                            </div>
                            
                            <h3 class="anuncio-titulo">
                                <?= htmlspecialchars($anuncio['titulo']) ?>
                            </h3>
                            
                            <p class="anuncio-descripcion">
                                <?php 
                                $descripcion = htmlspecialchars($anuncio['descripcion'] ?? '');
                                echo mb_substr($descripcion, 0, 150) . (mb_strlen($descripcion) > 150 ? '...' : '');
                                ?>
                            </p>
                            
                            <div class="anuncio-footer">
                                <div class="anuncio-info-top">
                                    <?php if ($anuncio['precio'] !== null && $anuncio['precio'] > 0): ?>
                                        <div class="anuncio-precio">
                                            $<?= number_format($anuncio['precio'], 0, ',', '.') ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="anuncio-precio" style="color: #007bff;">
                                            A convenir
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="anuncio-fecha">
                                        <i class="fas fa-clock"></i> 
                                        <?php
                                        $fecha = new DateTime($anuncio['created_at']);
                                        $ahora = new DateTime();
                                        $diff = $ahora->diff($fecha);
                                        
                                        if ($diff->days === 0) {
                                            echo "Publicado: Hoy";
                                        } elseif ($diff->days === 1) {
                                            echo "Publicado: Ayer";
                                        } elseif ($diff->days < 7) {
                                            echo "Publicado: Hace " . $diff->days . " días";
                                        } else {
                                            echo "Publicado: " . $fecha->format('d/m/Y');
                                        }
                                        ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($anuncio['usuario_telefono'])): 
                                    // Limpiar número de teléfono (quitar espacios, guiones, paréntesis)
                                    $telefono = preg_replace('/[^0-9]/', '', $anuncio['usuario_telefono']);
                                    // Si no empieza con 57, agregarlo
                                    if (substr($telefono, 0, 2) !== '57') {
                                        $telefono = '57' . $telefono;
                                    }
                                    // Formatear para mostrar
                                    $telefonoFormateado = '+57 ' . substr($telefono, 2, 3) . ' ' . substr($telefono, 5);
                                ?>
                                <div class="anuncio-botones" onclick="event.stopPropagation()">
                                    <a href="https://wa.me/<?= htmlspecialchars($telefono) ?>?text=Hola,%20vi%20tu%20anuncio:%20<?= urlencode($anuncio['titulo']) ?>" 
                                       class="btn btn-success btn-sm" 
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="Contactar por WhatsApp"
                                       onclick="event.stopPropagation()">
                                        <i class="fab fa-whatsapp"></i>&nbsp;Contactar
                                    </a>
                                    <button class="btn-reveal-phone btn-sm" 
                                            data-telefono="<?= htmlspecialchars($telefonoFormateado) ?>"
                                            data-anuncio-id="<?= $anuncio['id'] ?>"
                                            title="Ver número de teléfono"
                                            onclick="event.stopPropagation()">
                                        <i class="fas fa-phone"></i>&nbsp;<span class="phone-text">Ver #</span>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Modal de Anuncio -->
        <div id="anuncioModal" class="anuncio-modal">
            <div class="modal-container">
                <button class="modal-close" onclick="cerrarModal()">
                    <i class="fas fa-times"></i>
                </button>
                
                <button class="modal-nav prev" onclick="navegarAnuncio(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <button class="modal-nav next" onclick="navegarAnuncio(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="modal-content">
                    <div class="modal-images">
                        <img id="modalImage" src="" alt="" class="modal-image-main" style="display: none;">
                        <div id="modalImagePlaceholder" class="modal-image-placeholder" style="display: none;">
                            <i class="fas fa-image"></i>
                        </div>
                        <div id="modalImageCounter" class="modal-image-counter" style="display: none;"></div>
                        <div id="modalImageControls" class="modal-image-controls">
                            <button class="modal-control-btn modal-btn-back" title="Anterior">
                                <i class="fas fa-step-backward"></i>
                            </button>
                            <button class="modal-control-btn modal-btn-play" title="Reproducir">
                                <i class="fas fa-play"></i>
                            </button>
                            <button class="modal-control-btn modal-btn-pause" title="Pausar" style="display: none;">
                                <i class="fas fa-pause"></i>
                            </button>
                            <button class="modal-control-btn modal-btn-forward" title="Siguiente">
                                <i class="fas fa-step-forward"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="modal-info">
                        <div id="modalOficio" class="modal-oficio"></div>
                        <h2 id="modalTitulo" class="modal-titulo"></h2>
                        <p id="modalDescripcion" class="modal-descripcion"></p>
                        
                        <div id="modalPrecio" class="modal-precio"></div>
                        <div id="modalFecha" class="modal-fecha"></div>
                        
                        <div id="modalBotones" class="modal-botones"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Paginación -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination">
                    <!-- Anterior -->
                    <?php if ($page > 1): ?>
                        <a href="?view=categoria&<?= $categoriaSlug ? 'slug=' . urlencode($categoriaSlug) : 'id=' . $categoriaId ?>&page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </span>
                    <?php endif; ?>
                    
                    <!-- Números de página -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?view=categoria&<?= $categoriaSlug ? 'slug=' . urlencode($categoriaSlug) : 'id=' . $categoriaId ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Siguiente -->
                    <?php if ($page < $totalPages): ?>
                        <a href="?view=categoria&<?= $categoriaSlug ? 'slug=' . urlencode($categoriaSlug) : 'id=' . $categoriaId ?>&page=<?= $page + 1 ?>">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="disabled">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
// Variables globales para el modal
let anunciosData = [];
let currentIndex = 0;
let currentImageIndex = 0;
let currentImages = [];
let modalAutoplayInterval = null;
let modalIsPlaying = false;

// Variables globales para las tarjetas del grid
let cardImageIndices = {}; // {cardIndex: currentImageIndex}
let cardAutoplayIntervals = {}; // {cardIndex: intervalId}
let cardIsPlaying = {}; // {cardIndex: boolean}

// Funciones globales (deben estar disponibles para onclick)
function abrirModal(index) {
    // Verificar si se acaba de hacer un gesto de deslizamiento en tarjeta
    const deltaTouch = Math.abs(cardTouchEndX - cardTouchStartX);
    const deltaMouse = Math.abs(cardMouseEndX - cardMouseStartX);
    
    if (deltaTouch > 50 || deltaMouse > 50) {
        // Se hizo un gesto de deslizamiento, no abrir modal
        cardTouchStartX = 0;
        cardTouchEndX = 0;
        cardMouseStartX = 0;
        cardMouseEndX = 0;
        return;
    }
    
    // Pausar todos los autoplays antes de abrir modal
    Object.keys(cardAutoplayIntervals).forEach(cardIdx => {
        pauseCard(parseInt(cardIdx));
    });
    
    console.log('Abriendo modal para index:', index);
    console.log('Total anuncios:', anunciosData.length);
    if (index >= 0 && index < anunciosData.length) {
        currentIndex = index;
        mostrarAnuncio(index);
        const modal = document.getElementById('anuncioModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('Modal element not found');
        }
    } else {
        console.error('Invalid index:', index);
    }
}

function cerrarModal() {
    // Pausar autoplay del modal si está activo
    pauseModal();
    
    const modal = document.getElementById('anuncioModal');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function navegarAnuncio(direction) {
    const newIndex = currentIndex + direction;
    if (newIndex >= 0 && newIndex < anunciosData.length) {
        currentIndex = newIndex;
        mostrarAnuncio(currentIndex);
    }
}

function navegarImagen(direction) {
    const newImageIndex = currentImageIndex + direction;
    if (newImageIndex >= 0 && newImageIndex < currentImages.length) {
        currentImageIndex = newImageIndex;
        mostrarImagenActual();
    }
}

function mostrarImagenActual() {
    const modalImage = document.getElementById('modalImage');
    const modalImagePlaceholder = document.getElementById('modalImagePlaceholder');
    const modalImageCounter = document.getElementById('modalImageCounter');
    
    if (currentImages.length > 0 && currentImageIndex < currentImages.length) {
        let imageSrc = currentImages[currentImageIndex].trim();
        
        // Construir URL correctamente
        if (imageSrc.startsWith('http://') || imageSrc.startsWith('https://') || imageSrc.startsWith('//')) {
            modalImage.src = imageSrc;
        } else if (imageSrc.startsWith('/')) {
            modalImage.src = window.location.origin + imageSrc;
        } else {
            const currentUrl = new URL(window.location.href);
            const baseUrl = currentUrl.origin + currentUrl.pathname.substring(0, currentUrl.pathname.lastIndexOf('/') + 1);
            modalImage.src = baseUrl + imageSrc;
        }
        
        modalImage.style.display = 'block';
        modalImagePlaceholder.style.display = 'none';
        
        // Actualizar contador
        modalImageCounter.textContent = `${currentImageIndex + 1}/${currentImages.length} IMÁGENES`;
        modalImageCounter.style.display = 'block';
        
        // Actualizar controles
        actualizarControlesModal();
    } else {
        modalImage.style.display = 'none';
        modalImagePlaceholder.style.display = 'flex';
        modalImageCounter.style.display = 'none';
    }
}

function actualizarControlesModal() {
    const controlsContainer = document.getElementById('modalImageControls');
    if (!controlsContainer) return;
    
    const btnBack = controlsContainer.querySelector('.modal-btn-back');
    const btnForward = controlsContainer.querySelector('.modal-btn-forward');
    
    if (btnBack) {
        btnBack.disabled = (currentImageIndex === 0);
    }
    if (btnForward) {
        btnForward.disabled = (currentImageIndex === currentImages.length - 1);
    }
    
    // Mostrar/ocultar controles según cantidad de imágenes
    if (currentImages.length > 1) {
        controlsContainer.style.display = 'flex';
    } else {
        controlsContainer.style.display = 'none';
    }
}

function playModal() {
    if (!currentImages || currentImages.length <= 1) return;
    
    // Detener cualquier reproducción previa
    stopModal();
    
    modalIsPlaying = true;
    
    // Actualizar UI
    const controlsContainer = document.getElementById('modalImageControls');
    if (controlsContainer) {
        controlsContainer.querySelector('.modal-btn-play').style.display = 'none';
        controlsContainer.querySelector('.modal-btn-pause').style.display = 'flex';
        controlsContainer.querySelector('.modal-btn-pause').classList.add('playing');
    }
    
    // Iniciar autoplay cada 2 segundos
    modalAutoplayInterval = setInterval(() => {
        const nextIndex = (currentImageIndex + 1) % currentImages.length;
        currentImageIndex = nextIndex;
        mostrarImagenActual();
    }, 2000);
}

function pauseModal() {
    stopModal();
    
    // Actualizar UI
    const controlsContainer = document.getElementById('modalImageControls');
    if (controlsContainer) {
        const btnPlay = controlsContainer.querySelector('.modal-btn-play');
        const btnPause = controlsContainer.querySelector('.modal-btn-pause');
        if (btnPlay) btnPlay.style.display = 'flex';
        if (btnPause) {
            btnPause.style.display = 'none';
            btnPause.classList.remove('playing');
        }
    }
}

function stopModal() {
    if (modalAutoplayInterval) {
        clearInterval(modalAutoplayInterval);
        modalAutoplayInterval = null;
    }
    modalIsPlaying = false;
}

// Variables para gestos táctiles y arrastre (MODAL)
let touchStartX = 0;
let touchEndX = 0;
let mouseStartX = 0;
let mouseEndX = 0;
let isDragging = false;

function handleGestureEnd() {
    const modalImages = document.querySelector('.modal-images');
    if (!modalImages) return;
    
    // Determinar si es touch o mouse
    const deltaX = touchEndX !== 0 ? touchEndX - touchStartX : mouseEndX - mouseStartX;
    const threshold = 50; // Umbral mínimo de deslizamiento en píxeles
    
    if (Math.abs(deltaX) > threshold) {
        // Pausar autoplay si está activo
        pauseModal();
        
        if (deltaX > 0) {
            // Deslizar a la derecha = imagen anterior
            navegarImagen(-1);
        } else {
            // Deslizar a la izquierda = imagen siguiente
            navegarImagen(1);
        }
    }
    
    // Reset
    touchStartX = 0;
    touchEndX = 0;
    mouseStartX = 0;
    mouseEndX = 0;
    isDragging = false;
}

// Variables para gestos táctiles y arrastre (TARJETAS)
let cardTouchStartX = 0;
let cardTouchEndX = 0;
let cardMouseStartX = 0;
let cardMouseEndX = 0;
let cardIsDragging = false;
let currentCardIndex = null;

function handleCardGestureEnd(cardIndex) {
    if (currentCardIndex === null) return;
    
    const deltaX = cardTouchEndX !== 0 ? cardTouchEndX - cardTouchStartX : cardMouseEndX - cardMouseStartX;
    const threshold = 50;
    
    if (Math.abs(deltaX) > threshold) {
        // Pausar autoplay si está activo
        pauseCard(cardIndex);
        
        if (deltaX > 0) {
            navegarImagenCard(cardIndex, -1);
        } else {
            navegarImagenCard(cardIndex, 1);
        }
    }
    
    // Reset
    cardTouchStartX = 0;
    cardTouchEndX = 0;
    cardMouseStartX = 0;
    cardMouseEndX = 0;
    cardIsDragging = false;
    currentCardIndex = null;
}

function navegarImagenCard(cardIndex, direction) {
    const anuncio = anunciosData[cardIndex];
    if (!anuncio || !anuncio.imagenes || anuncio.imagenes.length <= 1) return;
    
    // Inicializar índice si no existe
    if (cardImageIndices[cardIndex] === undefined) {
        cardImageIndices[cardIndex] = 0;
    }
    
    const newIndex = cardImageIndices[cardIndex] + direction;
    if (newIndex >= 0 && newIndex < anuncio.imagenes.length) {
        cardImageIndices[cardIndex] = newIndex;
        actualizarImagenCard(cardIndex);
    }
}

function actualizarImagenCard(cardIndex) {
    const anuncio = anunciosData[cardIndex];
    if (!anuncio || !anuncio.imagenes) return;
    
    const imageIndex = cardImageIndices[cardIndex] || 0;
    const imageWrapper = document.querySelector(`.anuncio-image-wrapper[data-card-index="${cardIndex}"]`);
    if (!imageWrapper) return;
    
    const img = imageWrapper.querySelector('.card-image');
    const counter = imageWrapper.querySelector('.card-counter');
    const controlsContainer = imageWrapper.querySelector('.card-controls');
    
    if (img && anuncio.imagenes[imageIndex]) {
        let imageSrc = anuncio.imagenes[imageIndex].trim();
        
        // Construir URL correctamente
        if (imageSrc.startsWith('http://') || imageSrc.startsWith('https://') || imageSrc.startsWith('//')) {
            img.src = imageSrc;
        } else if (imageSrc.startsWith('/')) {
            img.src = window.location.origin + imageSrc;
        } else {
            const currentUrl = new URL(window.location.href);
            const baseUrl = currentUrl.origin + currentUrl.pathname.substring(0, currentUrl.pathname.lastIndexOf('/') + 1);
            img.src = baseUrl + imageSrc;
        }
    }
    
    // Actualizar contador
    if (counter) {
        counter.textContent = `${imageIndex + 1}/${anuncio.imagenes.length} IMÁGENES`;
    }
    
    // Actualizar botones
    if (controlsContainer) {
        const btnBack = controlsContainer.querySelector('.btn-back');
        const btnForward = controlsContainer.querySelector('.btn-forward');
        
        if (btnBack) {
            btnBack.disabled = (imageIndex === 0);
        }
        if (btnForward) {
            btnForward.disabled = (imageIndex === anuncio.imagenes.length - 1);
        }
    }
}

function playCard(cardIndex) {
    const anuncio = anunciosData[cardIndex];
    if (!anuncio || !anuncio.imagenes || anuncio.imagenes.length <= 1) return;
    
    // Detener cualquier reproducción previa
    stopCard(cardIndex);
    
    cardIsPlaying[cardIndex] = true;
    
    // Actualizar UI
    const controlsContainer = document.querySelector(`.card-controls[data-card-index="${cardIndex}"]`);
    if (controlsContainer) {
        controlsContainer.querySelector('.btn-play').style.display = 'none';
        controlsContainer.querySelector('.btn-pause').style.display = 'flex';
        controlsContainer.querySelector('.btn-pause').classList.add('playing');
    }
    
    // Iniciar autoplay cada 2 segundos
    cardAutoplayIntervals[cardIndex] = setInterval(() => {
        const currentIndex = cardImageIndices[cardIndex] || 0;
        const nextIndex = (currentIndex + 1) % anuncio.imagenes.length;
        cardImageIndices[cardIndex] = nextIndex;
        actualizarImagenCard(cardIndex);
    }, 2000);
}

function pauseCard(cardIndex) {
    stopCard(cardIndex);
    
    // Actualizar UI
    const controlsContainer = document.querySelector(`.card-controls[data-card-index="${cardIndex}"]`);
    if (controlsContainer) {
        controlsContainer.querySelector('.btn-play').style.display = 'flex';
        controlsContainer.querySelector('.btn-pause').style.display = 'none';
        controlsContainer.querySelector('.btn-pause').classList.remove('playing');
    }
}

function stopCard(cardIndex) {
    if (cardAutoplayIntervals[cardIndex]) {
        clearInterval(cardAutoplayIntervals[cardIndex]);
        delete cardAutoplayIntervals[cardIndex];
    }
    cardIsPlaying[cardIndex] = false;
}

function inicializarControlesCard(cardIndex) {
    const anuncio = anunciosData[cardIndex];
    if (!anuncio || !anuncio.imagenes || anuncio.imagenes.length <= 1) return;
    
    const controlsContainer = document.querySelector(`.card-controls[data-card-index="${cardIndex}"]`);
    if (!controlsContainer) return;
    
    const btnBack = controlsContainer.querySelector('.btn-back');
    const btnPlay = controlsContainer.querySelector('.btn-play');
    const btnPause = controlsContainer.querySelector('.btn-pause');
    const btnForward = controlsContainer.querySelector('.btn-forward');
    
    // Eventos de los botones
    if (btnBack) {
        btnBack.addEventListener('click', (e) => {
            e.stopPropagation();
            pauseCard(cardIndex);
            navegarImagenCard(cardIndex, -1);
        });
    }
    
    if (btnPlay) {
        btnPlay.addEventListener('click', (e) => {
            e.stopPropagation();
            playCard(cardIndex);
        });
    }
    
    if (btnPause) {
        btnPause.addEventListener('click', (e) => {
            e.stopPropagation();
            pauseCard(cardIndex);
        });
    }
    
    if (btnForward) {
        btnForward.addEventListener('click', (e) => {
            e.stopPropagation();
            pauseCard(cardIndex);
            navegarImagenCard(cardIndex, 1);
        });
    }
    
    // Estado inicial
    btnBack.disabled = true;
    btnForward.disabled = (anuncio.imagenes.length <= 1);
}

document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos de anuncios
    const anuncioCards = document.querySelectorAll('[data-anuncio]');
    anunciosData = Array.from(anuncioCards).map(card => {
        try {
            const data = JSON.parse(card.getAttribute('data-anuncio'));
            console.log('Anuncio data loaded:', data);
            return data;
        } catch (e) {
            console.error('Error parsing anuncio data:', e);
            return null;
        }
    }).filter(a => a !== null);
    
    console.log('Total anuncios loaded:', anunciosData.length);
    console.log('All anuncios data:', anunciosData);
    
    // Inicializar controles para cada tarjeta con múltiples imágenes
    anunciosData.forEach((anuncio, index) => {
        cardImageIndices[index] = 0;
        cardIsPlaying[index] = false;
        inicializarControlesCard(index);
    });
    
    // Manejar click en botones de revelar teléfono
    const btnRevealPhones = document.querySelectorAll('.btn-reveal-phone');
    
    btnRevealPhones.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const telefono = this.dataset.telefono;
            const phoneText = this.querySelector('.phone-text');
            
            // Toggle visibility
            if (this.classList.contains('revealed')) {
                phoneText.textContent = 'Ver #';
                this.classList.remove('revealed');
                this.title = 'Ver número de teléfono';
            } else {
                phoneText.textContent = telefono;
                this.classList.add('revealed');
                this.title = 'Ocultar número de teléfono';
            }
        });
    });
    
    // Cerrar modal al hacer clic fuera
    document.getElementById('anuncioModal').addEventListener('click', function(e) {
        if (e.target === this) {
            cerrarModal();
        }
    });
    
    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModal();
        }
        if (e.key === 'ArrowLeft') {
            navegarAnuncio(-1);
        }
        if (e.key === 'ArrowRight') {
            navegarAnuncio(1);
        }
    });
    
    // Eventos táctiles (touch) para dispositivos móviles
    const modalImages = document.querySelector('.modal-images');
    
    modalImages.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);
    
    modalImages.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleGestureEnd();
    }, false);
    
    // Eventos de mouse (click-arrastrar) para desktop
    modalImages.addEventListener('mousedown', function(e) {
        isDragging = true;
        mouseStartX = e.clientX;
        modalImages.style.cursor = 'grabbing';
    }, false);
    
    modalImages.addEventListener('mousemove', function(e) {
        if (isDragging) {
            mouseEndX = e.clientX;
        }
    }, false);
    
    modalImages.addEventListener('mouseup', function(e) {
        if (isDragging) {
            mouseEndX = e.clientX;
            handleGestureEnd();
            modalImages.style.cursor = 'grab';
        }
    }, false);
    
    modalImages.addEventListener('mouseleave', function(e) {
        if (isDragging) {
            mouseEndX = e.clientX;
            handleGestureEnd();
            modalImages.style.cursor = 'grab';
        }
    }, false);
    
    // Cursor visual para indicar interacción
    modalImages.style.cursor = 'grab';
    
    // Eventos de los controles del modal
    const modalControlsContainer = document.getElementById('modalImageControls');
    if (modalControlsContainer) {
        const modalBtnBack = modalControlsContainer.querySelector('.modal-btn-back');
        const modalBtnPlay = modalControlsContainer.querySelector('.modal-btn-play');
        const modalBtnPause = modalControlsContainer.querySelector('.modal-btn-pause');
        const modalBtnForward = modalControlsContainer.querySelector('.modal-btn-forward');
        
        if (modalBtnBack) {
            modalBtnBack.addEventListener('click', (e) => {
                e.stopPropagation();
                pauseModal();
                navegarImagen(-1);
            });
        }
        
        if (modalBtnPlay) {
            modalBtnPlay.addEventListener('click', (e) => {
                e.stopPropagation();
                playModal();
            });
        }
        
        if (modalBtnPause) {
            modalBtnPause.addEventListener('click', (e) => {
                e.stopPropagation();
                pauseModal();
            });
        }
        
        if (modalBtnForward) {
            modalBtnForward.addEventListener('click', (e) => {
                e.stopPropagation();
                pauseModal();
                navegarImagen(1);
            });
        }
    }
    
    // Eventos táctiles y de arrastre para tarjetas del grid
    const imageWrappers = document.querySelectorAll('.anuncio-image-wrapper');
    
    imageWrappers.forEach(wrapper => {
        const cardIndex = parseInt(wrapper.getAttribute('data-card-index'));
        
        // Touch events
        wrapper.addEventListener('touchstart', function(e) {
            cardTouchStartX = e.changedTouches[0].screenX;
            currentCardIndex = cardIndex;
        }, false);
        
        wrapper.addEventListener('touchend', function(e) {
            cardTouchEndX = e.changedTouches[0].screenX;
            handleCardGestureEnd(cardIndex);
            // Prevenir que se abra el modal si hubo un gesto de deslizamiento
            const deltaX = Math.abs(cardTouchEndX - cardTouchStartX);
            if (deltaX > 50) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, false);
        
        // Mouse events
        wrapper.addEventListener('mousedown', function(e) {
            cardIsDragging = true;
            cardMouseStartX = e.clientX;
            currentCardIndex = cardIndex;
            wrapper.style.cursor = 'grabbing';
        }, false);
        
        wrapper.addEventListener('mousemove', function(e) {
            if (cardIsDragging && currentCardIndex === cardIndex) {
                cardMouseEndX = e.clientX;
            }
        }, false);
        
        wrapper.addEventListener('mouseup', function(e) {
            if (cardIsDragging && currentCardIndex === cardIndex) {
                cardMouseEndX = e.clientX;
                handleCardGestureEnd(cardIndex);
                wrapper.style.cursor = 'grab';
                // Prevenir que se abra el modal si hubo un gesto de deslizamiento
                const deltaX = Math.abs(cardMouseEndX - cardMouseStartX);
                if (deltaX > 50) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }
        }, false);
        
        wrapper.addEventListener('mouseleave', function(e) {
            if (cardIsDragging && currentCardIndex === cardIndex) {
                cardMouseEndX = e.clientX;
                handleCardGestureEnd(cardIndex);
                wrapper.style.cursor = 'grab';
            }
        }, false);
    });
});

function mostrarAnuncio(index) {
    const anuncio = anunciosData[index];
    
    // Pausar autoplay anterior si existe
    pauseModal();
    
    // Inicializar carrusel de imágenes
    currentImages = anuncio.imagenes || [];
    currentImageIndex = 0;
    
    // Mostrar primera imagen del carrusel
    if (currentImages.length > 0) {
        mostrarImagenActual();
    } else {
        // Sin imágenes, mostrar placeholder
        const modalImage = document.getElementById('modalImage');
        const modalImagePlaceholder = document.getElementById('modalImagePlaceholder');
        const modalImageCounter = document.getElementById('modalImageCounter');
        const modalImageControls = document.getElementById('modalImageControls');
        
        modalImage.style.display = 'none';
        modalImagePlaceholder.style.display = 'flex';
        modalImageCounter.style.display = 'none';
        if (modalImageControls) modalImageControls.style.display = 'none';
    }
    
    // Actualizar información
    document.getElementById('modalOficio').innerHTML = `<i class="fas fa-tag"></i> ${anuncio.oficio_nombre}`;
    document.getElementById('modalTitulo').textContent = anuncio.titulo;
    document.getElementById('modalDescripcion').textContent = anuncio.descripcion || 'Sin descripción';
    
    // Precio
    const modalPrecio = document.getElementById('modalPrecio');
    if (anuncio.precio && anuncio.precio > 0) {
        modalPrecio.textContent = '$' + parseInt(anuncio.precio).toLocaleString('es-CO');
        modalPrecio.style.color = '#28a745';
    } else {
        modalPrecio.textContent = 'A convenir';
        modalPrecio.style.color = '#007bff';
    }
    
    // Fecha
    const fecha = new Date(anuncio.created_at);
    const ahora = new Date();
    const diffTime = Math.abs(ahora - fecha);
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    let fechaTexto = '';
    if (diffDays === 0) {
        fechaTexto = 'Publicado: Hoy';
    } else if (diffDays === 1) {
        fechaTexto = 'Publicado: Ayer';
    } else if (diffDays < 7) {
        fechaTexto = `Publicado: Hace ${diffDays} días`;
    } else {
        fechaTexto = 'Publicado: ' + fecha.toLocaleDateString('es-CO');
    }
    
    document.getElementById('modalFecha').innerHTML = `<i class="fas fa-clock"></i> ${fechaTexto}`;
    
    // Botones
    const modalBotones = document.getElementById('modalBotones');
    if (anuncio.usuario_telefono) {
        let telefono = anuncio.usuario_telefono.replace(/[^0-9]/g, '');
        if (!telefono.startsWith('57')) {
            telefono = '57' + telefono;
        }
        const telefonoFormateado = '+57 ' + telefono.substring(2, 5) + ' ' + telefono.substring(5);
        
        modalBotones.innerHTML = `
            <a href="https://wa.me/${telefono}?text=Hola,%20vi%20tu%20anuncio:%20${encodeURIComponent(anuncio.titulo)}" 
               class="btn btn-success btn-sm" 
               target="_blank"
               rel="noopener noreferrer">
                <i class="fab fa-whatsapp"></i>&nbsp;Contactar
            </a>
            <button class="btn-reveal-phone btn-sm modal-phone-btn" 
                    data-telefono="${telefonoFormateado}">
                <i class="fas fa-phone"></i>&nbsp;<span class="phone-text">Ver #</span>
            </button>
        `;
        
        // Agregar evento al botón de teléfono del modal
        const modalPhoneBtn = modalBotones.querySelector('.modal-phone-btn');
        modalPhoneBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const phoneText = this.querySelector('.phone-text');
            
            if (this.classList.contains('revealed')) {
                phoneText.textContent = 'Ver #';
                this.classList.remove('revealed');
            } else {
                phoneText.textContent = telefonoFormateado;
                this.classList.add('revealed');
            }
        });
    } else {
        modalBotones.innerHTML = '<p style="color: #6c757d; font-style: italic;">No hay información de contacto disponible</p>';
    }
    
    // Actualizar estado de botones de navegación
    const prevBtn = document.querySelector('.modal-nav.prev');
    const nextBtn = document.querySelector('.modal-nav.next');
    
    prevBtn.disabled = (index === 0);
    nextBtn.disabled = (index === anunciosData.length - 1);
}
</script>