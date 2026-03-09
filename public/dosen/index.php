<?php

// Security Middleware Bootstrap
require_once __DIR__ . '/../lib/security_bootstrap.php';

/**
 * Dosen Index - Main Entry Point
 * Security Update: 2025-10-14
 * 
 * SECURITY IMPROVEMENTS:
 * - Same security fixes as baak/index.php
 * - SQL injection prevention
 * - Secure password hashing
 * - Rate limiting
 * - Session security
 */

// Security: Force HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Security: Configure secure session BEFORE session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800);

// Disable error reporting in production
error_reporting(0);
session_start();

// Security: Set security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com https://static.cloudflareinsights.com https://accounts.google.com https://*.googleapis.com https://*.google.com https://*.gstatic.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com https://accounts.google.com https://*.googleapis.com https://*.google.com https://*.gstatic.com; frame-src 'self' https://accounts.google.com https://*.googleapis.com https://*.google.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://accounts.google.com; img-src 'self' data: https://*.googleusercontent.com https://*.gstatic.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; connect-src 'self' https://accounts.google.com https://*.googleapis.com;");

use Slim\Views\PhpRenderer;
use \Psr\Http\Message\ServerRequestInterface as RequestData;
use \Psr\Http\Message\ResponseInterface as ResponseData;

require '../../config/config.php';
require_once '../lib/Mobile_Detect.php';
require '../lib/tgl.php';
require '../lib/security.php';
require '../vendor/autoload.php';

