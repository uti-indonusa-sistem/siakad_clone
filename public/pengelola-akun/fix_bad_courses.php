<?php
// Script untuk mendeteksi dan menghapus course dengan format 'KODE-KELAS' (tanpa tahun)
// Save as: public/pengelola-akun/fix_bad_courses.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../ws/MoodleWebService.php';

header('Content-Type: text/html'); // Changed to text/html for HTML output

$db = koneksi();
$moodle = new MoodleWebService();
$semesterId = '20251'; // Ganti jika perlu
$limit = 50; // Batch size kecil
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

echo "=== FIX BAD COURSES (FORMAT: KODE-KELAS) ===<br>";
echo "Target Semester: $semesterId | Offset: $offset | Limit: $limit<br><hr>";

// 1. Ambil semua kelas di semester ini dengan LIMIT
$sql = "SELECT DISTINCT mk.kode_mk, kk.nm_kls 
        FROM wsia_kelas_kuliah kk
        JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
        WHERE kk.id_smt = '$semesterId'
        ORDER BY mk.kode_mk
        LIMIT $limit OFFSET $offset";

$stmt = $db->query($sql);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($classes)) {
    echo "<h3>SELESAI! Tidak ada data lagi.</h3>";
    exit;
}

echo "Memproses " . count($classes) . " data...<br>";
// Matikan output buffering
if (ob_get_level() > 0)
    ob_end_flush();
flush();

$foundCount = 0;
$deletedCount = 0;

foreach ($classes as $c) {
    $kode = trim($c['kode_mk']);
    $kelas = preg_replace('/[^a-z0-9]/i', '', $c['nm_kls']);

    // Pola Salah: TRPLIP5301-23C
    $badKey = $kode . '-' . $kelas;

    // Cek Moodle
    $resp = $moodle->getCourseByIdNumber($badKey);

    if (isset($resp['success']) && $resp['success'] && !empty($resp['data']['courses'])) {
        $mCourse = $resp['data']['courses'][0];
        $id = $mCourse['id'];
        $fullname = $mCourse['fullname'];

        echo "<span style='color:red'>[FOUND]</span> Key: $badKey | ID: $id | Name: $fullname ... ";
        flush();
        $foundCount++; // Added this back as it was in the original and makes sense for the final count.

        // HAPUS
        $del = $moodle->deleteCourse($id);
        if (isset($del['success']) && $del['success']) {
            echo "<b style='color:green'>DELETED OK</b><br>";
            $deletedCount++;

            // Hapus mapping jika ada (membersihkan sampah)
            $db->query("DELETE FROM moodle_sync_mapping WHERE siakad_id = '$badKey'");
        } else {
            echo "<b style='color:orange'>FAILED</b>: " . json_encode($del) . "<br>";
        }
    } else {
        // echo "[MISS] $badKey <br>"; 
    }

    if ($foundCount > 0)
        flush(); // Flush jika ada temuan
}

$nextOffset = $offset + $limit;
echo "<hr>";
echo "Ditemukan di Batch ini: $foundCount <br>";
echo "Dihapus di Batch ini: $deletedCount <br>";
echo "<h3>Lanjut ke Batch berikutnya... ($nextOffset)</h3>";

// Auto Redirect
echo "<script>
    setTimeout(function(){
        window.location.href = '?offset=$nextOffset';
    }, 2000);
</script>";
?>