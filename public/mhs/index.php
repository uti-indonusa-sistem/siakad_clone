<?php

// Security Middleware Bootstrap
require_once __DIR__ . '/../lib/security_bootstrap.php';


/**
 * Mahasiswa Index - Main Entry Point
 * Security Update: 2025-10-14
 * 
 * SECURITY IMPROVEMENTS:
 * - Same security fixes as baak/index.php and dosen/index.php
 * - SQL injection prevention
 * - Secure password hashing with SSO sync
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

// Security: Configure secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 1800);

error_reporting(0);
session_start();

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com https://static.cloudflareinsights.com https://accounts.google.com https://*.googleapis.com https://*.google.com https://*.gstatic.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com https://accounts.google.com https://*.googleapis.com https://*.google.com https://*.gstatic.com; frame-src 'self' https://accounts.google.com https://*.googleapis.com https://*.google.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://accounts.google.com; img-src 'self' data: https://*.googleusercontent.com https://*.gstatic.com; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; connect-src 'self' https://accounts.google.com https://*.googleapis.com;");

use Slim\Views\PhpRenderer;
use \Psr\Http\Message\ServerRequestInterface as RequestData;
use \Psr\Http\Message\ResponseInterface as ResponseData;

require '../../config/config.php';
require '../lib/tgl.php';
require '../lib/security.php';
require '../vendor/autoload.php';

function clean($str)
{
    return Security::clean($str);
}

$logger = "";

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

$appWsia->get('/login[/]', function ($request, $response, $args) {
    $param = array('domain' => DOMAIN);
    return $this->renderer->render($response, "/login.php", $param);
});

$appWsia->post('/login[/]', function ($request, $response, $args) {
    try {
        $data = json_decode($request->getBody());

        if (empty($data->user) || empty($data->pass)) {
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'NIM dan Password harus diisi'
            ]);
            return;
        }

        $user = trim($data->user);
        $password = $data->pass;

        // Rate limiting
        if (!Security::checkRateLimit('mhs_login_' . $user, 5, 900)) {
            Security::logSecurityEvent("Rate limit exceeded for Mahasiswa login: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'
            ]);
            return;
        }

        // Validate NIM format
        if (!Security::validateNIM($user)) {
            Security::logSecurityEvent("Invalid NIM format in login: {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Format NIM tidak valid'
            ]);
            return;
        }

        // Database query with parameter binding - SECURED
        $db = koneksi();
        $db2 = koneksi_sso();

        $perintah = "SELECT * FROM wsia_mahasiswa_pt, wsia_mahasiswa, wsia_sms_view 
                     WHERE wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd 
                     AND wsia_sms_view.xid_sms = wsia_mahasiswa_pt.id_sms 
                     AND nipd = :user 
                     LIMIT 1";

        $qry = $db->prepare($perintah);
        $qry->execute([':user' => $user]);
        $userData = $qry->fetch(PDO::FETCH_OBJ);
        $ada = $qry->rowCount();

        if ($ada > 0) {
            $isValidPassword = false;

            // Try new password format first
            if (password_verify($password, $userData->pass)) {
                $isValidPassword = true;
                
                // Auto-upgrade Argon2id to Bcrypt for UPM compatibility (PHP 5.6)
                if (strpos($userData->pass, '$argon2id$') === 0 || strpos($userData->pass, '$argon2i$') === 0) {
                    $newHash = Security::hashPassword($password);
                    $updateStmt = $db->prepare("UPDATE wsia_mahasiswa_pt SET pass = :pass WHERE nipd = :nipd");
                    $updateStmt->execute([
                        ':pass' => $newHash,
                        ':nipd' => $user
                    ]);
                    Security::logSecurityEvent("Password upgraded from Argon2id to Bcrypt for mahasiswa: {$user}", 'INFO');
                }
            }
            // Fallback to legacy format
            else {
                $legacyHash = sha1(md5($password) . $user);
                if ($userData->pass === $legacyHash) {
                    $isValidPassword = true;

                    // Auto-upgrade password
                    $newHash = Security::hashPassword($password);
                    $updateStmt = $db->prepare("UPDATE wsia_mahasiswa_pt SET pass = :pass WHERE nipd = :nipd");
                    $updateStmt->execute([
                        ':pass' => $newHash,
                        ':nipd' => $user
                    ]);
                    Security::logSecurityEvent("Password upgraded for mahasiswa: {$user}", 'INFO');
                }
            }

            if ($isValidPassword) {
                // SSO Synchronization - SECURED
                try {
                    $cekSSO = $db2->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                    $cekSSO->execute([$user]);
                    $dtSSO = $cekSSO->fetch(PDO::FETCH_OBJ);
                    $adaSSO = $cekSSO->rowCount();

                    $ssoHash = Security::hashPassword($password);

                    // Insert to SSO if not exists
                    if ($adaSSO == 0) {
                        $insSSO = $db2->prepare("INSERT INTO users (name, username, password, role, prodi, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                        $insSSO->execute([
                            $userData->nm_pd,
                            $userData->nipd,
                            $ssoHash,
                            3,
                            substr($userData->nipd, 0, 1),
                            date('Y-m-d H:i:s')
                        ]);
                    }
                    // Update SSO password if different
                    else if (!password_verify($password, $dtSSO->password)) {
                        $updtPass = $db2->prepare("UPDATE users SET password = ?, updated_at = ? WHERE username = ?");
                        $updtPass->execute([
                            $ssoHash,
                            date('Y-m-d H:i:s'),
                            $userData->nipd
                        ]);
                    }

                    // Update SSO name if different
                    if ($adaSSO > 0 && $userData->nm_pd != $dtSSO->name) {
                        $updtName = $db2->prepare("UPDATE users SET name = ?, updated_at = ? WHERE username = ?");
                        $updtName->execute([
                            $userData->nm_pd,
                            date('Y-m-d H:i:s'),
                            $userData->nipd
                        ]);
                    }
                } catch (PDOException $e) {
                    // Log SSO error but don't fail login
                    Security::logSecurityEvent("SSO sync error for {$user}: " . $e->getMessage(), 'WARNING');
                }

                // Get active semester
                $qryTAaktif = "SELECT * FROM wsia_semester WHERE krs_aktif='1' LIMIT 1";
                $eksekusiTA = $db->query($qryTAaktif);
                $dataTAaktif = $eksekusiTA->fetch(PDO::FETCH_OBJ);

                $id_smt_aktif = ($eksekusiTA->rowCount() > 0) ? $dataTAaktif->id_smt : "-";

                Security::resetRateLimit('mhs_login_' . $user);

                $sessionID = Security::generateSessionID();

                session_regenerate_id(true);
                $_SESSION['wsiaMHS'] = $sessionID;
                $_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');
                $_SESSION['xid_pd'] = $userData->xid_pd;
                $_SESSION['xid_reg_pd'] = $userData->xid_reg_pd;
                $_SESSION['nipd'] = $userData->nipd;
                $_SESSION['nm_pd'] = $userData->nm_pd;
                $_SESSION['kelas'] = $userData->kelas;
                $_SESSION['id_sms'] = $userData->id_sms;
                $_SESSION['id_smt_aktif'] = $id_smt_aktif;
                $_SESSION['mulai_smt'] = $userData->mulai_smt;
                $_SESSION['no_pend'] = $userData->no_pend ?? $userData->xid_reg_pd;
                $_SESSION['jenis_daftar'] = $userData->id_jns_daftar;
                $_SESSION['kode_prodi'] = $userData->kode_prodi;
                $_SESSION['nm_prodi'] = $userData->nm_prodi;
                $_SESSION['login_time'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['username'] = $user;
                session_write_close();

                Security::logSecurityEvent("Successful Mahasiswa login: {$user}", 'INFO');

                echo json_encode([
                    'berhasil' => 1,
                    'pesan' => 'Berhasil Login',
                    'domain' => DOMAIN,
                    'nipd' => $userData->nipd,
                    'nipdMd5' => md5($userData->nipd),
                    'xid_reg_pd' => $userData->xid_reg_pd,
                    'nm_pd' => $userData->nm_pd,
                    'mulai_smt' => $userData->mulai_smt,
                    'apiKey' => $sessionID,
                    'email_poltek' => $userData->email_poltek ?? ''
                ]);
            } else {
                Security::logSecurityEvent("Failed Mahasiswa login (invalid password): {$user}", 'WARNING');
                echo json_encode([
                    'berhasil' => 0,
                    'pesan' => 'NIM atau Password salah'
                ]);
            }
        } else {
            Security::logSecurityEvent("Failed Mahasiswa login (user not found): {$user}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'NIM atau Password salah'
            ]);
        }

        $db = null;
        $db2 = null;
    } catch (PDOException $e) {
        Security::logSecurityEvent("Mahasiswa login database error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
        ]);
    } catch (Exception $e) {
        Security::logSecurityEvent("Mahasiswa login error: " . $e->getMessage(), 'ERROR');
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan. Silakan coba lagi.'
        ]);
    }
});

// Google Login Handler
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

        $limitKey = 'mhs_google_login_' . md5($_SERVER['REMOTE_ADDR']);
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
            Security::logSecurityEvent("Invalid Google Token attempt for Mahasiswa", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Token Google tidak valid atau kadaluarsa'
            ]);
            return;
        }

        $email = $userInfo->email;

        // Restrict to @poltekindonusa.ac.id
        if (substr($email, -21) !== '@poltekindonusa.ac.id') {
            Security::logSecurityEvent("Invalid Google Login Domain for Mahasiswa: {$email}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Maaf, hanya email @poltekindonusa.ac.id yang diperbolehkan.'
            ]);
            return;
        }

        // Database query with parameter binding - SECURED
        $db = koneksi();

        // Find user by email_poltek
        $perintah = "SELECT * FROM wsia_mahasiswa_pt, wsia_mahasiswa, wsia_sms_view 
                     WHERE wsia_mahasiswa_pt.id_pd=wsia_mahasiswa.xid_pd 
                     AND wsia_sms_view.xid_sms = wsia_mahasiswa_pt.id_sms 
                     AND wsia_mahasiswa.email_poltek = :email 
                     LIMIT 1";

        $qry = $db->prepare($perintah);
        $qry->execute([':email' => $email]);
        $userData = $qry->fetch(PDO::FETCH_OBJ);
        $ada = $qry->rowCount();

        if ($ada > 0) {
            // Get active semester
            $qryTAaktif = "SELECT * FROM wsia_semester WHERE krs_aktif='1' LIMIT 1";
            $eksekusiTA = $db->query($qryTAaktif);
            $dataTAaktif = $eksekusiTA->fetch(PDO::FETCH_OBJ);

            $id_smt_aktif = ($eksekusiTA->rowCount() > 0) ? $dataTAaktif->id_smt : "-";

            Security::resetRateLimit($limitKey);

            $sessionID = Security::generateSessionID();

            session_regenerate_id(true);
            $_SESSION['wsiaMHS'] = $sessionID;
            $_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');
            $_SESSION['xid_pd'] = $userData->xid_pd;
            $_SESSION['xid_reg_pd'] = $userData->xid_reg_pd;
            $_SESSION['nipd'] = $userData->nipd;
            $_SESSION['nm_pd'] = $userData->nm_pd;
            $_SESSION['kelas'] = $userData->kelas;
            $_SESSION['id_sms'] = $userData->id_sms;
            $_SESSION['id_smt_aktif'] = $id_smt_aktif;
            $_SESSION['mulai_smt'] = $userData->mulai_smt;
            $_SESSION['no_pend'] = $userData->no_pend;
            $_SESSION['jenis_daftar'] = $userData->jenis_daftar;
            $_SESSION['kode_prodi'] = $userData->kode_prodi;
            $_SESSION['nm_prodi'] = $userData->nm_prodi;
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['username'] = $userData->nipd;
            session_write_close();

            Security::logSecurityEvent("Successful Mahasiswa Google login: {$email}", 'INFO');

            echo json_encode([
                'berhasil' => 1,
                'pesan' => 'Berhasil Login dengan Google',
                'domain' => DOMAIN,
                'nipd' => $userData->nipd,
                'nipdMd5' => md5($userData->nipd),
                'xid_reg_pd' => $userData->xid_reg_pd,
                'nm_pd' => $userData->nm_pd,
                'mulai_smt' => $userData->mulai_smt,
                'apiKey' => $sessionID,
                'email_poltek' => $userData->email_poltek ?? ''
            ]);
        } else {
            Security::logSecurityEvent("Failed Mahasiswa Google login (email not registered): {$email}", 'WARNING');
            echo json_encode([
                'berhasil' => 0,
                'pesan' => 'Email ini belum ditautkan, silahkan login menggunakan username dan password terlebih dahulu.'
            ]);
        }

        $db = null;
    } catch (Throwable $e) {
        // More descriptive error logging
        error_log("Google Login Error (MHS): " . $e->getMessage());
        Security::logSecurityEvent("Mahasiswa Google login error: " . $e->getMessage(), 'ERROR');

        // Return JSON error even if exception occurs
        echo json_encode([
            'berhasil' => 0,
            'pesan' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
        ]);
    }
});

$appWsia->get('/logout', function ($request, $response, $args) {
    Security::destroySession('wsiaMHS');
    Security::logSecurityEvent("Mahasiswa logout", 'INFO');
    session_destroy();
    exit("<script>window.location='" . DOMAIN . "/mhs/login';</script>");
});

$appWsia->get('/cekLogin', function ($request, $response, $args) {
    if (!Security::validateSession('wsiaMHS', 1800)) {
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
            'nipd' => $_SESSION['nipd'] ?? '',
            'nipdMd5' => md5($_SESSION['nipd'] ?? ''),
            'xid_reg_pd' => $_SESSION['xid_reg_pd'] ?? '',
            'nm_pd' => $_SESSION['nm_pd'] ?? '',
            'mulai_smt' => $_SESSION['mulai_smt'] ?? '',
            'apiKey' => $_SESSION['wsiaMHS']
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
        'mahasiswa',
        'profil',
        'hakakses',
        'krs',
        'khs',
        'nilai',
        'transkip',
        'jadwal',
        'presensi',
        'pa',
        'tagihan',
        'pembayaran',
        'kuesioner',
        'informasi',
        'pengumuman',
        'semester',
        'kelas_kuliah',
        'krs_pdf',
        'khs_pdf',
        'transkip_pdf',
        'kartu_ujian_pdf',
        'ktm_pdf',
        'cek_tagihan_ujian',
        'wilayah',
        'dosen',
        'siakad_kelas',
        'sms'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid Mahasiswa API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized Mahasiswa API access: {$api}", 'WARNING');
        return $response->withStatus(403)->write('Forbidden');
    }

    $apiFile = __DIR__ . "/api/" . $api . ".php";
    if (!file_exists($apiFile)) {
        return $response->withStatus(404)->write('API not found');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

// Route for optional ID (3 params) - with optional trailing slash
$appWsia->get('/sopingi/{api}/{aksi}/{key}[/]', function ($request, $response, $args) {
    $allowedApis = [
        'mahasiswa',
        'profil',
        'hakakses',
        'krs',
        'khs',
        'nilai',
        'transkip',
        'jadwal',
        'presensi',
        'pa',
        'tagihan',
        'pembayaran',
        'kuesioner',
        'informasi',
        'pengumuman',
        'semester',
        'kelas_kuliah',
        'krs_pdf',
        'khs_pdf',
        'transkip_pdf',
        'kartu_ujian_pdf',
        'ktm_pdf',
        'cek_tagihan_ujian',
        'wilayah',
        'dosen',
        'siakad_kelas',
        'sms'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = null; // ID is null/optional

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid Mahasiswa API name: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized Mahasiswa API access: {$api}", 'WARNING');
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
        'mahasiswa',
        'profil',
        'hakakses',
        'krs',
        'khs',
        'nilai',
        'transkip',
        'jadwal',
        'presensi',
        'pa',
        'tagihan',
        'pembayaran',
        'kuesioner',
        'informasi',
        'pengumuman',
        'semester',
        'kelas_kuliah',
        'wilayah',
        'dosen',
        'siakad_kelas',
        'sms'
    ];

    $api = $request->getAttribute('api');
    $aksi = $request->getAttribute('aksi');
    $key = $request->getAttribute('key');
    $id = $request->getAttribute('id');

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid Mahasiswa API POST: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    if (!in_array($api, $allowedApis)) {
        Security::logSecurityEvent("Unauthorized Mahasiswa API POST: {$api}", 'WARNING');
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

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
        Security::logSecurityEvent("Invalid Mahasiswa uploader API: {$api}", 'WARNING');
        return $response->withStatus(400)->write('Invalid API name');
    }

    $param = array('aksi' => $aksi, 'key' => $key, 'id' => $id);
    return $this->renderer->render($response, "/../api/" . $api . ".php", $param);
});

$appWsia->run();
