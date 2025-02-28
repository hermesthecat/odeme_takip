<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

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
        
        $text = "Merhaba! 👋\n\n" .
                "Ben sizin kişisel finans asistanınızım. Fiş fotoğraflarınızı analiz ederek giderlerinizi takip etmenize yardımcı oluyorum.\n\n" .
                "Kullanabileceğiniz komutlar:\n" .
                "/help - Yardım menüsü\n" .
                "/receipt - Fiş analizi (fotoğraf ile birlikte gönderin)\n\n" .
                "Bir fiş fotoğrafı göndermek için:\n" .
                "1. /receipt komutunu yazın\n" .
                "2. Fişin fotoğrafını çekip gönderin\n" .
                "3. Ben analiz edip size sonuçları göstereceğim\n" .
                "4. Web panelden giriş yapıp onaylayabilirsiniz";
        
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => $text,
            'parse_mode' => 'HTML'
        ]);
    }
} 