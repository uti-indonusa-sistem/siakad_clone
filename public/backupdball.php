<?php

// ==========================================
// SCRIPT BACKUP SERVER - HANYA DATABASE & KONFIGURASI
// ==========================================

// Proteksi Keamanan Sederhana:
// Mencegah file ini di-download oleh sembarang orang di public
$secret_token = "b4ckup@ll123";
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    header("HTTP/1.1 403 Forbidden");
    die("<h1>403 Access Denied</h1><p>Invalid Token.</p>");
}

// Mencegah timeout jika proses berjalan lama dan alokasi memori
set_time_limit(0);
ini_set('memory_limit', '512M');
date_default_timezone_set('Asia/Jakarta');

// 1. Konfigurasi
$date = date("Ymd_His");
$final_zip = "server_cfg_db_{$date}.zip";
$final_destination = "/home/utis";

// Konfigurasi Database
$db_user = "uti-check";
$db_pass = "haamA0iYA6^7aj8e*#";
$db_name = "ALL";

// Direktori Konfigurasi yang dibackup (TANPA Source Code)
$nginx_dir = "/etc/nginx";
$php_dir = "/etc/php";

$log = [];
$log[] = "Mulai proses backup khusus Database & Konfigurasi...";

// 2. Siapkan Direktori Sementara Kecil di /tmp
$tmp_dir = "/tmp/backup_data_{$date}";
if (!is_dir($tmp_dir)) {
    mkdir($tmp_dir, 0777, true);
}

// 3. Backup Database
$log[] = "=> Dumping Database...";
$sql_file = "{$tmp_dir}/database_{$date}.sql";
if ($db_name === "ALL") {
    $cmd_dump = "mysqldump -u'{$db_user}' -p'{$db_pass}' --all-databases > '{$sql_file}' 2>/dev/null";
} else {
    $cmd_dump = "mysqldump -u'{$db_user}' -p'{$db_pass}' '{$db_name}' > '{$sql_file}' 2>/dev/null";
}
shell_exec($cmd_dump);

// 4. Ambil Informasi System & Requirement
$log[] = "=> Mengumpulkan informasi server & requirements...";
$sys_info_file = "{$tmp_dir}/system_requirements.txt";

$sysinfo = "=== SYSTEM REQUIREMENTS ===\n";
$sysinfo .= "Date Backup: {$date}\n";
$sysinfo .= "Hostname: " . trim((string) shell_exec("hostname")) . "\n";
$sysinfo .= "OS: " . trim((string) shell_exec("grep PRETTY_NAME /etc/os-release | cut -d= -f2 | tr -d '\"'")) . "\n";
$sysinfo .= "--------------------------------\n";
$sysinfo .= trim((string) shell_exec("nginx -v 2>&1")) ?: "Nginx not installed";
$sysinfo .= "\n";
$sysinfo .= trim((string) shell_exec("mysql -V 2>&1")) ?: "MySQL/MariaDB not installed";
$sysinfo .= "\n";
$sysinfo .= trim((string) shell_exec("php -v 2>&1")) ?: "PHP not installed";
$sysinfo .= "\n";

file_put_contents($sys_info_file, $sysinfo);

// Ambil list paket terinstall
shell_exec("dpkg -l | grep -E '^ii' | awk '{print $2 \" \" $3}' > '{$tmp_dir}/installed_packages.txt' 2>/dev/null");

// 5. Mengompresi File Secara Langsung
$log[] = "=> Mengkompresi ZIP...";
$final_zip_path = "{$tmp_dir}/{$final_zip}";

// Hapus slash di awal direktori untuk eksekusi relatif dari folder root "/"
$nginx_zip_path = ltrim($nginx_dir, '/');
$php_zip_path = ltrim($php_dir, '/');

// Pastikan command zip berjalan di root
$cmd_zip = "cd / && zip -q -r '{$final_zip_path}' '{$nginx_zip_path}' '{$php_zip_path}' 2>/dev/null";
shell_exec($cmd_zip);

// Tambahkan SQL dan file txt ke dalam file zip tersebut
$cmd_zip_update = "cd '{$tmp_dir}' && zip -q -u '{$final_zip}' 'database_{$date}.sql' 'system_requirements.txt' 'installed_packages.txt' 2>/dev/null";
shell_exec($cmd_zip_update);

// 6. Pindahkan zip dan Cleanup
$log[] = "=> Menyelesaikan proses dan membersihkan temporary files...";
$is_moved = false;
$final_file_path = $final_zip_path;

// Pindahkan ke final destination HANYA jika folder tersebut bisa ditulis oleh web-server user (misal www-data)
if (is_dir($final_destination) && is_writable($final_destination)) {
    if (rename($final_zip_path, "{$final_destination}/{$final_zip}")) {
        $final_file_path = "{$final_destination}/{$final_zip}";
        $is_moved = true;
    }
}

// Cleanup txt dan sql
@unlink("{$tmp_dir}/database_{$date}.sql");
@unlink("{$tmp_dir}/system_requirements.txt");
@unlink("{$tmp_dir}/installed_packages.txt");

if ($is_moved) {
    // Apabila zip sudah terpindah ke home, hapus folder tmp-nya
    @rmdir($tmp_dir);
}

$log[] = "==========================================";
$log[] = "Backup Database & Konfigurasi selesai!";
$log[] = "File terakhir tersimpan di: {$final_file_path}";
$log[] = "Catatan: Source Code harap di backup secara terpisah / manual!";
$log[] = "==========================================";

// ==========================================
// AKSES BROWSER: AUTO-DOWNLOAD FILE ZIP
// ==========================================
if (php_sapi_name() !== 'cli') {
    // Jika diakses via browser web, otomatis download ZIP
    if (file_exists($final_file_path)) {
        // Matikan output buffer tambahan agar transfer file zip bersih
        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($final_file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($final_file_path));

        // Baca file ke output browser
        readfile($final_file_path);

        // Jika file zip tidak dipindah ke /home/utis (karena masalah permission), hapus dari folder tmp setelah didownload untuk hemat space
        if (!$is_moved) {
            @unlink($final_file_path);
            @rmdir($tmp_dir);
        }
        exit;
    } else {
        echo "<h1>Gagal membuat ZIP! Periksa log:</h1>";
        echo "<pre>" . implode("\n", $log) . "</pre>";
        echo "<p>Catatan: Kemungkinan user php (<i>www-data</i>) tidak memiliki hak akses yang cukup untuk menjalankan command <code>zip</code> atau tidak dapat membaca direktori nginx/php.</p>";
    }
} else {
    // Jika masih diakses via CLI / Terminal biasa
    echo implode("\n", $log) . "\n";
}
