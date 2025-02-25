<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/log.php';
checkLogin();

if ($_SESSION['is_admin'] != 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Yetkisiz erişim'
    ]);
    exit;
}

// Kullanıcı bilgilerini getir
if (isset($_GET['action']) && $_GET['action'] === 'get_user' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Şifreyi gönderme
            unset($user['password']);

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'user' => $user
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Kullanıcı bulunamadı'
            ]);
        }
    } catch (PDOException $e) {
        saveLog("Kullanıcı bilgileri alınırken hata: " . $e->getMessage(), 'error', 'get_user', $_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Veritabanı hatası'
        ]);
    }
    exit;
}

// POST isteklerini işle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_user':
            // Gerekli alanları kontrol et
            if (empty($_POST['username']) || empty($_POST['password'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Kullanıcı adı ve şifre zorunludur'
                ]);
                exit;
            }

            try {
                global $pdo;

                // Kullanıcı adının benzersiz olduğunu kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $stmt->execute(['username' => $_POST['username']]);
                if ($stmt->fetchColumn() > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bu kullanıcı adı zaten kullanılıyor'
                    ]);
                    exit;
                }

                // Yeni kullanıcıyı ekle
                $stmt = $pdo->prepare("INSERT INTO users (username, password, base_currency, theme_preference, is_admin, is_active) VALUES (:username, :password, :base_currency, :theme_preference, :is_admin, :is_active)");

                $result = $stmt->execute([
                    'username' => $_POST['username'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'base_currency' => $_POST['base_currency'] ?? 'TRY',
                    'theme_preference' => $_POST['theme_preference'] ?? 'light',
                    'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ]);

                if ($result) {
                    saveLog("Yeni kullanıcı eklendi: " . $_POST['username'], 'success', 'add_user', $_SESSION['user_id']);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Kullanıcı başarıyla eklendi'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı eklenirken bir hata oluştu'
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı eklenirken hata: " . $e->getMessage(), 'error', 'add_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası'
                ]);
            }
            break;

        case 'update_user':
            if (empty($_POST['id']) || empty($_POST['username'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz istek'
                ]);
                exit;
            }

            try {
                global $pdo;

                // Kullanıcı adının başka bir kullanıcı tarafından kullanılmadığını kontrol et
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
                $stmt->execute([
                    'username' => $_POST['username'],
                    'id' => $_POST['id']
                ]);
                if ($stmt->fetchColumn() > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bu kullanıcı adı zaten kullanılıyor'
                    ]);
                    exit;
                }

                // Güncelleme sorgusunu hazırla
                $sql = "UPDATE users SET 
                        username = :username,
                        base_currency = :base_currency,
                        theme_preference = :theme_preference,
                        is_admin = :is_admin,
                        is_active = :is_active";

                // Eğer şifre girilmişse şifreyi de güncelle
                if (!empty($_POST['password'])) {
                    $sql .= ", password = :password";
                }

                $sql .= " WHERE id = :id";

                $stmt = $pdo->prepare($sql);

                $params = [
                    'username' => $_POST['username'],
                    'base_currency' => $_POST['base_currency'] ?? 'TRY',
                    'theme_preference' => $_POST['theme_preference'] ?? 'light',
                    'is_admin' => isset($_POST['is_admin']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'id' => $_POST['id']
                ];

                if (!empty($_POST['password'])) {
                    $params['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }

                $result = $stmt->execute($params);

                if ($result) {
                    saveLog("Kullanıcı güncellendi: " . $_POST['username'], 'success', 'update_user', $_SESSION['user_id']);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Kullanıcı başarıyla güncellendi'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı güncellenirken bir hata oluştu'
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı güncellenirken hata: " . $e->getMessage(), 'error', 'update_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası'
                ]);
            }
            break;

        case 'delete_user':
            if (empty($_POST['id'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz istek'
                ]);
                exit;
            }

            // Kullanıcının kendisini silmesini engelle
            if ($_POST['id'] == $_SESSION['user_id']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Kendinizi silemezsiniz'
                ]);
                exit;
            }

            try {
                global $pdo;

                // Önce kullanıcı adını al (log için)
                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
                $stmt->execute(['id' => $_POST['id']]);
                $username = $stmt->fetchColumn();

                // Kullanıcıyı sil
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $result = $stmt->execute(['id' => $_POST['id']]);

                if ($result) {
                    saveLog("Kullanıcı silindi: " . $username, 'success', 'delete_user', $_SESSION['user_id']);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Kullanıcı başarıyla silindi'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı silinirken bir hata oluştu'
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı silinirken hata: " . $e->getMessage(), 'error', 'delete_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası'
                ]);
            }
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz işlem'
            ]);
            break;
    }
    exit;
}

// Geçersiz istek
header('Content-Type: application/json');
echo json_encode([
    'status' => 'error',
    'message' => 'Geçersiz istek'
]);
exit;
