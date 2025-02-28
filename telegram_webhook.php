<?php
/**
 * Telegram Webhook İşleyici
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';
require_once 'vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Telegram bot ayarları
    $telegram = new Telegram(getenv('TELEGRAM_BOT_TOKEN'), getenv('TELEGRAM_BOT_USERNAME'));
    
    // Gelen mesajı işle
    $telegram->handle();
    
} catch (TelegramException $e) {
    // Hata logla
    error_log($e->getMessage());
} 