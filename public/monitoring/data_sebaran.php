<?php
require_once 'auth_check.php';
require_once '../../config/config.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

header('Content-Type: application/json');

try {
    $db = koneksi();

    // 0. Parameters
    $action = $_GET['action'] ?? 'data';

    // Filters
    $filterProdi = $_GET['prodi'] ?? null;
    $filterMk = $_GET['matakuliah'] ?? null; // ID MK (Code or UUID)
    $filterKelas = $_GET['kelas'] ?? null;
    $filterAngkatan = $_GET['angkatan'] ?? null;
    $filterSemester = $_GET['semester'] ?? null;

    // 1. Get Active Semester (TA)
    if ($filterSemester) {
        $activeTA = $filterSemester;
    } else {
        // Default to latest
        $stmtTA = $db->query("SELECT id_smt FROM wsia_kelas_kuliah ORDER BY id_smt DESC LIMIT 1");
        $activeTA = $stmtTA->fetchColumn();
    }

    if (!$activeTA) {
        throw new Exception("Data Semester tidak ditemukan.");
    }

    // Base Logic for Filtering
    $whereClauses = ["k.id_smt = :ta", "n.nilai_huruf IS NOT NULL", "n.nilai_huruf != ''"];
    $params = ['ta' => $activeTA];

    // Joins
    // Note: Based on ws/ws/nilai.php and ws/ws/kelas_kuliah.php
    // wsia_nilai.xid_kls = wsia_kelas_kuliah.xid_kls
    // wsia_kelas_kuliah.id_mk = wsia_mata_kuliah.xid_mk (UUID match)
    // wsia_nilai.xid_reg_pd = mahasiswa.no_pend
    // trim(mahasiswa.nim) = trim(wsia_mahasiswa_pt.nipd)

    $joinSql = "
        JOIN wsia_kelas_kuliah k ON n.xid_kls = k.xid_kls
        LEFT JOIN wsia_mata_kuliah mk ON k.id_mk = mk.xid_mk
        LEFT JOIN viewMahasiswaPt mhs ON n.xid_reg_pd = mhs.no_pend
        LEFT JOIN wsia_mahasiswa_pt mpt ON mhs.nipd = mpt.nipd
        LEFT JOIN wsia_sms sms ON k.id_sms = sms.xid_sms
    ";

    if ($filterProdi) {
        // wsia_kelas_kuliah.id_sms links to wsia_sms.xid_sms (Prodi ID)
        $whereClauses[] = "k.id_sms = :prodi";
        $params['prodi'] = $filterProdi;
    }

    if ($filterMk) {
        // Filter by Course ID (Strict match since it comes from dropdown)
        $whereClauses[] = "mk.id_mk = :mk";
        $params['mk'] = $filterMk;
    }

    if ($filterKelas) {
        $whereClauses[] = "k.nm_kls = :kelas";
        $params['kelas'] = $filterKelas;
    }

    if ($filterAngkatan) {
        // Angkatan from mulai_smt (e.g. 20231 -> 2023)
        $whereClauses[] = "LEFT(mpt.mulai_smt, 4) = :angkatan";
        $params['angkatan'] = $filterAngkatan;
    }

    // --- ACTION: DOWNLOAD XLSX ---
    if ($action === 'download') {
        $whereSql = implode(" AND ", $whereClauses);

        $sqlInfo = "SELECT 
                        mhs.nipd, 
                        mhs.nm_pd, 
                        sms.nm_lemb as prodi,
                        mk.id_mk as kode_mk,
                        mk.nm_mk as mata_kuliah, 
                        k.nm_kls as kelas,
                        n.nilai_huruf, 
                        n.nilai_angka,
                        n.nilai_indeks,
                        mpt.mulai_smt as semester_masuk
                    FROM wsia_nilai n
                    $joinSql
                    WHERE $whereSql
                    ORDER BY mhs.nipd ASC, mk.nm_mk ASC";

        $stmt = $db->prepare($sqlInfo);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create Excel file using PhpSpreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sebaran Nilai');

        // Header row
        $headers = ['NIM', 'Nama Mahasiswa', 'Prodi', 'Kode MK', 'Mata Kuliah', 'Kelas', 'Nilai Huruf', 'Nilai Angka', 'Nilai Indeks', 'Semester Masuk'];
        $sheet->fromArray($headers, NULL, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Data rows
        $rowIndex = 2;
        foreach ($rows as $row) {
            $sheet->fromArray(array_values($row), NULL, 'A' . $rowIndex);
            $rowIndex++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output XLSX file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="sebaran_nilai_' . $activeTA . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- ACTION: GET FILTER OPTIONS ---
    // --- ACTION: GET MATAKULIAH ---
    else if ($action === 'get_mk') {
        $ta = $_GET['semester'] ?? $activeTA;
        $prodi = $_GET['prodi'] ?? null;

        $sql = "SELECT DISTINCT mk.id_mk as id, mk.nm_mk as text 
                FROM wsia_kelas_kuliah k
                JOIN wsia_mata_kuliah mk ON k.id_mk = mk.xid_mk
                WHERE k.id_smt = :ta";
        $params = ['ta' => $ta];

        if ($prodi) {
            $sql .= " AND k.id_sms = :prodi";
            $params['prodi'] = $prodi;
        }

        $sql .= " ORDER BY mk.nm_mk";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // --- ACTION: GET KELAS ---
    else if ($action === 'get_kelas') {
        $ta = $_GET['semester'] ?? $activeTA;
        $mk = $_GET['matakuliah'] ?? null;
        $prodi = $_GET['prodi'] ?? null;

        if (!$mk) {
            echo json_encode([]);
            exit;
        }

        $sql = "SELECT DISTINCT k.nm_kls as id, k.nm_kls as text 
                FROM wsia_kelas_kuliah k
                WHERE k.id_smt = :ta AND (k.id_mk = :mk OR k.id_mk = (SELECT xid_mk FROM wsia_mata_kuliah WHERE id_mk = :mk2 LIMIT 1))";

        // Handle logic where mk param might be ID code or UUID. 
        // Trying simple match first. logic above tries to match UUID or Code if passed. 
        // But for dropdown value we will use ID (Code) from get_mk.
        // Actually get_mk returns id_mk (Code). wsia_kelas_kuliah links xid_mk.
        // Let's refine join: 

        $sql = "SELECT DISTINCT k.nm_kls as id, k.nm_kls as text 
                FROM wsia_kelas_kuliah k
                JOIN wsia_mata_kuliah mk ON k.id_mk = mk.xid_mk
                WHERE k.id_smt = :ta AND mk.id_mk = :mk";

        $params = ['ta' => $ta, 'mk' => $mk];

        if ($prodi) {
            $sql .= " AND k.id_sms = :prodi";
            $params['prodi'] = $prodi;
        }

        $sql .= " ORDER BY k.nm_kls";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // --- ACTION: GET FILTER OPTIONS ---
    else if ($action === 'options') {
        // Return available options

        // 1. Prodi - Fetch ALL from wsia_sms directly
        $sqlProdi = "SELECT xid_sms as id, nm_lemb as text 
                     FROM wsia_sms 
                     WHERE nm_lemb IS NOT NULL AND nm_lemb != ''
                     ORDER BY nm_lemb";
        $stmtProdi = $db->query($sqlProdi);
        $prodiOpts = $stmtProdi->fetchAll(PDO::FETCH_ASSOC);

        // 2. Angkatan
        $currentYear = substr($activeTA, 0, 4);
        $angkatanOpts = [];
        for ($i = 0; $i < 7; $i++) {
            $y = $currentYear - $i;
            $angkatanOpts[] = ['id' => $y, 'text' => $y];
        }

        // 3. Semester Options (Available Semesters in DB)
        $sqlSmt = "SELECT DISTINCT id_smt FROM wsia_kelas_kuliah ORDER BY id_smt DESC LIMIT 10";
        $stmtSmt = $db->query($sqlSmt);
        $smtRaw = $stmtSmt->fetchAll(PDO::FETCH_COLUMN);

        $semesterOpts = [];
        foreach ($smtRaw as $sid) {
            $tahun = substr($sid, 0, 4);
            $smtCode = substr($sid, 4, 1);
            $smtName = ($smtCode == '1') ? 'Ganjil' : (($smtCode == '2') ? 'Genap' : 'Pendek');
            $label = "$tahun/" . ($tahun + 1) . " " . $smtName;
            $semesterOpts[] = ['id' => $sid, 'text' => $label];
        }

        echo json_encode([
            'prodi' => $prodiOpts,
            'angkatan' => $angkatanOpts,
            'semester' => $semesterOpts
        ]);
        exit;
    }

    // --- DEFAULT: DASHBOARD DATA ---

    // 2. Query Distribution (With Filters)
    $whereSql = implode(" AND ", $whereClauses);

    $sql = "SELECT 
                n.nilai_huruf, 
                COUNT(*) as jumlah 
            FROM wsia_nilai n
            $joinSql
            WHERE $whereSql
            GROUP BY n.nilai_huruf
            ORDER BY n.nilai_huruf ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Stats
    $totalSql = "SELECT COUNT(*) FROM wsia_nilai n $joinSql WHERE $whereSql";
    $stmtTotal = $db->prepare($totalSql);
    $stmtTotal->execute($params);
    $total = $stmtTotal->fetchColumn();

    // 4. Pass Count
    $passSql = "SELECT COUNT(*) FROM wsia_nilai n $joinSql WHERE $whereSql AND n.nilai_huruf IN ('A', 'B', 'C')";
    $stmtPass = $db->prepare($passSql);
    $stmtPass->execute($params);
    $passed = $stmtPass->fetchColumn();

    // 5. Recent Data / Table Preview (Limit 100)
    $sqlTable = "SELECT 
                    mhs.nipd, 
                    mhs.nm_pd, 
                    sms.nm_lemb as prodi,
                    mk.nm_mk as mata_kuliah, 
                    k.nm_kls as kelas,
                    n.nilai_huruf,
                    n.nilai_indeks
                FROM wsia_nilai n
                $joinSql
                WHERE $whereSql
                ORDER BY mhs.nm_pd ASC
                LIMIT 100";

    $stmtTable = $db->prepare($sqlTable);
    $stmtTable->execute($params);
    $tableData = $stmtTable->fetchAll(PDO::FETCH_ASSOC);

    // Format semester
    $tahun = substr($activeTA, 0, 4);
    $smtCode = substr($activeTA, 4, 1);
    $smtName = ($smtCode == '1') ? 'Ganjil' : (($smtCode == '2') ? 'Genap' : 'Pendek');
    $semLabel = "$tahun/" . ($tahun + 1) . " " . $smtName;

    $response = [
        'semester' => $semLabel,
        'semester_id' => $activeTA,
        'total_grades' => $total,
        'passed_count' => $passed,
        'passed_percentage' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
        'distribution' => $data,
        'recent_data' => $tableData, // New field for table
        'filter_applied' => [
            'prodi' => $filterProdi,
            'mk' => $filterMk,
            'kelas' => $filterKelas,
            'angkatan' => $filterAngkatan
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

