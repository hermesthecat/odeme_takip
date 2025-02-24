<?php

/**
 * Borsa Portföy Takip Sistemi - CRON
 * @author A. Kerem Gök
 * @version 1.0
 */

require_once 'config.php';

class BorsaCron
{
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
        $this->tabloyuGuncelle();
    }

    /**
     * Portföy tablosuna anlik_fiyat kolonu ekler
     */
    private function tabloyuGuncelle()
    {
        try {
            $sql = "SHOW COLUMNS FROM portfolio LIKE 'anlik_fiyat'";
            $stmt = $this->db->query($sql);

            if ($stmt->rowCount() == 0) {
                $sql = "ALTER TABLE portfolio ADD COLUMN anlik_fiyat DECIMAL(10,2) DEFAULT 0.00";
                $this->db->exec($sql);
                error_log("Anlik fiyat kolonu eklendi");
            }

            $sql = "SHOW COLUMNS FROM portfolio LIKE 'son_guncelleme'";
            $stmt = $this->db->query($sql);

            if ($stmt->rowCount() == 0) {
                $sql = "ALTER TABLE portfolio ADD COLUMN son_guncelleme TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
                $this->db->exec($sql);
                error_log("Son güncelleme kolonu eklendi");
            }
        } catch (PDOException $e) {
            error_log("Tablo güncelleme hatası: " . $e->getMessage());
        }
    }

    /**
     * Portföydeki benzersiz hisseleri listeler
     */
    private function benzersizHisseleriGetir()
    {
        $sql = "SELECT DISTINCT sembol FROM portfolio";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * BigPara API'den fiyat bilgisi çeker
     */
    private function hisseFiyatiCek($sembol)
    {
        $curl = curl_init();
        $url = "https://bigpara.hurriyet.com.tr/api/v1/borsa/hisseyuzeysel/" . urlencode($sembol);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                "content-type: application/json",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err || $httpcode !== 200) {
            error_log("API Hatası - Hisse: $sembol, HTTP: $httpcode, Hata: $err");
            return 0;
        }

        $data = json_decode($response, true);
        if (!isset($data['data']['hisseYuzeysel'])) {
            error_log("Geçersiz API yanıtı - Hisse: $sembol");
            return 0;
        }

        $hisse = $data['data']['hisseYuzeysel'];

        // Önce alış-satış ortalamasını dene
        if (
            isset($hisse['alis'], $hisse['satis']) &&
            is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
        ) {
            return ($hisse['alis'] + $hisse['satis']) / 2;
        }

        // Yoksa kapanış fiyatını kullan
        if (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
            return floatval($hisse['kapanis']);
        }

        return 0;
    }

    /**
     * Tüm portföy için fiyat güncellemesi yapar
     */
    public function fiyatlariGuncelle()
    {
        $hisseler = $this->benzersizHisseleriGetir();
        $guncellenen = 0;
        $basarisiz = 0;

        foreach ($hisseler as $sembol) {
            $fiyat = $this->hisseFiyatiCek($sembol);

            if ($fiyat > 0) {
                try {
                    // Aynı hissenin tüm kayıtlarını güncelle
                    $sql = "UPDATE portfolio SET 
                            anlik_fiyat = :fiyat,
                            son_guncelleme = CURRENT_TIMESTAMP
                            WHERE sembol = :sembol";

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([
                        'fiyat' => $fiyat,
                        'sembol' => $sembol
                    ]);

                    $guncellenen++;
                    error_log("Fiyat güncellendi - Hisse: $sembol, Fiyat: $fiyat");
                } catch (PDOException $e) {
                    error_log("Veritabanı güncelleme hatası - Hisse: $sembol, Hata: " . $e->getMessage());
                    $basarisiz++;
                }
            } else {
                error_log("Fiyat alınamadı - Hisse: $sembol");
                $basarisiz++;
            }

            // API limitlerini aşmamak için kısa bir bekleme
            usleep(200000); // 200ms bekle
        }

        return [
            'toplam' => count($hisseler),
            'guncellenen' => $guncellenen,
            'basarisiz' => $basarisiz
        ];
    }
}

// Script başlangıç zamanı
$baslangic = microtime(true);

// Cron işlemini başlat
$cron = new BorsaCron();
$sonuc = $cron->fiyatlariGuncelle();

// Bitiş zamanı ve süre hesaplama
$bitis = microtime(true);
$sure = round($bitis - $baslangic, 2);

// Sonuçları logla
error_log(sprintf(
    "Cron tamamlandı - Toplam: %d, Güncellenen: %d, Başarısız: %d, Süre: %d saniye",
    $sonuc['toplam'],
    $sonuc['guncellenen'],
    $sonuc['basarisiz'],
    $sure
));
