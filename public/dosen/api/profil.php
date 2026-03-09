<?php
if (!isset($key)) { exit(); }
include 'login_auth.php';
if ($key!=$_SESSION['wsiaDOSEN']) { exit(); }

// Security Helper
require_once __DIR__ . '/../../lib/security.php';

if ($aksi=="tampil") {
    $xid_ptk = $_SESSION['xid_ptk'];
    try {
        $db = koneksi();
        // Get Dosen data
        $query = "SELECT xid_ptk, nidn, nm_ptk, email, email_poltek 
                  FROM wsia_dosen 
                  WHERE xid_ptk = :xid_ptk LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':xid_ptk' => $xid_ptk]);
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        $db = null;
        header('Content-Type: application/json');
        echo json_encode($data);
        
    } catch (PDOException $salah) {
        echo json_encode(['error' => $salah->getMessage()]);
    }
} else if ($aksi == "link_google") {
    try {
        $token = $data->token ?? '';
        if (empty($token)) {
            throw new Exception('Token Google tidak ditemukan');
        }

        // Verify token with Google API via CURL
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

            // Update Database
            $db = koneksi();
            $stmt = $db->prepare("UPDATE wsia_dosen SET email_poltek = :email WHERE xid_ptk = :xid_ptk");
            $buat = $stmt->execute([
                ':email' => $email,
                ':xid_ptk' => $_SESSION['xid_ptk']
            ]);

            if ($buat) {
                Security::logSecurityEvent("Google Account linked for Dosen: " . $_SESSION['nidn'] . " ($email)", 'INFO');
                echo json_encode([
                    'berhasil' => 1,
                    'pesan' => 'Akun Google berhasil ditautkan: ' . $email
                ]);
            } else {
                throw new Exception('Gagal menyimpan email ke database.');
            }
            $db = null;

        } else {
            throw new Exception('Token Google tidak valid.');
        }

    } catch (Exception $e) {
        Security::logSecurityEvent("Google Link error for Dosen " . $_SESSION['nidn'] . ": " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => $e->getMessage()
        ]);
    }
} else if ($aksi == "unlink_google") {
    try {
        $db = koneksi();
        $stmt = $db->prepare("UPDATE wsia_dosen SET email_poltek = NULL WHERE xid_ptk = :xid_ptk");
        $hapus = $stmt->execute([
            ':xid_ptk' => $_SESSION['xid_ptk']
        ]);

        if ($hapus) {
            Security::logSecurityEvent("Google Account unlinked for Dosen: " . $_SESSION['nidn'], 'INFO');
            echo json_encode([
                'berhasil' => 1,
                'pesan' => 'Akun Google berhasil diputuskan.'
            ]);
        } else {
            throw new Exception('Gagal memutuskan akun Google.');
        }
        $db = null;

    } catch (Exception $e) {
        Security::logSecurityEvent("Google Unlink error for Dosen " . $_SESSION['nidn'] . ": " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => $e->getMessage()
        ]);
    }
}
?>
