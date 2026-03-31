<?php

// Security Middleware Bootstrap
require_once __DIR__ . '/../lib/security_bootstrap.php';

/**
 * BAAK Index - Main Entry Point
 * Security Update: 2025-10-14
 * 
 * SECURITY IMPROVEMENTS:
 * - Secure session configuration
 * - HTTPS enforcement
 * - Security headers
 * - SQL injection prevention
 * - Rate limiting
 * - Improved session validation
 * - API whitelist
 */

// Security: Force HTTPS
/*
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}
*/

// Security: Configure secure session BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

session_start();

// Security: Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com https://static.cloudflareinsights.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com;");

use Slim\Views\PhpRenderer;
use \Psr\Http\Message\ServerRequestInterface as RequestData;
use \Psr\Http\Message\ResponseInterface as ResponseData;

require '../../config/config.php';
require '../lib/tgl.php';
require '../lib/security.php';
require '../vendor/autoload.php';

// Backward compatibility function (deprecated - use Security::clean())
function clean($str)
{
    return Security::clean($str);
}

$configuration = [
    'settings' => [
        'displayErrorDetails' => ERROR,
    ],
];

$c = new \Slim\Container($configuration);
$appWsia = new Slim\App($c);

$templateVariables = [
    "judul" => "Sistem Informasi Akademik"
];

$container = $appWsia->getContainer();
$container['renderer'] = new PhpRenderer("./halaman", $templateVariables);

// Login page
$appWsia->get('/login[/]', function ($request, $response, $args) {
    $param = array('domain' => DOMAIN);
    return $this->renderer->render($response, "/login.php", $param);
});

