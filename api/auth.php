<?php
require_once '../config.php';
require_once '../classes/log.php';


// XSS koruma fonksiyonu
function sanitizeInput($data)
{
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

$response = ['status' => 'error', 'message' => t('auth.invalid_request')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = sanitizeInput($_POST['action'] ?? '');

    switch ($action) {
        case 'register':
            $username = sanitizeInput(trim($_POST['username'] ?? ''));
            $password = trim($_POST['password'] ?? '');
            $password_confirm = trim($_POST['password_confirm'] ?? '');
            $base_currency = sanitizeInput(trim($_POST['base_currency'] ?? 'TRY'));

            // Validasyon
            $errors = [];

            if (strlen($username) < 3) {
                $errors[] = t('auth.username_min_length');
            }

            if (strlen($password) < 6) {
                $errors[] = t('auth.password_min_length');
            }

            if ($password !== $password_confirm) {
                $errors[] = t('auth.password_mismatch');
            }

            // Kullanıcı adı benzersizlik kontrolü
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = t('auth.username_taken');
            }

            // Güçlü şifre politikası
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = t('auth.password_policy');
            }

            if (!empty($errors)) {
                $response = ['status' => 'error', 'message' => implode("\n", $errors)];
                break;
            }

            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $hashed_password, $base_currency])) {
                    $response = ['status' => 'success', 'message' => t('auth.register_success')];
                    saveLog("Kullanıcı kayıt işlemi: " . $username, 'info', 'register', 0);
                } else {
                    $response = ['status' => 'error', 'message' => t('auth.register_error')];
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')];
                saveLog("Kullanıcı kayıt işlemi hatası: " . $e->getMessage(), 'error', 'register', 0);
            }
            break;

        case 'login':
            $username = sanitizeInput(trim($_POST['username'] ?? ''));
            $password = trim($_POST['password'] ?? '');
            $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

            // Brute force koruması
            if (
                isset($_SESSION['login_attempts'][$username]) &&
                $_SESSION['login_attempts'][$username]['count'] >= 5 &&
                time() - $_SESSION['login_attempts'][$username]['time'] < 900
            ) {
                $response = ['status' => 'error', 'message' => t('auth.too_many_attempts')];
                break;
            }

            if (empty($username) || empty($password)) {
                $response = ['status' => 'error', 'message' => t('auth.credentials_required')];
                break;
            }

            // check if user is active
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() == 0) {
                $response = ['status' => 'error', 'message' => "Kullanıcı aktif değildir."];
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
                            session_regenerate_id(true);

                            // Session timeout ayarı
                            $_SESSION['last_activity'] = time();
                            $_SESSION['expire_time'] = 30 * 60; // 30 dakika

                            // Yeni CSRF token oluştur
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['base_currency'] = $row['base_currency'];
                            //$_SESSION['lang'] = $row['lang'];
                            $_SESSION['theme'] = $row['theme_preference'];
                            $_SESSION['is_admin'] = $row['is_admin'];

                            // update last login date
                            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                            $updateStmt->execute([$row['id']]);

                            if ($remember_me) {
                                $token = bin2hex(random_bytes(32));
                                setcookie('remember_token', $token, time() + (86400 * 30), "/");

                                $updateStmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                $updateStmt->execute([$token, $row['id']]);
                            }

                            $response = ['status' => 'success', 'message' => t('auth.login_success')];
                            saveLog("Kullanıcı giriş işlemi: " . $username, 'info', 'login', $row['id']);
                        } else {
                            $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')];
                            saveLog("Kullanıcı şifre hatası: " . $username, 'error', 'login', $row['id']);
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')];
                        saveLog("Kullanıcı bulunamadı : " . $username, 'error', 'login', 0);
                    }
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')];
                saveLog("Kullanıcı giriş işlemi hatası: " . $e->getMessage(), 'error', 'login', 0);
            }
            break;

        case 'logout':
            // Session zaten başlatılmış mı kontrol et
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Tüm session verilerini temizle
            $_SESSION = array();

            // Session cookie'sini sil
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }

            // Remember token'ı temizle
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                setcookie('remember_token', '', time() - 3600, '/');

                // Veritabanından token'ı sil
                try {
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
                    $stmt->execute([$token]);
                } catch (PDOException $e) {
                    // Token silme hatası önemsiz
                }
            }

            session_destroy();
            $response = ['status' => 'success', 'message' => t('auth.logout_success')];
            saveLog("Kullanıcı çıkış işlemi: " . $_SESSION['username'], 'info', 'logout', 0);
            break;
    }
}

echo json_encode($response);
