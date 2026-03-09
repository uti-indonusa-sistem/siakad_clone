<?php
/**
 * Moodle Web Service Client
 * Handle all communication with Moodle Web Service API
 * 
 * @version 1.0.0
 * @date 2026-01-21
 */

require_once __DIR__ . '/../config/moodle_config.php';

class MoodleWebService
{
    private $wsUrl;
    private $token;
    private $format;
    private $debug;

    public function __construct()
    {
        $config = getMoodleConfig();
        $this->wsUrl = $config['ws_url'];
        $this->token = $config['token'];
        $this->format = $config['format'];
        $this->debug = $config['debug'];
    }

    /**
     * Make API call to Moodle Web Service
     */
    private function call($function, $params = [])
    {
        try {
            $url = $this->wsUrl . '?' . http_build_query([
                'wstoken' => $this->token,
                'wsfunction' => $function,
                'moodlewsrestformat' => $this->format
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Timeout connecting (3-5s)
            curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Total timeout (15s)

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("cURL Error: " . $error);
            }

            if ($httpCode !== 200) {
                throw new Exception("HTTP Error: " . $httpCode);
            }

            $result = json_decode($response, true);

            if (isset($result['exception'])) {
                // Include more details from Moodle error
                $errorMsg = "Moodle Error: " . $result['message'];
                if (isset($result['debuginfo'])) {
                    $errorMsg .= " | Debug: " . $result['debuginfo'];
                }
                if (isset($result['errorcode'])) {
                    $errorMsg .= " | Code: " . $result['errorcode'];
                }

                // Log the params that caused the error for debugging
                if ($this->debug) {
                    $this->logError($function, $params, $errorMsg);
                }

                throw new Exception($errorMsg);
            }

            if ($this->debug) {
                $this->logDebug($function, $params, $result);
            }

            return [
                'success' => true,
                'data' => $result
            ];

        } catch (Exception $e) {
            if ($this->debug) {
                $this->logError($function, $params, $e->getMessage());
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sent_data' => $params // Include what was sent for debugging
            ];
        }
    }

    /**
     * Create user in Moodle
     */
    public function createUser($userData)
    {
        $params = [
            'users[0][username]' => $userData['username'],
            'users[0][password]' => $userData['password'],
            'users[0][firstname]' => $userData['firstname'],
            'users[0][lastname]' => $userData['lastname'],
            'users[0][email]' => $userData['email'],
            'users[0][auth]' => 'manual',
            'users[0][idnumber]' => $userData['idnumber'] ?? '',
            // 'lang' dan 'timezone' sering menyebabkan invalidparameter jika format/isinya tidak sesuai di Moodle tertentu
            // Gunakan nilai default yang paling aman
            'users[0][lang]' => 'en',
            'users[0][timezone]' => '99'
        ];

        // Add custom fields if provided
        if (isset($userData['customfields'])) {
            $i = 0;
            foreach ($userData['customfields'] as $field) {
                $params["users[0][customfields][$i][type]"] = $field['type'];
                $params["users[0][customfields][$i][value]"] = $field['value'];
                $i++;
            }
        }

        return $this->call('core_user_create_users', $params);
    }

    /**
     * Update user in Moodle
     */
    public function updateUser($userId, $userData)
    {
        $params = [
            'users[0][id]' => $userId,
            'users[0][username]' => $userData['username'],
            'users[0][firstname]' => $userData['firstname'],
            'users[0][lastname]' => $userData['lastname'],
            'users[0][email]' => $userData['email']
        ];

        if (isset($userData['password'])) {
            $params['users[0][password]'] = $userData['password'];
        }

        if (isset($userData['idnumber'])) {
            $params['users[0][idnumber]'] = $userData['idnumber'];
        }

        return $this->call('core_user_update_users', $params);
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        $params = [
            'criteria[0][key]' => 'username',
            'criteria[0][value]' => $username
        ];

        return $this->call('core_user_get_users', $params);
    }

    /**
     * Get user by ID number (NIM/NIDN)
     */
    public function getUserByIdNumber($idnumber)
    {
        $params = [
            'criteria[0][key]' => 'idnumber',
            'criteria[0][value]' => $idnumber
        ];

        return $this->call('core_user_get_users', $params);
    }

    /**
     * Create course in Moodle
     */
    public function createCourse($courseData)
    {
        $params = [
            'courses[0][fullname]' => $courseData['fullname'],
            'courses[0][shortname]' => $courseData['shortname'],
            'courses[0][categoryid]' => $courseData['categoryid'],
            'courses[0][idnumber]' => $courseData['idnumber'] ?? '',
            'courses[0][summary]' => $courseData['summary'] ?? '',
            'courses[0][summaryformat]' => 1,
            'courses[0][format]' => 'topics',
            'courses[0][showgrades]' => 1,
            'courses[0][newsitems]' => 5,
            'courses[0][startdate]' => $courseData['startdate'] ?? time(),
            'courses[0][enddate]' => $courseData['enddate'] ?? 0,
            'courses[0][numsections]' => $courseData['numsections'] ?? 10,
            'courses[0][maxbytes]' => 0,
            'courses[0][showreports]' => 0,
            'courses[0][visible]' => 1,
            'courses[0][hiddensections]' => 0,
            'courses[0][groupmode]' => 0,
            'courses[0][groupmodeforce]' => 0,
            'courses[0][defaultgroupingid]' => 0,
            'courses[0][enablecompletion]' => 1,
            'courses[0][completionnotify]' => 0,
            'courses[0][forcetheme]' => ''
        ];

        return $this->call('core_course_create_courses', $params);
    }

    /**
     * Update course in Moodle
     */
    public function updateCourse($courseId, $courseData)
    {
        $params = [
            'courses[0][id]' => $courseId
        ];

        // List of supported fields to update
        $supportedFields = [
            'fullname',
            'shortname',
            'categoryid',
            'idnumber',
            'summary',
            'visible',
            'startdate',
            'enddate',
            'groupmode',
            'groupmodeforce'
        ];

        foreach ($supportedFields as $field) {
            if (isset($courseData[$field])) {
                $params["courses[0][$field]"] = $courseData[$field];
            }
        }

        return $this->call('core_course_update_courses', $params);
    }

    public function deleteCourse($courseIds)
    {
        // Accept single ID or array
        if (!is_array($courseIds)) {
            $courseIds = [$courseIds];
        }

        $params = [
            'courseids' => $courseIds
        ];

        return $this->call('core_course_delete_courses', $params);
    }

    /**
     * Get course by ID number
     */
    public function getCourseByIdNumber($idnumber)
    {
        $params = [
            'field' => 'idnumber',
            'value' => $idnumber
        ];

        return $this->call('core_course_get_courses_by_field', $params);
    }

    /**
     * Enrol user to course
     */
    public function enrolUser($courseId, $userId, $roleId)
    {
        $params = [
            'enrolments[0][roleid]' => $roleId,
            'enrolments[0][userid]' => $userId,
            'enrolments[0][courseid]' => $courseId,
            'enrolments[0][timestart]' => time(),
            'enrolments[0][timeend]' => 0,
            'enrolments[0][suspend]' => 0
        ];

        return $this->call('enrol_manual_enrol_users', $params);
    }

    /**
     * Unenrol user from course
     */
    public function unenrolUser($courseId, $userId)
    {
        $params = [
            'enrolments[0][userid]' => $userId,
            'enrolments[0][courseid]' => $courseId
        ];

        return $this->call('enrol_manual_unenrol_users', $params);
    }

    /**
     * Get enrolled users in course
     */
    public function getEnrolledUsers($courseId)
    {
        $params = [
            'courseid' => $courseId
        ];

        return $this->call('core_enrol_get_enrolled_users', $params);
    }

    /**
     * Get grades for a course (Course Total)
     * @param int $courseId Moodle Course ID
     * @return array
     */
    public function getCourseGrades($courseId)
    {
        $params = [
            'courseid' => $courseId
        ];

        return $this->call('gradereport_user_get_grade_items', $params);
    }

    /**
     * Get specific grade items for a course
     * @param int $courseId Moodle Course ID
     * @return array
     */
    public function getGradeItems($courseId)
    {
        $params = [
            'courseid' => $courseId
        ];

        return $this->call('core_grades_get_grade_items', $params);
    }

    /**
     * Create course category
     */
    public function createCategory($categoryData)
    {
        $params = [
            'categories[0][name]' => $categoryData['name'],
            'categories[0][parent]' => $categoryData['parent'] ?? 0,
            'categories[0][idnumber]' => $categoryData['idnumber'] ?? '',
            'categories[0][description]' => $categoryData['description'] ?? '',
            'categories[0][descriptionformat]' => 1
        ];

        return $this->call('core_course_create_categories', $params);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        return $this->call('core_course_get_categories', []);
    }

    /**
     * Get category by ID number
     */
    public function getCategoryByIdNumber($idnumber)
    {
        $result = $this->getCategories();

        if ($result['success'] && isset($result['data'])) {
            foreach ($result['data'] as $category) {
                if (isset($category['idnumber']) && $category['idnumber'] === $idnumber) {
                    return [
                        'success' => true,
                        'data' => $category
                    ];
                }
            }
        }

        return [
            'success' => false,
            'error' => 'Category not found'
        ];
    }

    /**
     * Test connection to Moodle
     */
    public function testConnection()
    {
        return $this->call('core_webservice_get_site_info', []);
    }

    /**
     * Log debug information
     */
    private function logDebug($function, $params, $result)
    {
        $logFile = __DIR__ . '/../logs/moodle_debug.log';
        $logDir = dirname($logFile);

        if (!@is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'function' => $function,
            'params' => $params,
            'result' => $result
        ];

        @file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );
    }

    /**
     * Log error information
     */
    private function logError($function, $params, $error)
    {
        $logFile = __DIR__ . '/../logs/moodle_error.log';
        $logDir = dirname($logFile);

        if (!@is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'function' => $function,
            'params' => $params,
            'error' => $error
        ];

        @file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );
    }
}