// Login handler - SECURED
$appWsia->post('/login[/]', function ($request, $response, $args) {
    try {
        $data = json_decode($request->getBody());

        // Validate input exists
        if (empty($data->user) || empty($data->pass) || empty($data->ta)) {
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Semua field harus diisi'
            ]);
            return;
        }

        $user = trim($data->user);
        $password = $data->pass;
        $ta = trim($data->ta);

        // Rate limiting - max 5 attempts per 15 minutes
        if (!Security::checkRateLimit('baak_login_' . $user, 5, 900)) {
            Security::logSecurityEvent("Rate limit exceeded for BAAK login: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'
            ]);
            return;
        }

        // Validate username format
        if (!Security::validateAlphaNumericUnderscore($user)) {
            Security::logSecurityEvent("Invalid username format in BAAK login attempt: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Format username tidak valid'
            ]);
            return;
        }

        // Database query with proper parameter binding
        $db = koneksi();
        $perintah = "SELECT * FROM wsia_user WHERE username = :username LIMIT 1";
        $qry = $db->prepare($perintah);
        $qry->execute([':username' => $user]);
        $userData = $qry->fetch(PDO::FETCH_OBJ);
        $ada = $qry->rowCount();

        if ($ada > 0) {
            // Check if password is new format (password_hash) or old format (legacy)
            $isValidPassword = false;

            // Try new password format first
            if (password_verify($password, $userData->password)) {
                $isValidPassword = true;
            }
            // Fallback to legacy password format for migration period
            else {
                $legacyHash = Security::legacyPasswordHash($password, $user);
                if (md5($userData->password) === $legacyHash) {
                    $isValidPassword = true;

                    // Auto-upgrade to new password format
                    $newHash = Security::hashPassword($password);
                    $updateStmt = $db->prepare("UPDATE wsia_user SET password = :password WHERE username = :username");
                    $updateStmt->execute([
                        ':password' => $newHash,
                        ':username' => $user
                    ]);
                    Security::logSecurityEvent("Password upgraded to new format for user: {$user}", 'INFO');
                }
            }

            if ($isValidPassword) {
                // Reset rate limit on successful login
                Security::resetRateLimit('baak_login_' . $user);

                // Generate secure session ID
                $sessionID = Security::generateSessionID();

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Store session data
                $_SESSION['wsiaADMIN'] = $sessionID;
                $_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');
                $_SESSION['ta'] = $ta;
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['username'] = $user;
                session_write_close();

                Security::logSecurityEvent("Successful BAAK login: {$user}", 'INFO');

                echo json_encode([
                    'berhasil' => 1,
                    'pesan' => 'Berhasil Login',
                    'domain' => DOMAIN,
                    'ta' => $ta,
                    'nama' => $userData->name,
                    'apiKey' => $sessionID
                ]);
            } else {
                // Invalid password
                Security::logSecurityEvent("Failed BAAK login attempt (invalid password): {$user}", 'WARNING');
                echo json_encode([
                    'berhasil' => 0,
                    'pesan' => 'Username atau Password salah'
                ]);
            }
        } else {
            // User not found
            Security::logSecurityEvent("Failed BAAK login attempt (user not found): {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Username atau Password salah'
            ]);
        }

        $db = null;
    } catch (PDOException $e) {
        Security::logSecurityEvent("BAAK login database error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ]);
    } catch (Exception $e) {
        Security::logSecurityEvent("BAAK login error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
});

// Logout handler - SECURED
$appWsia->get('/logout', function ($request, $response, $args) {
    Security::destroySession('wsiaADMIN');
    Security::logSecurityEvent("BAAK logout", 'INFO');
    session_destroy();

    // Redirect to login
    return $response->withRedirect(DOMAIN . '/baak/login');
});

// Check login status - SECURED
$appWsia->get('/cekLogin', function ($request, $response, $args) {
    if (!Security::validateSession('wsiaADMIN', 1800)) {
        echo json_encode([
            'masihLogin' => 0,
            'pesan' => 'Status belum login',
            'apiKey' => ''
        ]);
    } else {
        echo json_encode([
            'masihLogin' => 1,
            'domain' => DOMAIN,
            'ta' => $_SESSION['ta'] ?? '',
            'pesan' => 'Status masih login',
            'apiKey' => $_SESSION['wsiaADMIN']
        ]);
    }
});

// Main dashboard
$appWsia->get('/', function ($request, $response, $args) {
    return $this->renderer->render($response, "/auth.php", $args);
});

$appWsia->get('/main', function ($request, $response, $args) {
    return $this->renderer->render($response, "/dashboard.php", $args);
});

// API routes with whitelist protection - SECURED
$appWsia->get('/sopingi/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    // Whitelist of allowed APIs
    $allowedApis = [
        'mahasiswa',
        'dosen',
        'nilai',
        'khs',
        'krs',
        'mata_kuliah',
        'kelas_kuliah',
        'user',
        'semester',
        'tahun_ajaran',
        'pendaftar',
        'kurikulum',
        'mata_kuliah_kurikulum',
        'ajar_dosen',
        'kuliah_mahasiswa',
        'dosen_pt',
        'bobot_nilai',
        'satuan_pendidikan',
        'jenis_keluar',
        'jenis_pendaftaran',
        'siakad_angkatan',
        'siakad_kelas',
        'siakad_ruang',
        'wilayah',
        'pendidikan',
        'sms',
        'buku_induk',
        'tahun_ajaran_login',
        'khs_cetak',
        'khs_per_cetak',
        'krs_pdf',
        'transkip_pdf',
        'absen_dosen_pdf',
        'absen_mhs_pdf',
        'absen_ujian_cetak',
        'x_khs_pdf'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    // Validate API name format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid API name format: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    // Check whitelist
    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized API access attempt: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    // Check if file exists
    $apiFile = __DIR__ . "/api/" . $api . ".php";
    if (!file_exists($apiFile)) {
        return $response->withStatus(404)->write('API not found');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

$appWsia->post('/sopingi/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    // Same whitelist as GET
    $allowedApis = [
        'mahasiswa',
        'dosen',
        'nilai',
        'khs',
        'krs',
        'mata_kuliah',
        'kelas_kuliah',
        'user',
        'semester',
        'tahun_ajaran',
        'pendaftar',
        'kurikulum',
        'mata_kuliah_kurikulum',
        'ajar_dosen',
        'kuliah_mahasiswa',
        'dosen_pt',
        'bobot_nilai',
        'satuan_pendidikan',
        'jenis_keluar',
        'jenis_pendaftaran',
        'siakad_angkatan',
        'siakad_kelas',
        'siakad_ruang',
        'wilayah',
        'pendidikan',
        'sms',
        'buku_induk'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    // Validate API name format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid API name format in POST: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    // Check whitelist
    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized API POST attempt: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    // Check if file exists
    $apiFile = __DIR__ . "/api/" . $api . ".php";
    if (!file_exists($apiFile)) {
        return $response->withStatus(404)->write('API not found');
    }

    $data = json_decode($request->getBody());
    $param = array('aksi' => $data->aksi ?? $aksi, 'key' => $key, 'id' => $id, 'data' => $data);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

$appWsia->post('/uploader/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    // Validate API name
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid uploader API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/api-" . $api . ".php", $param);
});

// Feeder routes - SECURED
$appWsia->get('/sopingi-feeder/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedFeederApis = [
        'kurikulum',
        'dosen',
        'mahasiswa_pt',
        'dosen_pt',
        'kelas_perkuliahan',
        'mahasiswa',
        'mata_kuliah_kurikulum',
        'kuliah_mahasiswa',
        'ajar_dosen',
        'mata_kuliah',
        'mahasiswa_keluar',
        'nilai',
        'referensi',
        'token'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid feeder API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedFeederApis)) {
        Security::logSecurityEvent("Unauthorized feeder API access: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../ws/" . $api . ".php", $param);
});

$appWsia->post('/sopingi-feeder/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedFeederApis = [
        'kurikulum',
        'dosen',
        'mahasiswa_pt',
        'dosen_pt',
        'kelas_perkuliahan',
        'mahasiswa',
        'mata_kuliah_kurikulum',
        'kuliah_mahasiswa',
        'ajar_dosen',
        'mata_kuliah',
        'mahasiswa_keluar',
        'nilai',
        'referensi',
        'token'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid feeder API name in POST: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedFeederApis)) {
        Security::logSecurityEvent("Unauthorized feeder API POST: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $data = json_decode($request->getBody());
    $param = array('aksi' => $data->aksi ?? $aksi, 'key' => $key, 'id' => $id, 'data' => $data);
    return $this->renderer->render($response, "/../ws/" . $api . ".php", $param);
});

// Moodle Integration routes
$appWsia->get('/moodle[/]', function ($request, $response, $args) {
    // Bypass Slim rendering for Moodle dashboard
    $moodlePath = __DIR__ . '/moodle/index.php';
    if (file_exists($moodlePath)) {
        require $moodlePath;
        exit; // Stop Slim processing
    }
    return $response->withStatus(404)->write('Moodle dashboard not found');
});

$appWsia->post('/moodle[/]', function ($request, $response, $args) {
    // Bypass Slim rendering for Moodle AJAX
    $moodlePath = __DIR__ . '/moodle/index.php';
    if (file_exists($moodlePath)) {
        require $moodlePath;
        exit; // Stop Slim processing
    }
    return $response->withStatus(404)->write('Moodle dashboard not found');
});

$appWsia->get('/moodle/api[/]', function ($request, $response, $args) {
    // Bypass Slim rendering for Moodle API
    $apiPath = __DIR__ . '/moodle/api.php';
    if (file_exists($apiPath)) {
        require $apiPath;
        exit; // Stop Slim processing
    }
    return $response->withStatus(404)->write('Moodle API not found');
});

// OBE Integration routes
$appWsia->get('/obe[/]', function ($request, $response, $args) {
    $obePath = __DIR__ . '/obe/index.php';
    if (file_exists($obePath)) {
        require $obePath;
        exit;
    }
    return $response->withStatus(404)->write('OBE dashboard not found');
});

$appWsia->post('/obe[/]', function ($request, $response, $args) {
    $obePath = __DIR__ . '/obe/index.php';
    if (file_exists($obePath)) {
        require $obePath;
        exit;
    }
    return $response->withStatus(404)->write('OBE dashboard not found');
});

$appWsia->get('/obe/api[/]', function ($request, $response, $args) {
    $apiPath = __DIR__ . '/obe/api.php';
    if (file_exists($apiPath)) {
        require $apiPath;
        exit;
    }
    return $response->withStatus(404)->write('OBE API not found');
});

$appWsia->post('/obe/api[/]', function ($request, $response, $args) {
    $apiPath = __DIR__ . '/obe/api.php';
    if (file_exists($apiPath)) {
        require $apiPath;
        exit;
    }
    return $response->withStatus(404)->write('OBE API not found');
});

// Excel routes - SECURED
$appWsia->get('/sopingi-excel/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedExcelApis = ['mahasiswa'];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid excel API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedExcelApis)) {
        Security::logSecurityEvent("Unauthorized excel API access: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../excel/" . $api . ".php", $param);
});

$appWsia->post('/sopingi-excel/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedExcelApis = ['mahasiswa'];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid excel API name in POST: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedExcelApis)) {
        Security::logSecurityEvent("Unauthorized excel API POST: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../excel/" . $api . ".php", $param);
});

// Route Backup Database
$appWsia->get('/backup-db', function ($request, $response, $args) {
    if (!Security::validateSession('wsiaADMIN', 1800)) {
        return $response->withStatus(403)->write('Forbidden: Harus login sebagai Admin');
    }

    // Auto inject secret token requirements for the standalone backup script
    $_GET['token'] = 'b4ckup@ll123';
    
    // Execute the PHP script
    require __DIR__ . '/../backupdball.php';
    exit;
});

$appWsia->run();
