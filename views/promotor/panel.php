<?php
/**
 * Panel del Promotor - Vista Principal
 * 
 * Vista mínima y no intrusiva del panel promotor
 * Integrada con el diseño existente
 */

// Verificar autenticación
if (!$usuario_autenticado) {
    header("Location: index.php?action=login");
    exit;
}

// Obtener datos del promotor
$promotorModel = new Promotor();
$referidosModel = new Referidos();
$comisionesModel = new Comisiones();

$promotor = $promotorModel->findOrCreateByUsuarioId($usuario_id);
$estadisticas = $referidosModel->getEstadisticasPromotor($promotor['id']);
$comisiones = $comisionesModel->getByPromotorId($promotor['id'], 'pendiente', 10);

// URL de referido
$url_referido = "https://{$_SERVER['HTTP_HOST']}/index.php?ref=" . $promotor['codigo'];
$qr_url = "tools/qr.php?url=" . urlencode($url_referido);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header del Panel -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fas fa-bullhorn text-primary me-2"></i>
                                Panel de Promotor
                            </h4>
                            <p class="text-muted mb-0">
                                Comparte tu código y gana comisiones por cada referido registrado
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-primary fs-6 px-3 py-2">
                                <i class="fas fa-tag me-1"></i>
                                <?= htmlspecialchars($promotor['codigo']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-left-primary h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Visitas Totales
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($estadisticas['total_visitas']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-eye fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-left-success h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Registros Exitosos
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= number_format($estadisticas['total_registros']) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-left-info h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Conversión
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $estadisticas['total_visitas'] > 0 ? 
                                            number_format(($estadisticas['total_registros'] / $estadisticas['total_visitas']) * 100, 1) : 0 ?>%
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-percentage fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card border-left-warning h-100">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Comisión Total
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        $<?= number_format($estadisticas['comision_total'], 0, ',', '.') ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Herramientas de Promoción -->
            <div class="row">
                <div class="col-md-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-share-alt me-2"></i>
                                Herramientas de Promoción
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- URL de Referido -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Tu enlace de referido:</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="url-referido" 
                                           value="<?= htmlspecialchars($url_referido) ?>" 
                                           readonly>
                                    <button class="btn btn-outline-primary" 
                                            type="button" 
                                            onclick="copiarUrl()"
                                            title="Copiar enlace">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button class="btn btn-outline-success" 
                                            type="button"
                                            onclick="compartirWhatsApp()"
                                            title="Compartir en WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                </div>
                                <small class="text-muted">
                                    Comparte este enlace para que los nuevos usuarios se registren a través de tu referencia
                                </small>
                            </div>

                            <!-- Mensaje de Promoción -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mensaje sugerido:</label>
                                <textarea class="form-control" 
                                          id="mensaje-promo" 
                                          rows="3"
                                          readonly>¡Únete a Camella! 🏡✨ 
Encuentra tu hogar ideal con nosotros. Regístrate usando mi enlace y comencemos juntos tu búsqueda: <?= $url_referido ?></textarea>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="copiarMensaje()">
                                        <i class="fas fa-copy me-1"></i> Copiar mensaje
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="compartirMensaje()">
                                        <i class="fab fa-whatsapp me-1"></i> Compartir mensaje
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow h-100">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-qrcode me-2"></i>
                                Código QR
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="qr-container mb-3">
                                <img src="<?= $qr_url ?>" 
                                     alt="QR Code" 
                                     class="img-fluid border rounded"
                                     style="max-width: 200px;"
                                     onerror="this.style.display='none'; document.getElementById('qr-error').style.display='block';">
                                <div id="qr-error" style="display:none;" class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <small>QR temporalmente no disponible</small>
                                </div>
                            </div>
                            <p class="text-muted small">
                                Comparte este QR para acceso directo desde dispositivos móviles
                            </p>
                            <button class="btn btn-sm btn-outline-primary" onclick="descargarQR()">
                                <i class="fas fa-download me-1"></i> Descargar QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comisiones Recientes -->
            <?php if (!empty($comisiones)): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-coins me-2"></i>
                        Comisiones Recientes
                    </h6>
                    <a href="index.php?action=promotor_comisiones" class="btn btn-sm btn-outline-primary">
                        Ver todas <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Referido</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comisiones as $comision): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($comision['fecha_generada'])) ?></td>
                                    <td>
                                        <small class="text-muted">
                                            Usuario #<?= $comision['usuario_referido_id'] ?>
                                        </small>
                                    </td>
                                    <td class="fw-bold text-success">
                                        $<?= number_format($comision['monto'], 0, ',', '.') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <?= ucfirst($comision['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidades -->
<script>
function copiarUrl() {
    const input = document.getElementById('url-referido');
    input.select();
    document.execCommand('copy');
    
    // Feedback visual
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

function copiarMensaje() {
    const textarea = document.getElementById('mensaje-promo');
    textarea.select();
    document.execCommand('copy');
    
    // Feedback visual
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-1"></i> ¡Copiado!';
    btn.classList.remove('btn-outline-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

function compartirWhatsApp() {
    const url = document.getElementById('url-referido').value;
    const mensaje = `¡Únete a Camella! 🏡✨ Encuentra tu hogar ideal con nosotros: ${url}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
    window.open(whatsappUrl, '_blank');
}

function compartirMensaje() {
    const mensaje = document.getElementById('mensaje-promo').value;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
    window.open(whatsappUrl, '_blank');
}

function descargarQR() {
    const qrImg = document.querySelector('.qr-container img');
    const link = document.createElement('a');
    link.href = qrImg.src;
    link.download = 'mi-codigo-qr-camella.png';
    link.click();
}

// Notificaciones de nuevas comisiones (opcional)
<?php if (isset($_GET['nueva_comision'])): ?>
    // Mostrar notificación de nueva comisión
    setTimeout(() => {
        alert('¡Felicidades! Has recibido una nueva comisión por referir un usuario.');
    }, 1000);
<?php endif; ?>
</script>

<style>
/* Estilos adicionales específicos del promotor */
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.qr-container {
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .qr-container img {
        max-width: 150px !important;
    }
    
    .h5 {
        font-size: 1.1rem;
    }
    
    .input-group {
        flex-direction: column;
    }
    
    .input-group .btn {
        margin-top: 0.5rem;
        width: 100%;
    }
}
</style>