<?php
require_once 'config/config.php';
$db = koneksi();
$stmt = $db->query("DESCRIBE siakad_pa_aktifitas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\n====\n";
$stmt2 = $db->query("DESCRIBE siakad_pa_jurnal");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
