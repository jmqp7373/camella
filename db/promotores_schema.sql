-- ========================================
-- ESQUEMA DE BASE DE DATOS - MÓDULO PROMOTORES
-- ========================================

-- Propósito: Crear tablas para sistema de referidos y comisiones
-- Tolerancia a producción: Usa CREATE TABLE IF NOT EXISTS para evitar errores
-- Reversión: DROP TABLE en orden inverso (comisiones, referidos, promotores)
-- FK esperadas: usuario_id → usuarios.id (debe existir tabla usuarios)

-- TABLA: promotores
-- Propósito: Almacenar información de usuarios promotores y sus códigos únicos
-- FK esperadas: usuario_id debe referenciar usuarios.id con rol='promotor'
-- Tolerante a producción: IF NOT EXISTS evita error si ya existe
-- Reversión: DROP TABLE IF EXISTS promotores;

CREATE TABLE IF NOT EXISTS promotores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,           -- FK con usuarios.id (rol=promotor)
  codigo VARCHAR(32) UNIQUE NOT NULL, -- código único alfanumérico (16 hex chars)
  activo TINYINT(1) DEFAULT 1,      -- permite desactivar promotor sin borrar historial
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_usuario_id (usuario_id),
  INDEX idx_codigo (codigo),
  INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: referidos
-- Propósito: Rastrear visitas y registros atribuidos a cada promotor
-- FK esperadas: 
--   - promotor_id → promotores.id
--   - registrado_usuario_id → usuarios.id (cuando se complete registro)
-- Tolerante a producción: IF NOT EXISTS evita conflictos
-- Reversión: DROP TABLE IF EXISTS referidos;

