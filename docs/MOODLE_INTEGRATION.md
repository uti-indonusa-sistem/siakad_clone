# Dokumentasi Integrasi SIAKAD - Moodle

## 📋 Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Instalasi](#instalasi)
4. [Konfigurasi Moodle](#konfigurasi-moodle)
5. [Konfigurasi SIAKAD](#konfigurasi-siakad)
6. [Penggunaan](#penggunaan)
7. [API Reference](#api-reference)
8. [Troubleshooting](#troubleshooting)

---

## 📖 Pendahuluan

Sistem integrasi ini menghubungkan SIAKAD dengan Moodle Learning Management System (LMS) untuk sinkronisasi otomatis data:
- **Mahasiswa** (akun dan akses)
- **Dosen** (akun dan akses)
- **Mata Kuliah** (courses)
- **Enrollment** (pendaftaran mahasiswa ke mata kuliah)

### Alur Data
```
SIAKAD → API Middleware → Moodle Web Service → Course & Enrolment
```

### Fitur Utama
✅ Sinkronisasi mahasiswa dengan username NIM  
✅ Sinkronisasi dosen dengan username NIDN  
✅ Email otomatis: `nim@poltekindonusa.ac.id` atau `nidn@poltekindonusa.ac.id`  
✅ Password sync (default: menggunakan NIM/NIDN)  
✅ Sinkronisasi mata kuliah berdasarkan prodi  
✅ Auto-enrollment mahasiswa ke kelas  
✅ Dashboard monitoring real-time  
✅ Logging lengkap untuk audit  

---

## 🏗️ Arsitektur Sistem

### Komponen Utama

1. **MoodleWebService.php**
   - Client untuk komunikasi dengan Moodle Web Service API
   - Handle semua operasi CRUD (Create, Read, Update, Delete)
   - Error handling dan logging

2. **MoodleSyncService.php**
   - Service layer untuk orchestrate sinkronisasi
   - Mapping data SIAKAD ke format Moodle
   - Batch processing untuk performa optimal

3. **Dashboard (index.php)**
   - Interface web untuk monitoring
   - Manual trigger sinkronisasi
   - Konfigurasi sistem

4. **API Endpoint (api.php)**
   - RESTful API untuk trigger otomatis
   - Support untuk cron job
   - API key authentication

### Database Schema

```sql
moodle_sync_mapping     -- Mapping ID SIAKAD ↔ Moodle
moodle_sync_log         -- Log aktivitas sinkronisasi
moodle_config           -- Konfigurasi sistem
moodle_sync_queue       -- Queue untuk async processing
moodle_password_mapping -- Password mapping (encrypted)
```

---

## 🚀 Instalasi

### 1. Persiapan Database

Jalankan migration SQL:

```bash
mysql -u usiakad -p siakaddb < database/migration_moodle_integration.sql
```

Atau import manual melalui phpMyAdmin.

### 2. Verifikasi File

Pastikan struktur file berikut ada:

```
siakad/
├── config/
│   └── moodle_config.php
├── ws/
│   ├── MoodleWebService.php
│   └── MoodleSyncService.php
├── public/baak/moodle/
│   ├── index.php (Dashboard)
│   └── api.php (API Endpoint)
└── database/
    └── migration_moodle_integration.sql
```

### 3. Set Permissions

```bash
chmod 755 public/baak/moodle/
chmod 644 config/moodle_config.php
mkdir -p logs
chmod 777 logs
```

---

## ⚙️ Konfigurasi Moodle

### 1. Enable Web Services

Login ke Moodle sebagai Administrator:

1. **Site administration → Advanced features**
   - ✅ Enable web services

2. **Site administration → Plugins → Web services → Manage protocols**
   - ✅ Enable REST protocol

### 2. Create Web Service User

1. **Site administration → Users → Add a new user**
   - Username: `siakad_sync`
   - Password: (strong password)
   - Email: `siakad@poltekindonusa.ac.id`

### 3. Create Custom Role

1. **Site administration → Users → Permissions → Define roles**
   - Create role: `Web Service SIAKAD`
   - Assign capabilities:
     - `moodle/user:create`
     - `moodle/user:update`
     - `moodle/user:viewdetails`
     - `moodle/course:create`
     - `moodle/course:update`
     - `moodle/course:view`
     - `moodle/course:viewhiddencourses`
     - `moodle/category:manage`
     - `enrol/manual:enrol`
     - `enrol/manual:unenrol`
     - `webservice/rest:use`

2. Assign role ke user `siakad_sync` di System context

### 4. Create External Service

1. **Site administration → Plugins → Web services → External services**
   - Add: `SIAKAD Integration Service`
   - Enabled: ✅
   - Authorized users only: ✅

2. Add functions:
   ```
   core_user_create_users
   core_user_update_users
   core_user_get_users
   core_course_create_courses
   core_course_update_courses
   core_course_get_courses_by_field
   core_course_create_categories
   core_course_get_categories
   core_enrol_get_enrolled_users
   enrol_manual_enrol_users
   enrol_manual_unenrol_users
   core_webservice_get_site_info
   ```

3. Add authorized user: `siakad_sync`

### 5. Generate Token

1. **Site administration → Plugins → Web services → Manage tokens**
   - Add token for user: `siakad_sync`
   - Service: `SIAKAD Integration Service`
   - Copy token (contoh: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`)

### 6. Test Web Service

```bash
curl "https://learning.poltekindonusa.ac.id/webservice/rest/server.php?wstoken=YOUR_TOKEN&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json"
```

---

## 🔧 Konfigurasi SIAKAD

### 1. Update Config File

Edit `config/moodle_config.php`:

```php
define('MOODLE_URL', 'https://learning.poltekindonusa.ac.id');
define('MOODLE_TOKEN', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'); // Token dari Moodle
define('MOODLE_SERVICE', 'SIAKAD Integration Service');
```

### 2. Update Database Config

Via Dashboard atau langsung ke database:

```sql
UPDATE moodle_config 
SET config_value = 'https://learning.poltekindonusa.ac.id' 
WHERE config_key = 'moodle_url';

UPDATE moodle_config 
SET config_value = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6' 
WHERE config_key = 'moodle_token';
```

### 3. Test Connection

Akses dashboard:
```
https://siakad.poltekindonusa.ac.id/baak/moodle/
```

Klik tombol "Test Connection" atau cek status di header.

---

## 📱 Penggunaan

### Via Dashboard (Manual)

1. **Akses Dashboard**
   ```
   https://siakad.poltekindonusa.ac.id/baak/moodle/
   ```

2. **Sync Individual**
   - Klik "Sync Mahasiswa" untuk sync data mahasiswa
   - Klik "Sync Dosen" untuk sync data dosen
   - Klik "Sync Courses" untuk sync mata kuliah
   - Klik "Sync Enrolments" untuk sync enrollment

3. **Sync All**
   - Klik "Sync All Data" untuk sync semua data sekaligus

4. **Monitor Progress**
   - Lihat statistics cards untuk jumlah data yang ter-sync
   - Cek "Recent Sync Logs" untuk detail hasil sync

### Via API (Automated)

#### Test Connection
```bash
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=test"
```

#### Sync Mahasiswa
```bash
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_mahasiswa"
```

#### Sync Mahasiswa (Limited)
```bash
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_mahasiswa&limit=10"
```

#### Sync All
```bash
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all"
```

#### Get Statistics
```bash
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=stats"
```

### Setup Cron Job (Auto Sync)

#### Linux/Unix Cron

Edit crontab:
```bash
crontab -e
```

Tambahkan (sync setiap jam):
```cron
0 * * * * curl -s "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all" > /dev/null 2>&1
```

Atau sync setiap hari jam 2 pagi:
```cron
0 2 * * * curl -s "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all" > /dev/null 2>&1
```

#### Windows Task Scheduler

1. Buat file `sync_moodle.bat`:
```batch
@echo off
curl "https://siakad.poltekindonusa.ac.id/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all"
```

2. Buat scheduled task via Task Scheduler

---

## 📚 API Reference

### Authentication

Semua request memerlukan API key:
```
?api_key=siakad_moodle_sync_2026
```

### Endpoints

#### 1. Test Connection
```
GET /baak/moodle/api.php?action=test&api_key=xxx
```

Response:
```json
{
  "success": true,
  "data": {
    "sitename": "Learning Politeknik Indonusa",
    "username": "siakad_sync",
    "userid": 123
  }
}
```

#### 2. Sync Mahasiswa
```
GET /baak/moodle/api.php?action=sync_mahasiswa&api_key=xxx&limit=50
```

Response:
```json
{
  "total": 50,
  "success": 48,
  "failed": 2,
  "created": 30,
  "updated": 18,
  "errors": [
    {
      "nim": "2021001",
      "error": "Email already exists"
    }
  ]
}
```

#### 3. Sync Dosen
```
GET /baak/moodle/api.php?action=sync_dosen&api_key=xxx
```

#### 4. Sync Courses
```
GET /baak/moodle/api.php?action=sync_courses&api_key=xxx
```

#### 5. Sync Enrolments
```
GET /baak/moodle/api.php?action=sync_enrolments&api_key=xxx
```

#### 6. Sync All
```
GET /baak/moodle/api.php?action=sync_all&api_key=xxx
```

Response:
```json
{
  "mahasiswa": {
    "total": 500,
    "success": 495,
    "failed": 5
  },
  "dosen": {
    "total": 50,
    "success": 50,
    "failed": 0
  },
  "courses": {
    "total": 100,
    "success": 98,
    "failed": 2
  },
  "enrolments": {
    "total": 1500,
    "success": 1490,
    "failed": 10
  }
}
```

#### 7. Get Statistics
```
GET /baak/moodle/api.php?action=stats&api_key=xxx
```

Response:
```json
{
  "success": true,
  "data": {
    "mahasiswa": 500,
    "dosen": 50,
    "course": 100,
    "category": 10,
    "last_sync": "2026-01-21 16:30:00"
  }
}
```

---

## 🔍 Troubleshooting

### 1. Connection Failed

**Problem:** Dashboard menampilkan "Connection failed"

**Solution:**
- Cek apakah Moodle URL benar
- Cek apakah token valid
- Cek apakah web service enabled di Moodle
- Test manual dengan curl:
  ```bash
  curl "https://learning.poltekindonusa.ac.id/webservice/rest/server.php?wstoken=YOUR_TOKEN&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json"
  ```

### 2. User Creation Failed

**Problem:** Error "Email already exists"

**Solution:**
- Cek apakah email sudah digunakan di Moodle
- Update user existing daripada create new
- Sistem akan otomatis update jika user sudah ada

### 3. Course Creation Failed

**Problem:** Error "Category not found"

**Solution:**
- Pastikan kategori (prodi) sudah dibuat di Moodle
- Jalankan sync courses yang akan otomatis create categories
- Atau manual create category di Moodle

### 4. Enrolment Failed

**Problem:** Error "Course not found"

**Solution:**
- Pastikan course sudah di-sync terlebih dahulu
- Jalankan sync courses sebelum sync enrolments
- Cek mapping di tabel `moodle_sync_mapping`

### 5. Slow Sync Performance

**Problem:** Sync memakan waktu lama

**Solution:**
- Gunakan parameter `limit` untuk batch processing
- Contoh: `?action=sync_mahasiswa&limit=50`
- Jalankan sync di off-peak hours
- Tingkatkan `SYNC_TIMEOUT` di config

### 6. Permission Denied

**Problem:** Error "Permission denied" saat create/update

**Solution:**
- Cek role dan capabilities user web service di Moodle
- Pastikan semua required capabilities sudah di-assign
- Cek context assignment (harus di System context)

### 7. Database Error

**Problem:** Error saat save mapping

**Solution:**
- Cek apakah migration SQL sudah dijalankan
- Verify tabel `moodle_sync_mapping` exists
- Cek database permissions

---

## 📊 Monitoring & Logging

### Log Files

1. **Debug Log**
   ```
   logs/moodle_debug.log
   ```
   Berisi detail request/response ke Moodle

2. **Error Log**
   ```
   logs/moodle_error.log
   ```
   Berisi error yang terjadi

### Database Logs

Query untuk melihat log:

```sql
-- Recent sync activities
SELECT * FROM moodle_sync_log 
ORDER BY created_at DESC 
LIMIT 20;

-- Failed syncs
SELECT * FROM moodle_sync_log 
WHERE status = 'failed' 
ORDER BY created_at DESC;

-- Sync statistics by action
SELECT action, COUNT(*) as count, 
       SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count
FROM moodle_sync_log 
GROUP BY action;
```

---

## 🔐 Security Best Practices

1. **API Key**
   - Ganti default API key di `api.php`
   - Simpan di environment variable atau config terpisah
   - Rotate secara berkala

2. **Moodle Token**
   - Jangan commit token ke Git
   - Simpan di environment variable
   - Regenerate jika terjadi security breach

3. **Password**
   - Default password menggunakan NIM/NIDN
   - Implementasi password hashing
   - Force password change on first login (di Moodle)

4. **HTTPS**
   - Pastikan semua komunikasi via HTTPS
   - Enable SSL certificate validation

5. **Access Control**
   - Batasi akses dashboard hanya untuk admin
   - Implement proper session management
   - Add IP whitelist untuk API

---

## 📝 Changelog

### Version 1.0.0 (2026-01-21)
- ✅ Initial release
- ✅ Sync mahasiswa, dosen, courses, enrolments
- ✅ Dashboard monitoring
- ✅ API endpoint
- ✅ Logging system
- ✅ Email generation (nim@poltekindonusa.ac.id)

---

## 🤝 Support

Untuk bantuan lebih lanjut:
- Email: support@poltekindonusa.ac.id
- Dokumentasi Moodle: https://docs.moodle.org/
- Moodle Web Services: https://docs.moodle.org/dev/Web_services

---

## 📄 License

Copyright © 2026 Politeknik Indonusa Surakarta
