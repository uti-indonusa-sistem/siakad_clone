<?php require_once 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Dokumen Mahasiswa (KRS/KHS)</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
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

        .back-btn {
            color: var(--text-muted);
            text-decoration: none;
            margin-right: 1rem;
            font-size: 0.9rem;
        }

        .back-btn:hover {
            color: var(--text-main);
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

        /* Filter Card */
        .filter-card {
            background-color: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            border: 1px solid var(--border-hover);
            color: var(--text-main);
            font-family: inherit;
        }

        .btn-primary,
        .btn-secondary,
        .btn-zip {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #334155;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-zip {
            background-color: var(--success);
            color: white;
        }

        .btn-zip:hover {
            background-color: #059669;
        }

        /* Table */
        .card {
            background-color: var(--bg-card);
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .table-responsive {
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
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--text-muted);
            font-weight: 500;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
            vertical-align: middle;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 0.4rem;
        }

        /* Checkbox */
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
        }
    </style>
</head>

<body>

    <div id="loader" class="loader-overlay">
        <i class="fas fa-circle-notch fa-spin fa-3x" style="color:var(--primary)"></i>
        <p style="margin-top:1rem;color:var(--text-muted)" id="loaderText">Memproses...</p>
    </div>

    <div class="container">
        <header>
            <div class="brand-section">
                <h1>Executive Dashboard</h1>
                <p>Monitoring Sebaran Nilai Mahasiswa</p>
            </div>

            <nav class="main-nav">
                <a href="dashboard.php" class="nav-link">Sebaran Nilai</a>
                <a href="cetak_dokumen.php" class="nav-link active">Cetak Dokumen</a>
            </nav>

            <div>
                <span style="color:var(--text-muted); margin-right: 1rem;">
                    Periode: <span id="activeSemesterLabel" style="color:var(--success); font-weight:bold">-</span>
                </span>

                <button onclick="toggleTheme()" class="logout-btn"
                    style="color:var(--text-muted); border-color:var(--border-color); background:transparent; margin-left:1rem; cursor:pointer; padding:0.2rem 0.5rem; border:1px solid var(--border-color); border-radius:0.3rem"
                    title="Toggle Theme">
                    <i class="fas fa-sun" id="themeIcon"></i>
                </button>

                <span style="color:var(--text-muted); font-size:0.9rem;">
                    <i class="fas fa-circle"
                        style="color:var(--success); font-size:0.6rem; margin-right:0.5rem; margin-left:1rem"></i>
                    <?php echo isset($_SESSION['monitoring_username']) ? ucfirst($_SESSION['monitoring_username']) : 'Admin'; ?>
                </span>
                <a href="logout.php"
                    style="margin-left:1rem;color:var(--danger);text-decoration:none;font-size:0.9rem"><i
                        class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="filter-card">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Tahun Akademik (Dokumen)</label>
                    <select id="filterSemester" class="form-control">
                        <!-- Loaded via JS -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Program Studi</label>
                    <select id="filterProdi" class="form-control">
                        <option value="">-- Pilih Prodi --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Angkatan</label>
                    <select id="filterAngkatan" class="form-control">
                        <option value="">-- Pilih Angkatan --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kelas</label>
                    <select id="filterKelas" class="form-control">
                        <option value="">-- Semua Kelas (Opsional) --</option>
                    </select>
                </div>
                <div class="form-group" style="display:flex; gap:0.5rem; align-items:end">
                    <button onclick="loadStudents()" class="btn-primary" style="width:100%"><i
                            class="fas fa-search"></i> Cari Mahasiswa</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div
                style="padding:1rem; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center">
                <h3 style="margin:0; font-size:1.1rem">Daftar Mahasiswa <span id="studentCount"
                        style="color:var(--text-muted); font-size:0.9rem; font-weight:normal">(0)</span></h3>
                <div class="action-btns">
                    <button onclick="downloadZip('krs')" class="btn-zip btn-sm" id="btnZipKrs" disabled>
                        <i class="fas fa-file-pdf"></i> Zip KRS
                    </button>
                    <button onclick="downloadZip('khs')" class="btn-zip btn-sm"
                        style="background-color: var(--warning);" id="btnZipKhs" disabled
                        title="Khusus untuk KHS, pastikan nilai sudah dipublish">
                        <i class="fas fa-file-pdf"></i> Zip KHS
                    </button>
                    <button onclick="downloadZip('uts')" class="btn-zip btn-sm"
                        style="background-color: var(--primary);" id="btnZipUts" disabled>
                        <i class="fas fa-file-pdf"></i> Batch PDF UTS
                    </button>
                    <button onclick="downloadZip('uas')" class="btn-zip btn-sm"
                        style="background-color: var(--primary-dark);" id="btnZipUas" disabled>
                        <i class="fas fa-file-pdf"></i> Batch PDF UAS
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:50px"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Prodi</th>
                            <th>Angkatan</th>
                            <th>Status (Smt Ini)</th>
                            <th style="text-align:right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <tr>
                            <td colspan="7" style="text-align:center; padding:3rem; color:var(--text-muted)">Silakan
                                pilih filter dan klik Cari Mahasiswa</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let activeSemesterId = '';

        document.addEventListener('DOMContentLoaded', () => {
            initFilters();
            initTheme();

            // Dynamic filter listeners
            document.getElementById('filterSemester').addEventListener('change', loadKelas);
            document.getElementById('filterProdi').addEventListener('change', loadKelas);
            document.getElementById('filterAngkatan').addEventListener('change', loadKelas);
        });

        // Theme Logic
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
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (theme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        async function initFilters() {
            try {
                const response = await fetch('data_dokumen.php?action=options');
                const data = await response.json();

                // Semester
                const semSelect = document.getElementById('filterSemester');
                data.semester.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.text = s.text;
                    semSelect.appendChild(opt);
                });
                activeSemesterId = data.semester[0].id; // Default most recent
                semSelect.value = activeSemesterId;
                document.getElementById('activeSemesterLabel').innerText = data.semester[0].text;

                // Prodi
                const prodiSelect = document.getElementById('filterProdi');
                data.prodi.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.id;
                    opt.text = p.text;
                    prodiSelect.appendChild(opt);
                });

                // Angkatan
                const angkatanSelect = document.getElementById('filterAngkatan');
                data.angkatan.forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = a.id;
                    opt.text = a.text;
                    angkatanSelect.appendChild(opt);
                });

            } catch (e) {
                console.error("Init failed", e);
                alert("Gagal memuat opsi filter");
            }
        }

        async function loadKelas() {
            const sem = document.getElementById('filterSemester').value;
            const prodi = document.getElementById('filterProdi').value;
            const angkatan = document.getElementById('filterAngkatan').value;
            const kelasSelect = document.getElementById('filterKelas');

            if (!sem || !prodi) return;

            try {
                const url = `data_dokumen.php?action=get_kelas&semester=${sem}&prodi=${prodi}&angkatan=${angkatan}`;
                const resp = await fetch(url);
                const data = await resp.json();

                kelasSelect.innerHTML = '<option value="">-- Semua Kelas --</option>';
                data.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k.id;
                    opt.text = k.text;
                    kelasSelect.appendChild(opt);
                });
            } catch (e) {
                console.error("Load kelas failed", e);
            }
        }

        async function loadStudents() {
            const sem = document.getElementById('filterSemester').value;
            const prodi = document.getElementById('filterProdi').value;
            const angkatan = document.getElementById('filterAngkatan').value;
            const kelas = document.getElementById('filterKelas').value;

            if (!prodi || !angkatan) {
                alert("Harap pilih Program Studi dan Angkatan terlebih dahulu");
                return;
            }

            const loader = document.getElementById('loader');
            loader.style.display = 'flex';
            document.getElementById('loaderText').innerText = 'Mengambil data mahasiswa...';

            try {
                let url = `data_dokumen.php?action=list_students&semester=${sem}&prodi=${prodi}&angkatan=${angkatan}`;
                if (kelas) url += `&kelas=${encodeURIComponent(kelas)}`;
 const resp = await fetch(url);
                const data = await resp.json();

                renderTable(data);
            } catch (e) {
                console.error(e);
                alert("Gagal mengambil data mahasiswa");
            } finally {
                loader.style.display = 'none';
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('studentTableBody');
            const countLabel = document.getElementById('studentCount');
            const btnZipKrs = document.getElementById('btnZipKrs');
            const btnZipKhs = document.getElementById('btnZipKhs');

            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:2rem; color:var(--text-muted)">Tidak ada mahasiswa ditemukan untuk kriteria ini.</td></tr>';
                countLabel.innerText = '(0)';
                btnZipKrs.disabled = true;
                btnZipKhs.disabled = true;
                return;
            }

            countLabel.innerText = `(${data.length} Mahasiswa)`;
            btnZipKrs.disabled = false;
            btnZipKhs.disabled = false;
            document.getElementById('btnZipUts').disabled = false;
            document.getElementById('btnZipUas').disabled = false;

            data.forEach(m => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="checkbox" name="ids[]" value="${m.xid_reg_pd}" class="row-checkbox"></td>
                    <td>${m.nipd}</td>
                    <td>${m.nm_pd}</td>
                    <td>${m.prodi}</td>
                    <td>${m.angkatan}</td>
                    <td><span class="badge" style="background:rgba(16,185,129,0.1); color:#34d399; padding:0.2rem 0.6rem; border-radius:1rem; font-size:0.75rem">Aktif</span></td>
                    <td style="text-align:right">
                         <div style="display:flex; gap:0.3rem; flex-wrap:wrap; justify-content:flex-end">
                            <button onclick="downloadSingle('${m.xid_reg_pd}', 'krs')" class="btn-primary btn-sm" title="Download KRS"><i class="fas fa-file"></i> KRS</button>
                            <button onclick="downloadSingle('${m.xid_reg_pd}', 'khs')" class="btn-secondary btn-sm" title="Download KHS"><i class="fas fa-list"></i> KHS</button>
                            <button onclick="downloadSingle('${m.xid_reg_pd}', 'uts')" class="btn-sm" style="background:#4f46e5; color:white" title="Download Kartu UTS"><i class="fas fa-id-card"></i> UTS</button>
                            <button onclick="downloadSingle('${m.xid_reg_pd}', 'uas')" class="btn-sm" style="background:#4338ca; color:white" title="Download Kartu UAS"><i class="fas fa-id-card"></i> UAS</button>
                         </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            document.getElementById('checkAll').checked = false;
        }

        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function downloadSingle(id, type) {
            const sem = document.getElementById('filterSemester').value;
            window.open(`data_dokumen.php?action=download_single&type=${type}&id=${id}&semester=${sem}`, '_blank');
        }

        async function downloadZip(type) {
            const sem = document.getElementById('filterSemester').value;
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => cb.value);

            if (ids.length === 0) {
                alert("Pilih minimal satu mahasiswa untuk di-download.");
                return;
            }

            if (ids.length > 50) {
                if (!confirm(`Anda akan mendownload ${ids.length} dokumen. Ini mungkin butuh waktu. Lanjutkan?`)) return;
            }

            const loader = document.getElementById('loader');
            loader.style.display = 'flex';
            document.getElementById('loaderText').innerText = 'Memproses ZIP... (Mungkin memakan waktu)';

            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'data_dokumen.php?action=download_zip';

                const inputType = document.createElement('input');
                inputType.type = 'hidden';
                inputType.name = 'type';
                inputType.value = type;
                form.appendChild(inputType);

                const inputSem = document.createElement('input');
                inputSem.type = 'hidden';
                inputSem.name = 'semester';
                inputSem.value = sem;
                form.appendChild(inputSem);

                const inputIds = document.createElement('input');
                inputIds.type = 'hidden';
                inputIds.name = 'ids';
                inputIds.value = JSON.stringify(ids);
                form.appendChild(inputIds);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);

                // Hide loader after a delay since we can't track download progress easily
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 3000);

            } catch (e) {
                console.error(e);
                loader.style.display = 'none';
                alert("Gagal memproses download.");
            }
        }
    </script>
</body>

</html>