// Backward compatibility
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

        if (empty($data->user) || empty($data->pass)) {
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'NIDN dan Password harus diisi'
            ]);
            return;
        }

        $user = trim($data->user);
        $password = $data->pass;

        // Rate limiting
        if (!Security::checkRateLimit('dosen_login_' . $user, 5, 900)) {
            Security::logSecurityEvent("Rate limit exceeded for Dosen login: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'
            ]);
            return;
        }

        // Validate NIDN format
        if (!Security::validateNIDN($user)) {
            Security::logSecurityEvent("Invalid NIDN format in login: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Format NIDN tidak valid'
            ]);
            return;
        }

        // Database query with parameter binding
        $db = koneksi();
        $perintah = "SELECT * FROM wsia_dosen WHERE nidn = :nidn LIMIT 1";
        $qry = $db->prepare($perintah);
        $qry->execute([':nidn' => $user]);
        $userData = $qry->fetch(PDO::FETCH_OBJ);
        $ada = $qry->rowCount();

        if ($ada > 0) {
            $isValidPassword = false;

            // Try new password format
            if (password_verify($password, $userData->pass)) {
                $isValidPassword = true;
            }
            // Fallback to legacy format
            else {
                $legacyHash = sha1(md5($password) . $user);
                if ($userData->pass === $legacyHash) {
                    $isValidPassword = true;

                    // Auto-upgrade
                    $newHash = Security::hashPassword($password);
                    $updateStmt = $db->prepare("UPDATE wsia_dosen SET pass = :pass WHERE nidn = :nidn");
                    $updateStmt->execute([
                        ':pass' => $newHash,
                        ':nidn' => $user
                    ]);
                    Security::logSecurityEvent("Password upgraded for dosen: {$user}", 'INFO');
                }
            }

            if ($isValidPassword) {
                // Get active semester
                $qryTAaktif = "SELECT * FROM wsia_semester WHERE krs_aktif='1' LIMIT 1";
                $eksekusiTA = $db->query($qryTAaktif);
                $dataTAaktif = $eksekusiTA->fetch(PDO::FETCH_OBJ);

                $id_smt_aktif = ($eksekusiTA->rowCount() > 0) ? $dataTAaktif->id_smt : "-";

                Security::resetRateLimit('dosen_login_' . $user);

                $sessionID = Security::generateSessionID();

                session_regenerate_id(true);
                $_SESSION['wsiaDOSEN'] = $sessionID;
                $_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');
                $_SESSION['xid_ptk'] = $userData->xid_ptk;
                $_SESSION['nidn'] = $userData->nidn;
                $_SESSION['nm_ptk'] = $userData->nm_ptk;
                $_SESSION['id_smt_aktif'] = $id_smt_aktif;
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['username'] = $user;
                session_write_close();

                Security::logSecurityEvent("Successful Dosen login: {$user}", 'INFO');

                echo json_encode([
                    'berhasil' => 1,
                    'pesan' => 'Berhasil Login',
                    'domain' => DOMAIN,
                    'nidn' => $userData->nidn,
                    'nidnMd5' => md5($userData->nidn),
                    'xid_ptk' => $userData->xid_ptk,
                    'nm_ptk' => $userData->nm_ptk,
                    'apiKey' => $sessionID,
                    'email_poltek' => $userData->email_poltek ?? ''
                ]);
            } else {
                Security::logSecurityEvent("Failed Dosen login (invalid password): {$user}", 'WARNING');
                echo json_encode([
                    'berhasil' => 0,
                    'pesan' => 'NIDN atau Password salah'
                ]);
            }
        } else {
            Security::logSecurityEvent("Failed Dosen login (user not found): {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'NIDN atau Password salah'
            ]);
        }

        $db = null;
    } catch (PDOException $e) {
        Security::logSecurityEvent("Dosen login database error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ]);
    } catch (Exception $e) {
        Security::logSecurityEvent("Dosen login error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
});

// Google Login handler
$appWsia->post('/login-google', function ($request, $response, $args) {
    try {
        $body = $request->getBody();
        $data = json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON Input');
        }

        if (!isset($data->token) || empty($data->token)) {
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Token Google tidak ditemukan'
            ]);
            return;
        }

        $limitKey = 'dosen_google_login_' . md5($_SERVER['REMOTE_ADDR']);
        if (!Security::checkRateLimit($limitKey, 10, 900)) {
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'
            ]);
            return;
        }

        // Verify token with Google via CURL (Safer than file_get_contents)
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . $data->token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $json = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $userInfo = json_decode($json);

        if ($httpCode !== 200 || !$userInfo || isset($userInfo->error)) {
            Security::logSecurityEvent("Invalid Google Token attempt for Dosen", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Token Google tidak valid atau kadaluarsa'
            ]);
            return;
        }

        $email = $userInfo->email;

        // Restrict to @poltekindonusa.ac.id
        if (substr($email, -21) !== '@poltekindonusa.ac.id') {
            Security::logSecurityEvent("Invalid Google Login Domain for Dosen: {$email}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Maaf, hanya email @poltekindonusa.ac.id yang diperbolehkan.'
            ]);
            return;
        }

        // Check if email exists in database
        $db = koneksi();
        $perintah = "SELECT * FROM wsia_dosen WHERE email_poltek = :email LIMIT 1";
        $qry = $db->prepare($perintah);
        $qry->execute([':email' => $email]);
        $userData = $qry->fetch(PDO::FETCH_OBJ);

        if ($qry->rowCount() > 0) {
            // Get active semester
            $qryTAaktif = "SELECT * FROM wsia_semester WHERE krs_aktif='1' LIMIT 1";
            $eksekusiTA = $db->query($qryTAaktif);
            $dataTAaktif = $eksekusiTA->fetch(PDO::FETCH_OBJ);

            $id_smt_aktif = ($eksekusiTA->rowCount() > 0) ? $dataTAaktif->id_smt : "-";

            Security::resetRateLimit($limitKey);

            $sessionID = Security::generateSessionID();

            session_regenerate_id(true);
            $_SESSION['wsiaDOSEN'] = $sessionID;
            $_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');
            $_SESSION['xid_ptk'] = $userData->xid_ptk;
            $_SESSION['nidn'] = $userData->nidn;
            $_SESSION['nm_ptk'] = $userData->nm_ptk;
            $_SESSION['id_smt_aktif'] = $id_smt_aktif;
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['username'] = $userData->nidn;
            session_write_close();

            Security::logSecurityEvent("Successful Dosen Google login: {$email}", 'INFO');

            echo json_encode([
                'berhasil' => 1,
                'pesan' => 'Berhasil Login dengan Google',
                'domain' => DOMAIN,
                'nidn' => $userData->nidn,
                'nidnMd5' => md5($userData->nidn),
                'xid_ptk' => $userData->xid_ptk,
                'nm_ptk' => $userData->nm_ptk,
                'apiKey' => $sessionID,
                'email_poltek' => $userData->email_poltek ?? ''
            ]);
        } else {
            Security::logSecurityEvent("Failed Dosen Google login (email not registered): {$email}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Email ini belum ditautkan, silahkan login menggunakan username dan password terlebih dahulu.'
            ]);
        }

        $db = null;

    } catch (Throwable $e) {
        // More descriptive error logging
        error_log("Google Login Error (Dosen): " . $e->getMessage());
        Security::logSecurityEvent("Dosen Google login error: " . $e->getMessage(), 'ERROR');

        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
    }
});


