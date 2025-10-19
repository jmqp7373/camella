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
<section id="anuncios-publicados" class="anuncios-section" style="margin-top: 2rem;">
    <h2 style="color: #003d7a; font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-briefcase"></i> Tus Anuncios Publicados
    </h2>
    
    <div class="bloque-wrapper">
        <?php if (count($anuncios) > 0): ?>
            <!-- Grid de Anuncios -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                <?php foreach ($anuncios as $anuncio): ?>
                    <?php 
                    // Usar el bloque reutilizable con botón eliminar
                    $mostrarEliminar = true;
                    include __DIR__ . '/bloque_mini_anuncio.php';
                    ?>
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
<?php include __DIR__ . '/modal_eliminar_anuncio.php'; ?>

<!-- Script para manejar eliminación -->
<?php include __DIR__ . '/script_eliminar_anuncio.php'; ?>
