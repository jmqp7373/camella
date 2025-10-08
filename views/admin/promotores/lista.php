<?php
/**
 * Vista Admin - Lista de Promotores
 * 
 * Panel administrativo para gestión de promotores del sistema
 */

if (!isset($promotores) || !isset($estadisticas_sistema)) {
    echo "Error: Datos no disponibles";
    return;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-users-cog text-primary me-2"></i>
                Gestión de Promotores
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="index.php?view=admin">Panel Admin</a>
                    </li>
                    <li class="breadcrumb-item active">Promotores</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?action=admin_comisiones" class="btn btn-outline-warning">
                <i class="fas fa-coins me-1"></i> Comisiones
            </a>
            <a href="index.php?action=admin_config_promotores" class="btn btn-outline-info">
                <i class="fas fa-cogs me-1"></i> Configuración
            </a>
        </div>
    </div>

    <!-- Estadísticas del Sistema -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Promotores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($estadisticas_sistema['total_promotores']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Promotores Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($estadisticas_sistema['promotores_activos']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                Total Registros
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($estadisticas_sistema['total_registros']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                                Comisión Pendiente
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?= number_format($estadisticas_sistema['comision_pendiente']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Promotores -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Promotores
            </h6>
            <div class="d-flex gap-2">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="filtro-promotor" 
                       placeholder="Buscar promotor..."
                       style="width: 200px;">
                <select class="form-select form-select-sm" 
                        id="filtro-estado" 
                        style="width: auto;">
                    <option value="">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                    <option value="suspendido">Suspendidos</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            <?php if (empty($promotores)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No hay promotores registrados</h5>
                    <p class="text-muted">Los promotores aparecerán aquí cuando los usuarios se registren para el programa de referidos.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-promotores">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Código</th>
                                <th>Registro</th>
                                <th>Visitas</th>
                                <th>Registros</th>
                                <th>Conversión</th>
                                <th>Comisión</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promotores as $promotor): ?>
                            <tr data-promotor-id="<?= $promotor['id'] ?>" 
                                data-estado="<?= $promotor['estado'] ?>"
                                data-usuario="<?= htmlspecialchars($promotor['usuario_email'] ?? '') ?>">
                                <td>
                                    <span class="badge bg-light text-dark">
                                        #<?= $promotor['id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">
                                                ID #<?= $promotor['usuario_id'] ?>
                                            </div>
                                            <?php if (!empty($promotor['usuario_email'])): ?>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($promotor['usuario_email']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <code class="bg-light px-2 py-1 rounded">
                                        <?= htmlspecialchars($promotor['codigo']) ?>
                                    </code>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($promotor['fecha_registro'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="fw-bold text-info">
                                        <?= number_format($promotor['total_visitas'] ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        <?= number_format($promotor['total_registros'] ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $conversion = ($promotor['total_visitas'] ?? 0) > 0 ? 
                                        (($promotor['total_registros'] ?? 0) / $promotor['total_visitas']) * 100 : 0;
                                    $color_conversion = $conversion >= 10 ? 'success' : ($conversion >= 5 ? 'warning' : 'secondary');
                                    ?>
                                    <span class="badge bg-<?= $color_conversion ?>">
                                        <?= number_format($conversion, 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-primary">
                                        $<?= number_format($promotor['comision_total'] ?? 0) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $badge_class = [
                                        'activo' => 'bg-success',
                                        'inactivo' => 'bg-secondary', 
                                        'suspendido' => 'bg-danger'
                                    ];
                                    $class = $badge_class[$promotor['estado']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $class ?> estado-badge" 
                                          data-promotor-id="<?= $promotor['id'] ?>">
                                        <?= ucfirst($promotor['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="index.php?action=admin_detalle_promotor&id=<?= $promotor['id'] ?>" 
                                           class="btn btn-sm btn-outline-info"
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($promotor['estado'] === 'activo'): ?>
                                            <button class="btn btn-sm btn-outline-warning" 
                                                    onclick="cambiarEstado(<?= $promotor['id'] ?>, 'inactivo')"
                                                    title="Desactivar">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="cambiarEstado(<?= $promotor['id'] ?>, 'activo')"
                                                    title="Activar">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($promotor['estado'] !== 'suspendido'): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cambiarEstado(<?= $promotor['id'] ?>, 'suspendido')"
                                                    title="Suspender">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Filtros en tiempo real
document.getElementById('filtro-promotor').addEventListener('input', filtrarTabla);
document.getElementById('filtro-estado').addEventListener('change', filtrarTabla);

function filtrarTabla() {
    const filtroUsuario = document.getElementById('filtro-promotor').value.toLowerCase();
    const filtroEstado = document.getElementById('filtro-estado').value.toLowerCase();
    const filas = document.querySelectorAll('#tabla-promotores tbody tr');
    
    filas.forEach(fila => {
        const usuario = fila.dataset.usuario.toLowerCase();
        const estado = fila.dataset.estado.toLowerCase();
        
        const coincideUsuario = !filtroUsuario || usuario.includes(filtroUsuario);
        const coincideEstado = !filtroEstado || estado === filtroEstado;
        
        fila.style.display = (coincideUsuario && coincideEstado) ? '' : 'none';
    });
}

// Cambiar estado de promotor
function cambiarEstado(promotorId, nuevoEstado) {
    if (!confirm(`¿Confirma cambiar el estado del promotor a "${nuevoEstado}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('promotor_id', promotorId);
    formData.append('estado', nuevoEstado);
    
    fetch('index.php?action=admin_cambiar_estado_promotor', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            // Actualizar badge del estado
            const badge = document.querySelector(`[data-promotor-id="${promotorId}"].estado-badge`);
            if (badge) {
                badge.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                
                // Cambiar clase del badge
                badge.className = 'badge estado-badge';
                const clases = {
                    'activo': 'bg-success',
                    'inactivo': 'bg-secondary',
                    'suspendido': 'bg-danger'
                };
                badge.classList.add(clases[nuevoEstado] || 'bg-secondary');
                
                // Actualizar dataset
                badge.closest('tr').dataset.estado = nuevoEstado;
            }
            
            // Mostrar notificación
            mostrarNotificacion(data.mensaje, 'success');
            
            // Recargar página para actualizar botones
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarNotificacion(data.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('Error al cambiar estado', 'error');
    });
}

function mostrarNotificacion(mensaje, tipo) {
    // Implementación simple de notificación
    const div = document.createElement('div');
    div.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    div.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    div.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(div);
    
    // Auto-remover después de 3 segundos
    setTimeout(() => {
        if (div.parentNode) {
            div.parentNode.removeChild(div);
        }
    }, 3000);
}
</script>

<style>
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

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.25rem !important;
        margin-bottom: 2px;
    }
}
</style>