# 🔍 Troubleshooting Moodle Connection Error

## Status Saat Ini
✅ Dashboard berhasil diakses  
❌ Connection error ke Moodle

## Langkah Troubleshooting

### 1. Cek Token Moodle

Token yang dikonfigurasi: `4be2211ea385dea733c825d9ce53978e`

**Verifikasi di Moodle:**
1. Login ke https://learning.poltekindonusa.ac.id sebagai admin
2. Buka: **Site administration → Plugins → Web services → Manage tokens**
3. Cari token: `4be2211ea385dea733c825d9ce53978e`
4. Pastikan:
   - ✅ Token aktif (enabled)
   - ✅ User yang terkait memiliki capability yang benar
   - ✅ Service yang dipilih memiliki semua function yang diperlukan

### 2. Cek Web Services di Moodle

**Enable Web Services:**
1. **Site administration → Advanced features**
   - ✅ Centang "Enable web services"
   - Klik "Save changes"

2. **Site administration → Plugins → Web services → Manage protocols**
   - ✅ Enable "REST protocol"

### 3. Test Connection Manual

**Via Browser:**
```
https://learning.poltekindonusa.ac.id/webservice/rest/server.php?wstoken=4be2211ea385dea733c825d9ce53978e&wsfunction=core_webservice_get_site_info&moodlewsrestformat=json
```

**Expected Response (jika berhasil):**
```json
{
  "sitename": "Learning Politeknik Indonusa",
  "username": "...",
  "userid": 123,
  ...
}
```

**Error Response (jika gagal):**
```json
{
  "exception": "...",
  "errorcode": "...",
  "message": "..."
}
```

### 4. Kemungkinan Masalah & Solusi

#### A. Token Tidak Valid
**Gejala:** Error "Invalid token"

**Solusi:**
1. Generate token baru di Moodle
2. Update di `config/moodle_config.php`:
   ```php
   define('MOODLE_TOKEN', 'TOKEN_BARU_DARI_MOODLE');
   ```

#### B. Web Services Belum Enabled
**Gejala:** Error "Web services are not enabled"

**Solusi:**
1. Site administration → Advanced features
2. Enable "Web services"
3. Save changes

#### C. Function Tidak Tersedia
**Gejala:** Error "The function ... does not exist"

**Solusi:**
1. Site administration → Plugins → Web services → External services
2. Pilih service yang digunakan
3. Tambahkan function yang diperlukan:
   - `core_webservice_get_site_info`
   - `core_user_create_users`
   - `core_user_update_users`
   - `core_user_get_users`
   - `core_course_create_courses`
   - `core_course_update_courses`
   - `core_course_get_courses_by_field`
   - `core_course_create_categories`
   - `core_course_get_categories`
   - `enrol_manual_enrol_users`
   - `enrol_manual_unenrol_users`
   - `core_enrol_get_enrolled_users`

#### D. User Tidak Memiliki Capability
**Gejala:** Error "You are not allowed to use this function"

**Solusi:**
1. Site administration → Users → Permissions → Define roles
2. Buat role "Web Service SIAKAD" dengan capabilities:
   - `moodle/user:create`
   - `moodle/user:update`
   - `moodle/user:viewdetails`
   - `moodle/course:create`
   - `moodle/course:update`
   - `moodle/course:view`
   - `moodle/category:manage`
   - `enrol/manual:enrol`
   - `enrol/manual:unenrol`
   - `webservice/rest:use`
3. Assign role ke user yang terkait dengan token

#### E. HTTPS/SSL Issue
**Gejala:** cURL error atau SSL certificate problem

**Solusi:**
Sementara untuk testing, sudah di-handle di code dengan:
```php
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
```

### 5. Cek Log Error

**Di Server:**
```bash
# Cek log Moodle
tail -f /var/116/indonusa_siakad/logs/moodle_error.log

# Atau cek PHP error log
tail -f /var/log/php/error.log
```

**Di Browser:**
1. Buka Developer Tools (F12)
2. Tab "Console"
3. Lihat error message

### 6. Test via API Endpoint

```bash
curl "https://siakadv2.poltekindonusa.ac.id/baak/moodle/api?api_key=siakad_moodle_sync_2026&action=test"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "sitename": "...",
    "username": "...",
    "userid": 123
  }
}
```

### 7. Quick Fix Checklist

- [ ] Web services enabled di Moodle
- [ ] REST protocol enabled
- [ ] Token valid dan aktif
- [ ] User memiliki capabilities yang benar
- [ ] Service memiliki semua functions yang diperlukan
- [ ] URL Moodle benar: `https://learning.poltekindonusa.ac.id`
- [ ] Database migration sudah dijalankan

### 8. Jika Masih Error

**Cek detail error di dashboard:**
1. Buka browser Developer Tools (F12)
2. Tab "Network"
3. Refresh halaman
4. Klik request "test_connection"
5. Lihat Response

**Atau test manual:**
```php
<?php
// Test file: test_moodle_connection.php
$token = '4be2211ea385dea733c825d9ce53978e';
$url = 'https://learning.poltekindonusa.ac.id/webservice/rest/server.php';

$params = [
    'wstoken' => $token,
    'wsfunction' => 'core_webservice_get_site_info',
    'moodlewsrestformat' => 'json'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

echo "Response: " . $response . "\n";
echo "Error: " . $error . "\n";
?>
```

## Next Steps

1. **Test connection manual** via browser dengan URL di atas
2. **Cek response** - apakah berhasil atau ada error?
3. **Share error message** jika ada, untuk troubleshooting lebih lanjut
4. **Verify token** di Moodle admin panel

---

**Need Help?**
Share screenshot atau error message yang muncul untuk bantuan lebih lanjut.
