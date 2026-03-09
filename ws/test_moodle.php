<?php
/**
 * Moodle Integration Test Script
 * Script untuk testing koneksi dan fungsi-fungsi integrasi
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/moodle_config.php';
require_once __DIR__ . '/../ws/MoodleWebService.php';
require_once __DIR__ . '/../ws/MoodleSyncService.php';

echo "===========================================\n";
echo "MOODLE INTEGRATION TEST SCRIPT\n";
echo "===========================================\n\n";

// Test 1: Configuration
echo "Test 1: Checking Configuration...\n";
$configValidation = validateMoodleConfig();
if ($configValidation['valid']) {
    echo "✅ Configuration is valid\n";
} else {
    echo "❌ Configuration errors:\n";
    foreach ($configValidation['errors'] as $error) {
        echo "   - $error\n";
    }
}
echo "\n";

// Test 2: Connection
echo "Test 2: Testing Moodle Connection...\n";
$moodle = new MoodleWebService();
$connectionTest = $moodle->testConnection();

if ($connectionTest['success']) {
    echo "✅ Connection successful\n";
    echo "   Site: " . ($connectionTest['data']['sitename'] ?? 'N/A') . "\n";
    echo "   User: " . ($connectionTest['data']['username'] ?? 'N/A') . "\n";
    echo "   User ID: " . ($connectionTest['data']['userid'] ?? 'N/A') . "\n";
} else {
    echo "❌ Connection failed\n";
    echo "   Error: " . ($connectionTest['error'] ?? 'Unknown error') . "\n";
}
echo "\n";

// Test 3: Database Connection
echo "Test 3: Testing Database Connection...\n";
try {
    $db = koneksi();
    echo "✅ Database connection successful\n";

    // Check if tables exist
    $tables = ['moodle_sync_mapping', 'moodle_sync_log', 'moodle_config'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ❌ Table '$table' not found\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Database connection failed\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Sample Data
echo "Test 4: Checking Sample Data...\n";
try {
    $db = koneksi();

    // Count mahasiswa
    $stmt = $db->query("SELECT COUNT(*) as count FROM mahasiswa WHERE status_mahasiswa = 'A'");
    $mahasiswaCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Mahasiswa aktif: $mahasiswaCount\n";

    // Count dosen
    $stmt = $db->query("SELECT COUNT(*) as count FROM dosen WHERE status_aktif = '1'");
    $dosenCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Dosen aktif: $dosenCount\n";

    // Count mata kuliah
    $stmt = $db->query("SELECT COUNT(*) as count FROM matakuliah WHERE status_aktif = '1'");
    $mkCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "   Mata kuliah aktif: $mkCount\n";

    echo "✅ Sample data available\n";
} catch (Exception $e) {
    echo "❌ Error checking sample data\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test Create User (Dry Run)
echo "Test 5: Testing User Creation (Dry Run)...\n";
try {
    $db = koneksi();
    $stmt = $db->query("SELECT * FROM mahasiswa WHERE status_mahasiswa = 'A' LIMIT 1");
    $testMhs = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($testMhs) {
        echo "   Test data: NIM " . $testMhs['nim'] . " - " . $testMhs['nama'] . "\n";

        // Check if already exists in Moodle
        $existingUser = $moodle->getUserByIdNumber($testMhs['nim']);

        if ($existingUser['success'] && !empty($existingUser['data']['users'])) {
            echo "   ✅ User already exists in Moodle (ID: " . $existingUser['data']['users'][0]['id'] . ")\n";
        } else {
            echo "   ℹ️  User not found in Moodle (ready to create)\n";
        }
    } else {
        echo "   ⚠️  No test data available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Sync Statistics
echo "Test 6: Current Sync Statistics...\n";
try {
    $syncService = new MoodleSyncService();
    $stats = $syncService->getSyncStats();

    echo "   Mahasiswa synced: " . ($stats['mahasiswa'] ?? 0) . "\n";
    echo "   Dosen synced: " . ($stats['dosen'] ?? 0) . "\n";
    echo "   Courses synced: " . ($stats['course'] ?? 0) . "\n";
    echo "   Categories synced: " . ($stats['category'] ?? 0) . "\n";
    echo "   Last sync: " . ($stats['last_sync'] ?? 'Never') . "\n";

    echo "✅ Statistics retrieved\n";
} catch (Exception $e) {
    echo "❌ Error getting statistics\n";
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Summary
echo "===========================================\n";
echo "TEST SUMMARY\n";
echo "===========================================\n";
echo "Configuration: " . ($configValidation['valid'] ? '✅ OK' : '❌ FAIL') . "\n";
echo "Connection: " . ($connectionTest['success'] ? '✅ OK' : '❌ FAIL') . "\n";
echo "\n";

if ($configValidation['valid'] && $connectionTest['success']) {
    echo "🎉 System is ready for synchronization!\n";
    echo "\nNext steps:\n";
    echo "1. Access dashboard: https://siakad.poltekindonusa.ac.id/baak/moodle/\n";
    echo "2. Start with small batch: Sync 10 mahasiswa first\n";
    echo "3. Verify in Moodle\n";
    echo "4. Then proceed with full sync\n";
} else {
    echo "⚠️  Please fix the errors above before proceeding\n";
    echo "\nRefer to MOODLE_INTEGRATION.md for troubleshooting\n";
}

echo "\n";