CREATE TABLE IF NOT EXISTS referidos (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  promotor_id INT NOT NULL,              -- FK promotores.id
  fingerprint VARCHAR(64),               -- hash ligero para dedup (cookie+IP+UA)
  ip_registro VARBINARY(16),            -- IP en formato binario (IPv4/IPv6)
  user_agent VARCHAR(255),              -- User-Agent del navegador
  pais VARCHAR(3),                      -- código ISO país (opcional, via GeoIP)
  registrado_usuario_id INT NULL,       -- FK usuarios.id si completa registro
  estado ENUM('visit','registro','rechazado') DEFAULT 'visit',
  valor_comision DECIMAL(10,2) DEFAULT 0.00, -- monto calculado al momento
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_promotor_id (promotor_id),
  INDEX idx_fingerprint (fingerprint),
  INDEX idx_registrado_usuario_id (registrado_usuario_id),
  INDEX idx_estado (estado),
  INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA: comisiones
-- Propósito: Gestionar pagos y estados de comisiones por referidos
-- FK esperadas:
--   - promotor_id → promotores.id  
--   - referido_id → referidos.id (puede ser NULL para comisiones manuales)
-- Tolerante a producción: IF NOT EXISTS evita errores de duplicación
-- Reversión: DROP TABLE IF EXISTS comisiones;

CREATE TABLE IF NOT EXISTS comisiones (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  promotor_id INT NOT NULL,                -- FK promotores.id
  referido_id BIGINT NULL,                 -- FK referidos.id (NULL si es comisión manual)
  tipo ENUM('registro','manual','bonus','penalizacion') DEFAULT 'registro',
  monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  moneda CHAR(3) DEFAULT 'COP',
  estado ENUM('pendiente','aprobada','pagada','rechazada') DEFAULT 'pendiente',
  nota VARCHAR(255),                       -- comentarios admin
  aprobada_por INT NULL,                   -- FK usuarios.id del admin que aprueba
  fecha_aprobacion TIMESTAMP NULL,
  fecha_pago TIMESTAMP NULL,
  referencia_pago VARCHAR(100),           -- ref. bancaria/transferencia
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_promotor_id (promotor_id),
  INDEX idx_referido_id (referido_id),
  INDEX idx_tipo (tipo),
  INDEX idx_estado (estado),
  INDEX idx_fecha_pago (fecha_pago),
  INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- CONFIGURACIÓN Y CONSTANTES DEL SISTEMA
-- ========================================

-- Propósito: Tabla para almacenar configuraciones del módulo promotores
-- Tolerante a producción: Permite parametrizar sin tocar código
-- Reversión: DROP TABLE IF EXISTS promotor_config;

CREATE TABLE IF NOT EXISTS promotor_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(50) UNIQUE NOT NULL,
  valor VARCHAR(255) NOT NULL,
  descripcion TEXT,
  tipo ENUM('decimal','int','string','boolean') DEFAULT 'string',
  activa TINYINT(1) DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_clave (clave),
  INDEX idx_activa (activa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones por defecto (solo si no existen)
INSERT IGNORE INTO promotor_config (clave, valor, descripcion, tipo) VALUES
('comision_registro_base', '5000.00', 'Comisión base por registro completado (COP)', 'decimal'),
('cookie_expiry_days', '7', 'Días de validez de cookie de referido', 'int'),
('max_referidos_dia', '50', 'Máximo referidos por promotor por día', 'int'),
('self_referral_permitido', '0', 'Permitir auto-referidos (0=no, 1=sí)', 'boolean'),
('qr_cache_minutes', '60', 'Minutos de cache para códigos QR', 'int');

-- ========================================
-- FOREIGN KEYS (OPCIONAL - SOLO SI EXISTE TABLA USUARIOS)
-- ========================================

-- NOTA: Estas FK se agregan solo si la tabla usuarios ya existe
-- En producción, verificar estructura antes de ejecutar

-- ALTER TABLE promotores 
--   ADD CONSTRAINT fk_promotores_usuario 
--   FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
--   ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE referidos 
--   ADD CONSTRAINT fk_referidos_promotor 
--   FOREIGN KEY (promotor_id) REFERENCES promotores(id) 
--   ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE referidos 
--   ADD CONSTRAINT fk_referidos_usuario 
--   FOREIGN KEY (registrado_usuario_id) REFERENCES usuarios(id) 
--   ON DELETE SET NULL ON UPDATE CASCADE;

-- ALTER TABLE comisiones 
--   ADD CONSTRAINT fk_comisiones_promotor 
--   FOREIGN KEY (promotor_id) REFERENCES promotores(id) 
--   ON DELETE CASCADE ON UPDATE CASCADE;

-- ALTER TABLE comisiones 
--   ADD CONSTRAINT fk_comisiones_referido 
--   FOREIGN KEY (referido_id) REFERENCES referidos(id) 
--   ON DELETE SET NULL ON UPDATE CASCADE;

-- ========================================
-- NOTAS DE IMPLEMENTACIÓN
-- ========================================

/*
TOLERANCIA A PRODUCCIÓN:
- IF NOT EXISTS evita errores si tablas ya existen
- INSERT IGNORE para configuraciones evita duplicados
- FK comentadas para aplicar manualmente después de verificar estructura
- Índices optimizados para consultas frecuentes

REVERSIÓN SEGURA (ejecutar en orden):
DROP TABLE IF EXISTS comisiones;
DROP TABLE IF EXISTS referidos;  
DROP TABLE IF EXISTS promotor_config;
DROP TABLE IF EXISTS promotores;

MIGRACIÓN DE DATOS (si hay tabla anterior):
-- Backup antes de cualquier cambio
-- Migrar datos existentes con mapeo de IDs
-- Verificar integridad referencial post-migración

PERFORMANCE CONSIDERATIONS:
- InnoDB para transacciones ACID
- Índices en columnas de búsqueda frecuente  
- BIGINT para referidos (escala alta esperada)
- VARCHAR optimizado por uso real

SEGURIDAD Y PRIVACIDAD:
- IP en formato binario para optimización
- Fingerprint hasheado, no datos raw
- No almacenar cookies/tokens directamente
- Campos para auditoría (fechas, aprobaciones)
*/