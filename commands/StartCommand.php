<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Keyboard;

class StartCommand extends SystemCommand
{
    protected $name = 'start';
    protected $description = 'Bot başlatma komutu';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        // Kullanıcının doğrulama durumunu kontrol et
        global $db;
        $stmt = $db->prepare("SELECT * FROM telegram_users WHERE telegram_id = ?");
        $stmt->execute([$chat_id]);
        $telegram_user = $stmt->fetch();

        if (!$telegram_user) {
            // Yeni kullanıcı
            $text = "Merhaba! 👋\n\n" .
                "Ben sizin kişisel finans asistanınızım. Fiş fotoğraflarınızı analiz ederek giderlerinizi takip etmenize yardımcı oluyorum.\n\n" .
                "Başlamak için önce web paneldeki hesabınızla giriş yapmanız gerekiyor.\n\n" .
                "1. Web panele gidin: " . getenv('SITE_URL') . "\n" .
                "2. Hesabınıza giriş yapın\n" .
                "3. Profil sayfanızdan 'Telegram Bağla' butonuna tıklayın\n" .
                "4. Size verilen 6 haneli kodu buraya gönderin\n\n" .
                "Kod formatı: /verify XXXXXX";

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => $text,
                'parse_mode' => 'HTML'
            ]);
        } elseif (!$telegram_user['is_verified']) {
            // Doğrulanmamış kullanıcı
            $text = "Hesabınız henüz doğrulanmamış.\n\n" .
                "Web panelden aldığınız 6 haneli doğrulama kodunu gönderin.\n" .
                "Kod formatı: /verify XXXXXX";

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => $text,
                'parse_mode' => 'HTML'
            ]);
        } else {
            // Doğrulanmış kullanıcı
            $text = "Hoş geldiniz! 👋\n\n" .
                "Fiş fotoğraflarınızı doğrudan gönderebilirsiniz. Ben analiz edip size sonuçları göstereceğim.\n\n" .
                "📝 <b>Önemli Notlar:</b>\n" .
                "- Fiş fotoğrafı net ve okunaklı olmalı\n" .
                "- Fişin tamamı fotoğraf karesinde olmalı\n" .
                "- Mümkünse düz bir zeminde çekim yapın\n\n" .
                "Yardım için /help yazabilirsiniz.";

            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => $text,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}
