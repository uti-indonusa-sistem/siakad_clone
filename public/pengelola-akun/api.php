<?php
/**
 * API Pengelola Akun Learning - Standalone Portal
 * Mendukung sinkronisasi massal bertahap (chunking) dan pengelolaan akun individu.
 */
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../ws/MoodleWebService.php';
require_once __DIR__ . '/../../ws/MoodleSyncService.php';

header('Content-Type: application/json');

// Prevent timeout (5 minutes)
set_time_limit(300);
ini_set('max_execution_time', '300');

// Simple Authentication Check
function checkAuth()
{
    if (!isset($_SESSION['moodle_manager_logged_in'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

$action = $_GET['action'] ?? '';

// --- PUBLIC ACTIONS ---
if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Default: admin / moodle2024
    if ($username === 'admin' && $password === 'moodle2024') {
        $_SESSION['moodle_manager_logged_in'] = true;
        $_SESSION['moodle_manager_user'] = 'Administrator';
        echo json_encode(['status' => 'success', 'message' => 'Login Berhasil']);
    } else {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah']);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(['status' => 'success']);
    exit;
}

// --- PROTECTED ACTIONS ---
checkAuth();

$db = koneksi(); // From config.php

if ($action === 'get_sync_filters') {
    try {
        // Angkatan
        $stmt = $db->query("SELECT DISTINCT SUBSTRING(mulai_smt, 1, 4) as angkatan FROM wsia_mahasiswa_pt ORDER BY angkatan DESC");
        $angkatan = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Semesters
        $stmtSem = $db->query("SELECT id_smt, nm_smt FROM wsia_semester ORDER BY id_smt DESC LIMIT 10");
        $semesters = $stmtSem->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'angkatan' => $angkatan, 'semesters' => $semesters]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'sync_prepare') {
    $type = $_GET['type'] ?? 'mahasiswa';
    $filter = $_GET['filter'] ?? ''; // angkatan for mhs, semester for courses/enrolments

    try {
        $total = 0;

        if ($type === 'mahasiswa') {
            $sql = "SELECT COUNT(*) FROM wsia_mahasiswa_pt mpt JOIN wsia_mahasiswa m ON mpt.id_pd = m.xid_pd WHERE mpt.id_jns_keluar = ''";
            if ($filter) {
                $sql .= " AND SUBSTRING(mpt.mulai_smt, 1, 4) = " . $db->quote($filter);
            }
            $stmt = $db->query($sql);
            $total = $stmt->fetchColumn();
        } elseif ($type === 'dosen') {
            $sql = "SELECT COUNT(*) FROM wsia_dosen d WHERE d.id_sp = (SELECT id_sp FROM wsia_satuan_pendidikan WHERE npsn = '" . NPSN . "')";
            $stmt = $db->query($sql);
            $total = $stmt->fetchColumn();
        } elseif ($type === 'courses') {
            // Logic Matches MoodleSyncService::syncCourses query (Offered Classes)
            $id_smt = $filter;
            if (!$id_smt) {
                $stmtSem = $db->query("SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1");
                $id_smt = $stmtSem->fetchColumn() ?: (date('Y') . '1');
            }
            // Count classes offered in this semester (Course = Class in Moodle)
            $sql = "SELECT COUNT(*) FROM wsia_kelas_kuliah WHERE id_smt = " . $db->quote($id_smt);
            $stmt = $db->query($sql);
            $total = $stmt->fetchColumn();
        } elseif ($type === 'enrolments') {
            // Logic Matches MoodleSyncService::syncEnrolments query
            // If filter (semester) is not provided, get active semester
            $id_smt = $filter;
            if (!$id_smt) {
                $stmtSem = $db->query("SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1");
                $id_smt = $stmtSem->fetchColumn() ?: (date('Y') . '1');
            }

            $sql = "SELECT COUNT(*) FROM wsia_kelas_kuliah kk WHERE kk.id_smt = " . $db->quote($id_smt);
            $stmt = $db->query($sql);
            $total = $stmt->fetchColumn();
        }

        echo json_encode(['status' => 'success', 'total' => (int) $total]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'sync_batch') {
    $type = $_GET['type'] ?? 'mahasiswa';
    $filter = $_GET['filter'] ?? null;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;

    $sync = new MoodleSyncService();
    $result = [];

    if ($type === 'mahasiswa') {
        $result = $sync->syncMahasiswa($limit, $filter, $offset);
    } elseif ($type === 'dosen') {
        // Dosen doesn't have offset in service yet? 
        // Checking Service: syncDosen($limit = null). It fetches ALL limit. 
        // It DOES NOT support offset in the service code provided (it just limits).
        // I should probably fix the Service to support offset, OR just process small chunks if limit is used.
        // Wait, the Service query is: `LIMIT intval($limit)`. No offset.
        // This means batching won't work correctly for Dosen/Courses/Enrolments unless I modify the Service 
        // OR I rely on the fact that maybe they aren't too many?
        // Actually, for "Blank Screen" fixes I should be careful.
        // But user asked for "Sync Data Lainnya".
        // I MUST UPDATE SERVICE TO SUPPORT OFFSET for these.
        // For now, I will use what is available, but if offset is ignored, it will resync the first N rows every time.
        // FIX: I will pass offset to syncDosen, syncCourses, syncEnrolments (need to update Service strictly).

        // Assuming I will update Service:
        $result = $sync->syncDosen($limit, $offset);
    } elseif ($type === 'courses') {
        $force = isset($_GET['force']) && $_GET['force'] === 'true';
        $result = $sync->syncCourses($limit, $filter, $offset, $force);
    } elseif ($type === 'enrolments') {
        $result = $sync->syncEnrolments($limit, $filter, $offset);
    }

    echo json_encode([
        'status' => 'success',
        'offset' => $offset,
        'limit' => $limit,
        'results' => $result
    ]);
    exit;
}

if ($action === 'search_mhs') {
    $q = $_GET['q'] ?? '';
    if (strlen($q) < 3) {
        echo json_encode([]);
        exit;
    }

    $stmt = $db->prepare("SELECT mpt.nipd as nim, m.nm_pd as nama, s.nm_lemb as prodi 
                         FROM wsia_mahasiswa m
                         JOIN wsia_mahasiswa_pt mpt ON m.xid_pd = mpt.id_pd
                         JOIN wsia_sms s ON mpt.id_sms = s.xid_sms
                         WHERE mpt.nipd LIKE :q OR m.nm_pd LIKE :q2
                         LIMIT 10");
    $stmt->execute(['q' => "%$q%", 'q2' => "%$q%"]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action === 'get_details') {
    $nim = $_GET['nim'] ?? '';

    // Get Siakad Data
    $stmt = $db->prepare("SELECT mpt.nipd as nim, m.nm_pd as nama, s.nm_lemb as prodi, m.email
                         FROM wsia_mahasiswa m
                         JOIN wsia_mahasiswa_pt mpt ON m.xid_pd = mpt.id_pd
                         JOIN wsia_sms s ON mpt.id_sms = s.xid_sms
                         WHERE mpt.nipd = ?");
    $stmt->execute([$nim]);
    $mhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mhs) {
        echo json_encode(['status' => 'error', 'message' => 'Mahasiswa tidak ditemukan']);
        exit;
    }

    // Get Moodle Status
    $moodle = new MoodleWebService();
    $moodleUser = $moodle->getUserByIdNumber($nim);

    $mhs['moodle_status'] = 'Belum Terdaftar';
    $mhs['moodle_id'] = null;
    $mhs['moodle_email'] = null;

    if ($moodleUser['success'] && !empty($moodleUser['data']['users'])) {
        $u = $moodleUser['data']['users'][0];
        $mhs['moodle_status'] = 'Aktif';
        $mhs['moodle_id'] = $u['id'];
        $mhs['moodle_email'] = $u['email'];
    }

    echo json_encode(['status' => 'success', 'data' => $mhs]);
    exit;
}

if ($action === 'update_moodle') {
    $data = json_decode(file_get_contents('php://input'), true);
    $nim = $data['nim'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $moodle = new MoodleWebService();
    $existing = $moodle->getUserByIdNumber($nim);

    if (!$existing['success'] || empty($existing['data']['users'])) {
        echo json_encode(['status' => 'error', 'message' => 'Akun Moodle belum ada. Silakan lakukan Sync terlebih dahulu.']);
        exit;
    }

    $userId = $existing['data']['users'][0]['id'];
    $updateData = [];
    if (!empty($email))
        $updateData['email'] = strtolower($email);
    if (!empty($password))
        $updateData['password'] = $password;

    if (empty($updateData)) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak ada data yang diubah']);
        exit;
    }

    $result = $moodle->updateUser($userId, $updateData);
    if ($result['success']) {
        echo json_encode(['status' => 'success', 'message' => 'Akun Moodle berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update Moodle: ' . ($result['error'] ?? 'Unknown error')]);
    }
    exit;
}

if ($action === 'sync_individual') {
    $nim = $_GET['nim'] ?? '';

    // Get full data for sync
    $stmt = $db->prepare("SELECT 
                    mpt.nipd as nim, m.nm_pd as nama, s.xid_sms as id_prodi,
                    s.nm_lemb as nama_program_studi, SUBSTRING(mpt.mulai_smt, 1, 4) as angkatan
                FROM wsia_mahasiswa m
                INNER JOIN wsia_mahasiswa_pt mpt ON m.xid_pd = mpt.id_pd
                INNER JOIN wsia_sms s ON mpt.id_sms = s.xid_sms
                WHERE mpt.nipd = ?");
    $stmt->execute([$nim]);
    $mhsData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$mhsData) {
        echo json_encode(['status' => 'error', 'message' => 'Data mahasiswa tidak ditemukan di SIAKAD']);
        exit;
    }

    $sync = new MoodleSyncService();
    $moodle = new MoodleWebService();

    // Force sync logic
    $userData = $sync->prepareMahasiswaData($mhsData);
    $existing = $moodle->getUserByIdNumber($nim);

    if ($existing['success'] && !empty($existing['data']['users'])) {
        $res = $moodle->updateUser($existing['data']['users'][0]['id'], $userData);
    } else {
        $res = $moodle->createUser($userData);
    }

    if ($res['success']) {
        echo json_encode(['status' => 'success', 'message' => 'Sync berhasil']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Sync gagal: ' . ($res['error'] ?? 'Unknown error')]);
    }
    exit;
}

if ($action === 'archive_prepare') {
    $semester = $_GET['semester'] ?? '';
    if (!$semester) {
        echo json_encode(['status' => 'error', 'message' => 'Semester belum dipilih']);
        exit;
    }
    try {
        $sql = "SELECT COUNT(DISTINCT id_mk) FROM wsia_kelas_kuliah WHERE id_smt = " . $db->quote($semester);
        $total = $db->query($sql)->fetchColumn();
        echo json_encode(['status' => 'success', 'total' => (int) $total]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'archive_batch') {
    $semester = $_GET['semester'] ?? '';
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;

    $sync = new MoodleSyncService();
    $result = $sync->archiveSemesterCourses($semester, $limit, $offset);

    echo json_encode(['status' => 'success', 'results' => $result]);
    exit;
}

if ($action === 'delete_prepare') {
    $semester = $_GET['semester'] ?? '';
    if (!$semester) {
        echo json_encode(['status' => 'error', 'message' => 'Semester belum dipilih']);
        exit;
    }
    try {
        $sql = "SELECT COUNT(DISTINCT id_mk) FROM wsia_kelas_kuliah WHERE id_smt = " . $db->quote($semester);
        $total = $db->query($sql)->fetchColumn();
        echo json_encode(['status' => 'success', 'total' => (int) $total]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'delete_batch') {
    $semester = $_GET['semester'] ?? '';
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;

    $sync = new MoodleSyncService();
    $result = $sync->deleteOldSemesterCourses($semester, $limit, $offset);

    echo json_encode(['status' => 'success', 'results' => $result]);
    exit;
}
