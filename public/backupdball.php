<?php

// ==========================================
// SCRIPT BACKUP SERVER - NATIVE PHP & ANTI-TIMEOUT
// ==========================================

// Proteksi Keamanan Sederhana:
$secret_token = "b4ckup@ll123";
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    header("HTTP/1.1 403 Forbidden");
    die("<h1>403 Access Denied</h1><p>Invalid Token.</p>");
}

// Mencegah timeout jika proses berjalan lama dan alokasi memori
@ini_set('max_execution_time', '0');
@set_time_limit(0);
@ini_set('memory_limit', '2048M'); 
date_default_timezone_set('Asia/Jakarta');

// Jika Anda menambahkan ?download=file.zip di URL, server hanya akan melayani file tersebut
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $dl_path = base64_decode($_GET['download']);
    if (file_exists($dl_path)) {
        if (ob_get_length()) ob_end_clean();
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($dl_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($dl_path));
        readfile($dl_path);
        @unlink($dl_path);
        exit;
    } else {
        die("File " . htmlspecialchars($dl_path) . " sudah tidak ada atau kadaluarsa.");
    }
}

// 1. Konfigurasi
$date = date("Ymd_His");
$final_zip = "server_cfg_db_{$date}.zip";
$final_destination = "/home/utis";

$db_user = "uti-check";
$db_pass = "haamA0iYA6^7aj8e*#";
// NOTE: Jika membackup ALL masih menyebabkan Gateway Time-out, ubah ALL menjadi 'siakad_clone'
$db_name = "ALL"; 

$nginx_dir = "/etc/nginx";
$php_dir = "/etc/php";

// NONAKTIFKAN BUFFER AGAR NGINX TIDAK TIMEOUT (Real-time send output)
if (php_sapi_name() !== 'cli') {
    header("Content-type: text/html; charset=utf-8");
    // Print blank characters to flush early browsers buffer
    echo str_pad("", 1024, " "); 
    echo "<title>Sistem Backup Server</title>";
    echo "<h3>Memulai Proses Backup... Jangan Tutup Halaman Ini!</h3><pre>";
    @ob_flush();
    @flush();
}

function logger($msg) {
    if (php_sapi_name() !== 'cli') {
        echo $msg . "\n";
        @ob_flush();
        @flush();
    } else {
        echo $msg . "\n";
    }
}

logger("Mulai proses backup khusus Database & Konfigurasi... (Bypassing Time-out)");

// 2. Siapkan Direktori Sementara Kecil di /tmp
$tmp_dir = "/tmp/backup_data_{$date}";
if (!is_dir($tmp_dir)) {
    @mkdir($tmp_dir, 0777, true);
}

// 3. Backup Database
logger("=> Dumping Database secara Native melalui PHP PDO...");
logger("   [INFO] Ini akan memakan waktu lama. Nginx ditahan agar tidak putus koneksi...");
$sql_file = "{$tmp_dir}/database_{$date}.sql";

function backupDatabaseNative($host, $user, $pass, $dbname, $sql_file) {
    try {
        // NON-BUFFERED query untuk menghemat RAM ekstrim (PDO tidak load seluruh result ke RAM)
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, array(
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
        ));
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
            logger("   -> Ekspor Database: {$db}...");
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
                $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $createTable = $stmt->fetch(PDO::FETCH_NUM);
                if (isset($createTable[1])) {
                    fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
                    fwrite($fp, $createTable[1] . ";\n\n");
                    
                    $rows = $pdo->query("SELECT * FROM `{$table}`");
                    
                    $rowCount = 0;
                    $colsCount = $rows->columnCount();
                    
                    while ($row = $rows->fetch(PDO::FETCH_NUM)) {
                        $values = [];
                        for ($i = 0; $i < $colsCount; $i++) {
                            if (!isset($row[$i])) {
                                $values[] = "NULL";
                            } else {
                                $values[] = $pdo->quote((string)$row[$i]);
                            }
                        }
                        fwrite($fp, "INSERT INTO `{$table}` VALUES(" . implode(",", $values) . ");\n");
                        
                        $rowCount++;
                        // Kirim sinyal setiap 5000 row DB agar Timeout NGINX di-reset
                        if ($rowCount % 5000 == 0) {
                            echo ". ";
                            @ob_flush(); @flush();
                        }
                    }
                    fwrite($fp, "\n\n");
                }
            }
            logger("   -> Selesai ekspor DB: {$db}");
        }
        fclose($fp);
        return true;
    } catch (Exception $e) {
        return "Error Dumping DB: " . $e->getMessage();
    }
}

