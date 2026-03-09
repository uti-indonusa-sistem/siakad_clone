# 🔄 SIAKAD - Moodle Integration System

Sistem integrasi otomatis antara SIAKAD Politeknik Indonusa dengan Moodle Learning Management System.

## 📋 Overview

Sistem ini menyinkronkan data dari SIAKAD ke Moodle secara otomatis:

- ✅ **Mahasiswa** → Moodle Users (Student Role)
- ✅ **Dosen** → Moodle Users (Teacher Role)
- ✅ **Mata Kuliah** → Moodle Courses
- ✅ **Kelas Kuliah** → Course Enrolments
- ✅ **Program Studi** → Course Categories

## 🎯 Fitur Utama

### Sinkronisasi Data
- Sync mahasiswa dengan username NIM
- Sync dosen dengan username NIDN
- Auto-generate email: `nim@poltekindonusa.ac.id`
- Password default menggunakan NIM/NIDN
- Mapping otomatis antara SIAKAD dan Moodle ID

### Dashboard Monitoring
- Real-time connection status
- Statistics cards (mahasiswa, dosen, courses synced)
- Recent sync logs dengan detail
- Configuration management
- Manual trigger untuk sync

### API Endpoint
- RESTful API untuk automation
- Support untuk cron job
- Batch processing dengan limit
- API key authentication

### Logging & Audit
- Database logging untuk semua aktivitas
- File logging untuk debug
- Error tracking dan reporting
- Sync history dengan timestamp

## 🏗️ Arsitektur

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   SIAKAD    │────────▶│  Middleware  │────────▶│   Moodle    │
│  Database   │         │  API Layer   │         │ Web Service │
└─────────────┘         └──────────────┘         └─────────────┘
                               │
                               ▼
                        ┌──────────────┐
                        │  Sync Logs   │
                        │   Mapping    │
                        └──────────────┘
```

## 📁 Struktur File

```
siakad/
├── config/
│   └── moodle_config.php          # Konfigurasi Moodle
├── ws/
│   ├── MoodleWebService.php       # Client API Moodle
│   ├── MoodleSyncService.php      # Service layer sync
│   └── test_moodle.php            # Test script
├── public/baak/moodle/
│   ├── index.php                  # Dashboard
│   └── api.php                    # API endpoint
├── database/
│   └── migration_moodle_integration.sql  # Database schema
├── logs/
│   ├── moodle_debug.log           # Debug log
│   └── moodle_error.log           # Error log
├── MOODLE_INTEGRATION.md          # Dokumentasi lengkap
├── QUICK_START_MOODLE.md          # Quick start guide
└── README_MOODLE.md               # File ini
```

## 🚀 Quick Start

### 1. Install Database

```bash
mysql -u usiakad -p siakaddb < database/migration_moodle_integration.sql
```

### 2. Konfigurasi Moodle

- Enable Web Services di Moodle
- Generate token untuk web service
- Copy token

### 3. Konfigurasi SIAKAD

Edit `config/moodle_config.php`:

```php
define('MOODLE_TOKEN', 'your_token_here');
```

### 4. Test Connection

```bash
php ws/test_moodle.php
```

### 5. Akses Dashboard

```
http://localhost/siakad/public/baak/moodle/
```

## 📖 Dokumentasi

- **[Quick Start Guide](QUICK_START_MOODLE.md)** - Setup dalam 5 menit
- **[Full Documentation](MOODLE_INTEGRATION.md)** - Dokumentasi lengkap
- **[Moodle Docs](https://docs.moodle.org/dev/Web_services)** - Moodle Web Services

## 🔧 Penggunaan

### Manual Sync (via Dashboard)

1. Akses dashboard: `http://localhost/siakad/public/baak/moodle/`
2. Klik tombol sync yang diinginkan:
   - Sync Mahasiswa
   - Sync Dosen
   - Sync Courses
   - Sync Enrolments
   - Sync All

### Auto Sync (via API)

```bash
# Sync all data
curl "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all"

# Sync mahasiswa (limited)
curl "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_mahasiswa&limit=50"

# Get statistics
curl "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=stats"
```

