<?php
/**
 * OBESyncService
 * Sinkronisasi nilai dari SIOBE ke SIAKAD via HTTP API (api-obe.poltekindonusa.ac.id)
 *
 * Alur:
 *  1. SIAKAD memanggil GET /v2/nilai-obe di api-obe.poltekindonusa.ac.id
 *  2. api-obe mengambil data dari database SIOBE (polinus_siobe)
 *  3. SIAKAD menerima JSON dan meng-update tabel wsia_nilai berdasarkan NIM & kode MK
 */
class OBESyncService
{
    /** Base URL API OBE */
    const API_BASE = 'https://api-obe.poltekindonusa.ac.id';

    /** API Key untuk autentikasi ke api-obe */
    const API_KEY  = 'siakad-sync-internal-key-2025';  // Ganti sesuai key yang terdaftar di storage api_auth.sqlite

    /** Timeout HTTP request (detik) */
    const TIMEOUT  = 60;

    private $db;  // Koneksi SIAKAD

    public function __construct()
    {
        if (!function_exists('koneksi')) {
            require_once __DIR__ . '/../config/config.php';
        }
        $this->db = koneksi();
    }

    // ---------------------------------------------------------------
    // PUBLIC
    // ---------------------------------------------------------------

    /**
     * Ambil daftar kelas yang tersedia di SIOBE (untuk preview / mapping)
     * Memanggil: GET /v2/nilai-obe/kelas
     */
    public function getKelasOBE(string $tahun = '', int $semester = 0): array
    {
        $params = [];
        if ($tahun)    $params['tahun']    = $tahun;
        if ($semester) $params['semester'] = $semester;

        return $this->apiGet('/v2/nilai-obe/kelas', $params);
    }

