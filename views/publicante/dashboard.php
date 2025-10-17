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
    <!-- Título Principal del Panel -->
    <section class="mb-4 text-center">
        <?php 
        $titulos = [
            'admin' => 'Panel de Administración',
            'promotor' => 'Panel del Promotor',
            'publicante' => 'Mi Panel de Publicaciones'
        ];
        $rol = $_SESSION['role'] ?? 'publicante';
        ?>
        <h1 class="fw-bold text-primary">
            <i class="fas fa-tachometer-alt me-2"></i> <?= $titulos[$rol] ?>
        </h1>
        <p class="text-muted">Gestión completa del sistema y tus publicaciones</p>
    </section>

    <!-- Header del Dashboard con Role Switcher -->
    <div class="publicante-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: linear-gradient(135deg, var(--color-verde), #27ae60); color: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px; box-shadow: var(--shadow-card);">
        <div style="text-align: left;">
            <h2 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 600;">
                <i class="fas fa-user-circle"></i>
                Mis Anuncios
            </h2>
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
    <section class="text-center my-5 p-5 border rounded-4 shadow-sm" style="background-color: #fff8f8;">
        <h3 class="fw-bold text-danger mb-3">
            <i class="fas fa-bullhorn me-2"></i> ¿Tienes un servicio para ofrecer?
        </h3>
        <p class="text-muted mb-4 fs-5">
            Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
        </p>
        <a href="<?= app_url('views/bloques/publicar.php') ?>" 
           class="btn px-4 py-2 fw-bold text-white" 
           style="background-color: #b90000; border-radius: 25px;">
            + Publicar anuncio
        </a>
    </section>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
