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
$anuncioId = isset($_GET['anuncio_id']) ? (int)$_GET['anuncio_id'] : null;
$isEdit = $anuncioId !== null;

// Determinar dashboard de retorno según el rol
$dashboardUrl = match($userRole) {
    'admin' => app_url('views/admin/dashboard.php'),
    'promotor' => app_url('views/promotor/dashboard.php'),
    'publicante' => app_url('views/publicante/dashboard.php'),
    default => app_url('index.php')
};

// Si es edición, cargar datos del anuncio
if ($isEdit) {
    require_once __DIR__ . '/../../config/database.php';
    $db = getPDO();
    
    $stmt = $db->prepare("
        SELECT * FROM anuncios 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$anuncioId, $userId]);
    $anuncio = $stmt->fetch();
    
    if (!$anuncio) {
        header('Location: ' . $dashboardUrl);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Publicar' ?> Anuncio - Camella</title>
    <link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .publicar-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        /* Upload de imágenes */
        .upload-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .upload-area {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-area:hover {
            background: #e7f3ff;
            border-color: #0056b3;
        }
        
        .upload-area.dragover {
            background: #cfe2ff;
            border-color: #0056b3;
        }
        
        .upload-area i {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 1rem;
        }
        
        .images-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .image-item .delete-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }
        
        .image-item .delete-btn:hover {
            background: #c82333;
        }
        
        .image-counter {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
            margin-left: 1rem;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
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
</head>
<body>

<div class="publicar-container">
    <h1>
        <i class="fas fa-<?= $isEdit ? 'edit' : 'plus-circle' ?>"></i>
        <?= $isEdit ? 'Editar' : 'Publicar' ?> Anuncio
    </h1>
    
    <div id="alertContainer"></div>
    
    <form id="anuncioForm">
        <input type="hidden" name="anuncio_id" value="<?= $anuncioId ?? '' ?>">
        
        <div class="form-group">
            <label for="titulo">
                <i class="fas fa-heading"></i> Título del anuncio *
            </label>
            <input 
                type="text" 
                id="titulo" 
                name="titulo" 
                required 
                maxlength="255"
                value="<?= htmlspecialchars($anuncio['titulo'] ?? '') ?>"
                placeholder="Ej: Plomero profesional con 10 años de experiencia">
        </div>
        
        <div class="form-group">
            <label for="descripcion">
                <i class="fas fa-align-left"></i> Descripción *
            </label>
            <textarea 
                id="descripcion" 
                name="descripcion" 
                required
                placeholder="Describe tu servicio, experiencia, disponibilidad, etc."
            ><?= htmlspecialchars($anuncio['descripcion'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="precio">
                <i class="fas fa-dollar-sign"></i> Precio (COP)
            </label>
            <input 
                type="number" 
                id="precio" 
                name="precio" 
                min="0" 
                step="1000"
                value="<?= $anuncio['precio'] ?? '' ?>"
                placeholder="50000">
            <small>Opcional - Deja en blanco si prefieres negociar</small>
        </div>
        
        <!-- Sección de imágenes -->
        <?php if ($isEdit): ?>
        <div class="upload-section">
            <h3>
                <i class="fas fa-images"></i> Imágenes del anuncio
            </h3>
            <p>Puedes subir hasta 5 imágenes. Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB por imagen.</p>
            
            <input 
                type="file" 
                id="imageInput" 
                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                multiple 
                style="display: none;">
            
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>Haz clic o arrastra imágenes aquí</p>
                <small>JPG, PNG, GIF, WEBP - Máximo 5MB cada una</small>
            </div>
            
            <div class="images-preview" id="imagesPreview"></div>
            
            <div class="image-counter" id="imageCounter">
                <i class="fas fa-info-circle"></i> 
                <span id="currentCount">0</span> de 5 imágenes subidas
            </div>
        </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-<?= $isEdit ? 'save' : 'paper-plane' ?>"></i>
                <?= $isEdit ? 'Guardar cambios' : 'Publicar anuncio' ?>
            </button>
            
            <a href="<?= $dashboardUrl ?>" class="btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<script>
const anuncioId = <?= $anuncioId ?? 'null' ?>;
const maxImages = 5;
let currentImages = [];

// ============================================
// CARGAR IMÁGENES EXISTENTES
// ============================================
if (anuncioId) {
    loadExistingImages();
}

async function loadExistingImages() {
    try {
        const response = await fetch(`<?= app_url('api.php') ?>?action=getImages&anuncio_id=${anuncioId}`);
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

uploadArea?.addEventListener('click', () => imageInput.click());

imageInput?.addEventListener('change', async (e) => {
    await handleFiles(e.target.files);
    imageInput.value = ''; // Reset input
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

async function handleFiles(files) {
    const remainingSlots = maxImages - currentImages.length;
    
    if (files.length > remainingSlots) {
        showAlert(`Solo puedes subir ${remainingSlots} imagen(es) más`, 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('anuncio_id', anuncioId);
    
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
    if (!confirm('¿Estás seguro de eliminar esta imagen?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('imagen_id', imagenId);
    
    try {
        const response = await fetch('<?= app_url('api.php') ?>?action=deleteImage', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            currentImages = currentImages.filter(img => img.id !== imagenId);
            renderImages();
        } else {
            showAlert(data.message, 'error');
        }
        
    } catch (error) {
        showAlert('Error al eliminar imagen', 'error');
        console.error(error);
    }
}

// ============================================
// RENDERIZAR IMÁGENES
// ============================================
function renderImages() {
    const container = document.getElementById('imagesPreview');
    const counter = document.getElementById('currentCount');
    
    if (!container || !counter) return;
    
    container.innerHTML = currentImages.map(img => `
        <div class="image-item">
            <img src="<?= SITE_URL ?>${img.ruta}" alt="Imagen ${img.orden}">
            <button type="button" class="delete-btn" onclick="deleteImage(${img.id})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
    
    counter.textContent = currentImages.length;
    
    // Deshabilitar upload si ya tiene 5 imágenes
    if (uploadArea) {
        if (currentImages.length >= maxImages) {
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
    
    // Aquí iría la lógica para guardar el anuncio (título, descripción, precio)
    // Por ahora solo mostramos mensaje
    
    showAlert('Anuncio guardado exitosamente', 'success');
    
    setTimeout(() => {
        window.location.href = '<?= $dashboardUrl ?>';
    }, 1500);
});
</script>

</body>
</html>
