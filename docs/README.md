# 📚 Dokumentasi Integrasi SIAKAD - Moodle

Selamat datang di dokumentasi sistem integrasi SIAKAD dengan Moodle LMS!

## 📖 Daftar Dokumentasi

### 1. [README_MOODLE.md](README_MOODLE.md)
**Overview dan Quick Reference**
- Ringkasan sistem integrasi
- Arsitektur dan komponen
- Struktur file
- Quick reference untuk penggunaan sehari-hari

### 2. [QUICK_START_MOODLE.md](QUICK_START_MOODLE.md)
**Panduan Setup Cepat (5 Menit)**
- Langkah-langkah instalasi
- Konfigurasi dasar
- Test connection
- Sync pertama
- Troubleshooting cepat

### 3. [MOODLE_INTEGRATION.md](MOODLE_INTEGRATION.md)
**Dokumentasi Lengkap**
- Instalasi detail
- Konfigurasi Moodle (Web Services, Token, Roles)
- Konfigurasi SIAKAD
- Penggunaan (Manual & Automated)
- API Reference lengkap
- Troubleshooting komprehensif
- Monitoring & Logging
- Security best practices

## 🚀 Mulai dari Mana?

### Untuk Pemula
1. Baca [QUICK_START_MOODLE.md](QUICK_START_MOODLE.md) untuk setup cepat
2. Ikuti langkah demi langkah
3. Test connection
4. Lakukan sync pertama dengan data kecil

### Untuk Administrator
1. Baca [MOODLE_INTEGRATION.md](MOODLE_INTEGRATION.md) untuk pemahaman lengkap
2. Setup Web Services di Moodle dengan benar
3. Konfigurasi security dan permissions
4. Setup automation dengan cron job

### Untuk Developer
1. Baca [README_MOODLE.md](README_MOODLE.md) untuk arsitektur sistem
2. Pelajari API Reference di [MOODLE_INTEGRATION.md](MOODLE_INTEGRATION.md)
3. Lihat source code di `/ws/` dan `/public/baak/moodle/`

## 🎯 Fitur Utama

✅ **Sinkronisasi Mahasiswa**
- Username: NIM
- Email: `nim@poltekindonusa.ac.id`
- Password: NIM (default)
- Auto-mapping SIAKAD ↔ Moodle

✅ **Sinkronisasi Dosen**
- Username: NIDN
- Email: `nidn@poltekindonusa.ac.id`
- Password: NIDN (default)
- Role: Teacher/Editing Teacher

✅ **Sinkronisasi Mata Kuliah**
- Berdasarkan program studi
- Auto-create categories
- Mapping kode mata kuliah

✅ **Sinkronisasi Enrollment**
- Auto-enroll mahasiswa ke kelas
- Enroll dosen sebagai teacher
- Berdasarkan semester aktif

## 📊 Struktur Database

### Tabel Utama
- `moodle_sync_mapping` - Mapping ID SIAKAD ↔ Moodle
- `moodle_sync_log` - Log aktivitas sinkronisasi
- `moodle_config` - Konfigurasi sistem
- `moodle_sync_queue` - Queue untuk async processing
- `moodle_password_mapping` - Password mapping (encrypted)

### Tabel SIAKAD (Production)
- `wsia_mahasiswa` & `wsia_mahasiswa_pt` - Data mahasiswa
- `wsia_dosen` - Data dosen
- `wsia_mata_kuliah` - Data mata kuliah
- `wsia_kelas_kuliah` - Data kelas kuliah
- `wsia_nilai` - Data nilai (untuk enrollment)
- `wsia_sms` - Data program studi
- `wsia_semester` - Data semester

## 🔧 Komponen Sistem

### Backend (PHP)
- **MoodleWebService.php** - Client API Moodle
- **MoodleSyncService.php** - Service layer untuk sync
- **moodle_config.php** - Konfigurasi

### Frontend (Dashboard)
- **index.php** - Dashboard monitoring
- **api.php** - RESTful API endpoint

### Database
- **migration_moodle_integration.sql** - Schema database

## 📞 Support

Jika mengalami kesulitan:
1. Cek [Troubleshooting](MOODLE_INTEGRATION.md#troubleshooting) di dokumentasi lengkap
2. Jalankan test script: `php ws/test_moodle.php`
3. Cek log error di `logs/moodle_error.log`
4. Hubungi tim IT support

## 📝 Changelog

### Version 1.0.0 (2026-01-21)
- ✅ Initial release
- ✅ Sync mahasiswa, dosen, courses, enrolments
- ✅ Dashboard monitoring
- ✅ API endpoint
- ✅ Logging system
- ✅ Dokumentasi lengkap
- ✅ Disesuaikan dengan struktur database production (wsia_*)

## 📄 License

Copyright © 2026 Politeknik Indonusa Surakarta

---

**Selamat menggunakan sistem integrasi SIAKAD-Moodle! 🎉**
