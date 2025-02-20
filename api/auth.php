<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => t('auth.invalid_request')];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'register':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $password_confirm = trim($_POST['password_confirm'] ?? '');
            $base_currency = trim($_POST['base_currency'] ?? 'TRY');

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

            if (!empty($errors)) {
                $response = ['status' => 'error', 'message' => implode("\n", $errors)];
                break;
            }

            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $hashed_password, $base_currency])) {
                    $response = ['status' => 'success', 'message' => t('auth.register_success')];
                } else {
                    $response = ['status' => 'error', 'message' => t('auth.register_error')];
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')];
            }
            break;

        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

            if (empty($username) || empty($password)) {
                $response = ['status' => 'error', 'message' => t('auth.credentials_required')];
                break;
            }

            $sql = "SELECT * FROM users WHERE username = :username";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":username", $username, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    if ($stmt->rowCount() == 1) {
                        $row = $stmt->fetch();
                        if (password_verify($password, $row['password'])) {
                            // Session zaten başlatılmış mı kontrol et
                            if (session_status() === PHP_SESSION_NONE) {
                                session_start();
                            }

                            $_SESSION['user_id'] = $row['id'];
                            $_SESSION['username'] = $row['username'];
                            $_SESSION['base_currency'] = $row['base_currency'];
                            //$_SESSION['lang'] = $row['lang'];
                            $_SESSION['theme'] = $row['theme_preference'];

                            if ($remember_me) {
                                $token = bin2hex(random_bytes(32));
                                setcookie('remember_token', $token, time() + (86400 * 30), "/");

                                $updateStmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                $updateStmt->execute([$token, $row['id']]);
                            }

                            $response = ['status' => 'success', 'message' => t('auth.login_success')];
                        } else {
                            $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')];
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => t('auth.invalid_credentials')];
                    }
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => t('auth.database_error')];
            }
            break;

        case 'logout':
            // Session zaten başlatılmış mı kontrol et
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
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
            break;
    }
}

echo json_encode($response);
