<?php
/**
 * SecurityMiddleware.php
 * 
 * Middleware keamanan untuk memblokir IP yang mencoba mengakses URL berbahaya
 * Berdasarkan pola fail2ban untuk OJS dengan Progressive Ban System (Recidive)
 * 
 * Features:
 * - Kategori A: Brute Force (5x dalam 5 menit → Ban 1 jam)
 * - Kategori B: Fatal/Malware (1x hit → Ban 24 jam)
 * - Kategori C: Jail Bertingkat (1hr → 24hr → Permanen)
 * 
 * @author Antigravity
 * @version 2.0.0
 * @date 2026-01-13
 */

class SecurityMiddleware {
    
    // ========================================
    // CONSTANTS - Ban Duration
    // ========================================
    const BAN_SOFT = 3600;           // 1 jam
    const BAN_MEDIUM = 86400;        // 24 jam
    const BAN_HARD = 604800;         // 1 minggu
    const BAN_PERMANENT = -1;        // Permanen (blacklist)
    
    // ========================================
    // CONSTANTS - Attack Categories
    // ========================================
    const CATEGORY_BRUTE_FORCE = 'A';  // Brute Force (Login)
    const CATEGORY_FATAL = 'B';         // Malware/Exploit (Fatal)
    const CATEGORY_GENERAL = 'C';       // General Attack
    
    // ========================================
    // CONSTANTS - Thresholds
    // ========================================
    const BRUTE_FORCE_MAX_ATTEMPTS = 5;     // Max 5x gagal
    const BRUTE_FORCE_WINDOW = 300;          // Dalam 5 menit
    const GENERAL_MAX_ATTEMPTS = 3;          // Max 3x untuk general
    const RECIDIVE_PERMANENT_THRESHOLD = 3;  // 3x offense = blacklist
    
    // ========================================
    // CONSTANTS - Subnet Banning
    // ========================================
    const SUBNET_ATTACK_THRESHOLD = 3;     // 3 different IPs from same /24 subnet
    const SUBNET_ATTACK_WINDOW = 3600;      // Within 1 hour
    const SUBNET_BAN_DURATION = 86400;      // 24 hour initial ban
    
    // Konfigurasi
    private $enabled = true;
    private $logEnabled = true;
    private $banDuration = 3600; // Default 1 jam (untuk backward compat)
    private $maxAttempts = 3;
    private $blockListFile;
    private $logFile;
    private $jailHistoryFile;
    private $blacklistFile;
    private $bruteForceFile;
    private $subnetBanFile;
    private $subnetTrackFile;
    
    // ========================================
    // PATTERNS - Kategori A: Brute Force
    // ========================================
    private $bruteForcePatterns = [
        '/\\/wp-login\\.php/i',
        '/\\/xmlrpc\\.php/i',
        '/\\/administrator\\/index\\.php/i',
        '/\\/admin\\/login/i',
        '/\\/user\\/login/i',
        '/\\/login\\.php/i',
    ];
    
    // ========================================
    // PATTERNS - Kategori B: Fatal/Malware (Instant Ban)
    // ========================================
    private $fatalPatterns = [
        // Environment & Config files
        '/\\.env$/i',
        '/\\.env\\./i',
        '/config\\.inc\\.php$/i',
        '/wp-config\\.php/i',
        
        // Web shells - INSTANT BAN
        '/(wso|alfa|shell|mini|x|c99|r57|b374k|fx29sh)\\.php/i',
        '/(wso|alfa|shell|c99|r57|b374k)\\d*\\.php/i',
        '/shell[^a-z]/i',
        
        // PHPUnit vulnerability
        '/\\/vendor\\/phpunit/i',
        '/\\/vendor\\/.*\\/phpunit/i',
        
        // Known backdoors
        '/\\/(adminer|pma|phpmyadmin|myadmin)\\.php/i',
        '/\\/eval-stdin\\.php/i',
        '/\\/php-backdoor/i',
        
        // Sensitive paths
        '/\\/etc\\/passwd/i',
        '/\\/proc\\/self/i',
        '/\\/\\.git\\/config/i',
        '/\\/\\.svn\\/entries/i',
        
        // ========================================
        // LFI/RFI - Local/Remote File Inclusion
        // ========================================
        '/\\/etc\\/(passwd|shadow|hosts|group|sudoers)/i',
        '/\\/proc\\/(version|cmdline|meminfo|cpuinfo)/i',
        '/\\/var\\/(log|www|mail|spool)/i',
        '/(c:|d:)[\\/\\\\].*(boot\\.ini|win\\.ini|system32)/i',
        
        // RFI - Remote File Inclusion
        '/(https?|ftp|file|expect|glob|phar|ogg|rar|ssh2|zlib):\\/\\//i',
        '/\\=\\s*(https?|ftp):\\/\\//i',
        
        // ========================================
        // Command Injection - CRITICAL
        // ========================================
        '/[;&|`\\$]\\s*(cat|ls|dir|pwd|cd|rm|mv|cp|wget|curl|nc|ncat|bash|sh|zsh|cmd|powershell|whoami|id|uname)/i',
        '/\\$\\([^\\)]+\\)/i',              // Command substitution $()
        '/`[^`]+`/i',                       // Backtick execution
        '/\\|\\s*(bash|sh|cmd|powershell)/i', // Pipe to shell
        '/\\>\\s*\\/[a-z]/i',               // Redirect to system paths
        '/\\&\\&|\\|\\|/',                  // Command chaining
        
        // ========================================
        // XXE - XML External Entity Injection
        // ========================================
        '/\\<!DOCTYPE[^>]+SYSTEM/i',
        '/\\<!ENTITY/i',
        '/\\<!\\[CDATA\\[/i',
        '/xmlns:xi=/i',                    // XInclude
        
        // ========================================
        // SSTI - Server-Side Template Injection
        // ========================================
        '/\\{\\{.*\\}\\}/i',               // Jinja2/Twig syntax
        '/\\{%.*%\\}/i',                   // Jinja2/Twig blocks
        '/\\$\\{.*\\}/i',                  // Expression language
        '/<%.* %>/i',                       // JSP/ASP syntax
        '/\\#\\{.*\\}/i',                  // RCE in some templates
        
        // ========================================
        // PHP Serialization Attack
        // ========================================
        '/(O:|a:|s:|i:|b:|d:)\\d+:/i',     // PHP serialize patterns
        
        // ========================================
        // Log Poisoning
        // ========================================
        '/\\/var\\/log\\/(apache|nginx|httpd)/i',
        '/\\bpasswd\\b.*\\%00/i',          // Null byte injection
    ];
    
