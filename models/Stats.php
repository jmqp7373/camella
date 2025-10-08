<?php
/**
 * Stats.php - Modelo de Estadísticas del Sistema
 * 
 * Propósito: Proporcionar contadores y métricas del sistema de forma segura,
 * tolerante a la ausencia de tablas y con manejo robusto de errores.
 * 
 * Características principales:
 * - Consultas preparadas para prevenir SQL injection
 * - Verificación de existencia de tablas antes de consultar
 * - Manejo de excepciones que devuelven valores por defecto seguros
 * - Diseño extensible para futuros KPIs sin romper funcionalidad
 * 
 * Efectos: Solo realiza consultas SELECT de lectura, no modifica datos.
 * No crea tablas nuevas, solo verifica existencia y cuenta registros.
 * 
 * @author Camella Development Team - Admin Dashboard
 * @version 1.0 
 * @date 2025-10-08
 */

class Stats {
    
    /**
     * Obtener contadores principales del sistema
     * 
     * Propósito: Recopilar métricas clave del sistema para mostrar
     * en el dashboard administrativo de forma segura y eficiente.
     * 
     * Parámetros:
     * @param PDO $db Conexión PDO a la base de datos (debe estar inicializada)
     * 
     * Retorno:
     * @return array Array asociativo con contadores del sistema:
     *   - usuarios_total: Total de usuarios en el sistema
     *   - usuarios_admin: Usuarios con rol admin
     *   - usuarios_promotor: Usuarios con rol promotor  
     *   - usuarios_publicante: Usuarios con rol publicante
     *   - ofertas_activas: Ofertas de trabajo activas (0 si tabla no existe)
     *   - empresas: Número de empresas registradas (0 si tabla no existe)
     *   - conexiones_exitosas: Logins exitosos recientes (0 si tabla no existe)
     * 
     * Efectos:
     * - Ejecuta múltiples consultas SELECT de solo lectura
     * - Registra errores en error_log si hay fallos
     * - Nunca lanza excepciones al código llamador
     * - Devuelve valores por defecto seguros en caso de error
     * 
     * Diseño de tolerancia a errores:
     * Cada consulta está envuelta en try/catch individual para que
     * el fallo de una métrica no afecte a las demás.
     */
    public function getCounts(PDO $db): array {
        // Inicializar array de contadores con valores por defecto seguros
        $stats = [
            'usuarios_total' => 0,
            'usuarios_admin' => 0,
            'usuarios_promotor' => 0,
            'usuarios_publicante' => 0,
            'ofertas_activas' => 0,
            'empresas' => 0,
            'conexiones_exitosas' => 0
        ];
        
        // Verificar existencia de tabla usuarios antes de contar
        if ($this->tableExists($db, 'usuarios')) {
            $this->getUserStats($db, $stats);
        } else {
            error_log("[STATS] Tabla usuarios no existe - contadores de usuarios en 0");
        }
        
        // Verificar y contar ofertas activas (tabla futura)
        if ($this->tableExists($db, 'ofertas')) {
            $this->getOfertasStats($db, $stats);
        } else {
            error_log("[STATS] Tabla ofertas no existe - contador en 0");
        }
        
        // Verificar y contar empresas (tabla futura)
        if ($this->tableExists($db, 'empresas')) {
            $this->getEmpresasStats($db, $stats);
        } else {
            error_log("[STATS] Tabla empresas no existe - contador en 0");
        }
        
        // Verificar y contar conexiones exitosas (tabla futura)
        if ($this->tableExists($db, 'login_logs')) {
            $this->getConexionesStats($db, $stats);
        } else {
            error_log("[STATS] Tabla login_logs no existe - contador en 0");
        }
        
        error_log("[STATS] Estadísticas generadas: " . json_encode($stats));
        return $stats;
    }
    
    /**
     * Verificar si una tabla existe en la base de datos
     * 
     * Propósito: Determinar de forma segura si una tabla existe antes
     * de intentar hacer consultas sobre ella, evitando errores SQL.
     * 
     * @param PDO $db Conexión PDO a la base de datos
     * @param string $table Nombre de la tabla a verificar
     * @return bool True si la tabla existe, false en caso contrario
     * 
     * Implementación: Usa INFORMATION_SCHEMA.TABLES que es estándar
     * en MySQL y otros SGBDs compatibles.
     */
    private function tableExists(PDO $db, string $table): bool {
        try {
            // Línea clave: Query a INFORMATION_SCHEMA para verificar existencia
            $query = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$table]);
            $count = $stmt->fetchColumn();
            
            $exists = $count > 0;
            error_log("[STATS] Tabla '$table' " . ($exists ? 'existe' : 'no existe'));
            
