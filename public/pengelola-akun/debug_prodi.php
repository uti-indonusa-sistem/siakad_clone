<?php
// Debug Script for Prodi Synchronization Issues
// Save as: public/pengelola-akun/debug_prodi.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../ws/MoodleWebService.php';

header('Content-Type: text/plain');

$db = koneksi();
$moodle = new MoodleWebService();

echo "=== DEBUG PRODI MANAJEMEN INFORMASI KESEHATAN ===\n\n";

// 1. Search for Prodi
echo "1. Searching for Prodi in wsia_sms...\n";
$stmt = $db->query("SELECT * FROM wsia_sms WHERE nm_lemb LIKE '%Manajemen Informasi Kesehatan%'");
$prodis = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($prodis)) {
    echo "[ERROR] Prodi not found in wsia_sms!\n";
} else {
    foreach ($prodis as $prodi) {
        echo "Found: ID={$prodi['xid_sms']}, Kode={$prodi['kode_prodi']}, Nama={$prodi['nm_lemb']}, ID_SP={$prodi['id_sp']}\n";

        // Check if ID_SP matches config NPSN
        $stmtSP = $db->prepare("SELECT npsn FROM wsia_satuan_pendidikan WHERE id_sp = ?");
        $stmtSP->execute([$prodi['id_sp']]);
        $sp = $stmtSP->fetch(PDO::FETCH_ASSOC);
        echo "  > Linked to NPSN: " . ($sp ? $sp['npsn'] : 'NOT FOUND') . "\n";

        // Check Mapping
        $stmtMap = $db->prepare("SELECT * FROM moodle_sync_mapping WHERE type='category' AND siakad_id = ?");
        $stmtMap->execute([$prodi['xid_sms']]);
        $mapping = $stmtMap->fetch(PDO::FETCH_ASSOC);
        echo "  > Sync Mapping: " . ($mapping ? "Mapped to Moodle ID {$mapping['moodle_id']}" : "NOT MAPPED") . "\n";

        // Moodle Check
        echo "  > Checking Moodle by ID Number '{$prodi['kode_prodi']}'...\n";
        $mCheck = $moodle->getCategoryByIdNumber($prodi['kode_prodi']);
        print_r($mCheck);

        // 2. Check Courses for this Prodi in Active Semester
        echo "\n2. Checking Courses for this Prodi (Current Semester)...\n";
        // Get active semester
        $stmtSem = $db->query("SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1");
        $id_smt = $stmtSem->fetchColumn() ?: (date('Y') . '1');
        echo "  > Active Semester: $id_smt\n";

        $sqlCourses = "SELECT COUNT(*) as cnt FROM wsia_kelas_kuliah WHERE id_sms = ? AND id_smt = ?";
        $stmtC = $db->prepare($sqlCourses);
        $stmtC->execute([$prodi['xid_sms'], $id_smt]);
        $cnt = $stmtC->fetchColumn();
        echo "  > Total Classes/Courses Found in DB: $cnt\n";

        if ($cnt > 0) {
            echo "  > Sample Course Code:\n";
            $sqlSample = "SELECT mk.kode_mk, mk.nm_mk FROM wsia_kelas_kuliah kk 
                          JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk 
                          WHERE kk.id_sms = ? AND kk.id_smt = ? LIMIT 1";
            $stmtS = $db->prepare($sqlSample);
            $stmtS->execute([$prodi['xid_sms'], $id_smt]);
            $sample = $stmtS->fetch(PDO::FETCH_ASSOC);
            print_r($sample);
        }
    }
}
?>