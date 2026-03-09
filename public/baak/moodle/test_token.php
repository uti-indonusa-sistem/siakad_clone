<?php
/**
 * Moodle Token Test Script
 * Test token dan capabilities secara detail
 */

header('Content-Type: text/html; charset=utf-8');

// Load configuration
$basePath = dirname(dirname(dirname(__DIR__)));
require_once $basePath . '/config/moodle_config.php';

$moodleUrl = MOODLE_URL;
$token = MOODLE_TOKEN;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Moodle Token Test</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { color: #155724; background: #d4edda; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #721c24; background: #f8d7da; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Moodle Token & Capability Test</h1>";

echo "<div class='card'>
    <h2>📋 Configuration</h2>
    <table>
        <tr><th>Moodle URL</th><td><code>$moodleUrl</code></td></tr>
        <tr><th>Token</th><td><code>" . substr($token, 0, 10) . "..." . substr($token, -5) . "</code> (hidden for security)</td></tr>
    </table>
</div>";

// Function to call Moodle API
function callMoodleAPI($url, $token, $function, $params = [])
{
    $wsUrl = rtrim($url, '/') . '/webservice/rest/server.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wsUrl . '?' . http_build_query([
        'wstoken' => $token,
        'wsfunction' => $function,
        'moodlewsrestformat' => 'json'
    ]));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'curl_error' => $error,
        'response' => $response,
        'data' => json_decode($response, true)
    ];
}

// Test 1: Site Info (Basic Connection)
echo "<div class='card'>
    <h2>🔗 Test 1: Basic Connection (core_webservice_get_site_info)</h2>";

$result = callMoodleAPI($moodleUrl, $token, 'core_webservice_get_site_info');

