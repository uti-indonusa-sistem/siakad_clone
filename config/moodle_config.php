<?php
/**
 * Moodle Integration Configuration
 * SIAKAD → API → Moodle Web Service → Course & Enrolment
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

// Moodle Configuration
define('MOODLE_URL', 'https://learning.poltekindonusa.ac.id');
define('MOODLE_TOKEN', 'b32db04fa66d39937b8dd53845b8518c'); // TODO: Isi dengan token dari Moodle Web Service
define('MOODLE_SERVICE', 'moodle_mobile_app'); // atau custom service name

// Moodle Web Service Endpoints
define('MOODLE_WS_URL', MOODLE_URL . '/webservice/rest/server.php');
define('MOODLE_WS_FORMAT', 'json');

// Email Configuration
define('EMAIL_DOMAIN', '@poltekindonusa.ac.id');
define('EMAIL_REQUIRED', false); // Email tidak wajib

// Sync Configuration
define('SYNC_BATCH_SIZE', 50); // Jumlah record per batch
define('SYNC_TIMEOUT', 300); // Timeout dalam detik
define('SYNC_LOG_ENABLED', true); // Enable logging

// Password Configuration
define('DEFAULT_PASSWORD_LENGTH', 8);
define('PASSWORD_SYNC_ENABLED', true); // Sync password ke Moodle

// Mapping Configuration
define('ROLE_STUDENT', 5); // Moodle student role ID
define('ROLE_TEACHER', 3); // Moodle teacher role ID
define('ROLE_EDITINGTEACHER', 4); // Moodle editing teacher role ID

// Sync Schedule
define('AUTO_SYNC_ENABLED', false); // Auto sync via cron
define('SYNC_INTERVAL', 3600); // Interval sync dalam detik (1 jam)

// Debug Mode
define('MOODLE_DEBUG', true); // Enable debug mode

/**
 * Get Moodle configuration as array
 */
function getMoodleConfig()
{
    return [
        'url' => MOODLE_URL,
        'token' => MOODLE_TOKEN,
        'service' => MOODLE_SERVICE,
        'ws_url' => MOODLE_WS_URL,
        'format' => MOODLE_WS_FORMAT,
        'email_domain' => EMAIL_DOMAIN,
        'email_required' => EMAIL_REQUIRED,
        'sync_batch_size' => SYNC_BATCH_SIZE,
        'sync_timeout' => SYNC_TIMEOUT,
        'sync_log_enabled' => SYNC_LOG_ENABLED,
        'password_sync_enabled' => PASSWORD_SYNC_ENABLED,
        'role_student' => ROLE_STUDENT,
        'role_teacher' => ROLE_TEACHER,
        'role_editingteacher' => ROLE_EDITINGTEACHER,
        'auto_sync_enabled' => AUTO_SYNC_ENABLED,
        'sync_interval' => SYNC_INTERVAL,
        'debug' => MOODLE_DEBUG
    ];
}

/**
 * Validate Moodle configuration
 */
function validateMoodleConfig()
{
    $errors = [];

    if (empty(MOODLE_TOKEN)) {
        $errors[] = 'Moodle token belum dikonfigurasi';
    }

    if (!filter_var(MOODLE_URL, FILTER_VALIDATE_URL)) {
        $errors[] = 'Moodle URL tidak valid';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