    // ========================================
    // PATTERNS - Kategori C: General (Progressive)
    // ========================================
    private $generalPatterns = [
        // WordPress related (non-login)
        '/\\/wp-(admin|includes|content)/i',
        
        // Sensitive files
        '/\\/phpinfo\\.php/i',
        
        // Database dumps
        '/\\/(db|sql|backup|dump|database)\\.(sql|gz|zip|tar|bak)/i',
        
        // Command injection attempts (basic)
        '/[\\?\\&](cmd|command|eval|exec|system|passthru)=/i',
        
        // Path traversal
        '/\\.\\.[\\/\\\\]/i',
        
        // PHP wrappers
        '/php:\\/\\/(filter|input|data)/i',
        
        // Additional exploits
        '/\\/\\.htaccess$/i',
        '/\\/\\.htpasswd$/i',
        '/\\/composer\\.(json|lock)/i',
        '/\\/vendor\\/.*\\.php$/i',
        
        // ========================================
        // SQL INJECTION - Comprehensive Detection
        // ========================================
        
        // Basic SQL injection
        '/\\b(union\\s+select|select\\s+.*\\s+from|insert\\s+into|drop\\s+table|delete\\s+from)\\b/i',
        
        // Time-based blind SQL injection
        '/\\bsleep\\s*\\(/i',
        '/\\bbenchmark\\s*\\(/i',
        '/\\bwaitfor\\s+delay/i',
        '/\\bpg_sleep\\s*\\(/i',
        '/\\bdbms_pipe\\.receive_message/i',
        
        // Error-based SQL injection
        '/\\band\\s+1\\s*=\\s*1/i',
        '/\\bor\\s+1\\s*=\\s*1/i',
        '/\\b(and|or)\\s+[\\d]+\\s*=\\s*[\\d]+/i',
        '/[\'\\"](\\s*)(--|#|;)/i',
        '/\\bextractvalue\\s*\\(/i',
        '/\\bupdatexml\\s*\\(/i',
        
        // UNION-based injection
        '/union\\s+(all\\s+)?select/i',
        '/order\\s+by\\s+\\d+/i',
        '/group\\s+by\\s+\\d+/i',
        '/having\\s+\\d+\\s*=\\s*\\d+/i',
        
        // Boolean-based blind injection
        '/\\band\\s*\\([^\\)]+\\)/i',
        '/\\bor\\s*\\([^\\)]+\\)/i',
        '/\\bif\\s*\\([^\\)]+,[^\\)]+,[^\\)]+\\)/i',
        '/\\bcase\\s+when/i',
        
        // Stacked queries
        '/;\\s*(select|insert|update|delete|drop|create|alter|exec|execute)/i',
        
        // Out-of-band injection
        '/\\bload_file\\s*\\(/i',
        '/\\binto\\s+(out|dump)file/i',
        '/\\butl_http/i',
        '/\\bxp_cmdshell/i',
        '/\\bxp_regread/i',
        
        // SQL functions abuse
        '/\\bchar\\s*\\(\\s*\\d/i',
        '/\\bconcat\\s*\\(/i',
        '/\\bconcat_ws\\s*\\(/i',
        '/\\bhex\\s*\\(/i',
        '/\\bunhex\\s*\\(/i',
        '/\\bord\\s*\\(/i',
        '/\\bascii\\s*\\(/i',
        '/\\bsubstring\\s*\\(/i',
        '/\\bsubstr\\s*\\(/i',
        '/\\bleft\\s*\\(/i',
        '/\\bright\\s*\\(/i',
        
        // ========================================
        // XSS - Cross-Site Scripting
        // ========================================
        
        // Script tags
        '/<script[^>]*>/i',
        '/<\\/script>/i',
        
        // DOM-based XSS
        '/document\\.(location|cookie|domain|write|writeln)/i',
        '/window\\.(location|name|eval)/i',
        '/\\beval\\s*\\(/i',
        '/\\bsetTimeout\\s*\\([^\\)]*[\'\\"]/i',
        '/\\bsetInterval\\s*\\([^\\)]*[\'\\"]/i',
        '/\\bFunction\\s*\\(/i',
        
        // Event handlers (comprehensive)
        '/\\bon(abort|blur|change|click|dblclick|drag|dragend|dragenter|dragleave|dragover|dragstart|drop|error|focus|focusin|focusout|input|invalid|keydown|keypress|keyup|load|mousedown|mouseenter|mouseleave|mousemove|mouseout|mouseover|mouseup|pointerdown|pointerenter|pointerleave|pointermove|pointerout|pointerover|pointerup|reset|resize|scroll|select|submit|touchcancel|touchend|touchmove|touchstart|transitionend|unload|wheel)\\s*=/i',
        
        // HTML injection (dangerous tags)
        '/<(img|svg|object|embed|iframe|frame|frameset|layer|bgsound|base|link|meta|style|applet|body|input|form|button|textarea|video|audio|source|marquee)/i',
        
        // JavaScript URI
        '/(javascript|vbscript|expression|mocha|livescript):/i',
        
        // Data URI with script
        '/data:[^;]+;base64,/i',
        
        // SVG-based XSS
        '/<svg[^>]*onload/i',
        '/<svg[^>]*onerror/i',
        
        // ========================================
        // LDAP INJECTION
        // ========================================
        '/[\\(\\)\\*\\\\]+(\\||\\&|=)/i',
        '/\\(\\|(cn|uid|mail|sn|givenName)=/i',
        '/\\)\\)\\(/i',
        '/\\*\\)\\(/i',
        
        // ========================================
        // SSRF - Server-Side Request Forgery
        // ========================================
        '/url=.*localhost/i',
        '/url=.*127\\.0\\.0\\.1/i',
        '/url=.*0\\.0\\.0\\.0/i',
        '/url=.*\\[::1\\]/i',
        '/url=.*169\\.254\\./i',           // Link-local
        '/url=.*10\\./i',                   // Private network
        '/url=.*192\\.168\\./i',            // Private network
        '/url=.*172\\.(1[6-9]|2[0-9]|3[01])\\./i',  // Private network
        '/(file|gopher|dict|ldap|tftp):\\/\\//i',
        
        // ========================================
        // HTTP Response Splitting
        // ========================================
        '/%0d%0a/i',
        '/%0d/i',
        '/%0a/i',
        '/\\r\\n/i',
        
        // ========================================
        // Open Redirect
        // ========================================
        '/(redirect|url|next|return|redir|destination)=.*(\\/\\/|%2f%2f)/i',
    ];
    
