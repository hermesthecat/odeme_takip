<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

class VerifyCommand extends UserCommand
{
    protected $name = 'verify';
    protected $description = 'Hesap doğrulama';
    protected $usage = '/verify <kod>';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = trim($message->getText(true));

        // Kod formatını kontrol et
        if (!preg_match('/^\d{6}$/', $text)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "Geçersiz kod formatı.\n" .
                    "Lütfen 6 haneli kodu gönderin (örn: /verify 123456)",
                'parse_mode' => 'HTML'
            ]);
        }

        // Kodu kontrol et
        global $db;
        $stmt = $db->prepare("SELECT * FROM telegram_users WHERE verification_code = ? AND is_verified = 0");
        $stmt->execute([$text]);
        $user = $stmt->fetch();

        if (!$user) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "Geçersiz veya kullanılmış doğrulama kodu.\n" .
                    "Lütfen web panelden yeni bir kod alın.",
                'parse_mode' => 'HTML'
            ]);
        }

        // Telegram ID'yi güncelle ve hesabı doğrula
        $stmt = $db->prepare("UPDATE telegram_users SET telegram_id = ?, is_verified = 1, verification_code = NULL WHERE id = ?");
        $stmt->execute([$chat_id, $user['id']]);

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "✅ Hesabınız başarıyla doğrulandı!\n\n" .
                "Artık fiş fotoğraflarınızı doğrudan gönderebilirsiniz. Ben analiz edip size sonuçları göstereceğim.\n\n" .
                "📝 <b>Önemli Notlar:</b>\n" .
                "- Fiş fotoğrafı net ve okunaklı olmalı\n" .
                "- Fişin tamamı fotoğraf karesinde olmalı\n" .
                "- Mümkünse düz bir zeminde çekim yapın\n\n" .
                "Yardım için /help yazabilirsiniz.",
            'parse_mode' => 'HTML'
        ]);
    }
}
