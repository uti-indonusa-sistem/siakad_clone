# QR Code Validasi Kartu Ujian

## 📋 Deskripsi

Fitur QR Code Validasi Kartu Ujian memungkinkan validasi otomatis kartu ujian mahasiswa melalui scanning QR code yang tercetak pada kartu ujian. Sistem ini memastikan:
- ✅ Data mahasiswa valid
- ✅ Pembayaran sudah lunas
- ✅ Kartu ujian adalah sah dan tidak palsu

## 🎯 Komponen Sistem

### 1. **Kartu Ujian PDF dengan QR Code**
**File**: `public/mhs/api/kartu_ujian_pdf.php`

**Fitur**:
- QR Code otomatis tergenerate di setiap kartu ujian
- Posisi: Samping kanan TTD Wadir I
- Ukuran: 100x100 px (proporsional)
- Generator: Google Charts API (tidak perlu library tambahan)

**URL yang di-encode dalam QR Code**:
```
https://siakadv2.poltekindonusa.ac.id/validasi-kartu-ujian.html?
  nim={NIM}&
  angkatan={ANGKATAN}&
  kode_prodi={KODE_PRODI}&
  jenis_daftar={JENIS_DAFTAR}&
  semester={SEMESTER}&
  tipe={UTS/UAS}
```

### 2. **Halaman Validasi Publik**
**File**: `public/validasi-kartu-ujian.html`

**Karakteristik**:
- ✅ Tidak memerlukan login
- ✅ Akses publik (tanpa kredensial)
- ✅ Responsive design
- ✅ Modern UI dengan gradient & animation
- ✅ Real-time validation via SIKEU API

**Endpoint API yang digunakan**:
```javascript
GET https://sikeu.poltekindonusa.ac.id/ujian/{nim}/{angkatan}/{kode_prodi}/{jenis_daftar}/{semester}/{tipe}
```

### 3. **API Backend (SIKEU)**
**File**: `routes/cek_tagihan_ujian.js` (existing)

**Endpoint**:
```
GET /ujian/:no_pend/:angkatan/:kode_prodi/:jenis_daftar/:semester/:tipe_ujian
```

**Response**:
```json
{
  "status": true,
  "message": "Data Tagihan Syarat Ujian UTS",
  "data": [
    {
      "nama_biaya": "SPP Semester 5",
      "tagihan": 3500000,
      "terbayar": 3500000,
      "potongan": 0,
      "kekurangan": 0,
      "syarat_ujian": "UTS"
    }
  ]
}
```

## 🚀 Cara Penggunaan

### Untuk Mahasiswa:
1. **Cetak Kartu Ujian**
   - Login ke SIAKAD → Menu Kartu Ujian
   - Pilih UTS/UAS
   - Cetak kartu (QR code otomatis ada di kartu)

2. **Verifikasi Validitas**
   - Scan QR code menggunakan smartphone
   - Browser otomatis membuka halaman validasi
   - Sistem menampilkan status validasi secara real-time

### Untuk Pengawas Ujian:
1. Scan QR code pada kartu ujian mahasiswa
2. Lihat status validasi:
   - **HIJAU (✅ VALID)**: Mahasiswa boleh mengikuti ujian
   - **MERAH (❌ INVALID)**: Mahasiswa tidak boleh ujian (ada tunggakan)

## 📊 Alur Validasi

```
[Mahasiswa Scan QR] 
    ↓
[Buka validasi-kartu-ujian.html]
    ↓
[Extract Parameters dari URL]
    ↓
[Call API: /ujian/{params}]
    ↓
[SIKEU: Cek Tagihan]
    ↓
┌─────────────────────────┬─────────────────────────┐
│  Ada Kekurangan > 0     │   Semua Lunas           │
│  ❌ TIDAK VALID          │   ✅ VALID               │
│  - Tampilkan tunggakan  │   - Mahasiswa boleh     │
│  - Total kekurangan     │     mengikuti ujian     │
│  - Info pembayaran      │   - Timestamp validasi  │
└─────────────────────────┴─────────────────────────┘
```

## 💻 Detail Teknis

