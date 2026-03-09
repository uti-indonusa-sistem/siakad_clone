<?php
/**
 * Security Admin Panel
 * Interface untuk mengelola SecurityMiddleware
 */

require_once 'SecurityMiddleware.php';

// Manual ENV Parser to ensure key is loaded
$rootDir = realpath(__DIR__ . '/../../../');
$envFile = $rootDir . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
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

// Load konfigurasi dari env jika ada, atau gunakan default
$securityKey = getenv('SECURITY_ADMIN_KEY') ?: 'Indonusa2026Secure!'; // Default fallback

// Cek akses key
$key = $_GET['key'] ?? '';
if ($key !== $securityKey) {
    header('HTTP/1.0 403 Forbidden');
    die('Access Denied');
}

// Inisialisasi Middleware
$security = new SecurityMiddleware();

// Handle Actions
$message = '';
$messageType = 'success';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// CSRF Token sederhana (based on key + date)
$csrfToken = md5($securityKey . date('Y-m-d'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if ($submittedToken !== $csrfToken) {
        $message = "Invalid CSRF Token";
        $messageType = 'error';
    } else {
        switch ($action) {
            case 'unblock':
                $ip = $_POST['ip'] ?? '';
                if ($ip) {
                    $security->manualUnblockIP($ip);
                    $message = "IP $ip berhasil di-unblock";
                }
                break;
                
            case 'block':
                $ip = $_POST['ip'] ?? '';
                $reason = $_POST['reason'] ?? 'Manual block via admin';
                if ($ip) {
                    $security->manualBlockIP($ip, $reason);
                    $message = "IP $ip berhasil diblokir manual";
                }
                break;
                
            case 'blacklist':
                $ip = $_POST['ip'] ?? '';
                $reason = $_POST['reason'] ?? 'Manual blacklist via admin';
                if ($ip) {
                    $security->manualBlacklist($ip, $reason);
                    $message = "IP $ip berhasil ditambahkan ke blacklist permanen";
                }
                break;
                
            case 'remove_blacklist':
                $ip = $_POST['ip'] ?? '';
                if ($ip) {
                    if ($security->removeFromBlacklist($ip)) {
                        $message = "IP $ip berhasil dihapus dari blacklist";
                    } else {
                        $message = "IP $ip tidak ditemukan di blacklist";
                        $messageType = 'warning';
                    }
                }
                break;
            
            case 'ban_subnet':
                $subnet = $_POST['subnet'] ?? '';
                $reason = $_POST['reason'] ?? 'Manual subnet ban';
                if ($subnet) {
                    if ($security->manualBanSubnet($subnet, $reason)) {
                        $message = "Subnet $subnet berhasil diblokir";
                    } else {
                        $message = "Format subnet tidak valid (Gunakan format CIDR: x.x.x.x/24)";
                        $messageType = 'error';
                    }
                }
                break;
                
            case 'unban_subnet':
                $subnet = $_POST['subnet'] ?? '';
                if ($subnet) {
                    if ($security->unbanSubnet($subnet)) {
                        $message = "Subnet $subnet berhasil di-unblock";
                    } else {
                        $message = "Subnet tidak ditemukan";
                        $messageType = 'warning';
                    }
                }
                break;
                
            case 'cleanup':
                $count = $security->cleanup();
                $message = "Cleanup selesai. $count data kadaluarsa dihapus.";
                break;
        }
    }
}

// Get Data
$blockedIPs = $security->getBlockedIPs();
$blacklist = $security->getBlacklist();
$jailHistory = $security->getJailHistoryAll();
$stats = $security->getStatistics();
$bannedSubnets = $security->getBannedSubnets();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Security Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { --primary: #2c3e50; --danger: #e74c3c; --success: #27ae60; --warning: #f39c12; --light: #ecf0f1; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #f5f7fa; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .h-title { margin: 0; color: var(--primary); }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card h2 { margin-top: 0; border-bottom: 2px solid var(--light); padding-bottom: 10px; font-size: 1.2rem; color: var(--primary); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-box { background: var(--light); padding: 15px; border-radius: 6px; text-align: center; }
        .stat-num { font-size: 2rem; font-weight: bold; color: var(--primary); display: block; }
        .stat-label { font-size: 0.9rem; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 0.9rem; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; }
        tr:hover { background-color: #f1f1f1; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; color: white; }
        .bg-danger { background: var(--danger); }
        .bg-warning { background: var(--warning); }
        .bg-success { background: var(--success); }
        .bg-info { background: #3498db; }
        .bg-dark { background: #34495e; }
        
        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.85rem; text-decoration: none; display: inline-block; }
        .btn-sm { padding: 4px 8px; font-size: 0.75rem; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .form-inline { display: flex; gap: 10px; margin-bottom: 15px; }
        .form-control { padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1; }
        
        .tabs { display: flex; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 2px solid transparent; }
        .tab.active { border-bottom-color: var(--primary); color: var(--primary); font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .form-inline { flex-direction: column; }
            table { display: block; overflow-x: auto; }
        }
    </style>
    <script>
        function showTab(id) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + id).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 class="h-title">Security Admin</h1>
                <small>Farmasindo Security System for SIAKAD</small>
            </div>
            <div>
                <form method="POST" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <button type="submit" name="action" value="cleanup" class="btn btn-primary">Run Cleanup</button>
                    <a href="?key=<?php echo $key; ?>" class="btn btn-success">Refresh</a>
                </form>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="card">
            <h2>System Overview</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <span class="stat-num"><?php echo $stats['blocked_ips']; ?></span>
                    <span class="stat-label">Active Host Blocks</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?php echo $stats['banned_subnets']; ?></span>
                    <span class="stat-label">Active Subnet Bans</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?php echo $stats['blacklisted_ips']; ?></span>
                    <span class="stat-label">Blacklisted IPs</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?php echo $stats['repeat_offenders']; ?></span>
                    <span class="stat-label">Repeat Offenders</span>
                </div>
                <div class="stat-box" style="background: #ffebee;">
                    <span class="stat-num" style="color: #c62828"><?php echo $stats['category_b']; ?></span>
                    <span class="stat-label">Fatal Attacks (Cat B)</span>
                </div>
                <div class="stat-box">
                    <span class="stat-num"><?php echo $stats['category_a']; ?></span>
                    <span class="stat-label">Brute Force (Cat A)</span>
                </div>
            </div>
        </div>
        
        <!-- Manual Actions -->
        <div class="card">
            <h2>Manual Actions</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 300px;">
                    <h3>Block IP / Add to Blacklist</h3>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="text" name="ip" placeholder="IP Address" class="form-control" required>
                        <input type="text" name="reason" placeholder="Reason" class="form-control" required>
                        <button type="submit" name="action" value="block" class="btn btn-warning">Temp Block</button>
                        <button type="submit" name="action" value="blacklist" class="btn btn-danger">Blacklist</button>
                    </form>
                </div>
                <div style="flex: 1; min-width: 300px;">
                    <h3>Ban Subnet (CIDR /24)</h3>
                    <form method="POST" class="form-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="text" name="subnet" placeholder="e.g. 192.168.1.0/24" class="form-control" required>
                        <input type="text" name="reason" placeholder="Reason" class="form-control" required>
                        <button type="submit" name="action" value="ban_subnet" class="btn btn-danger">Ban Subnet</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('blocked')">Active IP Blocks</div>
            <div class="tab" onclick="showTab('subnets')">Subnet Bans</div>
            <div class="tab" onclick="showTab('blacklist')">Blacklist</div>
            <div class="tab" onclick="showTab('history')">Jail History</div>
        </div>
        
        <!-- Active Blocks Tab -->
        <div id="tab-blocked" class="tab-content active card">
            <h2>Active IP Blocks</h2>
            <?php if (empty($blockedIPs)): ?>
                <p>No active IP blocks.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Category</th>
                            <th>Blocked At</th>
                            <th>Expires At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blockedIPs as $ip => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ip); ?></td>
                            <td><?php echo htmlspecialchars($data['reason']); ?></td>
                            <td>
                                <span class="badge <?php echo $data['category'] == 'B' ? 'bg-danger' : ($data['category'] == 'A' ? 'bg-warning' : 'bg-info'); ?>">
                                    <?php echo htmlspecialchars($data['category']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i:s', $data['blocked_at']); ?></td>
                            <td>
                                <?php 
                                    $timeLeft = $data['expires_at'] - time();
                                    echo date('Y-m-d H:i:s', $data['expires_at']); 
                                    echo " <small>(" . ($timeLeft > 0 ? ceil($timeLeft/60) . "m left" : "Expired") . ")</small>";
                                ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="ip" value="<?php echo htmlspecialchars($ip); ?>">
                                    <button type="submit" name="action" value="unblock" class="btn btn-sm btn-success">Unblock</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Subnet Bans Tab -->
        <div id="tab-subnets" class="tab-content card">
            <h2>Subnet Bans</h2>
            <?php if (empty($bannedSubnets)): ?>
                <p>No active subnet bans.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Subnet</th>
                            <th>Reason</th>
                            <th>Banned At</th>
                            <th>Expires At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bannedSubnets as $subnet => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($subnet); ?></td>
                            <td><?php echo htmlspecialchars($data['reason']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', $data['banned_at']); ?></td>
                            <td>
                                <?php 
                                    $timeLeft = $data['expires_at'] - time();
                                    echo date('Y-m-d H:i:s', $data['expires_at']); 
                                    echo " <small>(" . ($timeLeft > 0 ? ceil($timeLeft/3600) . "h left" : "Expired") . ")</small>";
                                ?>
                            </td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="subnet" value="<?php echo htmlspecialchars($subnet); ?>">
                                    <button type="submit" name="action" value="unban_subnet" class="btn btn-sm btn-success">Unban</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Blacklist Tab -->
        <div id="tab-blacklist" class="tab-content card">
            <h2>Permanent Blacklist</h2>
            <?php if (empty($blacklist)): ?>
                <p>No IPs in blacklist.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Offenses</th>
                            <th>Date Added</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blacklist as $ip => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ip); ?></td>
                            <td><?php echo htmlspecialchars($data['reason']); ?></td>
                            <td><?php echo htmlspecialchars($data['offense_count']); ?></td>
                            <td><?php echo date('Y-m-d H:i:s', $data['blacklisted_at']); ?></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="ip" value="<?php echo htmlspecialchars($ip); ?>">
                                    <button type="submit" name="action" value="remove_blacklist" class="btn btn-sm btn-success">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Jail History Tab -->
        <div id="tab-history" class="tab-content card">
            <h2>Jail History (Recent Offenders)</h2>
            <?php if (empty($jailHistory)): ?>
                <p>No history records.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Total Offenses</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Sort by last offense desc
                        uasort($jailHistory, function($a, $b) {
                            return $b['last_offense'] - $a['last_offense'];
                        });
                        
                        foreach (array_slice($jailHistory, 0, 50) as $ip => $data): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ip); ?></td>
                            <td>
                                <span class="badge bg-dark"><?php echo $data['offense_count']; ?></span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', $data['first_offense']); ?></td>
                            <td><?php echo date('Y-m-d H:i', $data['last_offense']); ?></td>
                            <td>
                                <?php if ($data['blacklisted']): ?>
                                    <span class="badge bg-danger">BLACKLISTED</span>
                                <?php elseif ($data['offense_count'] >= 2): ?>
                                    <span class="badge bg-warning">High Risk</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Monitored</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
    </div>
</body>
</html>
