<?php
/**
 * Controlador: Cambio de Rol (Solo para Administradores)
 * Permite al admin simular otros roles temporalmente
 */

session_start();

// Verificar que sea un administrador real
if (!isset($_SESSION['usuario']) || $_SESSION['original_role'] !== 'admin') {
    // Si no tiene original_role, verificar que sea admin actual
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Acceso denegado. Solo administradores pueden cambiar de rol.'
        ]);
        exit;
    }
    
    // Guardar el rol original la primera vez
    $_SESSION['original_role'] = 'admin';
}

// Obtener el rol solicitado
$nuevoRol = isset($_POST['role']) ? $_POST['role'] : '';

// Validar roles permitidos
$rolesPermitidos = ['admin', 'promotor', 'publicante'];

if (!in_array($nuevoRol, $rolesPermitidos)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Rol no válido'
    ]);
    exit;
}

// Cambiar el rol temporalmente
$rolAnterior = $_SESSION['role'];
$_SESSION['role'] = $nuevoRol;

// Log del cambio (opcional)
error_log("Admin cambió de rol: {$rolAnterior} → {$nuevoRol}");

// Determinar URL de redirección
$dashboardUrl = match($nuevoRol) {
    'admin' => '../../views/admin/dashboard.php',
    'promotor' => '../../views/promotor/dashboard.php',
    'publicante' => '../../views/publicante/dashboard.php',
    default => '../../index.php'
};

// Respuesta exitosa
echo json_encode([
    'success' => true,
    'message' => "Rol cambiado a: {$nuevoRol}",
    'role' => $nuevoRol,
    'redirectUrl' => $dashboardUrl
]);
