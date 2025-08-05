<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/RateLimiter.php';
require_once __DIR__ . '/utils.php';
checkLogin();

/**
 * Manuel exchange rate güncelleme API'si
 * Kullanıcıların güncel kur verilerini zorla çekmesini sağlar
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $from_currency = $_POST['from_currency'] ?? null;
        $to_currency = $_POST['to_currency'] ?? null;
        $user_id = validateUserId($_SESSION['user_id']);
        
        if (!$from_currency || !$to_currency) {
            throw new Exception('Para birimi bilgileri eksik');
        }
        
        // MySQL-based rate limiting
        $rateLimiter = RateLimiter::getInstance();
        $userIdentifier = $rateLimiter->getCombinedIdentifier($user_id);
        
        $exchangeLimit = $rateLimiter->checkLimit('exchange_rate_refresh', $userIdentifier, 'user');
        if (!$exchangeLimit['allowed']) {
            $rateLimiter->addRateLimitHeaders($exchangeLimit);
            throw new Exception('Çok fazla kur güncelleme isteği. ' . 
                              date('H:i:s', $exchangeLimit['reset_time']) . ' saatinde tekrar deneyin.');
        }
        
        // Add rate limit headers
        $rateLimiter->addRateLimitHeaders($exchangeLimit);
        
        // Kuru zorla güncelle
        $new_rate = forceUpdateExchangeRate($from_currency, $to_currency);
        
        if (!$new_rate) {
            throw new Exception('Kur bilgisi alınamadı. Lütfen daha sonra tekrar deneyin.');
        }
        
        // Kullanıcının bu ay cache'ini temizle (kur değişikliği summary'leri etkileyebilir)
        $current_date = date('Y-m-d');
        invalidateSummaryCacheForDate($user_id, $current_date);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Kur bilgisi güncellendi',
            'data' => [
                'from_currency' => $from_currency,
                'to_currency' => $to_currency,
                'rate' => $new_rate,
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    // GET isteği - mevcut kur bilgisini döndür
    header('Content-Type: application/json');
    
    try {
        $from_currency = $_GET['from_currency'] ?? null;
        $to_currency = $_GET['to_currency'] ?? null;
        
        if (!$from_currency || !$to_currency) {
            throw new Exception('Para birimi bilgileri eksik');
        }
        
        $rate = getCachedExchangeRate($from_currency, $to_currency);
        
        // Cache yaşını hesapla
        global $pdo;
        $stmt = $pdo->prepare("SELECT created_at FROM exchange_rates 
                              WHERE from_currency = ? AND to_currency = ? 
                              ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$from_currency, $to_currency]);
        $cache_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $cache_age_minutes = 0;
        if ($cache_info) {
            $cache_time = new DateTime($cache_info['created_at']);
            $now = new DateTime();
            $cache_age_minutes = $now->diff($cache_time)->i + ($now->diff($cache_time)->h * 60);
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => [
                'from_currency' => $from_currency,
                'to_currency' => $to_currency,
                'rate' => $rate,
                'cache_age_minutes' => $cache_age_minutes,
                'last_updated' => $cache_info['created_at'] ?? null
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}