<?php
/**
 * Vista: Publicar Anuncio
 * Formulario para crear/editar anuncios con subida de imágenes
 * Disponible para: admin, promotor, publicante
 */

// Incluir configuración
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/app_paths.php';

// Verificar sesión
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ' . app_url('login.php'));
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Determinar modo: nuevo, editar, ver
$modo = $_GET['modo'] ?? 'nuevo';
// Validar modo
if (!in_array($modo, ['nuevo', 'editar', 'ver'])) {
    $modo = 'nuevo';
}

// Compatibilidad con parámetro antiguo anuncio_id
$id = (int)($_GET['id'] ?? $_GET['anuncio_id'] ?? 0);
if ($id > 0 && $modo === 'nuevo') {
    $modo = 'editar';
}

$isEdit = ($modo === 'editar' || $modo === 'ver') && $id > 0;
$soloLectura = ($modo === 'ver');

// Determinar dashboard de retorno según el rol
$dashboardUrl = match($userRole) {
    'admin' => app_url('views/admin/dashboard.php'),
    'promotor' => app_url('views/promotor/dashboard.php'),
    'publicante' => app_url('views/publicante/dashboard.php'),
    default => app_url('index.php')
};

// Si es edición o vista, cargar datos del anuncio
if ($isEdit) {
    require_once __DIR__ . '/../../config/database.php';
    $db = getPDO();
    
    $stmt = $db->prepare("
        SELECT * FROM anuncios 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $anuncio = $stmt->fetch();
    
    if (!$anuncio) {
        // Anuncio no encontrado - mostrar página 404 amigable
        $pageTitle = "Anuncio no encontrado";
        require_once __DIR__ . '/../../partials/header.php';
        ?>
        <div style="max-width: 600px; margin: 4rem auto; padding: 2rem; text-align: center; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <i class="fas fa-exclamation-triangle" style="font-size: 5rem; color: #dc3545; margin-bottom: 1rem;"></i>
            <h1 style="color: #333; margin-bottom: 1rem;">Anuncio no encontrado</h1>
            <p style="color: #666; margin-bottom: 2rem;">El anuncio que buscas no existe o ha sido eliminado.</p>
            <a href="<?= $dashboardUrl ?>" style="display: inline-block; padding: 0.75rem 2rem; background: #007bff; color: white; text-decoration: none; border-radius: 6px; transition: background 0.3s;">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
        <?php
        require_once __DIR__ . '/../../partials/footer.php';
        exit;
    }
    
    // Verificar permisos: solo el dueño o admin pueden editar
    if ($modo === 'editar' && $anuncio['user_id'] != $userId && $userRole !== 'admin') {
        header('Location: ' . $dashboardUrl);
        exit;
    }
}

// Configurar título de página
$pageTitle = match($modo) {
    'ver' => 'Ver Anuncio',
    'editar' => 'Editar Anuncio',
    default => 'Crear Anuncio'
};

// Incluir header
require_once __DIR__ . '/../../partials/header.php';
?>

<style>
    body {
        background-color: #f0f2f5;
    }
    
    .publicar-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .page-header i {
        font-size: 1.5rem;
        color: #1877f2;
    }
    
    .page-header h1 {
        margin: 0;
        font-size: 1.5rem;
        color: #1c1e21;
        font-weight: 700;
    }
    
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1c1e21;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .section-subtitle {
        font-size: 0.875rem;
        color: #65676b;
        margin-bottom: 1rem;
        display: block;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #1c1e21;
        font-size: 0.9375rem;
    }
    
    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #dddfe2;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-family: inherit;
        transition: border-color 0.2s;
        background: #f0f2f5;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #1877f2;
        background: white;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .form-group small {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.8125rem;
        color: #65676b;
    }
    
    /* Upload de imágenes */
    .upload-section {
        text-align: center;
    }
    
    .upload-area {
        border: 2px dashed #dddfe2;
        border-radius: 8px;
        padding: 2.5rem 1rem;
        cursor: pointer;
        transition: all 0.2s;
        background: #f0f2f5;
    }
    
    .upload-area:hover {
        border-color: #1877f2;
        background: #e7f3ff;
    }
    
    .upload-area i {
        font-size: 3.5rem;
        color: #1877f2;
        margin-bottom: 1rem;
        display: block;
    }
    
    .upload-area p {
        margin: 0;
        font-weight: 600;
        color: #1c1e21;
        font-size: 1rem;
    }
    
    .upload-area small {
        display: block;
        margin-top: 0.5rem;
        color: #65676b;
        font-size: 0.8125rem;
    }
    
    .images-preview {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.75rem;
        margin-top: 1rem;
    }
    
    .image-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        aspect-ratio: 1;
    }
    
    .image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-item .delete-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: background 0.2s;
    }
    
    .image-item .delete-btn:hover {
        background: rgba(220, 53, 69, 0.9);
    }
    
    .image-counter {
        text-align: center;
        margin-top: 0.75rem;
        font-size: 0.8125rem;
        color: #65676b;
    }
    
    .form-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: flex-start;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .btn-primary {
        background: #1877f2;
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary:hover:not(:disabled) {
        background: #166fe5;
    }
    
    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .btn-secondary {
        background: #e4e6eb;
        color: #1c1e21;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-secondary:hover {
        background: #d8dadf;
    }
    
    .alert {
        padding: 0.875rem 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-size: 0.9375rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .alert-warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
</style>

<div class="publicar-container">
    <!-- Header de la página -->
    <div class="page-header">
        <i class="fas fa-<?= $modo === 'ver' ? 'eye' : ($isEdit ? 'edit' : 'plus-circle') ?>"></i>
        <h1><?php
            if ($modo === 'ver') {
                echo 'Ver Anuncio';
            } elseif ($isEdit) {
                echo 'Editar Anuncio';
            } else {
                echo 'Crear Anuncio';
            }
        ?></h1>
    </div>
    
    <div id="alertContainer"></div>
    
    <form id="anuncioForm">
        <input type="hidden" name="anuncio_id" value="<?= $id ?? '' ?>">
        
        <!-- Sección: Información básica -->
        <div class="form-section">
            <div class="form-group">
                <label for="titulo">Título del anuncio <span style="color: #dc3545;">*</span></label>
                <input 
                    type="text" 
                    id="titulo" 
                    name="titulo" 
                    required 
                    maxlength="255"
                    value="<?= htmlspecialchars($anuncio['titulo'] ?? '') ?>"
                    placeholder="Ej: Plomero profesional con 10 años de experiencia"
                    <?= $soloLectura ? 'readonly' : '' ?>>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción <span style="color: #dc3545;">*</span></label>
                <textarea 
                    id="descripcion" 
                    name="descripcion" 
                    required
                    placeholder="Describe tu servicio, experiencia, disponibilidad, etc."
                    <?= $soloLectura ? 'readonly' : '' ?>
                ><?= htmlspecialchars($anuncio['descripcion'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="precio">Precio (COP)</label>
                <input 
                    type="number" 
                    id="precio" 
                    name="precio" 
                    min="0" 
                    step="1000"
                    value="<?= $anuncio['precio'] ?? '' ?>"
                    placeholder="50000"
                    <?= $soloLectura ? 'readonly' : '' ?>>
                <small>Opcional - Deja en blanco si prefieres negociar</small>
            </div>
        </div>
        
        <!-- Sección: Fotos -->
        <div class="form-section" id="fotosSection">
            <div class="upload-section">
                <span class="section-title">Fotos:</span>
                <span class="section-subtitle">Los anuncios con fotos obtienen más vistas y contactos.</span>
                
                <?php if (!$soloLectura): ?>
                    <!-- Upload activo para modo nuevo y editar -->
                    <input 
                        type="file" 
                        id="imageInput" 
                        accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                        multiple 
                        style="display: none;">
                    
                    <div class="upload-area" id="uploadArea" style="<?= $modo === 'nuevo' ? 'opacity: 0.6; cursor: not-allowed;' : '' ?>">
                        <i class="fas fa-camera"></i>
                        <p>Puedes añadir hasta 5 fotos</p>
                        <small id="uploadHelp">
                            <?php if ($modo === 'nuevo'): ?>
                                Primero publica el anuncio para poder subir fotos
                            <?php else: ?>
                                JPG, PNG, GIF, WEBP - Máximo 5MB cada una
                            <?php endif; ?>
                        </small>
                    </div>
                <?php endif; ?>
                
                <div class="images-preview" id="imagesPreview" style="<?= $modo === 'nuevo' ? 'display: none;' : '' ?>"></div>
                
                <div class="image-counter" id="imageCounter" style="<?= $modo === 'nuevo' ? 'display: none;' : '' ?>">
                    <i class="fas fa-info-circle"></i> 
                    <span id="currentCount">0</span> de 5 fotos subidas
                </div>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="form-actions">
            <?php if (!$soloLectura): ?>
            <button type="submit" class="btn-primary">
                <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i>
                <?= $isEdit ? 'Guardar cambios' : 'Publicar anuncio' ?>
            </button>
            <?php endif; ?>
            
            <a href="<?= $dashboardUrl ?>" class="btn-secondary">
                <i class="fas fa-<?= $soloLectura ? 'arrow-left' : 'times' ?>"></i> 
                <?= $soloLectura ? 'Volver' : 'Cancelar' ?>
            </a>
        </div>
    </form>
</div>

<script>
// Variables globales - usar window.anuncioId para poder actualizarlo después de crear anuncio
window.anuncioId = <?= $id ?? 'null' ?>;
const maxImages = 5;
const soloLectura = <?= $soloLectura ? 'true' : 'false' ?>;
const baseUrl = '<?= SITE_URL ?>'; // URL base del sitio
let currentImages = [];

// ============================================
// CARGAR IMÁGENES EXISTENTES
// ============================================
if (window.anuncioId) {
    loadExistingImages();
}

async function loadExistingImages() {
    try {
        const response = await fetch(`<?= app_url('api.php') ?>?action=getImages&anuncio_id=${window.anuncioId}`);
        const data = await response.json();
        
        if (data.success) {
            currentImages = data.images;
            renderImages();
        }
    } catch (error) {
        console.error('Error cargando imágenes:', error);
    }
}

// ============================================
// UPLOAD DE IMÁGENES
// ============================================
const uploadArea = document.getElementById('uploadArea');
const imageInput = document.getElementById('imageInput');
let isDialogOpen = false; // Flag para evitar múltiples aperturas

if (!soloLectura) {
    uploadArea?.addEventListener('click', (e) => {
        // Prevenir que el clic se propague
        e.stopPropagation();
        
        // Evitar abrir el diálogo si ya está abierto
        if (isDialogOpen) {
            return;
        }
        
        // Solo permitir clic si hay anuncio_id
        if (window.anuncioId) {
            isDialogOpen = true;
            imageInput.click();
            
            // Reset flag después de un momento (por si el usuario cancela)
            setTimeout(() => {
                isDialogOpen = false;
            }, 500);
        } else {
            showAlert('Primero debes publicar el anuncio para poder subir fotos', 'warning');
        }
    });

    imageInput?.addEventListener('change', async (e) => {
        isDialogOpen = false; // Reset flag
        
        if (e.target.files.length > 0) {
            await handleFiles(e.target.files);
        }
        
        imageInput.value = ''; // Reset input
    });
    
    // Detectar cuando el usuario cancela el diálogo
    imageInput?.addEventListener('cancel', () => {
        isDialogOpen = false;
    });

    // Drag & Drop
    uploadArea?.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea?.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea?.addEventListener('drop', async (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        await handleFiles(e.dataTransfer.files);
    });
}

async function handleFiles(files) {
    // Verificar que existe anuncio_id
    if (!window.anuncioId) {
        showAlert('Error: No se puede subir imágenes sin un anuncio válido', 'error');
        return;
    }
    
    const remainingSlots = maxImages - currentImages.length;
    
    if (files.length > remainingSlots) {
        showAlert(`Solo puedes subir ${remainingSlots} imagen(es) más`, 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('anuncio_id', window.anuncioId);
    
    for (let file of files) {
        formData.append('images[]', file);
    }
    
    try {
        const response = await fetch('<?= app_url('api.php') ?>?action=uploadImage', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            currentImages.push(...data.uploaded);
            renderImages();
        } else {
            showAlert(data.message, 'error');
        }
        
        if (data.errors && data.errors.length > 0) {
            showAlert(data.errors.join('<br>'), 'warning');
        }
        
    } catch (error) {
        showAlert('Error al subir imágenes', 'error');
        console.error(error);
    }
}

// ============================================
// ELIMINAR IMAGEN
// ============================================
async function deleteImage(imagenId) {
    console.log('Intentando eliminar imagen ID:', imagenId);
    
    if (!confirm('¿Estás seguro de eliminar esta imagen?')) {
        console.log('Eliminación cancelada por el usuario');
        return;
    }
    
    const formData = new FormData();
    formData.append('imagen_id', imagenId);
    
    console.log('Enviando solicitud de eliminación...');
    
    try {
        const response = await fetch('<?= app_url('api.php') ?>?action=deleteImage', {
            method: 'POST',
            body: formData
        });
        
        console.log('Respuesta recibida:', response.status);
        
        const data = await response.json();
        console.log('Datos de respuesta:', data);
        
        if (data.success) {
            showAlert(data.message, 'success');
            currentImages = currentImages.filter(img => img.id !== imagenId);
            renderImages();
            console.log('Imagen eliminada exitosamente');
        } else {
            showAlert(data.message, 'error');
            console.error('Error del servidor:', data.message);
        }
        
    } catch (error) {
        showAlert('Error al eliminar imagen', 'error');
        console.error('Error de red:', error);
    }
}

// ============================================
// RENDERIZAR IMÁGENES
// ============================================
function renderImages() {
    const container = document.getElementById('imagesPreview');
    const counter = document.getElementById('currentCount');
    
    if (!container || !counter) return;
    
    container.innerHTML = currentImages.map(img => {
        // Construir URL correctamente - agregar / si la ruta no empieza con /
        const imagePath = img.ruta.startsWith('/') ? img.ruta : '/' + img.ruta;
        const imageUrl = baseUrl + imagePath;
        
        return `
        <div class="image-item">
            <img src="${imageUrl}" alt="Imagen ${img.orden}" onerror="console.error('Error cargando imagen:', '${imageUrl}')">
            ${!soloLectura ? `
            <button type="button" class="delete-btn" data-image-id="${img.id}">
                <i class="fas fa-times"></i>
            </button>
            ` : ''}
        </div>
        `;
    }).join('');
    
    counter.textContent = currentImages.length;
    
    // Agregar event listeners a los botones de eliminar
    if (!soloLectura) {
        const deleteButtons = container.querySelectorAll('.delete-btn');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const imageId = parseInt(this.getAttribute('data-image-id'));
                deleteImage(imageId);
            });
        });
    }
    
    // Deshabilitar upload si ya tiene 5 imágenes o está en modo solo lectura
    if (uploadArea) {
        if (currentImages.length >= maxImages || soloLectura) {
            uploadArea.style.opacity = '0.5';
            uploadArea.style.cursor = 'not-allowed';
            uploadArea.onclick = null;
        } else {
            uploadArea.style.opacity = '1';
            uploadArea.style.cursor = 'pointer';
            uploadArea.onclick = () => imageInput.click();
        }
    }
}

// ============================================
// MOSTRAR ALERTAS
// ============================================
function showAlert(message, type = 'info') {
    const container = document.getElementById('alertContainer');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
    
    container.appendChild(alert);
    
    setTimeout(() => alert.remove(), 5000);
}

// ============================================
// SUBMIT FORMULARIO
// ============================================
document.getElementById('anuncioForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Obtener datos del formulario
    const formData = new FormData();
    const titulo = document.getElementById('titulo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    const precio = document.getElementById('precio').value.trim();
    
    // Validaciones básicas
    if (!titulo) {
        showAlert('El título es obligatorio', 'error');
        return;
    }
    
    if (!descripcion) {
        showAlert('La descripción es obligatoria', 'error');
        return;
    }
    
    // Agregar datos al FormData
    if (window.anuncioId) {
        formData.append('anuncio_id', window.anuncioId);
    }
    formData.append('titulo', titulo);
    formData.append('descripcion', descripcion);
    if (precio) {
        formData.append('precio', precio);
    }
    
    // Deshabilitar botón de submit
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    try {
        const response = await fetch('<?= app_url('api.php') ?>?action=saveAnuncio', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Si es modo CREAR (nuevo), actualizar a modo EDITAR para permitir subir fotos
            if (data.mode === 'create' && data.anuncio_id) {
                // Actualizar anuncioId global
                window.anuncioId = data.anuncio_id;
                
                // Actualizar campo hidden del formulario
                document.querySelector('input[name="anuncio_id"]').value = data.anuncio_id;
                
                // Activar sección de fotos
                const uploadArea = document.getElementById('uploadArea');
                const uploadHelp = document.getElementById('uploadHelp');
                const imagesPreview = document.getElementById('imagesPreview');
                const imageCounter = document.getElementById('imageCounter');
                
                if (uploadArea) {
                    uploadArea.style.opacity = '1';
                    uploadArea.style.cursor = 'pointer';
                }
                if (uploadHelp) {
                    uploadHelp.textContent = 'JPG, PNG, GIF, WEBP - Máximo 5MB cada una';
                }
                if (imagesPreview) {
                    imagesPreview.style.display = 'grid';
                }
                if (imageCounter) {
                    imageCounter.style.display = 'flex';
                }
                
                // Cambiar título del formulario
                document.querySelector('.page-header h1').innerHTML = '<i class="fas fa-edit"></i> Editar Anuncio';
                
                // Cambiar botón de submit
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar cambios';
                
                // Actualizar URL sin recargar página
                const newUrl = window.location.pathname + '?modo=editar&id=' + data.anuncio_id;
                window.history.pushState({path: newUrl}, '', newUrl);
                
                // Mostrar mensaje adicional
                showAlert('¡Anuncio creado! Ahora puedes agregar fotos', 'success');
                
            } else {
                // Si es modo EDITAR, redirigir al dashboard después de 1.5 segundos
                setTimeout(() => {
                    window.location.href = '<?= $dashboardUrl ?>';
                }, 1500);
            }
            
        } else {
            showAlert(data.message || 'Error al guardar el anuncio', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
        
    } catch (error) {
        showAlert('Error de conexión al guardar el anuncio', 'error');
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
