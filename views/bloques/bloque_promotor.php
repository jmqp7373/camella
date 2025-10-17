<!-- BLOQUE PROMOTOR: Estadísticas de Promoción -->
<?php
// Cargar estadísticas del promotor
require_once __DIR__ . '/../../controllers/PromotorStatsHelper.php';
$promotorStats = getPromotorStatistics();
?>

<section class="promotor-stats-section">
    <h2 style="color: #003d7a; font-size: 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-chart-line"></i> Estadísticas Promotor
    </h2>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <!-- 24 Horas -->
        <div class="stats-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="text-align: center; padding: 1.5rem 1rem 1rem 1rem; border-bottom: 1px solid #e8e8e8;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003d7a; margin: 0;">24 Horas</h3>
            </div>
            <div style="padding: 1.5rem 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-users" style="color: #3498db; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Usuarios Registrados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['24h']['usuarios_registrados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-alt" style="color: #9b59b6; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones Activas</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['24h']['publicaciones_activas'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chart-bar" style="color: #e67e22; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones/Usuario</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['24h']['promedio_por_usuario'] ?? '0.00' ?></strong>
                </div>
            </div>
        </div>

        <!-- 7 Días -->
        <div class="stats-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="text-align: center; padding: 1.5rem 1rem 1rem 1rem; border-bottom: 1px solid #e8e8e8;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003d7a; margin: 0;">7 Días</h3>
            </div>
            <div style="padding: 1.5rem 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-users" style="color: #3498db; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Usuarios Registrados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['7d']['usuarios_registrados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-alt" style="color: #9b59b6; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones Activas</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['7d']['publicaciones_activas'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chart-bar" style="color: #e67e22; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones/Usuario</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['7d']['promedio_por_usuario'] ?? '0.00' ?></strong>
                </div>
            </div>
        </div>

        <!-- 1 Mes -->
        <div class="stats-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="text-align: center; padding: 1.5rem 1rem 1rem 1rem; border-bottom: 1px solid #e8e8e8;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003d7a; margin: 0;">1 Mes</h3>
            </div>
            <div style="padding: 1.5rem 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-users" style="color: #3498db; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Usuarios Registrados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['30d']['usuarios_registrados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-alt" style="color: #9b59b6; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones Activas</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['30d']['publicaciones_activas'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-chart-bar" style="color: #e67e22; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Publicaciones/Usuario</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $promotorStats['30d']['promedio_por_usuario'] ?? '0.00' ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>
