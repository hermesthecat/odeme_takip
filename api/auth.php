<?php

/**
 * Authentication API - Kimlik Doğrulama API'si
 * @author A. Kerem Gök
 *
 * Bu API dosyası, kullanıcı kimlik doğrulama işlemlerini gerçekleştirmek için kullanılır.
 * This API file is used to perform user authentication operations.
 */

require_once '../config.php';
require_once '../classes/log.php';

/**
 * XSS koruma fonksiyonu
 * XSS protection function
 *
 * Bu fonksiyon, girdi verilerini XSS saldırılarına karşı temizler.
 * This function sanitizes input data against XSS attacks.
 *
 * @param mixed $data - Temizlenecek veri / Data to be sanitized
 * @return string - Temizlenmiş veri / Sanitized data
 */
function sanitizeInput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Güvenlik başlıkları ayarla
// Set security headers
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

$response = ['status' => 'error', 'message' => t('auth.invalid_request')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = sanitizeInput($_POST['action'] ?? '');

    switch ($action) {
        /**
         * Kullanıcı kaydı
         * User registration
         *
         * Bu bölüm, yeni kullanıcıların sisteme kaydolmasını sağlar.
         * This section allows new users to register to the system.
         *
         * @param string $_POST['username'] - Kullanıcı adı / Username
         * @param string $_POST['password'] - Şifre / Password
         * @param string $_POST['password_confirm'] - Şifre tekrarı / Password confirmation
         * @param string $_POST['base_currency'] - Temel para birimi / Base currency
         */
        case 'register':
            $username = sanitizeInput(trim($_POST['username'] ?? ''));
            $password = trim($_POST['password'] ?? '');
            $password_confirm = trim($_POST['password_confirm'] ?? '');
            $base_currency = sanitizeInput(trim($_POST['base_currency'] ?? 'TRY'));

            // Validasyon
            // Validation
            $errors = [];

            if (strlen($username) < 3) {
                $errors[] = t('auth.username_min_length'); // Kullanıcı adı en az 3 karakter olmalıdır / Username must be at least 3 characters
            }

            if (strlen($password) < 6) {
                $errors[] = t('auth.password_min_length'); // Şifre en az 6 karakter olmalıdır / Password must be at least 6 characters
            }

            if ($password !== $password_confirm) {
                $errors[] = t('auth.password_mismatch'); // Şifreler eşleşmiyor / Passwords do not match
            }

            // Kullanıcı adı benzersizlik kontrolü
            // Check username uniqueness
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = t('auth.username_taken'); // Bu kullanıcı adı zaten alınmış / This username is already taken
            }

            // Güçlü şifre politikası
            // Strong password policy
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = t('auth.password_policy'); // Şifre politikasına uymuyor / Does not meet password policy
            }

            if (!empty($errors)) {
                $response = ['status' => 'error', 'message' => implode("\n", $errors)];
                break;
            }

            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $hashed_password, $base_currency])) {
                    $response = ['status' => 'success', 'message' => t('auth.register_success')]; // Kayıt başarılı / Registration successful
                    saveLog("Kullanıcı kayıt işlemi: " . $username, 'info', 'register', 0); // User registration: username
                } else {
                    $response = ['status' => 'error', 'message' => t('auth.register_error')]; // Kayıt başarısız / Registration failed
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')]; // Veritabanı hatası / Database error
                saveLog("Kullanıcı kayıt işlemi hatası: " . $e->getMessage(), 'error', 'register', 0); // User registration error: error message
            }
            break;

        /**
             * Kullanıcı girişi
             * User login
             *
             * Bu bölüm, kayıtlı kullanıcıların sisteme giriş yapmasını sağlar.
             * This section allows registered users to log in to the system.
             *
             * @param string $_POST['username'] - Kullanıcı adı / Username
             * @param string $_POST['password'] - Şifre / Password
             * @param boolean $_POST['remember_me'] - Beni hatırla / Remember me
             */
        case 'login':
            $username = sanitizeInput(trim($_POST['username'] ?? ''));
            $password = trim($_POST['password'] ?? '');
            $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

            // Brute force koruması
            // Brute force protection
            if (
                isset($_SESSION['login_attempts'][$username]) &&
                $_SESSION['login_attempts'][$username]['count'] >= 5 &&
                time() - $_SESSION['login_attempts'][$username]['time'] < 900
            ) {
                $response = ['status' => 'error', 'message' => t('auth.too_many_attempts')]; // Çok fazla deneme / Too many attempts
                break;
            }

            if (empty($username) || empty($password)) {
                $response = ['status' => 'error', 'message' => t('auth.credentials_required')]; // Kullanıcı adı ve şifre gerekli / Username and password are required
                break;
            }

            // Kullanıcının aktif olup olmadığını kontrol et
            // Check if user is active
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() == 0) {
                $response = ['status' => 'error', 'message' => "Kullanıcı aktif değildir."]; // User is not active
                break;
            }

            $sql = "SELECT * FROM users WHERE username = :username AND is_active = 1";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":username", $username, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        $row = $stmt->fetch();
                        if (password_verify($password, $row['password'])) {
                            // Session yenileme
                            // Regenerate session
                            session_regenerate_id(true);

                            // Session timeout ayarı
                            // Set session timeout
                            $_SESSION['last_activity'] = time();
                            $_SESSION['expire_time'] = 30 * 60; // 30 dakika / 30 minutes

                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['base_currency'] = $row['base_currency'];
                            $_SESSION['theme'] = $row['theme_preference'];
                            $_SESSION['is_admin'] = $row['is_admin'];

                            // Son giriş tarihini güncelle
                            // Update last login date
                            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            $updateStmt->execute([$row['id']]);

                            if ($remember_me) {
                                $token = bin2hex(random_bytes(32));
                                setcookie('remember_token', $token, time() + (86400 * 30), "/");

                                $updateStmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                $updateStmt->execute([$token, $row['id']]);
                            }

                            $response = ['status' => 'success', 'message' => t('auth.login_success')]; // Giriş başarılı / Login successful
                            saveLog("Kullanıcı giriş işlemi: " . $username, 'info', 'login', $row['id']); // User login: username
                        } else {
                            $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')]; // Geçersiz kullanıcı adı veya şifre / Invalid username or password
                            saveLog("Kullanıcı şifre hatası: " . $username, 'error', 'login', $row['id']); // User password error: username
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')]; // Geçersiz kullanıcı adı veya şifre / Invalid username or password
                        saveLog("Kullanıcı bulunamadı : " . $username, 'error', 'login', 0); // User not found: username
                    }
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')]; // Veritabanı hatası / Database error
                saveLog("Kullanıcı giriş işlemi hatası: " . $e->getMessage(), 'error', 'login', 0); // User login error: error message
            }
            break;

        /**
             * Kullanıcı çıkışı
             * User logout
             *
             * Bu bölüm, kullanıcının sistemden çıkış yapmasını sağlar.
             * This section allows the user to log out of the system.
             */
        case 'logout':
            // Session zaten başlatılmış mı kontrol et
            // Check if session is already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            saveLog("Kullanıcı çıkış işlemi: " . $_SESSION['username'], 'info', 'logout', 0); // User logout: username

            // Tüm session verilerini temizle
            // Clear all session data
            $_SESSION = array();

            // Session cookie'sini sil
            // Delete session cookie
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            // Remember token'ı temizle
            // Clear remember token
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                setcookie('remember_token', '', time() - 3600, '/');

                // Veritabanından token'ı sil
                // Delete token from database
                try {
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
                    $stmt->execute([$token]);
                } catch (PDOException $e) {
                    // Token silme hatası önemsiz
                    // Token deletion error is not important
                }
            }

            session_destroy();
            $response = ['status' => 'success', 'message' => t('auth.logout_success')]; // Çıkış başarılı / Logout successful
            break;
    }
}

echo json_encode($response);
