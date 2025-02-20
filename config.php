<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_NAME', 'odeme_takip');

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("HATA: Veritabanına bağlanılamadı. " . $e->getMessage());
}

session_start();

// Oturum kontrolü
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// supported currencies
$supported_currencies = [
    'TRY' => 'TRY - Türk Lirası',
    'USD' => 'USD - Amerikan Doları',
    'EUR' => 'EUR - Euro',
    'GBP' => 'GBP - İngiliz Sterlini'
];

$site_name = 'Bütçe Takip';
$site_description = 'Bütçe Takip, kişisel finans yönetimini kolaylaştıran modern bir çözümdür.';
$site_keywords = 'bütçe, takip, kişisel, finans, yönetim';
$site_author = 'A. Kerem Gök & Hermes';
$site_slogan = 'Kişisel finans yönetimini kolaylaştıran modern çözüm.';
