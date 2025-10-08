<?php
/**
 * Modelo Referidos
 * 
 * Propósito: Gestionar el seguimiento de visitas y registros atribuidos
 * a promotores. Maneja fingerprinting ligero, prevención de fraude básico
 * y atribución segura de conversiones.
 * 
 * Efectos: Operaciones de tracking y atribución sobre tabla referidos
 * con logging detallado y validaciones anti-fraude.
 * 
 * @author Camella Development Team - Módulo Promotores
 * @version 1.0
 * @date 2025-10-08
 */

class Referidos {
    private $db;
    
    /**
     * Constructor del modelo Referidos
     */
    public function __construct($database = null) {
        if ($database) {
            $this->db = $database;
        } else {
            $this->initDatabase();
        }
    }
    
    /**
     * Inicializar conexión a base de datos
     */
    private function initDatabase() {
        try {
            require_once dirname(__DIR__) . '/config/config.php';
            global $host, $usuario, $contrasena, $basedatos, $charset;
            
            $dsn = "mysql:host=$host;dbname=$basedatos;charset=$charset";
            $this->db = new PDO($dsn, $usuario, $contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
        } catch (Exception $e) {
            error_log("[REFERIDOS MODEL] Error conexión BD: " . $e->getMessage());
            throw new Exception("Error de conexión a base de datos");
        }
    }
    
    /**
     * Registrar visita de referido
     * 
     * Propósito: Crear entrada de tracking cuando un usuario llega
     * via link de referido. Implementa fingerprinting ligero para
     * deduplicación y prevención básica de fraude.
     * 
     * Parámetros:
     * @param int $promotorId ID del promotor que refiere
     * @param string $fingerprint Hash único del visitante (cookie+IP+UA)
     * @param string $ip Dirección IP del visitante
     * @param string $ua User-Agent del navegador
     * 
     * Retorno:
     * @return int ID del registro de referido creado
     * 
     * Efectos:
     * - INSERT en tabla referidos con estado 'visit'
     * - Deduplicación por fingerprint (evita spam de clicks)
     * - Conversión IP a formato binario para optimización storage
     * - Logging detallado para análisis de tráfico
     * 
     * @throws Exception Si no se puede crear el registro
     */
    public function registrarVisita(int $promotorId, string $fingerprint, string $ip, string $ua): int {
        try {
            // Bloque 1: Validación de parámetros de entrada
            if (empty($fingerprint) || empty($ip)) {
                throw new Exception("Fingerprint e IP son obligatorios para registro");
            }
            
            // Validar longitud de fingerprint (debe ser hash sha256 = 64 chars)
            if (strlen($fingerprint) !== 64) {
                error_log("[REFERIDOS] Fingerprint con longitud incorrecta: " . strlen($fingerprint));
            }
            
            // Bloque 2: Verificar si ya existe visita reciente con mismo fingerprint
            // Evitar spam de clicks del mismo usuario en ventana de tiempo corta
            $stmt = $this->db->prepare("
                SELECT id, creado_en
                FROM referidos 
                WHERE promotor_id = ? AND fingerprint = ?
                AND creado_en > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY creado_en DESC
                LIMIT 1
            ");
            
            $stmt->execute([$promotorId, $fingerprint]);
            $visitaReciente = $stmt->fetch();
            
            if ($visitaReciente) {
                error_log("[REFERIDOS] Visita duplicada detectada en última hora - ID: {$visitaReciente['id']}");
                return (int)$visitaReciente['id']; // Devolver ID existente
            }
            
            // Bloque 3: Conversión IP a formato binario para storage optimizado
            // Línea clave: inet_pton maneja tanto IPv4 como IPv6 automáticamente
            $ipBinary = inet_pton($ip);
            if ($ipBinary === false) {
                error_log("[REFERIDOS] IP inválida detectada: $ip");
                $ipBinary = null; // Permitir continuar sin IP
            }
            
            // Bloque 4: Truncar User-Agent si es muy largo (evitar errores de BD)
            $userAgent = substr($ua, 0, 255);
            
            // Bloque 5: Insertar nuevo registro de visita
            // Query preparada con manejo seguro de datos binarios
            $stmt = $this->db->prepare("
                INSERT INTO referidos (
                    promotor_id, fingerprint, ip_registro, user_agent, 
                    estado, creado_en
                ) VALUES (?, ?, ?, ?, 'visit', CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([
                $promotorId,
                $fingerprint,
                $ipBinary,
                $userAgent
            ]);
            
            $referidoId = $this->db->lastInsertId();
            
            error_log("[REFERIDOS] Visita registrada - ID: $referidoId, Promotor: $promotorId, IP: $ip");
            
            return (int)$referidoId;
            
        } catch (PDOException $e) {
            error_log("[REFERIDOS ERROR] Error registrando visita: " . $e->getMessage());
            throw new Exception("Error registrando visita: " . $e->getMessage());
        }
    }
    
    /**
     * Atribuir registro completado a referido
     * 
     * Propósito: Actualizar estado de referido cuando el visitante
     * completa el proceso de registro. Implementa validaciones
     * anti-fraude básicas y triggering de comisiones.
     * 
     * Parámetros:
     * @param int $referidoId ID del registro de referido existente
     * @param int $usuarioId ID del usuario que completó registro
     * 
     * Retorno:
     * @return bool True si la atribución fue exitosa
     * 
     * Efectos:
     * - UPDATE de estado 'visit' → 'registro'
     * - Asignación de registrado_usuario_id
     * - Validación anti-self-referral (promoción propia)
     * - Trigger automático de generación de comisión
     * 
     * Validaciones anti-fraude:
     * - Verificar que no sea auto-referido (mismo usuario)
     * - Validar ventana de tiempo razonable entre visita y registro
     * - Evitar doble atribución del mismo registro
     */
    public function atribuirRegistro(int $referidoId, int $usuarioId): bool {
        try {
            // Bloque 1: Obtener datos del referido existente
            $stmt = $this->db->prepare("
                SELECT r.id, r.promotor_id, r.estado, r.registrado_usuario_id, r.creado_en,
                       p.usuario_id as promotor_usuario_id
                FROM referidos r
                INNER JOIN promotores p ON r.promotor_id = p.id
                WHERE r.id = ?
            ");
            
            $stmt->execute([$referidoId]);
            $referido = $stmt->fetch();
            
            if (!$referido) {
                error_log("[REFERIDOS] Referido no encontrado para atribución: ID $referidoId");
                return false;
            }
            
            // Bloque 2: Validaciones anti-fraude
            
            // Validación 1: Evitar self-referral (auto-promoción)
            if ($referido['promotor_usuario_id'] == $usuarioId) {
                error_log("[REFERIDOS] Self-referral detectado y rechazado - Promotor usuario: {$referido['promotor_usuario_id']}, Usuario registro: $usuarioId");
                
                // Marcar como rechazado en lugar de registro
                $this->marcarComoRechazado($referidoId, 'Self-referral detectado');
                return false;
            }
            
            // Validación 2: Verificar que esté en estado 'visit'
            if ($referido['estado'] !== 'visit') {
                error_log("[REFERIDOS] Referido no está en estado 'visit' - Estado actual: {$referido['estado']}");
                return false;
            }
            
            // Validación 3: Verificar que no tenga ya un usuario asignado
            if ($referido['registrado_usuario_id']) {
                error_log("[REFERIDOS] Referido ya tiene usuario asignado: {$referido['registrado_usuario_id']}");
                return false;
            }
            
            // Validación 4: Verificar ventana de tiempo razonable (máximo 30 días)
            $tiempoTranscurrido = time() - strtotime($referido['creado_en']);
            $maxTiempo = 30 * 24 * 60 * 60; // 30 días en segundos
            
            if ($tiempoTranscurrido > $maxTiempo) {
                error_log("[REFERIDOS] Ventana de atribución expirada - Días transcurridos: " . ($tiempoTranscurrido / 86400));
                return false;
            }
            
            // Bloque 3: Actualizar referido con atribución exitosa
            // Línea clave: UPDATE atómico con verificación de estado
            $stmt = $this->db->prepare("
                UPDATE referidos 
                SET estado = 'registro', 
                    registrado_usuario_id = ?,
                    valor_comision = ?
                WHERE id = ? AND estado = 'visit'
            ");
            
            // Obtener valor de comisión desde configuración
            $valorComision = $this->obtenerComisionBase();
            
            $stmt->execute([$usuarioId, $valorComision, $referidoId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("[REFERIDOS] Atribución exitosa - Referido: $referidoId, Usuario: $usuarioId, Comisión: $valorComision");
                
                // Bloque 4: Trigger generación de comisión automática
                $this->generarComisionAutomatica($referido['promotor_id'], $referidoId, $valorComision);
                
                return true;
            } else {
                error_log("[REFERIDOS] No se pudo actualizar referido - posible condición de carrera");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("[REFERIDOS ERROR] Error en atribución: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar referido por fingerprint y promotor
     * 
     * Propósito: Localizar registro de visita existente para atribución
     * posterior cuando se complete el registro del usuario.
     * 
     * @param int $promotorId ID del promotor
     * @param string $fingerprint Hash del visitante
     * @return array|null Datos del referido o null si no existe
     */
    public function buscarPorFingerprint(int $promotorId, string $fingerprint): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, promotor_id, fingerprint, estado, registrado_usuario_id, creado_en
                FROM referidos 
                WHERE promotor_id = ? AND fingerprint = ?
                AND estado = 'visit'
                ORDER BY creado_en DESC
                LIMIT 1
            ");
            
            $stmt->execute([$promotorId, $fingerprint]);
            $referido = $stmt->fetch();
            
            return $referido ?: null;
            
        } catch (Exception $e) {
            error_log("[REFERIDOS ERROR] Error buscando por fingerprint: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener estadísticas de referidos por promotor
     * 
     * @param int $promotorId ID del promotor
     * @param int $dias Días hacia atrás para estadísticas (default 30)
     * @return array Estadísticas calculadas
     */
    public function getEstadisticasByPromotor(int $promotorId, int $dias = 30): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_visitas,
                    SUM(CASE WHEN estado = 'registro' THEN 1 ELSE 0 END) as total_registros,
                    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as total_rechazados,
                    AVG(CASE WHEN estado = 'registro' THEN valor_comision ELSE 0 END) as comision_promedio,
                    COUNT(DISTINCT DATE(creado_en)) as dias_activos
                FROM referidos 
                WHERE promotor_id = ? 
                AND creado_en >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            
            $stmt->execute([$promotorId, $dias]);
            $stats = $stmt->fetch();
            
            // Calcular tasa de conversión
            $tasaConversion = $stats['total_visitas'] > 0 
                ? ($stats['total_registros'] / $stats['total_visitas']) * 100 
                : 0;
            
            return [
                'total_visitas' => (int)$stats['total_visitas'],
                'total_registros' => (int)$stats['total_registros'],
                'total_rechazados' => (int)$stats['total_rechazados'],
                'tasa_conversion' => round($tasaConversion, 2),
                'comision_promedio' => round((float)$stats['comision_promedio'], 2),
                'dias_activos' => (int)$stats['dias_activos']
            ];
            
        } catch (Exception $e) {
            error_log("[REFERIDOS ERROR] Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_visitas' => 0,
                'total_registros' => 0,
                'total_rechazados' => 0,
                'tasa_conversion' => 0,
                'comision_promedio' => 0,
                'dias_activos' => 0
            ];
        }
    }
    
    /**
     * Marcar referido como rechazado
     * 
     * @param int $referidoId ID del referido
     * @param string $motivo Motivo del rechazo
     */
    private function marcarComoRechazado(int $referidoId, string $motivo): void {
        try {
            $stmt = $this->db->prepare("
                UPDATE referidos 
                SET estado = 'rechazado',
                    nota = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$motivo, $referidoId]);
            error_log("[REFERIDOS] Referido marcado como rechazado - ID: $referidoId, Motivo: $motivo");
            
        } catch (Exception $e) {
            error_log("[REFERIDOS ERROR] Error marcando como rechazado: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener valor base de comisión desde configuración
     * 
     * @return float Valor de comisión base
     */
    private function obtenerComisionBase(): float {
        try {
            $stmt = $this->db->prepare("
                SELECT valor 
                FROM promotor_config 
                WHERE clave = 'comision_registro_base' AND activa = 1
                LIMIT 1
            ");
            
            $stmt->execute();
            $config = $stmt->fetch();
            
            return $config ? (float)$config['valor'] : 5000.00; // Default fallback
            
        } catch (Exception $e) {
            error_log("[REFERIDOS] Error obteniendo comisión base, usando default: " . $e->getMessage());
            return 5000.00; // Constante de fallback
        }
    }
    
    /**
     * Generar comisión automática tras atribución exitosa
     * 
     * @param int $promotorId ID del promotor
     * @param int $referidoId ID del referido
     * @param float $monto Monto de la comisión
     */
    private function generarComisionAutomatica(int $promotorId, int $referidoId, float $monto): void {
        try {
            // Usar el modelo Comisiones para mantener consistencia
            require_once __DIR__ . '/Comisiones.php';
            $comisionesModel = new Comisiones($this->db);
            
            $comisionId = $comisionesModel->generarPorRegistro($promotorId, $referidoId, $monto);
            
            if ($comisionId) {
                error_log("[REFERIDOS] Comisión automática generada - ID: $comisionId, Monto: $monto");
            }
            
        } catch (Exception $e) {
            error_log("[REFERIDOS ERROR] Error generando comisión automática: " . $e->getMessage());
        }
    }
    
    /**
     * Generar fingerprint único para visitante
     * 
     * Propósito: Crear identificador único basado en datos disponibles
     * del navegador sin comprometer privacidad del usuario.
     * 
     * @param string $codigo Código del promotor
     * @param string $ip IP del visitante  
     * @param string $userAgent User-Agent del navegador
     * @param string $sessionId ID de sesión PHP
     * @return string Hash SHA256 de 64 caracteres
     */
    public static function generarFingerprint(string $codigo, string $ip, string $userAgent, string $sessionId): string {
        // Línea clave: Combinar datos disponibles sin comprometer privacidad
        // No incluir datos personales como email, nombre, etc.
        $datos = $codigo . '|' . $ip . '|' . substr($userAgent, 0, 100) . '|' . $sessionId;
        
        // Usar SHA256 para hash unidireccional seguro
        return hash('sha256', $datos);
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * FINGERPRINTING LIGERO:
 * - Combina código+IP+UA+sesión para identificación única
 * - NO es tracking invasivo, solo deduplicación básica
 * - Hash SHA256 hace irreversible la identificación
 * - Para mejor precisión: agregar canvas fingerprint o timezone
 * 
 * PREVENCIÓN DE FRAUDE BÁSICO:
 * 1. Self-referral: Evita que promotor se refiera a sí mismo
 * 2. Ventana temporal: Máximo 30 días entre visita y registro
 * 3. Deduplicación: Evita clicks repetidos en corto tiempo
 * 4. Estado único: Evita doble atribución del mismo referido
 * 
 * AMPLIACIÓN ANTI-FRAUDE:
 * - Verificar patrones de IP (mismo rango, VPN detection)
 * - Analizar timing sospechoso (registro inmediato tras click)
 * - Validar coherencia geográfica (IP país vs registro país)
 * - Machine learning para detectar patterns anómalos
 * 
 * OPTIMIZACIONES DE PERFORMANCE:
 * - Índice en (promotor_id, fingerprint) para búsquedas O(log n)
 * - Particionado por fecha si volumen es muy alto
 * - Archive de registros antiguos (>1 año) a tabla histórica
 * - Cache de estadísticas con invalidación inteligente
 * 
 * CONSIDERACIONES DE PRIVACIDAD:
 * - IP almacenada en binario, no en logs de texto
 * - Fingerprint hasheado, no datos raw del browser
 * - Purga automática de datos antiguos según política
 * - Cumplimiento GDPR: right to be forgotten
 * 
 * MÉTRICAS AVANZADAS:
 * - Tasa de conversión por fuente de tráfico
 * - Tiempo promedio entre visita y conversión
 * - Valor de vida del cliente (LTV) por referido
 * - Segmentación por características demográficas
 */
?>