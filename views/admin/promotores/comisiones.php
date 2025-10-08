<?php
/**
 * Vista Admin - Gestión de Comisiones
 * 
 * Panel para aprobar/rechazar comisiones y gestionar pagos
 */

if (!isset($comisiones) || !isset($estadisticas)) {
    echo "Error: Datos no disponibles";
    return;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-coins text-warning me-2"></i>
                Gestión de Comisiones
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php?view=admin">Panel Admin</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="index.php?action=admin_promotores">Promotores</a>
                    </li>
                    <li class="breadcrumb-item active">Comisiones</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="index.php?action=admin_promotores" class="btn btn-outline-primary">
                <i class="fas fa-users me-1"></i> Ver Promotores
            </a>
        </div>
    </div>

    <!-- Estadísticas de Comisiones -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($estadisticas['pendientes'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                (<?= $estadisticas['cantidad_pendientes'] ?? 0 ?> comisiones)
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aprobadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($estadisticas['aprobadas'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                (<?= $estadisticas['cantidad_aprobadas'] ?? 0 ?> comisiones)
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pagadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($estadisticas['pagadas'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                (<?= $estadisticas['cantidad_pagadas'] ?? 0 ?> comisiones)
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Rechazadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($estadisticas['rechazadas'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                (<?= $estadisticas['cantidad_rechazadas'] ?? 0 ?> comisiones)
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Controles -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Lista de Comisiones
                    </h6>
                </div>
                <div class="col-md-6">
                    <form method="GET" class="d-flex gap-2 justify-content-end">
                        <input type="hidden" name="action" value="admin_comisiones">
                        
                        <select name="estado" class="form-select form-select-sm" style="width: auto;">
                            <option value="pendiente" <?= ($estado ?? 'pendiente') === 'pendiente' ? 'selected' : '' ?>>
                                Pendientes
                            </option>
                            <option value="todas" <?= ($estado ?? '') === 'todas' ? 'selected' : '' ?>>
                                Todas
                            </option>
                            <option value="aprobada" <?= ($estado ?? '') === 'aprobada' ? 'selected' : '' ?>>
                                Aprobadas
                            </option>
                            <option value="pagada" <?= ($estado ?? '') === 'pagada' ? 'selected' : '' ?>>
                                Pagadas
                            </option>
                            <option value="rechazada" <?= ($estado ?? '') === 'rechazada' ? 'selected' : '' ?>>
                                Rechazadas
                            </option>
                        </select>
                        
                        <select name="promotor" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Todos los promotores</option>
                            <?php foreach ($promotores as $prom): ?>
                                <option value="<?= $prom['id'] ?>" 
                                        <?= ($promotor_id ?? '') == $prom['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prom['codigo']) ?> - 
                                    Usuario #<?= $prom['usuario_id'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($comisiones)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No hay comisiones para mostrar</h5>
                    <p class="text-muted">
                        Las comisiones aparecerán aquí cuando se generen referencias exitosas.
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Promotor</th>
                                <th>Usuario Referido</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Admin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comisiones as $comision): ?>
                            <tr id="comision-<?= $comision['id'] ?>">
                                <td>
                                    <span class="badge bg-light text-dark">
                                        #<?= $comision['id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-bullhorn text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div>
                                            <code class="bg-light px-2 py-1 rounded">
                                                <?= htmlspecialchars($comision['promotor_codigo']) ?>
                                            </code>
                                            <br>
                                            <small class="text-muted">
                                                Usuario #<?= $comision['promotor_usuario_id'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-success rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div>
                                            Usuario #<?= $comision['usuario_referido_id'] ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small class="fw-bold">
                                            <?= date('d/m/Y', strtotime($comision['fecha_generada'])) ?>
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('H:i', strtotime($comision['fecha_generada'])) ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">
                                        $<?= number_format($comision['monto']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $badge_classes = [
                                        'pendiente' => 'bg-warning',
                                        'aprobada' => 'bg-success',
                                        'pagada' => 'bg-info',
                                        'rechazada' => 'bg-danger'
                                    ];
                                    $class = $badge_classes[$comision['estado']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $class ?> estado-comision" 
                                          data-comision-id="<?= $comision['id'] ?>">
                                        <?= ucfirst($comision['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($comision['admin_id']): ?>
                                        <small class="text-muted">
                                            Admin #<?= $comision['admin_id'] ?>
                                            <br>
                                            <?= $comision['fecha_aprobacion'] ? 
                                                date('d/m/Y', strtotime($comision['fecha_aprobacion'])) : '' ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($comision['estado'] === 'pendiente'): ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="procesarComision(<?= $comision['id'] ?>, 'aprobar')"
                                                    title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="procesarComision(<?= $comision['id'] ?>, 'rechazar')"
                                                    title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif ($comision['estado'] === 'aprobada'): ?>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="marcarPagada(<?= $comision['id'] ?>)"
                                                    title="Marcar como pagada">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($comision['notas_admin'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    data-bs-toggle="tooltip" 
                                                    title="<?= htmlspecialchars($comision['notas_admin']) ?>">
                                                <i class="fas fa-sticky-note"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                <nav aria-label="Paginación de comisiones" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagina > 1): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?action=admin_comisiones&estado=<?= $estado ?>&promotor=<?= $promotor_id ?>&pagina=<?= $pagina - 1 ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $inicio = max(1, $pagina - 2);
                        $fin = min($total_paginas, $pagina + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++):
                        ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a class="page-link" 
                                   href="?action=admin_comisiones&estado=<?= $estado ?>&promotor=<?= $promotor_id ?>&pagina=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagina < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" 
                                   href="?action=admin_comisiones&estado=<?= $estado ?>&promotor=<?= $promotor_id ?>&pagina=<?= $pagina + 1 ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para procesar comisiones -->
<div class="modal fade" id="modalProcesarComision" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Procesar Comisión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formProcesarComision">
                <div class="modal-body">
                    <input type="hidden" id="comisionId" name="comision_id">
                    <input type="hidden" id="accionComision" name="accion">
                    
                    <div class="mb-3">
                        <label for="notasComision" class="form-label">Notas (opcional):</label>
                        <textarea class="form-control" 
                                  id="notasComision" 
                                  name="notas" 
                                  rows="3"
                                  placeholder="Razón de la decisión, comentarios adicionales..."></textarea>
                    </div>
                    
                    <div id="confirmarTexto" class="alert alert-info">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn" id="btnConfirmar">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para marcar como pagada -->
<div class="modal fade" id="modalMarcarPagada" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Marcar como Pagada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMarcarPagada">
                <div class="modal-body">
                    <input type="hidden" id="comisionPagadaId" name="comision_id">
                    
                    <div class="mb-3">
                        <label for="referenciaPago" class="form-label">Referencia de Pago:</label>
                        <input type="text" 
                               class="form-control" 
                               id="referenciaPago" 
                               name="referencia"
                               placeholder="Número de transferencia, ID de transacción, etc.">
                    </div>
                    
                    <div class="mb-3">
                        <label for="notasPago" class="form-label">Notas del Pago:</label>
                        <textarea class="form-control" 
                                  id="notasPago" 
                                  name="notas" 
                                  rows="3"
                                  placeholder="Método de pago, fecha de transferencia, comentarios..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-money-bill-wave me-1"></i>
                        Marcar como Pagada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Procesar comisión (aprobar/rechazar)
function procesarComision(comisionId, accion) {
    document.getElementById('comisionId').value = comisionId;
    document.getElementById('accionComision').value = accion;
    
    const modal = new bootstrap.Modal(document.getElementById('modalProcesarComision'));
    const titulo = document.getElementById('modalTitle');
    const confirmar = document.getElementById('confirmarTexto');
    const btnConfirmar = document.getElementById('btnConfirmar');
    
    if (accion === 'aprobar') {
        titulo.textContent = 'Aprobar Comisión';
        confirmar.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>¿Confirma que desea <strong>aprobar</strong> esta comisión?';
        btnConfirmar.className = 'btn btn-success';
        btnConfirmar.innerHTML = '<i class="fas fa-check me-1"></i> Aprobar';
    } else {
        titulo.textContent = 'Rechazar Comisión';
        confirmar.innerHTML = '<i class="fas fa-times-circle text-danger me-2"></i>¿Confirma que desea <strong>rechazar</strong> esta comisión?';
        btnConfirmar.className = 'btn btn-danger';
        btnConfirmar.innerHTML = '<i class="fas fa-times me-1"></i> Rechazar';
    }
    
    modal.show();
}

// Marcar como pagada
function marcarPagada(comisionId) {
    document.getElementById('comisionPagadaId').value = comisionId;
    const modal = new bootstrap.Modal(document.getElementById('modalMarcarPagada'));
    modal.show();
}

// Manejar envío del formulario de procesar
document.getElementById('formProcesarComision').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('index.php?action=admin_procesar_comision', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            location.reload();
        } else {
            alert('Error: ' + data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la comisión');
    });
});

// Manejar envío del formulario de marcar pagada
document.getElementById('formMarcarPagada').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('index.php?action=admin_marcar_comision_pagada', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            location.reload();
        } else {
            alert('Error: ' + data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al marcar como pagada');
    });
});

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-danger { border-left: 0.25rem solid #e74a3b !important; }

.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>