    // Ekstensi file yang diabaikan (static assets)
    private $ignoredExtensions = [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'pdf', 'webp', 'mp4', 'webm'
    ];
    
    // Whitelist IP (tidak akan diblokir)
    private $whitelistedIPs = [
        '127.0.0.1',
        '::1',
    ];
    
    /**
     * Constructor
     */
    public function __construct($options = []) {
        $baseDir = dirname(__FILE__);
        $this->blockListFile = $options['blockListFile'] ?? $baseDir . '/blocked_ips.json';
        $this->logFile = $options['logFile'] ?? $baseDir . '/security.log';
        $this->jailHistoryFile = $options['jailHistoryFile'] ?? $baseDir . '/jail_history.json';
        $this->blacklistFile = $options['blacklistFile'] ?? $baseDir . '/blacklist.json';
        $this->bruteForceFile = $options['bruteForceFile'] ?? $baseDir . '/brute_force.json';
        $this->subnetBanFile = $options['subnetBanFile'] ?? $baseDir . '/subnet_bans.json';
        $this->subnetTrackFile = $options['subnetTrackFile'] ?? $baseDir . '/subnet_tracking.json';
        
        if (isset($options['enabled'])) $this->enabled = $options['enabled'];
        if (isset($options['logEnabled'])) $this->logEnabled = $options['logEnabled'];
        if (isset($options['banDuration'])) $this->banDuration = $options['banDuration'];
        if (isset($options['maxAttempts'])) $this->maxAttempts = $options['maxAttempts'];
        if (isset($options['whitelistedIPs'])) {
            $this->whitelistedIPs = array_merge($this->whitelistedIPs, $options['whitelistedIPs']);
        }
    }
    
    /**
     * Main handler - panggil di awal request
     */
    public function handle() {
        if (!$this->enabled) {
            return true;
        }
        
        $ip = $this->getClientIP();
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Cek whitelist
        if ($this->isWhitelisted($ip)) {
            return true;
        }
        
        // Cek blacklist permanen FIRST
        if ($this->isBlacklisted($ip)) {
            $this->log("BLACKLIST HIT: IP $ip attempted access (permanently blacklisted)");
            $this->blockResponse($ip, 'IP dalam blacklist permanen');
            return false;
        }
        
        // Cek apakah IP currently banned
        if ($this->isBlocked($ip)) {
            $this->blockResponse($ip, 'IP dalam daftar blokir');
            return false;
        }
        
        // Cek apakah subnet IP di-ban
        $subnet = $this->getSubnet($ip);
        if ($this->isSubnetBanned($subnet)) {
            $this->log("SUBNET BAN HIT: IP $ip (subnet $subnet) attempted access");
            $this->blockResponse($ip, 'Subnet dalam daftar blokir');
            return false;
        }
        
        // Cek ekstensi file yang diabaikan
        if ($this->isIgnoredExtension($uri)) {
            return true;
        }
        
        // ========================================
        // KATEGORI B: Fatal Patterns - INSTANT BAN
        // ========================================
        $fatalMatch = $this->matchesPatternCategory($uri, $this->fatalPatterns);
        if ($fatalMatch) {
            $this->handleFatalAttack($ip, $uri, $method, $fatalMatch);
            return false;
        }
        
        // ========================================
        // KATEGORI A: Brute Force Patterns
        // ========================================
        $bruteForceMatch = $this->matchesPatternCategory($uri, $this->bruteForcePatterns);
        if ($bruteForceMatch) {
            return $this->handleBruteForceAttempt($ip, $uri, $method, $bruteForceMatch);
        }
        
        // ========================================
        // KATEGORI C: General Patterns - Progressive
        // ========================================
        $generalMatch = $this->matchesPatternCategory($uri, $this->generalPatterns);
        if ($generalMatch) {
            $this->handleGeneralAttack($ip, $uri, $method, $generalMatch);
            return false;
        }
        
        return true;
    }
    
