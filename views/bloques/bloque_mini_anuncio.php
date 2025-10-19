<?php
/**
 * Bloque Reutilizable: Mini Tarjeta de Anuncio
 * Componente para mostrar anuncios en formato compacto
 * 
 * Parámetros requeridos:
 * @param array $anuncio - Datos del anuncio (id, titulo, descripcion, precio, imagen_principal, created_at, total_imagenes)
 * @param bool $mostrarEliminar - Si se muestra el botón eliminar (default: false)
 */

/**
 * Función para calcular el tiempo transcurrido desde la publicación
 */
if (!function_exists('tiempoTranscurrido')) {
    function tiempoTranscurrido($fecha) {
        $ahora = new DateTime();
        $publicado = new DateTime($fecha);
        $diferencia = $ahora->diff($publicado);

        if ($diferencia->y > 0) return "hace " . $diferencia->y . " año" . ($diferencia->y > 1 ? "s" : "");
        if ($diferencia->m > 0) return "hace " . $diferencia->m . " mes" . ($diferencia->m > 1 ? "es" : "");
        if ($diferencia->d > 0) return "hace " . $diferencia->d . " día" . ($diferencia->d > 1 ? "s" : "");
        if ($diferencia->h > 0) return "hace " . $diferencia->h . " hora" . ($diferencia->h > 1 ? "s" : "");
        if ($diferencia->i > 0) return "hace " . $diferencia->i . " minuto" . ($diferencia->i > 1 ? "s" : "");
        return "hace unos segundos";
    }
}

// Validar que existe el array de anuncio
if (!isset($anuncio) || !is_array($anuncio)) {
    return;
}

// Configurar si se muestra el botón eliminar
$mostrarEliminar = $mostrarEliminar ?? false;

// Construir URL de imagen
$imagePath = $anuncio['imagen_principal'] ?? '';
if (!empty($imagePath) && strpos($imagePath, '/') !== 0) {
    $imagePath = '/' . $imagePath;
}
$imageUrl = !empty($imagePath) ? SITE_URL . $imagePath : '';
?>

<div class="card-anuncio" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: transform 0.2s;">
    <!-- Imagen del anuncio -->
    <div class="anuncio-imagen" style="position: relative; height: 180px; overflow: hidden; background: #f5f5f5;">
        <?php if (!empty($imageUrl)): ?>
            <img src="<?= htmlspecialchars($imageUrl) ?>" 
                 alt="<?= htmlspecialchars($anuncio['titulo']) ?>"
                 onerror="this.src='<?= SITE_URL ?>/assets/images/default-service.jpg'"
                 style="width: 100%; height: 100%; object-fit: cover;">
        <?php else: ?>
            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #e8e8e8;">
                <i class="fas fa-image" style="font-size: 3rem; color: #999;"></i>
            </div>
        <?php endif; ?>
        
        <!-- Contador de imágenes dinámico -->
        <?php
        $totalImagenes = $anuncio['total_imagenes'] ?? 0;
        if ($totalImagenes == 0) {
            $colorContador = '#999'; // gris
        } elseif ($totalImagenes < 5) {
            $colorContador = '#e67e22'; // naranja
        } else {
            $colorContador = '#27ae60'; // verde
        }
        ?>
        <div style="position: absolute; top: 8px; right: 10px; background: rgba(255,255,255,0.95); padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; text-align: center; color: <?= $colorContador ?>; font-weight: 600; line-height: 1.2; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
            <?= $totalImagenes ?>/5<br>
            <span style="font-size: 0.7rem; color: #777;">IMÁGENES</span>
        </div>
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
                Publicado <?= tiempoTranscurrido($anuncio['created_at']); ?>
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
            
            <?php if ($mostrarEliminar): ?>
                <button 
                    class="btn-eliminar" 
                    data-anuncio-id="<?= $anuncio['id'] ?>" 
                    data-anuncio-titulo="<?= htmlspecialchars($anuncio['titulo']) ?>"
                    style="flex: 1; padding: 0.5rem; text-align: center; background: #dc3545; color: white; border: none; border-radius: 4px; font-size: 0.9rem; cursor: pointer; transition: all 0.2s;"
                    onmouseover="this.style.background='#c82333'"
                    onmouseout="this.style.background='#dc3545'">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
