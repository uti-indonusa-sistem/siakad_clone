-- ============================================
-- QUICK MIGRATION SCRIPT
-- Untuk setup cepat tanpa perlu manual import
-- ============================================

-- 1. Cek apakah tabel sudah ada
SELECT 'Checking existing tables...' as status;

-- 2. Create tables jika belum ada
CREATE TABLE IF NOT EXISTS `moodle_sync_mapping` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('mahasiswa', 'dosen', 'course', 'category') NOT NULL,
  `siakad_id` VARCHAR(100) NOT NULL,
  `moodle_id` INT(11) NOT NULL,
  `last_sync` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_mapping` (`type`, `siakad_id`),
  KEY `idx_type` (`type`),
  KEY `idx_siakad_id` (`siakad_id`),
  KEY `idx_moodle_id` (`moodle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `moodle_sync_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(100) NOT NULL,
  `status` ENUM('success', 'failed', 'partial') DEFAULT 'success',
  `results` TEXT,
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `moodle_config` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `config_key` VARCHAR(100) NOT NULL,
  `config_value` TEXT,
  `description` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `moodle_sync_queue` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('mahasiswa', 'dosen', 'course', 'enrolment') NOT NULL,
  `data` TEXT NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `attempts` INT(11) DEFAULT 0,
  `error_message` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Insert default config
INSERT INTO `moodle_config` (`config_key`, `config_value`, `description`) VALUES
('moodle_url', 'https://learning.poltekindonusa.ac.id', 'Moodle base URL'),
('moodle_token', '4be2211ea385dea733c825d9ce53978e', 'Moodle web service token'),
('email_domain', '@poltekindonusa.ac.id', 'Default email domain'),
('sync_batch_size', '50', 'Number of records per batch'),
('auto_sync_enabled', '0', 'Enable automatic sync via cron')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

SELECT 'Migration completed successfully!' as status;
SELECT 'Tables created:' as info;
SELECT 'moodle_sync_mapping' as table_name UNION ALL
SELECT 'moodle_sync_log' UNION ALL
SELECT 'moodle_config' UNION ALL
SELECT 'moodle_sync_queue';
