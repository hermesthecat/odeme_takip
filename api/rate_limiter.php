<?php

/**
 * Rate Limiting Mekanizması
 * @author A. Kerem Gök
 * 
 * Bu dosya API çağrıları için rate limiting sağlar.
 * Gemini API, Exchange Rate API ve diğer external API'ler için kullanılır.
 */

require_once __DIR__ . '/../config.php';

/**
 * Rate limiting kontrolü
 * 
 * @param string $key Rate limiting anahtarı (user_id + action)
 * @param int $limit İzin verilen maksimum istek sayısı
 * @param int $window Zaman penceresi (saniye)
 * @return bool True ise devam edilebilir, false ise rate limit aşıldı
 */
function checkRateLimit($key, $limit = 10, $window = 60)
{
    $current_time = time();
    $session_key = "rate_limit_{$key}";
    
    // Session'da rate limit bilgisi yoksa başlat
    if (!isset($_SESSION[$session_key])) {
        $_SESSION[$session_key] = [
            'count' => 1,
            'window_start' => $current_time
        ];
        return true;
    }
    
    $rate_data = $_SESSION[$session_key];
    
    // Zaman penceresi geçtiyse sıfırla
    if (($current_time - $rate_data['window_start']) >= $window) {
        $_SESSION[$session_key] = [
            'count' => 1,
            'window_start' => $current_time
        ];
        return true;
    }
    
    // Limit kontrolü
    if ($rate_data['count'] >= $limit) {
        return false;
    }
    
    // Counter'ı artır
    $_SESSION[$session_key]['count']++;
    return true;
}

/**
 * API-specific rate limiting
 */
function checkGeminiApiLimit($user_id)
{
    // Gemini API: Kullanıcı başına 5 dakikada 10 istek
    return checkRateLimit("gemini_{$user_id}", 10, 300);
}

function checkExchangeRateLimit($user_id)
{
    // Exchange Rate API: Kullanıcı başına 10 dakikada 20 istek
    return checkRateLimit("exchange_{$user_id}", 20, 600);
}

function checkFileUploadLimit($user_id)
{
    // File Upload: Kullanıcı başına 5 dakikada 5 dosya
    return checkRateLimit("upload_{$user_id}", 5, 300);
}

function checkTelegramWebhookLimit($telegram_id)
{
    // Telegram Webhook: Bot başına dakikada 30 istek
    return checkRateLimit("telegram_{$telegram_id}", 30, 60);
}

/**
 * Rate limit aşıldığında error response döndür
 */
function rateLimitError($type = 'general', $retry_after = 60)
{
    $messages = [
        'general' => t('rate_limit.general_error'),
        'gemini' => t('rate_limit.ai_analysis_error'),
        'exchange' => t('rate_limit.exchange_rate_error'),
        'upload' => t('rate_limit.file_upload_error'),
        'telegram' => 'Rate limit exceeded. Please try again later.'
    ];
    
    $message = isset($messages[$type]) ? $messages[$type] : $messages['general'];
    
    // HTTP 429 header ekle
    http_response_code(429);
    header("Retry-After: $retry_after");
    
    return [
        'status' => 'error',
        'message' => $message,
        'retry_after' => $retry_after,
        'error_code' => 'RATE_LIMIT_EXCEEDED'
    ];
}

/**
 * Rate limit bilgilerini al (debugging için)
 */
function getRateLimitStatus($key)
{
    $session_key = "rate_limit_{$key}";
    
    if (!isset($_SESSION[$session_key])) {
        return [
            'remaining' => 'unlimited',
            'reset_time' => null
        ];
    }
    
    $rate_data = $_SESSION[$session_key];
    $current_time = time();
    
    return [
        'count' => $rate_data['count'],
        'window_start' => $rate_data['window_start'],
        'time_remaining' => max(0, 60 - ($current_time - $rate_data['window_start']))
    ];
}