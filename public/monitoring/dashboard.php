<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Sebaran Nilai</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            /* Palette */
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;

            /* Dark Theme (Default) */
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.05);
            --border-hover: rgba(255, 255, 255, 0.1);
            --input-bg: #1e293b;
        }

        [data-theme="light"] {
            --bg-body: #f8fafc;
            /* Slate 50 */
            --bg-card: #ffffff;
            --text-main: #0f172a;
            /* Slate 900 */
            --text-muted: #64748b;
            /* Slate 500 */
            --border-color: #e2e8f0;
            /* Slate 200 */
            --border-hover: #cbd5e1;
            /* Slate 300 */
            --input-bg: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Layout */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-section h1 {
            font-size: 1.5rem;
            margin: 0;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-section p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0.25rem 0 0 0;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .logout-btn {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
        }

        /* Nav Menu */
        .main-nav {
            display: flex;
            gap: 2rem;
            margin: 0 2rem;
            flex-grow: 1;
        }

        @media (max-width: 768px) {
            .main-nav {
                display: none;
                /* Hide on mobile for now, or stack */
            }
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s;
            padding: 0.5rem 0;
            position: relative;
        }

        .nav-link:hover {
            color: var(--text-main);
        }

        .nav-link.active {
            color: var(--primary);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -1rem;
            /* Matches header padding-bottom */
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--primary);
            box-shadow: 0 -2px 10px rgba(99, 102, 241, 0.5);
        }

        /* Dashboard Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }

        .card {
            background-color: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color);
        }

        /* Summary Stats (Top Row) */
        .stat-card {
            grid-column: span 3;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            .stat-card {
                grid-column: span 12;
            }
        }

        .stat-content h3 {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.5rem 0;
            font-weight: 500;
        }

        .stat-content .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-content .subtext {
            font-size: 0.75rem;
            color: var(--success);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-icon {
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Charts */
        .main-chart {
            grid-column: span 8;
            min-height: 400px;
        }

        .side-chart {
            grid-column: span 4;
            min-height: 400px;
        }

        @media (max-width: 1024px) {

            .main-chart,
            .side-chart {
                grid-column: span 12;
            }
        }

        .card-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Table */
        .table-container {
            grid-column: span 12;
            margin-top: 1rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            text-align: left;
            padding: 1rem;
            color: var(--text-muted);
            font-weight: 500;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-a {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .badge-b {
            background: rgba(59, 130, 246, 0.1);
            color: #60a5fa;
        }

        .badge-c {
            background: rgba(245, 158, 11, 0.1);
            color: #fbbf24;
        }

        .badge-d {
            background: rgba(249, 115, 22, 0.1);
            color: #fdba74;
        }

        .badge-e {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--bg-body);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s;
        }

        /* Filter Section */
        .filter-card {
            background-color: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group label {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: var(--input-bg);
            /* Solid background */
            border: 1px solid var(--border-hover);
            color: var(--text-main);
            font-family: inherit;
        }

        .form-control option {
            background-color: var(--bg-card);
            color: var(--text-main);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-group {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>

<body>

    <!-- Loader -->
    <div id="loader" class="loader-overlay">
        <div style="text-align:center">
            <i class="fas fa-circle-notch fa-spin fa-3x" style="color:var(--primary)"></i>
            <p style="margin-top:1rem;color:var(--text-gray)">Memuat Data Sebaran Nilai...</p>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <header>
            <div class="brand-section">
                <h1>Executive Dashboard</h1>
                <p>Monitoring Sebaran Nilai Mahasiswa</p>
            </div>

            <nav class="main-nav">
                <a href="dashboard.php" class="nav-link active">Sebaran Nilai</a>
                <a href="cetak_dokumen.php" class="nav-link">Cetak Dokumen</a>
            </nav>

            <div class="user-nav">
                <button onclick="toggleTheme()" class="logout-btn"
                    style="color:var(--text-muted); border-color:var(--border-color); background:transparent; margin-right:1rem; cursor:pointer"
                    title="Toggle Theme">
                    <i class="fas fa-sun" id="themeIcon"></i>
                </button>

                <span style="color:var(--text-muted); font-size:0.9rem;">
                    <i class="fas fa-circle" style="color:var(--success); font-size:0.6rem; margin-right:0.5rem"></i>
                    <?php echo isset($_SESSION['monitoring_username']) ? ucfirst($_SESSION['monitoring_username']) : 'Admin'; ?>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </header>

        <!-- Filter Section -->
        <div class="filter-card">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Tahun Akademik</label>
                    <select id="filterSemester" class="form-control">
                        <option value="">-- Periode Aktif --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Program Studi</label>
                    <select id="filterProdi" class="form-control">
                        <option value="">Semua Prodi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Mata Kuliah</label>
                    <select id="filterMk" class="form-control">
                        <option value="">-- Pilih Mata Kuliah --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select id="filterKelas" class="form-control">
                        <option value="">-- Pilih Kelas --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Angkatan</label>
                    <select id="filterAngkatan" class="form-control">
                        <option value="">Semua Angkatan</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button onclick="applyFilters()" class="btn-primary"><i class="fas fa-filter"></i> Terapkan</button>
                    <button onclick="downloadCsv()" class="btn-success"><i class="fas fa-download"></i> XLSX</button>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid">
            <div class="card stat-card">
                <div class="stat-content">
                    <h3>Periode Aktif</h3>
                    <div class="value" id="semesterVal">-</div>
                    <div class="subtext">
                        Tahun Ajaran Terakhir
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="card stat-card">
                <div class="stat-content">
                    <h3>Total Nilai Masuk</h3>
                    <div class="value" id="totalVal">0</div>
                    <div class="subtext">
                        <i class="fas fa-arrow-up"></i> Data Terkini
                    </div>
                </div>
                <div class="stat-icon" style="background:rgba(16, 185, 129, 0.1); color:var(--success)">
                    <i class="fas fa-database"></i>
                </div>
            </div>

            <div class="card stat-card">
                <div class="stat-content">
                    <h3>Rasio Kelulusan</h3>
                    <div class="value" id="passRateVal">0%</div>
                    <div class="subtext">
                        (Nilai A, B, C)
                    </div>
                </div>
                <div class="stat-icon" style="background:rgba(245, 158, 11, 0.1); color:var(--warning)">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>

            <div class="card stat-card">
                <div class="stat-content">
                    <h3>Dominasi Nilai</h3>
                    <div class="value" id="topGradeVal">-</div>
                    <div class="subtext" id="topGradeDesc">
                        0 Mahasiswa
                    </div>
                </div>
                <div class="stat-icon" style="background:rgba(239, 68, 68, 0.1); color:var(--danger)">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>

            <!-- Main Charts -->
            <div class="card main-chart">
                <div class="card-header">
                    <h2 class="card-title">Distribusi Nilai (Bar Chart)</h2>
                    <button onclick="applyFilters()"
                        style="background:none; border:none; color:var(--text-muted); cursor:pointer">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div style="position: relative; height: 300px; width:100%">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <div class="card side-chart">
                <div class="card-header">
                    <h2 class="card-title">Proporsi (Pie Chart)</h2>
                </div>
                <div style="position: relative; height: 300px; width:100%">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Data Table -->
        <div class="card table-container" style="margin-top:2rem">
            <div class="card-header">
                <h2 class="card-title">Detail Nilai Mahasiswa (100 Data Terbaru)</h2>
                <div style="font-size:0.85rem; color:var(--text-muted)" id="tableCount">0 data</div>
            </div>
            <div style="overflow-x:auto">
                <table style="width:100%">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th>Mata Kuliah</th>
                            <th>Kelas</th>
                            <th>Nilai</th>
                            <th>Indeks</th>
                        </tr>
                    </thead>
                    <tbody id="gradeTableBody">
                        <tr>
                            <td colspan="7" style="text-align:center; padding:2rem; color:var(--text-gray)">Menunggu
                                filter...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Data Fetching and Chart Rendering
        let barChartInstance = null;
        let pieChartInstance = null;
        let activeSemesterId = '';
        let lastDashboardData = null;

        async function initFilters() {
            try {
                const response = await fetch('data_sebaran.php?action=options');
                const data = await response.json();

                // Populate Semester
                const semSelect = document.getElementById('filterSemester');
                if (data.semester && data.semester.length > 0) {
                    data.semester.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.id;
                        opt.text = s.text;
                        semSelect.appendChild(opt);
                    });
                    // Set default to first (latest)
                    activeSemesterId = data.semester[0].id;
                    semSelect.value = activeSemesterId;
                }

                // Populate Prodi
                const prodiSelect = document.getElementById('filterProdi');
                data.prodi.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.text = p.text;
                    prodiSelect.appendChild(opt);
                });

                // Populate Angkatan
                const angkatanSelect = document.getElementById('filterAngkatan');
                data.angkatan.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.text = a.text;
                    angkatanSelect.appendChild(opt);
                });

                // Trigger Load MK - REMOVED for lazy loading
                // loadMatakuliah();

            } catch (e) {
                console.error('Failed to load filter options', e);
            }
        }

        // ... existing functions ...

        // Run
        document.addEventListener('DOMContentLoaded', () => {
            // Theme Init
            initTheme();

            console.log("Dashboard Loaded: v1.7 (Lazy Load)");
            setupDynamicFilters();
            initFilters().catch(e => console.error("InitFilters failed", e));
            // Do not auto-load data. Wait for user to click Apply.
            initDashboard(null); // Just to set the "Select Filter" state
        });
        async function loadMatakuliah() {
            const sem = document.getElementById('filterSemester').value || activeSemesterId;
            const prodi = document.getElementById('filterProdi').value;
            const mkSelect = document.getElementById('filterMk');

            mkSelect.innerHTML = '<option value="">Memuat...</option>';
            document.getElementById('filterKelas').innerHTML = '<option value="">-- Pilih Kelas --</option>'; // Reset kelas

            try {
                const url = `data_sebaran.php?action=get_mk&semester=${sem}&prodi=${prodi}`;
                const resp = await fetch(url);
                const data = await resp.json();

                mkSelect.innerHTML = '<option value="">-- Semua Mata Kuliah --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.text = item.text + ' (' + item.id + ')';
                    mkSelect.appendChild(opt);
                });
            } catch (e) {
                console.error('Failed load MK', e);
                mkSelect.innerHTML = '<option value="">Gagal Memuat</option>';
            }
        }

        async function loadKelas() {
            const sem = document.getElementById('filterSemester').value || activeSemesterId;
            const prodi = document.getElementById('filterProdi').value;
            const mk = document.getElementById('filterMk').value;
            const kelasSelect = document.getElementById('filterKelas');

            if (!mk) {
                kelasSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                return;
            }

            kelasSelect.innerHTML = '<option value="">Memuat...</option>';

            try {
                const url = `data_sebaran.php?action=get_kelas&semester=${sem}&prodi=${prodi}&matakuliah=${mk}`;
                const resp = await fetch(url);
                const data = await resp.json();

                kelasSelect.innerHTML = '<option value="">-- Semua Kelas --</option>';
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id; // nm_kls
                    opt.text = item.text;
                    kelasSelect.appendChild(opt);
                });
            } catch (e) {
                console.error('Failed load Kelas', e);
                kelasSelect.innerHTML = '<option value="">Gagal Memuat</option>';
            }
        }

        function setupDynamicFilters() {
            document.getElementById('filterSemester').addEventListener('change', () => {
                loadMatakuliah();
            });
            document.getElementById('filterProdi').addEventListener('change', () => {
                loadMatakuliah();
            });
            document.getElementById('filterMk').addEventListener('change', () => {
                loadKelas();
            });

            // Auto-disable Angkatan if Kelas is selected
            document.getElementById('filterKelas').addEventListener('change', (e) => {
                const angkatanInput = document.getElementById('filterAngkatan');
                const angkatanGroup = angkatanInput.closest('.form-group');
                if (e.target.value) {
                    angkatanInput.value = ''; // Reset
                    angkatanGroup.style.opacity = '0.5';
                    angkatanGroup.style.pointerEvents = 'none';
                } else {
                    angkatanGroup.style.opacity = '1';
                    angkatanGroup.style.pointerEvents = 'auto';
                }
            });
        }

        function getFilterParams() {
            const params = new URLSearchParams();
            const semester = document.getElementById('filterSemester').value;
            const prodi = document.getElementById('filterProdi').value;
            const mk = document.getElementById('filterMk').value;
            const kelas = document.getElementById('filterKelas').value;
            const angkatan = document.getElementById('filterAngkatan').value;

            if (semester) params.append('semester', semester);
            if (prodi) params.append('prodi', prodi);
            if (mk) params.append('matakuliah', mk);
            if (kelas) params.append('kelas', kelas);
            if (angkatan) params.append('angkatan', angkatan);

            return params;
        }

        async function initDashboard(params = null) {
            const loader = document.getElementById('loader');

            // IF INITIAL LOAD (No params), DO NOT FETCH DATA
            if (!params) {
                // Hide loader immediately
                loader.style.display = 'none';

                // Show "Select Filter" state
                document.getElementById('semesterVal').innerText = '-';
                document.getElementById('totalVal').innerText = '-';
                document.getElementById('passRateVal').innerText = '-';
                document.getElementById('topGradeVal').innerText = '-';
                document.getElementById('topGradeDesc').innerText = 'Silakan pilih filter';

                // Clear charts with message
                const ctxBar = document.getElementById('barChart').getContext('2d');
                if (barChartInstance) barChartInstance.destroy();
                ctxBar.clearRect(0, 0, ctxBar.canvas.width, ctxBar.canvas.height);
                ctxBar.font = "14px Arial";
                ctxBar.fillStyle = getComputedStyle(document.body).getPropertyValue('--text-muted');
                ctxBar.textAlign = "center";
                ctxBar.fillText("Silakan pilih Tahun Akademik & Prodi untuk menampilkan data", ctxBar.canvas.width / 2, ctxBar.canvas.height / 2);

                const ctxPie = document.getElementById('pieChart').getContext('2d');
                if (pieChartInstance) pieChartInstance.destroy();

                // Clear table
                const tbody = document.getElementById('gradeTableBody');
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:3rem; color:var(--text-muted)"><i class="fas fa-filter" style="margin-bottom:1rem; font-size:2rem"></i><br>Silakan pilih <b>Tahun Akademik</b> dan <b>Program Studi</b><br>kemudian klik <b>Terapkan</b></td></tr>';
                document.getElementById('tableCount').innerText = '';

                return;
            }

            loader.style.display = 'flex';
            loader.style.opacity = '1';

            try {
                // Build URL
                const urlObj = new URL('data_sebaran.php', window.location.href);
                // Append filters
                if (params) {
                    for (const [key, value] of params) {
                        urlObj.searchParams.append(key, value);
                    }
                }
                // Append cache buster
                urlObj.searchParams.append('_', new Date().getTime());

                // Add 300s (5 min) timeout
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 300000);

                const response = await fetch(urlObj.toString(), { signal: controller.signal });
                clearTimeout(timeoutId);

                const text = await response.text();

                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    loader.style.display = 'none';
                    console.error('Invalid JSON response:', text);
                    alert('Error System (Invalid JSON): ' + text.substring(0, 200));
                    return;
                }

                if (result.error) {
                    loader.style.display = 'none';
                    alert('Error Server: ' + result.error);
                    return;
                }

                lastDashboardData = result;

                updateStats(result);
                renderCharts(result.distribution);
                renderTable(result.recent_data);

                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 500);

            } catch (error) {
                loader.style.display = 'none';
                console.error('Error fetching data:', error);
                const msg = error.name === 'AbortError' ? 'Koneksi timeout (5 menit). Silakan periksa koneksi atau filter data.' : error.message;
                alert('Gagal mengambil data: ' + msg);
            }
        }

        function applyFilters() {
            const params = getFilterParams();
            initDashboard(params);
        }

        function downloadCsv() {
            const params = getFilterParams();
            params.append('action', 'download');
            window.location.href = 'data_sebaran.php?' + params.toString();
        }

        function updateStats(data) {
            document.getElementById('semesterVal').innerText = data.semester;
            document.getElementById('totalVal').innerText = new Intl.NumberFormat('id-ID').format(data.total_grades);
            document.getElementById('passRateVal').innerText = data.passed_percentage + '%';

            // Find dominance
            if (data.distribution.length > 0) {
                // Sort to find max
                const sorted = [...data.distribution].sort((a, b) => b.jumlah - a.jumlah);
                const distinct = sorted[0];
                document.getElementById('topGradeVal').innerText = 'Grade ' + distinct.nilai_huruf;
                document.getElementById('topGradeDesc').innerText = new Intl.NumberFormat('id-ID').format(distinct.jumlah) + ' Mahasiswa';
            } else {
                document.getElementById('topGradeVal').innerText = '-';
                document.getElementById('topGradeDesc').innerText = '0 Mahasiswa';
            }
        }

        function renderTable(rows) {
            const tbody = document.getElementById('gradeTableBody');
            const countLabel = document.getElementById('tableCount');
            tbody.innerHTML = '';

            if (!rows || rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-gray)">Tidak ada data ditemukan</td></tr>';
                countLabel.innerText = '0 data';
                return;
            }

            countLabel.innerText = `Menampilkan ${rows.length} data terbaru`;

            rows.forEach(row => {
                const tr = document.createElement('tr');

                // Color badge for grade
                let badgeClass = 'badge';
                if (row.nilai_huruf === 'A') badgeClass = 'badge badge-a';
                else if (row.nilai_huruf === 'B') badgeClass = 'badge badge-b';
                else if (row.nilai_huruf === 'C') badgeClass = 'badge badge-c';
                else if (row.nilai_huruf === 'D') badgeClass = 'badge badge-d';
                else if (row.nilai_huruf === 'E') badgeClass = 'badge badge-e';

                tr.innerHTML = `
                    <td>${row.nipd || '-'}</td>
                    <td>${row.nm_pd || '-'}</td>
                    <td>${row.prodi || '-'}</td>
                    <td>${row.mata_kuliah || '-'}</td>
                    <td>${row.kelas || '-'}</td>
                    <td><span class="${badgeClass}">${row.nilai_huruf || '-'}</span></td>
                    <td>${row.nilai_indeks || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        function renderCharts(distData) {
            const labels = distData.map(d => d.nilai_huruf);
            const values = distData.map(d => d.jumlah);

            // Colors for Grades usually: A(Green), B(Blue), C(Yellow), D(Orange), E(Red)
            const getBgColor = (letter) => {
                const map = {
                    'A': 'rgba(16, 185, 129, 0.7)',
                    'B': 'rgba(59, 130, 246, 0.7)',
                    'C': 'rgba(245, 158, 11, 0.7)',
                    'D': 'rgba(249, 115, 22, 0.7)',
                    'E': 'rgba(239, 68, 68, 0.7)'
                };
                return map[letter] || 'rgba(148, 163, 184, 0.7)';
            };

            const bgColors = labels.map(l => getBgColor(l));

            const total = values.reduce((a, b) => a + Number(b), 0);

            // Get current theme colors from computed style
            const style = getComputedStyle(document.body);
            const gridColor = style.getPropertyValue('--border-color').trim();
            const tickColor = style.getPropertyValue('--text-muted').trim();

            const barChartConfig = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Jumlah Mahasiswa',
                        data: values,
                        backgroundColor: bgColors,
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: style.getPropertyValue('--bg-card').trim(),
                            titleColor: style.getPropertyValue('--text-main').trim(),
                            bodyColor: style.getPropertyValue('--text-main').trim(),
                            borderColor: gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const val = context.raw;
                                    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
                                    return label + percentage + ' (' + val + ' Mahasiswa)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: { color: tickColor }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: tickColor }
                        }
                    }
                }
            };

            // Bar Chart
            const ctxBar = document.getElementById('barChart').getContext('2d');
            if (barChartInstance) barChartInstance.destroy();
            barChartInstance = new Chart(ctxBar, barChartConfig);

            // Pie Chart
            const ctxPie = document.getElementById('pieChart').getContext('2d');
            if (pieChartInstance) pieChartInstance.destroy();

            const pieChartConfig = {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: tickColor, padding: 20 }
                        },
                        tooltip: {
                            backgroundColor: style.getPropertyValue('--bg-card').trim(),
                            titleColor: style.getPropertyValue('--text-main').trim(),
                            bodyColor: style.getPropertyValue('--text-main').trim(),
                            borderColor: gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const val = context.raw;
                                    const percentage = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
                                    return label + ': ' + percentage + ' (' + val + ' Mhs)';
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            };

            pieChartInstance = new Chart(ctxPie, pieChartConfig);
        }

        // Run
        document.addEventListener('DOMContentLoaded', () => {
            console.log("Dashboard Loaded: v1.8 (Chart Percentage)");
            setupDynamicFilters();
            initFilters().catch(e => console.error("InitFilters failed", e));
            // Do not auto-load data. Wait for user to click Apply.
            initDashboard(null);
        });

        // --- Theme Logic ---
        function initTheme() {
            const savedTheme = localStorage.getItem('monitoring_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        }

        function toggleTheme() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('monitoring_theme', next);
            updateThemeIcon(next);

            // Re-render charts immediately if data exists
            if (typeof lastDashboardData !== 'undefined' && lastDashboardData && lastDashboardData.distribution) {
                renderCharts(lastDashboardData.distribution);
            } else {
                // Update placeholder text color
                const ctxBar = document.getElementById('barChart').getContext('2d');
                if (!barChartInstance) {
                    ctxBar.clearRect(0, 0, ctxBar.canvas.width, ctxBar.canvas.height);
                    ctxBar.fillStyle = getComputedStyle(document.body).getPropertyValue('--text-muted');
                    ctxBar.textAlign = "center";
                    ctxBar.fillText("Silakan pilih Tahun Akademik & Prodi untuk menampilkan data", ctxBar.canvas.width / 2, ctxBar.canvas.height / 2);
                }
            }
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }
    </script>
</body>

</html>