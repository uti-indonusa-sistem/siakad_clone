# Perubahan Download Sebaran Nilai

## Ringkasan Perubahan
Fitur download sebaran nilai telah diperbaiki dari format CSV menjadi format XLSX (Excel) yang benar dengan kolom terpisah.

## Detail Perubahan

### File yang Dimodifikasi:

1. **`public/monitoring/data_sebaran.php`**
   - Menambahkan `require_once '../vendor/autoload.php'` untuk autoload PhpSpreadsheet
   - Menambahkan `use PhpOffice\PhpSpreadsheet\Spreadsheet;` dan `use PhpOffice\PhpSpreadsheet\Writer\Xlsx;`
   - Mengubah fungsi download dari CSV (menggunakan `fputcsv()`) menjadi XLSX (menggunakan PhpSpreadsheet)
   
2. **`public/monitoring/dashboard.php`**
   - Mengubah label tombol download dari "CSV" menjadi "XLSX"

### Fitur Baru:

✅ **Format File:** Download menghasilkan file `.xlsx` (Excel) yang benar, bukan `.csv`

✅ **Kolom Terpisah:** Setiap data ditempatkan di kolom yang terpisah:
   - Kolom A: NIM
   - Kolom B: Nama Mahasiswa
   - Kolom C: Prodi
   - Kolom D: Kode MK
   - Kolom E: Mata Kuliah
   - Kolom F: Kelas
   - Kolom G: Nilai Huruf
   - Kolom H: Nilai Angka
   - Kolom I: Nilai Indeks
   - Kolom J: Semester Masuk

✅ **Header Styling:** Header dengan background abu-abu dan teks bold

✅ **Auto-size Columns:** Semua kolom secara otomatis menyesuaikan lebar sesuai isi

✅ **Sheet Title:** Sheet diberi nama "Sebaran Nilai"

## Cara Menggunakan:

1. Buka halaman Monitoring → Sebaran Nilai
2. Pilih filter yang diinginkan (Tahun Akademik, Prodi, Mata Kuliah, dll)
3. Klik tombol "Terapkan" untuk melihat data
4. Klik tombol "XLSX" untuk download data dalam format Excel

## Perbedaan dengan Sebelumnya:

### Sebelumnya (CSV):
```
NIM,Nama,Prodi,Kode MK,... (semua dalam 1 baris dengan koma)
12345,"John Doe","TI","MK001",... (data dalam 1 baris teks dengan koma)
```

### Sekarang (XLSX):
```
| NIM   | Nama      | Prodi | Kode MK | ... |
|-------|-----------|-------|---------|-----|
| 12345 | John Doe  | TI    | MK001   | ... |
```

Setiap data sekarang berada di kolom yang terpisah dan dapat langsung diolah di Excel tanpa perlu parsing tambahan.

## Teknologi yang Digunakan:

- **PhpSpreadsheet**: Library untuk membuat dan memanipulasi file Excel di PHP
- Format output: `.xlsx` (Office Open XML Spreadsheet)

---
**Tanggal Update:** 19 Januari 2026
**Developer:** Antigravity AI Assistant
