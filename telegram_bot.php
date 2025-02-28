<?php
/**
 * Telegram Bot - Fiş Analiz Sistemi
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

require_once 'config.php';
require_once 'vendor/autoload.php';

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;

try {
    // Telegram bot ayarları
    $telegram = new Telegram(getenv('TELEGRAM_BOT_TOKEN'), getenv('TELEGRAM_BOT_USERNAME'));
    
    // Webhook ayarla
    $telegram->setWebhook(getenv('WEBHOOK_URL') . '/telegram_webhook.php');
    
    // Komutların bulunduğu dizini ayarla
    $telegram->addCommandsPath(__DIR__ . '/commands');
    
    // Bot'u başlat
    $telegram->handle();
    
} catch (Exception $e) {
    // Hata logla
    error_log($e->getMessage());
} 