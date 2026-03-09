-- Moodle Integration Database Schema
-- Created: 2026-01-21
-- Description: Tables for SIAKAD-Moodle synchronization

-- Table for storing sync mapping between SIAKAD and Moodle
CREATE TABLE IF NOT EXISTS `moodle_sync_mapping` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('mahasiswa', 'dosen', 'course', 'category') NOT NULL,
  `siakad_id` VARCHAR(100) NOT NULL,
  `moodle_id` INT(11) NOT NULL,
  `last_sync` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mapping` (`type`, `siakad_id`),
  KEY `idx_type` (`type`),
  KEY `idx_siakad_id` (`siakad_id`),
  KEY `idx_moodle_id` (`moodle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for logging sync activities
CREATE TABLE IF NOT EXISTS `moodle_sync_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(100) NOT NULL,
  `results` TEXT,
  `status` ENUM('success', 'failed', 'partial') DEFAULT 'success',
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_by` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing Moodle configuration
CREATE TABLE IF NOT EXISTS `moodle_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT,
  `description` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration
INSERT INTO `moodle_config` (`config_key`, `config_value`, `description`) VALUES
('moodle_url', 'https://learning.poltekindonusa.ac.id', 'Moodle base URL'),
('moodle_token', '', 'Moodle web service token'),
('email_domain', '@poltekindonusa.ac.id', 'Default email domain for users'),
('email_required', '0', 'Whether email is required (0=no, 1=yes)'),
('auto_sync_enabled', '0', 'Enable automatic synchronization (0=no, 1=yes)'),
('sync_interval', '3600', 'Sync interval in seconds'),
('last_sync_mahasiswa', NULL, 'Last sync timestamp for mahasiswa'),
('last_sync_dosen', NULL, 'Last sync timestamp for dosen'),
('last_sync_courses', NULL, 'Last sync timestamp for courses'),
('last_sync_enrolments', NULL, 'Last sync timestamp for enrolments')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

-- Table for storing sync queue (for async processing)
CREATE TABLE IF NOT EXISTS `moodle_sync_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('mahasiswa', 'dosen', 'course', 'enrolment') NOT NULL,
  `entity_id` VARCHAR(100) NOT NULL,
  `action` ENUM('create', 'update', 'delete') NOT NULL,
  `data` TEXT,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `attempts` INT(11) DEFAULT 0,
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for storing password mappings (encrypted)
CREATE TABLE IF NOT EXISTS `moodle_password_mapping` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_type` ENUM('mahasiswa', 'dosen') NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `last_changed` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for better performance
CREATE INDEX idx_sync_mapping_last_sync ON moodle_sync_mapping(last_sync);
CREATE INDEX idx_sync_log_status ON moodle_sync_log(status);
CREATE INDEX idx_sync_queue_status_type ON moodle_sync_queue(status, type);
