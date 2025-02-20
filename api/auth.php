<?php
require_once '../config.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Geçersiz istek'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
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