### QR Code Generation
```php
// Parameter yang di-encode
$validation_url = "https://siakadv2.poltekindonusa.ac.id/validasi-kartu-ujian.html?" . 
                  "nim=" . urlencode($nipd) . 
                  "&angkatan=" . urlencode($angkatan) . 
                  "&kode_prodi=" . urlencode($kode_prodi) . 
                  "&jenis_daftar=" . urlencode($jenis_daftar) . 
                  "&semester=" . urlencode($semester_number) . 
                  "&tipe=" . urlencode($tipe_ujian);

// Generate QR Code via Google Charts API
$qr_code_url = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=" . 
               urlencode($validation_url) . "&choe=UTF-8";
```

### Validation Logic (JavaScript)
```javascript
// 1. Extract parameters
const params = {
  nim: getUrlParameter('nim'),
  angkatan: getUrlParameter('angkatan'),
  kode_prodi: getUrlParameter('kode_prodi'),
  jenis_daftar: getUrlParameter('jenis_daftar'),
  semester: getUrlParameter('semester'),
  tipe: getUrlParameter('tipe')
};

// 2. Call SIKEU API
const apiUrl = `https://sikeu.poltekindonusa.ac.id/ujian/${params.nim}/...`;
const response = await fetch(apiUrl);
const result = await response.json();

// 3. Process result
const tagihanBelumLunas = result.data.filter(item => item.kekurangan > 0);
const isValid = tagihanBelumLunas.length === 0;

// 4. Display result
displayValidationResult({ valid: isValid, ... });
```

### CSS Classes
```css
.status-valid     /* Hijau - Valid */
.status-invalid   /* Merah - Invalid */
.status-warning   /* Kuning - Warning */
.qr-container     /* Container QR code */
.qr-label         /* Label di bawah QR */
```

## 🔒 Keamanan

### Data Protection
- ✅ Data hanya bisa diakses melalui QR code yang valid
- ✅ HTTPS untuk semua komunikasi
- ✅ API menggunakan parameter validation
- ✅ CORS enabled untuk cross-domain request

### No Authentication Required
- Halaman validasi adalah **public** (by design)
- Tidak perlu login untuk validasi
- Data yang ditampilkan: hanya status pembayaran publik

## 🎨 Desain UI

### Color Scheme
- **Primary**: Purple gradient (#667eea → #764ba2)
- **Success**: Green (#38a169)
- **Danger**: Red (#e53e3e)
- **Warning**: Yellow (#856404)

### Responsive
- ✅ Mobile-friendly
- ✅ Desktop-optimized
- ✅ Print-ready (untuk kartu ujian)

## 📱 Browser Compatibility

- ✅ Chrome/Edge (Recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers

## 🐛 Troubleshooting

### QR Code tidak muncul di PDF
**Penyebab**: Google Charts API tidak bisa diakses
**Solusi**: Pastikan server memiliki akses internet

### Validasi gagal
**Penyebab**: 
1. Parameter tidak lengkap
2. SIKEU API down
3. Data mahasiswa tidak ditemukan

**Solusi**:
1. Cek URL parameters
2. Test API endpoint langsung
3. Verifikasi data di database

### CORS Error
**Penyebab**: Cross-origin request blocked
**Solusi**: Pastikan SIKEU `app.js` memiliki `app.use(cors())`

## 📝 Changelog

### v1.0.0 (2025-12-15)
- ✅ Initial implementation
- ✅ QR code generation in exam card
- ✅ Public validation page
- ✅ Integration with existing SIKEU /ujian endpoint
- ✅ Responsive design
- ✅ Error handling

## 👨‍💻 Developer Notes

### Files Modified/Created:
1. ✏️ Modified: `indonusa_siakad/public/mhs/api/kartu_ujian_pdf.php`
   - Added QR code generation
   - Added CSS for QR container
   - Updated layout for TTD section

2. ✨ Created: `indonusa_siakad/public/validasi-kartu-ujian.html`
   - Public validation page
   - Modern UI design
   - Real-time validation

3. ♻️ Reused: `indonusa_sikeu/routes/cek_tagihan_ujian.js`
   - Existing endpoint (no changes needed)

### No Database Changes Required
- ✅ Uses existing tables and views
- ✅ No migration needed

## 🔗 Related Documentation

- [SIKEU API Documentation](../indonusa_sikeu/README.md)
- [Kartu Ujian Flow](./MODULE_KARTU_UJIAN.md)
- [Payment Integration](./PAYMENT_FLOW.md)

## 📞 Support

Untuk pertanyaan atau issue:
- Developer: Politeknik Indonusa IT Team
- Email: support@poltekindonusa.ac.id

---

**Last Updated**: 2025-12-15
**Version**: 1.0.0
