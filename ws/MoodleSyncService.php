<?php
/**
 * Moodle Sync Service
 * Handle synchronization between SIAKAD and Moodle
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/moodle_config.php';
require_once __DIR__ . '/MoodleWebService.php';

class MoodleSyncService
{
    private $db;
    private $moodle;
    private $config;

    public function __construct()
    {
        $this->db = koneksi();
        if (!$this->db) {
            throw new Exception("Gagal terhubung ke database SIAKAD. Silakan cek konfigurasi database.");
        }
        $this->moodle = new MoodleWebService();
        $this->config = getMoodleConfig();
    }

    /**
     * Sync all data (mahasiswa, dosen, courses)
     */
    public function syncAll()
    {
        $results = [
            'mahasiswa' => $this->syncMahasiswa(),
            'dosen' => $this->syncDosen(),
            'courses' => $this->syncCourses(),
            'enrolments' => $this->syncEnrolments()
        ];

        $this->logSync('sync_all', $results);

        return $results;
    }

    /**
     * Sync mahasiswa to Moodle
     * @param int|null $limit Limit number of records
     * @param string|null $angkatan Filter by angkatan (year)
     * @param int $offset Offset start position
     */
    public function syncMahasiswa($limit = null, $angkatan = null, $offset = 0)
    {
        // Release session lock to prevent 504 Gateway Timeout on other requests
        if (session_id()) {
            session_write_close();
        }

        try {
            // Get mahasiswa from SIAKAD (using production table structure)
            $sql = "SELECT 
                        mpt.nipd as nim,
                        m.nm_pd as nama,
                        s.xid_sms as id_prodi,
                        s.nm_lemb as nama_program_studi,
                        SUBSTRING(mpt.mulai_smt, 1, 4) as angkatan,
                        'A' as status_mahasiswa
                    FROM wsia_mahasiswa m
                    INNER JOIN wsia_mahasiswa_pt mpt ON m.xid_pd = mpt.id_pd
                    INNER JOIN wsia_sms s ON mpt.id_sms = s.xid_sms
                    WHERE mpt.id_jns_keluar = ''";

            // Filter by angkatan if provided
            if ($angkatan) {
                $sql .= " AND SUBSTRING(mpt.mulai_smt, 1, 4) = " . $this->db->quote($angkatan);
            }

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $mahasiswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [
                'total' => count($mahasiswaList),
                'success' => 0,
                'failed' => 0,
                'updated' => 0,
                'created' => 0,
                'errors' => []
            ];

            foreach ($mahasiswaList as $mhs) {
                try {
                    // Check if user exists in Moodle
                    $existingUser = $this->moodle->getUserByIdNumber($mhs['nim']);

                    // Prepare user data
                    $userData = $this->prepareMahasiswaData($mhs);

                    if ($existingUser['success'] && !empty($existingUser['data']['users'])) {
                        // Update existing user
                        $userId = $existingUser['data']['users'][0]['id'];
                        $result = $this->moodle->updateUser($userId, $userData);

                        if ($result['success']) {
                            $results['success']++;
                            $results['updated']++;
                            $this->saveSyncMapping('mahasiswa', $mhs['nim'], $userId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'nim' => $mhs['nim'],
                                'error' => $result['error']
                            ];
                        }
                    } else {
                        // Create new user
                        $result = $this->moodle->createUser($userData);

                        if ($result['success'] && isset($result['data'][0]['id'])) {
                            $userId = $result['data'][0]['id'];
                            $results['success']++;
                            $results['created']++;
                            $this->saveSyncMapping('mahasiswa', $mhs['nim'], $userId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'nim' => $mhs['nim'],
                                'error' => $result['error'] ?? 'Unknown error',
                                'debug_data' => $result['sent_data'] ?? null
                            ];
                        }
                    }

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'nim' => $mhs['nim'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync dosen to Moodle
     */
    public function syncDosen($limit = null, $offset = 0)
    {
        // Release session lock
        if (session_id()) {
            session_write_close();
        }

        try {
            // Get dosen from SIAKAD (using production table structure)
            $sql = "SELECT 
                        d.nidn,
                        d.nm_ptk as nama_dosen,
                        d.xid_ptk as id_prodi,
                        '' as nama_program_studi,
                        '1' as status_aktif
                    FROM wsia_dosen d
                    WHERE d.id_sp = (SELECT id_sp FROM wsia_satuan_pendidikan WHERE npsn = '" . NPSN . "')";

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $dosenList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [
                'total' => count($dosenList),
                'success' => 0,
                'failed' => 0,
                'updated' => 0,
                'created' => 0,
                'errors' => []
            ];

            foreach ($dosenList as $dosen) {
                try {
                    // Check if user exists in Moodle
                    $existingUser = $this->moodle->getUserByIdNumber($dosen['nidn']);

                    // Prepare user data
                    $userData = $this->prepareDosenData($dosen);

                    if ($existingUser['success'] && !empty($existingUser['data']['users'])) {
                        // Update existing user
                        $userId = $existingUser['data']['users'][0]['id'];
                        $result = $this->moodle->updateUser($userId, $userData);

                        if ($result['success']) {
                            $results['success']++;
                            $results['updated']++;
                            $this->saveSyncMapping('dosen', $dosen['nidn'], $userId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'nidn' => $dosen['nidn'],
                                'error' => $result['error']
                            ];
                        }
                    } else {
                        // Create new user
                        $result = $this->moodle->createUser($userData);

                        if ($result['success'] && isset($result['data'][0]['id'])) {
                            $userId = $result['data'][0]['id'];
                            $results['success']++;
                            $results['created']++;
                            $this->saveSyncMapping('dosen', $dosen['nidn'], $userId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'nidn' => $dosen['nidn'],
                                'error' => $result['error'] ?? 'Unknown error'
                            ];
                        }
                    }

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'nidn' => $dosen['nidn'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync courses (mata kuliah) to Moodle
     * @param int|null $limit Limit number of records
     * @param string|null $semester Filter by semester (e.g., 20241)
     * @param int $offset Offset for pagination
     * @param bool $forceUpdate If true, force check Moodle even if mapping exists
     */
    public function syncCourses($limit = null, $semester = null, $offset = 0, $forceUpdate = false)
    {
        // Release session lock
        if (session_id()) {
            session_write_close();
        }

        try {
            // First, sync categories (prodi)
            $this->syncCategories();

            // Use provided semester or get current active semester
            $id_smt = $semester ?: $this->getCurrentSemester();

            // Get offered classes (courses) for this semester
            $sql = "SELECT DISTINCT 
                        mk.kode_mk as kode_mata_kuliah,
                        mk.nm_mk as nama_mata_kuliah,
                        mk.sks_mk as sks_mata_kuliah,
                        kk.id_sms as id_prodi,
                        s.kode_prodi,
                        s.nm_lemb as nama_program_studi,
                        kk.nm_kls as nama_kelas_kuliah
                    FROM wsia_kelas_kuliah kk
                    INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
                    INNER JOIN wsia_sms s ON kk.id_sms = s.xid_sms
                    WHERE kk.id_smt = " . $this->db->quote($id_smt);

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $courseList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [
                'total' => count($courseList),
                'success' => 0,
                'failed' => 0,
                'updated' => 0,
                'created' => 0,
                'errors' => []
            ];


            // --- BULK MAPPING PRE-CHECK START ---
            if (!$forceUpdate && !empty($courseList)) {
                $batchIds = [];
                // Generate ID numbers for all courses in this batch
                foreach ($courseList as $i => $c) {
                    $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $c['nama_kelas_kuliah']);
                    $idNum = $c['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanKelas;
                    $batchIds[$i] = $idNum; // Keep index mapping
                }

                if (!empty($batchIds)) {
                    // Chunk keys to avoid SQL limit if limit is large
                    $placeholders = [];
                    $params = [];
                    foreach ($batchIds as $bid) {
                        $params[] = $bid;
                        $placeholders[] = '?';
                    }

                    $plString = implode(',', $placeholders);
                    // Single query check
                    $sqlMap = "SELECT siakad_id FROM moodle_sync_mapping 
                               WHERE type='course' AND siakad_id IN ($plString)";
                    $stmtMap = $this->db->prepare($sqlMap);
                    $stmtMap->execute($params);
                    $existingKeys = $stmtMap->fetchAll(PDO::FETCH_COLUMN);

                    // Create fast lookup map
                    $existingMap = array_flip($existingKeys);
                }
            }
            // --- BULK MAPPING PRE-CHECK END ---

            foreach ($courseList as $i => $course) {
                try {
                    $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $course['nama_kelas_kuliah']);
                    $idNumber = $course['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanKelas;

                    // FAST CHECK
                    if (!$forceUpdate && isset($existingMap[$idNumber])) {
                        $results['success']++;
                        continue;
                    }

                    // Get category ID from mapping
                    $categoryId = $this->getCategoryIdByProdi($course['id_prodi']);

                    if (!$categoryId) {
                        // Fallback: Check if category exists in Moodle by Kode Prodi
                        $existingCat = $this->moodle->getCategoryByIdNumber($course['kode_prodi']);
                        if ($existingCat['success']) {
                            $categoryId = $existingCat['data']['id'];
                            // Heal mapping
                            $this->saveSyncMapping('category', $course['id_prodi'], $categoryId);
                        }
                    }

                    if (!$categoryId) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'kode' => $course['kode_mata_kuliah'],
                            'error' => 'Category not found for prodi: ' . $course['id_prodi'] . ' (' . $course['kode_prodi'] . ')'
                        ];
                        continue;
                    }

                    // Proceed with Moodle Sync...
                    $existingCourse = $this->moodle->getCourseByIdNumber($idNumber);

                    // Prepare course data
                    $courseData = $this->prepareCourseData($course, $categoryId, $id_smt);

                    if ($existingCourse['success'] && !empty($existingCourse['data']['courses'])) {
                        // Update existing course
                        $courseId = $existingCourse['data']['courses'][0]['id'];
                        $result = $this->moodle->updateCourse($courseId, $courseData);

                        if ($result['success']) {
                            $results['success']++;
                            $results['updated']++;
                            $this->saveSyncMapping('course', $idNumber, $courseId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'kode' => $course['kode_mata_kuliah'],
                                'error' => $result['error']
                            ];
                        }
                    } else {
                        // Create new course
                        $result = $this->moodle->createCourse($courseData);

                        if ($result['success'] && isset($result['data'][0]['id'])) {
                            $courseId = $result['data'][0]['id'];
                            $results['success']++;
                            $results['created']++;
                            $this->saveSyncMapping('course', $idNumber, $courseId);
                        } else {
                            $results['failed']++;
                            $results['errors'][] = [
                                'kode' => $course['kode_mata_kuliah'],
                                'error' => $result['error'] ?? 'Unknown error'
                            ];
                        }
                    }

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'kode' => $course['kode_mata_kuliah'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync course categories (prodi)
     */
    private function syncCategories()
    {
        try {
            // Get prodi from SIAKAD (using production table structure)
            $sql = "SELECT 
                        xid_sms as id_prodi, 
                        kode_prodi as kode_program_studi, 
                        nm_lemb as nama_program_studi 
                    FROM wsia_sms 
                    WHERE id_sp = (SELECT id_sp FROM wsia_satuan_pendidikan WHERE npsn = '" . NPSN . "')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $prodiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($prodiList as $prodi) {
                // Check if category exists
                $existingCategory = $this->moodle->getCategoryByIdNumber($prodi['kode_program_studi']);

                if (!$existingCategory['success']) {
                    // Create new category
                    $categoryData = [
                        'name' => $prodi['nama_program_studi'],
                        'idnumber' => $prodi['kode_program_studi'],
                        'description' => 'Program Studi: ' . $prodi['nama_program_studi'],
                        'parent' => 0
                    ];

                    $result = $this->moodle->createCategory($categoryData);

                    if ($result['success'] && isset($result['data'][0]['id'])) {
                        $categoryId = $result['data'][0]['id'];
                        $this->saveSyncMapping('category', $prodi['id_prodi'], $categoryId);
                    }
                } else {
                    // Save mapping for existing category
                    $categoryId = $existingCategory['data']['id'];
                    $this->saveSyncMapping('category', $prodi['id_prodi'], $categoryId);
                }
            }

        } catch (Exception $e) {
            error_log('Error syncing categories: ' . $e->getMessage());
        }
    }

    /**
     * Sync enrolments (kelas kuliah)
     * @param int|null $limit Limit number of records
     * @param string|null $semester Filter by semester (e.g., 20241)
     */
    public function syncEnrolments($limit = null, $semester = null, $offset = 0)
    {
        // Release session lock
        if (session_id()) {
            session_write_close();
        }

        try {
            // Use provided semester or get current active semester
            if ($semester) {
                $id_smt = $semester;
            } else {
                $sqlSemester = "SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1";
                $stmtSem = $this->db->prepare($sqlSemester);
                $stmtSem->execute();
                $semesterData = $stmtSem->fetch(PDO::FETCH_ASSOC);

                if (!$semesterData) {
                    return [
                        'success' => false,
                        'error' => 'No active semester found'
                    ];
                }

                $id_smt = $semesterData['id_smt'];
            }

            // Ensure categories (prodi) are synced first
            $this->syncCategories();

            // Get kelas kuliah from SIAKAD (using production table structure)
            $sql = "SELECT 
                        kk.xid_kls as id_kelas_kuliah,
                        kk.id_mk as id_matkul,
                        mk.kode_mk as kode_mata_kuliah,
                        kk.nm_kls as nama_kelas_kuliah,
                        kk.id_smt as id_semester,
                        s.xid_sms as id_prodi
                    FROM wsia_kelas_kuliah kk
                    INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
                    INNER JOIN wsia_sms s ON kk.id_sms = s.xid_sms
                    WHERE kk.id_smt = :id_smt";

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_smt' => $id_smt]);
            $kelasList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [
                'total' => count($kelasList),
                'success' => 0,
                'failed' => 0,
                'enrolled_users' => 0,
                'errors' => []
            ];

            // --- BULK PRE-CHECK COURSES START ---
            $courseMap = [];
            if (!empty($kelasList)) {
                $batchKeys = [];
                foreach ($kelasList as $k) {
                    $cleanK = preg_replace('/[^a-z0-9]/i', '', $k['nama_kelas_kuliah']);
                    $batchKeys[] = $k['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanK;
                }

                if (!empty($batchKeys)) {
                    $placeholders = implode(',', array_fill(0, count($batchKeys), '?'));
                    $sqlMap = "SELECT siakad_id, moodle_id FROM moodle_sync_mapping WHERE type='course' AND siakad_id IN ($placeholders)";
                    $stmtMap = $this->db->prepare($sqlMap);
                    $stmtMap->execute($batchKeys);
                    $courseMap = $stmtMap->fetchAll(PDO::FETCH_KEY_PAIR);
                }
            }
            // --- BULK PRE-CHECK COURSES END ---

            foreach ($kelasList as $kelas) {
                try {
                    // Get course ID from mapping
                    // Format: KODE-SEMESTER-KELAS
                    $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $kelas['nama_kelas_kuliah']);
                    $mappingKey = $kelas['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanKelas;
                    $courseId = $this->getCourseIdByKode($mappingKey);

                    if (!$courseId) {
                        // TRY LOCAL BULK CACHE FIRST (Calculated at start of batch)
                        $mappingKey = $kelas['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanKelas;
                        if (isset($courseMap[$mappingKey])) {
                            $courseId = $courseMap[$mappingKey];
                        } else {
                            // Fallback: Check DB Mapping directly (in case batch cache missed somehow)
                            $courseId = $this->getCourseIdByKode($mappingKey);
                        }
                    }

                    if (!$courseId) {
                        // FINAL ATTEMPT (Slow API Call) - Re-enabled as safety net
                        // Use this if Sync Courses was run but Mapping is missing
                        $moodleCourse = $this->moodle->getCourseByIdNumber($mappingKey);
                        if ($moodleCourse['success'] && !empty($moodleCourse['data']['courses'])) {
                            $courseId = $moodleCourse['data']['courses'][0]['id'];
                            $this->saveSyncMapping('course', $mappingKey, $courseId);
                        }
                    }

                    if (!$courseId) {
                        $results['failed']++;
                        $results['errors'][] = [
                            'kelas' => $kelas['nama_kelas_kuliah'],
                            'error' => 'Course not found in Moodle: ' . $mappingKey . '. HARAP JALANKAN SYNC MATA KULIAH DAHULU (Checklist Force Sync)!'
                        ];
                        continue;
                    }

                    // Get mahasiswa in this kelas (from wsia_nilai table)
                    $sqlMhs = "SELECT DISTINCT mpt.nipd as nim 
                               FROM wsia_nilai n
                               INNER JOIN wsia_mahasiswa_pt mpt ON n.xid_reg_pd = mpt.xid_reg_pd
                               WHERE n.xid_kls = :id_kelas";

                    $stmtMhs = $this->db->prepare($sqlMhs);
                    $stmtMhs->execute(['id_kelas' => $kelas['id_kelas_kuliah']]);
                    $mahasiswaList = $stmtMhs->fetchAll(PDO::FETCH_ASSOC);


                    // --- OPTIMIZATION: Bulk Get User IDs for this Class ---
                    $nimList = array_column($mahasiswaList, 'nim');
                    if (!empty($nimList)) {
                        $params = array_map(function ($nim) {
                            return is_string($nim) ? trim($nim) : $nim;
                        }, $nimList); // Trim check
                        $placeholders = implode(',', array_fill(0, count($params), '?'));

                        // 1. Check Local Mapping
                        $sqlMap = "SELECT siakad_id, moodle_id FROM moodle_sync_mapping WHERE type IN ('mahasiswa','dosen') AND siakad_id IN ($placeholders)";
                        $stmtMapUser = $this->db->prepare($sqlMap);
                        $stmtMapUser->execute($params);
                        $userMap = $stmtMapUser->fetchAll(PDO::FETCH_KEY_PAIR); // [nim => moodle_id]

                        foreach ($mahasiswaList as $mhs) {
                            $nim = trim($mhs['nim']);
                            $mhsMoodleId = isset($userMap[$nim]) ? $userMap[$nim] : null;

                            // Fallback (Only if strictly needed, but slow. Better to rely on Sync Mahasiswa first)
                            // Skip fallback here to prevent timeout. Data Mahasiswa must be synced first!

                            if ($mhsMoodleId) {
                                // Optimized: Enrol creates minimal overhead if already enrolled
                                $this->moodle->enrolUser($courseId, $mhsMoodleId, ROLE_STUDENT);
                                $results['enrolled_users']++;
                            }
                        }
                    }

                    // Get Dosen Pengajar in this kelas
                    // wsia_ajar_dosen (id_reg_ptk) -> wsia_dosen_pt (xid_reg_ptk, id_ptk) -> wsia_dosen (xid_ptk, nidn)
                    $sqlDosen = "SELECT d.nidn 
                               FROM wsia_ajar_dosen ad
                               INNER JOIN wsia_dosen_pt dp ON ad.id_reg_ptk = dp.xid_reg_ptk
                               INNER JOIN wsia_dosen d ON dp.id_ptk = d.xid_ptk
                               WHERE ad.id_kls = :id_kelas";

                    $stmtDosen = $this->db->prepare($sqlDosen);
                    $stmtDosen->execute(['id_kelas' => $kelas['id_kelas_kuliah']]);
                    $dosenList = $stmtDosen->fetchAll(PDO::FETCH_ASSOC);

                    // Enrol each dosen
                    foreach ($dosenList as $dosen) {
                        $dosenMoodleId = $this->getUserIdByIdNumber($dosen['nidn']);
                        if ($dosenMoodleId) {
                            $enrolResult = $this->moodle->enrolUser($courseId, $dosenMoodleId, ROLE_TEACHER);
                            // Dosen enrollment success is counted towards total success or separately?
                            // For now, let's just count it to ensure batch progress moves
                            if ($enrolResult['success']) {
                                $results['enrolled_users']++;
                            }
                        }
                    }

                    $results['success']++; // Class processed successfully

                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'kelas' => $kelas['nama_kelas_kuliah'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $results;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Prepare mahasiswa data for Moodle
     */
    public function prepareMahasiswaData($mhs)
    {
        $email = $this->config['email_required']
            ? $mhs['nim'] . $this->config['email_domain']
            : $mhs['nim'] . $this->config['email_domain'];

        // Generate password yang memenuhi syarat Moodle
        $password = $this->generatePassword($mhs['nim']);

        $nameParts = $this->splitName($mhs['nama']);

        $userData = [
            'username' => strtolower($mhs['nim']), // Username harus lowercase
            'password' => $password,
            'firstname' => $nameParts['firstname'],
            'lastname' => $nameParts['lastname'],
            'email' => strtolower($email), // Email harus lowercase
            'idnumber' => $mhs['nim'],
        ];

        // Custom fields hanya ditambahkan jika sudah ada di Moodle
        // Uncomment jika sudah membuat custom field di Moodle:
        // $userData['customfields'] = [
        //     ['type' => 'prodi', 'value' => $mhs['nama_program_studi']],
        //     ['type' => 'angkatan', 'value' => $mhs['angkatan']]
        // ];

        return $userData;
    }

    /**
     * Prepare dosen data for Moodle
     */
    private function prepareDosenData($dosen)
    {
        $email = $this->config['email_required']
            ? $dosen['nidn'] . $this->config['email_domain']
            : $dosen['nidn'] . $this->config['email_domain'];

        // Generate password
        $password = $this->generatePassword($dosen['nidn']);

        $nameParts = $this->splitName($dosen['nama_dosen']);

        $userData = [
            'username' => strtolower($dosen['nidn']), // Username lowercase
            'password' => $password,
            'firstname' => $nameParts['firstname'],
            'lastname' => $nameParts['lastname'],
            'email' => strtolower($email), // Email lowercase
            'idnumber' => $dosen['nidn'],
        ];

        // Custom fields hanya ditambahkan jika sudah ada di Moodle
        // Uncomment jika sudah membuat custom field di Moodle:
        // $userData['customfields'] = [
        //     ['type' => 'prodi', 'value' => $dosen['nama_program_studi']]
        // ];

        return $userData;
    }

    /**
     * Prepare course data for Moodle
     * @param array $course Course data from SIAKAD
     * @param int $categoryId Moodle category ID
     * @param string|null $semester Semester ID (e.g., 20241)
     */
    private function prepareCourseData($course, $categoryId, $id_smt)
    {
        // Unique Identifier: KODE-SEMESTER-KELAS
        $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $course['nama_kelas_kuliah']);
        $idnumber = $course['kode_mata_kuliah'] . '-' . $id_smt . '-' . $cleanKelas;

        $fullname = $course['nama_mata_kuliah'] . ' - ' . $course['nama_kelas_kuliah'];
        $shortname = $course['kode_mata_kuliah'] . ' ' . $course['nama_kelas_kuliah'];

        return [
            'fullname' => $fullname,
            'shortname' => $shortname,
            'categoryid' => $categoryId,
            'idnumber' => $idnumber,
            'summary' => sprintf(
                'Mata Kuliah: %s<br>Kelas: %s<br>Kode: %s<br>SKS: %s<br>Semester: %s<br>Program Studi: %s',
                $course['nama_mata_kuliah'],
                $course['nama_kelas_kuliah'],
                $course['kode_mata_kuliah'],
                $course['sks_mata_kuliah'],
                $this->formatSemester($id_smt), // Assuming formatSemester exists or use $id_smt
                $course['nama_program_studi']
            ),
            'numsections' => 14 // Default 14 minggu
        ];
    }

    /**
     * Get current active semester
     */
    private function getCurrentSemester()
    {
        try {
            $sql = "SELECT id_smt FROM wsia_semester WHERE a_periode_aktif = '1' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['id_smt'] ?? date('Y') . '1';
        } catch (Exception $e) {
            // Fallback to current year + semester 1
            return date('Y') . '1';
        }
    }

    /**
     * Format semester ID to readable format
     * Example: 20241 -> 2024/2025 Ganjil
     */
    private function formatSemester($id_smt)
    {
        $tahun = substr($id_smt, 0, 4);
        $tahun_next = $tahun + 1;
        $smt = substr($id_smt, 4, 1);

        $smt_name = [
            '1' => 'Ganjil',
            '2' => 'Genap',
            '3' => 'Pendek'
        ];

        return $tahun . '/' . $tahun_next . ' ' . ($smt_name[$smt] ?? 'Unknown');
    }

    /**
     * Sync grades from Moodle back to SIAKAD
     * @param string|null $semester Semester ID (e.g., 20241)
     * @param int|null $limit Limit number of courses
     */
    public function syncGrades($semester = null, $limit = null, $offset = 0)
    {
        // Release session lock
        if (session_id()) {
            session_write_close();
        }

        try {
            $id_smt = $semester ?: $this->getCurrentSemester();

            // Get synced courses for this semester from mapping
            // siakad_id format: KODE-SEMESTER-KELAS
            $sql = "SELECT m.siakad_id, m.moodle_id, kk.xid_kls 
                    FROM moodle_sync_mapping m
                    INNER JOIN wsia_kelas_kuliah kk ON m.siakad_id LIKE CONCAT('%-', :id_smt, '-%')
                    INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
                    WHERE m.type = 'course' 
                    AND m.siakad_id = CONCAT(mk.kode_mk, '-', kk.id_smt, '-', kk.nm_kls)
                    AND kk.id_smt = :id_smt2";
            
            // Simplified query if mapping is reliable
            $sql = "SELECT siakad_id, moodle_id FROM moodle_sync_mapping 
                    WHERE type='course' AND siakad_id LIKE :sem_pattern";

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['sem_pattern' => '%-' . $id_smt . '-%']);
            $syncedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [
                'total_courses' => count($syncedCourses),
                'success_courses' => 0,
                'total_grades_updated' => 0,
                'errors' => []
            ];

            foreach ($syncedCourses as $course) {
                try {
                    // Get grades from Moodle
                    $moodleRes = $this->moodle->getCourseGrades($course['moodle_id']);

                    if (!$moodleRes['success']) {
                        $results['errors'][] = [
                            'course' => $course['siakad_id'],
                            'error' => $moodleRes['error']
                        ];
                        continue;
                    }

                    $userGrades = $moodleRes['data']['usergrades'] ?? [];
                    $courseGradesUpdated = 0;

                    // Parse siakad_id (KODE-SEMESTER-KELAS) to get xid_kls
                    $parts = explode('-', $course['siakad_id']);
                    $kode_mk = $parts[0];
                    // parts[1] is semester
                    $nm_kls = end($parts); // Last part is class name

                    $sqlKls = "SELECT xid_kls FROM wsia_kelas_kuliah kk 
                             INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk 
                             WHERE mk.kode_mk = :kode AND kk.id_smt = :smt AND kk.nm_kls = :kls LIMIT 1";
                    $stmtKls = $this->db->prepare($sqlKls);
                    $stmtKls->execute(['kode' => $kode_mk, 'smt' => $id_smt, 'kls' => $nm_kls]);
                    $klsData = $stmtKls->fetch(PDO::FETCH_ASSOC);

                    if (!$klsData) {
                        $results['errors'][] = [
                            'course' => $course['siakad_id'],
                            'error' => 'Could not find SIAKAD xid_kls for this course mapping'
                        ];
                        continue;
                    }

                    $xid_kls = $klsData['xid_kls'];

                    foreach ($userGrades as $uGrade) {
                        $moodleUserId = $uGrade['userid'];
                        
                        // Find Grade Item that is "Course total"
                        $finalGradeValue = null;
                        foreach ($uGrade['gradeitems'] as $item) {
                            if ($item['itemtype'] == 'course') {
                                $finalGradeValue = $item['graderaw'] ?? null;
                                break;
                            }
                        }

                        if ($finalGradeValue === null) continue;

                        // Map Moodle User back to SIAKAD Mahasiswa
                        $sqlUser = "SELECT siakad_id FROM moodle_sync_mapping 
                                  WHERE type = 'mahasiswa' AND moodle_id = :mid LIMIT 1";
                        $stmtUser = $this->db->prepare($sqlUser);
                        $stmtUser->execute(['mid' => $moodleUserId]);
                        $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

                        if (!$userData) continue;

                        $nim = $userData['siakad_id'];

                        // Get xid_reg_pd and id_sms from NIM
                        $sqlReg = "SELECT xid_reg_pd, id_sms FROM wsia_mahasiswa_pt WHERE nipd = :nim LIMIT 1";
                        $stmtReg = $this->db->prepare($sqlReg);
                        $stmtReg->execute(['nim' => $nim]);
                        $regData = $stmtReg->fetch(PDO::FETCH_ASSOC);

                        if (!$regData) continue;

                        $xid_reg_pd = $regData['xid_reg_pd'];
                        $id_sms = $regData['id_sms'];
                        $id_nilai = md5($xid_kls . $xid_reg_pd);

                        // Calculate Letter and Index
                        $gradeItem = $this->getGradeItem($finalGradeValue, $id_sms);

                        // Update SIAKAD Grade
                        $sqlUpdate = "UPDATE wsia_nilai SET 
                                    nilai_angka = :nilai,
                                    nilai_huruf = :huruf,
                                    nilai_indeks = :indeks,
                                    updated_at = NOW(),
                                    nilai_tampil = '2' 
                                    WHERE id_nilai = :id_nilai";
                        
                        $stmtUpdate = $this->db->prepare($sqlUpdate);
                        $stmtUpdate->execute([
                            'nilai' => $finalGradeValue,
                            'huruf' => $gradeItem['letter'],
                            'indeks' => $gradeItem['index'],
                            'id_nilai' => $id_nilai
                        ]);

                        if ($stmtUpdate->rowCount() > 0) {
                            $courseGradesUpdated++;
                            $results['total_grades_updated']++;
                        }
                    }

                    $results['success_courses']++;

                } catch (Exception $e) {
                    $results['errors'][] = [
                        'course' => $course['siakad_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->logSync('sync_grades', $results);
            return $results;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Split name into firstname and lastname
     */
    private function splitName($fullName)
    {
        $fullName = trim($fullName);
        if (empty($fullName)) {
            return ['firstname' => 'User', 'lastname' => 'SIAKAD'];
        }

        $parts = explode(' ', $fullName, 2);

        return [
            'firstname' => trim($parts[0]),
            'lastname' => isset($parts[1]) ? trim($parts[1]) : '-'
        ];
    }
    /**
     * Generate password yang memenuhi syarat Moodle
     * Syarat: min 8 karakter, huruf besar, huruf kecil, angka, simbol
     */
    private function generatePassword($idNumber)
    {
        // Format: NIM + @Siakad (contoh: c23001@Siakad)
        // Ini memenuhi semua syarat Moodle:
        // - Huruf kecil dari NIM
        // - Huruf besar (S)
        // - Simbol (@)
        // - Angka dari NIM
        return strtolower($idNumber) . '@Siakad';
    }

    /**
     * Save sync mapping
     */
    private function saveSyncMapping($type, $siakadId, $moodleId)
    {
        try {
            $sql = "INSERT INTO moodle_sync_mapping 
                    (type, siakad_id, moodle_id, last_sync) 
                    VALUES (:type, :siakad_id, :moodle_id, NOW())
                    ON DUPLICATE KEY UPDATE 
                    moodle_id = :moodle_id, 
                    last_sync = NOW()";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'type' => $type,
                'siakad_id' => $siakadId,
                'moodle_id' => $moodleId
            ]);

        } catch (Exception $e) {
            error_log('Error saving sync mapping: ' . $e->getMessage());
        }
    }

    /**
     * Get Moodle user ID by ID number
     */
    private function getUserIdByIdNumber($idNumber)
    {
        try {
            $sql = "SELECT moodle_id FROM moodle_sync_mapping 
                    WHERE type IN ('mahasiswa', 'dosen') 
                    AND siakad_id = :idnumber";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idnumber' => $idNumber]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return $result['moodle_id'];
            }

            // Fallback: Check Moodle directly
            $moodleUser = $this->moodle->getUserByIdNumber($idNumber);
            if ($moodleUser['success'] && !empty($moodleUser['data']['users'])) {
                return $moodleUser['data']['users'][0]['id'];
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get Moodle course ID by kode mata kuliah
     */
    private function getCourseIdByKode($kode)
    {
        try {
            $sql = "SELECT moodle_id FROM moodle_sync_mapping 
                    WHERE type = 'course' 
                    AND siakad_id = :kode";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['kode' => $kode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['moodle_id'] : null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get Moodle category ID by prodi ID
     */
    private function getCategoryIdByProdi($prodiId)
    {
        try {
            $sql = "SELECT moodle_id FROM moodle_sync_mapping 
                    WHERE type = 'category' 
                    AND siakad_id = :prodi_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['prodi_id' => $prodiId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['moodle_id'] : null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get grade letter and index from numeric value
     * @param float $score
     * @param string $id_sms Prodi ID
     * @return array [letter, index]
     */
    private function getGradeItem($score, $id_sms)
    {
        try {
            // Find appropriate grade from weights table
            // Siakad usually has different weights per prodi
            $sql = "SELECT nilai_huruf, nilai_indeks 
                    FROM wsia_bobot_nilai 
                    WHERE id_sms = :id_sms 
                    AND :score >= bobot_nilai_min 
                    AND :score <= bobot_nilai_maks 
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_sms' => $id_sms, 'score' => $score]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return [
                    'letter' => $result['nilai_huruf'],
                    'index' => $result['nilai_indeks']
                ];
            }

            // Fallback to default if prodi specific grade not found
            $sqlDef = "SELECT nilai_huruf, nilai_indeks 
                       FROM wsia_bobot_nilai 
                       WHERE :score >= bobot_nilai_min 
                       AND :score <= bobot_nilai_maks 
                       ORDER BY id_sms ASC LIMIT 1";
            $stmtDef = $this->db->prepare($sqlDef);
            $stmtDef->execute(['score' => $score]);
            $resultDef = $stmtDef->fetch(PDO::FETCH_ASSOC);

            return [
                'letter' => $resultDef['nilai_huruf'] ?? '',
                'index' => $resultDef['nilai_indeks'] ?? 0
            ];

        } catch (Exception $e) {
            return ['letter' => '', 'index' => 0];
        }
    }

    /**
     * Log sync activity
     */
    private function logSync($action, $results)
    {
        $logData = json_encode([
            'time' => date('Y-m-d H:i:s'),
            'action' => $action,
            'results' => $results
        ], JSON_PRETTY_PRINT);

        // Backup log to file for easier debugging
        file_put_contents(__DIR__ . '/moodle_sync.log', $logData . PHP_EOL . "---" . PHP_EOL, FILE_APPEND);

        try {
            $sql = "INSERT INTO moodle_sync_log 
                    (action, results, created_at) 
                    VALUES (:action, :results, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'action' => $action,
                'results' => json_encode($results)
            ]);

        } catch (Exception $e) {
            error_log('Error logging sync: ' . $e->getMessage());
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats()
    {
        try {
            $stats = [];

            // Count mappings
            $sql = "SELECT type, COUNT(*) as count 
                    FROM moodle_sync_mapping 
                    GROUP BY type";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($mappings as $mapping) {
                $stats[$mapping['type']] = $mapping['count'];
            }

            // Get last sync time
            $sql = "SELECT MAX(created_at) as last_sync 
                    FROM moodle_sync_log";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $lastSync = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats['last_sync'] = $lastSync['last_sync'];

            return $stats;

        } catch (Exception $e) {
            return [];
        }
    }


    /**
     * Archive (Hide) courses for a specific semester
     */
    public function archiveSemesterCourses($semesterId, $limit = 20, $offset = 0)
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        try {
            // Get classes for this semester
            $sql = "SELECT DISTINCT mk.kode_mk as kode_mata_kuliah, kk.nm_kls as nama_kelas_kuliah
                    FROM wsia_kelas_kuliah kk
                    INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
                    WHERE kk.id_smt = " . $this->db->quote($semesterId);

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($classes as $kelas) {
                // Mapping key format: KODE_MK-SEMESTER-KELAS
                $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $kelas['nama_kelas_kuliah']);
                $mappingKey = $kelas['kode_mata_kuliah'] . '-' . $semesterId . '-' . $cleanKelas;
                $courseId = $this->getCourseIdByKode($mappingKey);

                if ($courseId) {
                    // Update course visibility to 0 (Hidden)
                    $updateRes = $this->moodle->updateCourse($courseId, ['visible' => 0]);

                    if ($updateRes['success']) {
                        $results['success']++;
                    } else {
                        $results['failed']++;
                        $results['errors'][] = [
                            'kode' => $kelas['kode_mata_kuliah'],
                            'error' => $updateRes['error'] ?? 'Unknown error'
                        ];
                    }
                } else {
                    // Course not synced yet, technically success (already effectively hidden/non-existent)
                    $results['success']++;
                }
            }

        } catch (Exception $e) {
            $results['failed']++;
            $results['errors'][] = ['error' => $e->getMessage()];
        }

        return $results;
    }

    /**
     * Delete old courses (generic KODE-SEMESTER without class suffix)
     * Used to cleanup duplicates after migrating to Class-Based Courses
     */
    public function deleteOldSemesterCourses($semesterId, $limit = 20, $offset = 0)
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        try {
            // Get unique course keys (Kode MK & Class Name) for this semester
            $sql = "SELECT DISTINCT mk.kode_mk as kode_mata_kuliah, kk.nm_kls as nama_kelas_kuliah
                    FROM wsia_kelas_kuliah kk
                    INNER JOIN wsia_mata_kuliah mk ON kk.id_mk = mk.xid_mk
                    WHERE kk.id_smt = " . $this->db->quote($semesterId);

            if ($limit) {
                $sql .= " LIMIT " . intval($limit);
                if ($offset > 0) {
                    $sql .= " OFFSET " . intval($offset);
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($courses as $course) {
                // Key format lama: KODE_MK-SEMESTER (tanpa kelas)
                $cleanKode = trim($course['kode_mata_kuliah']);
                $mappingKey = $cleanKode . '-' . $semesterId;
                $courseId = 0;



                // 1. Cek Mapping Lokal
                $localId = $this->getCourseIdByKode($mappingKey);

                if ($localId) {
                    $courseId = $localId;
                } else {
                    // 2. Fallback: Cek Moodle langsung by IDNumber
                    try {
                        $moodleCourses = $this->moodle->getCourseByIdNumber($mappingKey);
                        if ($moodleCourses['success'] && !empty($moodleCourses['data']['courses'])) {
                            $courseId = $moodleCourses['data']['courses'][0]['id'];
                        } else {
                            // 2b. Try removing spaces from Code? (e.g. "TI 101" -> "TI101")
                            $noSpaceKey = str_replace(' ', '', $cleanKode) . '-' . $semesterId;
                            if ($noSpaceKey !== $mappingKey) {
                                $mc2 = $this->moodle->getCourseByIdNumber($noSpaceKey);
                                if ($mc2['success'] && !empty($mc2['data']['courses'])) {
                                    $courseId = $mc2['data']['courses'][0]['id'];

                                    $courseId = $mc2['data']['courses'][0]['id'];

                                }
                            }

                            // 2c. CHECK FOR COURSES WITHOUT SEMESTER ID (Pattern: CODE-CLASS only)
                            // User Request: "salah format tahun tidak ada" -> KODE-KELAS (e.g. TRPLIP5301-23C)
                            if (!$courseId) {
                                $cleanKelas = preg_replace('/[^a-z0-9]/i', '', $course['nama_kelas_kuliah']);
                                $wrongKey = $cleanKode . '-' . $cleanKelas;
                                $mc3 = $this->moodle->getCourseByIdNumber($wrongKey);
                                if ($mc3['success'] && !empty($mc3['data']['courses'])) {
                                    $courseId = $mc3['data']['courses'][0]['id'];
                                    // Treat this key as the one to delete
                                    $mappingKey = $wrongKey;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // Ignore lookup error
                    }
                }

                if ($courseId) {
                    // Hapus dari Moodle
                    try {
                        $delResWrapper = $this->moodle->deleteCourse($courseId);

                        if ($delResWrapper['success']) {
                            $delRes = $delResWrapper['data']; // Unwrap data

                            // Moodle delete usually returns null on success, or warnings on failure/partial
                            if (empty($delRes) || (isset($delRes['warnings']) && empty($delRes['warnings']))) {
                                $results['success']++;
                            } elseif (isset($delRes['warnings']) && !empty($delRes['warnings'])) {
                                $warnStr = json_encode($delRes['warnings']);
                                if (strpos($warnStr, 'unknown') !== false) {
                                    $results['success']++;
                                } else {
                                    $results['failed']++;
                                    $results['errors'][] = ['kode' => $cleanKode, 'err' => $warnStr];
                                }
                            }
                        } else {
                            $results['failed']++;
                            $results['errors'][] = ['kode' => $cleanKode, 'err' => $delResWrapper['error'] ?? 'Unknown API error'];
                        }
                    } catch (Exception $e) {
                        $results['failed']++;
                    }

                    // Always clean up mapping
                    $keysToDelete = [$mappingKey, str_replace(' ', '', $cleanKode) . '-' . $semesterId];
                    foreach (array_unique($keysToDelete) as $kdel) {
                        $dsql = "DELETE FROM moodle_sync_mapping WHERE type='course' AND siakad_id = :key";
                        $dstmt = $this->db->prepare($dsql);
                        $dstmt->execute(['key' => $kdel]);
                    }
                }
            }

        } catch (Exception $e) {
            $results['failed']++;
            $results['errors'][] = ['error' => $e->getMessage()];
        }

        return $results;
    }
}
