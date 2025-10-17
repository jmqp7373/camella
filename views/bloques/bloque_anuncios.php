<!-- BLOQUE ANUNCIOS: Visible para todos los roles (admin, promotor, publicante) -->
<?php
// Este bloque es visible para TODOS los roles autenticados

// Incluir configuración para SITE_URL
require_once __DIR__ . '/../../config/config.php';

// Obtener anuncios del usuario actual
$userId = $_SESSION['user_id'] ?? null;
$anuncios = [];

if ($userId) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $pdo = getPDO();
        
        // Consultar anuncios del usuario (solo si la tabla existe)
        $checkTable = $pdo->query("SHOW TABLES LIKE 'anuncios'");
        if ($checkTable->rowCount() > 0) {
            $stmt = $pdo->prepare("
                SELECT 
                    a.id, 
                    a.titulo, 
                    a.descripcion, 
                    a.precio, 
                    a.status, 
                    a.created_at,
                    (SELECT ai.ruta FROM anuncio_imagenes ai WHERE ai.anuncio_id = a.id ORDER BY ai.orden LIMIT 1) as imagen_principal
                FROM anuncios a
                WHERE a.user_id = ? AND a.status = 'activo' 
                ORDER BY a.created_at DESC 
                LIMIT 6
            ");
            $stmt->execute([$userId]);
            $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Error obteniendo anuncios: " . $e->getMessage());
    }
}
?>

