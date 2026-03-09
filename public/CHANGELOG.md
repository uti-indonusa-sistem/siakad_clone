# CHANGELOG - Perbaikan Keamanan SIAKAD

## [Security Update 1.0.0] - 2025-10-14

### 🔴 CRITICAL SECURITY FIXES

#### 1. Database Credentials Protection
**File:** `config/config.php`  
**Masalah:** Password database terekspos dalam plaintext di source code

**Perubahan:**
- ✅ Memindahkan semua credentials ke file `.env`
- ✅ Menggunakan `vlucas/phpdotenv` untuk load environment variables
- ✅ Menambahkan error handling untuk missing configuration
- ✅ Menambahkan `PDO::ATTR_EMULATE_PREPARES => false` untuk force prepared statements
- ✅ Menambahkan proper UTF-8 character set configuration

**Files Modified:**
- `config/config.php` (UPDATED - COMPLETELY REWRITTEN)

**New Files:**
- `.env` (CREATED - contains actual credentials, do NOT commit to git)
- `.env.example` (CREATED - template untuk setup baru)
- `.gitignore` (CREATED - to protect sensitive files)

---

#### 2. SQL Injection Prevention
**File:** `baak/index.php`  
**Masalah:** SQL injection vulnerability di login dan routing

**Perubahan:**
- ✅ Mengganti string interpolation dengan prepared statements + parameter binding
- ✅ Menambahkan input validation untuk username
- ✅ Menambahkan whitelist untuk API routes
- ✅ Menambahkan path validation untuk prevent directory traversal

**Before:**
```php
$perintah = "select * from wsia_user where (username='$user' and md5(password)='$epass')";
$qry = $db->prepare($perintah);
$qry->execute();
```

**After:**
```php
$perintah = "SELECT * FROM wsia_user WHERE username = :username LIMIT 1";
$qry = $db->prepare($perintah);
$qry->execute([':username' => $user]);
```

**Files Modified:**
- `baak/index.php` (UPDATED - COMPLETELY REWRITTEN)

---

#### 3. Weak Password Hashing
**File:** `baak/index.php`  
**Masalah:** Menggunakan MD5 dan SHA1 yang sudah tidak aman

**Perubahan:**
- ✅ Menambahkan support untuk Argon2ID password hashing
- ✅ Menambahkan backward compatibility untuk legacy passwords
- ✅ Auto-upgrade password ke format baru saat login
- ✅ Menambahkan password verification yang proper

**Before:**
```php
$pass = crypt(md5(clean($data->pass)),"$1$".$user);
$epass = md5($vpass);
```

**After:**
```php
if (password_verify($password, $userData->password)) {
    // Login success
} else {
    // Fallback to legacy for migration
    if (Security::verifyLegacyPassword($password, $user, md5($userData->password))) {
        // Auto-upgrade
        $newHash = Security::hashPassword($password);
        // Update database
    }
}
```

**Files Modified:**
- `baak/index.php` (UPDATED)
- `lib/security.php` (CREATED - Security helper functions)

---

#### 4. Weak Session Management
**File:** `baak/index.php`  
**Masalah:** Session menggunakan hardcoded salt dan tidak ada timeout

**Perubahan:**
- ✅ Mengganti hardcoded salt dengan secret key dari environment
- ✅ Menambahkan session timeout (30 menit)
- ✅ Menambahkan IP address validation
- ✅ Menggunakan secure random untuk session ID
- ✅ Menambahkan secure session configuration (httponly, secure, samesite)

**Before:**
```php
$sessionID=sha1("com.sopingi.permata:".date('YmdHis'));
$_SESSION[$sessionID]=crypt($sessionID,"$1$".md5($sessionID."sopingi"));
```

**After:**
```php
$sessionID = Security::generateSessionID(); // bin2hex(random_bytes(32))
$_SESSION[$sessionID] = hash_hmac('sha256', $sessionID, $_ENV['SESSION_SECRET']);
$_SESSION['login_time'] = time();
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
```

**Files Modified:**
- `baak/index.php` (UPDATED)

---

#### 5. Arbitrary File Inclusion
**File:** `baak/index.php`  
**Masalah:** API routing vulnerable to directory traversal

**Perubahan:**
- ✅ Menambahkan whitelist untuk allowed API files
- ✅ Menambahkan regex validation untuk API names
- ✅ Menambahkan file existence check
- ✅ Menambahkan security logging untuk unauthorized access

**Before:**
```php
$api = $request->getAttribute('api');
return $this->renderer->render($response, "/../api/".$api.".php",$param);
```

**After:**
```php
$allowedApis = ['mahasiswa', 'dosen', 'nilai', /* ... */];
if (!preg_match('/^[a-zA-Z0-9_]+$/', $api)) {
    return $response->withStatus(400)->write('Invalid API name');
}
if (!in_array($api, $allowedApis)) {
    return $response->withStatus(403)->write('Forbidden');
}
// Then render
```

**Files Modified:**
- `baak/index.php` (UPDATED)

---

### 🟠 HIGH SECURITY IMPROVEMENTS

