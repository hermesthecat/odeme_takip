<?php

// check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Autoload sınıfları
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Dil yönetimi
$lang = Language::getInstance();

// Dil seçimini belirle
if (isset($_GET['lang'])) {
    // URL'den dil seçimi
    $lang->setLanguage($_GET['lang']);
} elseif (isset($_SESSION['lang'])) {
    // Session'dan dil seçimi
    $lang->setLanguage($_SESSION['lang']);
} elseif (isset($_COOKIE['lang'])) {
    // Cookie'den dil seçimi
    $lang->setLanguage($_COOKIE['lang']);
} else {
    // Tarayıcı dilini kontrol et
    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    if (in_array($browserLang, $lang->getAvailableLanguages())) {
        $lang->setLanguage($browserLang);
    }
}

// Kısaltma fonksiyonu
function t($key, $params = [])
{
    return Language::t($key, $params);
}

// Oturum kontrolü
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// convert date from english to turkish
// like 2025-02-25 to 25/02/2025
function formatDate($date)
{

    // check session lang is turkish
    if (isset($_SESSION['lang']) && $_SESSION['lang'] === 'tr') {
        return date('d/m/Y', strtotime($date));
    }

    return date('Y-m-d', strtotime($date));
}

// supported currencies
$supported_currencies = [
    'TRY' => 'TRY - ' . t('currencies.try'),
    'USD' => 'USD - ' . t('currencies.usd'),
    'EUR' => 'EUR - ' . t('currencies.eur'),
    'GBP' => 'GBP - ' . t('currencies.gbp')
];

$site_name = "Parendo";
$site_description = t('site_description');
$site_keywords = 'bütçe, takip, kişisel, finans, yönetim';
$site_author = 'A. Kerem Gök & Hermes';
$site_slogan = t('site_description');
