<?php
/**
 * Generador de Códigos QR - Endpoint Simple
 * 
 * Propósito: Generar códigos QR para links de referido de promotores
 * sin dependencias pesadas de Composer. Optimizado para hosting GoDaddy
 * con cache ligero y rate limiting básico.
 * 
 * Uso: /tools/qr.php?code=abc123&size=200
 * 
 * @author Camella Development Team - Módulo Promotores  
 * @version 1.0
 * @date 2025-10-08
 */

// Headers de cache y performance para GoDaddy
header('Content-Type: image/png');
header('Cache-Control: public, max-age=3600'); // Cache 1 hora
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// Rate limiting básico para prevenir abuso
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimit = 60; // Máximo 60 requests por minuto
$cacheDir = __DIR__ . '/cache';

// Crear directorio cache si no existe
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Verificar rate limit por IP
$rateLimitFile = $cacheDir . '/rate_' . md5($clientIP) . '.txt';
if (file_exists($rateLimitFile)) {
    $lastRequests = json_decode(file_get_contents($rateLimitFile), true) ?? [];
    $now = time();
    
    // Limpiar requests antiguos (más de 1 minuto)
    $lastRequests = array_filter($lastRequests, function($timestamp) use ($now) {
        return ($now - $timestamp) < 60;
    });
    
    // Verificar si excede el límite
    if (count($lastRequests) >= $rateLimit) {
        http_response_code(429);
        die('Rate limit exceeded');
    }
    
    // Agregar request actual
    $lastRequests[] = $now;
} else {
    $lastRequests = [time()];
}

// Guardar rate limit actualizado
file_put_contents($rateLimitFile, json_encode($lastRequests));

try {
    // Validar parámetros de entrada
    $codigo = $_GET['code'] ?? '';
    $size = min(400, max(100, (int)($_GET['size'] ?? 200))); // Entre 100-400px
    
    if (empty($codigo)) {
        throw new Exception('Código requerido');
    }
    
    // Validar formato del código
    if (!preg_match('/^[a-f0-9]{16,32}$/', $codigo)) {
        throw new Exception('Formato de código inválido');
    }
    
    // Construir URL completa del referido
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $domain = $_SERVER['HTTP_HOST'];
    $url = "$protocol://$domain/?ref=$codigo";
    
    // Verificar cache existente
    $cacheKey = md5($url . $size);
    $cacheFile = $cacheDir . '/qr_' . $cacheKey . '.png';
    
    // Si existe cache y es reciente (1 hora), servirlo directamente
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        header('X-Cache: HIT');
        readfile($cacheFile);
        exit;
    }
    
    // Generar nuevo código QR usando librería simple
    require_once __DIR__ . '/../vendor-libs/phpqrcode.php';
    
    // Configuración del QR
    $errorCorrectionLevel = 'M'; // Nivel medio de corrección
    $matrixPointSize = max(1, min(10, (int)($size / 40))); // Tamaño basado en size
    
    // Buffer de salida para capturar el PNG
    ob_start();
    
    // Generar QR code directamente a output
    QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize, 2);
    
    // Obtener contenido del buffer
    $qrData = ob_get_contents();
    ob_end_clean();
    
    if (empty($qrData)) {
        throw new Exception('Error generando código QR');
    }
    
    // Guardar en cache para requests futuros
    file_put_contents($cacheFile, $qrData);
    
    // Headers adicionales
    header('X-Cache: MISS');
    header('Content-Length: ' . strlen($qrData));
    
    // Enviar imagen
    echo $qrData;
    
} catch (Exception $e) {
    // Limpiar cualquier output anterior
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Log del error
    error_log("[QR_GENERATOR ERROR] " . $e->getMessage() . " - Code: " . ($codigo ?? 'none'));
    
    // Generar imagen de error simple
    $width = 200;
    $height = 200;
    $image = imagecreate($width, $height);
    
    // Colores
    $bg = imagecolorallocate($image, 255, 255, 255); // Blanco
    $fg = imagecolorallocate($image, 255, 0, 0);     // Rojo
    
    // Fondo blanco
    imagefill($image, 0, 0, $bg);
    
    // Texto de error
    $text = 'ERROR QR';
    $font_size = 3;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $fg);
    
    // Enviar imagen de error
    imagepng($image);
    imagedestroy($image);
}

/**
 * NOTAS DE IMPLEMENTACIÓN:
 * 
 * CACHE STRATEGY:
 * - Archivos PNG cacheados por 1 hora en /tools/cache/
 * - Cache key: MD5 de URL + tamaño
 * - Limpieza automática: implementar cron job semanal
 * 
 * RATE LIMITING:
 * - 60 requests por IP por minuto
 * - Archivos temporales en /tools/cache/rate_*
 * - Para producción: usar Redis o base de datos
 * 
 * OPTIMIZACIONES GODADDY:
 * - Tamaño máximo 400px para no sobrecargar
 * - Nivel de corrección moderado (M)
 * - Cache agresivo con headers HTTP
 * - Buffer de salida para mejor performance
 * 
 * SEGURIDAD:
 * - Validación estricta de códigos (solo hex)
 * - Rate limiting por IP
 * - No exposición de paths internos en errores
 * - Sanitización de parámetros de entrada
 * 
 * ESCALABILIDAD:
 * - Para volumen alto: implementar CDN
 * - Cache distribuido con Redis
 * - Batch generation durante off-peak hours
 * - API versioning para backward compatibility
 */
?>