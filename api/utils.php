<?php
require_once __DIR__ . '/../config.php';
checkLogin();

require_once __DIR__ . '/./currency.php';

// Tekrar sayısını hesaplama fonksiyonu
function calculateRepeatCount($frequency)
{
    switch ($frequency) {
        case 'none':
            return 1;
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 1;
    }
}

// İki tarih arasındaki ay sayısını hesaplama
function getMonthDifference($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    $interval = $start->diff($end);
    return $interval->y * 12 + $interval->m;
}

// Tekrarlama sıklığına göre ay aralığını hesapla
function getMonthInterval($frequency)
{
    switch ($frequency) {
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 0;
    }
}

// Sonraki ödeme tarihini hesapla
function calculateNextPaymentDate($date, $months)
{
    $next_date = new DateTime($date);
    $next_date->modify('+' . $months . ' months');
    return $next_date->format('Y-m-d');
}

// Sonraki tarihi hesaplama fonksiyonu
function calculateNextDate($date, $frequency, $count = 1)
{
    $nextDate = new DateTime($date);

    switch ($frequency) {
        case 'monthly':
            $nextDate->modify('+' . $count . ' month');
            break;
        case 'bimonthly':
            $nextDate->modify('+' . ($count * 2) . ' months');
            break;
        case 'quarterly':
            $nextDate->modify('+' . ($count * 3) . ' months');
            break;
        case 'fourmonthly':
            $nextDate->modify('+' . ($count * 4) . ' months');
            break;
        case 'fivemonthly':
            $nextDate->modify('+' . ($count * 5) . ' months');
            break;
        case 'sixmonthly':
            $nextDate->modify('+' . ($count * 6) . ' months');
            break;
        case 'yearly':
            $nextDate->modify('+' . $count . ' year');
            break;
        default:
            return $date;
    }

    return $nextDate->format('Y-m-d');
}


// Sayısal değerleri formatla
function formatNumber($number, $decimals = 2)
{
    if (!is_numeric($number)) {
        return '0.00';
    }
    return number_format((float)$number, $decimals, '.', '');
}

// Exchange rate cache mekanizması - performans optimizasyonu
function getCachedExchangeRate($from_currency, $to_currency)
{
    global $pdo;
    
    // Aynı para birimi ise 1 döndür
    if ($from_currency === $to_currency) {
        return 1.0;
    }
    
    // Cache'den bugünkü kuru kontrol et (30 dakika cache - daha güncel kurlar için)
    $stmt = $pdo->prepare("SELECT rate FROM exchange_rates 
                          WHERE from_currency = ? AND to_currency = ? 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                          ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$from_currency, $to_currency]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return floatval($result['rate']);
    }
    
    // Cache'de yoksa external API'den al ve cache'le
    $rate = getExchangeRate($from_currency, $to_currency);
    if ($rate) {
        // Cache'e kaydet
        try {
            $stmt = $pdo->prepare("INSERT INTO exchange_rates (from_currency, to_currency, rate, date) 
                                  VALUES (?, ?, ?, CURDATE())
                                  ON DUPLICATE KEY UPDATE 
                                  rate = VALUES(rate), created_at = CURRENT_TIMESTAMP");
            $stmt->execute([$from_currency, $to_currency, $rate]);
        } catch (Exception $e) {
            // Cache hatası log'la ama rate'i döndür
            saveLog("Exchange rate cache hatası: " . $e->getMessage(), 'warning', 'getCachedExchangeRate', 0);
        }
    }
    
    return $rate;
}

// Session tabanlı summary cache - daha da hızlı işlem için
function getCachedSummary($user_id, $month, $year)
{
    $cache_key = "summary_{$user_id}_{$month}_{$year}";
    
    // Session cache kontrol et (5 dakika cache)
    if (isset($_SESSION[$cache_key]) && 
        isset($_SESSION[$cache_key . '_time']) && 
        (time() - $_SESSION[$cache_key . '_time']) < 300) {
        return $_SESSION[$cache_key];
    }
    
    return null;
}

// Summary'yi cache'le
function cacheSummary($user_id, $month, $year, $summary_data)
{
    $cache_key = "summary_{$user_id}_{$month}_{$year}";
    $_SESSION[$cache_key] = $summary_data;
    $_SESSION[$cache_key . '_time'] = time();
}

// Cache invalidation - kullanıcının belirli ay cache'ini temizle
function clearSummaryCache($user_id, $month, $year)
{
    $cache_key = "summary_{$user_id}_{$month}_{$year}";
    if (isset($_SESSION[$cache_key])) {
        unset($_SESSION[$cache_key]);
        unset($_SESSION[$cache_key . '_time']);
    }
}

// Cache invalidation - kullanıcının tüm summary cache'lerini temizle
function clearAllUserSummaryCache($user_id)
{
    // Session'daki tüm summary cache'lerini bul ve temizle
    $prefix = "summary_{$user_id}_";
    $keys_to_remove = [];
    
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, $prefix) === 0) {
            $keys_to_remove[] = $key;
        }
    }
    
    foreach ($keys_to_remove as $key) {
        unset($_SESSION[$key]);
    }
}

// Smart cache invalidation - sadece etkilenen ay cache'lerini temizle
function invalidateSummaryCacheForDate($user_id, $date)
{
    $date_obj = new DateTime($date);
    $month = (int)$date_obj->format('n');
    $year = (int)$date_obj->format('Y');
    
    clearSummaryCache($user_id, $month, $year);
    
    // Log cache temizleme işlemini
    if (function_exists('saveLog')) {
        saveLog("Summary cache cleared for user {$user_id}, date: {$date}", 'info', 'cache_invalidation', $user_id);
    }
}

// Exchange rate cache invalidation - belirli para birimi çiftinin cache'ini temizle
function invalidateExchangeRateCache($from_currency, $to_currency)
{
    global $pdo;
    
    try {
        // Belirli para birimi çiftinin cache'ini sil
        $stmt = $pdo->prepare("DELETE FROM exchange_rates 
                              WHERE (from_currency = ? AND to_currency = ?) 
                              OR (from_currency = ? AND to_currency = ?)");
        $stmt->execute([$from_currency, $to_currency, $to_currency, $from_currency]);
        
        if (function_exists('saveLog')) {
            saveLog("Exchange rate cache cleared for {$from_currency}/{$to_currency}", 'info', 'exchange_rate_invalidation', 0);
        }
    } catch (Exception $e) {
        if (function_exists('saveLog')) {
            saveLog("Exchange rate cache invalidation error: " . $e->getMessage(), 'error', 'exchange_rate_invalidation', 0);
        }
    }
}

// Güncel kur verisi zorla çek - kullanıcı tarafından tetiklenen manuel güncelleme
function forceUpdateExchangeRate($from_currency, $to_currency)
{
    global $pdo;
    
    // Önce cache'i temizle
    invalidateExchangeRateCache($from_currency, $to_currency);
    
    // Yeni kuru çek
    $rate = getExchangeRate($from_currency, $to_currency);
    
    if ($rate && function_exists('saveLog')) {
        saveLog("Exchange rate force updated: {$from_currency} to {$to_currency} = {$rate}", 'info', 'force_exchange_update', 0);
    }
    
    return $rate;
}