### Cron Job Setup

**Linux:**
```bash
# Sync setiap hari jam 2 pagi
0 2 * * * curl -s "http://localhost/siakad/public/baak/moodle/api.php?api_key=siakad_moodle_sync_2026&action=sync_all" > /dev/null 2>&1
```

**Windows Task Scheduler:**
- Buat file `sync_moodle.bat`
- Setup scheduled task untuk menjalankan file tersebut

## 📊 Database Tables

### moodle_sync_mapping
Menyimpan mapping antara SIAKAD ID dan Moodle ID

| Field | Type | Description |
|-------|------|-------------|
| type | enum | mahasiswa, dosen, course, category |
| siakad_id | varchar | NIM/NIDN/Kode MK |
| moodle_id | int | Moodle entity ID |
| last_sync | datetime | Waktu sync terakhir |

### moodle_sync_log
Log aktivitas sinkronisasi

| Field | Type | Description |
|-------|------|-------------|
| action | varchar | Jenis aksi sync |
| results | text | JSON hasil sync |
| status | enum | success, failed, partial |
| created_at | timestamp | Waktu log dibuat |

### moodle_config
Konfigurasi sistem

| Field | Type | Description |
|-------|------|-------------|
| config_key | varchar | Nama konfigurasi |
| config_value | text | Nilai konfigurasi |
| description | text | Deskripsi |

## 🔐 Security

### API Key
Default API key: `siakad_moodle_sync_2026`

**⚠️ PENTING:** Ganti API key di production!

Edit `public/baak/moodle/api.php`:
```php
$validApiKey = 'your_secure_api_key_here';
```

### Moodle Token
- Jangan commit token ke Git
- Simpan di environment variable
- Rotate secara berkala

### Password
- Default: menggunakan NIM/NIDN
- Implementasi password hashing
- Force change on first login (di Moodle)

## 🐛 Troubleshooting

### Connection Failed
```bash
# Test manual
curl "https://learning.poltekindonusa.ac.id/webservice/rest/server.php?wstoken=YOUR_TOKEN&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json"
```

### Check Logs
```bash
# Error log
tail -f logs/moodle_error.log

# Debug log
tail -f logs/moodle_debug.log
```

### Database Check
```sql
-- Cek mapping
SELECT * FROM moodle_sync_mapping ORDER BY last_sync DESC LIMIT 10;

-- Cek log error
SELECT * FROM moodle_sync_log WHERE status = 'failed' ORDER BY created_at DESC;
```

## 📈 Performance Tips

1. **Batch Processing**
   - Gunakan parameter `limit` untuk sync bertahap
   - Contoh: `?action=sync_mahasiswa&limit=50`

2. **Scheduling**
   - Jalankan sync di off-peak hours (malam hari)
   - Gunakan cron job untuk automation

3. **Monitoring**
   - Monitor logs secara berkala
   - Check error rate di dashboard
   - Setup alert untuk failed sync

## 🔄 Update & Maintenance

### Update Configuration
Via dashboard atau database:
```sql
UPDATE moodle_config 
SET config_value = 'new_value' 
WHERE config_key = 'config_name';
```

### Clear Logs
```sql
-- Clear old logs (older than 30 days)
DELETE FROM moodle_sync_log 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Backup
```bash
# Backup mapping table
mysqldump -u usiakad -p siakaddb moodle_sync_mapping > backup_mapping.sql

# Backup all moodle tables
mysqldump -u usiakad -p siakaddb moodle_sync_mapping moodle_sync_log moodle_config > backup_moodle.sql
```

## 📞 Support

- **Email:** support@poltekindonusa.ac.id
- **Documentation:** [MOODLE_INTEGRATION.md](MOODLE_INTEGRATION.md)
- **Moodle Docs:** https://docs.moodle.org/

## 📄 License

Copyright © 2026 Politeknik Indonusa Surakarta

## 🙏 Credits

Developed by: Politeknik Indonusa Surakarta IT Team  
Version: 1.0.0  
Date: 2026-01-21

---

**Happy Syncing! 🚀**
