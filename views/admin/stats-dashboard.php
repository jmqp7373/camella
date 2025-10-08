<?php 
/**
 * Dashboard de Estadísticas del Sistema
 * 
 * Propósito: Renderizar contadores y métricas principales del sistema
 * sin modificar la maquetación existente, manteniendo diseño intacto
 * y proporcionando información clave para administradores.
 * 
 * Sección de render no intrusivo: Este dashboard utiliza los partials
 * existentes (header/footer) y mantiene la tipografía y espaciados 
 * del diseño actual, sin introducir estilos personalizados que
 * puedan romper la consistencia visual del sistema.
 */

if (!isset($pageTitle)) $pageTitle = "Dashboard - Estadísticas";
include 'partials/header.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">
            <i class="fas fa-chart-bar"></i>
            Dashboard de Estadísticas
        </h1>
        <p class="admin-subtitle">Métricas y contadores del sistema</p>
    </div>
    
    <!-- Bloque de Estadísticas Principales -->
    <section class="system-status">
        <h2><i class="fas fa-tachometer-alt"></i> Estadísticas del Sistema</h2>
        
        <?php if (isset($stats) && is_array($stats)): ?>
            <!-- Grid de contadores usando estructura existente -->
            <div class="status-grid">
                <!-- Usuarios - Total y desglose por roles -->
                <div class="status-item info">
                    <div class="status-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="status-info">
                        <h3>Usuarios Totales</h3>
                        <p><strong><?= htmlspecialchars($stats['usuarios_total']) ?></strong> usuarios registrados</p>
                    </div>
                </div>
                
                <div class="status-item success">
                    <div class="status-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="status-info">
                        <h3>Administradores</h3>
                        <p><strong><?= htmlspecialchars($stats['usuarios_admin']) ?></strong> usuarios admin</p>
                    </div>
                </div>
                
                <div class="status-item warning">
                    <div class="status-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="status-info">
                        <h3>Promotores</h3>
                        <p><strong><?= htmlspecialchars($stats['usuarios_promotor']) ?></strong> usuarios promotor</p>
                    </div>
                </div>
                
                <div class="status-item info">
                    <div class="status-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="status-info">
                        <h3>Publicantes</h3>
                        <p><strong><?= htmlspecialchars($stats['usuarios_publicante']) ?></strong> usuarios publicante</p>
                    </div>
                </div>
                
                <!-- Ofertas de Trabajo -->
                <div class="status-item <?= $stats['ofertas_activas'] > 0 ? 'success' : 'warning' ?>">
                    <div class="status-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="status-info">
                        <h3>Ofertas Activas</h3>
                        <p><strong><?= htmlspecialchars($stats['ofertas_activas']) ?></strong> ofertas de trabajo</p>
                        <?php if ($stats['ofertas_activas'] == 0): ?>
                            <small>Tabla ofertas no disponible</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Empresas Registradas -->
                <div class="status-item <?= $stats['empresas'] > 0 ? 'success' : 'warning' ?>">
                    <div class="status-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="status-info">
                        <h3>Empresas</h3>
                        <p><strong><?= htmlspecialchars($stats['empresas']) ?></strong> empresas registradas</p>
                        <?php if ($stats['empresas'] == 0): ?>
                            <small>Tabla empresas no disponible</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Conexiones Exitosas -->
                <div class="status-item <?= $stats['conexiones_exitosas'] > 0 ? 'success' : 'info' ?>">
                    <div class="status-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div class="status-info">
                        <h3>Conexiones (30 días)</h3>
                        <p><strong><?= htmlspecialchars($stats['conexiones_exitosas']) ?></strong> logins exitosos</p>
                        <?php if ($stats['conexiones_exitosas'] == 0): ?>
                            <small>Tabla login_logs no disponible</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Indicador de Estado del Sistema -->
                <div class="status-item success">
                    <div class="status-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="status-info">
                        <h3>Estado del Sistema</h3>
                        <p><strong>Operativo</strong></p>
                        <small>Estadísticas actualizadas</small>
                    </div>
                </div>
            </div>
            
            <!-- Resumen Textual Adicional -->
            <div class="stats-summary" style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 0.5rem;">
                <h3><i class="fas fa-info-circle"></i> Resumen Ejecutivo</h3>
                <p>
                    El sistema cuenta con <strong><?= htmlspecialchars($stats['usuarios_total']) ?></strong> usuarios activos distribuidos en 
                    <strong><?= htmlspecialchars($stats['usuarios_admin']) ?></strong> administradores, 
                    <strong><?= htmlspecialchars($stats['usuarios_promotor']) ?></strong> promotores y 
                    <strong><?= htmlspecialchars($stats['usuarios_publicante']) ?></strong> publicantes.
                </p>
                
                <?php if ($stats['ofertas_activas'] > 0): ?>
                    <p>Actualmente hay <strong><?= htmlspecialchars($stats['ofertas_activas']) ?></strong> ofertas de trabajo activas.</p>
                <?php endif; ?>
                
                <?php if ($stats['empresas'] > 0): ?>
                    <p>Se tienen registradas <strong><?= htmlspecialchars($stats['empresas']) ?></strong> empresas en la plataforma.</p>
                <?php endif; ?>
                
                <p><em>Última actualización: <?= date('d/m/Y H:i:s') ?></em></p>
            </div>
            
        <?php else: ?>
            <!-- Estado cuando las estadísticas no están disponibles -->
            <div class="status-grid">
                <div class="status-item error">
                    <div class="status-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="status-info">
                        <h3>Estadísticas no disponibles</h3>
                        <p>No se pudieron cargar las estadísticas del sistema</p>
                        <small>Posible problema de conexión a base de datos</small>
                    </div>
                </div>
                
                <div class="status-item warning">
                    <div class="status-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="status-info">
                        <h3>Acción Recomendada</h3>
                        <p>Revisar logs del sistema y configuración de BD</p>
                        <small>Contactar al equipo técnico si persiste</small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
    
    <!-- Sección de Navegación Rápida -->
    <section class="quick-nav" style="margin-top: 2rem;">
        <h2><i class="fas fa-rocket"></i> Navegación Rápida</h2>
        
        <div class="status-grid">
            <div class="status-item info">
                <div class="status-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="status-info">
                    <h3><a href="?view=admin" style="text-decoration: none; color: inherit;">Gestión de Categorías</a></h3>
                    <p>Administrar categorías y oficios</p>
                </div>
            </div>
            
            <div class="status-item success">
                <div class="status-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="status-info">
                    <h3><a href="?view=admin-users" style="text-decoration: none; color: inherit;">Gestión de Usuarios</a></h3>
                    <p>Administrar cuentas de usuario</p>
                    <small>Próximamente disponible</small>
                </div>
            </div>
            
            <div class="status-item warning">
                <div class="status-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="status-info">
                    <h3><a href="?view=admin-ofertas" style="text-decoration: none; color: inherit;">Gestión de Ofertas</a></h3>
                    <p>Moderar ofertas de trabajo</p>
                    <small>Próximamente disponible</small>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * DÓNDE AÑADIR TARJETAS/ÍCONOS MÁS ADELANTE:
 * 
 * 1. NUEVAS MÉTRICAS:
 *    - Duplicar un bloque <div class="status-item"> existente
 *    - Cambiar ícono usando clases de Font Awesome
 *    - Actualizar título, valor y descripción
 *    - Mantener estructura HTML idéntica
 * 
 * 2. NUEVAS SECCIONES:
 *    - Crear nueva <section> después de la actual
 *    - Usar misma estructura: h2 + status-grid + status-item
 *    - Mantener clases CSS existentes para consistencia
 * 
 * 3. GRÁFICOS Y VISUALIZACIONES:
 *    - Agregar después del resumen textual
 *    - Usar librerías como Chart.js sin alterar CSS global
 *    - Envolver en contenedor con estilos inline si necesario
 * 
 * 4. FILTROS Y CONTROLES:
 *    - Añadir antes del grid principal
 *    - Usar formularios con clases bootstrap existentes
 *    - Implementar AJAX para actualizar stats dinámicamente
 * 
 * ESTRUCTURA EXISTENTE A MANTENER:
 * - admin-container: Contenedor principal
 * - admin-header: Cabecera con título
 * - system-status: Sección de contenido
 * - status-grid: Grid responsive para tarjetas
 * - status-item: Tarjeta individual con clases de estado
 * - status-icon: Contenedor del ícono
 * - status-info: Contenedor del texto
 * 
 * CLASES DE ESTADO DISPONIBLES:
 * - success: Verde (datos positivos)
 * - warning: Amarillo (alertas, datos faltantes)
 * - error: Rojo (errores, fallos)
 * - info: Azul (información neutral)
 */

include 'partials/footer.php'; 
?>