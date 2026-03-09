-- =====================================================
-- Table: kartu_ujian_tokens
-- Purpose: Menyimpan token unik untuk validasi kartu ujian
-- =====================================================

CREATE TABLE IF NOT EXISTS `kartu_ujian_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `no_pend` varchar(50) NOT NULL,
  `angkatan` varchar(4) NOT NULL,
  `kode_prodi` varchar(10) NOT NULL,
  `jenis_daftar` varchar(10) NOT NULL,
  `semester` int(2) NOT NULL,
  `id_smt` varchar(5) NOT NULL,
  `tipe_ujian` enum('UTS','UAS') NOT NULL,
  `tanggal_cetak` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=aktif, 0=revoked',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_nim_semester_tipe` (`nim`, `semester`, `tipe_ujian`),
  KEY `idx_token_active` (`token`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index untuk performa query validasi
CREATE INDEX idx_validation ON kartu_ujian_tokens(token, is_active, tipe_ujian);

-- Contoh data (untuk testing)
-- INSERT INTO kartu_ujian_tokens (token, nim, no_pend, angkatan, kode_prodi, jenis_daftar, semester, id_smt, tipe_ujian, tanggal_cetak) 
-- VALUES ('abc123...', '20240001', '123456', '2024', 'TI', 'REG', 5, '20252', 'UTS', NOW());