// Localhost fallback host resolution
$dbhost = "116.206.197.228"; 
$db_backup_res = backupDatabaseNative($dbhost, $db_user, $db_pass, $db_name, $sql_file);

if ($db_backup_res !== true) {
    $db_backup_res = backupDatabaseNative("localhost", $db_user, $db_pass, $db_name, $sql_file);
}

if ($db_backup_res !== true) {
    logger("   [!] " . $db_backup_res);
} else {
    logger("   [v] Berhasil Dump DB");
}

// 4. Ambil Informasi System & Requirement
logger("=> Mengumpulkan informasi server & requirements...");
$sys_info_file = "{$tmp_dir}/system_requirements.txt";

$sysinfo = "=== SYSTEM REQUIREMENTS ===\n";
$sysinfo .= "Date Backup: {$date}\n";
$sysinfo .= "Hostname: " . php_uname('n') . "\n";
$sysinfo .= "OS: " . php_uname('s') . " " . php_uname('r') . " " . php_uname('v') . " " . php_uname('m') . "\n";
$sysinfo .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? "Unknown") . "\n";
$sysinfo .= "PHP Version: " . phpversion() . "\n";
file_put_contents($sys_info_file, $sysinfo);

// 5. Mengompresi File Secara Langsung dengan ZipArchive
logger("=> Mengkompresi menjadi ZIP (Memakan waktu beberapa menit)...");
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
    
    logger("   Menyertakan /etc/nginx jika dapat diakses...");
    addFolderToZip($nginx_dir, $zip, basename($nginx_dir));
    
    logger("   Menyertakan /etc/php jika dapat diakses...");
    addFolderToZip($php_dir, $zip, basename($php_dir));
    
    $zip->close();
} else {
    logger("   [!] Gagal membuat file ZIP. Pastikan modul PHP zip aktif.");
}

// 6. Pindahkan zip dan Cleanup
logger("=> Menyelesaikan proses dan membersihkan temporary files...");
$is_moved = false;
$final_file_path = $final_zip_path;

if (is_dir($final_destination) && is_writable($final_destination) && file_exists($final_zip_path)) {
    if (@rename($final_zip_path, "{$final_destination}/{$final_zip}")) {
        $final_file_path = "{$final_destination}/{$final_zip}";
        $is_moved = true;
    }
}

@unlink($sql_file);
@unlink($sys_info_file);

if ($is_moved) {
    @rmdir($tmp_dir);
}

logger("==========================================");
logger("Backup Database & Konfigurasi selesai!");
if (file_exists($final_file_path)) {
    logger("Ukuran File: " . round(filesize($final_file_path) / 1024 / 1024, 2) . " MB");
}
logger("File terakhir tersimpan di: {$final_file_path}");
logger("==========================================");

// ==========================================
// AKSES BROWSER: LINK DOWNLOAD MANUAL OTOMATIS
// ==========================================
if (php_sapi_name() !== 'cli') {
    if (file_exists($final_file_path)) {
        $dl_base64 = base64_encode($final_file_path);
        echo "</pre>";
        echo "<br><br><a id='btnDownload' href='?token=".$_GET['token']."&download=".$dl_base64."' style='padding:15px; background:#007bff; color:white; font-size:18px; text-decoration:none; font-family:sans-serif; border-radius:5px;'>DOWNLOAD FILE REKAMAN ZIP</a>";
        echo '<br><br><small>Men-download otomatis dalam 3 detik...</small>';
        echo '<script>setTimeout(function(){ document.getElementById("btnDownload").click(); }, 3000);</script>';
    } else {
        echo "<br><b>Gagal menyiapkan file ZIP untuk diunduh.</b>";
    }
}
