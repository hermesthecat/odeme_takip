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

        // Kullanıcı doğrulama kontrolü
        global $db;
        $stmt = $db->prepare("SELECT tu.*, u.id as user_id FROM telegram_users tu 
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
                // Google Cloud Vision API ile fotoğrafı analiz et
                $imageContent = base64_encode(file_get_contents($local_file));

                // Gemini API'ya gönder
                $client = new \Google\Client();
                $client->setApiKey(getenv('GEMINI_API_KEY'));

                $prompt = "Bu bir fiş fotoğrafı. Lütfen aşağıdaki bilgileri çıkar:
                
                1. Toplam tutar
                2. Para birimi
                3. Tarih
                4. Mağaza/İşletme adı
                
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
                    'json' => $data
                ]);

                $result = json_decode($response->getBody(), true);

                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $analysis = json_decode($result['candidates'][0]['content']['parts'][0]['text'], true);

                    // Veritabanına kaydet
                    $stmt = $db->prepare("INSERT INTO ai_analysis_temp (user_id, file_name, file_type, description, amount, currency, category, suggested_name) 
                                        VALUES (?, ?, 'receipt', ?, ?, ?, 'expense', ?)");
                    $stmt->execute([
                        $user['user_id'],
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
                        'parse_mode' => 'HTML'
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

        // Diğer mesaj tipleri için yardım menüsünü göster
        return Request::sendMessage([
            'chat_id' => $chat_id,
            'text'    => "Fiş fotoğrafı göndermek için fotoğrafı doğrudan gönderebilirsiniz.\n" .
                "Yardım için /help yazabilirsiniz.",
            'parse_mode' => 'HTML'
        ]);
    }
}
