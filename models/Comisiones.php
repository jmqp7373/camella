<?php
/**
 * Modelo Comisiones
 * 
 * Propósito: Gestionar el sistema de comisiones para promotores,
 * incluyendo generación automática, estados de pago y administración
 * por parte de los administradores del sistema.
 * 
 * Efectos: Operaciones CRUD sobre tabla comisiones con workflows
 * de aprobación, control de estados y cálculo de montos.
 * 
 * @author Camella Development Team - Módulo Promotores
 * @version 1.0
 * @date 2025-10-08
 */

class Comisiones {
    private $db;
    
    /**
     * Constructor del modelo Comisiones
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
            error_log("[COMISIONES MODEL] Error conexión BD: " . $e->getMessage());
            throw new Exception("Error de conexión a base de datos");
        }
    }
    
    /**
     * Generar comisión por registro completado
     * 
     * Propósito: Crear entrada de comisión automáticamente cuando
     * se atribuye un registro exitoso a un promotor. Esta es la
     * función principal del sistema de incentivos.
     * 
     * Parámetros:
     * @param int $promotorId ID del promotor que recibe la comisión
     * @param int $referidoId ID del referido que generó la comisión
     * @param float $montoBase Monto base de comisión (desde config)
     * 
     * Retorno:
     * @return int ID de la comisión generada
     * 
     * Efectos:
     * - INSERT en tabla comisiones con estado 'pendiente'
     * - Logging detallado para auditoría financiera
     * - Validación de montos y referencias
     * - Prevención de duplicación de comisiones
     * 
     * @throws Exception Si no se puede generar la comisión
     */
    public function generarPorRegistro(int $promotorId, int $referidoId, float $montoBase): int {
        try {
            // Bloque 1: Validaciones de entrada
            if ($promotorId <= 0) {
                throw new Exception("ID de promotor inválido: $promotorId");
            }
            
            if ($montoBase < 0) {
                throw new Exception("Monto base no puede ser negativo: $montoBase");
            }
            
            // Bloque 2: Verificar que no exista ya comisión para este referido
            // Prevenir doble pago por el mismo registro
            $stmt = $this->db->prepare("
                SELECT id 
                FROM comisiones 
                WHERE referido_id = ? AND tipo = 'registro'
                LIMIT 1
            ");
            
            $stmt->execute([$referidoId]);
            $existente = $stmt->fetch();
            
            if ($existente) {
                error_log("[COMISIONES] Comisión ya existe para referido $referidoId - ID: {$existente['id']}");
                return (int)$existente['id']; // Devolver ID existente, no crear duplicado
            }
            
            // Bloque 3: Calcular monto final aplicando bonificaciones/descuentos
            $montoFinal = $this->calcularMontoFinal($promotorId, $montoBase);
            
            // Bloque 4: Insertar nueva comisión en estado pendiente
            // Línea clave: INSERT con todos los campos requeridos para auditoría
            $stmt = $this->db->prepare("
                INSERT INTO comisiones (
                    promotor_id, referido_id, tipo, monto, moneda,
                    estado, nota, creado_en
                ) VALUES (?, ?, 'registro', ?, 'COP', 'pendiente', ?, CURRENT_TIMESTAMP)
            ");
            
            $nota = "Comisión por registro - Referido ID: $referidoId, Monto base: $montoBase";
            
            $stmt->execute([
                $promotorId,
                $referidoId,
                $montoFinal,
                $nota
            ]);
            
            $comisionId = $this->db->lastInsertId();
            
            error_log("[COMISIONES] Comisión generada - ID: $comisionId, Promotor: $promotorId, Monto: $montoFinal");
            
            return (int)$comisionId;
            
        } catch (PDOException $e) {
            error_log("[COMISIONES ERROR] Error generando comisión: " . $e->getMessage());
            throw new Exception("Error generando comisión: " . $e->getMessage());
        }
    }
    
    /**
     * Listar comisiones por promotor
     * 
     * Propósito: Obtener historial completo de comisiones de un promotor
     * para mostrar en su panel personal. Incluye filtrado por estado
     * y ordenamiento cronológico.
     * 
     * Parámetros:
     * @param int $promotorId ID del promotor
     * @param string|null $estado Filtro por estado específico (opcional)
     * @param int $limite Número máximo de registros (default 50)
     * 
     * Retorno:
     * @return array Lista de comisiones con detalles completos
     * 
     * Efectos:
     * - SELECT con JOIN para incluir datos del referido
     * - Ordenamiento por fecha descendente (más recientes primero)
     * - Formateo de montos y fechas para display
     */
    public function listarPorPromotor(int $promotorId, ?string $estado = null, int $limite = 50): array {
        try {
            // Construir query base con JOIN opcional para datos de referido
            $sql = "
                SELECT c.id, c.promotor_id, c.referido_id, c.tipo, c.monto, c.moneda,
                       c.estado, c.nota, c.fecha_aprobacion, c.fecha_pago, 
                       c.referencia_pago, c.creado_en,
                       r.registrado_usuario_id, r.creado_en as fecha_referido,
                       u.nombre as usuario_referido, u.email as email_referido
                FROM comisiones c
                LEFT JOIN referidos r ON c.referido_id = r.id
                LEFT JOIN usuarios u ON r.registrado_usuario_id = u.id
                WHERE c.promotor_id = ?
            ";
            
            $params = [$promotorId];
            
            // Agregar filtro por estado si se especifica
            if ($estado && in_array($estado, ['pendiente', 'aprobada', 'pagada', 'rechazada'])) {
                $sql .= " AND c.estado = ?";
                $params[] = $estado;
            }
            
            $sql .= " ORDER BY c.creado_en DESC LIMIT ?";
            $params[] = $limite;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $comisiones = $stmt->fetchAll();
            
            // Formatear datos para display
            foreach ($comisiones as &$comision) {
                $comision['monto_formateado'] = number_format($comision['monto'], 0, ',', '.') . ' ' . $comision['moneda'];
                $comision['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($comision['creado_en']));
                
                if ($comision['fecha_aprobacion']) {
                    $comision['fecha_aprobacion_formateada'] = date('d/m/Y H:i', strtotime($comision['fecha_aprobacion']));
                }
                
                if ($comision['fecha_pago']) {
                    $comision['fecha_pago_formateada'] = date('d/m/Y H:i', strtotime($comision['fecha_pago']));
                }
                
                // Estado con emoji para mejor UX
                $comision['estado_display'] = $this->formatearEstado($comision['estado']);
            }
            
            error_log("[COMISIONES] Listado generado para promotor $promotorId - " . count($comisiones) . " comisiones");
            
            return $comisiones;
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error listando comisiones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Aprobar comisión (solo administradores)
     * 
     * Propósito: Cambiar estado de comisión de 'pendiente' a 'aprobada'
     * como paso previo al pago. Incluye auditoría de quién aprobó.
     * 
     * @param int $comisionId ID de la comisión
     * @param int $adminId ID del administrador que aprueba
     * @param string $nota Nota opcional del administrador
     * @return bool Éxito de la operación
     */
    public function aprobar(int $comisionId, int $adminId, string $nota = ''): bool {
        try {
            // Verificar que la comisión existe y está pendiente
            $stmt = $this->db->prepare("
                SELECT id, estado, monto
                FROM comisiones 
                WHERE id = ? AND estado = 'pendiente'
            ");
            
            $stmt->execute([$comisionId]);
            $comision = $stmt->fetch();
            
            if (!$comision) {
                error_log("[COMISIONES] Comisión no encontrada o no está pendiente - ID: $comisionId");
                return false;
            }
            
            // Actualizar estado a aprobada
            $stmt = $this->db->prepare("
                UPDATE comisiones 
                SET estado = 'aprobada',
                    aprobada_por = ?,
                    fecha_aprobacion = CURRENT_TIMESTAMP,
                    nota = CONCAT(COALESCE(nota, ''), ' | Aprobada: ', ?)
                WHERE id = ?
            ");
            
            $notaAprobacion = $nota ?: 'Aprobación administrativa';
            $stmt->execute([$adminId, $notaAprobacion, $comisionId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("[COMISIONES] Comisión aprobada - ID: $comisionId, Admin: $adminId, Monto: {$comision['monto']}");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error aprobando comisión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rechazar comisión (solo administradores)
     * 
     * @param int $comisionId ID de la comisión
     * @param int $adminId ID del administrador que rechaza
     * @param string $motivo Motivo del rechazo
     * @return bool Éxito de la operación
     */
    public function rechazar(int $comisionId, int $adminId, string $motivo): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE comisiones 
                SET estado = 'rechazada',
                    aprobada_por = ?,
                    fecha_aprobacion = CURRENT_TIMESTAMP,
                    nota = CONCAT(COALESCE(nota, ''), ' | Rechazada: ', ?)
                WHERE id = ? AND estado IN ('pendiente', 'aprobada')
            ");
            
            $stmt->execute([$adminId, $motivo, $comisionId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("[COMISIONES] Comisión rechazada - ID: $comisionId, Admin: $adminId, Motivo: $motivo");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error rechazando comisión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar como pagada (solo administradores)
     * 
     * @param int $comisionId ID de la comisión
     * @param int $adminId ID del administrador que registra el pago
     * @param string $referenciaPago Referencia bancaria o de transferencia
     * @return bool Éxito de la operación
     */
    public function marcarComoPagada(int $comisionId, int $adminId, string $referenciaPago): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE comisiones 
                SET estado = 'pagada',
                    fecha_pago = CURRENT_TIMESTAMP,
                    referencia_pago = ?,
                    nota = CONCAT(COALESCE(nota, ''), ' | Pagada por admin ID: ', ?)
                WHERE id = ? AND estado = 'aprobada'
            ");
            
            $stmt->execute([$referenciaPago, $adminId, $comisionId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("[COMISIONES] Comisión marcada como pagada - ID: $comisionId, Ref: $referenciaPago");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error marcando como pagada: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de comisiones por promotor
     * 
     * @param int $promotorId ID del promotor
     * @return array Estadísticas resumidas
     */
    public function getEstadisticasByPromotor(int $promotorId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    estado,
                    COUNT(*) as cantidad,
                    SUM(monto) as total_monto,
                    AVG(monto) as promedio_monto
                FROM comisiones 
                WHERE promotor_id = ?
                GROUP BY estado
            ");
            
            $stmt->execute([$promotorId]);
            $resultados = $stmt->fetchAll();
            
            // Inicializar estadísticas con valores por defecto
            $stats = [
                'pendiente' => ['cantidad' => 0, 'monto' => 0],
                'aprobada' => ['cantidad' => 0, 'monto' => 0],
                'pagada' => ['cantidad' => 0, 'monto' => 0],
                'rechazada' => ['cantidad' => 0, 'monto' => 0],
                'total_ganado' => 0,
                'total_pendiente' => 0
            ];
            
            // Procesar resultados
            foreach ($resultados as $row) {
                $estado = $row['estado'];
                $stats[$estado] = [
                    'cantidad' => (int)$row['cantidad'],
                    'monto' => (float)$row['total_monto']
                ];
                
                if ($estado === 'pagada') {
                    $stats['total_ganado'] = (float)$row['total_monto'];
                } elseif (in_array($estado, ['pendiente', 'aprobada'])) {
                    $stats['total_pendiente'] += (float)$row['total_monto'];
                }
            }
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'pendiente' => ['cantidad' => 0, 'monto' => 0],
                'aprobada' => ['cantidad' => 0, 'monto' => 0],
                'pagada' => ['cantidad' => 0, 'monto' => 0],
                'rechazada' => ['cantidad' => 0, 'monto' => 0],
                'total_ganado' => 0,
                'total_pendiente' => 0
            ];
        }
    }
    
    /**
     * Obtener lista de comisiones para administrador
     * 
     * @param string|null $estado Filtro por estado
     * @param int $limite Límite de registros
     * @return array Lista de comisiones con datos del promotor
     */
    public function listarParaAdmin(?string $estado = null, int $limite = 100): array {
        try {
            $sql = "
                SELECT c.id, c.promotor_id, c.referido_id, c.tipo, c.monto, c.moneda,
                       c.estado, c.nota, c.fecha_aprobacion, c.fecha_pago, c.creado_en,
                       p.codigo as promotor_codigo,
                       u.nombre as promotor_nombre, u.email as promotor_email
                FROM comisiones c
                INNER JOIN promotores p ON c.promotor_id = p.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
            ";
            
            $params = [];
            
            if ($estado && in_array($estado, ['pendiente', 'aprobada', 'pagada', 'rechazada'])) {
                $sql .= " WHERE c.estado = ?";
                $params[] = $estado;
            }
            
            $sql .= " ORDER BY c.creado_en DESC LIMIT ?";
            $params[] = $limite;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("[COMISIONES ERROR] Error listando para admin: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular monto final aplicando bonificaciones
     * 
     * Propósito: Aplicar modificadores al monto base según criterios
     * del negocio (promotor premium, volumen, etc.)
     * 
     * @param int $promotorId ID del promotor
     * @param float $montoBase Monto base de la comisión
     * @return float Monto final calculado
     */
    private function calcularMontoFinal(int $promotorId, float $montoBase): float {
        // Por ahora, devolver monto base sin modificaciones
        // Aquí se pueden agregar lógicas de bonificación:
        // - Promotor premium: +20%
        // - Volumen alto: +10%
        // - Primer referido del mes: +50%
        
        return $montoBase;
    }
    
    /**
     * Formatear estado para display con emoji
     * 
     * @param string $estado Estado de la comisión
     * @return string Estado formateado con emoji
     */
    private function formatearEstado(string $estado): string {
        $estados = [
            'pendiente' => '⏳ Pendiente',
            'aprobada' => '✅ Aprobada',
            'pagada' => '💰 Pagada',
            'rechazada' => '❌ Rechazada'
        ];
        
        return $estados[$estado] ?? $estado;
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * CÓMO CAMBIAR EL MONTO BASE DESDE ADMIN:
 * 
 * 1. ACTUALIZAR CONFIGURACIÓN:
 *    UPDATE promotor_config 
 *    SET valor = '7500.00' 
 *    WHERE clave = 'comision_registro_base';
 * 
 * 2. CREAR INTERFACE ADMIN:
 *    - Formulario en views/admin/configuracion.php
 *    - Método en AdminController::actualizarConfiguracion()
 *    - Validación de montos y permisos
 * 
 * 3. APLICAR CAMBIOS DINÁMICOS:
 *    - Los nuevos registros usarán el monto actualizado
 *    - Los existentes mantienen su monto original (auditoría)
 *    - Historial de cambios en tabla config_history
 * 
 * PARAMETRIZAR OTROS ASPECTOS:
 * - Porcentajes de bonificación por volumen
 * - Límites máximos de comisión por mes
 * - Tasas diferenciadas por tipo de registro
 * - Descuentos por incumplimiento de calidad
 * 
 * WORKFLOWS DE APROBACIÓN:
 * - Auto-aprobación para montos < X
 * - Aprobación manual para montos altos
 * - Requiere 2 firmas para pagos > Y
 * - Notificaciones por email en cambios de estado
 * 
 * INTEGRACIÓN CON SISTEMAS DE PAGO:
 * - PayPal API para pagos automatizados
 * - Transferencias bancarias con archivo NACHA
 * - Wallet interno del promotor
 * - Criptomonedas para pagos internacionales
 * 
 * REPORTES Y ANÁLISIS:
 * - Dashboard financiero con KPIs
 * - Exportación a Excel/CSV para contabilidad
 * - Análisis de ROI por promotor
 * - Forecasting de comisiones futuras
 * 
 * AUDITORÍA Y COMPLIANCE:
 * - Log de todos los cambios de estado
 * - Rastreo de quién aprobó/pagó cada comisión
 * - Backup automático antes de operaciones críticas
 * - Reportes regulatorios (SUNAT, etc.)
 */

    /**
     * Actualizar configuración del sistema
     */
    public function actualizarConfiguracion($config) {
        try {
            $this->db->beginTransaction();
            
            foreach ($config as $clave => $valor) {
                $query = "INSERT INTO promotor_config (clave, valor, fecha_actualizacion) 
                         VALUES (:clave, :valor, NOW()) 
                         ON DUPLICATE KEY UPDATE 
                         valor = VALUES(valor), 
                         fecha_actualizacion = VALUES(fecha_actualizacion)";
                
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':clave', $clave);
                $stmt->bindParam(':valor', $valor);
                $stmt->execute();
            }
            
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollback();
            error_log("Error actualizando configuración: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Listar comisiones para administración
     */
    public function listarParaAdmin($filtros = [], $limite = 50, $offset = 0) {
        try {
            $where_conditions = [];
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $where_conditions[] = "c.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['promotor_id'])) {
                $where_conditions[] = "c.promotor_id = :promotor_id";
                $params[':promotor_id'] = $filtros['promotor_id'];
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT c.*, p.codigo as promotor_codigo, p.usuario_id as promotor_usuario_id
                     FROM comisiones c
                     LEFT JOIN promotores p ON c.promotor_id = p.id
                     {$where_clause}
                     ORDER BY c.fecha_generada DESC
                     LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error listando comisiones admin: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar comisiones para administración
     */
    public function contarParaAdmin($filtros = []) {
        try {
            $where_conditions = [];
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $where_conditions[] = "estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            if (!empty($filtros['promotor_id'])) {
                $where_conditions[] = "promotor_id = :promotor_id";
                $params[':promotor_id'] = $filtros['promotor_id'];
            }
            
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            $query = "SELECT COUNT(*) FROM comisiones {$where_clause}";
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error contando comisiones admin: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener estadísticas para administración
     */
    public function getEstadisticasAdmin() {
        try {
            $query = "SELECT 
                        estado,
                        COUNT(*) as cantidad,
                        COALESCE(SUM(monto), 0) as total_monto
                     FROM comisiones 
                     GROUP BY estado";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $estadisticas = [
                'pendientes' => 0,
                'aprobadas' => 0,
                'pagadas' => 0,
                'rechazadas' => 0,
                'cantidad_pendientes' => 0,
                'cantidad_aprobadas' => 0,
                'cantidad_pagadas' => 0,
                'cantidad_rechazadas' => 0
            ];
            
            foreach ($resultados as $row) {
                $estado = $row['estado'];
                $estadisticas[$estado . 's'] = $row['total_monto'];
                $estadisticas['cantidad_' . $estado . 's'] = $row['cantidad'];
            }
            
            return $estadisticas;
            
        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas admin: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener totales para el sistema
     */
    public function getTotalPendiente() {
        return $this->getTotalPorEstado('pendiente');
    }
    
    public function getTotalPagada() {
        return $this->getTotalPorEstado('pagada');
    }
    
    private function getTotalPorEstado($estado) {
        try {
            $query = "SELECT COALESCE(SUM(monto), 0) FROM comisiones WHERE estado = :estado";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->execute();
            
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error obteniendo total por estado: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Marcar comisión como pagada
     */
    public function marcarPagada($comisionId, $adminId, $referencia = '', $notas = '') {
        try {
            $query = "UPDATE comisiones 
                     SET estado = 'pagada',
                         fecha_pago = NOW(),
                         referencia_pago = :referencia,
                         notas_admin = CONCAT(COALESCE(notas_admin, ''), ' | Pagado: ', :notas),
                         admin_id = :admin_id
                     WHERE id = :id AND estado = 'aprobada'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $comisionId, PDO::PARAM_INT);
            $stmt->bindParam(':admin_id', $adminId, PDO::PARAM_INT);
            $stmt->bindParam(':referencia', $referencia);
            $stmt->bindParam(':notas', $notas);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
            
        } catch (PDOException $e) {
            error_log("Error marcando comisión como pagada: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener resumen financiero del promotor
     */
    public function getResumenPromotor($promotorId) {
        try {
            $query = "SELECT 
                        estado,
                        COALESCE(SUM(monto), 0) as total
                     FROM comisiones
                     WHERE promotor_id = :promotor_id
                     GROUP BY estado";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':promotor_id', $promotorId, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resumen = [
                'pendientes' => 0,
                'aprobadas' => 0, 
                'pagadas' => 0,
                'rechazadas' => 0,
                'total' => 0
            ];
            
            foreach ($resultados as $row) {
                $resumen[$row['estado'] . 's'] = $row['total'];
                $resumen['total'] += $row['total'];
            }
            
            return $resumen;
            
        } catch (PDOException $e) {
            error_log("Error obteniendo resumen promotor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar comisiones por promotor
     */
    public function contarByPromotorId($promotorId, $estado = 'todas') {
        try {
            $query = "SELECT COUNT(*) FROM comisiones WHERE promotor_id = :promotor_id";
            $params = [':promotor_id' => $promotorId];
            
            if ($estado !== 'todas') {
                $query .= " AND estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $stmt = $this->db->prepare($query);
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Error contando comisiones: " . $e->getMessage());
            return 0;
        }
    }
}
?>