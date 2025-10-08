<?php
/**
 * Vista de Comisiones del Promotor
 * 
 * Historial completo de comisiones generadas
 */

// Verificar autenticación
if (!$usuario_autenticado) {
    header("Location: index.php?action=login");
    exit;
}

// Obtener datos
$promotorModel = new Promotor();
$comisionesModel = new Comisiones();

$promotor = $promotorModel->findOrCreateByUsuarioId($usuario_id);

// Parámetros de filtrado
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'todas';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 20;

// Obtener comisiones
$comisiones = $comisionesModel->getByPromotorId($promotor['id'], $estado, $por_pagina, ($pagina - 1) * $por_pagina);
$total_comisiones = $comisionesModel->contarByPromotorId($promotor['id'], $estado);
$total_paginas = ceil($total_comisiones / $por_pagina);

// Resumen financiero
$resumen = $comisionesModel->getResumenPromotor($promotor['id']);
?>

<div class="container-fluid">
    
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-coins text-warning me-2"></i>
                Mis Comisiones
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php?action=promotor_panel">Panel Promotor</a>
                    </li>
                    <li class="breadcrumb-item active">Comisiones</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="index.php?action=promotor_panel" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Comisiones Aprobadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($resumen['aprobadas'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pendientes de Aprobación
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($resumen['pendientes'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                $<?= number_format($resumen['pagadas'] ?? 0, 0, ',', '.') ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Generado
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($resumen['total'] ?? 0, 0, ',', '.') ?>
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

    <!-- Filtros y Tabla -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Historial de Comisiones
                    </h6>
                </div>
                <div class="col-md-6">
                    <!-- Filtro por estado -->
                    <div class="d-flex justify-content-end">
                        <select class="form-select form-select-sm" 
                                style="width: auto;" 
                                onchange="filtrarPorEstado(this.value)">
                            <option value="todas" <?= $estado === 'todas' ? 'selected' : '' ?>>
                                Todas las comisiones
                            </option>
                            <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>
                                Pendientes
                            </option>
                            <option value="aprobada" <?= $estado === 'aprobada' ? 'selected' : '' ?>>
                                Aprobadas
                            </option>
                            <option value="pagada" <?= $estado === 'pagada' ? 'selected' : '' ?>>
                                Pagadas
                            </option>
                            <option value="rechazada" <?= $estado === 'rechazada' ? 'selected' : '' ?>>
                                Rechazadas
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($comisiones)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-coins fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No hay comisiones registradas</h5>
                    <p class="text-muted">
                        Cuando generes referencias exitosas, aparecerán aquí tus comisiones.
                    </p>
                    <a href="index.php?action=promotor_panel" class="btn btn-primary">
                        <i class="fas fa-share-alt me-1"></i>
                        Empezar a Promocionar
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha Generada</th>
                                <th>Usuario Referido</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha Estado</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comisiones as $comision): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        #<?= $comision['id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($comision['fecha_generada'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                Usuario #<?= $comision['usuario_referido_id'] ?>
                                            </div>
                                            <?php if (!empty($comision['referido_email'])): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($comision['referido_email']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6">
                                        $<?= number_format($comision['monto'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = [
                                        'pendiente' => 'bg-warning',
                                        'aprobada' => 'bg-success',
                                        'pagada' => 'bg-info',
                                        'rechazada' => 'bg-danger'
                                    ];
                                    $class = $badge_class[$comision['estado']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $class ?>">
                                        <?= ucfirst($comision['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($comision['fecha_aprobacion']): ?>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($comision['fecha_aprobacion'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($comision['notas_admin'])): ?>
                                        <button class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="tooltip" 
                                                title="<?= htmlspecialchars($comision['notas_admin']) ?>">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
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
                        <!-- Página anterior -->
                        <?php if ($pagina > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=promotor_comisiones&estado=<?= $estado ?>&pagina=<?= $pagina - 1 ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Páginas numeradas -->
                        <?php
                        $inicio = max(1, $pagina - 2);
                        $fin = min($total_paginas, $pagina + 2);
                        
                        for ($i = $inicio; $i <= $fin; $i++):
                        ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a class="page-link" href="?action=promotor_comisiones&estado=<?= $estado ?>&pagina=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <!-- Página siguiente -->
                        <?php if ($pagina < $total_paginas): ?>
                            <li class="page-item">
                                <a class="page-link" href="?action=promotor_comisiones&estado=<?= $estado ?>&pagina=<?= $pagina + 1 ?>">
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

<script>
function filtrarPorEstado(estado) {
    window.location.href = `?action=promotor_comisiones&estado=${estado}&pagina=1`;
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.avatar-sm {
    width: 2rem;
    height: 2rem;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pagination .page-link {
    color: #4e73df;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #4e73df;
    border-color: #4e73df;
}

.pagination .page-link:hover {
    color: #2e59d9;
    background-color: #f8f9fc;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .d-flex.justify-content-end {
        justify-content: start !important;
        margin-top: 1rem;
    }
}
</style>