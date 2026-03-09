<?php
/**
 * OBE Sync Dashboard
 * Sinkronisasi nilai dari SIOBE ke SIAKAD via api-obe.poltekindonusa.ac.id
 */
session_start();
$basePath = dirname(dirname(dirname(__DIR__)));
require_once $basePath . '/config/config.php';

// Auth check
// if (!isset($_SESSION['wsiaADMIN'])) { header('Location: /login.php'); exit; }

$currentYear = date('Y');
$currentMonth = date('n');
// Semester ganjil: Jul-Des, Genap: Jan-Jun
$defaultSemester = ($currentMonth >= 7) ? 1 : 2;
$defaultTahun = ($currentMonth >= 7)
    ? $currentYear . '/' . ($currentYear + 1)
    : ($currentYear - 1) . '/' . $currentYear;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OBE Grade Sync | BAAK Politeknik Indonusa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-hover: #4338ca;
            --success: #10b981;
            --success-light: #d1fae5;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --radius: 0.875rem;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); }

        .topbar { background: #fff; border-bottom: 1px solid var(--border); padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
        .topbar .logo { font-size: 1.25rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 0.5rem; }
        .topbar .breadcrumb { font-size: 0.8125rem; color: var(--muted); margin-left: auto; }

        .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1rem; }

        .page-header { margin-bottom: 1.75rem; }
        .page-header h1 { font-size: 1.75rem; font-weight: 700; }
        .page-header p { color: var(--muted); margin-top: 0.25rem; }

        .status-pill { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.3rem 0.875rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; cursor: pointer; }
        .status-online  { background: var(--success-light); color: #065f46; }
        .status-offline { background: var(--danger-light);  color: #991b1b; }
        .status-checking { background: #f0f9ff; color: #0369a1; }

        .dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; }

        .card { background: var(--card); border-radius: var(--radius); border: 1px solid var(--border); box-shadow: 0 1px 4px rgb(0 0 0 / 6%); padding: 1.75rem; }
        .card-title { font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; color: var(--text); }
        .card-title i { color: var(--primary); }

        label { display: block; font-size: 0.8125rem; font-weight: 500; color: var(--muted); margin-bottom: 0.375rem; }
        select, input[type=text], input[type=number] {
            width: 100%; padding: 0.625rem 0.875rem; border: 1px solid var(--border);
            border-radius: 0.5rem; font-size: 0.875rem; outline: none;
            transition: border-color 0.2s;
        }
        select:focus, input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
        .form-row { margin-bottom: 1rem; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.7rem 1.5rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: none; transition: all 0.18s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover:not(:disabled) { background: var(--primary-hover); }
        .btn-secondary { background: #f1f5f9; color: var(--text); }
        .btn-secondary:hover:not(:disabled) { background: #e2e8f0; }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-block { width: 100%; }
        .btn-actions { display: flex; gap: 0.75rem; margin-top: 1.5rem; }

        .progress-wrap { display: none; margin-top: 1.25rem; }
        .progress-label { display: flex; justify-content: space-between; font-size: 0.8125rem; margin-bottom: 0.375rem; }
        .progress-bar-bg { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; }
        .progress-bar { height: 100%; background: var(--primary); border-radius: 4px; transition: width 0.4s ease; width: 0%; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
        .stat-box { background: var(--bg); border-radius: 0.5rem; padding: 0.875rem; text-align: center; }
        .stat-box .val { font-size: 1.5rem; font-weight: 700; }
        .stat-box .lbl { font-size: 0.7rem; color: var(--muted); margin-top: 0.25rem; text-transform: uppercase; letter-spacing: .05em; }
        .stat-updated { color: var(--success); }
        .stat-skipped { color: var(--warning); }
        .stat-error   { color: var(--danger); }

        .log-box { background: #0f172a; color: #94a3b8; font-family: 'Cascadia Code', 'Fira Code', monospace; font-size: 0.75rem; padding: 1.25rem; border-radius: 0.625rem; height: 240px; overflow-y: auto; line-height: 1.7; }
        .log-ok     { color: #34d399; }
        .log-err    { color: #f87171; }
        .log-warn   { color: #fbbf24; }
        .log-dim    { color: #475569; }
        .log-time   { color: #475569; margin-right: 0.5rem; }

        .kelas-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; margin-top: 0.75rem; }
        .kelas-table th { background: var(--bg); padding: 0.5rem 0.75rem; text-align: left; color: var(--muted); font-weight: 500; border-bottom: 1px solid var(--border); }
        .kelas-table td { padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; }
        .kelas-table tr:hover td { background: #f8fafc; }

        .info-bar { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 0.75rem 1rem; color: #1d4ed8; }

        .footer { text-align: center; color: var(--muted); font-size: 0.8rem; margin-top: 3rem; padding-bottom: 2rem; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="logo"><i class="fas fa-circle-nodes"></i> SIAKAD BAAK</div>
    <div class="breadcrumb">BAAK > Sinkronisasi > OBE Grades</div>
    <div id="connStatus" class="status-pill status-checking" onclick="checkConn()" title="Klik untuk cek ulang koneksi">
        <span class="dot"></span> Memeriksa koneksi...
    </div>
</div>

<div class="container">
    <div class="page-header">
        <h1>OBE Grade Sync</h1>
        <p>Tarik dan sinkronkan nilai akhir dari <strong>SIOBE</strong> → <strong>SIAKAD</strong> via <code>api-obe.poltekindonusa.ac.id</code></p>
    </div>

    <div class="info-bar" style="margin-bottom:1.5rem;">
        <i class="fas fa-info-circle"></i>
        <span>Nilai diambil dari tabel <code>nilai_makul</code> di SIOBE, dicocokkan berdasarkan <strong>NIM</strong> + <strong>Kode Mata Kuliah</strong>, lalu di-update ke <code>wsia_nilai</code> di SIAKAD.</span>
    </div>

    <div class="grid-2">
        <!-- Kontrol Sync -->
        <div class="card">
            <div class="card-title"><i class="fas fa-bolt"></i> Kontrol Sinkronisasi</div>

            <div class="form-row">
                <label>Tahun Ajaran (SIOBE)</label>
                <input type="text" id="tahun" placeholder="contoh: 2024/2025" value="<?= htmlspecialchars($defaultTahun) ?>">
            </div>
            <div class="form-row-2">
                <div>
                    <label>Semester</label>
                    <select id="semester">
                        <option value="0">-- Semua --</option>
                        <option value="1" <?= $defaultSemester == 1 ? 'selected' : '' ?>>Ganjil (1)</option>
                        <option value="2" <?= $defaultSemester == 2 ? 'selected' : '' ?>>Genap (2)</option>
                    </select>
                </div>
                <div>
                    <label>Max Data</label>
                    <input type="number" id="limit" value="500" min="1" max="2000">
                </div>
            </div>
            <div class="form-row">
                <label>Filter Kode Mata Kuliah <span style="color:var(--muted)">(opsional)</span></label>
                <input type="text" id="kode_makul" placeholder="contoh: MPB1212">
            </div>
            <div class="form-row">
                <label>Filter Kelas <span style="color:var(--muted)">(opsional)</span></label>
                <input type="text" id="kelas" placeholder="contoh: 22A">
            </div>

            <div class="btn-actions">
                <button id="btnSync" class="btn btn-primary" style="flex:2;" onclick="doSync()">
                    <i class="fas fa-sync"></i> Mulai Sync
                </button>
                <button class="btn btn-secondary" style="flex:1;" onclick="loadKelas()">
                    <i class="fas fa-list"></i> Preview Kelas
                </button>
            </div>

            <div class="progress-wrap" id="progressWrap">
                <div class="progress-label">
                    <span id="progressText">Memproses...</span>
                    <span id="progressPct">0%</span>
                </div>
                <div class="progress-bar-bg"><div class="progress-bar" id="progressBar"></div></div>
            </div>

            <!-- Ringkasan hasil -->
            <div id="statsWrap" style="display:none; margin-top:1.25rem;">
                <div class="stats-grid">
                    <div class="stat-box"><div class="val stat-updated" id="sUpdated">0</div><div class="lbl">Updated</div></div>
                    <div class="stat-box"><div class="val stat-skipped" id="sSkipped">0</div><div class="lbl">Skipped</div></div>
                    <div class="stat-box"><div class="val stat-error"   id="sErrors">0</div><div class="lbl">Error</div></div>
                </div>
            </div>
        </div>

        <!-- Log konsol -->
        <div class="card">
            <div class="card-title" style="justify-content:space-between;">
                <span><i class="fas fa-terminal"></i> Activity Log</span>
                <button onclick="clearLog()" style="background:none;border:none;cursor:pointer;color:var(--muted);font-size:0.75rem;">
                    <i class="fas fa-eraser"></i> Clear
                </button>
            </div>
            <div id="logBox" class="log-box">
                <span class="log-dim">Terminal siap. Klik "Mulai Sync" atau "Preview Kelas" untuk memulai.</span>
            </div>
        </div>
    </div>

    <!-- Tabel Kelas OBE (preview) -->
    <div class="card" id="kelasCard" style="display:none; margin-top:1.5rem;">
        <div class="card-title"><i class="fas fa-table"></i> Daftar Kelas di SIOBE</div>
        <div style="overflow-x:auto;">
            <table class="kelas-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kelas</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Semester</th>
                        <th>Tahun Ajaran</th>
                        <th>Jml Mhs</th>
                    </tr>
                </thead>
                <tbody id="kelasTbody"></tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        &copy; <?= date('Y') ?> Politeknik Indonusa Surakarta &nbsp;·&nbsp; IT Infrastructure
    </div>
</div>

<script>
const API = 'api.php';

const logBox = document.getElementById('logBox');
const btnSync = document.getElementById('btnSync');

function ts() {
    return new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
}
function log(msg, cls='') {
    const d = document.createElement('div');
    d.innerHTML = `<span class="log-time">[${ts()}]</span><span class="${cls}">${msg}</span>`;
    logBox.appendChild(d);
    logBox.scrollTop = logBox.scrollHeight;
}
function clearLog() {
    logBox.innerHTML = '<span class="log-dim">Log dibersihkan.</span>';
}
function setProgress(pct, text) {
    document.getElementById('progressBar').style.width = pct + '%';
    document.getElementById('progressPct').innerText = pct + '%';
    document.getElementById('progressText').innerText = text;
}

// Cek koneksi ke api-obe
async function checkConn() {
    const el = document.getElementById('connStatus');
    el.className = 'status-pill status-checking';
    el.innerHTML = '<span class="dot"></span> Memeriksa...';

    try {
        const fd = new FormData();
        fd.append('action','test_connection');
        const r = await fetch(API, {method:'POST', body: fd});
        const d = await r.json();

        if (d.success) {
            el.className = 'status-pill status-online';
            el.innerHTML = '<span class="dot"></span> API OBE Terhubung';
            log('Koneksi ke api-obe.poltekindonusa.ac.id berhasil ✓', 'log-ok');
        } else {
            throw new Error(d.message || d.error || 'Gagal');
        }
    } catch(e) {
        document.getElementById('connStatus').className = 'status-pill status-offline';
        document.getElementById('connStatus').innerHTML = '<span class="dot"></span> Tidak terhubung';
        log('Koneksi gagal: ' + e.message, 'log-err');
    }
}
checkConn(); // Auto-cek saat load

// Preview daftar kelas dari SIOBE
async function loadKelas() {
    log('Memuat daftar kelas dari SIOBE...');
    const fd = new FormData();
    fd.append('action','get_kelas');
    fd.append('tahun', document.getElementById('tahun').value);
    fd.append('semester', document.getElementById('semester').value);

    try {
        const r = await fetch(API, {method:'POST', body:fd});
        const d = await r.json();

        if (!d.success) { log('Gagal: ' + (d.error || 'Unknown'), 'log-err'); return; }

        const results = d.data?.results ?? [];
        const tbody = document.getElementById('kelasTbody');
        tbody.innerHTML = '';

        if (!results.length) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:var(--muted)">Tidak ada data ditemukan</td></tr>';
        } else {
            results.forEach((r, i) => {
                tbody.innerHTML += `<tr>
                    <td>${i+1}</td>
                    <td><strong>${r.kelas}</strong></td>
                    <td><code>${r.kode_makul}</code></td>
                    <td>${r.nama_makul}</td>
                    <td>${r.semester_mengajar}</td>
                    <td>${r.tahun_ajaran}</td>
                    <td>${r.jumlah_mahasiswa}</td>
                </tr>`;
            });
        }

        document.getElementById('kelasCard').style.display = 'block';
        log(`Ditemukan ${results.length} kelas di SIOBE.`, 'log-ok');
    } catch(e) {
        log('Error: ' + e.message, 'log-err');
    }
}

// Sinkronisasi nilai
async function doSync() {
    const tahun     = document.getElementById('tahun').value.trim();
    const semester  = document.getElementById('semester').value;
    const kodeMakul = document.getElementById('kode_makul').value.trim();
    const kelas     = document.getElementById('kelas').value.trim();
    const limit     = document.getElementById('limit').value;

    const confirmMsg = `Sinkronisasi nilai dari SIOBE ke SIAKAD?\n\nTahun: ${tahun||'Semua'}\nSemester: ${semester=='0'?'Semua':semester}\nLimit: ${limit} data\n\nData wsia_nilai yang cocok akan di-update!`;
    if (!confirm(confirmMsg)) return;

    btnSync.disabled = true;
    document.getElementById('progressWrap').style.display = 'block';
    setProgress(5, 'Menghubungi API OBE...');
    log(`Mulai sync → Tahun: ${tahun||'Semua'}, Semester: ${semester=='0'?'Semua':semester}`, 'log-warn');

    const fd = new FormData();
    fd.append('action',     'sync_grades');
    fd.append('tahun',      tahun);
    fd.append('semester',   semester);
    fd.append('kode_makul', kodeMakul);
    fd.append('kelas',      kelas);
    fd.append('limit',      limit);

    try {
        setProgress(30, 'Mengambil data nilai dari SIOBE...');
        const r = await fetch(API, {method:'POST', body:fd});
        setProgress(70, 'Memproses dan meng-update SIAKAD...');
        const d = await r.json();
        setProgress(100, 'Selesai!');

        if (!d.success) { log('Gagal: ' + (d.error || 'Unknown error'), 'log-err'); return; }

        const data = d.data;
        log(`Data diambil dari SIOBE: ${data.total_fetched} record.`);
        log(`Berhasil update di SIAKAD: ${data.total_updated} record.`, 'log-ok');
        if (data.total_skipped) log(`Dilewati (tidak ditemukan di SIAKAD): ${data.total_skipped}`, 'log-warn');

        (data.messages||[]).forEach(m => log(m));
        (data.errors  ||[]).slice(0,10).forEach(e => log(e, 'log-err'));
        if ((data.errors||[]).length > 10) log('...dan ' + ((data.errors||[]).length - 10) + ' error lainnya.', 'log-err');

        // Update stats
        document.getElementById('statsWrap').style.display = 'block';
        document.getElementById('sUpdated').innerText = data.total_updated ?? 0;
        document.getElementById('sSkipped').innerText = data.total_skipped ?? 0;
        document.getElementById('sErrors') .innerText = (data.errors||[]).length;

    } catch(e) {
        setProgress(0, 'Gagal');
        log('Exception: ' + e.message, 'log-err');
    } finally {
        btnSync.disabled = false;
        setTimeout(() => {
            document.getElementById('progressWrap').style.display = 'none';
            setProgress(0, '');
        }, 5000);
    }
}
</script>
</body>
</html>
