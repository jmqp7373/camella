<!-- BLOQUE ADMIN: Estadísticas de Twilio y Gestión del Sistema -->
<?php
// Este bloque solo es visible para usuarios con rol 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    return; // No mostrar si no es admin
}

// Cargar estadísticas de Twilio (solo para admin)
if (!isset($twilioStats)) {
    require_once __DIR__ . '/../../controllers/TwilioStatsHelper.php';
    $twilioStats = getTwilioStatistics();
}

// Cargar estadísticas de Promotor (solo para admin)
if (!isset($promotorStats)) {
    require_once __DIR__ . '/../../controllers/PromotorStatsHelper.php';
    $promotorStats = getPromotorStatistics();
}
?>

<!-- Estadísticas de Twilio (Solo Admin) -->
<section class="twilio-stats-section">
    <h2><i class="fas fa-sms"></i> Estadísticas de SMS (Twilio)</h2>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
        <!-- 24 Horas -->
        <div class="stats-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <div style="text-align: center; padding: 1.5rem 1rem 1rem 1rem; border-bottom: 1px solid #e8e8e8;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003d7a; margin: 0;">24 Horas</h3>
            </div>
            <div style="padding: 1.5rem 1.25rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-check-circle" style="color: #27ae60; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Enviados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['24h']['total_enviados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times-circle" style="color: #e74c3c; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">No Usados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['24h']['total_no_usados'] ?? 0 ?></strong>
                </div>
                <div style="padding: 1rem 0 0 0; margin-top: 0.75rem; border-top: 1px solid #f0f0f0; text-align: center;">
                    <span style="color: #333; font-size: 0.9rem;">Tasa de éxito: <strong style="color: #003d7a;"><?= $twilioStats['24h']['tasa_exito'] ?? 0 ?>%</strong></span>
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
                        <i class="fas fa-check-circle" style="color: #27ae60; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Enviados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['7d']['total_enviados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times-circle" style="color: #e74c3c; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">No Usados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['7d']['total_no_usados'] ?? 0 ?></strong>
                </div>
                <div style="padding: 1rem 0 0 0; margin-top: 0.75rem; border-top: 1px solid #f0f0f0; text-align: center;">
                    <span style="color: #333; font-size: 0.9rem;">Tasa de éxito: <strong style="color: #003d7a;"><?= $twilioStats['7d']['tasa_exito'] ?? 0 ?>%</strong></span>
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
                        <i class="fas fa-check-circle" style="color: #27ae60; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">Enviados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['30d']['total_enviados'] ?? 0 ?></strong>
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times-circle" style="color: #e74c3c; font-size: 0.9rem;"></i>
                        <span style="color: #333; font-size: 0.9rem;">No Usados</span>
                    </div>
                    <strong style="color: #003d7a; font-size: 1rem;"><?= $twilioStats['30d']['total_no_usados'] ?? 0 ?></strong>
                </div>
                <div style="padding: 1rem 0 0 0; margin-top: 0.75rem; border-top: 1px solid #f0f0f0; text-align: center;">
                    <span style="color: #333; font-size: 0.9rem;">Tasa de éxito: <strong style="color: #003d7a;"><?= $twilioStats['30d']['tasa_exito'] ?? 0 ?>%</strong></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas Promotor (Solo Admin) -->
<section class="twilio-stats-section" style="margin-top: 2rem;">
    <h2><i class="fas fa-chart-line"></i> Estadísticas Promotor</h2>
    
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
