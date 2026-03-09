<?php
/**
 * API Data Mahasiswa Eksternal
 * 
 * Endpoint khusus untuk integrasi dengan aplikasi eksternal.
 * 
 * SECURITY WARNING:
 * API ini mengekspos data sensitif termasuk hash password.
 * HANYA gunakan API ini untuk integrasi internal yang terpercaya.
 * JANGAN PERNAH mengekspos endpoint ini ke publik secara terbuka.
 * 
 * Endpoint: /api/mahasiswa_external.php
 * Method: GET
 * Header: X-Api-Key: [API_SECRET_KEY]
 * Params: 
 *   - nim (optional): Filter by NIM specific
 *   - limit (optional): Limit results (default 100)
 *   - offset (optional): Offset for pagination
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Sesuaikan dengan domain aplikasi eksternal untuk keamanan lebih baik
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: X-Api-Key, Content-Type');

// 1. KONFIGURASI KEAMANAN
// GANTI ini dengan key yang sangat rahasia dan panjang
define('API_SECRET_KEY', 'INDONUSA_SECRET_API_KEY_2026_X7Z'); 

// 2. Load Config
require_once '../../config/config.php';

// 3. Validasi API Key
$headers = getallheaders();
$api_key = isset($headers['X-Api-Key']) ? $headers['X-Api-Key'] : (isset($_GET['key']) ? $_GET['key'] : '');

if ($api_key !== API_SECRET_KEY) {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'Unauthorized: Invalid or missing API Key'
    ]);
    exit();
}

try {
    $db = koneksi();
    
    // Parameter Filter
    $nim = isset($_GET['nim']) ? $_GET['nim'] : '';
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    
    // Batasi limit maksimum untuk performa
    // if ($limit > 500) $limit = 500;
    
    if (!empty($nim)) {
        // Query Single Mahasiswa
        $sql = "SELECT 
                    mp.nipd as nim, 
                    m.nm_pd as nama, 
                    m.email, 
                    COALESCE(m.email_poltek, '') as email_institusi, 
                    mp.pass as password_hash,
                    m.hp as no_hp
                FROM wsia_mahasiswa_pt mp
                JOIN wsia_mahasiswa m ON mp.id_pd = m.xid_pd
                WHERE mp.nipd = :nim
                LIMIT 1";
                
        $stmt = $db->prepare($sql);
        $stmt->execute([':nim' => $nim]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($data) {
            $result = [
                'status' => true,
                'data' => $data
            ];
        } else {
            http_response_code(404);
            $result = [
                'status' => false,
                'message' => 'Mahasiswa not found'
            ];
        }
        
    } else {
        // Query List Mahasiswa
        $sql = "SELECT 
                    mp.nipd as nim, 
                    m.nm_pd as nama, 
                    m.email, 
                    COALESCE(m.email_poltek, '') as email_institusi, 
                    mp.pass as password_hash
                FROM wsia_mahasiswa_pt mp
                JOIN wsia_mahasiswa m ON mp.id_pd = m.xid_pd
                WHERE (mp.id_jns_keluar IS NULL OR mp.id_jns_keluar = '')
                ORDER BY mp.nipd DESC
                LIMIT :limit OFFSET :offset";
                
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [
            'status' => true,
            'count' => count($data),
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset
            ],
            'data' => $data
        ];
    }
    
    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
