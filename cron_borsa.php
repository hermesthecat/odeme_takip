<?php

/**
 * Borsa Portföy Takip Sistemi - CRON
 * @author A. Kerem Gök
 * @version 1.0
 */

require_once 'config.php';
require_once 'classes/log.php'; // Log fonksiyonları
global $pdo;

/**
 * Portföy tablosuna gerekli kolonları ekler
 */
function tabloyuGuncelle()
{
    global $pdo;

    try {
        $sql = "SHOW COLUMNS FROM portfolio LIKE 'anlik_fiyat'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN anlik_fiyat DECIMAL(10,2) DEFAULT 0.00";
            $pdo->exec($sql);
            var_dump("Anlik fiyat kolonu eklendi");
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'son_guncelleme'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN son_guncelleme TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $pdo->exec($sql);
            var_dump("Son güncelleme kolonu eklendi");
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'hisse_adi'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN hisse_adi VARCHAR(255) DEFAULT ''";
            $pdo->exec($sql);
            var_dump("Hisse adı kolonu eklendi");
        }

        // Satış bilgileri için yeni kolonlar
        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_fiyati'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_fiyati DECIMAL(10,2) DEFAULT NULL";
            $pdo->exec($sql);
            var_dump("Satış fiyatı kolonu eklendi");
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_tarihi'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_tarihi TIMESTAMP NULL DEFAULT NULL";
            $pdo->exec($sql);
            var_dump("Satış tarihi kolonu eklendi");
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_adet'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_adet INT DEFAULT NULL";
            $pdo->exec($sql);
            var_dump("Satış adedi kolonu eklendi");
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'durum'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN durum ENUM('aktif', 'satildi', 'kismi_satildi') DEFAULT 'aktif'";
            $pdo->exec($sql);
            var_dump("Durum kolonu eklendi");
        }
    } catch (PDOException $e) {
        var_dump("Tablo güncelleme hatası: " . $e->getMessage());
    }
}

/**
 * Portföydeki benzersiz hisseleri listeler
 */
function benzersizHisseleriGetir()
{
    global $pdo;
    $sql = "SELECT DISTINCT sembol FROM portfolio";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * BigPara API'den fiyat bilgisi çeker
 */
function hisseFiyatiCek($sembol)
{
    $curl = curl_init();

    // BigPara hisse detay endpoint'i
    $url = "https://bigpara.hurriyet.com.tr/api/v1/borsa/hisseyuzeysel/" . urlencode($sembol);
    var_dump("API İsteği URL: " . $url);

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

    // Debug bilgileri
    var_dump("BigPara API İstek - Hisse: " . $sembol);
    var_dump("BigPara API Yanıt Kodu: " . $httpcode);
    var_dump("BigPara API Hata: " . $err);
    var_dump("BigPara API Ham Yanıt: " . $response);

    if ($err) {
        var_dump("BigPara API Curl Hatası: " . $err);
        return 0;
    }

    if ($httpcode !== 200) {
        var_dump("BigPara API HTTP Hata Kodu: " . $httpcode);
        return 0;
    }

    $data = json_decode($response, true);
    if (!isset($data['data']['hisseYuzeysel'])) {
        var_dump("BigPara API Geçersiz Yanıt: " . print_r($data, true));
        return 0;
    }

    $hisse = $data['data']['hisseYuzeysel'];
    var_dump("İşlenen hisse verisi: " . print_r($hisse, true));

    // Son işlem fiyatını al (alış-satış ortalaması)
    if (
        isset($hisse['alis']) && isset($hisse['satis']) &&
        is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
    ) {
        $fiyat = ($hisse['alis'] + $hisse['satis']) / 2;
        var_dump("Fiyat alış-satış ortalamasından alındı: " . $fiyat);
        return $fiyat;
    }

    // Alternatif olarak son fiyatı kontrol et
    if (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
        $fiyat = floatval($hisse['kapanis']);
        var_dump("Fiyat kapanıştan alındı: " . $fiyat);
        return $fiyat;
    }

    var_dump("Hisse fiyatı bulunamadı: " . $sembol);
    return 0;
}

/**
 * Tüm portföy için fiyat güncellemesi yapar
 */
function fiyatlariGuncelle()
{
    global $pdo;
    $hisseler = benzersizHisseleriGetir();
    $guncellenen = 0;
    $basarisiz = 0;

    // Önce hisse listesini al
    $curl = curl_init();
    $url = "https://bigpara.hurriyet.com.tr/api/v1/hisse/list";

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
    $hisse_listesi = json_decode($response, true);
    curl_close($curl);

    $hisse_isimleri = [];
    if (isset($hisse_listesi['data']) && is_array($hisse_listesi['data'])) {
        foreach ($hisse_listesi['data'] as $hisse) {
            if (!empty($hisse['kod']) && !empty($hisse['ad'])) {
                $hisse_isimleri[$hisse['kod']] = $hisse['ad'];
            }
        }
    }

    foreach ($hisseler as $sembol) {
        $fiyat = hisseFiyatiCek($sembol);
        $hisse_adi = isset($hisse_isimleri[$sembol]) ? $hisse_isimleri[$sembol] : '';

        if ($fiyat > 0) {
            try {
                // Aynı hissenin tüm kayıtlarını güncelle
                $sql = "UPDATE portfolio SET 
                            anlik_fiyat = :fiyat,
                            son_guncelleme = CURRENT_TIMESTAMP,
                            hisse_adi = :hisse_adi
                            WHERE sembol = :sembol";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'fiyat' => $fiyat,
                    'sembol' => $sembol,
                    'hisse_adi' => $hisse_adi
                ]);

                $guncellenen++;
                var_dump("Fiyat ve isim güncellendi - Hisse: $sembol, Ad: $hisse_adi, Fiyat: $fiyat");
            } catch (PDOException $e) {
                var_dump("Veritabanı güncelleme hatası - Hisse: $sembol, Hata: " . $e->getMessage());
                $basarisiz++;
            }
        } else {
            var_dump("Fiyat alınamadı - Hisse: $sembol");
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


// Script başlangıç zamanı
$baslangic = microtime(true);

// Cron işlemini başlat
$sonuc = fiyatlariGuncelle();

// Bitiş zamanı ve süre hesaplama
$bitis = microtime(true);
$sure = round($bitis - $baslangic, 2);

// Sonuçları logla
var_dump(sprintf(
    "Cron tamamlandı - Toplam: %d, Güncellenen: %d, Başarısız: %d, Süre: %d saniye",
    $sonuc['toplam'],
    $sonuc['guncellenen'],
    $sonuc['basarisiz'],
    $sure
));
