<?php
/**
 * Security Bootstrap for SIAKAD
 * Load and initialize SecurityMiddleware
 */

// Simple .env parsing (Manual implementation to avoid dependencies)
$rootDir = realpath(__DIR__ . '/../../'); // Root project directory from public/lib/
$envFile = $rootDir . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

require_once __DIR__ . '/security/SecurityMiddleware.php';

// Inisialisasi Middleware
$security = new SecurityMiddleware([
    'enabled' => getenv('SECURITY_ENABLED') !== 'false',
    'logEnabled' => getenv('SECURITY_LOG_ENABLED') !== 'false',
    'banDuration' => 3600, // 1 jam default
    'maxAttempts' => 5
]);

// Jalankan security check untuk setiap request
$security->handle();
