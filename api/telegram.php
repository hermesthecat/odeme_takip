<?php
require_once __DIR__ . '/../config.php';
checkLogin();


// Telegram bağlantı durumunu kontrol et
$stmt = $pdo->prepare("SELECT * FROM telegram_users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$telegram_user = $stmt->fetch();

// Yeni kod oluştur
function generate_telegram_code()
{
    global $pdo, $telegram_user;
    // Rastgele 6 haneli kod oluştur
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    if ($telegram_user) {
        // Mevcut kaydı güncelle
        $stmt = $pdo->prepare("UPDATE telegram_users SET verification_code = ?, is_verified = 0 WHERE user_id = ?");
        $stmt->execute([$code, $_SESSION['user_id']]);
    } else {
        // Yeni kayıt oluştur
        $stmt = $pdo->prepare("INSERT INTO telegram_users (user_id, verification_code) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $code]);
    }

    $_SESSION['success'] = "Yeni doğrulama kodu oluşturuldu: " . $code;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Telegram bağlantısını kaldır
function unlink_telegram()
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM telegram_users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    $_SESSION['success'] = "Telegram bağlantısı kaldırıldı.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
