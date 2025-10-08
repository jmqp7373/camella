<?php
/**
 * Controlador de Promotores
 * 
 * Propósito: Gestionar funcionalidades específicas del rol promotor,
 * incluyendo panel personal, generación de links de referido, códigos QR
 * y tracking de visitas. Integrado con sistema de autenticación existente.
 * 
 * @author Camella Development Team - Módulo Promotores
 * @version 1.0
 * @date 2025-10-08
 */
class PromotorController {
    private $promotorModel;
    private $referidosModel;
    private $comisionesModel;
    
    public function __construct() {
        // Cargar modelos necesarios
        require_once dirname(__DIR__) . '/models/Promotor.php';
        require_once dirname(__DIR__) . '/models/Referidos.php';
        require_once dirname(__DIR__) . '/models/Comisiones.php';
        
        $this->promotorModel = new Promotor();
        $this->referidosModel = new Referidos();
        $this->comisionesModel = new Comisiones();
    }
    
    /**
     * Panel principal del promotor
     * 
     * Propósito: Mostrar dashboard personalizado con estadísticas,
     * link de referido, código QR y historial de comisiones.
     * Funciona como centro de control para las actividades del promotor.
     * 
     * Flujo de ejecución:
     * 1. Verificar acceso de roles admin/promotor
     * 2. Obtener/crear código único del promotor
     * 3. Construir link de referido seguro
     * 4. Calcular métricas y estadísticas
     * 5. Renderizar vista del panel con todos los datos
     * 
     * Efectos:
     * - Queries a BD para estadísticas
     * - Generación de código si no existe
     * - Logging de acceso al panel
     * - Renderizado de vista no intrusiva
     */
    public function panel() {
        try {
            // Bloque 1: Verificación de acceso y permisos
            // Solo usuarios admin o promotor pueden acceder al panel
            $this->verificarAcceso(['admin', 'promotor']);
            
            $usuarioId = $_SESSION['usuario']['id'];
            $usuarioRol = $_SESSION['usuario']['rol'];
            
            // Bloque 2: Obtener/crear datos del promotor
            // findOrCreateByUsuarioId es idempotente - seguro llamar múltiples veces
            $promotor = $this->promotorModel->findOrCreateByUsuarioId($usuarioId);
            
            if (!$promotor) {
                throw new Exception("Error obteniendo datos del promotor");
            }
            
            // Bloque 3: Construir link de referido seguro
            // Usar HTTPS en producción y protocolo apropiado
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $domain = $_SERVER['HTTP_HOST'];
            
            // Línea clave: Link de referido con código único
            $linkReferido = "$protocol://$domain/?ref=" . $promotor['codigo'];
            
            // Bloque 4: Calcular métricas del promotor
            $estadisticas = $this->promotorModel->getEstadisticas($promotor['id']);
            $estadisticasReferidos = $this->referidosModel->getEstadisticasByPromotor($promotor['id']);
            $estadisticasComisiones = $this->comisionesModel->getEstadisticasByPromotor($promotor['id']);
            
            // Combinar todas las estadísticas para la vista
            $metricas = array_merge($estadisticas, [
                'tasa_conversion' => $estadisticasReferidos['tasa_conversion'],
                'comision_promedio' => $estadisticasReferidos['comision_promedio'],
                'total_pendiente_monto' => $estadisticasComisiones['total_pendiente'],
                'total_ganado_monto' => $estadisticasComisiones['total_ganado']
            ]);
            
            // Bloque 5: Obtener historial reciente de comisiones
            $historialComisiones = $this->comisionesModel->listarPorPromotor($promotor['id'], null, 10);
            
            // Bloque 6: Preparar datos para la vista
            $datosVista = [
                'promotor' => $promotor,
                'usuario' => $_SESSION['usuario'],
                'link_referido' => $linkReferido,
                'codigo_qr_url' => "/tools/qr.php?code=" . $promotor['codigo'],
                'metricas' => $metricas,
                'historial_comisiones' => $historialComisiones,
                'es_admin' => $usuarioRol === 'admin'
            ];
            
            // Logging de acceso al panel para analytics
            error_log("[PROMOTOR_PANEL] Acceso - Usuario: $usuarioId, Código: {$promotor['codigo']}, Visitas: {$metricas['visitas_totales']}");
            
            // Renderizar vista del panel
            $pageTitle = "Panel de Promotor";
            extract($datosVista);
            include dirname(__DIR__) . '/views/promotor/panel.php';
            
        } catch (Exception $e) {
            error_log("[PROMOTOR_PANEL ERROR] " . $e->getMessage());
            
            // Mostrar error sin romper la maquetación
            $errorMessage = "Error cargando panel del promotor: " . $e->getMessage();
            $pageTitle = "Error - Panel Promotor";
            include dirname(__DIR__) . '/views/error/generic.php';
        }
    }
    
