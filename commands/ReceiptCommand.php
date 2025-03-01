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
    protected $name = 'receipt';
    protected $description = 'Fiş fotoğrafını analiz et';
    protected $usage = '/receipt';
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
                'text'    => 'Lütfen bir fiş fotoğrafı gönderin.',
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
            // Gemini API ile fotoğrafı analiz et
            $imageContent = base64_encode(file_get_contents($local_file));

            // Gemini API'ya gönder
            $client = new \GuzzleHttp\Client();
            
            $headers = [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => getenv('GEMINI_API_KEY')
            ];

            $prompt = "Bu bir fiş fotoğrafı. Lütfen aşağıdaki bilgileri çıkar:
            1. Toplam tutar
            2. Para birimi
            3. Tarih
            4. Mağaza/İşletme adı
            5. Harcama kategorisi
            
            Lütfen JSON formatında yanıt ver.";

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
                global $db;
                $stmt = $db->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, description, amount, currency, category, suggested_name) 
                                    VALUES (?, ?, 'receipt', ?, ?, ?, 'expense', ?)");
                $stmt->execute([
                    $chat_id,
                    basename($local_file),
                    $analysis['mağaza_adı'] . ' - ' . $analysis['tarih'],
                    $analysis['toplam_tutar'],
                    $analysis['para_birimi'],
                    $analysis['kategori']
                ]);

                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => "Fiş analiz edildi!\n\n" .
                        "Mağaza: {$analysis['mağaza_adı']}\n" .
                        "Tutar: {$analysis['toplam_tutar']} {$analysis['para_birimi']}\n" .
                        "Tarih: {$analysis['tarih']}\n" .
                        "Kategori: {$analysis['kategori']}\n\n" .
                        "Web panelinden onaylayabilirsiniz: " . getenv('SITE_URL') . "/ai_analysis.php",
                ]);
            } else {
                return Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text'    => 'Fiş analizi başarısız oldu.',
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