    // ========================================
    // KATEGORI B: FATAL ATTACK HANDLER
    // ========================================
    private function handleFatalAttack($ip, $uri, $method, $pattern) {
        $this->log("FATAL ATTACK (CAT-B): IP $ip - $method $uri - Pattern: $pattern");
        
        // Track subnet attack untuk automatic subnet ban
        $this->trackSubnetAttack($ip, $pattern);
        
        // Instant ban 24 jam (atau lebih untuk repeat offender)
        $jailHistory = $this->getJailHistory($ip);
        $offenseCount = $jailHistory['offense_count'] ?? 0;
        
        $banDuration = self::BAN_MEDIUM; // 24 jam default
        
        // Jika sudah pernah melanggar, langsung blacklist
        if ($offenseCount >= 1) {
            $this->addToBlacklist($ip, "Repeat fatal attack: $pattern", $offenseCount + 1);
            $this->blockResponse($ip, 'Ditambahkan ke blacklist permanen');
            return;
        }
        
        // Ban 24 jam untuk first offense
        $this->blockIPWithCategory($ip, $pattern, self::CATEGORY_FATAL, $banDuration);
        $this->updateJailHistory($ip, self::CATEGORY_FATAL, $banDuration);
        $this->blockResponse($ip, "Fatal attack detected - Ban 24 jam");
    }
    
    // ========================================
    // KATEGORI A: BRUTE FORCE HANDLER
    // ========================================
    private function handleBruteForceAttempt($ip, $uri, $method, $pattern) {
        // Record attempt dengan window 5 menit
        $this->recordBruteForceAttempt($ip, $uri, $method, $pattern);
        
        $attempts = $this->getBruteForceAttemptCount($ip);
        
        if ($attempts >= $this->maxAttempts) {
            $this->log("BRUTE FORCE DETECTED (CAT-A): IP $ip - $attempts attempts in 5 min");
            
            // Cek recidive untuk durasi ban
            $banDuration = $this->getRecidiveBanDuration($ip, self::CATEGORY_BRUTE_FORCE);
            
            $this->blockIPWithCategory($ip, $pattern, self::CATEGORY_BRUTE_FORCE, $banDuration);
            $this->updateJailHistory($ip, self::CATEGORY_BRUTE_FORCE, $banDuration);
            $this->clearBruteForceAttempts($ip);
            
            // Track subnet attack
            $this->trackSubnetAttack($ip, $pattern);
            
            $this->blockResponse($ip, "Brute force detected - Ban " . ($banDuration / 3600) . " jam");
            return false;
        }
        
        // Warning - masih dalam batas
        $this->log("BRUTE FORCE ATTEMPT (CAT-A): IP $ip - Attempt $attempts/" . $this->maxAttempts);
        
        // Return true untuk login pages - biarkan aplikasi handle authentication
        return true;
    }
    
    // ========================================
    // KATEGORI C: GENERAL ATTACK HANDLER
    // ========================================
    private function handleGeneralAttack($ip, $uri, $method, $pattern) {
        $this->recordAttempt($ip, $uri, $method, $pattern);
        
        $attempts = $this->getAttemptCount($ip);
        
        if ($attempts >= self::GENERAL_MAX_ATTEMPTS) {
            $banDuration = $this->getRecidiveBanDuration($ip, self::CATEGORY_GENERAL);
            
            $this->log("GENERAL ATTACK (CAT-C): IP $ip - $attempts attempts - Ban " . ($banDuration / 3600) . " jam");
            
            $this->blockIPWithCategory($ip, $pattern, self::CATEGORY_GENERAL, $banDuration);
            $this->updateJailHistory($ip, self::CATEGORY_GENERAL, $banDuration);
            
            // Track subnet attack
            $this->trackSubnetAttack($ip, $pattern);
            
            $this->blockResponse($ip, "Attack detected - Ban " . ($banDuration / 3600) . " jam");
            return;
        }
        
        $this->log("ATTEMPT (CAT-C): IP $ip - $method $uri - Pattern: $pattern - Attempt $attempts/" . self::GENERAL_MAX_ATTEMPTS);
        $this->warningResponse($ip, $attempts);
    }
    
    // ========================================
    // RECIDIVE SYSTEM
    // ========================================
    
