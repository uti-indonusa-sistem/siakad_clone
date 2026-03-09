<?php
/**
 * KTM (Kartu Tanda Mahasiswa) PDF Generator
 * 
 * Integrated into Siakad MHS Portal
 * Based on kartu_ujian_pdf.php pattern
 * 
 * URL: /mhs/sopingi/ktm_pdf/cetak/{key}
 * URL View: /mhs/sopingi/ktm_pdf/view/{key}/{nim_md5}
 */

error_reporting(0);

if (!isset($key)) {
    exit();
}

include 'login_auth.php';

if ($key != $_SESSION['wsiaMHS']) {
    exit();
}

// Session Data
$nipd = $_SESSION['nipd'];
$nm_pd = $_SESSION['nm_pd'];
$xid_reg_pd = $_SESSION['xid_reg_pd'];
$jenis_daftar = $_SESSION['jenis_daftar'];

// Get database connection
try {
    $db = koneksi();
    
    // For view action with NIM parameter
    if ($aksi == "view" && !empty($id)) {
        $nim_md5 = $id;
        
        // Query with MD5 hash of NIPD
        $qryMhs = "SELECT 
            m.nm_pd,
            m.tmpt_lahir,
            mp.nipd,
            a.nm_agama,
            m.jk,
            DATE_FORMAT(m.tgl_lahir, '%d-%m-%Y') AS tgl_lahir,
            m.jln,
            m.rt,
            m.rw,
            m.nm_dsn,
            m.ds_kel,
            w.value,
            mp.id_jns_daftar,
            mp.mulai_smt
        FROM 
            wsia_mahasiswa m
        LEFT JOIN 
            wsia_mahasiswa_pt mp ON m.xid_pd = mp.id_pd
        LEFT JOIN 
            wsia_agama a ON m.id_agama = a.id_agama
        LEFT JOIN 
            siakad_wilayah w ON m.id_wil = w.id
        WHERE 
            MD5(mp.nipd) = :nim_md5
        LIMIT 1";
        
        $stmt = $db->prepare($qryMhs);
        $stmt->execute([':nim_md5' => $nim_md5]);
        $dataMhs = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$dataMhs) {
            exit("Data mahasiswa tidak ditemukan.");
        }
        
        $nipd = $dataMhs->nipd;
        $nm_pd = $dataMhs->nm_pd;
        $jenis_daftar = $dataMhs->id_jns_daftar;
        
    } else {
        // Query for logged in user
        $qryMhs = "SELECT 
            m.nm_pd,
            m.tmpt_lahir,
            mp.nipd,
            a.nm_agama,
            m.jk,
            DATE_FORMAT(m.tgl_lahir, '%d-%m-%Y') AS tgl_lahir,
            m.jln,
            m.rt,
            m.rw,
            m.nm_dsn,
            m.ds_kel,
            w.value,
            mp.id_jns_daftar,
            mp.mulai_smt
        FROM 
            wsia_mahasiswa m
        LEFT JOIN 
            wsia_mahasiswa_pt mp ON m.xid_pd = mp.id_pd
        LEFT JOIN 
            wsia_agama a ON m.id_agama = a.id_agama
        LEFT JOIN 
            siakad_wilayah w ON m.id_wil = w.id
        WHERE 
            mp.nipd = :nipd
        LIMIT 1";
        
        $stmt = $db->prepare($qryMhs);
        $stmt->execute([':nipd' => $nipd]);
        $dataMhs = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$dataMhs) {
            exit("Data mahasiswa tidak ditemukan.");
        }
    }
    
} catch (PDOException $e) {
    exit("Database Error: " . $e->getMessage());
}

// Helper function to get prodi name from NIM prefix
function getProdiName($nipd) {
    $prefix = substr($nipd, 0, 1);
    $prodis = [
        'A' => 'Teknologi Otomotif',
        'B' => 'Sistem Informasi',
        'C' => 'Komunikasi Massa',
        'D' => 'Perhotelan',
        'E' => 'Farmasi',
        'F' => 'Manajemen Informasi Kesehatan',
        'G' => 'Teknologi Laboratorium Medis',
        'H' => 'Bisnis Manajemen Ritel'
    ];
    return $prodis[$prefix] ?? '-';
}

// Helper function to get gender text
function getGenderText($jk) {
    return ($jk == 'P') ? 'Perempuan' : (($jk == 'L') ? 'Laki-laki' : '-');
}

// Format tempat lahir
$tp_lahir = ucwords(strtolower($dataMhs->tmpt_lahir ?? ''));
$nama_prodi = getProdiName($dataMhs->nipd);
$jenis_kelamin = getGenderText($dataMhs->jk);

// Build alamat
$alamat = "";
if (!empty($dataMhs->jln) && $dataMhs->jln != '-') {
    $alamat = $dataMhs->jln;
}

if (!empty($dataMhs->nm_dsn)) {
    $namadusun = ucfirst(strtolower($dataMhs->nm_dsn));
    $alamat .= ($alamat == '') ? $namadusun : ", " . $namadusun;
}

if (!empty($dataMhs->rt) && $dataMhs->rt != '0') {
    if ($alamat == '') {
        $alamat .= "RT " . $dataMhs->rt;
    } else {
        $alamat .= (!empty($dataMhs->nm_dsn)) ? " RT " . $dataMhs->rt : ", RT " . $dataMhs->rt;
    }
}

if (!empty($dataMhs->rw) && $dataMhs->rw != '0') {
    if ($alamat == '') {
        $alamat .= "RW " . $dataMhs->rw;
    } else {
        $alamat .= (!empty($dataMhs->rt) && $dataMhs->rt != '0') ? "/RW " . $dataMhs->rw : ", RW " . $dataMhs->rw;
    }
}

