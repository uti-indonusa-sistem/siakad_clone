<?php
/**
 * Dosen Login Authentication Check
 * Security Update: 2025-10-14
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/../lib/security.php';

$host = "https://".$_SERVER['HTTP_HOST']."";

if (!isset($_SESSION['wsiaDOSEN']) || trim($_SESSION['wsiaDOSEN']) == '') {
	Security::logSecurityEvent("Unauthorized access attempt to Dosen (no session)", 'WARNING');
	exit("<script>window.location='".$host."/dosen/login';</script>");
}

if (!Security::validateSession('wsiaDOSEN', 1800)) {
	Security::logSecurityEvent("Session expired or invalid for Dosen", 'INFO');
	Security::destroySession('wsiaDOSEN');
	exit("<script>window.location='".$host."/dosen/login';</script>");
}

$sessionID = $_SESSION['wsiaDOSEN'];
$expectedSignature = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');

if (!isset($_SESSION[$sessionID]) || $_SESSION[$sessionID] !== $expectedSignature) {
	Security::logSecurityEvent("Invalid session signature for Dosen", 'WARNING');
	Security::destroySession('wsiaDOSEN');
	exit("<script>window.location='".$host."/dosen/login';</script>");
}
?>