if ($result['curl_error']) {
    echo "<div class='error'>❌ cURL Error: {$result['curl_error']}</div>";
} elseif ($result['http_code'] !== 200) {
    echo "<div class='error'>❌ HTTP Error: {$result['http_code']}</div>";
} elseif (isset($result['data']['exception'])) {
    echo "<div class='error'>❌ Moodle Error: {$result['data']['message']}</div>";
    echo "<pre>" . json_encode($result['data'], JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<div class='success'>✅ Connection successful!</div>";
    echo "<table>
        <tr><th>Site Name</th><td>{$result['data']['sitename']}</td></tr>
        <tr><th>Username</th><td>{$result['data']['username']}</td></tr>
        <tr><th>User ID</th><td>{$result['data']['userid']}</td></tr>
        <tr><th>Full Name</th><td>{$result['data']['fullname']}</td></tr>
    </table>";

    // Show available functions
    if (isset($result['data']['functions'])) {
        echo "<h3>📦 Available Functions for this Token:</h3>";
        $functions = array_column($result['data']['functions'], 'name');

        $requiredFunctions = [
            'core_user_create_users' => 'Create users',
            'core_user_update_users' => 'Update users',
            'core_user_get_users' => 'Get users',
            'core_user_get_users_by_field' => 'Get users by field',
            'core_course_create_courses' => 'Create courses',
            'core_course_update_courses' => 'Update courses',
            'core_course_get_courses_by_field' => 'Get courses',
            'core_course_get_categories' => 'Get categories',
            'core_course_create_categories' => 'Create categories',
            'enrol_manual_enrol_users' => 'Enrol users',
            'enrol_manual_unenrol_users' => 'Unenrol users',
            'core_enrol_get_enrolled_users' => 'Get enrolled users',
        ];

        echo "<table>
            <tr><th>Function</th><th>Description</th><th>Status</th></tr>";

        $missingCount = 0;
        foreach ($requiredFunctions as $func => $desc) {
            $available = in_array($func, $functions);
            $badge = $available
                ? "<span class='badge badge-success'>Available</span>"
                : "<span class='badge badge-danger'>Missing</span>";
            if (!$available)
                $missingCount++;
            echo "<tr><td><code>$func</code></td><td>$desc</td><td>$badge</td></tr>";
        }
        echo "</table>";

        if ($missingCount > 0) {
            echo "<div class='warning'>⚠️ $missingCount required function(s) missing. Please add them to the External Service.</div>";
        } else {
            echo "<div class='success'>✅ All required functions are available!</div>";
        }
    }
}
echo "</div>";

// Test 2: Get Users (Read Permission)
echo "<div class='card'>
    <h2>👤 Test 2: Get User (core_user_get_users_by_field)</h2>";

$result = callMoodleAPI($moodleUrl, $token, 'core_user_get_users_by_field', [
    'field' => 'username',
    'values' => ['admin']
]);

if (isset($result['data']['exception'])) {
    echo "<div class='error'>❌ Error: {$result['data']['message']}</div>";
    echo "<div class='info'>💡 Pastikan function <code>core_user_get_users_by_field</code> sudah ditambahkan ke External Service</div>";
} else {
    echo "<div class='success'>✅ Read user permission OK!</div>";
}
echo "</div>";

// Test 3: Create User (Write Permission) - Dry Run dengan test user
echo "<div class='card'>
    <h2>✍️ Test 3: Create User Permission (Dry Test)</h2>";

$testUsername = 'test_siakad_' . time();
$result = callMoodleAPI($moodleUrl, $token, 'core_user_create_users', [
    'users' => [
        [
            'username' => $testUsername,
            'password' => 'Test@12345',
            'firstname' => 'Test',
            'lastname' => 'SIAKAD',
            'email' => $testUsername . '@test.local',
            'idnumber' => 'TEST_' . time()
        ]
    ]
]);

if (isset($result['data']['exception'])) {
    echo "<div class='error'>❌ Error: {$result['data']['message']}</div>";

    // Common error solutions
    if (strpos($result['data']['message'], 'Access control exception') !== false) {
        echo "<div class='warning'>
            <strong>🔧 Kemungkinan Solusi:</strong>
            <ol>
                <li><strong>Assign System Role:</strong> Site Administration → Users → Permissions → Assign system roles → Pilih Manager → Add user yang token-nya dipakai</li>
                <li><strong>Authorize User:</strong> Site Administration → Plugins → Web services → External services → [Nama Service] → Authorised users → Add user</li>
                <li><strong>Check Functions:</strong> Pastikan <code>core_user_create_users</code> ada di External Service</li>
            </ol>
        </div>";
    }

    echo "<h4>Raw Response:</h4>";
    echo "<pre>" . json_encode($result['data'], JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<div class='success'>✅ Create user permission OK! Test user created successfully.</div>";

    // Clean up - delete test user
    if (isset($result['data'][0]['id'])) {
        $userId = $result['data'][0]['id'];
        echo "<div class='info'>ℹ️ Test user created with ID: $userId</div>";

        // Try to delete
        $deleteResult = callMoodleAPI($moodleUrl, $token, 'core_user_delete_users', [
            'userids' => [$userId]
        ]);

        if (!isset($deleteResult['data']['exception'])) {
            echo "<div class='info'>🗑️ Test user deleted successfully.</div>";
        }
    }
}
echo "</div>";

// Summary
echo "<div class='card'>
    <h2>📊 Summary & Next Steps</h2>
    <div class='info'>
        <strong>Jika masih error 'Access control exception':</strong>
        <ol>
            <li>Buka Moodle sebagai Admin</li>
            <li>Pergi ke <strong>Site Administration → Users → Permissions → Assign system roles</strong></li>
            <li>Klik <strong>Manager</strong></li>
            <li>Tambahkan user yang token-nya digunakan (cek username di Test 1 di atas)</li>
            <li>Pergi ke <strong>Site Administration → Plugins → Web services → External services</strong></li>
            <li>Klik nama service Anda → <strong>Authorised users</strong> → Add user yang sama</li>
        </ol>
    </div>
</div>";

echo "</body></html>";
