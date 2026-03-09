<?php
/**
 * Kartu Ujian PDF Generator (Template Baru)
 * 
 * Layout: 2 Kolom (Kiri: Info Mhs, Kanan: Daftar Matkul)
 * Header: kop.png
 */

error_reporting(0);

if (!isset($key)) {
    exit();
}

include 'login_auth.php';

if ($key != $_SESSION['wsiaMHS']) {
    exit();
}

// Configuration for SIKEU API
define('SIKEU_API_URL', 'https://sikeu.poltekindonusa.ac.id');

/**
 * Make HTTP request to SIKEU API
 */
function httpRequestSikeu($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SIAKAD-KartuUjian/1.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['success' => false, 'data' => []];
    }
    
    curl_close($ch);
    
    if ($httpCode != 200) {
        return ['success' => false, 'data' => []];
    }
    
    $data = json_decode($output, true);
    return [
        'success' => $data['status'] ?? false,
        'data' => $data['data'] ?? []
    ];
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get exam type from action (UTS or UAS)
$tipe_ujian = strtoupper($aksi);
if (!in_array($tipe_ujian, ['UTS', 'UAS'])) {
    exit('Tipe ujian tidak valid');
}

// Get semester ID
if (isset($id) && strlen($id) == 5) {
    $id_smt = $id;
} else {
    $id_smt = $_SESSION['id_smt_aktif'];
}

if ($id_smt == "-" || empty($id_smt)) {
    exit('Semester aktif tidak ditemukan');
}

// Session Data
$no_pend = $_SESSION['no_pend'];
$angkatan = substr($_SESSION['mulai_smt'], 0, 4);
$kode_prodi = $_SESSION['kode_prodi'];
$jenis_daftar = $_SESSION['jenis_daftar'];
$xid_reg_pd = $_SESSION['xid_reg_pd'];
$nipd = $_SESSION['nipd'];
$nm_pd = $_SESSION['nm_pd'];

// Logic Semester (Semester ke-berapa)
$tahun_mulai = substr($_SESSION['mulai_smt'], 0, 4);
$smt_mulai = substr($_SESSION['mulai_smt'], 4, 1);
$tahun_smt = substr($id_smt, 0, 4);
$smt_smt = substr($id_smt, 4, 1);
$semester_number = (($tahun_smt - $tahun_mulai) * 2) + ($smt_smt - $smt_mulai) + 1;
if ($semester_number < 1) $semester_number = 1;

// === CEK PEMBAYARAN VIA SIKEU ===
$sikeuUrl = SIKEU_API_URL . "/ujian/{$no_pend}/{$angkatan}/{$kode_prodi}/{$jenis_daftar}/{$semester_number}/{$tipe_ujian}";
$tagihanResult = httpRequestSikeu($sikeuUrl);

// Jika Mau Debug Hapus Komentar Di Bawah
// echo '<pre>';
// echo "URL: " . $sikeuUrl . "\n\n";
// echo "Response: ";
// print_r($tagihanResult);
// echo '</pre>';
// die();

$tagihanBelumLunas = [];
$totalKekurangan = 0;

if ($tagihanResult['success'] && !empty($tagihanResult['data'])) {
    foreach ($tagihanResult['data'] as $tagihan) {
        if (isset($tagihan['kekurangan']) && $tagihan['kekurangan'] > 0) {
            $tagihanBelumLunas[] = $tagihan;
            $totalKekurangan += $tagihan['kekurangan'];
        }
    }
}

// --- DATA LOGIC ---
$jenis_ujian_text = ($tipe_ujian == 'UTS') ? 'TENGAH SEMESTER' : 'AKHIR SEMESTER'; 
$smt_kode = substr($id_smt, 4, 1);
$smt_text = ($smt_kode == "1") ? "GANJIL" : (($smt_kode == "2") ? "GENAP" : "PENDEK");

$th_awal = substr($id_smt, 0, 4);
$th_akhir = $th_awal + 1;
$tahun_akademik_text = $th_awal . "/" . $th_akhir;

$keterangan_id = $jenis_ujian_text . " " . $smt_text;

