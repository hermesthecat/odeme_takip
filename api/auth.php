<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Geçersiz istek'];

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
                $errors[] = "Kullanıcı adı en az 3 karakter olmalıdır.";
            }

            if (strlen($password) < 6) {
                $errors[] = "Şifre en az 6 karakter olmalıdır.";
            }

            if ($password !== $password_confirm) {
                $errors[] = "Şifreler eşleşmiyor.";
            }

            // Kullanıcı adı benzersizlik kontrolü
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Bu kullanıcı adı zaten kullanılıyor.";
            }

            if (!empty($errors)) {
                $response = ['status' => 'error', 'message' => implode("\n", $errors)];
                break;
            }

            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency) VALUES (?, ?, ?)");

                if ($stmt->execute([$username, $hashed_password, $base_currency])) {
                    $response = ['status' => 'success', 'message' => 'Kayıt başarılı!'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Kayıt sırasında bir hata oluştu.'];
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
            }
            break;

        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember_me = isset($_POST['remember_me']) && $_POST['remember_me'] === 'true';

            if (empty($username) || empty($password)) {
                $response = ['status' => 'error', 'message' => 'Kullanıcı adı ve şifre gereklidir.'];
                break;
            }

            $sql = "SELECT id, username, password FROM users WHERE username = :username";

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

                            if ($remember_me) {
                                $token = bin2hex(random_bytes(32));
                                setcookie('remember_token', $token, time() + (86400 * 30), "/");

                                $updateStmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                                $updateStmt->execute([$token, $row['id']]);
                            }

                            $response = ['status' => 'success', 'message' => 'Giriş başarılı'];
                        } else {
                            $response = ['status' => 'error', 'message' => 'Geçersiz kullanıcı adı veya şifre.'];
                        }
                    } else {
                        $response = ['status' => 'error', 'message' => 'Geçersiz kullanıcı adı veya şifre.'];
                    }
                }
            } catch (PDOException $e) {
                $response = ['status' => 'error', 'message' => 'Veritabanı hatası: ' . $e->getMessage()];
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
            $response = ['status' => 'success', 'message' => 'Çıkış başarılı'];
            break;
    }
}

echo json_encode($response);
