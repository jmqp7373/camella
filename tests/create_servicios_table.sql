-- Script SQL para crear la tabla servicios y datos de prueba
-- Ejecutar en phpMyAdmin o por línea de comandos

CREATE TABLE IF NOT EXISTS `servicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `status` enum('activo','inactivo','pausado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos anuncios de ejemplo para el usuario con ID 1
INSERT INTO `servicios` (`user_id`, `titulo`, `descripcion`, `precio`, `status`, `created_at`) VALUES
(1, 'Plomero profesional con experiencia', 'Servicio de plomería residencial y comercial. Instalación, mantenimiento y reparaciones. Disponibilidad inmediata.', 50000, 'activo', NOW()),
(1, 'Electricista certificado 24/7', 'Instalaciones eléctricas, mantenimiento preventivo y correctivo. Atención a emergencias.', 60000, 'activo', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'Servicio de carpintería', 'Muebles a medida, reparaciones y restauración. Trabajos en madera de alta calidad.', 80000, 'activo', DATE_SUB(NOW(), INTERVAL 5 DAY));
