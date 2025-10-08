<?php
/**
 * tools/peek_log.php — Visor temporal para leer las últimas líneas del error_log.
 * 
 * PROPÓSITO:
 * - Diagnóstico rápido en producción sin exponer detalles visuales.
 * - Inspeccionar errores de PHP/Apache en hosting GoDaddy.
 * - Herramienta temporal para debugging del sistema de referidos.
 * 
 * SEGURIDAD:
 * - Archivo temporal; ELIMINAR al finalizar el diagnóstico.
 * - No contiene autenticación; solo para uso interno inmediato.
 * - Salida en texto plano sin información sensible del sistema.
 * 
 * EFECTOS:
 * - Muestra solo texto plano, últimas 200 líneas si existe error_log.
 * - No modifica archivos ni base de datos.
 * - No afecta la funcionalidad del sitio web.
 * 
 * NOTAS DEV NOVATO:
 * - No incluir en vistas; acceder directo por URL.
 * - Usar para verificar errores del tracking de referidos.
 * - Eliminar tras completar diagnóstico de producción.
 * - En GoDaddy, error_log suele estar en la raíz del sitio.
 * 
 * USO:
 * - Acceder vía: https://camella.com.co/tools/peek_log.php
 * - Revisar errores recientes del sistema.
 * - Eliminar archivo cuando ya no se necesite.
 */

declare(strict_types=1);

// LÍNEA CLAVE: Establecer salida como texto plano para fácil lectura
header('Content-Type: text/plain; charset=utf-8');

// LÍNEA CLAVE: Ruta habitual del error_log en GoDaddy (raíz del sitio)
$log = __DIR__ . '/../error_log';

// Verificar existencia del archivo de log
if (!file_exists($log)) {
    exit("No se encontró error_log en la ruta esperada.\nRuta verificada: {$log}\n");
}

// LÍNEA CLAVE: Intentar leer archivo de log con manejo de errores
$lines = @file($log);
if (!$lines) {
    exit("No se pudo leer error_log. Verificar permisos de archivo.\n");
}

// Información del archivo para contexto
$fileSize = filesize($log);
$totalLines = count($lines);
$showLines = min(200, $totalLines);

echo "=== VISOR TEMPORAL ERROR_LOG CAMELLA.COM.CO ===\n";
echo "Archivo: {$log}\n";
echo "Tamaño: " . number_format($fileSize) . " bytes\n";
echo "Total líneas: {$totalLines}\n";
echo "Mostrando: últimas {$showLines} líneas\n";
echo "Timestamp: " . date('Y-m-d H:i:s T') . "\n\n";
echo "=== INICIO LOG ===\n";

// LÍNEA CLAVE: Mostrar solo las últimas 200 líneas para evitar salida excesiva
echo implode('', array_slice($lines, -200));

echo "\n=== FIN LOG ===\n";
echo "\nRECORDATORIO: Eliminar este archivo tras completar diagnóstico.\n";

/**
 * NOTAS PARA MANTENIMIENTO:
 * 
 * ELIMINACIÓN POST-USO:
 * - git rm -f tools/peek_log.php
 * - git commit -m "chore: remove temporary log viewer"
 * - git push
 * 
 * TROUBLESHOOTING:
 * - Si no aparece error_log: verificar configuración PHP en GoDaddy
 * - Si error de permisos: contactar soporte hosting
 * - Para logs custom: modificar variable $log con ruta específica
 * 
 * ALTERNATIVAS:
 * - tail -f error_log (si tienes acceso SSH)
 * - Panel de control GoDaddy > Error Logs
 * - Logs personalizados en /logs/ o /tmp/
 */
?>