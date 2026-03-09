<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Akses Pengelola Akun | Portal Administrasi Learning</title>
    <script type="text/javascript" src="../lib/jquery-1.7.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="../lib/webix.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="../lib/webix.js" type="text/javascript" charset="utf-8"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body,
        .webix_view {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .bg_gradient {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
        }

        .card_manager {
            background: #ffffff !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1) !important;
        }

        .login_box {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            border-radius: 20px !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
        }

        .status_badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
        }

        .status_aktif {
            background: #e6fffa;
            color: #047481;
            border: 1px solid #b2f5ea;
        }

        .status_belum {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .webix_secondary button {
            background: #edf2f7 !important;
            color: #4a5568 !important;
            border-radius: 8px !important;
        }

        .webix_primary button {
            background: #1e3a8a !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
        }

        .header_bar {
            background: #ffffff !important;
            border-bottom: 1px solid #edf2f7 !important;
        }

        .label_bold {
            font-weight: 700 !important;
            color: #2d3748 !important;
        }

        .sync_log {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #1a202c;
            color: #48bb78;
            padding: 15px;
            border-radius: 8px;
            height: 300px;
            overflow-y: scroll;
        }
    </style>
</head>

<body class="<?= !isset($_SESSION['moodle_manager_logged_in']) ? 'bg_gradient' : '' ?>">
    <script type="text/javascript">
        webix.ready(function () {
            <?php if (!isset($_SESSION['moodle_manager_logged_in'])): ?>
                // --- LOGIN PAGE ---
                webix.ui({
                    rows: [
                        {},
                        {
                            cols: [
                                {},
                                {
                                    view: "form",
                                    id: "loginForm",
                                    width: 400,
                                    padding: 40,
                                    css: "login_box",
                                    elements: [
                                        { template: "<center><img src='../../gambar/logo.png' height='50'><h2 style='margin-top:10px; color:#1e3a8a;'>Pengelola Akun</h2><p style='color:#718096; font-size:14px;'>Portal Akses Pengelola Akun Learning</p></center>", height: 160, borderless: true },
                                        { view: "text", name: "username", label: "Username", labelPosition: "top", placeholder: "admin", required: true },
                                        { view: "text", name: "password", label: "Password", labelPosition: "top", type: "password", placeholder: "••••••••", required: true },
                                        { height: 20 },
                                        { view: "button", value: "MASUK SYSTEM", css: "webix_primary", height: 50, click: doLogin },
                                        { template: "<center style='font-size:12px; color:#a0aec0; margin-top:20px;'>&copy; 2024 IT Support Poltek Indonusa</center>", height: 40, borderless: true }
                                    ]
                                },
                                {}
                            ]
                        },
                        {}
                    ]
                });

                function doLogin() {
                    if ($$("loginForm").validate()) {
                        var values = $$("loginForm").getValues();
                        webix.ajax().post("api.php?action=login", values, function (text) {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                location.reload();
                            } else {
                                webix.message({ type: "error", text: res.message });
                            }
                        }).fail(function (xhr) {
                            var res = JSON.parse(xhr.responseText);
                            webix.message({ type: "error", text: res.message });
                        });
                    }
                }

            <?php else: ?>
                // --- DASHBOARD MANAGER ---
                webix.ui({
                    rows: [
                        {
                            view: "toolbar",
                            css: "header_bar",
                            padding: 10,
                            cols: [
                                { width: 10 },
                                { template: "<img src='../../gambar/logo.png' height='30'>", width: 40, borderless: true },
                                { view: "label", label: "AKSES PENGELOLA AKUN LEARNING", css: "label_bold" },
                                {},
                                { view: "label", label: "<i class='fa fa-user-circle'></i> <?= $_SESSION['moodle_manager_user'] ?>", width: 150 },
                                {
                                    view: "button", label: "Keluar", width: 100, css: "webix_secondary", click: function () {
                                        webix.ajax().get("api.php?action=logout", function () { location.reload(); });
                                    }
                                }
                            ]
                        },
                        {
                            view: "tabview",
                            tabbar: {
                                optionWidth: 200
                            },
                            cells: [
                                {
                                    header: "<i class='fa fa-user'></i> Kelola Individu",
                                    id: "tab_individu",
                                    cols: [
                                        {
                                            width: 350,
                                            padding: 20,
                                            rows: [
                                                { view: "search", id: "searchMhs", placeholder: "Cari NIM atau Nama...", label: "Cari Mahasiswa", labelPosition: "top" },
                                                {
                                                    view: "list",
                                                    id: "listMhs",
                                                    select: true,
                                                    template: "<b>#nim#</b><br><span style='font-size:12px; color:#718096;'>#nama#</span>",
                                                    type: { height: 65 },
                                                    on: {
                                                        onAfterSelect: function (id) {
                                                            loadDetails(this.getItem(id).nim);
                                                        }
                                                    }
                                                }
                                            ]
                                        },
                                        {
                                            padding: 20,
                                            rows: [
                                                {
                                                    id: "detailView",
                                                    hidden: true,
                                                    rows: [
                                                        {
                                                            view: "form",
                                                            id: "formDetail",
                                                            css: "card_manager",
                                                            padding: 30,
                                                            elements: [
                                                                {
                                                                    cols: [
                                                                        { template: "<div style='font-size:20px; font-weight:700;' id='det_nama'>Nama Mahasiswa</div>", borderless: true },
                                                                        { id: "det_status", template: "", width: 150, borderless: true }
                                                                    ]
                                                                },
                                                                { template: "<hr style='border:0; border-top:1px solid #edf2f7; margin:10px 0;'>", height: 20, borderless: true },
                                                                {
                                                                    cols: [
                                                                        { view: "text", label: "NIM", id: "det_nim", readonly: true, labelPosition: "top" },
                                                                        { width: 20 },
                                                                        { view: "text", label: "Program Studi", id: "det_prodi", readonly: true, labelPosition: "top" }
                                                                    ]
                                                                },
                                                                { height: 20 },
                                                                { template: "<div class='label_bold' style='margin-bottom:10px;'><i class='fa fa-edit'></i> Update Akun Moodle</div>", height: 35, borderless: true },
                                                                {
                                                                    cols: [
                                                                        { view: "text", name: "email", label: "Email Moodle", id: "edit_email", labelPosition: "top", placeholder: "Email baru" },
                                                                        { width: 20 },
                                                                        { view: "text", name: "password", label: "Password Moodle Baru", id: "edit_pass", labelPosition: "top", type: "password", placeholder: "Kosongkan jika tidak diubah" }
                                                                    ]
                                                                },
                                                                { template: "<div style='font-size:11px; color:#1e3a8a; font-style:italic;'>Password harus memenuhi syarat (8 char, Besar, Kecil, Angka, Simbol)</div>", height: 30, borderless: true },
                                                                { height: 20 },
                                                                {
                                                                    cols: [
                                                                        { view: "button", label: "FORCE SYNC DATA", css: "webix_secondary", width: 180, click: doSync },
                                                                        {},
                                                                        { view: "button", label: "SIMPAN PERUBAHAN", css: "webix_primary", width: 220, click: doUpdate }
                                                                    ]
                                                                }
                                                            ]
                                                        },
                                                        {}
                                                    ]
                                                },
                                                { id: "emptyView", template: "<center style='margin-top:100px; color:#a0aec0;'><i class='fa fa-search fa-4x'></i><br><h3>Pilih mahasiswa untuk dikelola</h3></center>", borderless: true }
                                            ]
                                        }
                                    ]
                                },
                                {
                                    header: "<i class='fa fa-refresh'></i> Sinkronisasi Massal",
                                    id: "tab_sinkron",
                                    padding: 30,
                                    rows: [
                                        {
                                            view: "form",
                                            id: "syncForm",
                                            css: "card_manager",
                                            padding: 30,
                                            gravity: 1,
                                            elements: [
                                                { template: "<h2 style='margin-bottom:5px; color:#1e3a8a;'><i class='fa fa-refresh'></i> Sinkronisasi Massal SIAKAD ke Moodle</h2><p style='color:#718096; line-height:1.5;'>Pilih jenis data yang akan disinkronkan ke Moodle dibawah ini.</p>", height: 120, borderless: true },
                                                {
                                                    view: "segmented", id: "sync_type", value: "mahasiswa", options: [
                                                        { id: "mahasiswa", value: "Mahasiswa" },
                                                        { id: "dosen", value: "Dosen" },
                                                        { id: "courses", value: "Mata Kuliah" },
                                                        { id: "enrolments", value: "Peserta Kelas" }
                                                    ],
                                                    height: 50,
                                                    on: {
                                                        onChange: function (id) {
                                                            $$("filter_angkatan").hide();
                                                            $$("filter_semester").hide();
                                                            $$("lbl_info_dosen").hide();

                                                            if (id == 'mahasiswa') $$("filter_angkatan").show();
                                                            else if (id == 'dosen') $$("lbl_info_dosen").show();
                                                            else if (id == 'dosen') $$("lbl_info_dosen").show();
                                                            else {
                                                                $$("filter_semester").show();
                                                                if (id == 'courses') $$("force_sync_course").show();
                                                            }
                                                        }
                                                    }
                                                },
                                                { height: 20 },
                                                {
                                                    cols: [
                                                        { view: "richselect", id: "filter_angkatan", label: "Pilih Angkatan", labelPosition: "top", options: [], placeholder: "Semua Angkatan" },
                                                        { view: "richselect", id: "filter_semester", label: "Pilih Semester", labelPosition: "top", options: [], placeholder: "Pilih Semester", hidden: true },
                                                        { id: "lbl_info_dosen", template: "<div style='padding-top:25px; color:#718096; font-style:italic;'>Sinkronisasi seluruh data dosen aktif.</div>", borderless: true, hidden: true },
                                                        { view: "checkbox", id: "force_sync_course", label: "Force Sync (Cek Ulang Moodle)", labelWidth: 180, hidden: true, tooltip: "Centang jika ingin memaksa cek ulang ke Moodle meskipun data sudah ada di database lokal." },
                                                        { width: 20 },
                                                        { view: "counter", id: "sync_limit", label: "Batch Size", labelPosition: "top", value: 10, min: 1, max: 100, tooltip: "Jumlah data yang diproses per pengiriman. Kecilkan jika sering Timeout." }
                                                    ]
                                                },
                                                { height: 20 },
                                                {
                                                    cols: [
                                                        {},
                                                        { view: "button", id: "btn_start_sync", label: "MULAI SINKRONISASI", css: "webix_primary", width: 300, click: startMassSync }
                                                    ]
                                                },
                                                { height: 40 },
                                                {
                                                    id: "sync_progress_area", hidden: true, rows: [
                                                        { template: "<b>Progress Sinkronisasi:</b>", height: 30, borderless: true },
                                                        { view: "template", id: "sync_pbar", height: 40, borderless: true, template: "<div style='width:100%; height:20px; background:#edf2f7; border-radius:10px; overflow:hidden; margin-top:5px;'><div id='pbar_inner' style='width:0%; height:100%; background:#1e3a8a; transition: width 0.3s;'></div></div>" },
                                                        { id: "sync_stats", template: "<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: 0 / 0 Data</div>", height: 40, borderless: true },
                                                        { template: "<b>Log Progress:</b>", height: 30, borderless: true },
                                                        { view: "template", id: "sync_log_box", css: "sync_log", template: "<div id='log_content' style='white-space: pre-wrap;'>Menunggu proses dimulai...</div>" }
                                                    ]
                                                }
                                            ]
                                        },
                                        {}
                                    ]
                                },
                                {
                                    header: "<i class='fa fa-archive'></i> Maintenance Data",
                                    id: "tab_maintenance",
                                    padding: 30,
                                    rows: [
                                        {
                                            view: "form",
                                            id: "archiveForm",
                                            css: "card_manager",
                                            padding: 30,
                                            gravity: 1,
                                            elements: [
                                                { template: "<h2 style='color:#c53030;'><i class='fa fa-archive'></i> Arsip Semester Lama</h2><p style='color:#718096;'>Fitur ini akan menonaktifkan (Hide) semua mata kuliah pada semester yang dipilih di Moodle. Gunakan saat pergantian semester.</p>", height: 100, borderless: true },
                                                {
                                                    cols: [
                                                        { view: "richselect", id: "archive_semester", label: "Pilih Semester Lama", labelPosition: "top", options: [], placeholder: "Pilih Semester..." },
                                                        { width: 20 },
                                                        { view: "button", id: "btn_start_archive", label: "ARSIPKAN SEMESTER INI", css: "webix_danger", width: 250, click: startArchive, tooltip: "Perhatian! Data akan disembunyikan di Moodle." }
                                                    ]
                                                },
                                                { height: 20 },
                                                {
                                                    id: "archive_progress_area", hidden: true, rows: [
                                                        { template: "<b>Progress Arsip:</b>", height: 30, borderless: true },
                                                        { view: "template", id: "archive_pbar", height: 40, borderless: true, template: "<div style='width:100%; height:20px; background:#edf2f7; border-radius:10px; overflow:hidden; margin-top:5px;'><div id='pbar_archive_inner' style='width:0%; height:100%; background:#c53030; transition: width 0.3s;'></div></div>" },
                                                        { id: "archive_stats", template: "<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: 0 / 0 Data</div>", height: 40, borderless: true },
                                                        { template: "<b>Log Process:</b>", height: 30, borderless: true },
                                                    ]
                                                },
                                                { height: 30 },
                                                { template: "<h2 style='color:#742a2a;'><i class='fa fa-trash'></i> Hapus Course Lama (Duplikat)</h2><p style='color:#718096;'>Fitur ini akan MENGHAPUS mata kuliah format lama (tanpa kelas) pada semester yang dipilih. Hati-hati, data nilai/aktivitas di course lama akan hilang permanen.</p>", height: 100, borderless: true },
                                                {
                                                    cols: [
                                                        { view: "richselect", id: "delete_semester", label: "Pilih Semester", labelPosition: "top", options: [], placeholder: "Pilih Semester..." },
                                                        { width: 20 },
                                                        { view: "button", id: "btn_start_delete", label: "HAPUS PERMANEN", css: "webix_danger", width: 250, click: startDelete, tooltip: "Menghapus course KODE-SEMESTER tanpa kelas." }
                                                    ]
                                                },
                                                { height: 20 },
                                                {
                                                    id: "delete_progress_area", hidden: true, rows: [
                                                        { template: "<b>Progress Hapus:</b>", height: 30, borderless: true },
                                                        { view: "template", id: "delete_pbar", height: 40, borderless: true, template: "<div style='width:100%; height:20px; background:#edf2f7; border-radius:10px; overflow:hidden; margin-top:5px;'><div id='pbar_delete_inner' style='width:0%; height:100%; background:#742a2a; transition: width 0.3s;'></div></div>" },
                                                        { id: "delete_stats", template: "<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: 0 / 0 Data</div>", height: 40, borderless: true },
                                                        { template: "<b>Log Process:</b>", height: 30, borderless: true },
                                                        { view: "template", id: "delete_log_box", css: "sync_log", template: "<div id='log_delete_content' style='white-space: pre-wrap;'>Menunggu proses...</div>" }
                                                    ]
                                                }
                                            ]
                                        },
                                        {}
                                    ]
                                }
                            ]
                        }
                    ]
                });

                // Init Filters
                webix.ajax().get("api.php?action=get_sync_filters", function (text) {
                    var res = JSON.parse(text);
                    if (res.status == 'success') {
                        // Angkatan
                        var angkatanOptions = [{ id: "", value: "Semua Angkatan" }];
                        res.angkatan.forEach(function (a) {
                            angkatanOptions.push({ id: a, value: "Angkatan " + a });
                        });
                        $$("filter_angkatan").getPopup().getList().clearAll();
                        $$("filter_angkatan").getPopup().getList().parse(angkatanOptions);

                        // Semesters
                        var semOptions = [];
                        if (res.semesters && res.semesters.length > 0) {
                            res.semesters.forEach(function (s) {
                                semOptions.push({ id: s.id_smt, value: s.nm_smt + " (" + s.id_smt + ")" });
                            });
                            $$("filter_semester").getPopup().getList().clearAll();
                            $$("filter_semester").getPopup().getList().parse(semOptions);

                            // Archive Semester List (Same logic)
                            $$("archive_semester").getPopup().getList().clearAll();
                            $$("archive_semester").getPopup().getList().parse(semOptions);

                            // Delete Semester List
                            $$("delete_semester").getPopup().getList().clearAll();
                            $$("delete_semester").getPopup().getList().parse(semOptions);

                            // Default to first active semester
                            $$("filter_semester").setValue(semOptions[0].id);
                        }
                    }
                });

                // Logic
                $$("searchMhs").attachEvent("onTimedKeyPress", function () {
                    var val = this.getValue();
                    if (val.length >= 3) {
                        $$("listMhs").clearAll();
                        $$("listMhs").load("api.php?action=search_mhs&q=" + val);
                    }
                });

                function loadDetails(nim) {
                    // webix.ProgressBar is for 4.0+, using plain logic for 3.4
                    // $$("formDetail").showProgress(); 
                    $$("formDetail").disable();

                    webix.ajax().get("api.php?action=get_details&nim=" + nim, function (text) {
                        $$("formDetail").enable();
                        var res = JSON.parse(text);
                        if (res.status == 'success') {
                            var d = res.data;
                            document.getElementById('det_nama').innerHTML = d.nama;
                            $$("det_nim").setValue(d.nim);
                            $$("det_prodi").setValue(d.prodi);
                            $$("edit_email").setValue(d.moodle_email || d.email);
                            $$("edit_pass").setValue("");

                            var statusHtml = d.moodle_status == 'Aktif'
                                ? "<div class='status_badge status_aktif'>AKTIF DI MOODLE</div>"
                                : "<div class='status_badge status_belum'>BELUM TERDAFTAR</div>";
                            $$("det_status").setHTML(statusHtml);

                            $$("emptyView").hide();
                            $$("detailView").show();
                        }
                    });
                }

                // Mass Sync Logic
                var totalToSync = 0;
                var currentOffset = 0;
                var isSyncing = false;
                var successCount = 0;
                var failedCount = 0;

                function startMassSync() {
                    if (isSyncing) return;

                    var type = $$("sync_type").getValue();
                    var limit = $$("sync_limit").getValue();
                    var filter = "";

                    if (type === 'mahasiswa') filter = $$("filter_angkatan").getValue();
                    else if (type === 'courses' || type === 'enrolments') filter = $$("filter_semester").getValue();

                    $$("btn_start_sync").disable();
                    $$("sync_progress_area").show();

                    // Reset Log
                    document.getElementById('log_content').innerHTML = "";

                    var typeLabel = $$("sync_type").getValue();
                    addLog("Menyiapkan Sync " + typeLabel + "...");

                    webix.ajax().get("api.php?action=sync_prepare&type=" + type + "&filter=" + filter, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                totalToSync = res.total;
                                currentOffset = 0;
                                successCount = 0;
                                failedCount = 0;
                                isSyncing = true;

                                addLog("Total data ditemukan: " + totalToSync);
                                $$("sync_stats").setHTML("<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: " + currentOffset + " / " + totalToSync + " Data</div>");

                                // Start loop with small delay
                                setTimeout(function () {
                                    runBatchSync(type, filter, limit);
                                }, 500);
                            } else {
                                addLog("Error Prepare: " + res.message);
                                $$("btn_start_sync").enable();
                            }
                        } catch (e) {
                            addLog("Error Parsing Response: " + e.message + " | " + text);
                            $$("btn_start_sync").enable();
                        }
                    }).fail(function (xhr) {
                        addLog("Error Network/Server: " + xhr.status + " " + xhr.statusText);
                        $$("btn_start_sync").enable();
                    });
                }

                function runBatchSync(type, filter, limit) {
                    var force = "";
                    if (type === 'courses' && $$("force_sync_course").getValue()) {
                        force = "&force=true";
                    }

                    if (currentOffset >= totalToSync && totalToSync > 0) {
                        finishSync();
                        return;
                    }
                    if (totalToSync === 0) {
                        addLog("Tidak ada data untuk disinkronkan.");
                        finishSync();
                        return;
                    }

                    addLog("Memproses Batch Offset " + currentOffset + " (Limit " + limit + ")...");

                    webix.ajax().get("api.php?action=sync_batch&type=" + type + "&filter=" + filter + "&offset=" + currentOffset + "&limit=" + limit + force, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                var batchRes = res.results;

                                if (batchRes.success === false) {
                                    // Backend caught an exception internally
                                    addLog("Backend Error: " + (batchRes.error || "Unknown"));
                                    // Treat as failure (but we don't know how many failed in total vs caught, 
                                    // usually 1 batch failed = 0 processed)
                                    // Optional: failedCount += limit; 
                                } else {
                                    successCount += batchRes.success || 0;
                                    failedCount += batchRes.failed || 0;
                                }

                                currentOffset += limit;

                                // Visual bar update
                                var prg = (currentOffset / totalToSync) * 100;
                                if (prg > 100) prg = 100;
                                if (document.getElementById('pbar_inner')) {
                                    document.getElementById('pbar_inner').style.width = prg + "%";
                                }

                                // Visual stats update
                                var showOffset = currentOffset > totalToSync ? totalToSync : currentOffset;
                                $$("sync_stats").setHTML("<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: " + showOffset + " / " + totalToSync + " Data (Sukses: " + successCount + ", Gagal: " + failedCount + ")</div>");

                                addLog("Batch Selesai. Sukses: " + batchRes.success + ", Gagal: " + batchRes.failed);

                                // Run Next Batch with DELAY to prevent UI freeze
                                if (currentOffset < totalToSync) {
                                    setTimeout(function () {
                                        runBatchSync(type, filter, limit);
                                    }, 500);
                                } else {
                                    finishSync();
                                }
                            } else {
                                addLog("Error Batch: " + res.message);
                                isSyncing = false;
                                $$("btn_start_sync").enable();
                            }
                        } catch (e) {
                            addLog("Error Parsing Batch Response: " + e.message);
                            isSyncing = false;
                            $$("btn_start_sync").enable();
                        }
                    }).fail(function (xhr) {
                        addLog("Server Error / Timeout pada Offset " + currentOffset + " (" + xhr.status + ")");
                        isSyncing = false;
                        $$("btn_start_sync").enable();
                    });
                }

                function finishSync() {
                    addLog("--- SINKRONISASI SELESAI ---");
                    addLog("Total Berhasil: " + successCount);
                    addLog("Total Gagal: " + failedCount);
                    isSyncing = false;
                    $$("btn_start_sync").enable();

                    // Ensure bar is 100%
                    if (document.getElementById('pbar_inner')) {
                        document.getElementById('pbar_inner').style.width = "100%";
                    }
                }

                // ARCHIVE LOGIC
                var totalArchive = 0;
                var currentArchiveOffset = 0;
                var isArchiving = false;
                var archiveSuccess = 0;
                var archiveFailed = 0;

                function startArchive() {
                    if (isArchiving) return;
                    var semester = $$("archive_semester").getValue();
                    if (!semester) {
                        webix.alert({ type: "alert-error", text: "Pilih semester terlebih dahulu!" });
                        return;
                    }

                    webix.confirm({
                        title: "Konfirmasi Arsip",
                        text: "PERHATIAN: Semua mata kuliah di semester " + semester + " akan disembunyikan (Hide) di Moodle. Yakin ingin melanjutkan?",
                        type: "confirm-warning",
                        callback: function (result) {
                            if (result) {
                                executeArchive(semester);
                            }
                        }
                    });
                }

                function executeArchive(semester) {
                    $$("btn_start_archive").disable();
                    $$("archive_progress_area").show();
                    document.getElementById('log_archive_content').innerHTML = "Memulai proses arsip...\n";

                    webix.ajax().get("api.php?action=archive_prepare&semester=" + semester, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                totalArchive = res.total;
                                currentArchiveOffset = 0;
                                archiveSuccess = 0;
                                archiveFailed = 0;
                                isArchiving = true;

                                addArchiveLog("Total Matakuliah ditemukan: " + totalArchive);
                                $$("archive_stats").setHTML("<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: " + currentArchiveOffset + " / " + totalArchive + " Data</div>");

                                setTimeout(function () {
                                    runBatchArchive(semester, 10); // Batch 10 safer
                                }, 500);
                            } else {
                                addArchiveLog("Error Prepare: " + res.message);
                                $$("btn_start_archive").enable();
                            }
                        } catch (e) {
                            addArchiveLog("Error Response: " + e.message);
                            $$("btn_start_archive").enable();
                        }
                    });
                }

                function runBatchArchive(semester, limit) {
                    if (currentArchiveOffset >= totalArchive && totalArchive > 0) {
                        finishArchive();
                        return;
                    }
                    if (totalArchive === 0) {
                        addArchiveLog("Tidak ada data.");
                        finishArchive();
                        return;
                    }

                    addArchiveLog("Memproses Batch Offset " + currentArchiveOffset + "...");

                    webix.ajax().get("api.php?action=archive_batch&semester=" + semester + "&offset=" + currentArchiveOffset + "&limit=" + limit, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                var batchRes = res.results;
                                archiveSuccess += batchRes.success;
                                archiveFailed += batchRes.failed;
                                currentArchiveOffset += limit;

                                // Update Bar
                                var prg = (currentArchiveOffset / totalArchive) * 100;
                                if (prg > 100) prg = 100;
                                if (document.getElementById('pbar_archive_inner')) document.getElementById('pbar_archive_inner').style.width = prg + "%";

                                $$("archive_stats").setHTML("Processed: " + (currentArchiveOffset > totalArchive ? totalArchive : currentArchiveOffset) + " / " + totalArchive);
                                addArchiveLog("Batch OK. Sukses: " + batchRes.success + " Gagal: " + batchRes.failed);

                                if (currentArchiveOffset < totalArchive) {
                                    setTimeout(function () { runBatchArchive(semester, limit); }, 500);
                                } else {
                                    finishArchive();
                                }
                            } else {
                                addArchiveLog("Error Batch: " + res.message);
                                isArchiving = false;
                                $$("btn_start_archive").enable();
                            }
                        } catch (e) {
                            addArchiveLog("Error Parsing: " + e.message);
                            isArchiving = false;
                            $$("btn_start_archive").enable();
                        }
                    });
                }

                function finishArchive() {
                    addArchiveLog("-- SELESAI --");
                    addArchiveLog("Total Archived: " + archiveSuccess);
                    isArchiving = false;
                    $$("btn_start_archive").enable();
                    if (document.getElementById('pbar_archive_inner')) document.getElementById('pbar_archive_inner').style.width = "100%";
                }

                function addArchiveLog(msg) {
                    var box = document.getElementById('log_archive_content');
                    var now = new Date().toLocaleTimeString();
                    box.innerHTML += "[" + now + "] " + msg + "\n";
                    // Auto scroll not strictly needed for this small box but good to have
                    // var sbox = document.querySelector("#archive_log_box .webix_template"); // tricky selector
                }

                function addLog(msg) {
                    var box = document.getElementById('log_content');
                    var now = new Date().toLocaleTimeString();
                    box.innerHTML += "[" + now + "] " + msg + "\n";
                    var scrollBox = document.querySelector('.sync_log');
                    scrollBox.scrollTop = scrollBox.scrollHeight;
                }

                // DELETE LOGIC
                var totalDelete = 0;
                var currentDeleteOffset = 0;
                var isDeleting = false;
                var deleteSuccess = 0;
                var deleteFailed = 0;

                function startDelete() {
                    if (isDeleting) return;
                    var semester = $$("delete_semester").getValue();
                    if (!semester) {
                        webix.alert({ type: "alert-error", text: "Pilih semester terlebih dahulu!" });
                        return;
                    }

                    webix.confirm({
                        title: "KONFIRMASI HAPUS PERMANEN",
                        text: "PERHATIAN KERAS: Semua mata kuliah 'Generic' (tanpa kelas) pada semester " + semester + " akan DIHAPUS PERMANEN dari Moodle. Pastikan Anda sudah melakukan Sync Mata Kuliah (Kelas) sebelumnya. Tindakan ini tidak bisa dibatalkan. Lanjutkan?",
                        ok: "YA, HAPUS",
                        cancel: "BATAL",
                        callback: function (result) {
                            if (result) {
                                executeDelete(semester);
                            }
                        }
                    });
                }

                function executeDelete(semester) {
                    $$("btn_start_delete").disable();
                    $$("delete_progress_area").show();

                    // Safety: Ensure log box is reachable
                    var logBox = document.getElementById('log_delete_content');
                    if (logBox) logBox.innerHTML = "Memulai proses hapus...\n";

                    addDeleteLog("Mengambil data...");

                    webix.ajax().get("api.php?action=delete_prepare&semester=" + semester, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                totalDelete = res.total;
                                currentDeleteOffset = 0;
                                deleteSuccess = 0;
                                deleteFailed = 0;
                                isDeleting = true;

                                addDeleteLog("Total Matakuliah ditemukan: " + totalDelete);
                                $$("delete_stats").setHTML("<div style='text-align:center; padding:10px; font-weight:600;'>Memproses: " + currentDeleteOffset + " / " + totalDelete + " Data</div>");

                                setTimeout(function () {
                                    runBatchDelete(semester, 10);
                                }, 500);
                            } else {
                                addDeleteLog("Error Prepare: " + res.message);
                                $$("btn_start_delete").enable();
                            }
                        } catch (e) {
                            addDeleteLog("Error Response (Parse): " + e.message + " | " + text);
                            $$("btn_start_delete").enable();
                        }
                    }).fail(function (xhr) {
                        addDeleteLog("Server Error (Prepare): " + xhr.status + " " + xhr.statusText);
                        $$("btn_start_delete").enable();
                    });
                }

                function runBatchDelete(semester, limit) {
                    if (currentDeleteOffset >= totalDelete && totalDelete > 0) {
                        finishDelete();
                        return;
                    }
                    if (totalDelete === 0) {
                        addDeleteLog("Tidak ada data.");
                        finishDelete();
                        return;
                    }

                    addDeleteLog("Memproses Batch Offset " + currentDeleteOffset + "...");

                    webix.ajax().get("api.php?action=delete_batch&semester=" + semester + "&offset=" + currentDeleteOffset + "&limit=" + limit, function (text) {
                        try {
                            var res = JSON.parse(text);
                            if (res.status == 'success') {
                                var batchRes = res.results;
                                deleteSuccess += batchRes.success;
                                deleteFailed += batchRes.failed;
                                currentDeleteOffset += limit;

                                // Update Bar
                                var prg = (currentDeleteOffset / totalDelete) * 100;
                                if (prg > 100) prg = 100;
                                if (document.getElementById('pbar_delete_inner')) document.getElementById('pbar_delete_inner').style.width = prg + "%";

                                $$("delete_stats").setHTML("Processed: " + (currentDeleteOffset > totalDelete ? totalDelete : currentDeleteOffset) + " / " + totalDelete);

                                if (batchRes.success > 0) {
                                    addDeleteLog("Batch OK. Terhapus: " + batchRes.success);
                                }
                                if (batchRes.failed > 0) {
                                    addDeleteLog("Batch Gagal: " + batchRes.failed);
                                    if (batchRes.errors && batchRes.errors.length > 0) {
                                        addDeleteLog("Err: " + JSON.stringify(batchRes.errors[0]));
                                    }
                                }

                                if (currentDeleteOffset < totalDelete) {
                                    setTimeout(function () { runBatchDelete(semester, limit); }, 500);
                                } else {
                                    finishDelete();
                                }
                            } else {
                                addDeleteLog("Error Batch: " + res.message);
                                isDeleting = false;
                                $$("btn_start_delete").enable();
                            }
                        } catch (e) {
                            addDeleteLog("Error Parsing (Batch): " + e.message + " | " + text);
                            isDeleting = false;
                            $$("btn_start_delete").enable();
                        }
                    }).fail(function (xhr) {
                        addDeleteLog("Server Error (Batch): " + xhr.status + " " + xhr.statusText + " | " + xhr.responseText);
                        isDeleting = false;
                        $$("btn_start_delete").enable();
                    });
                }

                function finishDelete() {
                    addDeleteLog("-- SELESAI --");
                    addDeleteLog("Total Dihapus: " + deleteSuccess);
                    isDeleting = false;
                    $$("btn_start_delete").enable();
                    if (document.getElementById('pbar_delete_inner')) document.getElementById('pbar_delete_inner').style.width = "100%";
                }

                function addDeleteLog(msg) {
                    var box = document.getElementById('log_delete_content');
                    var now = new Date().toLocaleTimeString();
                    box.innerHTML += "[" + now + "] " + msg + "\n";
                }

                // Individu Logic
                function doUpdate() {
                    var nim = $$("det_nim").getValue();
                    var data = {
                        nim: nim,
                        email: $$("edit_email").getValue(),
                        password: $$("edit_pass").getValue()
                    };

                    webix.confirm({
                        title: "Konfirmasi Update",
                        text: "Apakah Anda yakin ingin memperbarui data akun Moodle mahasiswa ini?",
                        callback: function (result) {
                            if (result) {
                                $$("formDetail").disable();
                                webix.ajax().headers({ "Content-Type": "application/json" })
                                    .post("api.php?action=update_moodle", JSON.stringify(data), function (text) {
                                        $$("formDetail").enable();
                                        var res = JSON.parse(text);
                                        if (res.status == 'success') {
                                            webix.message({ type: "success", text: res.message });
                                            loadDetails(nim);
                                        } else {
                                            webix.alert({ title: "Gagal", text: res.message, type: "alert-error" });
                                        }
                                    });
                            }
                        }
                    });
                }

                function doSync() {
                    var nim = $$("det_nim").getValue();
                    webix.confirm({
                        title: "Force Sync",
                        text: "Ini akan mengirim ulang seluruh data profil mahasiswa ke Moodle. Lanjutkan?",
                        callback: function (result) {
                            if (result) {
                                $$("formDetail").disable();
                                webix.ajax().get("api.php?action=sync_individual&nim=" + nim, function (text) {
                                    $$("formDetail").enable();
                                    var res = JSON.parse(text);
                                    if (res.status == 'success') {
                                        webix.message({ type: "success", text: res.message });
                                        loadDetails(nim);
                                    } else {
                                        webix.alert({ title: "Gagal Sync", text: res.message, type: "alert-error" });
                                    }
                                });
                            }
                        }
                    });
                }

            <?php endif; ?>
        });
    </script>
</body>

</html>