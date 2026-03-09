<?php
// File: index.php
// Fungsi: Redirect otomatis ke halaman login
// Ini penting agar saat user akses /monitoring/ langsung diarahkan
header("Location: login.php");
exit();
?>
