<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

class GenericmessageCommand extends SystemCommand
{
    protected $name = 'genericmessage';
    protected $description = 'Genel mesaj işleyici';
    protected $version = '1.0.0';
    
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        
        // Fotoğraf kontrolü
        if ($message->getPhoto() !== null) {
            // ReceiptCommand'ı çağır
            return $this->getTelegram()->executeCommand('receipt');
        }
        
        // Diğer mesaj tipleri için yardım menüsünü göster
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "Fiş fotoğrafı göndermek için önce /receipt komutunu kullanın.\n" .
                        "Yardım için /help yazabilirsiniz.",
            'parse_mode' => 'HTML'
        ]);
    }
} 