<!-- Sección: Tus Anuncios Publicados -->
<section class="anuncios-section" style="margin-top: 2rem;">
    <h2 style="color: #003d7a; font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-briefcase"></i> Tus Anuncios Publicados
    </h2>
    
    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 2rem; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <?php if (count($anuncios) > 0): ?>
            <!-- Grid de Anuncios -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php foreach ($anuncios as $anuncio): ?>
                    <div class="card-anuncio" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: transform 0.2s;">
                        <!-- Imagen del anuncio -->
                        <div class="anuncio-imagen" style="height: 180px; overflow: hidden; background: #f5f5f5;">
                            <?php if (!empty($anuncio['imagen_principal'])): ?>
                                <?php 
                                    // Construir URL de imagen correctamente
                                    $imagePath = $anuncio['imagen_principal'];
                                    // Si la ruta no empieza con /, agregarla
                                    if (strpos($imagePath, '/') !== 0) {
                                        $imagePath = '/' . $imagePath;
                                    }
                                    $imageUrl = SITE_URL . $imagePath;
                                ?>
                                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                     alt="<?= htmlspecialchars($anuncio['titulo']) ?>"
                                     onerror="this.src='<?= SITE_URL ?>/assets/images/default-service.jpg'"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e8e8e8;">
                                    <i class="fas fa-image" style="font-size: 3rem; color: #999;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contenido del anuncio -->
                        <div style="padding: 1rem;">
                            <h5 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: #333; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?= htmlspecialchars($anuncio['titulo']) ?>
                            </h5>
                            
                            <?php if (isset($anuncio['descripcion'])): ?>
                                <p style="margin: 0 0 0.75rem 0; font-size: 0.9rem; color: #666; line-height: 1.4; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?= htmlspecialchars(substr($anuncio['descripcion'], 0, 80)) ?><?= strlen($anuncio['descripcion']) > 80 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem;">
                                <?php if (isset($anuncio['precio']) && $anuncio['precio'] > 0): ?>
                                    <p style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #27ae60;">
                                        $<?= number_format($anuncio['precio'], 0, ',', '.') ?>
                                    </p>
                                <?php else: ?>
                                    <p style="margin: 0; font-size: 1rem; color: #666;">
                                        A convenir
                                    </p>
                                <?php endif; ?>
                                
                                <span style="font-size: 0.85rem; color: #999;">
                                    <i class="far fa-calendar"></i>
                                    <?= date('d M Y', strtotime($anuncio['created_at'])) ?>
                                </span>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <a href="<?= app_url('views/bloques/publicar.php?modo=editar&id=' . (int)$anuncio['id']) ?>" 
                                   class="btn-editar" 
                                   style="flex: 1; padding: 0.5rem; text-align: center; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background 0.2s;"
                                   onmouseover="this.style.background='#2980b9'"
                                   onmouseout="this.style.background='#3498db'">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="<?= app_url('views/bloques/publicar.php?modo=ver&id=' . (int)$anuncio['id']) ?>" 
                                   class="btn-ver" 
                                   style="flex: 1; padding: 0.5rem; text-align: center; background: #666; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background 0.2s;"
                                   onmouseover="this.style.background='#555'"
                                   onmouseout="this.style.background='#666'">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <button 
                                    class="btn-eliminar" 
                                    data-anuncio-id="<?= $anuncio['id'] ?>" 
                                    data-anuncio-titulo="<?= htmlspecialchars($anuncio['titulo']) ?>"
                                    style="flex: 1; padding: 0.5rem; text-align: center; background: #dc3545; color: white; border: none; border-radius: 4px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;"
                                    onmouseover="this.style.background='#c82333'"
                                    onmouseout="this.style.background='#dc3545'">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Botón Ver Todos -->
            <?php if (count($anuncios) >= 6): ?>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="<?= app_url('views/bloques/publicar.php?modo=ver_todos') ?>" 
                       style="display: inline-block; padding: 0.75rem 2rem; background: #003d7a; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s;"
                       onmouseover="this.style.background='#002b5a'"
                       onmouseout="this.style.background='#003d7a'">
                        Ver todos mis anuncios <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Estado vacío -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="margin-bottom: 1.5rem;">
                    <i class="fas fa-briefcase" style="font-size: 4rem; color: #3c4c78;"></i>
                </div>
                <h3 style="color: #003d7a; font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 600;">
                    Aún no tienes anuncios publicados
                </h3>
                <p style="color: #666; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    Comienza a publicar tus servicios para llegar a más clientes
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Modal de confirmación para eliminar anuncio -->
<div id="modalEliminar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 90%; padding: 2rem; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3); animation: slideDown 0.3s ease;">
        <!-- Header del modal -->
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; background: #fee; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; color: #dc3545;"></i>
            </div>
            <h3 style="color: #333; font-size: 1.5rem; margin-bottom: 0.5rem; font-weight: 600;">
                ¿Eliminar anuncio?
            </h3>
            <p style="color: #666; font-size: 0.95rem; margin: 0;">
                Esta acción no se puede deshacer
            </p>
        </div>
        
        <!-- Información del anuncio -->
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #dc3545;">
            <p style="margin: 0; color: #333; font-weight: 500;">
                <i class="fas fa-file-alt" style="color: #dc3545; margin-right: 0.5rem;"></i>
                <span id="anuncioTituloModal">Título del anuncio</span>
            </p>
        </div>
        
        <!-- Botones de acción -->
        <div style="display: flex; gap: 1rem;">
            <button 
                id="btnCancelar" 
                style="flex: 1; padding: 0.75rem; background: #6c757d; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#5a6268'"
                onmouseout="this.style.background='#6c757d'">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button 
                id="btnConfirmarEliminar" 
                style="flex: 1; padding: 0.75rem; background: #dc3545; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;"
                onmouseover="this.style.background='#c82333'"
                onmouseout="this.style.background='#dc3545'">
                <i class="fas fa-trash-alt"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEliminar');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
    const anuncioTituloModal = document.getElementById('anuncioTituloModal');
    let anuncioIdSeleccionado = null;
    
    // Abrir modal al hacer clic en eliminar
    document.querySelectorAll('.btn-eliminar').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            anuncioIdSeleccionado = this.getAttribute('data-anuncio-id');
            const titulo = this.getAttribute('data-anuncio-titulo');
            
            anuncioTituloModal.textContent = titulo;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Bloquear scroll
        });
    });
    
    // Cerrar modal al cancelar
    btnCancelar.addEventListener('click', function() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        anuncioIdSeleccionado = null;
    });
    
    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            anuncioIdSeleccionado = null;
        }
    });
    
    // Confirmar eliminación
    btnConfirmarEliminar.addEventListener('click', async function() {
        if (!anuncioIdSeleccionado) return;
        
        // Deshabilitar botón mientras procesa
        this.disabled = true;
        this.style.opacity = '0.6';
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Eliminando...';
        
        try {
            const formData = new FormData();
            formData.append('anuncio_id', anuncioIdSeleccionado);
            
            const response = await fetch('<?= app_url("api.php") ?>?action=deleteAnuncio', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.3); z-index: 99999; animation: slideInRight 0.3s ease;';
                toast.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                document.body.appendChild(toast);
                
                // Cerrar modal
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // Recargar página después de 1 segundo
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar el anuncio'));
                this.disabled = false;
                this.style.opacity = '1';
                this.innerHTML = '<i class="fas fa-trash-alt"></i> Eliminar';
            }
        } catch (error) {
            console.error('Error al eliminar:', error);
            alert('Error de conexión. Por favor intenta de nuevo.');
            this.disabled = false;
            this.style.opacity = '1';
            this.innerHTML = '<i class="fas fa-trash-alt"></i> Eliminar';
        }
    });
});
</script>
