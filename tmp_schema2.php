<?php
require 'config/config.php';
$db = koneksi();
$stmt = $db->query('DESCRIBE wsia_mahasiswa_pt');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