    /**
     * Mantener compatibilidad con método index existente
     */
    public function index() {
        $this->panel();
    }
    
    /**
     * Rastrear visita de referido
     * 
     * Propósito: Procesar llegadas via link de referido, crear cookie
     * de tracking y registrar visita en BD. Implementa fingerprinting
     * ligero y prevención básica de fraude.
     * 
     * Flujo anti-fraude mínimo:
     * - Self-referral: Si usuario ya está logueado y es el mismo promotor, rechazar
     * - Rate limiting: Máximo 1 registro por IP+UA en 1 hora
     * - Cookie expiry: 7 días de validez para atribución
     * - Fingerprinting: Hash de código+IP+UA+sesión para deduplicación
     * 
     * Efectos:
     * - Setea cookie 'ref_code' con 7 días de expiración
     * - INSERT en tabla referidos con estado 'visit'
     * - Redirección a página principal u objetivo
     * - Logging detallado para análisis de tráfico
     * 
     * @param string|null $codigo Código del promotor (desde URL ?ref=)
     */
    public function rastrearVisita(?string $codigo = null) {
        try {
            // Obtener código desde parámetro URL si no se pasa directamente
            $codigo = $codigo ?? ($_GET['ref'] ?? '');
            
            if (empty($codigo)) {
                // No hay código, redireccionar normalmente sin tracking
                $this->redirigirSinTracking();
                return;
            }
            
            // Validar formato del código
            if (!preg_match('/^[a-f0-9]{16,32}$/', $codigo)) {
                error_log("[PROMOTOR_TRACKING] Código inválido rechazado: $codigo");
                $this->redirigirSinTracking();
                return;
            }
            
            // Buscar promotor por código
            $promotor = $this->promotorModel->getByCodigo($codigo);
            
            if (!$promotor) {
                error_log("[PROMOTOR_TRACKING] Código no encontrado: $codigo");
                $this->redirigirSinTracking();
                return;
            }
            
            // Bloque de prevención de self-referral
            if (isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $promotor['usuario_id']) {
                error_log("[PROMOTOR_TRACKING] Self-referral detectado - Usuario: {$_SESSION['usuario']['id']}, Promotor: {$promotor['usuario_id']}");
                $this->redirigirSinTracking('Ya eres promotor, no puedes usar tu propio link');
                return;
            }
            
            // Obtener datos del visitante para fingerprinting
            $ip = $this->obtenerIPReal();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $sessionId = session_id();
            
            // Generar fingerprint ligero para deduplicación
            $fingerprint = Referidos::generarFingerprint($codigo, $ip, $userAgent, $sessionId);
            
            // Registrar visita en BD (maneja deduplicación internamente)
            $referidoId = $this->referidosModel->registrarVisita(
                (int)$promotor['id'],
                $fingerprint,
                $ip,
                $userAgent
            );
            
            // Configurar cookie de tracking (7 días de validez)
            $cookieExpiry = time() + (7 * 24 * 60 * 60); // 7 días
            $cookieDomain = $_SERVER['HTTP_HOST'] === 'localhost' ? '' : $_SERVER['HTTP_HOST'];
            
            setcookie('ref_code', $codigo, [
                'expires' => $cookieExpiry,
                'path' => '/',
                'domain' => $cookieDomain,
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            // Logging de tracking exitoso
            error_log("[PROMOTOR_TRACKING] Visita registrada - Código: $codigo, Referido ID: $referidoId, IP: $ip");
            
            // Redirigir a página objetivo (home por defecto)
            $paginaObjetivo = $_GET['dest'] ?? '/';
            $this->redirigirSinTracking(null, $paginaObjetivo);
            
        } catch (Exception $e) {
            error_log("[PROMOTOR_TRACKING ERROR] " . $e->getMessage());
            $this->redirigirSinTracking();
        }
    }
    
    /**
     * Hook para atribución en registro de usuario
     * 
     * Propósito: Llamar desde el controlador de registro cuando se complete
     * exitosamente un nuevo registro. Busca cookie de referido y atribuye
     * la conversión al promotor correspondiente.
     * 
     * Líneas clave para evitar doble conteo:
     * - Verificar que cookie existe y es válida
     * - Buscar referido en estado 'visit' únicamente
     * - Actualizar estado a 'registro' atómicamente
     * - Generar comisión solo si atribución fue exitosa
     * - Limpiar cookie después del procesamiento
     * 
     * @param int $usuarioId ID del usuario recién registrado
     * @return bool True si se atribuyó exitosamente
     */
    public function atribuirRegistro(int $usuarioId): bool {
        try {
            // Verificar si hay cookie de referido válida
            $codigoReferido = $_COOKIE['ref_code'] ?? '';
            
            if (empty($codigoReferido)) {
                error_log("[PROMOTOR_ATRIBUCION] No hay cookie de referido para usuario $usuarioId");
                return false;
            }
            
            // Buscar promotor por código
            $promotor = $this->promotorModel->getByCodigo($codigoReferido);
            
            if (!$promotor) {
                error_log("[PROMOTOR_ATRIBUCION] Código de referido inválido: $codigoReferido");
                $this->limpiarCookieReferido();
                return false;
            }
            
            // Prevención adicional de self-referral al momento del registro
            if ($usuarioId == $promotor['usuario_id']) {
                error_log("[PROMOTOR_ATRIBUCION] Self-referral en registro - Usuario: $usuarioId");
                $this->limpiarCookieReferido();
                return false;
            }
            
            // Buscar visita pendiente de atribución usando fingerprint actual
            $ip = $this->obtenerIPReal();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $sessionId = session_id();
            $fingerprint = Referidos::generarFingerprint($codigoReferido, $ip, $userAgent, $sessionId);
            
            $referidoPendiente = $this->referidosModel->buscarPorFingerprint(
                (int)$promotor['id'],
                $fingerprint
            );
            
            if (!$referidoPendiente) {
                error_log("[PROMOTOR_ATRIBUCION] No se encontró visita pendiente para fingerprint");
                $this->limpiarCookieReferido();
                return false;
            }
            
            // Línea clave: Atribuir registro de forma atómica
            $atribucionExitosa = $this->referidosModel->atribuirRegistro(
                (int)$referidoPendiente['id'],
                $usuarioId
            );
            
            if ($atribucionExitosa) {
                error_log("[PROMOTOR_ATRIBUCION] Atribución exitosa - Referido: {$referidoPendiente['id']}, Usuario: $usuarioId, Promotor: {$promotor['codigo']}");
                
                // Limpiar cookie después de atribución exitosa para evitar reutilización
                $this->limpiarCookieReferido();
                
                return true;
            } else {
                error_log("[PROMOTOR_ATRIBUCION] Falló la atribución del referido {$referidoPendiente['id']}");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[PROMOTOR_ATRIBUCION ERROR] " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar acceso por roles
     * 
     * @param array $rolesPermitidos Lista de roles que pueden acceder
     */
    private function verificarAcceso(array $rolesPermitidos) {
        // Usar AuthHelper existente del proyecto
        $authHelper = new AuthHelper();
        
        if (!$authHelper->estaAutenticado()) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $tieneAcceso = false;
        foreach ($rolesPermitidos as $rol) {
            if ($authHelper->verificarAcceso($rol)) {
                $tieneAcceso = true;
                break;
            }
        }
        
        if (!$tieneAcceso) {
            $usuario = $authHelper->obtenerUsuarioActual();
            error_log("Acceso denegado al panel promotor - Usuario: {$usuario['email']}, Rol: {$usuario['rol']}");
            header('Location: /?error=' . urlencode('No tiene permisos para acceder a esta sección'));
            exit;
        }
    }
    
    /**
     * Obtener IP real del visitante (considerando proxies/CDN)
     * 
     * @return string IP del visitante
     */
    private function obtenerIPReal(): string {
        // Verificar headers comunes de proxies y CDNs
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                // Validar que sea una IP válida y no privada/reservada
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback a REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Redireccionar sin tracking
     * 
     * @param string|null $mensaje Mensaje de error opcional
     * @param string $destino URL de destino
     */
    private function redirigirSinTracking(?string $mensaje = null, string $destino = '/') {
        if ($mensaje) {
            $destino .= (strpos($destino, '?') !== false ? '&' : '?') . 'msg=' . urlencode($mensaje);
        }
        
        header("Location: $destino");
        exit;
    }
    
    /**
     * Limpiar cookie de referido
     */
    private function limpiarCookieReferido() {
        $cookieDomain = $_SERVER['HTTP_HOST'] === 'localhost' ? '' : $_SERVER['HTTP_HOST'];
        
        setcookie('ref_code', '', [
            'expires' => time() - 3600, // Expirar en el pasado
            'path' => '/',
            'domain' => $cookieDomain,
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * INTEGRACIÓN CON REGISTRO DE USUARIOS:
 * 
 * En el controlador que procesa registro (ej. LoginController::registro()):
 * 
 * // Después de INSERT exitoso del usuario
 * if ($registroExitoso) {
 *     require_once 'controllers/PromotorController.php';
 *     $promotorController = new PromotorController();
 *     $promotorController->atribuirRegistro($nuevoUsuarioId);
 * }
 * 
 * PREVENCIÓN DE FRAUDE AMPLIADA:
 * 
 * 1. VALIDACIÓN DE EMAIL:
 *    - Verificar que el email del registrado no sea similar al del promotor
 *    - Blacklist de dominios temporales (10minutemail, etc.)
 * 
 * 2. ANÁLISIS DE PATRONES:
 *    - Múltiples registros desde misma IP en poco tiempo
 *    - User-Agent identical entre promotor y referidos
 *    - Nombres/datos demasiado similares
 * 
 * 3. RATE LIMITING:
 *    - Máximo X referidos por promotor por día
 *    - Máximo Y registros por IP por hora
 *    - Cooldown period entre registros del mismo promotor
 * 
 * OPTIMIZACIÓN DE PERFORMANCE:
 * 
 * 1. CACHE DE CÓDIGOS:
 *    - Redis/Memcached para códigos válidos (evitar DB queries)
 *    - TTL de 1 hora, invalidar al cambiar estado del promotor
 * 
 * 2. BATCH PROCESSING:
 *    - Procesar comisiones en lotes nocturnos
 *    - Queue para atribuciones pesadas
 *    - Estadísticas precalculadas con TTL
 * 
 * TRACKING AVANZADO:
 * 
 * 1. FUENTE DE TRÁFICO:
 *    - UTM parameters para identificar canal (social, email, etc.)
 *    - Referrer header para saber de dónde viene
 *    - Device detection (mobile, desktop, tablet)
 * 
 * 2. GEOLOCALIZACIÓN:
 *    - GeoIP para obtener país/ciudad del visitante
 *    - Timezone detection via JavaScript
 *    - Language preference del browser
 * 
 * AJUSTAR LÓGICA CUANDO EXISTA FLUJO COMPLETO:
 * 
 * 1. MÚLTIPLES STEPS DE REGISTRO:
 *    - Atribuir solo cuando se complete TODO el proceso
 *    - Trackear abandono en cada step
 *    - Comisión diferenciada por completitud del perfil
 * 
 * 2. VALIDACIÓN DE CALIDAD:
 *    - Verificar email válido antes de pagar comisión
 *    - Actividad mínima del usuario (login, completar perfil)
 *    - Retention period antes del pago final
 * 
 * HOSTING GODADDY CONSIDERATIONS:
 * 
 * - Evitar librerías pesadas (usar QR simple en lugar de composer)
 * - Optimizar queries para shared hosting limitations
 * - Usar file-based cache si no hay Redis disponible
 * - Considerar rate limits del servidor compartido
 */
?>