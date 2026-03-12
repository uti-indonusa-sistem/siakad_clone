<?php
/**
 * Security Helper Class
 * Provides security functions for the SIAKAD system
 * 
 * @author Security Team
 * @version 1.0.0
 * @date 2025-10-14
 */

class Security {
    
    /**
     * Sanitize string input
     * Remove HTML tags and encode special characters
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeString($input) {
        if ($input === null || $input === '') return '';
        $input = strip_tags($input);
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean input for database (backward compatibility)
     * Use prepared statements instead when possible
     * 
     * @param string $str
     * @return string
     */
    public static function clean($str) {
        $str = @trim($str);
        return self::sanitizeString($str);
    }
    
    /**
     * Validate email address
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indonesian format)
     * 
     * @param string $phone
     * @return bool
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[\s\-]/', '', $phone);
        return preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $phone) === 1;
    }
    
    /**
     * Validate date format (Y-m-d)
     * 
     * @param string $date
     * @return bool
     */
    public static function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Validate NIM/NIPD format
     * Supports both numeric (123456) and alphanumeric (B23064) formats
     * 
     * @param string $nim
     * @return bool
     */
    public static function validateNIM($nim) {
        // Accept alphanumeric NIM (letters and numbers), length 5-15 characters
        return preg_match('/^[A-Za-z0-9]{5,15}$/', $nim) === 1;
    }
    
    /**
     * Validate NIDN format
     * NIDN can be 7-12 digits (some institutions use 7-digit NIDN)
     * 
     * @param string $nidn
     * @return bool
     */
    public static function validateNIDN($nidn) {
        return preg_match('/^[0-9]{7,12}$/', $nidn) === 1;
    }
    
    /**
     * Validate alphanumeric only
     * 
     * @param string $input
     * @return bool
     */
    public static function validateAlphaNumeric($input) {
        return preg_match('/^[a-zA-Z0-9]+$/', $input) === 1;
    }
    
