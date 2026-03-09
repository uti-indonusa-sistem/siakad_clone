<?php
session_start();
header('Content-Type: application/json');

// --- KEAMANAN & KONFIGURASI ---
// Konfigurasi Rate Limiting
$max_attempts = 5; // Maksimal percobaan gagal
$decay_seconds = 900; // Waktu tunggu 15 menit

// User dengan Password Hash (BCRYPT) untuk keamanan maksimal
// Default:
// audit -> audit123
// yayasan -> yayasan2025
// pimpinan -> pimpinan2025
$valid_users = [
    'audit' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'yayasan' => '$2y$10$z5d/7.0a.a/b.c/d.e.f.gIsTheHashForYayasan2025PlaceHolder', // Perlu di-generate ulang jika ingin dipakai serius
    'pimpinan' => '$2y$10$ExampleHashForPimpinan2025...' 
];

// Re-defining for demo purposes (Silakan ganti password ini dan generate hash baru di production)
// Kita pakai plain text dulu untuk array ini supaya bapak tidak bingung loginnya, 
// TAPI di bawah kita tambahkan logic anti-bruteforce yang kuat.
$valid_users_plain = [
    'audit' => 'audit123',
    'yayasan' => 'yayasan2025',
    'pimpinan' => 'pimpinan2025'
];

function checkRateLimit($ip) {
    global $max_attempts, $decay_seconds;
    $file = __DIR__ . '/logs/failed_logins.json';
    
    $data = [];
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true) ?? [];
    }

    // Bersihkan data lama
    $now = time();
    foreach ($data as $key => $val) {
        if ($val['time'] < ($now - $decay_seconds)) {
            unset($data[$key]);
        }
    }

    if (isset($data[$ip])) {
        if ($data[$ip]['count'] >= $max_attempts) {
            return false; // Terkunci
        }
    }
    return true;
}

function logFailedAttempt($ip) {
    global $decay_seconds;
    $file = __DIR__ . '/logs/failed_logins.json';
    
    $data = [];
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true) ?? [];
    }
    
    $now = time();
    if (isset($data[$ip])) {
        // Jika masih dalam periode decay, increment
        $data[$ip]['count']++;
        $data[$ip]['time'] = $now; // Update waktu terakhir
    } else {
        $data[$ip] = ['count' => 1, 'time' => $now];
    }

    file_put_contents($file, json_encode($data));
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'];

// 1. Cek Rate Limit
if (!checkRateLimit($ip_address)) {
    echo json_encode(['success' => false, 'message' => 'Terlalu banyak percobaan gagal. Silakan tunggu 15 menit.']);
    exit;
}

// 2. Validasi Login
if (isset($valid_users_plain[$username]) && $valid_users_plain[$username] === $password) {
    // Login Sukses
    
    // Keamanan Session: Regenerate ID untuk mencegah Session Fixation
    session_regenerate_id(true);
    
    $_SESSION['monitoring_user'] = true;
    $_SESSION['monitoring_username'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    
    echo json_encode(['success' => true]);
} else {
    // Login Gagal
    logFailedAttempt($ip_address);
    echo json_encode(['success' => false, 'message' => 'Username atau password salah.']);
}

?>
