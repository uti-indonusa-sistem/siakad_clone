# 📚 Strategi Manajemen Course per Semester

## Konsep

Untuk menghindari storage membengkak, kita gunakan strategi:
1. **Course baru tiap semester** dengan naming convention
2. **Archive course lama** setelah semester selesai
3. **Cleanup files** yang tidak diperlukan
4. **Backup data** sebelum archive

## Implementasi

### 1. Naming Convention

**Format Course:**
```
[KODE_MK] - [NAMA_MK] - [TAHUN][SEMESTER]
```

**Contoh:**
- `TI101 - Pemrograman Dasar - 20241` (Ganjil 2024/2025)
- `TI101 - Pemrograman Dasar - 20242` (Genap 2024/2025)
- `TI101 - Pemrograman Dasar - 20243` (Pendek 2024/2025)

**Kode Semester:**
- `1` = Ganjil (Semester 1, 3, 5, 7)
- `2` = Genap (Semester 2, 4, 6, 8)
- `3` = Pendek/Antara

### 2. ID Number Format

**Format:**
```
[KODE_MK]-[TAHUN][SEMESTER]
```

**Contoh:**
- `TI101-20241`
- `TI102-20242`

Ini memastikan setiap course unik per semester.

### 3. Workflow per Semester

#### **Awal Semester (Sync Baru)**
1. Sync mata kuliah dengan semester aktif
2. Course baru dibuat dengan nama + semester
3. Enrollment mahasiswa ke course baru
4. Enrollment dosen ke course baru

#### **Akhir Semester (Archive)**
1. Export nilai ke SIAKAD
2. Hide course lama (tidak delete)
3. Backup course data
4. Cleanup files yang besar (video, dll)

#### **Semester Baru**
1. Course baru dibuat untuk semester baru
2. Template bisa di-copy dari semester lalu
3. Enrollment fresh untuk semester baru

### 4. Keuntungan Strategi Ini

✅ **History Terjaga**
- Mahasiswa bisa akses materi semester lalu
- Nilai tersimpan per semester
- Audit trail lengkap

✅ **Storage Efisien**
- Course lama di-hide
- Files besar di-cleanup
- Hanya course aktif yang visible

✅ **Manajemen Mudah**
- Jelas mana course semester ini
- Mudah filter per semester
- Mudah restore jika perlu

✅ **Fleksibel**
- Mahasiswa mengulang bisa akses course lama
- Dosen bisa review semester lalu
- Admin bisa audit kapan saja

### 5. Cleanup Strategy

**Yang Bisa Dihapus (Setelah Archive):**
- ❌ Video recordings (jika sudah di-backup)
- ❌ Large attachments (jika sudah di-backup)
- ❌ Forum posts (optional, jika tidak penting)
- ❌ Quiz attempts (setelah nilai final)

**Yang Harus Tetap:**
- ✅ Nilai akhir mahasiswa
- ✅ Struktur course
- ✅ Assignment submissions (compressed)
- ✅ Gradebook data

### 6. Automation

**Script Archive Otomatis:**
```php
// Jalankan setiap akhir semester
// Archive course semester lalu
// Cleanup files besar
// Backup ke storage eksternal
```

**Cron Job:**
```bash
# Setiap akhir semester (misal: 31 Januari & 31 Juli)
0 0 31 1,7 * /path/to/archive_courses.php
```

### 7. Moodle Course Management

**Hide Course (Bukan Delete):**
1. Course → Settings
2. Course visibility: **Hide**
3. Course masih ada, tapi tidak visible untuk mahasiswa

**Backup Course:**
1. Course → More → Backup
2. Download backup file (.mbz)
3. Simpan di storage eksternal

**Delete Files:**
1. Course → Files
2. Delete files besar yang tidak diperlukan
3. Keep essential files only

### 8. Recommended Settings

