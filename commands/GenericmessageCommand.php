<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Exception;

class GenericmessageCommand extends SystemCommand
{
    protected $name = 'genericmessage';
    protected $description = 'Genel mesaj işleyici';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        // Kullanıcı doğrulama kontrolü
        global $pdo;
        $stmt = $pdo->prepare("SELECT tu.*, u.id as user_id FROM telegram_users tu 
                             INNER JOIN users u ON u.id = tu.user_id 
                             WHERE tu.telegram_id = ? AND tu.is_verified = 1");
        $stmt->execute([$chat_id]);
        $user = $stmt->fetch();

        if (!$user) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => "Lütfen önce hesabınızı doğrulayın.\n" .
                    "Başlamak için /start yazın.",
                'parse_mode' => 'HTML'
            ]);
        }

        // Fotoğraf kontrolü
        if ($message->getPhoto() !== null) {
            // Fotoğrafı analiz et
            $photo = end($message->getPhoto());
            $file_id = $photo->getFileId();

            // Fotoğrafı indir
            $file = Request::getFile(['file_id' => $file_id]);
            if (!$file->isOk()) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => 'Fotoğraf indirilemedi.',
                ]);
            }

            $file_path = $file->getResult()->getFilePath();
            $local_file = 'uploads/' . uniqid() . '.jpg';

            // Fotoğrafı kaydet
            $downloaded = Request::downloadFile($file->getResult(), $local_file);
            if (!$downloaded) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => 'Fotoğraf kaydedilemedi.',
                ]);
            }

            try {

                // Gemini API anahtarını config'den al
                $apiKey = GEMINI_API_KEY;

                // Google Cloud Vision API ile fotoğrafı analiz et
                $imageContent = base64_encode(file_get_contents($local_file));

                // Gemini API'ya gönder
                $client = new \GuzzleHttp\Client();

                $headers = [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey
                ];

                $prompt = <<<EOD
                Bu bir fatura veya fiş fotoğrafı. Lütfen aşağıdaki bilgileri çıkar:
                
                1. Toplam tutar
                2. Para birimi
                3. Tarih
                4. Mağaza/İşletme adı
                
                Lütfen JSON formatında yanıt ver.
                
                Örnek JSON formatı:
                {
                    "total_amount": 100,
                    "currency": "USD",
                    "date": "2024-01-01",
                    "store_name": "Mağaza Adı"
                }
EOD;

                $data = [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                    'inlineData' => [
                                        'mimeType' => 'image/jpeg',
                                        'data' => $imageContent
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent', [
                    'headers' => $headers,
                    'json' => $data
                ]);

                $result = json_decode($response->getBody(), true);

                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $analysis = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);

                    // Veritabanına kaydet
                    $stmt = $pdo->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, suggested_name, amount, currency, category) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $user['user_id'],
                        basename($local_file),
                        'receipt',
                        $analysis['store_name'] . ' - ' . $analysis['date'],
                        $analysis['total_amount'],
                        $analysis['currency'],
                        'expense'
                    ]);

                    return Request::sendMessage([
                        'chat_id' => $chat_id,
                        'text'    => "Fatura analiz edildi!\n\n" .
                            "Mağaza: {$analysis['store_name']}\n" .
                            "Tutar: {$analysis['total_amount']} {$analysis['currency']}\n" .
                            "Tarih: {$analysis['date']}\n" .
                            "Web panelinden onaylayabilirsiniz: " . getenv('SITE_URL') . "/ai_analysis.php",
                        'parse_mode' => 'HTML'
                    ]);
                } else {
                    return Request::sendMessage([
                        'chat_id' => $chat_id,
                        'text'    => 'Fatura analizi başarısız oldu.',
                    ]);
                }
            } catch (Exception $e) {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => 'Bir hata oluştu: ' . $e->getMessage(),
                ]);
            } finally {
                // Geçici dosyayı sil
                if (file_exists($local_file)) {
                    unlink($local_file);
                }
            }
        }

        // Diğer mesaj tipleri için yardım menüsünü göster
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "Fatura veya fiş fotoğrafı göndermek için fotoğrafı doğrudan gönderebilirsiniz.\n" .
                "Yardım için /help yazabilirsiniz.",
            'parse_mode' => 'HTML'
        ]);
    }
}
