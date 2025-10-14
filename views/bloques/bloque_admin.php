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
    
    <div class="stats-grid">
        <!-- Últimas 24 horas -->
        <div class="stats-card">
            <div class="stats-card-header">
                <h3><i class="fas fa-clock"></i> Últimas 24 horas</h3>
            </div>
            <div class="stats-card-body">
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-paper-plane"></i> Enviados:
                    </span>
                    <span class="stat-value"><?= $twilioStats['24h']['total_enviados'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-check-circle"></i> Entregados:
                    </span>
                    <span class="stat-value success"><?= $twilioStats['24h']['entregas_exitosas'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-clock"></i> No convertidos:
                    </span>
                    <span class="stat-value warning"><?= $twilioStats['24h']['no_convertidos'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-hourglass-end"></i> Expirados:
                    </span>
                    <span class="stat-value error"><?= $twilioStats['24h']['expirados'] ?? 0 ?></span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-dollar-sign"></i> Costo estimado:
                    </span>
                    <span class="stat-value">$<?= $twilioStats['24h']['costo_estimado'] ?? '0.00' ?> USD</span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-percent"></i> Tasa de éxito:
                    </span>
                    <span class="stat-value"><?= $twilioStats['24h']['tasa_exito'] ?? 0 ?>%</span>
                </div>
            </div>
        </div>

        <!-- Última semana -->
        <div class="stats-card">
            <div class="stats-card-header">
                <h3><i class="fas fa-calendar-week"></i> Última semana</h3>
            </div>
            <div class="stats-card-body">
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-paper-plane"></i> Enviados:
                    </span>
                    <span class="stat-value"><?= $twilioStats['7d']['total_enviados'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-check-circle"></i> Entregados:
                    </span>
                    <span class="stat-value success"><?= $twilioStats['7d']['entregas_exitosas'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-clock"></i> No convertidos:
                    </span>
                    <span class="stat-value warning"><?= $twilioStats['7d']['no_convertidos'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-hourglass-end"></i> Expirados:
                    </span>
                    <span class="stat-value error"><?= $twilioStats['7d']['expirados'] ?? 0 ?></span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-dollar-sign"></i> Costo estimado:
                    </span>
                    <span class="stat-value">$<?= $twilioStats['7d']['costo_estimado'] ?? '0.00' ?> USD</span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-percent"></i> Tasa de éxito:
                    </span>
                    <span class="stat-value"><?= $twilioStats['7d']['tasa_exito'] ?? 0 ?>%</span>
                </div>
            </div>
        </div>

        <!-- Último mes -->
        <div class="stats-card">
            <div class="stats-card-header">
                <h3><i class="fas fa-calendar-alt"></i> Último mes</h3>
            </div>
            <div class="stats-card-body">
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-paper-plane"></i> Enviados:
                    </span>
                    <span class="stat-value"><?= $twilioStats['30d']['total_enviados'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-check-circle"></i> Entregados:
                    </span>
                    <span class="stat-value success"><?= $twilioStats['30d']['entregas_exitosas'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-clock"></i> No convertidos:
                    </span>
                    <span class="stat-value warning"><?= $twilioStats['30d']['no_convertidos'] ?? 0 ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">
                        <i class="fas fa-hourglass-end"></i> Expirados:
                    </span>
                    <span class="stat-value error"><?= $twilioStats['30d']['expirados'] ?? 0 ?></span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-dollar-sign"></i> Costo estimado:
                    </span>
                    <span class="stat-value">$<?= $twilioStats['30d']['costo_estimado'] ?? '0.00' ?> USD</span>
                </div>
                <div class="stat-row highlight">
                    <span class="stat-label">
                        <i class="fas fa-percent"></i> Tasa de éxito:
                    </span>
                    <span class="stat-value"><?= $twilioStats['30d']['tasa_exito'] ?? 0 ?>%</span>
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