// Fetch Data
try {
    $db = koneksi();
    $qryMhs = "SELECT wmp.*, wm.*, ws.nm_lemb, ws.kode_prodi, wj.nm_jenj_didik 
               FROM wsia_mahasiswa_pt wmp
               JOIN wsia_mahasiswa wm ON wmp.id_pd = wm.xid_pd
               JOIN wsia_sms ws ON wmp.id_sms = ws.xid_sms
               JOIN wsia_jenjang_pendidikan wj ON ws.id_jenj_didik = wj.id_jenj_didik
               WHERE wmp.xid_reg_pd = :xid_reg_pd";
    $stmt = $db->prepare($qryMhs);
    $stmt->execute([':xid_reg_pd' => $xid_reg_pd]);
    $dataMhs = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$dataMhs) exit("Data mahasiswa tidak ditemukan");

    $qryKrs = "SELECT wn.id_nilai, wkk.xid_kls, wkk.nm_kls, wmk.kode_mk, wmk.nm_mk, 
                      wkk.sks_mk, (
                        SELECT CONCAT(IFNULL(wd.gelar_depan,''), ' ', wd.nm_ptk, ', ', IFNULL(wd.gelar_belakang,'')) 
                        FROM wsia_ajar_dosen wad
                        JOIN wsia_dosen_pt wdp ON wad.id_reg_ptk = wdp.xid_reg_ptk
                        JOIN wsia_dosen wd ON wdp.id_ptk = wd.xid_ptk
                        WHERE wad.id_kls = wkk.xid_kls LIMIT 1
                      ) as nama_dosen
               FROM wsia_nilai wn
               JOIN wsia_kelas_kuliah wkk ON wn.xid_kls = wkk.xid_kls
               JOIN wsia_mata_kuliah wmk ON wkk.id_mk = wmk.xid_mk
               WHERE wn.xid_reg_pd = :xid_reg_pd AND wkk.id_smt = :id_smt
               ORDER BY wmk.nm_mk ASC";
    $stmtKrs = $db->prepare($qryKrs);
    $stmtKrs->execute([':xid_reg_pd' => $xid_reg_pd, ':id_smt' => $id_smt]);
    $dataKrs = $stmtKrs->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    exit("Database Error: " . $e->getMessage());
}

$tgl_cetak = date('d-m-Y');

// === GENERATE TOKEN UNTUK VALIDASI KARTU ===
// Token ini memastikan kartu ujian adalah asli dari sistem, bukan editan
try {
    // Generate unique token
    $token_data = $no_pend . '|' . $angkatan . '|' . $kode_prodi . '|' . $jenis_daftar . '|' . $semester_number . '|' . $tipe_ujian . '|' . time();
    $secret_key = 'POLTEK_INDONUSA_KARTU_UJIAN_2025'; // Ganti dengan secret key yang aman
    $token = hash('sha256', $token_data . $secret_key);
    
    // Cek apakah sudah ada token untuk kartu yang sama (sama semua parameternya)
    $qryCheckToken = "SELECT id, token FROM kartu_ujian_tokens 
                      WHERE nim = :nim 
                      AND semester = :semester 
                      AND tipe_ujian = :tipe_ujian 
                      AND id_smt = :id_smt
                      AND is_active = 1
                      ORDER BY created_at DESC LIMIT 1";
    $stmtCheck = $db->prepare($qryCheckToken);
    $stmtCheck->execute([
        ':nim' => $nipd,
        ':semester' => $semester_number,
        ':tipe_ujian' => $tipe_ujian,
        ':id_smt' => $id_smt
    ]);
    $existingToken = $stmtCheck->fetch(PDO::FETCH_OBJ);
    
    if ($existingToken) {
        // Gunakan token yang sudah ada (kartu yang sama di-print ulang)
        $token = $existingToken->token;
    } else {
        // Simpan token baru ke database
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $qryInsertToken = "INSERT INTO kartu_ujian_tokens 
                          (token, nim, no_pend, angkatan, kode_prodi, jenis_daftar, semester, id_smt, tipe_ujian, tanggal_cetak, ip_address, user_agent)
                          VALUES 
                          (:token, :nim, :no_pend, :angkatan, :kode_prodi, :jenis_daftar, :semester, :id_smt, :tipe_ujian, NOW(), :ip_address, :user_agent)";
        
        $stmtInsert = $db->prepare($qryInsertToken);
        $stmtInsert->execute([
            ':token' => $token,
            ':nim' => $nipd,
            ':no_pend' => $no_pend,
            ':angkatan' => $angkatan,
            ':kode_prodi' => $kode_prodi,
            ':jenis_daftar' => $jenis_daftar,
            ':semester' => $semester_number,
            ':id_smt' => $id_smt,
            ':tipe_ujian' => $tipe_ujian,
            ':ip_address' => $ip_address,
            ':user_agent' => substr($user_agent, 0, 255)
        ]);
    }
} catch (PDOException $e) {
    // Jika gagal generate token, tetap lanjut tapi log error
    error_log("Failed to generate token: " . $e->getMessage());
    // Generate fallback token (tidak disimpan di DB)
    $token = hash('sha256', $no_pend . time());
}

