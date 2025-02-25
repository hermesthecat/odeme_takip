<?php

/**
 * Borsa Portföy Takip Sistemi - Worker Script
 * @author A. Kerem Gök
 * @version 1.0
 */

// Bu script cron_borsa.php tarafından çağrılır ve belirli bir hisse grubunu işler

require_once 'config.php';
require_once 'classes/log.php'; // Log fonksiyonları
global $pdo;

// Komut satırı argümanlarını kontrol et
if ($argc < 2) {
    die("Kullanım: php cron_borsa_worker.php [veri_dosyası]\n");
}

$veri_dosyasi = $argv[1];
if (!file_exists($veri_dosyasi)) {
    die("Veri dosyası bulunamadı: $veri_dosyasi\n");
}

// Veri dosyasını oku
$veri = json_decode(file_get_contents($veri_dosyasi), true);
if (!$veri || !isset($veri['hisseler']) || !is_array($veri['hisseler'])) {
    die("Geçersiz veri dosyası formatı\n");
}

$hisseler = $veri['hisseler'];
$hisse_isimleri = isset($veri['hisse_isimleri']) ? $veri['hisse_isimleri'] : [];

// İşlem sonuçları
$sonuc = [
    'guncellenen' => 0,
    'basarisiz' => 0
];

// İşlem ID'sini al
$islem_id = basename($veri_dosyasi, '.json');
$sonuc_dosyasi = sys_get_temp_dir() . "/{$islem_id}_result.json";

saveLog("Worker başlatıldı: $islem_id, Hisse sayısı: " . count($hisseler), 'info', 'worker', 0);

/**
 * Toplu hisse fiyatı çekme işlemi
 * @param array $semboller Fiyatı çekilecek hisse sembolleri
 * @return array Sembol => Fiyat eşleşmesi
 */
function topluHisseFiyatiCek($semboller)
{
    if (empty($semboller)) {
        return [];
    }

    saveLog("Toplu hisse fiyatı çekiliyor. Hisse sayısı: " . count($semboller), 'info', 'topluHisseFiyatiCek', 0);

    // Sonuç dizisi
    $sonuclar = [];

    // Multi-curl kullanarak paralel istekler yap
    $multi_curl = curl_multi_init();
    $curl_handles = [];

    // Her sembol için bir curl isteği oluştur
    foreach ($semboller as $sembol) {
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

        // Multi-curl'e ekle
        curl_multi_add_handle($multi_curl, $curl);
        $curl_handles[$sembol] = $curl;
    }

    // İstekleri çalıştır
    $running = null;
    do {
        curl_multi_exec($multi_curl, $running);
        curl_multi_select($multi_curl);
    } while ($running > 0);

    // Sonuçları işle
    foreach ($curl_handles as $sembol => $curl) {
        $response = curl_multi_getcontent($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode === 200) {
            $data = json_decode($response, true);
            if (isset($data['data']['hisseYuzeysel'])) {
                $hisse = $data['data']['hisseYuzeysel'];

                // Son işlem fiyatını al (alış-satış ortalaması)
                if (
                    isset($hisse['alis']) && isset($hisse['satis']) &&
                    is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
                ) {
                    $fiyat = ($hisse['alis'] + $hisse['satis']) / 2;
                    $sonuclar[$sembol] = $fiyat;
                    saveLog("Fiyat alındı (alış-satış ort.) - Hisse: $sembol, Fiyat: $fiyat", 'info', 'topluHisseFiyatiCek', 0);
                }
                // Alternatif olarak son fiyatı kontrol et
                elseif (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
                    $fiyat = floatval($hisse['kapanis']);
                    $sonuclar[$sembol] = $fiyat;
                    saveLog("Fiyat alındı (kapanış) - Hisse: $sembol, Fiyat: $fiyat", 'info', 'topluHisseFiyatiCek', 0);
                } else {
                    saveLog("Hisse fiyatı bulunamadı: " . $sembol, 'error', 'topluHisseFiyatiCek', 0);
                }
            } else {
                saveLog("Geçersiz API yanıtı - Hisse: $sembol", 'error', 'topluHisseFiyatiCek', 0);
            }
        } else {
            saveLog("API HTTP Hata Kodu: " . $httpcode . " - Hisse: $sembol", 'error', 'topluHisseFiyatiCek', 0);
        }

        // Curl handle'ı temizle
        curl_multi_remove_handle($multi_curl, $curl);
    }

    // Multi-curl'ü kapat
    curl_multi_close($multi_curl);

    saveLog("Toplu hisse fiyatı çekme tamamlandı. Başarılı: " . count($sonuclar) . "/" . count($semboller), 'info', 'topluHisseFiyatiCek', 0);
    return $sonuclar;
}