#### 6. Security Helper Functions
**File:** `lib/security.php` (NEW)

**Perubahan:**
- ✅ Created comprehensive security helper class
- ✅ Input validation functions (email, phone, NIM, NIDN, date, etc)
- ✅ CSRF token generation and verification
- ✅ Rate limiting implementation
- ✅ Session validation with timeout
- ✅ Password hashing functions (new + legacy)
- ✅ File upload validation
- ✅ Security event logging
- ✅ Filename sanitization

**Functions Added:**
- `Security::sanitizeString()` - Clean HTML and special chars
- `Security::validateEmail()` - Email format validation
- `Security::validatePhone()` - Indonesian phone format
- `Security::validateNIM()` - NIM format validation
- `Security::validateNIDN()` - NIDN format validation
- `Security::generateCSRFToken()` - CSRF protection
- `Security::verifyCSRFToken()` - CSRF verification
- `Security::checkRateLimit()` - Rate limiting
- `Security::validateSession()` - Session security check
- `Security::hashPassword()` - Argon2ID password hashing
- `Security::verifyPassword()` - Password verification
- `Security::validateFileUpload()` - File upload security
- `Security::logSecurityEvent()` - Security audit logging
- `Security::forceHTTPS()` - HTTPS enforcement
- `Security::setSecurityHeaders()` - Security headers

**Files Created:**
- `lib/security.php` (NEW - 600+ lines of security functions)

---

#### 7. HTTPS Enforcement
**File:** `baak/index.php`

**Perubahan:**
- ✅ Force HTTPS redirect di awal file
- ✅ Menambahkan HSTS header
- ✅ Menambahkan secure cookie configuration

**Files Modified:**
- `baak/index.php` (UPDATED)

---

#### 8. Security Headers
**File:** `baak/index.php`

**Perubahan:**
- ✅ X-Frame-Options: DENY
- ✅ X-Content-Type-Options: nosniff
- ✅ X-XSS-Protection: 1; mode=block
- ✅ Referrer-Policy: strict-origin-when-cross-origin
- ✅ Content-Security-Policy
- ✅ Strict-Transport-Security

**Files Modified:**
- `baak/index.php` (UPDATED)

---

#### 9. Rate Limiting
**File:** `baak/index.php` + `lib/security.php`

**Perubahan:**
- ✅ Implementasi rate limiting untuk login (5 attempts per 15 minutes)
- ✅ Session-based rate limit storage
- ✅ Per-user and per-IP tracking
- ✅ Automatic cleanup of old attempts

**Files Modified:**
- `baak/index.php` (UPDATED)
- `lib/security.php` (CREATED)

---

#### 10. Security Logging
**File:** `lib/security.php`

**Perubahan:**
- ✅ Logging untuk failed login attempts
- ✅ Logging untuk unauthorized API access
- ✅ Logging untuk security events
- ✅ Include IP address, timestamp, username in logs
- ✅ Support untuk separate security.log file

**Files Modified:**
- `lib/security.php` (CREATED)
- `baak/index.php` (UPDATED - menambahkan log calls)

---

### 🟡 MEDIUM SECURITY IMPROVEMENTS

#### 11. Error Handling
**File:** `config/config.php`, `baak/index.php`

**Perubahan:**
- ✅ Disable error display in production (controlled by .env)
- ✅ Proper exception handling dengan try-catch
- ✅ Generic error messages untuk user
- ✅ Detailed logging untuk admin

**Files Modified:**
- `config/config.php` (UPDATED)
- `baak/index.php` (UPDATED)

---

#### 12. .gitignore Implementation
**File:** `.gitignore` (NEW)

**Perubahan:**
- ✅ Ignore `.env` file
- ✅ Ignore vendor directories
- ✅ Ignore log files
- ✅ Ignore upload directories (content only)
- ✅ Ignore IDE and OS specific files
- ✅ Ignore backup files

**Files Created:**
- `.gitignore` (NEW)

---

### 📋 FILES SUMMARY

#### Files Created:
1. `.env` - Environment variables (DO NOT COMMIT!)
2. `.env.example` - Environment template
3. `.gitignore` - Git ignore rules
4. `lib/security.php` - Security helper functions (NEW)
5. `CHANGELOG.md` - This file
6. `LAPORAN_KEAMANAN_SIAKAD.md` - Security audit report
7. `PANDUAN_PERBAIKAN_CEPAT.md` - Quick fix guide

#### Files Modified:
1. `config/config.php` - COMPLETELY REWRITTEN
2. `baak/index.php` - COMPLETELY REWRITTEN

#### Files To Be Modified (Next Phase):
1. `baak/login_auth.php` - Session validation improvement
2. `dosen/index.php` - Same fixes as baak/index.php
3. `dosen/login_auth.php` - Session validation improvement
4. `mhs/index.php` - Same fixes as baak/index.php
5. `mhs/login_auth.php` - Session validation improvement
6. `baak/api/mahasiswa.php` - SQL injection fixes
7. `baak/api/dosen.php` - SQL injection fixes
8. `baak/api/kelas_kuliah.php` - SQL injection fixes
9. `baak/api/nilai.php` - SQL injection fixes
10. (And many more API files...)

