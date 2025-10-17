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
    <section id="crear-anuncio" style="margin: 3rem 0; padding: 0 1rem;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 3rem; text-align: center; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            <div style="max-width: 600px; margin: 0 auto;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-bullhorn" style="color: white; opacity: 0.9;"></i>
                </div>
                <h2 style="color: white; font-size: 2rem; margin-bottom: 1rem; font-weight: 700;">
                    ¿Tienes un servicio para ofrecer?
                </h2>
                <p style="color: rgba(255,255,255,0.9); font-size: 1.1rem; margin-bottom: 2rem; line-height: 1.6;">
                    Crea tu anuncio y comienza a recibir solicitudes de clientes interesados en tus servicios profesionales.
                </p>
                <a href="<?= app_url('views/bloques/publicar.php') ?>" 
                   style="display: inline-block; padding: 1rem 3rem; background: white; color: #667eea; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.2);"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.2)';">
                    <i class="fas fa-plus-circle"></i> Crear mi anuncio ahora
                </a>
            </div>
        </div>
    </section>

</div>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