            return $exists;
            
        } catch (PDOException $e) {
            // Comentario crítico: Si falla la verificación, asumir que no existe
            error_log("[STATS ERROR] Error verificando tabla '$table': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de usuarios por rol
     * 
     * Propósito: Contar usuarios totales y desglosados por rol
     * usando consultas preparadas seguras.
     * 
     * @param PDO $db Conexión a base de datos
     * @param array &$stats Array de estadísticas a actualizar (por referencia)
     */
    private function getUserStats(PDO $db, array &$stats): void {
        try {
            // Contar usuarios totales
            $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
            $stmt->execute();
            $stats['usuarios_total'] = (int)$stmt->fetchColumn();
            
            // Contar usuarios por rol específico
            $roles = ['admin', 'promotor', 'publicante'];
            
            foreach ($roles as $rol) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = ? AND activo = 1");
                $stmt->execute([$rol]);
                $stats["usuarios_$rol"] = (int)$stmt->fetchColumn();
            }
            
            error_log("[STATS] Estadísticas de usuarios obtenidas exitosamente");
            
        } catch (PDOException $e) {
            // Mantener valores por defecto (0) si hay error
            error_log("[STATS ERROR] Error obteniendo stats de usuarios: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estadísticas de ofertas de trabajo
     * 
     * Propósito: Contar ofertas activas cuando la tabla ofertas exista.
     * 
     * Nota para desarrolladores: Esta función asume que la tabla ofertas
     * tendrá una columna 'estado' o 'activa' para filtrar ofertas válidas.
     * Ajustar la consulta cuando se implemente la tabla real.
     * 
     * @param PDO $db Conexión a base de datos
     * @param array &$stats Array de estadísticas a actualizar
     */
    private function getOfertasStats(PDO $db, array &$stats): void {
        try {
            // Comentario: Consulta preparada para cuando exista la tabla ofertas
            // Asumir columna 'activa' o 'estado' = 'activa' para filtrar
            $stmt = $db->prepare("SELECT COUNT(*) FROM ofertas WHERE activa = 1 OR estado = 'activa'");
            $stmt->execute();
            $stats['ofertas_activas'] = (int)$stmt->fetchColumn();
            
            error_log("[STATS] Estadísticas de ofertas obtenidas");
            
        } catch (PDOException $e) {
            // Si la estructura de tabla es diferente, mantener en 0
            error_log("[STATS ERROR] Error obteniendo stats de ofertas: " . $e->getMessage());
            $stats['ofertas_activas'] = 0;
        }
    }
    
    /**
     * Obtener estadísticas de empresas registradas
     * 
     * @param PDO $db Conexión a base de datos  
     * @param array &$stats Array de estadísticas a actualizar
     */
    private function getEmpresasStats(PDO $db, array &$stats): void {
        try {
            // Comentario: Consulta genérica para contar empresas activas
            $stmt = $db->prepare("SELECT COUNT(*) FROM empresas WHERE activa = 1");
            $stmt->execute();
            $stats['empresas'] = (int)$stmt->fetchColumn();
            
            error_log("[STATS] Estadísticas de empresas obtenidas");
            
        } catch (PDOException $e) {
            error_log("[STATS ERROR] Error obteniendo stats de empresas: " . $e->getMessage());
            $stats['empresas'] = 0;
        }
    }
    
    /**
     * Obtener estadísticas de conexiones exitosas recientes
     * 
     * Propósito: Contar logins exitosos de los últimos 30 días
     * cuando exista tabla de logs de autenticación.
     * 
     * @param PDO $db Conexión a base de datos
     * @param array &$stats Array de estadísticas a actualizar
     */
    private function getConexionesStats(PDO $db, array &$stats): void {
        try {
            // Contar conexiones exitosas de los últimos 30 días
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM login_logs 
                WHERE exitoso = 1 
                AND fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $stats['conexiones_exitosas'] = (int)$stmt->fetchColumn();
            
            error_log("[STATS] Estadísticas de conexiones obtenidas");
            
        } catch (PDOException $e) {
            error_log("[STATS ERROR] Error obteniendo stats de conexiones: " . $e->getMessage());
            $stats['conexiones_exitosas'] = 0;
        }
    }
}

/**
 * NOTAS PARA DESARROLLADORES NOVATOS:
 * 
 * CÓMO AGREGAR NUEVOS KPIs SIN ROMPER FUNCIONALIDAD:
 * 
 * 1. AGREGAR AL ARRAY INICIAL:
 *    En getCounts(), agregar nueva clave con valor por defecto 0:
 *    $stats['nuevo_kpi'] = 0;
 * 
 * 2. CREAR MÉTODO HELPER PRIVADO:
 *    private function getNuevoKpiStats(PDO $db, array &$stats): void {
 *        // Lógica similar a getUserStats()
 *    }
 * 
 * 3. VERIFICAR TABLA Y LLAMAR:
 *    if ($this->tableExists($db, 'nueva_tabla')) {
 *        $this->getNuevoKpiStats($db, $stats);
 *    }
 * 
 * 4. MANEJO DE ERRORES:
 *    Siempre envolver en try/catch
 *    Registrar en error_log
 *    Mantener valor por defecto si falla
 * 
 * EJEMPLOS DE FUTUROS KPIs:
 * - postulaciones_mes: Aplicaciones del mes actual
 * - empresas_premium: Empresas con suscripción activa  
 * - ofertas_destacadas: Ofertas con promoción activa
 * - candidatos_activos: Usuarios buscando empleo
 * 
 * PRINCIPIOS DE DISEÑO:
 * - Nunca romper el dashboard si falla un KPI
 * - Valores por defecto seguros (0 para contadores)
 * - Logging detallado para debugging
 * - Consultas preparadas siempre
 * - Verificación de tabla antes de consultar
 * 
 * CONECTAR NUEVAS TABLAS CUANDO EXISTAN:
 * 1. Actualizar las consultas en los métodos helper
 * 2. Ajustar nombres de columnas según esquema real
 * 3. Agregar filtros apropiados (activo, estado, fecha)
 * 4. Probar con datos reales
 * 5. Monitorear logs para errores
 */

?>