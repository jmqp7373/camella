<?php 
/**
 * Panel de Publicante - Dashboard Modular
 * Vista con bloque básico
 */

// Verificar sesión y rol
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] !== 'publicante') {
    header('Location: ../../index.php');
    exit;
}

// Cargar configuración de rutas
require_once __DIR__ . '/../../config/app_paths.php';

$pageTitle = "Mi Panel";
require_once __DIR__ . '/../../partials/header.php';
?>

<div class="dashboard-container">
    <!-- Header del Dashboard -->
    <div class="publicante-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-verde), #27ae60); color: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px; box-shadow: var(--shadow-card);">
        <div style="text-align: left;">
            <h1 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 600;">
                <i class="fas fa-user-circle"></i>
                Mi Panel
            </h1>
            <p style="margin: 0; opacity: 0.9; font-size: 16px;">
                Bienvenido, <?= htmlspecialchars($_SESSION['phone'] ?? '') ?>
            </p>
        </div>
        
        <!-- Role Switcher (Solo Admin) -->
        <?php include __DIR__ . '/../bloques/role_switcher.php'; ?>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios -->
    <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>

    <!-- BLOQUE ANUNCIOS: Tus Anuncios Publicados (visible para todos) -->
    <?php include __DIR__ . '/../bloques/bloque_anuncios.php'; ?>

    <!-- CTA: Crear Anuncio -->
    <section id="crear-anuncio" class="text-center border rounded p-5 bg-light shadow-sm my-4">
        <h4 class="fw-bold text-primary mb-3">
            <i class="fas fa-bullhorn me-2"></i>
            ¿Tienes un servicio para ofrecer?
        </h4>
        <p class="text-muted mb-4">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" class="btn btn-primary btn-lg px-4">
            <i class="fas fa-plus-circle me-2"></i> Crear mi anuncio ahora
        </a>
    </section>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
