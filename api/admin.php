<?php

/**
 * Admin API - Yönetici İşlemleri API'si
 * @author A. Kerem Gök
 *
 * Bu API dosyası, yönetici işlemlerini gerçekleştirmek için kullanılır.
 * This API file is used to perform administrative operations.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/log.php';

/**
 * @description Kullanıcının giriş yapıp yapmadığını kontrol eder.
 * Checks if the user is logged in.
 */
checkLogin();

// Yönetici yetkisi kontrolü
// Check for admin privileges
if ($_SESSION['is_admin'] != 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Yetkisiz erişim' // Unauthorized access
    ]);
    exit;
}

/**
 * Kullanıcı bilgilerini getir
 * Get user information
 *
 * Bu fonksiyon, belirtilen ID'ye sahip kullanıcının bilgilerini veritabanından getirir.
 * This function retrieves user information from the database for the specified ID.
 *
 * @param int $_GET['id'] - Kullanıcı ID'si / User ID
 * @return json - Kullanıcı bilgileri / User information
 */
if (isset($_GET['action']) && $_GET['action'] === 'get_user' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Şifreyi gönderme
            // Do not send the password
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
                'message' => 'Kullanıcı bulunamadı' // User not found
            ]);
        }
    } catch (PDOException $e) {
        saveLog("Kullanıcı bilgileri alınırken hata: " . $e->getMessage(), 'error', 'get_user', $_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Veritabanı hatası' // Database error
        ]);
    }
    exit;
}

// POST isteklerini işle
// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        /**
         * Yeni kullanıcı ekleme
         * Add new user
         *
         * Bu fonksiyon, yeni bir kullanıcıyı veritabanına ekler.
         * This function adds a new user to the database.
         *
         * @param string $_POST['username'] - Kullanıcı adı / Username
         * @param string $_POST['password'] - Şifre / Password
         * @param string $_POST['base_currency'] - Temel para birimi / Base currency
         * @param string $_POST['theme_preference'] - Tema tercihi / Theme preference
         * @param bool $_POST['is_admin'] - Yönetici yetkisi / Admin privileges
         * @param bool $_POST['is_active'] - Aktif durumu / Active status
         */
        case 'add_user':
            // Gerekli alanları kontrol et
            // Check required fields
            if (empty($_POST['username']) || empty($_POST['password'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Kullanıcı adı ve şifre zorunludur' // Username and password are required
                ]);
                exit;
            }

            try {
                global $pdo;

                // Kullanıcı adının benzersiz olduğunu kontrol et
                // Check if username is unique
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $stmt->execute(['username' => $_POST['username']]);
                if ($stmt->fetchColumn() > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bu kullanıcı adı zaten kullanılıyor' // This username is already in use
                    ]);
                    exit;
                }

                // Yeni kullanıcıyı ekle
                // Add new user
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
                        'message' => 'Kullanıcı başarıyla eklendi' // User added successfully
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı eklenirken bir hata oluştu' // An error occurred while adding the user
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı eklenirken hata: " . $e->getMessage(), 'error', 'add_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası' // Database error
                ]);
            }
            break;

        /**
             * Kullanıcı bilgilerini güncelleme
             * Update user information
             *
             * Bu fonksiyon, mevcut bir kullanıcının bilgilerini günceller.
             * This function updates the information of an existing user.
             *
             * @param int $_POST['id'] - Kullanıcı ID'si / User ID
             * @param string $_POST['username'] - Kullanıcı adı / Username
             * @param string $_POST['password'] - Şifre (opsiyonel) / Password (optional)
             * @param string $_POST['base_currency'] - Temel para birimi / Base currency
             * @param string $_POST['theme_preference'] - Tema tercihi / Theme preference
             * @param bool $_POST['is_admin'] - Yönetici yetkisi / Admin privileges
             * @param bool $_POST['is_active'] - Aktif durumu / Active status
             */
        case 'update_user':
            if (empty($_POST['id']) || empty($_POST['username'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz istek' // Invalid request
                ]);
                exit;
            }

            try {
                global $pdo;

                // Kullanıcı adının başka bir kullanıcı tarafından kullanılmadığını kontrol et
                // Check if username is not used by another user
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username AND id != :id");
                $stmt->execute([
                    'username' => $_POST['username'],
                    'id' => $_POST['id']
                ]);
                if ($stmt->fetchColumn() > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Bu kullanıcı adı zaten kullanılıyor' // This username is already in use
                    ]);
                    exit;
                }

                // Güncelleme sorgusunu hazırla
                // Prepare update query
                $sql = "UPDATE users SET 
                        username = :username,
                        base_currency = :base_currency,
                        theme_preference = :theme_preference,
                        is_admin = :is_admin,
                        is_active = :is_active";

                // Eğer şifre girilmişse şifreyi de güncelle
                // Update password if provided
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
                        'message' => 'Kullanıcı başarıyla güncellendi' // User updated successfully
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı güncellenirken bir hata oluştu' // An error occurred while updating the user
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı güncellenirken hata: " . $e->getMessage(), 'error', 'update_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası' // Database error
                ]);
            }
            break;

        /**
             * Kullanıcı silme
             * Delete user
             *
             * Bu fonksiyon, belirtilen ID'ye sahip bir kullanıcıyı siler.
             * This function deletes a user with the specified ID.
             *
             * @param int $_POST['id'] - Silinecek kullanıcı ID'si / User ID to delete
             * @throws Exception - Kullanıcı kendisini silemez / User cannot delete themselves
             */
        case 'delete_user':
            if (empty($_POST['id'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Geçersiz istek' // Invalid request
                ]);
                exit;
            }

            // Kullanıcının kendisini silmesini engelle
            // Prevent user from deleting themselves
            if ($_POST['id'] == $_SESSION['user_id']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Kendinizi silemezsiniz' // You cannot delete yourself
                ]);
                exit;
            }

            try {
                global $pdo;

                // Önce kullanıcı adını al (log için)
                // Get username first (for logging)
                $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
                $stmt->execute(['id' => $_POST['id']]);
                $username = $stmt->fetchColumn();

                // Kullanıcıyı sil
                // Delete user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $result = $stmt->execute(['id' => $_POST['id']]);

                if ($result) {
                    saveLog("Kullanıcı silindi: " . $username, 'success', 'delete_user', $_SESSION['user_id']);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Kullanıcı başarıyla silindi' // User deleted successfully
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Kullanıcı silinirken bir hata oluştu' // An error occurred while deleting the user
                    ]);
                }
            } catch (PDOException $e) {
                saveLog("Kullanıcı silinirken hata: " . $e->getMessage(), 'error', 'delete_user', $_SESSION['user_id']);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Veritabanı hatası' // Database error
                ]);
            }
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Geçersiz işlem' // Invalid operation
            ]);
            break;
    }
    exit;
}

// Geçersiz istek
// Invalid request
header('Content-Type: application/json');
echo json_encode([
    'status' => 'error',
    'message' => 'Geçersiz istek' // Invalid request
]);
exit;
