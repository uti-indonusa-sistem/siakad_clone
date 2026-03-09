<?php
// Database credentials retrieved from config/config.php
$dbhost = "localhost";
$dbuser = "usiakad";
$dbpass = "%Lr#g?I+UR)Q";
$dbname = "siakaddb";

echo "=== DIAGNOSIS SYNC MOODLE ===\n";
try {
    $db = new PDO("mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);

    // 1. Ambil Log Terakhir
    $stmt = $db->query("SELECT * FROM moodle_sync_log ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "[LOG INFO]\n";
        echo "Waktu: " . $row['created_at'] . "\n";
        echo "Aksi: " . $row['action'] . "\n";

        $results = json_decode($row['results'], true);
        if (isset($results['errors']) && !empty($results['errors'])) {
            echo "\n[ERROR(S) FOUND]\n";
            print_r($results['errors']);
        } else {
            echo "\n[SUMMARY RESULTS]\n";
            print_r($results);
        }
    } else {
        echo "Belum ada log di tabel moodle_sync_log.\n";
    }

} catch (Exception $e) {
    echo "ERROR SISTEM: " . $e->getMessage() . "\n";
}