    /**
     * Calculate ban duration based on offense history
     */
    private function getRecidiveBanDuration($ip, $category) {
        $jailHistory = $this->getJailHistory($ip);
        $offenseCount = $jailHistory['offense_count'] ?? 0;
        
        // Progressive punishment
        switch ($offenseCount) {
            case 0:
                // First offense
                return self::BAN_SOFT; // 1 jam
            case 1:
                // Second offense
                return self::BAN_MEDIUM; // 24 jam
            case 2:
            default:
                // Third+ offense - akan dihandle di checkAndBlacklist
                return self::BAN_HARD; // 1 minggu (sebelum blacklist)
        }
    }
    
    /**
     * Get jail history for an IP
     */
    private function getJailHistory($ip) {
        $history = $this->loadJailHistory();
        return $history[$ip] ?? [
            'offense_count' => 0,
            'first_offense' => null,
            'last_offense' => null,
            'history' => [],
            'blacklisted' => false
        ];
    }
    
    /**
     * Update jail history after a ban
     */
    private function updateJailHistory($ip, $category, $duration) {
        $history = $this->loadJailHistory();
        
        if (!isset($history[$ip])) {
            $history[$ip] = [
                'offense_count' => 0,
                'first_offense' => time(),
                'last_offense' => null,
                'history' => [],
                'blacklisted' => false
            ];
        }
        
        $history[$ip]['offense_count']++;
        $history[$ip]['last_offense'] = time();
        $history[$ip]['history'][] = [
            'time' => time(),
            'category' => $category,
            'duration' => $duration
        ];
        
        $this->saveJailHistory($history);
        
        // Check if should be blacklisted
        if ($history[$ip]['offense_count'] >= self::RECIDIVE_PERMANENT_THRESHOLD) {
            $this->addToBlacklist($ip, "Repeat offender - {$history[$ip]['offense_count']} offenses", $history[$ip]['offense_count']);
        }
    }
    
