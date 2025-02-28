<?php

// check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// .env dosyasını yükle
if (file_exists(__DIR__ . '/.env')) {
    $envFile = file_get_contents(__DIR__ . '/.env');
    $lines = explode("\n", $envFile);
    foreach ($lines as $line) {
        if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Tırnak işaretlerini kaldır
        $value = trim($value, '"');
        $value = trim($value, "'");
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
} else {
    echo "HATA: .env dosyası bulunamadı.";
    exit;
}

// Veritabanı bağlantı bilgileri
define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'odeme_takip');

// API Anahtarları
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY'));

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

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

// Site ayarları
$site_name = getenv('SITE_NAME') ?: "Pecunia";
$site_description = t('site_description');
$site_keywords = "bütçe, takip, kişisel, finans, yönetim";
$site_author = getenv('SITE_AUTHOR');
$site_slogan = t('site_description');
