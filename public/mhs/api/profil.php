<?php
/**
 * Mahasiswa Profile API - SECURED
 * Upload Foto & Update Profil
 * Security Update: 2025-10-14
 */

if (!isset($key)) {
    exit();
}
include 'login_auth.php';
if ($key != $_SESSION['wsiaMHS']) {
    exit();
}

// Load security helper
require_once __DIR__ . '/../../lib/security.php';
// Load Moodle Service
require_once __DIR__ . '/../../../ws/MoodleWebService.php';

if ($aksi == "tampil") {
    try {
        $db = koneksi();

        // Get mahasiswa data with JOIN to get complete information
        $query = "SELECT 
                    wsia_mahasiswa_pt.nipd,
                    wsia_mahasiswa.nm_pd,
                    wsia_mahasiswa.email_poltek,
                    wsia_mahasiswa.email,
                    wsia_sms_view.nm_jenj_didik,
                    wsia_sms_view.nm_lemb,
                    wsia_mahasiswa_pt.id_sms,
                    wsia_mahasiswa_pt.xid_reg_pd
                  FROM wsia_mahasiswa_pt
                  INNER JOIN wsia_mahasiswa ON wsia_mahasiswa_pt.id_pd = wsia_mahasiswa.xid_pd
                  INNER JOIN wsia_sms_view ON wsia_sms_view.xid_sms = wsia_mahasiswa_pt.id_sms
                  WHERE wsia_mahasiswa_pt.nipd = :nipd
                  LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->execute([':nipd' => $_SESSION['nipd']]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Check Moodle Status ONLY if specifically requested (to avoid slowing down main dashboard)
            if (isset($_GET['moodle']) && $_GET['moodle'] == '1') {
                $moodle = new MoodleWebService();
                $moodleUser = $moodle->getUserByIdNumber($_SESSION['nipd']);

                $data['moodle_status'] = ($moodleUser['success'] && !empty($moodleUser['data']['users'])) ? 'Aktif' : 'Belum Terintegrasi';
                $data['moodle_email'] = ($moodleUser['success'] && !empty($moodleUser['data']['users'])) ? $moodleUser['data']['users'][0]['email'] : null;
                $data['moodle_id'] = ($moodleUser['success'] && !empty($moodleUser['data']['users'])) ? $moodleUser['data']['users'][0]['id'] : null;
            } else {
                // Default values if not checking Moodle
                $data['moodle_status'] = 'Checking...';
                $data['moodle_email'] = null;
                $data['moodle_id'] = null;
            }

            // Return data in format expected by template
            header('Content-Type: application/json');
            echo json_encode($data);
        } else {
            // Fallback data if not found
            echo json_encode([
                'nipd' => $_SESSION['nipd'] ?? 'N/A',
                'nm_pd' => $_SESSION['nm_pd'] ?? 'Mahasiswa',
                'email_poltek' => null,
                'nm_jenj_didik' => 'D3/D4',
                'nm_lemb' => 'POLITEKNIK INDONUSA Surakarta'
            ]);
        }

        $db = null;

    } catch (PDOException $e) {
        Security::logSecurityEvent("Profile load error: " . $e->getMessage(), 'ERROR');

        // Return fallback data on error
        echo json_encode([
            'nipd' => $_SESSION['nipd'] ?? 'N/A',
            'nm_pd' => $_SESSION['nm_pd'] ?? 'Mahasiswa',
            'nm_jenj_didik' => 'D3/D4',
            'nm_lemb' => 'POLITEKNIK INDONUSA Surakarta'
        ]);
    }

} else if ($aksi == "update_learning") {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $newEmail = $data['email'] ?? '';
        $newPassword = $data['password'] ?? '';

        if (empty($newEmail) && empty($newPassword)) {
            throw new Exception('Data tidak boleh kosong');
        }

        $moodle = new MoodleWebService();

        // Find Moodle User ID by NIM
        $moodleUser = $moodle->getUserByIdNumber($_SESSION['nipd']);
        if (!$moodleUser['success'] || empty($moodleUser['data']['users'])) {
            throw new Exception('Akun Learning belum aktif. Silakan hubungi admin BAAK.');
        }

        $moodleId = $moodleUser['data']['users'][0]['id'];
        $updateData = [];

        if (!empty($newEmail)) {
            if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid');
            }
            $updateData['email'] = strtolower(trim($newEmail));
        }

        if (!empty($newPassword)) {
            // Moodle Password Policy check (at least 8 chars, 1 digit, 1 lower, 1 upper, 1 non-alphanumeric)
            if (strlen($newPassword) < 8) {
                throw new Exception('Password minimal 8 karakter');
            }
            if (!preg_match('/[A-Z]/', $newPassword)) {
                throw new Exception('Password harus mengandung minimal 1 huruf besar');
            }
            if (!preg_match('/[a-z]/', $newPassword)) {
                throw new Exception('Password harus mengandung minimal 1 huruf kecil');
            }
            if (!preg_match('/[0-9]/', $newPassword)) {
                throw new Exception('Password harus mengandung minimal 1 angka');
            }
            if (!preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
                throw new Exception('Password harus mengandung minimal 1 simbol');
            }
            $updateData['password'] = $newPassword;
        }

        // Update Moodle
        $result = $moodle->updateUser($moodleId, $updateData);

        if ($result['success']) {
            Security::logSecurityEvent("Mahasiswa updated Moodle account: " . $_SESSION['nipd'], 'INFO');
            echo json_encode([
                'status' => 'success',
                'message' => 'Akun Learning berhasil diperbarui'
            ]);
        } else {
            throw new Exception('Gagal update Moodle: ' . ($result['error'] ?? 'Unknown error'));
        }

    } catch (Exception $e) {
        Security::logSecurityEvent("Moodle update error for " . $_SESSION['nipd'] . ": " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

} else if ($aksi == "foto") {
    try {
        // Validate file upload
        if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }

        $file = $_FILES['upload'];

        // Validate file type using Security helper
        $validation = Security::validateFileUpload(
            $file,
            ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], // Allowed MIME types
            2097152 // 2MB max
        );

        if (!$validation['valid']) {
            throw new Exception($validation['error']);
        }

        // Additional check: verify actual file type (not just extension)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, GIF allowed.');
        }

        // Get destination directory
        $destination = realpath(__DIR__ . '/../foto');

        if (!$destination) {
            // Directory doesn't exist, try to create
            $destination = __DIR__ . '/../foto';
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }
            $destination = realpath($destination);
        }

        // Check if directory is writable
        if (!is_writable($destination)) {
            throw new Exception('Upload directory is not writable');
        }

        // Generate filename
        $filename = $destination . "/" . md5($_SESSION['nipd']) . ".jpg";

        // Delete old file if exists
        if (file_exists($filename)) {
            if (!@unlink($filename)) {
                // Log warning but continue (file might be locked)
                error_log("Warning: Could not delete old photo file: " . $filename);
            }
        }

        // Compress and save image
        $hasil = kompresGbr($file["tmp_name"], $filename, 250, 75);

        if ($hasil) {
            // Log successful upload
            Security::logSecurityEvent("Photo uploaded successfully for NIM: " . $_SESSION['nipd'], 'INFO');

            echo json_encode([
                'status' => 'server', // Webix expects 'server' for success
                'success' => true,
                'message' => 'Foto berhasil diupload',
                'filename' => md5($_SESSION['nipd']) . ".jpg",
                'timestamp' => time()
            ]);
        } else {
            throw new Exception('Failed to compress and save image');
        }

    } catch (Exception $e) {
        // Log error
        Security::logSecurityEvent("Photo upload error for NIM " . $_SESSION['nipd'] . ": " . $e->getMessage(), 'ERROR');

        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

} else if ($aksi == "cekfoto") {
    $destination = realpath(__DIR__ . '/../foto');
    $filename = $destination . "/" . md5($_SESSION['nipd']) . ".jpg";

    if (file_exists($filename)) {
        // Get file modification time for cache busting
        $timestamp = filemtime($filename);

        echo json_encode([
            'exists' => true,
            'url' => '../mhs/foto/' . md5($_SESSION['nipd']) . '.jpg?t=' . $timestamp,
            'size' => filesize($filename)
        ]);
    } else {
        echo json_encode([
            'exists' => false,
            'url' => '../gambar/no-foto.jpg'
        ]);
    }

} else if ($aksi == "hapusfoto") {
    try {
        $destination = realpath(__DIR__ . '/../foto');
        $filename = $destination . "/" . md5($_SESSION['nipd']) . ".jpg";

        if (file_exists($filename)) {
            if (unlink($filename)) {
                Security::logSecurityEvent("Photo deleted for NIM: " . $_SESSION['nipd'], 'INFO');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Foto berhasil dihapus'
                ]);
            } else {
                throw new Exception('Gagal menghapus foto');
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Foto tidak ditemukan'
            ]);
        }
    } catch (Exception $e) {
        Security::logSecurityEvent("Photo deletion error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else if ($aksi == "link_google") {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';

        if (empty($token)) {
            throw new Exception('Token Google tidak ditemukan');
        }

        // Verifikasi token dengan Google API via CURL
        $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $payload = json_decode($response, true);

        if ($payload && isset($payload['email'])) {
            $email = $payload['email'];
            $hosted_domain = $payload['hd'] ?? '';

            // Validate Domain
            if ($hosted_domain !== 'poltekindonusa.ac.id') {
                throw new Exception('Maaf, hanya email @poltekindonusa.ac.id yang diperbolehkan.');
            }

            // Update Database - Corrected Query (nipd is in wsia_mahasiswa_pt)
            $db = koneksi();
            $stmt = $db->prepare("UPDATE wsia_mahasiswa 
                                 SET email_poltek = :email 
                                 WHERE xid_pd = (SELECT id_pd FROM wsia_mahasiswa_pt WHERE nipd = :nipd LIMIT 1)");
            $buat = $stmt->execute([
                ':email' => $email,
                ':nipd' => $_SESSION['nipd']
            ]);

            if ($buat) {
                Security::logSecurityEvent("Google Account linked for NIM: " . $_SESSION['nipd'] . " ($email)", 'INFO');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Akun Google berhasil ditautkan: ' . $email
                ]);
            } else {
                throw new Exception('Gagal menyimpan email ke database.');
            }
            $db = null;

        } else {
            throw new Exception('Token Google tidak valid.');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent("Google Link error for NIM " . $_SESSION['nipd'] . ": " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else if ($aksi == "unlink_google") {
    try {
        // Update Database - Set email_poltek to NULL - Corrected Query
        $db = koneksi();
        $stmt = $db->prepare("UPDATE wsia_mahasiswa 
                             SET email_poltek = NULL 
                             WHERE xid_pd = (SELECT id_pd FROM wsia_mahasiswa_pt WHERE nipd = :nipd LIMIT 1)");
        $hapus = $stmt->execute([
            ':nipd' => $_SESSION['nipd']
        ]);

        if ($hapus) {
            Security::logSecurityEvent("Google Account unlinked for NIM: " . $_SESSION['nipd'], 'INFO');
            echo json_encode([
                'status' => 'success',
                'message' => 'Akun Google berhasil diputuskan.'
            ]);
        } else {
            throw new Exception('Gagal memutuskan akun Google.');
        }
        $db = null;

    } catch (Exception $e) {
        Security::logSecurityEvent("Google Unlink error for NIM " . $_SESSION['nipd'] . ": " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
?>