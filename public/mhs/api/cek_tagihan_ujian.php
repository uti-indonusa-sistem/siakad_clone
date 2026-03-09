<?php
/**
 * API Cek Tagihan Ujian
 * 
 * Endpoint ini digunakan untuk mengecek tagihan ujian mahasiswa
 * sebelum cetak kartu ujian. Proxy ke SIKEU API.
 * 
 * @param string $aksi - tipe ujian: UTS atau UAS
 * @param string $key - session key
 * @param string $id - id_smt (optional)
 */

error_reporting(0);

if (!isset($key)) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized', 'data' => []]);
    exit();
}

include 'login_auth.php';

if ($key != $_SESSION['wsiaMHS']) {
    echo json_encode(['status' => false, 'message' => 'Invalid session', 'data' => []]);
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
    curl_setopt($ch, CURLOPT_USERAGENT, 'SIAKAD-CekTagihan/1.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['success' => false, 'data' => [], 'message' => 'Connection error'];
    }
    
    curl_close($ch);
    
    if ($httpCode != 200) {
        return ['success' => false, 'data' => [], 'message' => 'HTTP Error: ' . $httpCode];
    }
    
    $data = json_decode($output, true);
    return [
        'success' => $data['status'] ?? false,
        'data' => $data['data'] ?? [],
        'message' => $data['message'] ?? ''
    ];
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get exam type from action (UTS or UAS)
$tipe_ujian = strtoupper($aksi);
if (!in_array($tipe_ujian, ['UTS', 'UAS'])) {
    echo json_encode(['status' => false, 'message' => 'Tipe ujian tidak valid', 'data' => []]);
    exit();
}

// Get semester ID
if (isset($id) && strlen($id) == 5) {
    $id_smt = $id;
} else {
    $id_smt = $_SESSION['id_smt_aktif'];
}

if ($id_smt == "-" || empty($id_smt)) {
    echo json_encode(['status' => false, 'message' => 'Semester aktif tidak ditemukan', 'data' => []]);
    exit();
}

// Session Data
$no_pend = $_SESSION['no_pend'];
$angkatan = substr($_SESSION['mulai_smt'], 0, 4);
$kode_prodi = $_SESSION['kode_prodi'];
$jenis_daftar = $_SESSION['jenis_daftar'];

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

$tagihanBelumLunas = [];
$totalKekurangan = 0;

if ($tagihanResult['success'] && !empty($tagihanResult['data'])) {
    foreach ($tagihanResult['data'] as $tagihan) {
        if (isset($tagihan['kekurangan']) && $tagihan['kekurangan'] > 0) {
            $tagihanBelumLunas[] = [
                'nama_biaya' => $tagihan['nama_biaya'],
                'tagihan' => $tagihan['tagihan'],
                'terbayar' => $tagihan['terbayar'],
                'potongan' => $tagihan['potongan'],
                'kekurangan' => $tagihan['kekurangan'],
                'kekurangan_formatted' => formatRupiah($tagihan['kekurangan']),
                'syarat_ujian' => $tagihan['syarat_ujian'] ?? ''
            ];
            $totalKekurangan += $tagihan['kekurangan'];
        }
    }
}

// Response
if (!empty($tagihanBelumLunas)) {
    echo json_encode([
        'status' => false,
        'message' => 'Ada tagihan belum lunas',
        'lunas' => false,
        'total_kekurangan' => $totalKekurangan,
        'total_kekurangan_formatted' => formatRupiah($totalKekurangan),
        'data' => $tagihanBelumLunas
    ]);
} else {
    echo json_encode([
        'status' => true,
        'message' => 'Semua tagihan sudah lunas',
        'lunas' => true,
        'total_kekurangan' => 0,
        'total_kekurangan_formatted' => formatRupiah(0),
        'data' => []
    ]);
}
