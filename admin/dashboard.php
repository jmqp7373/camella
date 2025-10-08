<?php
/**
 * Dashboard de Estadísticas - Front Controller
 * 
 * Propósito: Proporcionar acceso directo al dashboard de estadísticas
 * del panel de administración mediante URL directa sin alterar el 
 * sistema de routing actual del proyecto.
 * 
 * Acceso: /admin/dashboard.php
 * 
 * Este archivo existe para permitir acceso por URL directa al dashboard
 * de estadísticas sin modificar el router principal (index.php) del proyecto.
 * Mantiene la arquitectura MVC actual y se integra con el sistema de
 * autenticación y autorización existente.
 * 
 * Flujo de ejecución:
 * 1. Cargar bootstrap para inicialización del sistema
 * 2. Verificar acceso de rol admin (AuthHelper via bootstrap)
 * 3. Instanciar controlador AdminController
 * 4. Ejecutar método dashboard() que renderiza la vista
 * 
 * Seguridad: Integrado con sistema de verificación de roles
 * Performance: Carga mínima, reutiliza componentes existentes
 * Mantenimiento: Compatible con estructura MVC actual
 */

// Cargar bootstrap del sistema para inicialización completa
// Incluye: sesiones, helpers, configuración y manejo de errores
require_once dirname(__DIR__) . '/bootstrap.php';

// Verificar acceso de administrador antes de proceder
// Esta verificación está integrada en el método dashboard() del controlador
// pero se puede hacer aquí también para fail-fast en caso de acceso no autorizado
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    // Redirigir a login con URL de retorno para mejor UX
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Cargar controlador de administración
require_once dirname(__DIR__) . '/controllers/AdminController.php';

try {
    // Instanciar controlador y ejecutar dashboard de estadísticas
    $adminController = new AdminController();
    
    // Ejecutar método dashboard que:
    // - Verifica acceso admin (doble verificación por seguridad)
    // - Obtiene conexión PDO segura
    // - Carga modelo Stats para contadores
    // - Renderiza vista stats-dashboard.php
    $adminController->dashboard();
    
} catch (Exception $e) {
    // Manejo de errores del dashboard - no romper la página
    error_log("[DASHBOARD FRONT-CONTROLLER ERROR] " . $e->getMessage());
    
    // Mostrar página de error administrativa amigable
    $pageTitle = "Error - Dashboard";
    include dirname(__DIR__) . '/partials/header.php';
    ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">
                <i class="fas fa-exclamation-triangle text-danger"></i>
                Error en Dashboard
            </h1>
        </div>
        
        <div class="alert alert-danger">
            <h4><i class="fas fa-bug"></i> Error Interno del Sistema</h4>
            <p>No se pudo cargar el dashboard de estadísticas en este momento.</p>
            <p><strong>Acción recomendada:</strong> Intentar nuevamente en unos minutos o contactar al equipo técnico.</p>
            
            <div style="margin-top: 1rem;">
                <a href="../?view=admin" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver al Panel Principal
                </a>
                <a href="javascript:location.reload()" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Reintentar
                </a>
            </div>
        </div>
    </div>
    
    <?php
    include dirname(__DIR__) . '/partials/footer.php';
}

/**
 * NOTAS DE IMPLEMENTACIÓN:
 * 
 * ROUTING ALTERNATIVO:
 * Si tu proyecto ya controla rutas via index.php?view=admin-dashboard,
 * entonces en lugar de este archivo, modificar index.php para mapear:
 * 
 * // En index.php
 * case 'admin-dashboard':
 *     require_once 'controllers/AdminController.php';
 *     $controller = new AdminController();
 *     $controller->dashboard();
 *     break;
 * 
 * INTEGRACIÓN CON .HTACCESS:
 * Para URLs más amigables, agregar a .htaccess:
 * RewriteRule ^admin/stats/?$ admin/dashboard.php [L]
 * 
 * Esto permitiría acceder via /admin/stats en lugar de /admin/dashboard.php
 * 
 * SEGURIDAD ADICIONAL:
 * - Verificación de CSRF token si implementado
 * - Rate limiting para prevenir spam de requests
 * - IP whitelist para acceso admin si requerido
 * - Logging de accesos para auditoría
 * 
 * PERFORMANCE:
 * - Cache de estadísticas con TTL de 5-10 minutos
 * - Carga asíncrona de métricas pesadas via AJAX
 * - Compresión gzip de respuesta
 */

// NO MAS CONTENIDO HTML - TODO SE MANEJA VIA CONTROLADOR Y VISTA

?>