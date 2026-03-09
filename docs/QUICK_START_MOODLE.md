# 🚀 Quick Start Guide - Integrasi SIAKAD Moodle

## Langkah Cepat (5 Menit Setup)

### 1️⃣ Install Database (1 menit)

Buka terminal/command prompt di folder siakad:

```bash
cd c:\laragon\www\siakad
mysql -u usiakad -p siakaddb < database/migration_moodle_integration.sql
```

Atau via phpMyAdmin:
- Buka phpMyAdmin
- Pilih database `siakaddb`
- Import file `database/migration_moodle_integration.sql`

### 2️⃣ Konfigurasi Moodle (2 menit)

**A. Enable Web Services di Moodle**

Login ke https://learning.poltekindonusa.ac.id sebagai admin:

1. **Site administration → Advanced features**
   - Centang "Enable web services" → Save

2. **Site administration → Plugins → Web services → Manage protocols**
   - Enable "REST protocol"

**B. Create Token**

1. **Site administration → Plugins → Web services → Manage tokens**
   - Add token
   - User: pilih admin user
   - Service: pilih "Moodle mobile web service" (atau buat custom)
   - Copy token yang dihasilkan

### 3️⃣ Konfigurasi SIAKAD (1 menit)

Edit file `config/moodle_config.php`:

```php
define('MOODLE_TOKEN', 'PASTE_TOKEN_DARI_MOODLE_DISINI');
```

Atau via Dashboard (lebih mudah):
1. Akses: http://localhost/siakad/public/baak/moodle/
2. Scroll ke "Configuration"
3. Paste token di field "Moodle Token"
4. Klik "Save"

### 4️⃣ Test Connection (30 detik)

Akses dashboard:
```
http://localhost/siakad/public/baak/moodle/
```

Atau jalankan test script:
```bash
php ws/test_moodle.php
```

Jika muncul "Connected to Moodle" → ✅ Berhasil!

### 5️⃣ Sync Pertama (30 detik)

Di dashboard, klik tombol:
1. **"Sync Mahasiswa"** (test dengan 10 data dulu)
2. Cek di Moodle apakah user sudah muncul
3. Jika berhasil, lanjut sync semua data

---

## 🎯 Penggunaan Sehari-hari

### Manual Sync via Dashboard

Akses: http://localhost/siakad/public/baak/moodle/

**Sync Individual:**
- Klik "Sync Mahasiswa" → Sync semua mahasiswa aktif
- Klik "Sync Dosen" → Sync semua dosen aktif
- Klik "Sync Courses" → Sync semua mata kuliah
- Klik "Sync Enrolments" → Sync enrollment mahasiswa ke kelas

**Sync All:**
- Klik "Sync All Data" → Sync semuanya sekaligus

### Auto Sync via Cron (Recommended)

**Windows (Task Scheduler):**

1. Buat file `sync_moodle.bat`:
```batch
@echo off
curl "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all"
```

2. Buat scheduled task:
   - Buka Task Scheduler
   - Create Basic Task
   - Name: "SIAKAD Moodle Sync"
   - Trigger: Daily, 2:00 AM
   - Action: Start a program → pilih `sync_moodle.bat`

**Linux (Crontab):**

```bash
# Edit crontab
crontab -e

# Tambahkan (sync setiap hari jam 2 pagi)
0 2 * * * curl -s "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all" > /dev/null 2>&1
```

---

## 📊 Monitoring

### Via Dashboard

Akses: http://localhost/siakad/public/baak/moodle/

**Statistics Cards:**
- Lihat jumlah mahasiswa, dosen, courses yang ter-sync
- Cek last sync time

**Recent Logs:**
- Scroll ke bawah untuk melihat log aktivitas
- Klik "View" untuk detail hasil sync

### Via Database

```sql
-- Lihat mapping
SELECT * FROM moodle_sync_mapping ORDER BY last_sync DESC LIMIT 20;

-- Lihat log
SELECT * FROM moodle_sync_log ORDER BY created_at DESC LIMIT 20;

-- Statistik
SELECT type, COUNT(*) as total 
FROM moodle_sync_mapping 
GROUP BY type;
```

---

## ❓ Troubleshooting Cepat

### Problem: "Connection failed"

**Solusi:**
1. Cek apakah Moodle bisa diakses: https://learning.poltekindonusa.ac.id
2. Cek token di config sudah benar
3. Cek web service di Moodle sudah enabled
4. Test manual:
```bash
curl "https://learning.poltekindonusa.ac.id/webservice/rest/server.php?wstoken=YOUR_TOKEN&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json"
```

### Problem: "User creation failed"

**Solusi:**
1. Cek log error di `logs/moodle_error.log`
2. Pastikan user belum ada di Moodle
3. Cek email tidak duplikat
4. Sistem akan auto-update jika user sudah ada

### Problem: Sync lambat

**Solusi:**
1. Gunakan batch sync dengan limit:
```bash
curl "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_mahasiswa&limit=50"
```
2. Jalankan sync di waktu sepi (malam hari)
3. Gunakan cron job untuk auto sync

---

## 🔐 Security Checklist

- [ ] Ganti default API key di `public/baak/moodle/api.php`
- [ ] Simpan Moodle token di tempat aman (jangan commit ke Git)
- [ ] Batasi akses dashboard hanya untuk admin
- [ ] Enable HTTPS untuk production
- [ ] Backup database secara berkala

---

## 📞 Butuh Bantuan?

**Dokumentasi Lengkap:**
- Baca `MOODLE_INTEGRATION.md` untuk detail lengkap

**Test Script:**
```bash
php ws/test_moodle.php
```

**Check Logs:**
```bash
# Error log
tail -f logs/moodle_error.log

# Debug log
tail -f logs/moodle_debug.log
```

**Database Check:**
```sql
-- Cek konfigurasi
SELECT * FROM moodle_config;

-- Cek mapping terakhir
SELECT * FROM moodle_sync_mapping ORDER BY last_sync DESC LIMIT 10;

-- Cek log error
SELECT * FROM moodle_sync_log WHERE status = 'failed' ORDER BY created_at DESC;
```

---

## ✅ Checklist Setup

- [ ] Database migration sudah dijalankan
- [ ] Web service di Moodle sudah enabled
- [ ] Token sudah dikonfigurasi
- [ ] Test connection berhasil
- [ ] Sync pertama berhasil (test 10 data)
- [ ] Verifikasi data di Moodle
- [ ] Setup cron job untuk auto sync
- [ ] Ganti default API key
- [ ] Backup database

---

## 🎉 Selamat!

Sistem integrasi SIAKAD-Moodle sudah siap digunakan!

**Next Steps:**
1. Sync semua data mahasiswa dan dosen
2. Sync mata kuliah dan enrollment
3. Setup auto sync via cron
4. Monitor secara berkala via dashboard

**Tips:**
- Lakukan sync pertama kali di waktu sepi
- Monitor log untuk memastikan tidak ada error
- Backup database sebelum sync besar-besaran
- Test dulu dengan data kecil (limit=10)
