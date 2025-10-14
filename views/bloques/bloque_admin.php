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
?>

<!-- Estadísticas de Twilio (Solo Admin) -->
<section class="twilio-stats-section">
    <h2><i class="fas fa-sms"></i> Estadísticas de SMS (Twilio)</h2>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <!-- 24 Horas -->
        <div class="stats-card" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white;">
            <div style="text-align: center; padding: 2rem 1rem 1rem 1rem; border-bottom: 1px solid #f0f0f0;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003366; margin: 0;">24 Horas</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-paper-plane" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Enviados</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-clock" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">No Convertidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-check-circle" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Vencidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-hourglass-end" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">24 Horas</span>
                </div>
                <div style="padding: 1rem 0 0.5rem 0; text-align: center;">
                    <span style="color: #555; font-size: 0.9rem;">% Tasa de éxito: $<?= $twilioStats['24h']['costo_estimado'] ?? '0.00' ?>USD</span>
                </div>
            </div>
        </div>

        <!-- 7 Días -->
        <div class="stats-card" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white;">
            <div style="text-align: center; padding: 2rem 1rem 1rem 1rem; border-bottom: 1px solid #f0f0f0;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003366; margin: 0;">7 Días</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-paper-plane" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Enviados</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-clock" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">No Convertidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-check-circle" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Vencidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-hourglass-end" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">24 Horas</span>
                </div>
                <div style="padding: 1rem 0 0.5rem 0; text-align: center;">
                    <span style="color: #555; font-size: 0.9rem;">% Tasa de éxito: $<?= $twilioStats['7d']['costo_estimado'] ?? '0.00' ?>USD</span>
                </div>
            </div>
        </div>

        <!-- 1 Mes -->
        <div class="stats-card" style="border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; background: white;">
            <div style="text-align: center; padding: 2rem 1rem 1rem 1rem; border-bottom: 1px solid #f0f0f0;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #003366; margin: 0;">1 Mes</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-paper-plane" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Enviados</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-clock" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">No Convertidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-check-circle" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">Vencidos</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0;">
                    <i class="fas fa-hourglass-end" style="color: #003366; font-size: 1rem;"></i>
                    <span style="color: #555; font-size: 0.95rem;">24 Horas</span>
                </div>
                <div style="padding: 1rem 0 0.5rem 0; text-align: center;">
                    <span style="color: #555; font-size: 0.9rem;">% Tasa de éxito: $<?= $twilioStats['30d']['costo_estimado'] ?? '0.00' ?>USD</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gestión del Sistema (Solo Admin) -->
<section class="admin-only-section">
    <h2><i class="fas fa-cogs"></i> Gestión del Sistema</h2>
    
    <div class="dashboard-grid">
        <div class="stat-card">
            <i class="fas fa-users-cog stat-icon"></i>
            <h3>Gestión de Usuarios</h3>
            <div class="stat-number">0</div>
            <p>Usuarios registrados</p>
            <a href="#" class="btn-small">Ver todos</a>
        </div>

        <div class="stat-card">
            <i class="fas fa-shield-alt stat-icon"></i>
            <h3>Roles y Permisos</h3>
            <div class="stat-number">3</div>
            <p>Roles configurados</p>
            <a href="#" class="btn-small">Gestionar</a>
        </div>

        <div class="stat-card">
            <i class="fas fa-database stat-icon"></i>
            <h3>Base de Datos</h3>
            <div class="stat-number">✓</div>
            <p>Sistema operativo</p>
            <a href="#" class="btn-small">Verificar</a>
        </div>
    </div>
</section>