    /**
     * Validate alphanumeric with underscore
     * 
     * @param string $input
     * @return bool
     */
    public static function validateAlphaNumericUnderscore($input) {
        return preg_match('/^[a-zA-Z0-9_]+$/', $input) === 1;
    }
    
    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        // Regenerate token setiap 1 jam
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate secure random session ID
     * 
     * @return string
     */
    public static function generateSessionID() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate session with additional security checks
     * 
     * @param string $sessionKey
     * @param int $timeout Session timeout in seconds (default: 30 minutes)
     * @return bool
     */
    public static function validateSession($sessionKey, $timeout = 1800) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION[$sessionKey])) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $timeout) {
                self::destroySession($sessionKey);
                return false;
            }
            // Refresh timeout
            $_SESSION['login_time'] = time();
        }
        
        // Check IP address (optional - can cause issues with mobile networks)
        // Uncomment if needed
        /*
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                error_log("Session IP mismatch for {$sessionKey}: " . $_SESSION['ip_address'] . " vs " . $_SERVER['REMOTE_ADDR']);
                self::destroySession($sessionKey);
                return false;
            }
        }
        */
        
        return true;
    }
    
    /**
     * Destroy session and cleanup
     * 
     * @param string $sessionKey
     */
    public static function destroySession($sessionKey) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION[$sessionKey])) {
            $sessionID = $_SESSION[$sessionKey];
            unset($_SESSION[$sessionKey]);
            unset($_SESSION[$sessionID]);
        }
        
        unset($_SESSION['login_time']);
        unset($_SESSION['ip_address']);
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }
    
    /**
     * Rate limiting check
     * 
     * @param string $identifier Unique identifier (e.g., 'login', 'api_call')
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed, False if rate limit exceeded
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 900) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['rate_limit'])) {
            $_SESSION['rate_limit'] = array();
        }
        
        $now = time();
        $key = md5($identifier . $_SERVER['REMOTE_ADDR']);
        
        if (isset($_SESSION['rate_limit'][$key])) {
            $attempts = $_SESSION['rate_limit'][$key];
            
            // Remove old attempts outside time window
            $attempts = array_filter($attempts, function($timestamp) use ($now, $timeWindow) {
                return ($now - $timestamp) < $timeWindow;
            });
            
            if (count($attempts) >= $maxAttempts) {
                error_log("Rate limit exceeded for: {$identifier} from IP: " . $_SERVER['REMOTE_ADDR']);
                return false;
            }
            
            $attempts[] = $now;
            $_SESSION['rate_limit'][$key] = $attempts;
        } else {
            $_SESSION['rate_limit'][$key] = array($now);
        }
        
        return true;
    }
    
    /**
     * Reset rate limit for an identifier
     * 
     * @param string $identifier
     */
    public static function resetRateLimit($identifier) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = md5($identifier . $_SERVER['REMOTE_ADDR']);
        if (isset($_SESSION['rate_limit'][$key])) {
            unset($_SESSION['rate_limit'][$key]);
        }
    }
    
    /**
     * Hash password using secure algorithm
     * 
     * COMPATIBILITY NOTE: Changed from Argon2id to Bcrypt (PASSWORD_DEFAULT)
     * to support UPM system running PHP 5.6 which doesn't support Argon2id.
     * Bcrypt is still very secure and widely compatible (PHP 5.5+).
     * 
     * @param string $password
     * @return string
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password against hash
     * 
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Legacy password hash (for migration compatibility)
     * DO NOT USE for new passwords
     * 
     * @param string $password
     * @param string $salt
     * @return string
     */
    public static function legacyPasswordHash($password, $salt) {
        $pass = crypt(md5($password), "$1$".$salt);
        $apass = explode("$", $pass);
        $vpass = sha1($apass[3]);
        return md5($vpass);
    }
    
    /**
     * Legacy password verification (for migration)
     * 
     * @param string $password
     * @param string $salt
     * @param string $storedHash
     * @return bool
     */
    public static function verifyLegacyPassword($password, $salt, $storedHash) {
        $computedHash = self::legacyPasswordHash($password, $salt);
        return hash_equals($storedHash, $computedHash);
    }
    
    /**
     * Sanitize filename
     * 
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename($filename) {
        // Remove directory traversal
        $filename = basename($filename);
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 2097152) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['valid' => false, 'error' => 'Invalid file upload'];
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['valid' => false, 'error' => 'File terlalu besar'];
            case UPLOAD_ERR_NO_FILE:
                return ['valid' => false, 'error' => 'Tidak ada file yang diupload'];
            default:
                return ['valid' => false, 'error' => 'Upload error'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File terlalu besar (max ' . ($maxSize / 1024 / 1024) . 'MB)'];
        }
        
        if (!empty($allowedTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return ['valid' => false, 'error' => 'Tipe file tidak diperbolehkan'];
            }
        }
        
        return ['valid' => true, 'error' => ''];
    }
    
    /**
     * Log security event
     * 
     * @param string $event Event description
     * @param string $level Log level (INFO, WARNING, ERROR)
     */
    public static function logSecurityEvent($event, $level = 'INFO') {
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [User: %s] %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SESSION['username'] ?? 'guest',
            $event
        );
        
        error_log($logEntry);
        
        // Optionally write to separate security log file
        $logFile = __DIR__ . '/../logs/security.log';
        if (is_writable(dirname($logFile))) {
            file_put_contents($logFile, $logEntry, FILE_APPEND);
        }
    }
    
    /**
     * Force HTTPS redirection
     */
    public static function forceHTTPS() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
                $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                header('Location: ' . $redirect, true, 301);
                exit;
            }
        }
    }
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com https://static.cloudflareinsights.com; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; img-src 'self' data:; font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com;");
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    
    /**
     * Configure secure session
     */
    public static function configureSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Strict');
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.gc_maxlifetime', 1800); // 30 minutes
        }
    }
}