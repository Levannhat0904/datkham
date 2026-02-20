-- Database: datkham (create DB first: CREATE DATABASE datkham; USE datkham;)
-- Appointment status: pending (Chờ xác nhận), confirmed (Đã xác nhận), cancelled (Đã hủy), completed (Đã khám)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- patients
CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- services
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- schedules: each row = one available (service, date, time)
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `schedules_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- appointments; status: pending | confirmed | cancelled | completed (default pending)
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) unsigned NOT NULL,
  `service_id` int(11) unsigned NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `patient_name` varchar(255) NOT NULL,
  `patient_phone` varchar(50) NOT NULL,
  `patient_email` varchar(255) DEFAULT NULL,
  `note` text,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`),
  KEY `service_id` (`service_id`),
  KEY `status` (`status`),
  CONSTRAINT `appointments_patient_fk` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_service_fk` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- admin_users (seed 1 account only; no UI to create admin)
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- Seed 1 admin (password: admin123)
INSERT INTO `admin_users` (`username`, `password_hash`, `created_at`) VALUES
('admin', '$2y$12$rTgI3YeU3RLmcrI0JRV1vOLGBnaBBoLLYnrfTahchQJCY82Q28Oqy', NOW())
ON DUPLICATE KEY UPDATE `username` = `username`;

-- Seed sample services
INSERT INTO `services` (`name`, `description`) VALUES
('Khám tổng quát', 'Khám sức khỏe tổng quát'),
('Nội soi', 'Nội soi dạ dày, đường tiêu hóa')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