/**
 * Veritabanı işlemlerini toplu olarak yapar
 * @param array $fiyatlar Sembol => Fiyat eşleşmesi
 * @param array $hisse_isimleri Sembol => İsim eşleşmesi
 * @return array Güncelleme istatistikleri
 */
function topluVeritabaniGuncelle($fiyatlar, $hisse_isimleri)
{
    global $pdo;
    $guncellenen = 0;
    $basarisiz = 0;

    if (empty($fiyatlar)) {
        return ['guncellenen' => 0, 'basarisiz' => 0];
    }

    try {
        // Transaction başlat
        $pdo->beginTransaction();

        // Toplu güncelleme için hazırlık
        $sql = "UPDATE portfolio SET 
                    anlik_fiyat = :fiyat,
                    son_guncelleme = CURRENT_TIMESTAMP,
                    hisse_adi = :hisse_adi
                    WHERE sembol = :sembol";
        $stmt = $pdo->prepare($sql);

        // Her hisse için güncelleme yap
        foreach ($fiyatlar as $sembol => $fiyat) {
            $hisse_adi = isset($hisse_isimleri[$sembol]) ? $hisse_isimleri[$sembol] : '';

            $stmt->execute([
                'fiyat' => $fiyat,
                'sembol' => $sembol,
                'hisse_adi' => $hisse_adi
            ]);

            $guncellenen++;
            saveLog("Fiyat ve isim güncellendi - Hisse: $sembol, Ad: $hisse_adi, Fiyat: $fiyat", 'info', 'topluVeritabaniGuncelle', 0);
        }

        // Transaction'ı tamamla
        $pdo->commit();

        saveLog("Toplu veritabanı güncellemesi tamamlandı. Güncellenen: $guncellenen", 'info', 'topluVeritabaniGuncelle', 0);
    } catch (PDOException $e) {
        // Hata durumunda geri al
        $pdo->rollBack();
        $basarisiz = count($fiyatlar);
        $guncellenen = 0;
        saveLog("Toplu veritabanı güncellemesi hatası: " . $e->getMessage(), 'error', 'topluVeritabaniGuncelle', 0);
    }

    return [
        'guncellenen' => $guncellenen,
        'basarisiz' => $basarisiz
    ];
}

// Hisseleri işle
$hisse_gruplari = array_chunk($hisseler, 10); // Her seferde 10 hisse işle

foreach ($hisse_gruplari as $grup) {
    // Toplu fiyat çekme
    $fiyatlar = topluHisseFiyatiCek($grup);

    // Toplu veritabanı güncellemesi
    $sonuc_db = topluVeritabaniGuncelle($fiyatlar, $hisse_isimleri);
    $sonuc['guncellenen'] += $sonuc_db['guncellenen'];
    $sonuc['basarisiz'] += $sonuc_db['basarisiz'];

    // Başarısız olanları say
    $sonuc['basarisiz'] += count($grup) - count($fiyatlar);

    // API limitlerini aşmamak için kısa bir bekleme
    usleep(500000); // 500ms bekle
}

// Sonuçları dosyaya yaz
file_put_contents($sonuc_dosyasi, json_encode($sonuc));
saveLog("Worker tamamlandı: $islem_id, Güncellenen: {$sonuc['guncellenen']}, Başarısız: {$sonuc['basarisiz']}", 'info', 'worker', 0);
