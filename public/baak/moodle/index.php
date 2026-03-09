<?php
/**
 * Moodle Sync Dashboard
 * Dashboard untuk monitoring dan kontrol sinkronisasi SIAKAD-Moodle
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

session_start();

// Matikan output error HTML agar tidak merusak JSON jika request AJAX
if (isset($_POST['action'])) {
    set_time_limit(300); // Increase execution time to 5 minutes
    error_reporting(0);
    ini_set('display_errors', 0);
    ob_start();
}

// Determine base path - works for both local and production
$basePath = dirname(dirname(dirname(__DIR__)));

require_once $basePath . '/config/config.php';
require_once $basePath . '/config/moodle_config.php';
require_once $basePath . '/ws/MoodleWebService.php';
require_once $basePath . '/ws/MoodleSyncService.php';

// Check authentication (sesuaikan dengan sistem auth SIAKAD)
// if (!isset($_SESSION['user_id'])) {
//     header('Location: /login.php');
//     exit;
// }

$db = koneksi();
$moodleService = new MoodleWebService();
$syncService = new MoodleSyncService();

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'test_connection':
                $result = $moodleService->testConnection();
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_mahasiswa':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : null;
                $angkatan = isset($_POST['angkatan']) ? $_POST['angkatan'] : null;
                $result = $syncService->syncMahasiswa($limit, $angkatan);
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_dosen':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : null;
                $result = $syncService->syncDosen($limit);
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_courses':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : null;
                $semester = isset($_POST['semester']) ? $_POST['semester'] : null;
                $result = $syncService->syncCourses($limit, $semester);
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_enrolments':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : null;
                $semester = isset($_POST['semester']) ? $_POST['semester'] : null;
                $result = $syncService->syncEnrolments($limit, $semester);
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_grades':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : null;
                $semester = isset($_POST['semester']) ? $_POST['semester'] : null;
                $result = $syncService->syncGrades($semester, $limit);
                ob_clean();
                echo json_encode($result);
                break;

            case 'sync_all':
                $result = $syncService->syncAll();
                ob_clean();
                echo json_encode($result);
                break;

            case 'get_stats':
                $statsData = $syncService->getSyncStats();
                ob_clean();
                echo json_encode(['success' => true, 'data' => $statsData]);
                break;

            case 'get_logs':
                $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
                $sql = "SELECT * FROM moodle_sync_log ORDER BY created_at DESC LIMIT :limit";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ob_clean();
                echo json_encode(['success' => true, 'data' => $logs]);
                break;

            case 'update_config':
                $key = $_POST['key'] ?? '';
                $value = $_POST['value'] ?? '';

                if ($key) {
                    $sql = "UPDATE moodle_config SET config_value = :value WHERE config_key = :key";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['key' => $key, 'value' => $value]);
                    ob_clean();
                    echo json_encode(['success' => true, 'message' => 'Configuration updated']);
                } else {
                    ob_clean();
                    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
                }
                break;

            default:
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $_POST['action']]);
        }
    } catch (Exception $e) {
        // Tangkap error database atau sistem dan kembalikan sebagai JSON
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'details' => [
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10)
            ],
            'hint' => 'Pastikan database migration sudah dijalankan dan file MoodleSyncService.php ada.'
        ]);
    }
    ob_end_flush();
    exit;
}

// Get configuration
$configArray = [];
try {
    $sql = "SELECT * FROM moodle_config";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $configs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($configs as $config) {
        $configArray[$config['config_key']] = $config['config_value'];
    }
} catch (Exception $e) {
    // Table doesn't exist - migration not run yet
    $configArray = [
        'moodle_url' => MOODLE_URL,
        'moodle_token' => MOODLE_TOKEN,
        'email_domain' => EMAIL_DOMAIN
    ];
}

// Get statistics
$stats = [];
try {
    $stats = $syncService->getSyncStats();
} catch (Exception $e) {
    // Table doesn't exist yet
    $stats = [
        'mahasiswa' => 0,
        'dosen' => 0,
        'course' => 0,
        'category' => 0,
        'last_sync' => null
    ];
}

// Get recent logs
$recentLogs = [];
try {
    $sql = "SELECT * FROM moodle_sync_log ORDER BY created_at DESC LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $recentLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table doesn't exist yet
    $recentLogs = [];
}

// Check if migration tables exist
$tablesExist = true;
try {
    $stmt = $db->query("SHOW TABLES LIKE 'moodle_sync_mapping'");
    if ($stmt->rowCount() == 0) {
        $tablesExist = false;
    }
} catch (Exception $e) {
    $tablesExist = false;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moodle Sync Dashboard - SIAKAD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 16px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #999;
            font-size: 14px;
        }

        .action-panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .action-panel h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 239, 125, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .config-panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .config-panel h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .config-grid {
            display: grid;
            gap: 15px;
        }

        .config-item {
            display: grid;
            grid-template-columns: 200px 1fr auto;
            gap: 15px;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .config-item label {
            font-weight: 600;
            color: #555;
        }

        .config-item input {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .config-item input:focus {
            outline: none;
            border-color: #667eea;
        }

        .logs-panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .logs-panel h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
        }

        .log-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e0e0;
        }

        .log-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .log-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.active {
            display: flex;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.active {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .connection-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .connection-status.connected {
            background: #d4edda;
            color: #155724;
        }

        .connection-status.disconnected {
            background: #f8d7da;
            color: #721c24;
        }

        .connection-status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <div class="header">
            <h1>🔄 Moodle Sync Dashboard</h1>
            <p>Sinkronisasi data SIAKAD dengan Moodle Learning Management System</p>
            <div style="margin-top: 15px;">
                <span class="connection-status" id="connectionStatus">
                    <span class="dot"></span>
                    <span id="connectionText">Checking connection...</span>
                </span>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Mahasiswa</h3>
                <div class="value">
                    <?= $stats['mahasiswa'] ?? 0 ?>
                </div>
                <div class="label">Total synced</div>
            </div>
            <div class="stat-card">
                <h3>Dosen</h3>
                <div class="value">
                    <?= $stats['dosen'] ?? 0 ?>
                </div>
                <div class="label">Total synced</div>
            </div>
            <div class="stat-card">
                <h3>Courses</h3>
                <div class="value">
                    <?= $stats['course'] ?? 0 ?>
                </div>
                <div class="label">Total synced</div>
            </div>
            <div class="stat-card">
                <h3>Categories</h3>
                <div class="value">
                    <?= $stats['category'] ?? 0 ?>
                </div>
                <div class="label">Total synced</div>
            </div>
        </div>

        <?php if (!$tablesExist): ?>
            <!-- Migration Warning -->
            <div
                style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Database Migration Required</h3>
                <p style="color: #856404; margin-bottom: 15px;">
                    Tabel database untuk Moodle integration belum dibuat. Silakan jalankan migration terlebih dahulu.
                </p>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <strong>Via phpMyAdmin:</strong>
                    <ol style="margin: 10px 0; padding-left: 20px; color: #856404;">
                        <li>Login ke phpMyAdmin</li>
                        <li>Pilih database: <code>siakaddb</code></li>
                        <li>Tab "SQL"</li>
                        <li>Copy-paste isi file: <code>database/quick_migration_moodle.sql</code></li>
                        <li>Klik "Go"</li>
                    </ol>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px;">
                    <strong>Via Command Line:</strong>
                    <pre
                        style="background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; color: #333;">mysql -u usiakad -p siakaddb &lt; /var/116/indonusa_siakad/database/quick_migration_moodle.sql</pre>
                </div>
            </div>
        <?php endif; ?>

        <div class="action-panel">
            <h2>⚡ Quick Actions</h2>

            <!-- Filter Options -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
                <h3 style="margin-bottom: 15px; font-size: 18px; color: #555;">🔍 Filter Options</h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">
                            Angkatan Mahasiswa
                        </label>
                        <select id="filter_angkatan"
                            style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                            <option value="">Semua Angkatan</option>
                            <?php
                            $currentYear = date('Y');
                            for ($i = 0; $i < 5; $i++) {
                                $year = $currentYear - $i;
                                echo "<option value='$year'>Angkatan $year</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">
                            Semester Aktif
                        </label>
                        <select id="filter_semester"
                            style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;">
                            <option value="">Semester Aktif</option>
                            <?php
                            try {
                                $sqlSmt = "SELECT id_smt, nm_smt FROM wsia_semester ORDER BY id_smt DESC LIMIT 5";
                                $stmtSmt = $db->prepare($sqlSmt);
                                $stmtSmt->execute();
                                $semesters = $stmtSmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($semesters as $smt) {
                                    $selected = '';
                                    echo "<option value='{$smt['id_smt']}' $selected>{$smt['nm_smt']}</option>";
                                }
                            } catch (Exception $e) {
                                echo "<option value=''>Error loading semesters</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #555;">
                            Limit Data (Test)
                        </label>
                        <input type="number" id="filter_limit" placeholder="Kosongkan untuk semua"
                            style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;" min="1"
                            max="1000">
                    </div>
                </div>

                <div
                    style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <small style="color: #856404;">
                        <strong>💡 Tip:</strong> Untuk test pertama, gunakan limit 5-10 data.
                        Setelah berhasil, baru sync semua data.
                    </small>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="syncData('sync_mahasiswa')">
                    👨‍🎓 Sync Mahasiswa
                </button>
                <button class="btn btn-success" onclick="syncData('sync_dosen')">
                    👨‍🏫 Sync Dosen
                </button>
                <button class="btn btn-warning" onclick="syncData('sync_courses')">
                    📚 Sync Courses
                </button>
                <button class="btn btn-info" onclick="syncData('sync_enrolments')">
                    🎯 Sync Enrolments
                </button>
                <button class="btn btn-warning" onclick="syncData('sync_grades')" style="background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);">
                    🏆 Sync Grades from Moodle
                </button>
            </div>
            <button class="btn btn-primary" onclick="syncData('sync_all')" style="width: 100%;">
                🚀 Sync All Data
            </button>
        </div>

        <div class="config-panel">
            <h2>⚙️ Configuration</h2>
            <div class="config-grid">
                <div class="config-item">
                    <label>Moodle URL</label>
                    <input type="text" id="config_moodle_url"
                        value="<?= htmlspecialchars($configArray['moodle_url'] ?? '') ?>">
                    <button class="btn btn-primary" onclick="updateConfig('moodle_url')">Save</button>
                </div>
                <div class="config-item">
                    <label>Moodle Token</label>
                    <input type="password" id="config_moodle_token"
                        value="<?= htmlspecialchars($configArray['moodle_token'] ?? '') ?>">
                    <button class="btn btn-primary" onclick="updateConfig('moodle_token')">Save</button>
                </div>
                <div class="config-item">
                    <label>Email Domain</label>
                    <input type="text" id="config_email_domain"
                        value="<?= htmlspecialchars($configArray['email_domain'] ?? '') ?>">
                    <button class="btn btn-primary" onclick="updateConfig('email_domain')">Save</button>
                </div>
                <div class="config-item">
                    <label>Auto Sync</label>
                    <input type="checkbox" id="config_auto_sync_enabled" <?= ($configArray['auto_sync_enabled'] ?? '0') == '1' ? 'checked' : '' ?>>
                    <button class="btn btn-primary" onclick="updateConfig('auto_sync_enabled')">Save</button>
                </div>
            </div>
        </div>

        <div class="logs-panel">
            <h2>📋 Recent Sync Logs</h2>
            <table class="log-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td>
                                <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($log['action']) ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $log['status'] ?? 'success' ?>">
                                    <?= strtoupper($log['status'] ?? 'success') ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-info"
                                    onclick="showLogDetails(<?= htmlspecialchars($log['results']) ?>)">
                                    View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Check connection on load
        window.addEventListener('load', function () {
            testConnection();
        });

        function testConnection() {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_connection'
            })
                .then(response => response.json())
                .then(data => {
                    const statusEl = document.getElementById('connectionStatus');
                    const textEl = document.getElementById('connectionText');

                    if (data.success) {
                        statusEl.className = 'connection-status connected';
                        textEl.textContent = 'Connected to Moodle';
                    } else {
                        statusEl.className = 'connection-status disconnected';
                        textEl.textContent = 'Connection failed';
                    }
                })
                .catch(error => {
                    const statusEl = document.getElementById('connectionStatus');
                    const textEl = document.getElementById('connectionText');
                    statusEl.className = 'connection-status disconnected';
                    textEl.textContent = 'Connection error';
                });
        }

        function syncData(action) {
            const loading = document.getElementById('loading');
            loading.classList.add('active');

            // Get filter values
            const angkatan = document.getElementById('filter_angkatan').value;
            const semester = document.getElementById('filter_semester').value;
            const limit = document.getElementById('filter_limit').value;

            // Build request body with filters
            let body = 'action=' + action;
            if (angkatan) body += '&angkatan=' + angkatan;
            if (semester) body += '&semester=' + semester;
            if (limit) body += '&limit=' + limit;

            console.log('🔄 Sync Request:', { action, angkatan, semester, limit, body });

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            })
                .then(response => {
                    console.log('📥 Response Status:', response.status, response.statusText);
                    return response.text().then(text => {
                        console.log('📄 Raw Response:', text);

                        // Check if response is empty
                        if (!text || text.trim() === '') {
                            throw new Error('Server returned empty response');
                        }

                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('❌ JSON Parse Error:', e);
                            console.error('📄 Raw Response Text:', text);

                            // Show the raw response in a modal for debugging
                            showDebugModal('JSON Parse Error', text);
                            throw new Error('Server returned invalid JSON. Check the debug modal for raw response.');
                        }
                    });
                })
                .then(data => {
                    loading.classList.remove('active');
                    console.log('✅ Parsed Response:', data);

                    if (data.success !== false) {
                        showAlert('success', 'Sync completed successfully!');

                        // Show detailed results
                        let message = '';
                        if (action === 'sync_all') {
                            for (let key in data) {
                                if (data[key].total) {
                                    message += `${key}: ${data[key].success}/${data[key].total} synced\n`;
                                }
                            }
                        } else {
                            message = `Total: ${data.total || 0}\n`;
                            message += `Success: ${data.success || 0}\n`;
                            message += `Failed: ${data.failed || 0}\n`;
                            message += `Created: ${data.created || 0}\n`;
                            message += `Updated: ${data.updated || 0}`;
                        }

                        if (confirm('Sync completed!\n\n' + message + '\n\nRefresh page to see updated stats?')) {
                            location.reload();
                        }
                    } else {
                        console.error('❌ Sync Failed:', data);
                        showAlert('error', 'Sync failed: ' + (data.error || 'Unknown error'));

                        // Show full error details
                        if (data.error || data.details) {
                            showDebugModal('Sync Error Details', JSON.stringify(data, null, 2));
                        }
                    }
                })
                .catch(error => {
                    loading.classList.remove('active');
                    console.error('❌ Fetch Error:', error);
                    showAlert('error', 'Error: ' + error.message);
                });
        }

        function showDebugModal(title, content) {
            // Remove existing modal if any
            const existingModal = document.getElementById('debugModal');
            if (existingModal) existingModal.remove();

            const modal = document.createElement('div');
            modal.id = 'debugModal';
            modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.8);z-index:10000;display:flex;justify-content:center;align-items:center;padding:20px;';

            modal.innerHTML = `
                <div style="background:white;border-radius:15px;max-width:800px;width:100%;max-height:80vh;overflow:hidden;display:flex;flex-direction:column;">
                    <div style="padding:20px;background:#f8d7da;border-bottom:1px solid #f5c6cb;">
                        <h3 style="margin:0;color:#721c24;">🐛 ${title}</h3>
                    </div>
                    <div style="padding:20px;overflow:auto;flex:1;">
                        <pre style="background:#f8f9fa;padding:15px;border-radius:8px;overflow-x:auto;white-space:pre-wrap;word-wrap:break-word;font-size:12px;max-height:400px;overflow-y:auto;">${escapeHtml(content)}</pre>
                    </div>
                    <div style="padding:15px;background:#f8f9fa;display:flex;gap:10px;justify-content:flex-end;">
                        <button onclick="copyDebugContent()" style="padding:10px 20px;border:none;border-radius:8px;background:#667eea;color:white;cursor:pointer;">📋 Copy</button>
                        <button onclick="document.getElementById('debugModal').remove()" style="padding:10px 20px;border:none;border-radius:8px;background:#6c757d;color:white;cursor:pointer;">Close</button>
                    </div>
                </div>
            `;

            modal.querySelector('pre').setAttribute('data-content', content);
            document.body.appendChild(modal);

            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function copyDebugContent() {
            const pre = document.querySelector('#debugModal pre');
            if (pre) {
                navigator.clipboard.writeText(pre.getAttribute('data-content') || pre.textContent)
                    .then(() => showAlert('success', 'Content copied to clipboard!'))
                    .catch(() => showAlert('error', 'Failed to copy'));
            }
        }

        function updateConfig(key) {
            const inputEl = document.getElementById('config_' + key);
            let value = inputEl.value;

            if (inputEl.type === 'checkbox') {
                value = inputEl.checked ? '1' : '0';
            }

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update_config&key=' + key + '&value=' + encodeURIComponent(value)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', 'Configuration updated successfully!');

                        // Test connection if URL or token changed
                        if (key === 'moodle_url' || key === 'moodle_token') {
                            setTimeout(testConnection, 1000);
                        }
                    } else {
                        showAlert('error', 'Failed to update configuration');
                    }
                })
                .catch(error => {
                    showAlert('error', 'Error: ' + error.message);
                });
        }

        function showAlert(type, message) {
            const container = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' active';
            alert.textContent = message;

            container.appendChild(alert);

            setTimeout(() => {
                alert.classList.remove('active');
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        function showLogDetails(results) {
            try {
                const data = typeof results === 'string' ? JSON.parse(results) : results;
                alert(JSON.stringify(data, null, 2));
            } catch (e) {
                alert(results);
            }
        }

        // Auto refresh stats every 30 seconds
        setInterval(() => {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_stats'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update stats display
                        console.log('Stats updated:', data.data);
                    }
                });
        }, 30000);
    </script>
</body>

</html>