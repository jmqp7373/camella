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
    <div class="publicante-header" style="background: linear-gradient(135deg, var(--color-verde), #27ae60); color: white; padding: 30px; border-radius: var(--border-radius); margin-bottom: 30px; box-shadow: var(--shadow-card); text-align: center;">
        <h1 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 600;">
            <i class="fas fa-user-circle"></i>
            Mi Panel
        </h1>
        <p style="margin: 0; opacity: 0.9; font-size: 16px;">
            Bienvenido, <?= htmlspecialchars($_SESSION['phone'] ?? '') ?>
        </p>
    </div>

    <!-- BLOQUE PUBLICANTE: Mis Anuncios -->
    <?php include __DIR__ . '/../bloques/bloque_publicante.php'; ?>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
