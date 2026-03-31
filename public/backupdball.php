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
    @mkdir($tmp_dir, 0777, true);
}

// 3. Backup Database
$log[] = "=> Dumping Database secara native melalu PHP PDO...";
$sql_file = "{$tmp_dir}/database_{$date}.sql";

function backupDatabaseNative($host, $user, $pass, $dbname, $sql_file) {
    try {
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $fp = fopen($sql_file, 'w');
        if (!$fp) return "Gagal membuat file SQL.";

        $databases = [];
        if ($dbname === "ALL") {
            $stmt = $pdo->query("SHOW DATABASES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                if (!in_array($row[0], ['information_schema', 'performance_schema', 'mysql', 'sys'])) {
                    $databases[] = $row[0];
                }
            }
        } else {
            $databases[] = $dbname;
        }

        foreach ($databases as $db) {
            fwrite($fp, "-- Database: `{$db}`\n");
            fwrite($fp, "CREATE DATABASE IF NOT EXISTS `{$db}`;\n");
            fwrite($fp, "USE `{$db}`;\n\n");
            
            $pdo->exec("USE `{$db}`");
            $tables = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
            
            foreach ($tables as $table) {
                // Jangan gunakan transaction atau table lock global untuk hindari timeout/permission error
                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $createTable = $stmt->fetch(PDO::FETCH_NUM);
                if (isset($createTable[1])) {
                    fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
                    fwrite($fp, $createTable[1] . ";\n\n");
                    
                    $rows = $pdo->query("SELECT * FROM `{$table}`");
                    if ($rows->rowCount() > 0) {
                        $colsCount = $rows->columnCount();
                        while ($row = $rows->fetch(PDO::FETCH_NUM)) {
                            $values = [];
                            for ($i = 0; $i < $colsCount; $i++) {
                                if (!isset($row[$i])) {
                                    $values[] = "NULL";
                                } else {
                                    $values[] = $pdo->quote($row[$i]);
                                }
                            }
                            fwrite($fp, "INSERT INTO `{$table}` VALUES(" . implode(",", $values) . ");\n");
                        }
                    }
                    fwrite($fp, "\n\n");
                }
            }
        }
        fclose($fp);
        return true;
    } catch (Exception $e) {
        return "Error Dumping DB: " . $e->getMessage();
    }
}

// Menjalankan backup DB native (Hostname default '116.206.197.228' mengikuti config atau 'localhost' jika db di internal)
$dbhost = "116.206.197.228"; 
$db_backup_res = backupDatabaseNative($dbhost, $db_user, $db_pass, $db_name, $sql_file);

if ($db_backup_res !== true) {
    // Apabila gagal karena IP public, coba localhost (fallback)
    $db_backup_res = backupDatabaseNative("localhost", $db_user, $db_pass, $db_name, $sql_file);
}

if ($db_backup_res !== true) {
    $log[] = "   [!] " . $db_backup_res;
} else {
    $log[] = "   [v] Berhasil Dump DB";
}

// 4. Ambil Informasi System & Requirement
$log[] = "=> Mengumpulkan informasi server & requirements...";
$sys_info_file = "{$tmp_dir}/system_requirements.txt";

$sysinfo = "=== SYSTEM REQUIREMENTS ===\n";
$sysinfo .= "Date Backup: {$date}\n";
$sysinfo .= "Hostname: " . php_uname('n') . "\n";
$sysinfo .= "OS: " . php_uname('s') . " " . php_uname('r') . " " . php_uname('v') . " " . php_uname('m') . "\n";
$sysinfo .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? "Unknown") . "\n";
$sysinfo .= "PHP Version: " . phpversion() . "\n";
file_put_contents($sys_info_file, $sysinfo);

// 5. Mengompresi File Secara Langsung dengan ZipArchive
$log[] = "=> Mengkompresi ZIP melalui modul PHP ZipArchive...";
$final_zip_path = "{$tmp_dir}/{$final_zip}";

function addFolderToZip($dir, $zipArchive, $zipdir = '') {
    if (!is_dir($dir)) return;
    $files = @scandir($dir);
    if (!$files) return;
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $filePath = $dir . '/' . $file;
        $localPath = $zipdir ? $zipdir . '/' . $file : $file;
        if (is_dir($filePath)) {
            $zipArchive->addEmptyDir($localPath);
            addFolderToZip($filePath, $zipArchive, $localPath);
        } else {
            // Zip file hanya jika readable
            if (is_readable($filePath)) {
                $zipArchive->addFile($filePath, $localPath);
            }
        }
    }
}

$zip = new ZipArchive();
if ($zip->open($final_zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    if (file_exists($sql_file)) {
        $zip->addFile($sql_file, "database_{$date}.sql");
    }
    if (file_exists($sys_info_file)) {
        $zip->addFile($sys_info_file, "system_requirements.txt");
    }
    
    // Add Nginx config
    $log[] = "   Menyertakan /etc/nginx jika dapat diakses...";
    addFolderToZip($nginx_dir, $zip, basename($nginx_dir));
    
    // Add PHP config
    $log[] = "   Menyertakan /etc/php jika dapat diakses...";
    addFolderToZip($php_dir, $zip, basename($php_dir));
    
    $zip->close();
} else {
    $log[] = "   [!] Gagal membuat file ZIP. Pastikan modul PHP zip aktif.";
}

// 6. Pindahkan zip dan Cleanup
$log[] = "=> Menyelesaikan proses dan membersihkan temporary files...";
$is_moved = false;
$final_file_path = $final_zip_path;

if (is_dir($final_destination) && is_writable($final_destination) && file_exists($final_zip_path)) {
    if (@rename($final_zip_path, "{$final_destination}/{$final_zip}")) {
        $final_file_path = "{$final_destination}/{$final_zip}";
        $is_moved = true;
    }
}

// Cleanup txt dan sql
@unlink($sql_file);
@unlink($sys_info_file);

if ($is_moved) {
    @rmdir($tmp_dir);
}

$log[] = "==========================================";
$log[] = "Backup Database & Konfigurasi selesai!";
if (file_exists($final_file_path)) {
    $log[] = "Ukuran File: " . round(filesize($final_file_path) / 1024 / 1024, 2) . " MB";
}
$log[] = "File terakhir tersimpan di: {$final_file_path}";
$log[] = "Catatan: Source Code harap di backup secara terpisah / manual!";
$log[] = "==========================================";

// ==========================================
// AKSES BROWSER: AUTO-DOWNLOAD FILE ZIP
// ==========================================
if (php_sapi_name() !== 'cli') {
    if (file_exists($final_file_path)) {
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

        readfile($final_file_path);

        if (!$is_moved) {
            @unlink($final_file_path);
            @rmdir($tmp_dir);
        }
        exit;
    } else {
        echo "<h1>Gagal membuat ZIP! Periksa log:</h1>";
        echo "<pre>" . implode("\n", $log) . "</pre>";
        echo "<p>Catatan: Kemungkinan hak akses folder tidak mencukupi untuk modul ZipArchive.</p>";
    }
} else {
    echo implode("\n", $log) . "\n";
}
