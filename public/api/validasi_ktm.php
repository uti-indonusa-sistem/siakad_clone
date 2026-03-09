<?php
/**
 * API Validasi KTM (Kartu Tanda Mahasiswa)
 * 
 * Endpoint: /api/validasi_ktm.php
 * Method: GET
 * Parameters: nim (MD5 hash of NIPD)
 * 
 * Response:
 * {
 *   "status": true/false,
 *   "message": string,
 *   "data": {
 *     "valid": true/false,
 *     "mahasiswa": {...}
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
require_once '../../config/config.php';

// Get parameters
$nim_md5 = $_GET['nim'] ?? '';

// Validate required parameters
if (empty($nim_md5)) {
    echo json_encode([
        'status' => false,
        'message' => 'Parameter NIM tidak boleh kosong',
        'data' => null
    ]);
    exit();
}

// Validate MD5 format (32 hex characters)
if (!preg_match('/^[a-f0-9]{32}$/i', $nim_md5)) {
    echo json_encode([
        'status' => false,
        'message' => 'Format NIM tidak valid',
        'data' => [
            'valid' => false,
            'reason' => 'INVALID_FORMAT'
        ]
    ]);
    exit();
}

try {
    $db = koneksi();

    // Query mahasiswa by MD5 hash of NIPD
    $qryMhs = "SELECT 
        m.nm_pd,
        m.tmpt_lahir,
        mp.nipd,
        a.nm_agama,
        s.nm_lemb as nm_prodi,
        j.nm_jenj_didik,
        m.jk,
        DATE_FORMAT(m.tgl_lahir, '%d-%m-%Y') AS tgl_lahir,
        mp.mulai_smt,
        mp.id_jns_daftar,
        CASE mp.id_jns_daftar
            WHEN '1' THEN 'Reguler'
            WHEN '2' THEN 'Pindahan'
            ELSE 'Lainnya'
        END as jenis_daftar_text
    FROM 
        wsia_mahasiswa m
    LEFT JOIN 
        wsia_mahasiswa_pt mp ON m.xid_pd = mp.id_pd
    LEFT JOIN 
        wsia_agama a ON m.id_agama = a.id_agama
    LEFT JOIN 
        wsia_sms s ON mp.id_sms = s.xid_sms
    LEFT JOIN 
        wsia_jenjang_pendidikan j ON s.id_jenj_didik = j.id_jenj_didik
    WHERE 
        MD5(mp.nipd) = :nim_md5
    LIMIT 1";

    $stmt = $db->prepare($qryMhs);
    $stmt->execute([':nim_md5' => $nim_md5]);
    $dataMhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dataMhs) {
        echo json_encode([
            'status' => false,
            'message' => '⛔ Data mahasiswa tidak ditemukan. KTM tidak valid.',
            'data' => [
                'valid' => false,
                'reason' => 'NOT_FOUND'
            ]
        ]);
        exit();
    }

    // Calculate angkatan and masa berlaku
    $angkatan = '20' . substr($dataMhs['nipd'], 1, 2);
    $prefix = substr($dataMhs['nipd'], 0, 1);

    if (in_array($prefix, ['F', 'A', 'B', 'C', 'G']) && $dataMhs['id_jns_daftar'] == '1') {
        $durasikuliah = 4;
    } else if ($dataMhs['id_jns_daftar'] == '2') {
        $durasikuliah = 1;
    } else {
        $durasikuliah = 3;
    }

    $masaberlaku = intval(substr($dataMhs['nipd'], 1, 2)) + $durasikuliah;
    $masa_berlaku_text = "31 Agustus 20" . $masaberlaku;

    // Determine status (aktif/expired)
    $current_year = intval(date('y'));
    $current_month = intval(date('m'));
    $is_expired = ($current_year > $masaberlaku) || ($current_year == $masaberlaku && $current_month > 8);

    // Success response
    echo json_encode([
        'status' => true,
        'message' => $is_expired ?
            '⚠️ KTM Valid tetapi sudah melewati masa berlaku' :
            '✅ KTM Valid dan masih berlaku',
        'data' => [
            'valid' => true,
            'expired' => $is_expired,
            'mahasiswa' => [
                'nim' => $dataMhs['nipd'],
                'nama' => $dataMhs['nm_pd'],
                'tempat_lahir' => $dataMhs['tmpt_lahir'],
                'tanggal_lahir' => $dataMhs['tgl_lahir'],
                'jenis_kelamin' => ($dataMhs['jk'] == 'L') ? 'Laki-laki' : 'Perempuan',
                'agama' => $dataMhs['nm_agama'],
                'prodi' => $dataMhs['nm_prodi'],
                'jenjang' => $dataMhs['nm_jenj_didik'],
                'angkatan' => $angkatan,
                'jenis_daftar' => $dataMhs['jenis_daftar_text']
            ],
            'masa_berlaku' => $masa_berlaku_text,
            'foto_url' => 'https://siakadv2.poltekindonusa.ac.id/mhs/foto/' . $nim_md5 . '.jpg'
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