---

### 🔧 INSTALLATION INSTRUCTIONS

#### 1. Install Composer Dependencies
```bash
cd /path/to/project
composer require vlucas/phpdotenv
```

#### 2. Create .env File
```bash
cp .env.example .env
nano .env  # Edit dengan credentials yang benar
```

#### 3. Set Permissions
```bash
chmod 600 .env
chmod 600 config/config.php
```

#### 4. Create Logs Directory
```bash
mkdir -p logs
chmod 755 logs
touch logs/security.log
chmod 666 logs/security.log
```

#### 5. Update .gitignore
```bash
# Already created, just verify
cat .gitignore
```

#### 6. Test Login
- Akses https://your-domain.com/baak/login
- Test dengan user yang valid
- Verify di logs/security.log

---

### ⚠️ BREAKING CHANGES

#### Password Format Changed
**Impact:** Existing users will need password migration

**Solution:** 
- System automatically upgrades passwords on first login
- Or run migration script: `php tools/migrate_passwords.php`

#### .env File Required
**Impact:** System will not run without .env file

**Solution:**
- Copy .env.example to .env
- Fill in all required values

#### HTTPS Required
**Impact:** HTTP requests will be redirected to HTTPS

**Solution:**
- Install valid SSL certificate
- Or disable HTTPS check in development (not recommended)

---

### 🔐 SECURITY NOTES

#### Passwords
- Old passwords using MD5/SHA1 will be auto-upgraded to Argon2ID
- New passwords use Argon2ID by default (most secure)
- Password complexity requirements should be enforced (separate implementation)

#### Sessions
- Session timeout: 30 minutes
- Session tied to IP address (optional, can cause issues with mobile)
- Secure cookies enabled (httponly, secure, samesite)

#### Rate Limiting
- Login: 5 attempts per 15 minutes
- Can be adjusted in Security::checkRateLimit() calls

#### Logging
- All security events logged to error_log and logs/security.log
- Review logs regularly for suspicious activity

---

### 📊 TESTING CHECKLIST

#### Before Deployment:
- [ ] Verify .env file exists and has correct values
- [ ] Test admin login
- [ ] Test dosen login (after dosen/index.php updated)
- [ ] Test mahasiswa login (after mhs/index.php updated)
- [ ] Verify HTTPS redirect works
- [ ] Verify rate limiting works (try 6 failed logins)
- [ ] Check logs/security.log for entries
- [ ] Test API access with valid and invalid API names
- [ ] Verify session timeout (wait 30 minutes, try to access)
- [ ] Test password auto-upgrade (login with old account)

#### After Deployment:
- [ ] Monitor logs for suspicious activity
- [ ] Verify no errors in production
- [ ] Test all major functions
- [ ] Verify database connections work
- [ ] Check performance impact (should be minimal)

---

### 🚀 NEXT STEPS

#### High Priority (Complete Within 1 Week):
1. ✅ Update `config/config.php` - DONE
2. ✅ Create `lib/security.php` - DONE
3. ✅ Update `baak/index.php` - DONE
4. ⏳ Update `baak/login_auth.php` - IN PROGRESS
5. ⏳ Update `dosen/index.php` - PENDING
6. ⏳ Update `dosen/login_auth.php` - PENDING
7. ⏳ Update `mhs/index.php` - PENDING
8. ⏳ Update `mhs/login_auth.php` - PENDING

#### Medium Priority (Complete Within 2 Weeks):
1. ⏳ Update ALL API files in `baak/api/` - PENDING
2. ⏳ Update ALL API files in `dosen/api/` - PENDING
3. ⏳ Update ALL API files in `mhs/api/` - PENDING
4. ⏳ Implement CSRF protection in forms - PENDING
5. ⏳ Add file upload validation - PENDING

#### Low Priority (Complete Within 1 Month):
1. ⏳ Add password complexity requirements
2. ⏳ Implement account lockout after failed attempts
3. ⏳ Add 2FA (Two-Factor Authentication)
4. ⏳ Security headers optimization
5. ⏳ Penetration testing
6. ⏳ Security audit external

---

### 📞 SUPPORT

Jika ada pertanyaan atau masalah:
1. Check `LAPORAN_KEAMANAN_SIAKAD.md` untuk detail celah keamanan
2. Check `PANDUAN_PERBAIKAN_CEPAT.md` untuk contoh perbaikan
3. Review logs di `logs/security.log`
4. Contact security team

---

### 📝 VERSION HISTORY

- **1.0.0** (2025-10-14) - Initial security update
  - Database credentials protection
  - SQL injection prevention
  - Password hashing improvement
  - Session management improvement
  - File inclusion protection
  - Rate limiting
  - Security logging
  - HTTPS enforcement
  - Security headers

---

### ⚖️ LICENSE

Internal use only - Politeknik Indonusa Surakarta

---

**IMPORTANT:** This security update is CRITICAL. Please deploy as soon as possible after testing.

**Last Updated:** 2025-10-14
**Author:** Security Team
**Version:** 1.0.0

