<?php
/**
 * API Validasi Token Kartu Ujian
 * 
 * Endpoint: /api/validasi_kartu_ujian.php
 * Method: GET
 * Parameters: token, nim, angkatan, kode_prodi, jenis_daftar, semester, tipe
 * 
 * Response:
 * {
 *   "status": true/false,
 *   "message": string,
 *   "data": {
 *     "token_valid": true/false,
 *     "pembayaran_lunas": true/false,
 *     "mahasiswa": {...},
 *     "ujian": {...},
 *     "tagihan_belum_lunas": [...],
 *     "total_kekurangan": number,
 *     "tanggal_cetak": datetime
 *   }
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include koneksi database
require_once '../../ws/lib/koneksi.php';

// Configuration for SIKEU API
define('SIKEU_API_URL', 'https://sikeu.poltekindonusa.ac.id');

/**
 * Make HTTP request to SIKEU API
 */
function httpRequestSikeu($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SIAKAD-Validasi/1.0');
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

// Get parameters
$token = $_GET['token'] ?? '';
$nim = $_GET['nim'] ?? '';
$angkatan = $_GET['angkatan'] ?? '';
$kode_prodi = $_GET['kode_prodi'] ?? '';
$jenis_daftar = $_GET['jenis_daftar'] ?? '';
$semester = $_GET['semester'] ?? '';
$tipe = $_GET['tipe'] ?? '';

// Validate required parameters
if (empty($token) || empty($nim) || empty($angkatan) || empty($kode_prodi) || 
    empty($jenis_daftar) || empty($semester) || empty($tipe)) {
    echo json_encode([
        'status' => false,
        'message' => 'Parameter tidak lengkap',
        'data' => null
    ]);
    exit();
}

// Validate token format (SHA-256 = 64 characters)
if (strlen($token) !== 64) {
    echo json_encode([
        'status' => false,
        'message' => 'Format token tidak valid',
        'data' => [
            'token_valid' => false,
            'reason' => 'INVALID_FORMAT'
        ]
    ]);
    exit();
}

try {
    $db = koneksi_wsia();
    
    // === STEP 1: VALIDASI TOKEN ===
    $qryToken = "SELECT 
                    id, token, nim, angkatan, kode_prodi, jenis_daftar, 
                    semester, id_smt, tipe_ujian, tanggal_cetak, is_active
                 FROM kartu_ujian_tokens 
                 WHERE token = :token 
                 AND is_active = 1 
                 LIMIT 1";
    
    $stmtToken = $db->prepare($qryToken);
    $stmtToken->execute([':token' => $token]);
    $tokenData = $stmtToken->fetch(PDO::FETCH_ASSOC);
    
    // Token tidak ditemukan atau inactive
    if (!$tokenData) {
        echo json_encode([
            'status' => false,
            'message' => '⛔ Token tidak valid atau kartu ujian palsu',
            'data' => [
                'token_valid' => false,
                'reason' => 'TOKEN_NOT_FOUND'
            ]
        ]);
        exit();
    }
    
    // Verify data match
    if ($tokenData['nim'] !== $nim || 
        $tokenData['angkatan'] !== $angkatan ||
        $tokenData['kode_prodi'] !== $kode_prodi ||
        $tokenData['jenis_daftar'] !== $jenis_daftar ||
        $tokenData['semester'] != $semester ||
        $tokenData['tipe_ujian'] !== $tipe) {
        
        echo json_encode([
            'status' => false,
            'message' => '⛔ Data tidak cocok dengan token - Kemungkinan data dimanipulasi',
            'data' => [
                'token_valid' => false,
                'reason' => 'DATA_MISMATCH'
            ]
        ]);
        exit();
    }
    
    // === STEP 2: CEK PEMBAYARAN VIA SIKEU ===
    $sikeuUrl = SIKEU_API_URL . "/ujian/{$nim}/{$angkatan}/{$kode_prodi}/{$jenis_daftar}/{$semester}/{$tipe}";
    $tagihanResult = httpRequestSikeu($sikeuUrl);
    
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
    
    $isPembayaranLunas = count($tagihanBelumLunas) === 0;
    
    // === RESPONSE ===
    echo json_encode([
        'status' => true,
        'message' => $isPembayaranLunas ? 
                     'Kartu ujian valid dan pembayaran lunas' : 
                     'Kartu ujian valid tetapi ada tagihan yang belum lunas',
        'data' => [
            'token_valid' => true,
            'pembayaran_lunas' => $isPembayaranLunas,
            'mahasiswa' => [
                'nim' => $nim,
                'angkatan' => $angkatan,
                'kode_prodi' => $kode_prodi,
                'jenis_daftar' => $jenis_daftar
            ],
            'ujian' => [
                'tipe' => $tipe,
                'semester' => $semester,
                'id_smt' => $tokenData['id_smt']
            ],
            'tagihan_belum_lunas' => $tagihanBelumLunas,
            'total_kekurangan' => $totalKekurangan,
            'tanggal_cetak' => $tokenData['tanggal_cetak']
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => null
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