    /**
     * Load jail history from file
     */
    private function loadJailHistory() {
        if (!file_exists($this->jailHistoryFile)) {
            return [];
        }
        $content = @file_get_contents($this->jailHistoryFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    /**
     * Save jail history to file
     */
    private function saveJailHistory($history) {
        $dir = dirname($this->jailHistoryFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->jailHistoryFile, json_encode($history, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    // ========================================
    // BLACKLIST SYSTEM
    // ========================================
    
    /**
     * Check if IP is permanently blacklisted
     */
    private function isBlacklisted($ip) {
        $blacklist = $this->loadBlacklist();
        return isset($blacklist[$ip]);
    }
    
    /**
     * Add IP to permanent blacklist
     */
    private function addToBlacklist($ip, $reason, $offenseCount) {
        $blacklist = $this->loadBlacklist();
        
        $blacklist[$ip] = [
            'blacklisted_at' => time(),
            'reason' => $reason,
            'offense_count' => $offenseCount
        ];
        
        $this->saveBlacklist($blacklist);
        $this->log("BLACKLISTED: IP $ip - Reason: $reason - Offense count: $offenseCount");
        
        // Update jail history
        $history = $this->loadJailHistory();
        if (isset($history[$ip])) {
            $history[$ip]['blacklisted'] = true;
            $this->saveJailHistory($history);
        }
    }
    
    /**
     * Remove IP from blacklist
     */
    public function removeFromBlacklist($ip) {
        $blacklist = $this->loadBlacklist();
        
        if (isset($blacklist[$ip])) {
            unset($blacklist[$ip]);
            $this->saveBlacklist($blacklist);
            $this->log("UNBLACKLISTED: IP $ip");
            
            // Reset jail history
            $history = $this->loadJailHistory();
            if (isset($history[$ip])) {
                unset($history[$ip]);
                $this->saveJailHistory($history);
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Get blacklist
     */
    public function getBlacklist() {
        return $this->loadBlacklist();
    }
    
    /**
     * Load blacklist from file
     */
    private function loadBlacklist() {
        if (!file_exists($this->blacklistFile)) {
            return [];
        }
        $content = @file_get_contents($this->blacklistFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    /**
     * Save blacklist to file
     */
    private function saveBlacklist($blacklist) {
        $dir = dirname($this->blacklistFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->blacklistFile, json_encode($blacklist, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    // ========================================
    // BRUTE FORCE TRACKING
    // ========================================
    
    private function recordBruteForceAttempt($ip, $uri, $method, $pattern) {
        $attempts = $this->loadBruteForceAttempts();
        
        if (!isset($attempts[$ip])) {
            $attempts[$ip] = [];
        }
        
        $attempts[$ip][] = [
            'time' => time(),
            'uri' => $uri,
            'method' => $method,
            'pattern' => $pattern
        ];
        
        // Clean old attempts (older than 5 minutes)
        $attempts[$ip] = array_filter($attempts[$ip], function($attempt) {
            return (time() - $attempt['time']) < self::BRUTE_FORCE_WINDOW;
        });
        
        $this->saveBruteForceAttempts($attempts);
    }
    
    private function getBruteForceAttemptCount($ip) {
        $attempts = $this->loadBruteForceAttempts();
        
        if (!isset($attempts[$ip])) {
            return 0;
        }
        
        // Count only recent attempts (within 5 min window)
        $recentAttempts = array_filter($attempts[$ip], function($attempt) {
            return (time() - $attempt['time']) < self::BRUTE_FORCE_WINDOW;
        });
        
        return count($recentAttempts);
    }
    
    private function clearBruteForceAttempts($ip) {
        $attempts = $this->loadBruteForceAttempts();
        
        if (isset($attempts[$ip])) {
            unset($attempts[$ip]);
            $this->saveBruteForceAttempts($attempts);
        }
    }
    
    private function loadBruteForceAttempts() {
        if (!file_exists($this->bruteForceFile)) {
            return [];
        }
        $content = @file_get_contents($this->bruteForceFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    private function saveBruteForceAttempts($attempts) {
        $dir = dirname($this->bruteForceFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->bruteForceFile, json_encode($attempts, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Mendapatkan IP klien yang sebenarnya
     */
    private function getClientIP() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    private function isWhitelisted($ip) {
        return in_array($ip, $this->whitelistedIPs);
    }
    
    private function isIgnoredExtension($uri) {
        $path = parse_url($uri, PHP_URL_PATH);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, $this->ignoredExtensions);
    }
    
    private function matchesPatternCategory($uri, $patterns) {
        $decodedUri = urldecode($uri);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uri) || preg_match($pattern, $decodedUri)) {
                return $pattern;
            }
        }
        
        // Check query string too
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $decodedQuery = urldecode($queryString);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $queryString) || preg_match($pattern, $decodedQuery)) {
                return $pattern;
            }
        }
        
        return null;
    }
    
    // ========================================
    // BLOCKING METHODS
    // ========================================
    
    private function isBlocked($ip) {
        $blockedIPs = $this->loadBlockList();
        
        if (isset($blockedIPs[$ip])) {
            $blockData = $blockedIPs[$ip];
            $expiresAt = $blockData['expires_at'] ?? 0;
            
            if ($expiresAt > 0 && time() > $expiresAt) {
                $this->unblockIP($ip);
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    private function blockIPWithCategory($ip, $reason, $category, $duration) {
        $blockedIPs = $this->loadBlockList();
        
        $blockedIPs[$ip] = [
            'blocked_at' => time(),
            'reason' => $reason,
            'category' => $category,
            'duration' => $duration,
            'expires_at' => time() + $duration,
            'attempts' => $this->getAttemptCount($ip)
        ];
        
        $this->saveBlockList($blockedIPs);
    }
    
    private function blockIP($ip, $reason) {
        $this->blockIPWithCategory($ip, $reason, self::CATEGORY_GENERAL, $this->banDuration);
    }
    
    private function unblockIP($ip) {
        $blockedIPs = $this->loadBlockList();
        
        if (isset($blockedIPs[$ip])) {
            unset($blockedIPs[$ip]);
            $this->saveBlockList($blockedIPs);
            $this->log("UNBLOCKED: IP $ip (expired)");
        }
        
        $this->clearAttempts($ip);
    }
    
    // ========================================
    // ATTEMPT TRACKING (General)
    // ========================================
    
    private function recordAttempt($ip, $uri, $method, $pattern) {
        $attemptsFile = dirname($this->blockListFile) . '/attempts.json';
        $attempts = [];
        
        if (file_exists($attemptsFile)) {
            $content = @file_get_contents($attemptsFile);
            $attempts = $content ? (json_decode($content, true) ?: []) : [];
        }
        
        if (!isset($attempts[$ip])) {
            $attempts[$ip] = [];
        }
        
        $attempts[$ip][] = [
            'time' => time(),
            'uri' => $uri,
            'method' => $method,
            'pattern' => $pattern,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        // Clean old attempts (older than 1 hour)
        $attempts[$ip] = array_filter($attempts[$ip], function($attempt) {
            return (time() - $attempt['time']) < 3600;
        });
        
        @file_put_contents($attemptsFile, json_encode($attempts, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    private function getAttemptCount($ip) {
        $attemptsFile = dirname($this->blockListFile) . '/attempts.json';
        
        if (!file_exists($attemptsFile)) {
            return 0;
        }
        
        $content = @file_get_contents($attemptsFile);
        $attempts = $content ? (json_decode($content, true) ?: []) : [];
        
        if (!isset($attempts[$ip])) {
            return 0;
        }
        
        $recentAttempts = array_filter($attempts[$ip], function($attempt) {
            return (time() - $attempt['time']) < 3600;
        });
        
        return count($recentAttempts);
    }
    
    private function clearAttempts($ip) {
        $attemptsFile = dirname($this->blockListFile) . '/attempts.json';
        
        if (!file_exists($attemptsFile)) {
            return;
        }
        
        $content = @file_get_contents($attemptsFile);
        $attempts = $content ? (json_decode($content, true) ?: []) : [];
        
        if (isset($attempts[$ip])) {
            unset($attempts[$ip]);
            @file_put_contents($attemptsFile, json_encode($attempts, JSON_PRETTY_PRINT), LOCK_EX);
        }
    }
    
    // ========================================
    // FILE OPERATIONS
    // ========================================
    
    private function loadBlockList() {
        if (!file_exists($this->blockListFile)) {
            return [];
        }
        $content = @file_get_contents($this->blockListFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    private function saveBlockList($blockedIPs) {
        $dir = dirname($this->blockListFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->blockListFile, json_encode($blockedIPs, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    // ========================================
    // RESPONSES
    // ========================================
    
    private function blockResponse($ip, $reason) {
        http_response_code(500);
        exit;
    }
    
    private function warningResponse($ip, $attempts) {
        http_response_code(500);
        exit;
    }
    
    // ========================================
    // LOGGING
    // ========================================
    
    private function log($message) {
        if (!$this->logEnabled) {
            return;
        }
        
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $this->getClientIP();
        $logMessage = "[$timestamp] [$ip] $message\n";
        
        @file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    // ========================================
    // PUBLIC API
    // ========================================
    
    public function addPattern($pattern) {
        $this->generalPatterns[] = $pattern;
    }
    
    public function addWhitelistedIP($ip) {
        if (!in_array($ip, $this->whitelistedIPs)) {
            $this->whitelistedIPs[] = $ip;
        }
    }
    
    public function manualBlockIP($ip, $reason = 'Manual block', $duration = null) {
        $duration = $duration ?? $this->banDuration;
        
        $blockedIPs = $this->loadBlockList();
        
        $blockedIPs[$ip] = [
            'blocked_at' => time(),
            'reason' => $reason,
            'category' => 'MANUAL',
            'duration' => $duration,
            'expires_at' => time() + $duration,
            'manual' => true
        ];
        
        $this->saveBlockList($blockedIPs);
        $this->log("MANUAL BLOCK: IP $ip - Reason: $reason - Duration: {$duration}s");
    }
    
    public function manualUnblockIP($ip) {
        $this->unblockIP($ip);
        $this->log("MANUAL UNBLOCK: IP $ip");
    }
    
    public function manualBlacklist($ip, $reason = 'Manual blacklist') {
        $this->addToBlacklist($ip, $reason, 0);
    }
    
    public function getBlockedIPs() {
        return $this->loadBlockList();
    }
    
    public function getJailHistoryAll() {
        return $this->loadJailHistory();
    }
    
    public function getStatistics() {
        $blockedIPs = $this->loadBlockList();
        $blacklist = $this->loadBlacklist();
        $jailHistory = $this->loadJailHistory();
        $subnetBans = $this->loadSubnetBans();
        $attemptsFile = dirname($this->blockListFile) . '/attempts.json';
        $attempts = file_exists($attemptsFile) 
            ? (json_decode(@file_get_contents($attemptsFile), true) ?: [])
            : [];
        
        $totalAttempts = 0;
        foreach ($attempts as $ipAttempts) {
            $totalAttempts += count($ipAttempts);
        }
        
        // Count by category
        $categoryA = 0;
        $categoryB = 0;
        $categoryC = 0;
        foreach ($blockedIPs as $data) {
            switch ($data['category'] ?? 'C') {
                case self::CATEGORY_BRUTE_FORCE:
                    $categoryA++;
                    break;
                case self::CATEGORY_FATAL:
                    $categoryB++;
                    break;
                default:
                    $categoryC++;
            }
        }
        
        // Count repeat offenders
        $repeatOffenders = 0;
        foreach ($jailHistory as $data) {
            if (($data['offense_count'] ?? 0) > 1) {
                $repeatOffenders++;
            }
        }
        
        return [
            'blocked_ips' => count($blockedIPs),
            'blacklisted_ips' => count($blacklist),
            'banned_subnets' => count($subnetBans),
            'unique_attackers' => count($attempts),
            'total_attempts' => $totalAttempts,
            'repeat_offenders' => $repeatOffenders,
            'category_a' => $categoryA,
            'category_b' => $categoryB,
            'category_c' => $categoryC,
            'ban_duration' => $this->banDuration,
            'max_attempts' => $this->maxAttempts
        ];
    }
    
    public function cleanup() {
        $blockedIPs = $this->loadBlockList();
        $cleaned = 0;
        
        foreach ($blockedIPs as $ip => $data) {
            if (isset($data['expires_at']) && time() > $data['expires_at']) {
                unset($blockedIPs[$ip]);
                $this->clearAttempts($ip);
                $cleaned++;
            }
        }
        
        if ($cleaned > 0) {
            $this->saveBlockList($blockedIPs);
            $this->log("CLEANUP: Removed $cleaned expired IP blocks");
        }
        
        // Cleanup expired subnet bans
        $subnetBans = $this->loadSubnetBans();
        $subnetCleaned = 0;
        
        foreach ($subnetBans as $subnet => $data) {
            if (isset($data['expires_at']) && time() > $data['expires_at']) {
                unset($subnetBans[$subnet]);
                $subnetCleaned++;
            }
        }
        
        if ($subnetCleaned > 0) {
            $this->saveSubnetBans($subnetBans);
            $this->log("CLEANUP: Removed $subnetCleaned expired subnet blocks");
        }
        
        // Cleanup old subnet tracking entries
        $this->cleanupSubnetTracking();
        
        return $cleaned + $subnetCleaned;
    }
    
    // ========================================
    // SUBNET BANNING SYSTEM
    // ========================================
    
    /**
     * Ekstrak subnet /24 dari IP address
     */
    private function getSubnet($ip, $mask = 24) {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null; // Skip IPv6 for now
        }
        
        $parts = explode('.', $ip);
        if (count($parts) !== 4) {
            return null;
        }
        
        // For /24, return first 3 octets
        return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.0/24';
    }
    
    /**
     * Cek apakah subnet di-ban
     */
    private function isSubnetBanned($subnet) {
        if ($subnet === null) {
            return false;
        }
        
        $subnetBans = $this->loadSubnetBans();
        
        if (isset($subnetBans[$subnet])) {
            $banData = $subnetBans[$subnet];
            $expiresAt = $banData['expires_at'] ?? 0;
            
            // Check if expired
            if ($expiresAt > 0 && time() > $expiresAt) {
                $this->unbanSubnet($subnet);
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Track attack dari subnet untuk automatic banning
     */
    private function trackSubnetAttack($ip, $pattern) {
        $subnet = $this->getSubnet($ip);
        if ($subnet === null) {
            return;
        }
        
        $tracking = $this->loadSubnetTracking();
        
        if (!isset($tracking[$subnet])) {
            $tracking[$subnet] = [
                'ips' => [],
                'first_attack' => time(),
                'last_attack' => time()
            ];
        }
        
        // Record this IP attack
        $tracking[$subnet]['ips'][$ip] = [
            'time' => time(),
            'pattern' => $pattern
        ];
        $tracking[$subnet]['last_attack'] = time();
        
        // Remove old entries (outside window)
        $tracking[$subnet]['ips'] = array_filter($tracking[$subnet]['ips'], function($data) {
            return (time() - $data['time']) < self::SUBNET_ATTACK_WINDOW;
        });
        
        $this->saveSubnetTracking($tracking);
        
        // Check if should ban subnet
        $this->checkAndBanSubnet($subnet, $tracking[$subnet]);
    }
    
    /**
     * Check if subnet should be banned based on attack patterns
     */
    private function checkAndBanSubnet($subnet, $subnetData) {
        $uniqueIPs = count($subnetData['ips']);
        
        if ($uniqueIPs >= self::SUBNET_ATTACK_THRESHOLD) {
            $ips = array_keys($subnetData['ips']);
            $reason = "Automated ban: $uniqueIPs unique IPs attacked from subnet - IPs: " . implode(', ', array_slice($ips, 0, 5));
            
            $this->banSubnet($subnet, $reason, self::SUBNET_BAN_DURATION);
            $this->log("SUBNET BAN TRIGGERED: $subnet - $uniqueIPs unique attacking IPs");
            
            // Clear tracking for this subnet
            $tracking = $this->loadSubnetTracking();
            unset($tracking[$subnet]);
            $this->saveSubnetTracking($tracking);
        }
    }
    
    /**
     * Ban entire subnet
     */
    public function banSubnet($subnet, $reason = 'Manual ban', $duration = null) {
        $duration = $duration ?? self::SUBNET_BAN_DURATION;
        
        $subnetBans = $this->loadSubnetBans();
        
        $subnetBans[$subnet] = [
            'banned_at' => time(),
            'reason' => $reason,
            'duration' => $duration,
            'expires_at' => time() + $duration
        ];
        
        $this->saveSubnetBans($subnetBans);
        $this->log("SUBNET BANNED: $subnet - Reason: $reason - Duration: " . ($duration / 3600) . " hours");
    }
    
    /**
     * Unban subnet
     */
    public function unbanSubnet($subnet) {
        $subnetBans = $this->loadSubnetBans();
        
        if (isset($subnetBans[$subnet])) {
            unset($subnetBans[$subnet]);
            $this->saveSubnetBans($subnetBans);
            $this->log("SUBNET UNBANNED: $subnet");
            return true;
        }
        
        return false;
    }
    
    /**
     * Get all banned subnets
     */
    public function getBannedSubnets() {
        return $this->loadSubnetBans();
    }
    
    /**
     * Manual subnet ban from admin panel
     */
    public function manualBanSubnet($subnet, $reason = 'Manual ban', $duration = null) {
        // Validate subnet format
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2}$/', $subnet)) {
            return false;
        }
        
        $this->banSubnet($subnet, $reason, $duration ?? self::SUBNET_BAN_DURATION);
        return true;
    }
    
    // ========================================
    // SUBNET FILE OPERATIONS
    // ========================================
    
    private function loadSubnetBans() {
        if (!file_exists($this->subnetBanFile)) {
            return [];
        }
        $content = @file_get_contents($this->subnetBanFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    private function saveSubnetBans($bans) {
        $dir = dirname($this->subnetBanFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->subnetBanFile, json_encode($bans, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    private function loadSubnetTracking() {
        if (!file_exists($this->subnetTrackFile)) {
            return [];
        }
        $content = @file_get_contents($this->subnetTrackFile);
        return $content ? (json_decode($content, true) ?: []) : [];
    }
    
    private function saveSubnetTracking($tracking) {
        $dir = dirname($this->subnetTrackFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->subnetTrackFile, json_encode($tracking, JSON_PRETTY_PRINT), LOCK_EX);
    }
    
    private function cleanupSubnetTracking() {
        $tracking = $this->loadSubnetTracking();
        $cleaned = 0;
        
        foreach ($tracking as $subnet => $data) {
            // Remove if all entries are old
            $hasRecent = false;
            foreach ($data['ips'] ?? [] as $ipData) {
                if ((time() - $ipData['time']) < self::SUBNET_ATTACK_WINDOW) {
                    $hasRecent = true;
                    break;
                }
            }
            
            if (!$hasRecent) {
                unset($tracking[$subnet]);
                $cleaned++;
            }
        }
        
        if ($cleaned > 0) {
            $this->saveSubnetTracking($tracking);
        }
        
        return $cleaned;
    }
}
