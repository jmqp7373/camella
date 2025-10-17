<?php
/**
 * Controlador: Role Switcher (Impersonación de Roles)
 * Solo para administradores
 */

session_start();

// Verificar que sea administrador real
if (!isset($_SESSION['role']) || $_SESSION['original_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'error' => 'Acceso denegado. Solo administradores pueden cambiar de rol.'
    ]);
    exit;
}

// Obtener rol solicitado
$requestedRole = $_POST['role'] ?? '';

// Roles permitidos
$allowedRoles = ['admin', 'promotor', 'publicante'];

if (!in_array($requestedRole, $allowedRoles, true)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Rol no válido'
    ]);
    exit;
}

// Guardar el rol impersonado en sesión
$_SESSION['impersonate_role'] = $requestedRole;
$_SESSION['role'] = $requestedRole; // También actualizar role para compatibilidad

// Log del cambio (opcional)
error_log("[ROLE_SWITCHER] Admin cambió a rol: {$requestedRole}");

// Respuesta exitosa
header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'role' => $requestedRole,
    'message' => "Visualizando como: {$requestedRole}"
]);