// Logout handler
$appWsia->get('/logout', function ($request, $response, $args) {
    Security::destroySession('wsiaDOSEN');
    Security::logSecurityEvent("Dosen logout", 'INFO');
    session_destroy();
    exit("<script>window.location='" . DOMAIN . "/dosen/login';</script>");
});

// Check login status
$appWsia->get('/cekLogin', function ($request, $response, $args) {
    if (!Security::validateSession('wsiaDOSEN', 1800)) {
        echo json_encode([
            'masihLogin' => 0,
            'pesan' => 'Status belum login',
            'apiKey' => ''
        ]);
    } else {
        echo json_encode([
            'masihLogin' => 1,
            'pesan' => 'Masih Login',
            'domain' => DOMAIN,
            'nidn' => $_SESSION['nidn'] ?? '',
            'nidnMd5' => md5($_SESSION['nidn'] ?? ''),
            'xid_ptk' => $_SESSION['xid_ptk'] ?? '',
            'nm_ptk' => $_SESSION['nm_ptk'] ?? '',
            'apiKey' => $_SESSION['wsiaDOSEN']
        ]);
    }
});

$appWsia->get('/', function ($request, $response, $args) {
    return $this->renderer->render($response, "/auth.php", $args);
});

$appWsia->get('/main', function ($request, $response, $args) {
    return $this->renderer->render($response, "/dashboard.php", $args);
});

// API routes with whitelist - SECURED
$appWsia->get('/sopingi/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedApis = [
        'dosen',
        'profil',
        'kelas_kuliah',
        'nilai',
        'mahasiswa',
        'kelas_pa',
        'pa',
        'pa_aktifitas',
        'presensi_pdf',
        'khs_pdf',
        'krs_pdf',
        'transkip_pdf',
        'user',
        'semester',
        'validasi',
        'siakad_angkatan',
        'siakad_kelas',
        'satuan_pendidikan',
        'wilayah',
        'hakakses',
        'persen_nilai',
        'aktifitas_bimbingan_pdf',
        'sms',
        'auth-sms'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $api)) {
        Security::logSecurityEvent("Invalid Dosen API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized Dosen API access: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $apiFile = __DIR__ . "/api/" . $api . ".php";
    if (!file_exists($apiFile)) {
        return $response->withStatus(404)->write('API not found');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

$appWsia->post('/sopingi/{api}/{aksi}/{key}/{id}', function ($request, $response, $args) {
    $allowedApis = [
        'dosen',
        'profil',
        'kelas_kuliah',
        'nilai',
        'mahasiswa',
        'kelas_pa',
        'pa',
        'pa_aktifitas',
        'user',
        'semester',
        'validasi',
        'siakad_angkatan',
        'siakad_kelas',
        'hakakses',
        'persen_nilai',
        'sms',
        'auth-sms'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $api)) {
        Security::logSecurityEvent("Invalid Dosen API POST: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized Dosen API POST: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

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

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $api)) {
        Security::logSecurityEvent("Invalid Dosen uploader API: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

$appWsia->run();
