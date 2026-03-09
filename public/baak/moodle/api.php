<?php
/**
 * Moodle Sync API Endpoint
 * API untuk trigger sinkronisasi dari external system atau cron job
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

header('Content-Type: application/json');

// Determine base path - works for both local and production
$basePath = dirname(dirname(dirname(__DIR__)));

require_once $basePath . '/config/config.php';
require_once $basePath . '/config/moodle_config.php';
require_once $basePath . '/ws/MoodleWebService.php';
require_once $basePath . '/ws/MoodleSyncService.php';

// Simple API authentication
$apiKey = $_GET['api_key'] ?? $_POST['api_key'] ?? '';
$validApiKey = 'siakad_moodle_sync_2026'; // TODO: Move to config

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized: Invalid API key'
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? 'sync_all';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;

try {
    $syncService = new MoodleSyncService();

    switch ($action) {
        case 'sync_mahasiswa':
            $result = $syncService->syncMahasiswa($limit);
            break;

        case 'sync_dosen':
            $result = $syncService->syncDosen($limit);
            break;

        case 'sync_courses':
            $result = $syncService->syncCourses($limit);
            break;

        case 'sync_enrolments':
            $result = $syncService->syncEnrolments($limit);
            break;
            
        case 'sync_grades':
            $semester = $_GET['semester'] ?? $_POST['semester'] ?? null;
            $result = $syncService->syncGrades($semester, $limit);
            break;

        case 'sync_all':
            $result = $syncService->syncAll();
            break;

        case 'test':
            $moodleService = new MoodleWebService();
            $result = $moodleService->testConnection();
            break;

        case 'stats':
            $result = [
                'success' => true,
                'data' => $syncService->getSyncStats()
            ];
            break;

        default:
            http_response_code(400);
            $result = [
                'success' => false,
                'error' => 'Invalid action'
            ];
    }

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
