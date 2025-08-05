<?php

/**
 * Telegram Webhook İşleyici
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/rate_limiter.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Telegram webhook rate limiting - IP bazında
    $telegram_id = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkTelegramWebhookLimit($telegram_id)) {
        http_response_code(429);
        header('Retry-After: 60');
        exit('Rate limit exceeded');
    }

    // Telegram bot ayarları
    $telegram = new Telegram(getenv('TELEGRAM_BOT_TOKEN'), getenv('TELEGRAM_BOT_USERNAME'));

    // Gelen mesajı işle
    $telegram->handle();
} catch (TelegramException $e) {
    // Hata logla
    error_log($e->getMessage());
}
