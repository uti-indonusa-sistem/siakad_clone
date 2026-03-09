<?php
session_start();

// --- SECURITY HEADERS ---
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");

// --- SESSION SECURITY ---
// 1. Check Login Flag
if (!isset($_SESSION['monitoring_user']) || $_SESSION['monitoring_user'] !== true) {
    header("Location: login.php");
    exit();
}

// 2. Check Timeout (30 Menit)
$timeout_duration = 1800;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
// Reset timer on activity
$_SESSION['login_time'] = time();

// 3. User Agent Validation (Prevent Session Hijacking)
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy(); // Destroy suspicious session
    header("Location: login.php?security=1");
    exit();
}
?>
