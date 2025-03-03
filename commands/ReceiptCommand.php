<?php

/**
 * Fiş Fotoğrafı İşleme Komutu
 * @author A. Kerem Gök
 * @date 2024-02-28
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;
use Exception;

class ReceiptCommand extends UserCommand
{
    protected $name = 'fatura';
    protected $description = 'Fatura fotoğrafını analiz et';
    protected $usage = '/fatura';
    protected $version = '1.0.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        // Fotoğraf kontrolü
        $photo = $message->getPhoto();
        if (empty($photo)) {
            return Request::sendMessage([
                'chat_id' => $chat_id,
                'text'    => 'Lütfen bir fatura ve ya fiş fotoğrafı gönderin.',
            ]);
        }

        // En yüksek çözünürlüklü fotoğrafı al
        $photo = end($photo);
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

            // Gemini API ile fotoğrafı analiz et
            $imageContent = base64_encode(file_get_contents($local_file));

            // Gemini API'ya gönder
            $client = new \GuzzleHttp\Client();

            $headers = [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey
            ];

            $prompt = <<<EOD
            Bu bir fiş veya fatura fotoğrafı. Lütfen aşağıdaki bilgileri çıkar:
            
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
                global $pdo;
                $stmt = $pdo->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, suggested_name, amount, currency, category) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $chat_id,
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
                        "Tarih: {$analysis['date']}\n\n" .
                        "Web panelinden onaylayabilirsiniz: " . getenv('SITE_URL') . "/ai_analysis.php",
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
}
