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
    // JSON response için check et
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Configuration file not found']);
    } else {
        // Web page request için redirect
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><title>Configuration Error</title></head><body>";
        echo "<h1>Configuration Error</h1>";
        echo "<p>Application configuration file (.env) not found.</p>";
        echo "<p>Please contact the system administrator.</p>";
        echo "</body></html>";
    }
    exit;
}

// Create dynamic domain URL with HTTP/HTTPS check
// Dinamik domain URL'si oluştur (HTTP/HTTPS kontrolü ile)
$domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

// Veritabanı bağlantı bilgileri
define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'root');
define('DB_NAME', getenv('DB_NAME') ?: 'odeme_takip');
define('SITE_URL', $domain);
define('WEBHOOK_URL', $domain . '/telegram_webhook.php');

// API Anahtarları
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY'));

// Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

// Global error handling
require_once __DIR__ . '/api/error_handler.php';
initializeErrorHandlers();

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    // Log hatayı (production'da detay göstermemek için)
    error_log("Database connection failed: " . $e->getMessage());
    
    // JSON response için check et
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database connection failed',
            'error_code' => 'DB_CONNECTION_ERROR'
        ]);
    } else {
        // Web page request için user-friendly error
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><title>Database Error</title></head><body>";
        echo "<h1>Database Connection Error</h1>";
        echo "<p>Unable to connect to the database. Please try again later.</p>";
        echo "<p>If the problem persists, please contact the system administrator.</p>";
        echo "</body></html>";
    }
    exit;
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
        // JSON request için farklı response
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Unauthorized access',
                'redirect' => SITE_URL . '/login.php'
            ]);
        } else {
            // Normal web request için PHP redirect
            header("Location: " . SITE_URL . "/login.php");
            http_response_code(302);
        }
        exit;
    }
    
    // Session timeout kontrolü ekle
    validateSessionTimeout();
}

// Session timeout validation
function validateSessionTimeout()
{
    $timeout = 30 * 60; // 30 dakika
    
    if (isset($_SESSION['last_activity_time'])) {
        if (time() - $_SESSION['last_activity_time'] > $timeout) {
            session_destroy();
            
            // JSON request için farklı response
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Session expired',
                    'redirect' => SITE_URL . '/login.php?timeout=1'
                ]);
            } else {
                header("Location: " . SITE_URL . "/login.php?timeout=1");
                http_response_code(302);
            }
            exit;
        }
    }
    
    $_SESSION['last_activity_time'] = time();
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

// User ID validation function
function validateUserId($userId)
{
    if (!is_numeric($userId) || $userId <= 0) {
        throw new Exception('Invalid user ID');
    }
    return (int)$userId;
}

// CSRF Protection Functions
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function getCSRFTokenInput()
{
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function requireCSRFToken()
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!validateCSRFToken($token)) {
        // JSON request için
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'CSRF token validation failed',
                'error_code' => 'CSRF_TOKEN_INVALID'
            ]);
        } else {
            // Web request için
            http_response_code(403);
            echo "<!DOCTYPE html><html><head><title>Security Error</title></head><body>";
            echo "<h1>Security Error</h1>";
            echo "<p>CSRF token validation failed. Please refresh the page and try again.</p>";
            echo "<a href='javascript:history.back()'>Go Back</a>";
            echo "</body></html>";
        }
        exit;
    }
}

// get user cards
function get_user_cards()
{
    global $pdo;
    $user_id = validateUserId($_SESSION['user_id']);
    $query = "SELECT * FROM card WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



// Site ayarları
$site_name = getenv('SITE_NAME') ?: "Pecunia";
$site_description = t('site_description');
$site_keywords = "bütçe, takip, kişisel, finans, yönetim";
$site_author = getenv('SITE_AUTHOR');
$site_slogan = t('site_description');