// Render HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kartu Ujian - <?= $nipd ?></title>
    <style>
        @page {
            size: A4 portrait; /* Atau landscape jika perlu */
            margin: 1cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact; /* Pastikan background/border tercetak */
            print-color-adjust: exact;
        }

        /* Layout Utama */
        .main-container {
            width: 100%;
            border: 1px solid #000;
            display: flex;
            flex-direction: row;
        }

        .left-col {
            width: 40%;
            border-right: 1px solid #000;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .right-col {
            width: 60%;
            padding: 10px;
            box-sizing: border-box;
        }

        /* Helper Classes */
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 15px; }
        .mt-1 { margin-top: 5px; }
        
        /* Kop & Header */
        .kop-img {
            width: 100%;
            height: auto;
            margin-bottom: 5px;
            display: block;
        }
        
        .card-title {
            font-size: 12px;
            line-height: 1.4;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        /* Footer TTD */
        .footer-ttd {
            margin-top: auto; /* Push ke bawah jika pakai Flex */
            text-align: right;
        }

        /* Tabel Mata Kuliah */
        .matkul-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .table-matkul {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .table-matkul th, .table-matkul td {
            border: 1px solid #000;
            padding: 5px;
        }
        .table-matkul th {
            text-align: center;
            background-color: #fff; /* Bisa ubah #eee kalau mau abu */
        }
        .td-center { text-align: center; }
        
        /* QR Code Styling */
        .qr-container {
            text-align: center;
            margin-left: 10px;
        }
        
        .qr-container img {
            width: 100px;
            height: 100px;
            border: 1px solid #000;
            display: block;
            margin: 0 auto;
        }
        
        .qr-label {
            font-size: 8px;
            margin-top: 2px;
        }
        
        /* Utility untuk Print */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol Print Manual (Hidden saat diprint) -->
    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor:pointer;">Cetak Kartu / Simpan PDF</button>
    </div>

    <div class="main-container">
        <!-- === KOLOM KIRI === -->
        <div class="left-col">
            <!-- Kop Image -->
            <!-- Menggunakan path relative browser yang pasti jalan -->
            <img src="https://siakadv2.poltekindonusa.ac.id/gambar/kop.jpg" class="kop-img" alt="Kop Surat" onerror="this.onerror=null; this.src='../../gambar/kop.jpg';"> 
            
            <div class="text-center text-bold card-title">
                KARTU UJIAN<br>
                <?= $keterangan_id ?><br>
                TAHUN AKADEMIK <?= $tahun_akademik_text ?>
            </div>

            <table class="info-table">
                <tr>
                    <td width="80">NAMA</td>
                    <td width="10">:</td>
                    <td><?= strtoupper($nm_pd) ?></td>
                </tr>
                <tr>
                    <td>NIM</td>
                    <td>:</td>
                    <td><?= $nipd ?></td>
                </tr>
                <tr>
                    <td>PROGRAM<br>STUDI</td>
                    <td>:</td>
                    <td><?= strtoupper($dataMhs->nm_lemb) ?></td>
                </tr>
            </table>

            <?php
            // Generate QR Code URL for validation
            $validation_url = "https://siakadv2.poltekindonusa.ac.id/validasi-kartu-ujian.html?" . 
                              "nim=" . urlencode($nipd) . 
                              "&angkatan=" . urlencode($angkatan) . 
                              "&kode_prodi=" . urlencode($kode_prodi) . 
                              "&jenis_daftar=" . urlencode($jenis_daftar) . 
                              "&semester=" . urlencode($semester_number) . 
                              "&tipe=" . urlencode($tipe_ujian) . 
                              "&token=" . urlencode($token);
            
            /**
             * Simple QR Code Generator (Pure PHP - No Dependencies)
             * Using SVG for QR code generation
             */
            function generateSimpleQR($data, $size = 150) {
                // Encode data for URL
                $encoded_data = urlencode($data);
                
                // Try multiple QR APIs as fallback
                // QuickChart.io is most reliable and free
                $apis = [
                    "https://quickchart.io/qr?text=" . $encoded_data . "&size=" . $size . "&margin=1",
                    "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . $encoded_data . "&margin=10",
                    "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl=" . $encoded_data . "&choe=UTF-8"
                ];
                
                // Try to fetch from first working API
                foreach ($apis as $api_url) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $api_url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Increased timeout
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    
                    $image_data = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $error = curl_error($ch);
                    curl_close($ch);
                    
                    // Debug log
                    error_log("QR API attempt: $api_url - HTTP $http_code");
                    
                    if ($http_code == 200 && $image_data && strlen($image_data) > 100) {
                        // Success! Return as base64
                        error_log("QR Code generated successfully from API");
                        return 'data:image/png;base64,' . base64_encode($image_data);
                    }
                }
                
                // If all APIs fail, log error and return text-based fallback
                error_log("ERROR: All QR APIs failed! Returning text fallback");
                
                // Return URL as text (better than nothing)
                return 'data:image/svg+xml;base64,' . base64_encode(
                    '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '">' .
                    '<rect width="100%" height="100%" fill="white"/>' .
                    '<text x="50%" y="50%" text-anchor="middle" font-size="10" fill="red">QR API FAILED</text>' .
                    '</svg>'
                );
            }
            
            // Generate QR code with larger size for better scanning
            $qr_code_url = generateSimpleQR($validation_url, 150);
            ?>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto;">
                <div class="qr-container">
                    <img src="<?= $qr_code_url ?>" alt="QR Code Validasi" 
                         onerror="this.style.display='none'; document.getElementById('qr-fallback').style.display='block';">
                    <div id="qr-fallback" style="display:none; font-size:8px; word-break:break-all; width:100px;">
                        URL: <?= substr($validation_url, 0, 50) ?>...
                    </div>
                    <div class="qr-label">Scan untuk validasi</div>
                    <!-- Debug: Uncomment to see URL -->
                    <!-- <div style="font-size:8px; word-break:break-all;"><?= $qr_code_url ?></div> -->
                </div>
                <div class="footer-ttd" style="flex: 1; text-align: right;">
                    Surakarta, <?= $tgl_cetak ?><br>
                    Wadir I<br>
                    <br><br><br><br>
                    Edy Susena, M.Kom
                </div>
            </div>
        </div>

        <!-- === KOLOM KANAN === -->
        <div class="right-col">
            <div class="matkul-title">MATA KULIAH</div>
            
            <table class="table-matkul">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th width="40%">Mata Kuliah</th>
                        <th width="35%">Dosen</th>
                        <th width="15%">Paraf</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $min_rows = 0;
                    $counter = 0;

                    if(!empty($dataKrs)) {
                        foreach($dataKrs as $row) {
                            $nama_dosen = !empty($row->nama_dosen) ? $row->nama_dosen : '-';
                            // Truncate nama dosen visual only
                            if(strlen($nama_dosen) > 35) $nama_dosen = substr($nama_dosen, 0, 35) . '...';
                            ?>
                            <tr>
                                <td class="td-center"><?= $no++ ?></td>
                                <td><?= $row->nm_mk ?></td>
                                <td><?= $nama_dosen ?></td>
                                <td></td>
                            </tr>
                            <?php
                            $counter++;
                        }
                    }

                    // Filler rows
                    for($i = $counter; $i < $min_rows; $i++) {
                        echo '<tr>
                            <td class="td-center" style="color:transparent">.</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Auto trigger print dialog when loaded
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
