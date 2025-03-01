<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

class HelpCommand extends SystemCommand
{
    protected $name = 'help';
    protected $description = 'Yardım menüsü';
    protected $usage = '/help';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $text = "📋 <b>Komut Listesi</b>\n\n" .
            "/start - Botu başlat\n" .
            "/help - Bu yardım menüsü\n" .
            "/receipt - Fiş analizi\n\n" .
            "📝 <b>Fiş Analizi Nasıl Yapılır?</b>\n\n" .
            "1. /receipt komutunu gönderin\n" .
            "2. Fişin net bir fotoğrafını çekin\n" .
            "3. Fotoğrafı bota gönderin\n" .
            "4. Yapay zeka fişi analiz edecek\n" .
            "5. Size sonuçları göstereceğim\n" .
            "6. Web panelden giriş yapıp onaylayabilirsiniz\n\n" .
            "⚠️ <b>Önemli Notlar:</b>\n" .
            "- Fiş fotoğrafı net ve okunaklı olmalı\n" .
            "- Fişin tamamı fotoğraf karesinde olmalı\n" .
            "- Mümkünse düz bir zeminde çekim yapın\n\n" .
            "🌐 Web Panel: " . getenv('SITE_URL');

        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => $text,
            'parse_mode' => 'HTML'
        ]);
    }
}
