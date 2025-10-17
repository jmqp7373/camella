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
    <!-- Título Principal del Panel (solo título, sin CTA) -->
    <?php 
    $mostrarSoloTitulo = true;
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

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

    <!-- CTA: Crear Anuncio (desde bloque reutilizable) -->
    <?php 
    unset($mostrarSoloTitulo);
    include __DIR__ . '/../bloques/bloque_titulo.php'; 
    ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
