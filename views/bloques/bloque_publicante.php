<!-- BLOQUE PUBLICANTE: Herramientas básicas para todos los usuarios -->
<?php
// Este bloque es visible para TODOS los roles (admin, promotor, publicante)

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

<!-- Mis Anuncios (Todos los usuarios) -->
<section class="publicante-section">
    <h2><i class="fas fa-briefcase"></i> Mis Anuncios</h2>
    
    <div class="dashboard-grid">
        <div class="stat-card stat-card-publicante">
            <i class="fas fa-list stat-icon" style="color: var(--color-verde);"></i>
            <h3>Anuncios Activos</h3>
            <div class="stat-number">0</div>
            <p>Publicados actualmente</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-eye stat-icon" style="color: var(--color-azul);"></i>
            <h3>Vistas Totales</h3>
            <div class="stat-number">0</div>
            <p>En todos tus anuncios</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-phone-alt stat-icon" style="color: var(--color-verde);"></i>
            <h3>Contactos</h3>
            <div class="stat-number">0</div>
            <p>Personas interesadas</p>
        </div>

        <div class="stat-card stat-card-publicante">
            <i class="fas fa-star stat-icon" style="color: var(--color-naranja);"></i>
            <h3>Calificación</h3>
            <div class="stat-number">0.0</div>
            <p>De 5.0 estrellas</p>
        </div>
    </div>

    <!-- Lista de Anuncios -->
    <div class="anuncios-list">
        <div class="list-header">
            <h3>Tus Anuncios Publicados</h3>
            <?php if (count($anuncios) > 0): ?>
                <a href="#" class="btn-small">Ver todos</a>
            <?php endif; ?>
        </div>
        
        <?php if (count($anuncios) > 0): ?>
            <!-- Grid de Anuncios -->
            <div class="anuncios-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin: 1.5rem 0;">
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
                        <div class="anuncio-contenido" style="padding: 1rem;">
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
                                    <p style="margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--color-verde, #27ae60);">
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
                                <a href="#" class="btn-editar" style="flex: 1; padding: 0.5rem; text-align: center; background: var(--color-azul, #3498db); color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="#" class="btn-ver" style="flex: 1; padding: 0.5rem; text-align: center; background: #666; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem;">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Estado vacío -->
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aún no tienes anuncios publicados</p>
                <a href="#" class="link-create">
                    <i class="fas fa-plus-circle"></i> Crear tu primer anuncio
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tips para Mejores Resultados -->
    <div class="tips-section">
        <h3><i class="fas fa-lightbulb"></i> Tips para mejores resultados</h3>
        <div class="tips-grid">
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Agrega fotos de calidad</strong>
                    <p>Los anuncios con fotos reciben 10x más contactos</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Completa tu perfil</strong>
                    <p>Los clientes confían más en perfiles completos</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Describe tus servicios</strong>
                    <p>Sé específico sobre lo que ofreces</p>
                </div>
            </div>
            <div class="tip-item">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Responde rápido</strong>
                    <p>Contesta los mensajes en menos de 24 horas</p>
                </div>
            </div>
        </div>
    </div>
</section>