    /**
     * Sinkronisasi nilai OBE → SIAKAD
     * Memanggil: GET /v2/nilai-obe dengan filter tahun/semester/kode_makul/kelas
     *
     * @param string $tahun     Tahun ajaran SIOBE, contoh: "2024/2025"
     * @param int    $semester  Semester mengajar di SIOBE (1=Ganjil, 2=Genap)
     * @param string $kodeMakul Opsional – filter kode mata kuliah
     * @param string $kelas     Opsional – filter kode kelas
     * @param int    $limit     Max baris yang diambil dari API
     */
    public function syncGrades(
        string $tahun    = '',
        int    $semester = 0,
        string $kodeMakul = '',
        string $kelas    = '',
        int    $limit    = 500
    ): array {
        $results = [
            'total_fetched' => 0,
            'total_updated' => 0,
            'total_skipped' => 0,
            'errors'        => [],
            'messages'      => []
        ];

        // 1. Ambil data dari API OBE
        $params = ['limit' => $limit];
        if ($tahun)     $params['tahun']      = $tahun;
        if ($semester)  $params['semester']   = $semester;
        if ($kodeMakul) $params['kode_makul'] = $kodeMakul;
        if ($kelas)     $params['kelas']      = $kelas;

        $apiResponse = $this->apiGet('/v2/nilai-obe', $params);

        if (!isset($apiResponse['results'])) {
            $results['errors'][] = 'Gagal mengambil data dari API OBE: ' .
                ($apiResponse['status']['description'] ?? 'Unknown error');
            return $results;
        }

        $grades = $apiResponse['results'];
        $results['total_fetched'] = count($grades);
        $results['messages'][] = "Berhasil mengambil {$results['total_fetched']} data nilai dari SIOBE.";

        // 2. Proses tiap record
        foreach ($grades as $g) {
            try {
                $nim       = $g['nim']         ?? '';
                $kodeMk    = $g['kode_makul']  ?? '';
                $klsOBE    = $g['kelas']       ?? '';
                $nilaiAngka = $g['nilai_angka'] ?? 0;
                $nilaiHuruf = $g['nilai_huruf'] ?? '';

                if (!$nim || !$kodeMk) {
                    $results['total_skipped']++;
                    continue;
                }

                // 3. Cari id_nilai di SIAKAD berdasarkan NIM + kode MK + kelas (nm_kls)
                // nm_kls di SIAKAD = kode_kelas di SIOBE (biasanya sama)
                $idNilai = $this->findIdNilai($nim, $kodeMk, $klsOBE);

                if (!$idNilai) {
                    // Coba tanpa filter kelas (fallback ke semester/tahun saja)
                    $idNilai = $this->findIdNilaiFlexible($nim, $kodeMk);
                }

                if (!$idNilai) {
                    $results['total_skipped']++;
                    // $results['errors'][] = "Tidak ditemukan di SIAKAD: NIM=$nim, MK=$kodeMk, Kelas=$klsOBE";
                    continue;
                }

                // 4. Hitung nilai indeks berdasarkan huruf
                $nilaiIndeks = $this->hurufKeIndeks($nilaiHuruf);

                // 5. Update nilai di SIAKAD
                $sql = "UPDATE wsia_nilai SET
                            nilai_angka   = :angka,
                            nilai_huruf   = :huruf,
                            nilai_indeks  = :indeks,
                            nilai_tampil  = '2',
                            updated_at    = NOW()
                        WHERE id_nilai = :id_nilai";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':angka'    => $nilaiAngka,
                    ':huruf'    => $nilaiHuruf,
                    ':indeks'   => $nilaiIndeks,
                    ':id_nilai' => $idNilai
                ]);

                if ($stmt->rowCount() > 0) {
                    $results['total_updated']++;
                } else {
                    $results['total_skipped']++;
                }

            } catch (Throwable $e) {
                $results['errors'][] = "Error NIM {$g['nim']}: " . $e->getMessage();
            }
        }

        $results['messages'][] = "Sync selesai. Update: {$results['total_updated']}, Skip: {$results['total_skipped']}.";
        return $results;
    }

    /**
     * Cek apakah API OBE bisa dijangkau
     */
    public function testConnection(): array
    {
        $res = $this->apiGet('/v2/health', []);
        if (isset($res['ok']) && $res['ok'] === true) {
            return ['success' => true, 'message' => 'Koneksi ke API OBE berhasil.'];
        }
        return ['success' => false, 'message' => 'Gagal terhubung ke API OBE.', 'detail' => $res];
    }

    // ---------------------------------------------------------------
    // PRIVATE HELPERS
    // ---------------------------------------------------------------

    private function apiGet(string $endpoint, array $params = []): array
    {
        $url = self::API_BASE . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'X-API-Key: ' . self::API_KEY,
                'Accept: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false, // Sesuaikan jika prod pakai cert resmi
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['error' => 'curl_error', 'message' => $err];
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'json_decode_error', 'raw' => substr($body, 0, 500), 'http_code' => $code];
        }

        return $data ?? [];
    }

    /**
     * Cari id_nilai di SIAKAD berdasarkan NIM + kode MK + kelas (exact match)
     */
    private function findIdNilai(string $nim, string $kodeMk, string $kelas): ?int
    {
        $sql = "SELECT n.id_nilai
                FROM wsia_nilai n
                INNER JOIN wsia_kelas_kuliah kk ON n.xid_kls = kk.xid_kls
                INNER JOIN wsia_mata_kuliah  mk ON kk.id_mk  = mk.xid_mk
                INNER JOIN wsia_mahasiswa_pt mpt ON n.xid_reg_pd = mpt.xid_reg_pd
                WHERE mpt.nipd  = :nim
                  AND mk.kode_mk = :kode_mk
                  AND kk.nm_kls  = :kelas
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nim' => $nim, ':kode_mk' => $kodeMk, ':kelas' => $kelas]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_nilai'] : null;
    }

    /**
     * Fallback: cari id_nilai tanpa filter kelas (ambil semester aktif / terbaru)
     */
    private function findIdNilaiFlexible(string $nim, string $kodeMk): ?int
    {
        $sql = "SELECT n.id_nilai
                FROM wsia_nilai n
                INNER JOIN wsia_kelas_kuliah kk ON n.xid_kls = kk.xid_kls
                INNER JOIN wsia_mata_kuliah  mk ON kk.id_mk  = mk.xid_mk
                INNER JOIN wsia_mahasiswa_pt mpt ON n.xid_reg_pd = mpt.xid_reg_pd
                WHERE mpt.nipd   = :nim
                  AND mk.kode_mk  = :kode_mk
                ORDER BY kk.id_smt DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':nim' => $nim, ':kode_mk' => $kodeMk]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id_nilai'] : null;
    }

    /**
     * Konversi nilai huruf ke nilai indeks (default universitas Indonesia)
     * Bisa di-override dengan query ke wsia_bobot_nilai jika ada.
     */
    private function hurufKeIndeks(string $huruf): float
    {
        $map = [
            'A'  => 4.00, 'A-' => 3.75,
            'B+' => 3.50, 'B'  => 3.00, 'B-' => 2.75,
            'C+' => 2.50, 'C'  => 2.00, 'C-' => 1.75,
            'D+' => 1.50, 'D'  => 1.00,
            'E'  => 0.00, 'F'  => 0.00,
        ];

        $huruf = strtoupper(trim($huruf));
        if (isset($map[$huruf])) return $map[$huruf];

        // Fallback: cari dari database SIAKAD
        try {
            $stmt = $this->db->prepare("SELECT nilai_indeks FROM wsia_bobot_nilai WHERE nilai_huruf = :h ORDER BY id_sms DESC LIMIT 1");
            $stmt->execute([':h' => $huruf]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return (float)$row['nilai_indeks'];
        } catch (Throwable $e) { /* ignore */ }

        return 0.00;
    }
}
