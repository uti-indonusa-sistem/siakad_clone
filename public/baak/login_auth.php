<?php
/**
 * BAAK Login Authentication Check
 * Security Update: 2025-10-14
 * 
 * SECURITY IMPROVEMENTS:
 * - Proper session validation
 * - Session timeout check
 * - Security logging
 * - Clean redirect
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Load security helper
require_once __DIR__ . '/../lib/security.php';

$host = "https://".$_SERVER['HTTP_HOST']."";

// Check if session exists
if (!isset($_SESSION['wsiaADMIN']) || trim($_SESSION['wsiaADMIN']) == '') {
	Security::logSecurityEvent("Unauthorized access attempt to BAAK (no session)", 'WARNING');
	exit("<script>window.location='".$host."/baak/login';</script>");
}

// Validate session with timeout (30 minutes)
if (!Security::validateSession('wsiaADMIN', 1800)) {
	Security::logSecurityEvent("Session expired or invalid for BAAK", 'INFO');
	Security::destroySession('wsiaADMIN');
	exit("<script>window.location='".$host."/baak/login';</script>");
}

// Additional validation: Check session signature
$sessionID = $_SESSION['wsiaADMIN'];
$expectedSignature = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET'] ?? 'default_secret');

if (!isset($_SESSION[$sessionID]) || $_SESSION[$sessionID] !== $expectedSignature) {
	Security::logSecurityEvent("Invalid session signature for BAAK", 'WARNING');
	Security::destroySession('wsiaADMIN');
	exit("<script>window.location='".$host."/baak/login';</script>");
}

// Session is valid, continue
?>
