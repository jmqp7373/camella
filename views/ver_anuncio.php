<?php
/**
 * Vista: Ver Anuncio Individual
 * Muestra todos los detalles de un anuncio específico
 */

// Verificar sesión
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/app_paths.php';
require_once __DIR__ . '/../config/database.php';

// Obtener ID del anuncio
$anuncioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$anuncioId) {
    header('Location: ' . app_url('index.php'));
    exit;
}

// Obtener datos del anuncio
$db = getPDO();
$stmt = $db->prepare("
    SELECT a.*, u.nombre as usuario_nombre, u.email as usuario_email
    FROM anuncios a
    INNER JOIN users u ON a.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$anuncioId]);
$anuncio = $stmt->fetch();

if (!$anuncio) {
    header('Location: ' . app_url('index.php'));
    exit;
}

// Obtener imágenes del anuncio
$stmt = $db->prepare("
    SELECT * FROM anuncio_imagenes 
    WHERE anuncio_id = ? 
    ORDER BY orden ASC
");
$stmt->execute([$anuncioId]);
$imagenes = $stmt->fetchAll();

$pageTitle = $anuncio['titulo'];
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container" style="max-width: 1200px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Breadcrumb -->
    <nav style="margin-bottom: 2rem;">
        <a href="<?= app_url('index.php') ?>" style="color: #3498db; text-decoration: none;">Inicio</a>
        <span style="margin: 0 0.5rem; color: #999;">›</span>
        <span style="color: #666;">Ver Anuncio</span>
    </nav>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Columna izquierda: Imágenes -->
        <div>
            <?php if (!empty($imagenes)): ?>
                <!-- Imagen principal -->
                <div id="imagenPrincipal" style="background: #f5f5f5; border-radius: 12px; overflow: hidden; margin-bottom: 1rem; aspect-ratio: 4/3;">
                    <img src="<?= app_url($imagenes[0]['ruta']) ?>" 
                         alt="<?= htmlspecialchars($anuncio['titulo']) ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                
                <!-- Miniaturas -->
                <?php if (count($imagenes) > 1): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem;">
                    <?php foreach ($imagenes as $index => $imagen): ?>
                        <div onclick="cambiarImagen('<?= app_url($imagen['ruta']) ?>')" 
                             style="cursor: pointer; border-radius: 8px; overflow: hidden; aspect-ratio: 1; border: 2px solid <?= $index === 0 ? '#3498db' : 'transparent' ?>; transition: border 0.2s;">
                            <img src="<?= app_url($imagen['ruta']) ?>" 
                                 alt="Miniatura <?= $index + 1 ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Imagen placeholder -->
                <div style="background: #f5f5f5; border-radius: 12px; aspect-ratio: 4/3; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image" style="font-size: 4rem; color: #ccc;"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha: Información -->
        <div>
            <!-- Título -->
            <h1 style="color: #003d7a; font-size: 2rem; margin-bottom: 1rem; font-weight: 700;">
                <?= htmlspecialchars($anuncio['titulo']) ?>
            </h1>

            <!-- Precio -->
            <?php if ($anuncio['precio']): ?>
            <div style="margin-bottom: 1.5rem;">
                <span style="font-size: 2rem; color: #27ae60; font-weight: 700;">
                    $<?= number_format($anuncio['precio'], 0, ',', '.') ?>
                </span>
                <span style="color: #666; margin-left: 0.5rem;">COP</span>
            </div>
            <?php endif; ?>

            <!-- Descripción -->
            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h3 style="color: #333; font-size: 1.1rem; margin-bottom: 1rem; font-weight: 600;">
                    <i class="fas fa-align-left"></i> Descripción
                </h3>
                <p style="color: #666; line-height: 1.6; white-space: pre-wrap;">
                    <?= htmlspecialchars($anuncio['descripcion']) ?>
                </p>
            </div>

            <!-- Información del publicador -->
            <div style="background: #e3f2fd; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <h3 style="color: #1565c0; font-size: 1.1rem; margin-bottom: 1rem; font-weight: 600;">
                    <i class="fas fa-user"></i> Información del publicador
                </h3>
                <p style="margin: 0.5rem 0; color: #333;">
                    <strong>Nombre:</strong> <?= htmlspecialchars($anuncio['usuario_nombre']) ?>
                </p>
                <p style="margin: 0.5rem 0; color: #333;">
                    <strong>Email:</strong> <?= htmlspecialchars($anuncio['usuario_email']) ?>
                </p>
            </div>

            <!-- Fecha de publicación -->
            <div style="color: #999; font-size: 0.9rem; margin-bottom: 1.5rem;">
                <i class="far fa-calendar"></i> Publicado el <?= date('d/m/Y', strtotime($anuncio['created_at'])) ?>
            </div>

            <!-- Botones de acción (solo si es el propietario) -->
            <?php if ($anuncio['user_id'] == $_SESSION['user_id']): ?>
            <div style="display: flex; gap: 1rem;">
                <a href="<?= app_url('views/bloques/publicar.php?anuncio_id=' . $anuncio['id']) ?>" 
                   style="flex: 1; padding: 0.75rem; text-align: center; background: #3498db; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;"
                   onmouseover="this.style.background='#2980b9'"
                   onmouseout="this.style.background='#3498db'">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <button onclick="history.back()" 
                        style="flex: 1; padding: 0.75rem; text-align: center; background: #666; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#555'"
                        onmouseout="this.style.background='#666'">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
            <?php else: ?>
            <!-- Botón de contacto para usuarios que no son el propietario -->
            <div style="display: flex; gap: 1rem;">
                <a href="mailto:<?= htmlspecialchars($anuncio['usuario_email']) ?>" 
                   style="flex: 1; padding: 0.75rem; text-align: center; background: #27ae60; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;"
                   onmouseover="this.style.background='#229954'"
                   onmouseout="this.style.background='#27ae60'">
                    <i class="fas fa-envelope"></i> Contactar
                </a>
                <button onclick="history.back()" 
                        style="flex: 1; padding: 0.75rem; text-align: center; background: #666; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;"
                        onmouseover="this.style.background='#555'"
                        onmouseout="this.style.background='#666'">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Función para cambiar la imagen principal
function cambiarImagen(src) {
    const imagenPrincipal = document.querySelector('#imagenPrincipal img');
    imagenPrincipal.src = src;
    
    // Actualizar borde de miniatura activa
    document.querySelectorAll('[onclick^="cambiarImagen"]').forEach(thumb => {
        thumb.style.border = '2px solid transparent';
    });
    event.currentTarget.style.border = '2px solid #3498db';
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