if (!empty($dataMhs->ds_kel)) {
    $namakelurahan = ucfirst(strtolower($dataMhs->ds_kel));
    $alamat .= ($alamat == '') ? $namakelurahan : ", " . $namakelurahan;
}

if (!empty($dataMhs->value)) {
    $alamat .= ($alamat == '') ? $dataMhs->value : ", " . $dataMhs->value;
}

// Clean up alamat
$alamat = str_replace([' - Indonesia', ' - Prov.', ' - Kab.', ' - Kota.', ', -, tidak ada'], ['', ',', ', Kab', ', Kota', '-'], $alamat);

// Calculate masa berlaku
$angkatan = substr($dataMhs->nipd, 1, 2);
$prefix = substr($dataMhs->nipd, 0, 1);

if (in_array($prefix, ['F', 'A', 'B', 'C', 'G']) && $dataMhs->id_jns_daftar == '1') {
    $durasikuliah = 4;
} else if ($dataMhs->id_jns_daftar == '2') {
    $durasikuliah = 1;
} else {
    $durasikuliah = 3;
}

$masaberlaku = $angkatan + $durasikuliah;

// Generate QR Code URL for validation
$validation_url = "https://siakadv2.poltekindonusa.ac.id/validasi-ktm.html?nim=" . urlencode(md5($dataMhs->nipd));

/**
 * Simple QR Code Generator using external API
 */
function generateQRCode($data, $size = 100) {
    $encoded_data = urlencode($data);
    
    // Try QuickChart.io first (most reliable)
    $apis = [
        "https://quickchart.io/qr?text=" . $encoded_data . "&size=" . $size . "&margin=1",
        "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . $encoded_data . "&margin=5",
    ];
    
    foreach ($apis as $api_url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $image_data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && $image_data && strlen($image_data) > 100) {
            return 'data:image/png;base64,' . base64_encode($image_data);
        }
    }
    
    // Fallback SVG placeholder
    return 'data:image/svg+xml;base64,' . base64_encode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '">' .
        '<rect width="100%" height="100%" fill="white"/>' .
        '<text x="50%" y="50%" text-anchor="middle" font-size="10" fill="red">QR Error</text>' .
        '</svg>'
    );
}

$qr_code_url = generateQRCode($validation_url, 100);
$foto_url = "https://siakadv2.poltekindonusa.ac.id/mhs/foto/" . md5($dataMhs->nipd) . ".jpg?v=" . time();
$ktm_depan_url = "https://siakadv2.poltekindonusa.ac.id/gambar/ktm_depan.png";
$ktm_belakang_url = "https://siakadv2.poltekindonusa.ac.id/gambar/ktm_belakang.png";

// Render HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>KTM - <?= $dataMhs->nipd ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .ktm-card {
            border: 1px solid black;
            width: 318px;
            height: 209px;
            background-size: contain;
            background-repeat: no-repeat;
            margin: 10px auto;
            position: relative;
            overflow: hidden;
        }
        
        .ktm-depan {
            background-image: url('<?= $ktm_depan_url ?>');
        }
        
        .ktm-belakang {
            background-image: url('<?= $ktm_belakang_url ?>');
        }
        
        .foto-container {
            position: absolute;
            top: 58px;
            left: 23px;
        }
        
        .foto-container img {
            width: 60px;
            height: 78px;
            border: 2px solid white;
            object-fit: cover;
        }
        
        .info-container {
            position: absolute;
            top: 70px;
            left: 99px;
            width: 180px;
            margin-top: 5px;
        }
        
        .info-name {
            font-size: 18px;
            font-weight: bold;
        }

        .info-nim {
            font-size: 12px;
        }

        .info-prodi {
            font-size: 12px;
        }

        .info-table {
            border-collapse: collapse;
            font-size: 8px;
        }
        
        .info-table td {
            padding: 1px 2px;
            vertical-align: top;
        }
        
        .qr-container {
            position: absolute;
            bottom: 10px;
            left: 5px;
        }
        
        .qr-container img {
            width: 40px;
            height: 40px;
            border: 1px solid white;
        }
        
        .ketentuan {
            padding: 35px 30px 10px 30px;
            font-size: 8px;
            text-align: justify;
        }
        
        .ketentuan-title {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .ketentuan-list {
            margin: 0;
            padding-left: 15px;
        }
        
        .ketentuan-list li {
            margin-bottom: 3px;
        }
        
        .masa-berlaku {
            margin-top: 5px;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; font-size: 14px;">
            🖨️ Cetak KTM / Simpan PDF
        </button>
    </div>
    
    <div class="container">
        <!-- KTM DEPAN -->
        <div class="ktm-card ktm-depan">
            <div class="foto-container">
                <img src="<?= $foto_url ?>" alt="Foto" onerror="this.src='https://siakadv2.poltekindonusa.ac.id/gambar/no-foto.jpg';">
            </div>
            
            <div class="info-container">
                <span class="info-name"><?= ucwords(strtolower($dataMhs->nm_pd)) ?></span>
                <br>
                <span class="info-nim">NIM. <?= $dataMhs->nipd ?></span>
                <br>
                <span class="info-prodi"><?= $nama_prodi ?></span>
            </div>
            
            <div class="qr-container">
                <img src="<?= $qr_code_url ?>" alt="QR Code">
            </div>
        </div>
        
        <!-- KTM BELAKANG -->
        <div class="ktm-card ktm-belakang"></div>
    </div>
    
    <script>
        window.onload = function() {
            // Auto print when loaded
            // window.print();
        }
    </script>
</body>
</html>
