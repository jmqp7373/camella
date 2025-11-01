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
    padding: 1rem 0;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.breadcrumb-categoria a {
    color: #007bff;
    text-decoration: none;
}

.breadcrumb-categoria a:hover {
    text-decoration: underline;
}

.categoria-header {
    background: linear-gradient(135deg, #3a8be8 0%, #2870d1 100%);
    color: white;
    padding: 2.5rem 0;
    margin-bottom: 2rem;
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
    font-size: 2.5rem;
    margin-bottom: 1rem;
    background: rgba(255, 255, 255, 0.15);
    width: 70px;
    height: 70px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.categoria-title {
    font-size: 2.25rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
    letter-spacing: -0.5px;
}

.categoria-descripcion {
    font-size: 1rem;
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto 1rem;
    line-height: 1.5;
}

.categoria-stats {
    margin-top: 1rem;
    font-size: 0.95rem;
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1.25rem;
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
    align-items: center;
    gap: 1rem;
}

.anuncio-botones {
    display: flex;
    gap: 1rem;
    align-items: stretch;
}

.anuncio-precio {
    font-size: 1.75rem;
    font-weight: 700;
    color: #28a745;
    line-height: 1;
    margin: 0;
    padding: 0;
}

.anuncio-fecha {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0;
    padding: 0;
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
    padding: 0.5rem 1rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1.5;
    text-align: center;
    white-space: nowrap;
    height: 38px;
    min-width: 85px;
}

.btn-reveal-phone.revealed {
    background: #28a745;
    border-color: #28a745;
    min-width: 165px;
}

.btn.btn-success.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 6px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    transition: all 0.2s ease;
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

@media (max-width: 768px) {
    .categoria-title {
        font-size: 2rem;
    }
    
    .categoria-icon {
        font-size: 2.5rem;
    }
    
    .anuncio-footer {
        gap: 1rem;
        padding-top: 1rem;
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
    
    .anuncio-botones {
        width: 100%;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn.btn-success.btn-sm,
    .btn-reveal-phone {
        width: 100%;
        justify-content: center;
        min-width: unset;
        padding: 0.625rem 1rem;
        font-size: 0.9rem;
    }
    
    .btn-reveal-phone.revealed {
        width: 100%;
        min-width: unset;
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
<div class="container-fluid px-4 mb-5">
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
            <?php foreach ($anuncios as $anuncio): ?>
                <div>
                    <div class="anuncio-card">
                        <!-- Imagen -->
                        <div class="anuncio-image-wrapper">
                            <?php if (!empty($anuncio['imagen_principal'])): ?>
                                <img src="<?= app_url($anuncio['imagen_principal']) ?>" 
                                     alt="<?= htmlspecialchars($anuncio['titulo']) ?>" 
                                     class="anuncio-image"
                                     onerror="this.parentElement.innerHTML='<div class=\'anuncio-image-placeholder\'><i class=\'fas fa-image\'></i></div>'">
                            <?php else: ?>
                                <div class="anuncio-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <?php if ($anuncio['total_imagenes'] > 0): ?>
                                <div class="anuncio-image-counter">
                                    1/<?= $anuncio['total_imagenes'] ?> IMÁGENES
                                </div>
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
                                <div class="anuncio-botones">
                                    <a href="https://wa.me/<?= htmlspecialchars($telefono) ?>?text=Hola,%20vi%20tu%20anuncio:%20<?= urlencode($anuncio['titulo']) ?>" 
                                       class="btn btn-success btn-sm" 
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="Contactar por WhatsApp">
                                        <i class="fab fa-whatsapp"></i>&nbsp;Contactar
                                    </a>
                                    <button class="btn-reveal-phone btn-sm" 
                                            data-telefono="<?= htmlspecialchars($telefonoFormateado) ?>"
                                            data-anuncio-id="<?= $anuncio['id'] ?>"
                                            title="Ver número de teléfono">
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
document.addEventListener('DOMContentLoaded', function() {
    // Manejar click en botones de revelar teléfono
    const btnRevealPhones = document.querySelectorAll('.btn-reveal-phone');
    
    btnRevealPhones.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const telefono = this.dataset.telefono;
            const phoneText = this.querySelector('.phone-text');
            const icon = this.querySelector('i');
            
            // Toggle visibility con transición suave
            if (!this.classList.contains('revealed')) {
                // Mostrar teléfono
                this.classList.add('revealed');
                icon.className = 'fas fa-phone-alt';
                phoneText.style.opacity = '0';
                
                setTimeout(() => {
                    phoneText.textContent = telefono;
                    phoneText.style.opacity = '1';
                }, 150);
                
                this.title = 'Ocultar número';
                this.style.backgroundColor = '#6c757d';
                this.style.borderColor = '#6c757d';
            } else {
                // Ocultar teléfono
                phoneText.style.opacity = '0';
                
                setTimeout(() => {
                    this.classList.remove('revealed');
                    icon.className = 'fas fa-phone';
                    phoneText.textContent = 'Ver #';
                    phoneText.style.opacity = '1';
                    this.title = 'Ver número de teléfono';
                    this.style.backgroundColor = '';
                    this.style.borderColor = '';
                }, 150);
            }
        });
    });
});
</script>