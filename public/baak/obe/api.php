<?php
/**
 * OBE Sync API - Bridge SIAKAD ← api-obe.poltekindonusa.ac.id (SIOBE)
 * Di-serve dari: /baak/obe/api
 */
session_start();
set_time_limit(300);

// Matikan output error agar tidak merusak JSON
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

header('Content-Type: application/json');

$basePath = dirname(dirname(dirname(__DIR__)));
require_once $basePath . '/config/config.php';
require_once $basePath . '/ws/OBESyncService.php';

// Auth check — sesuaikan dengan session key yang dipakai SIAKAD
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? '';
$isValidApiKey = ($apiKey === OBESyncService::API_KEY);

if (!isset($_SESSION['wsiaADMIN']) && !$isValidApiKey) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $obeService = new OBESyncService();

    ob_clean();

    switch ($action) {

        // --- Cek koneksi ke api-obe ---
        case 'test_connection':
            $result = $obeService->testConnection();
            echo json_encode($result);
            break;

        // --- Ambil daftar kelas yang ada di SIOBE (preview sebelum sync) ---
        case 'get_kelas':
            $tahun    = $_POST['tahun']    ?? $_GET['tahun']    ?? '';
            $semester = (int)($_POST['semester'] ?? $_GET['semester'] ?? 0);
            $data     = $obeService->getKelasOBE($tahun, $semester);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        // --- Sinkronisasi nilai OBE → SIAKAD ---
        case 'sync_grades':
            $tahun     = $_POST['tahun']      ?? '';
            $semester  = (int)($_POST['semester']  ?? 0);
            $kodeMakul = $_POST['kode_makul'] ?? '';
            $kelas     = $_POST['kelas']      ?? '';
            $limit     = (int)($_POST['limit'] ?? 500);

            $result = $obeService->syncGrades($tahun, $semester, $kodeMakul, $kelas, $limit);
            echo json_encode(['success' => true, 'data' => $result]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
    }

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();
