# QR Code Generation - Troubleshooting Guide

## Problem: QR Code tidak muncul di PDF

### Solusi yang sudah dicoba:
1. ✅ Google Charts API → Diganti dengan QR Server API
2. ⏳ QR Server API → Test dulu

### Jika masih tidak muncul:

## Opsi 1: Install PHP QR Code Library (Recommended)

**Download:**
- Library: phpqrcode
- URL: https://github.com/t0k4rt/phpqrcode

**Installation Steps:**

1. Download dan extract ke:
   ```
   d:\Code\INDONUSA\indonusa_siakad\public\lib\phpqrcode\
   ```

2. Include di kartu_ujian_pdf.php:
   ```php
   require_once '../../lib/phpqrcode/qrlib.php';
   
   // Generate QR code
   ob_start();
   QRcode::png($validation_url, false, QR_ECLEVEL_L, 4, 2);
   $qr_code_base64 = base64_encode(ob_get_clean());
   
   // Use in img tag:
   $qr_code_url = 'data:image/png;base64,' . $qr_code_base64;
   ```

## Opsi 2: Use Composer

```bash
cd d:\Code\INDONUSA\indonusa_siakad\public\mhs\api
composer require chillerlan/php-qrcode
```

```php
use chillerlan\QRCode\QRCode;

$qr_code_base64 = (new QRCode)->render($validation_url);
$qr_code_url = $qr_code_base64; // already base64
```

## Opsi 3: Test Current Implementation

**Test URL:**
```
https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://siakadv2.poltekindonusa.ac.id/validasi-kartu-ujian.html?nim=B22023&angkatan=2022&kode_prodi=TI&jenis_daftar=REG&semester=5&tipe=UTS&token=abc123
```

Open di browser - jika muncul QR code, berarti API works.

## Debug Steps:

1. Check if URL generated:
   ```php
   error_log("QR URL: " . $qr_code_url);
   ```

2. Check if image loads:
   - Right click QR area → Inspect
   - Check src attribute
   - Check network tab for 404/errors

3. Test with simple URL first:
   ```php
   $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=test";
   ```

## Current Implementation Status:

✅ Changed to: https://api.qrserver.com/v1/create-qr-code/
⏳ Waiting for test result
🔄 Ready to switch to offline generation if needed
