<?php
/**
 * Mahasiswa Login Authentication Check
 * Security Update: 2025-10-14
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/../lib/security.php';

$host = "https://".$_SERVER['HTTP_HOST']."";

if (!isset($_SESSION['wsiaMHS']) || trim($_SESSION['wsiaMHS']) == '') {
	Security::logSecurityEvent("Unauthorized access attempt to Mahasiswa (no session)", 'WARNING');
	exit("<script>window.location='".$host."/mhs/login';</script>");
}

if (!Security::validateSession('wsiaMHS', 1800)) {
	Security::logSecurityEvent("Session expired or invalid for Mahasiswa", 'INFO');
	Security::destroySession('wsiaMHS');
	exit("<script>window.location='".$host."/mhs/login';</script>");
}

$sessionID = $_SESSION['wsiaMHS'];
$expectedSignature = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');

if (!isset($_SESSION[$sessionID]) || $_SESSION[$sessionID] !== $expectedSignature) {
	Security::logSecurityEvent("Invalid session signature for Mahasiswa", 'WARNING');
	Security::destroySession('wsiaMHS');
	exit("<script>window.location='".$host."/mhs/login';</script>");
}