**Moodle Site Settings:**
```
Site administration → Courses → Course default settings

- Course visibility: Hidden (untuk course lama)
- Number of sections: 14 (14 minggu)
- Files and uploads: Limit file size
- Completion tracking: Enabled
```

**Backup Settings:**
```
Site administration → Courses → Backups

- Automated backup: Enabled
- Backup schedule: Weekly
- Keep backups: 2 (last 2 backups only)
- Backup destination: External storage
```

### 9. Storage Optimization

**Database:**
- Cleanup old logs (> 1 year)
- Archive old messages
- Remove deleted users data

**Files:**
- Compress old course files
- Move to cold storage
- Use CDN for static files

**Moodle Data:**
```bash
# Cleanup old cache
php admin/cli/purge_caches.php

# Cleanup old temp files
find /var/moodledata/temp -type f -mtime +7 -delete

# Cleanup old backups
find /var/moodledata/backup -type f -mtime +30 -delete
```

### 10. Best Practices

1. **Naming Convention Konsisten**
   - Selalu gunakan format yang sama
   - Mudah identify semester
   - Mudah filter & search

2. **Archive, Jangan Delete**
   - Course lama di-hide, bukan delete
   - Data tetap ada untuk audit
   - Bisa restore kapan saja

3. **Regular Cleanup**
   - Setiap akhir semester
   - Automated via cron
   - Monitor storage usage

4. **Backup Regular**
   - Weekly automated backup
   - Store di external storage
   - Test restore procedure

5. **Monitor Storage**
   - Dashboard monitoring
   - Alert jika storage > 80%
   - Regular cleanup schedule

## Implementation Code

Saya akan update `MoodleSyncService.php` untuk support naming convention ini.

### Modified Course Sync

```php
private function prepareCourseData($course, $categoryId) {
    // Get current semester
    $semester = $this->getCurrentSemester();
    
    // Format: [KODE] - [NAMA] - [SEMESTER]
    $shortname = $course['kode_mata_kuliah'] . '-' . $semester;
    $fullname = $course['nama_mata_kuliah'] . ' - ' . $this->formatSemester($semester);
    
    // ID Number: KODE-SEMESTER (unique per semester)
    $idnumber = $course['kode_mata_kuliah'] . '-' . $semester;
    
    return [
        'fullname' => $fullname,
        'shortname' => $shortname,
        'categoryid' => $categoryId,
        'idnumber' => $idnumber,
        'summary' => sprintf(
            'Mata Kuliah: %s<br>Kode: %s<br>SKS: %s<br>Semester: %s<br>Program Studi: %s',
            $course['nama_mata_kuliah'],
            $course['kode_mata_kuliah'],
            $course['sks_mata_kuliah'],
            $this->formatSemester($semester),
            $course['nama_program_studi']
        ),
        'numsections' => 14,
        'visible' => 1 // Visible untuk semester aktif
    ];
}

private function getCurrentSemester() {
    // Get from wsia_semester where a_periode_aktif = '1'
    $sql = "SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['id_smt'] ?? date('Y') . '1';
}

private function formatSemester($id_smt) {
    $tahun = substr($id_smt, 0, 4);
    $tahun_next = $tahun + 1;
    $smt = substr($id_smt, 4, 1);
    
    $smt_name = [
        '1' => 'Ganjil',
        '2' => 'Genap',
        '3' => 'Pendek'
    ];
    
    return $tahun . '/' . $tahun_next . ' ' . ($smt_name[$smt] ?? 'Unknown');
}
```

## Summary

**Strategi Terbaik:**
1. ✅ Buat course baru tiap semester dengan naming convention
2. ✅ Archive (hide) course lama, jangan delete
3. ✅ Cleanup files besar setelah semester selesai
4. ✅ Backup regular ke external storage
5. ✅ Monitor storage usage

**Keuntungan:**
- History terjaga
- Storage efisien
- Manajemen mudah
- Audit trail lengkap
- Fleksibel untuk mahasiswa mengulang

Apakah Anda ingin saya implementasikan strategi ini ke dalam code?
