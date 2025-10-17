<!-- BLOQUE ANUNCIOS: Visible para todos los roles (admin, promotor, publicante) -->
<?php
// Este bloque es visible para TODOS los roles autenticados

// Obtener anuncios del usuario actual
$userId = $_SESSION['user_id'] ?? null;
$anuncios = [];

if ($userId) {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $pdo = getPDO();
        
        // Consultar anuncios del usuario (solo si la tabla existe)
        $checkTable = $pdo->query("SHOW TABLES LIKE 'servicios'");
        if ($checkTable->rowCount() > 0) {
            $stmt = $pdo->prepare("
                SELECT id, titulo, descripcion, precio, imagen_principal, status, created_at 
                FROM servicios 
                WHERE user_id = ? AND status = 'activo' 
                ORDER BY created_at DESC 
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
                                <img src="<?= htmlspecialchars(APP_SUBDIR . '/uploads/' . $anuncio['imagen_principal']) ?>" 
                                     alt="<?= htmlspecialchars($anuncio['titulo']) ?>"
                                     onerror="this.src='<?= htmlspecialchars(APP_SUBDIR . '/assets/images/default-service.jpg') ?>'"
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
                                <a href="#" class="btn-editar" style="flex: 1; padding: 0.5rem; text-align: center; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background 0.2s;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="#" class="btn-ver" style="flex: 1; padding: 0.5rem; text-align: center; background: #666; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background 0.2s;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Botón Ver Todos -->
            <?php if (count($anuncios) >= 6): ?>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="#" style="display: inline-block; padding: 0.75rem 2rem; background: #003d7a; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s;">
                        Ver todos mis anuncios <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Estado vacío -->
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="margin-bottom: 1.5rem;">
                    <i class="fas fa-briefcase" style="font-size: 4rem; color: #003d7a; opacity: 0.3;"></i>
                </div>
                <h3 style="color: #003d7a; font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 600;">
                    Aún no tienes anuncios publicados
                </h3>
                <p style="color: #666; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    Comienza a publicar tus servicios para llegar a más clientes
                </p>
                <a href="#" style="display: inline-block; padding: 0.75rem 2rem; background: #27ae60; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s;">
                    <i class="fas fa-plus-circle"></i> Crear mi primer anuncio
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
