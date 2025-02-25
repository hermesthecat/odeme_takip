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
            saveLog("Anlik fiyat kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'son_guncelleme'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN son_guncelleme TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
            $pdo->exec($sql);
            saveLog("Son güncelleme kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'hisse_adi'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN hisse_adi VARCHAR(255) DEFAULT ''";
            $pdo->exec($sql);
            saveLog("Hisse adı kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        // Satış bilgileri için yeni kolonlar
        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_fiyati'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_fiyati DECIMAL(10,2) DEFAULT NULL";
            $pdo->exec($sql);
            saveLog("Satış fiyatı kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_tarihi'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_tarihi TIMESTAMP NULL DEFAULT NULL";
            $pdo->exec($sql);
            saveLog("Satış tarihi kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'satis_adet'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN satis_adet INT DEFAULT NULL";
            $pdo->exec($sql);
            saveLog("Satış adedi kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }

        $sql = "SHOW COLUMNS FROM portfolio LIKE 'durum'";
        $stmt = $pdo->query($sql);

        if ($stmt->rowCount() == 0) {
            $sql = "ALTER TABLE portfolio ADD COLUMN durum ENUM('aktif', 'satildi', 'kismi_satildi') DEFAULT 'aktif'";
            $pdo->exec($sql);
            saveLog("Durum kolonu eklendi", 'info', 'tabloyuGuncelle', 0);
        }
    } catch (PDOException $e) {
        saveLog("Tablo güncelleme hatası: " . $e->getMessage(), 'error', 'tabloyuGuncelle', 0);
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
    saveLog("API İsteği URL: " . $url, 'info', 'hisseFiyatiCek', 0);

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
    saveLog("BigPara API İstek - Hisse: " . $sembol, 'info', 'hisseFiyatiCek', 0);
    saveLog("BigPara API Yanıt Kodu: " . $httpcode, 'info', 'hisseFiyatiCek', 0);
    saveLog("BigPara API Ham Yanıt: " . $response, 'info', 'hisseFiyatiCek', 0);

    if ($err) {
        saveLog("BigPara API Curl Hatası: " . $err, 'error', 'hisseFiyatiCek', 0);
        return 0;
    }

    if ($httpcode !== 200) {
        saveLog("BigPara API HTTP Hata Kodu: " . $httpcode, 'error', 'hisseFiyatiCek', 0);
        return 0;
    }

    $data = json_decode($response, true);
    if (!isset($data['data']['hisseYuzeysel'])) {
        saveLog("BigPara API Geçersiz Yanıt: " . print_r($data, true), 'error', 'hisseFiyatiCek', 0);
        return 0;
    }

    $hisse = $data['data']['hisseYuzeysel'];
    saveLog("İşlenen hisse verisi: " . print_r($hisse, true), 'info', 'hisseFiyatiCek', 0);

    // Son işlem fiyatını al (alış-satış ortalaması)
    if (
        isset($hisse['alis']) && isset($hisse['satis']) &&
        is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
    ) {
        $fiyat = ($hisse['alis'] + $hisse['satis']) / 2;
        saveLog("Fiyat alış-satış ortalamasından alındı: " . $fiyat, 'info', 'hisseFiyatiCek', 0);
        return $fiyat;
    }

    // Alternatif olarak son fiyatı kontrol et
    if (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
        $fiyat = floatval($hisse['kapanis']);
        saveLog("Fiyat kapanıştan alındı: " . $fiyat, 'info', 'hisseFiyatiCek', 0);
        return $fiyat;
    }

    saveLog("Hisse fiyatı bulunamadı: " . $sembol, 'error', 'hisseFiyatiCek', 0);
    return 0;
}

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

/**
 * Tüm portföy için fiyat güncellemesi yapar
 * @param int $thread_count İşlem sayısı
 * @return array Güncelleme istatistikleri
 */
function fiyatlariGuncelle($thread_count = 5)
{
    global $pdo;
    $hisseler = benzersizHisseleriGetir();
    $guncellenen = 0;
    $basarisiz = 0;

    // Hisse listesi boşsa işlem yapma
    if (empty($hisseler)) {
        saveLog("Güncellenecek hisse bulunamadı", 'warning', 'fiyatlariGuncelle', 0);
        return [
            'toplam' => 0,
            'guncellenen' => 0,
            'basarisiz' => 0
        ];
    }

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

    // Çok sayıda hisse varsa, paralel işleme yap
    if (count($hisseler) > 20) {
        saveLog("Çok sayıda hisse tespit edildi (" . count($hisseler) . "), paralel işleme başlatılıyor", 'info', 'fiyatlariGuncelle', 0);

        // Hisseleri gruplara böl
        $hisse_gruplari = array_chunk($hisseler, ceil(count($hisseler) / $thread_count));
        $sonuclar = [];

        // Her grup için ayrı bir işlem başlat
        $islemler = [];
        foreach ($hisse_gruplari as $grup_id => $grup) {
            $islem_id = uniqid('hisse_');
            $temp_file = sys_get_temp_dir() . "/{$islem_id}.json";

            // Alt işlem için komut oluştur
            $php_path = PHP_BINARY;
            $script_path = __DIR__ . '/cron_borsa_worker.php';

            // Hisse grubunu geçici dosyaya yaz
            file_put_contents($temp_file, json_encode([
                'hisseler' => $grup,
                'hisse_isimleri' => $hisse_isimleri
            ]));

            // Alt işlemi başlat
            $cmd = "$php_path $script_path $temp_file > /dev/null 2>&1 &";
            exec($cmd);

            $islemler[] = [
                'id' => $islem_id,
                'temp_file' => $temp_file,
                'result_file' => sys_get_temp_dir() . "/{$islem_id}_result.json",
                'count' => count($grup)
            ];

            saveLog("Alt işlem başlatıldı: $islem_id, Hisse sayısı: " . count($grup), 'info', 'fiyatlariGuncelle', 0);
        }

        // Alt işlemlerin tamamlanmasını bekle (maksimum 5 dakika)
        $timeout = time() + 300;
        $tamamlanan = 0;

        while ($tamamlanan < count($islemler) && time() < $timeout) {
            foreach ($islemler as $key => $islem) {
                if (isset($islem['completed'])) continue;

                if (file_exists($islem['result_file'])) {
                    $sonuc = json_decode(file_get_contents($islem['result_file']), true);
                    if ($sonuc) {
                        $guncellenen += $sonuc['guncellenen'];
                        $basarisiz += $sonuc['basarisiz'];
                        $islemler[$key]['completed'] = true;
                        $tamamlanan++;

                        // Geçici dosyaları temizle
                        @unlink($islem['temp_file']);
                        @unlink($islem['result_file']);

                        saveLog("Alt işlem tamamlandı: {$islem['id']}, Güncellenen: {$sonuc['guncellenen']}, Başarısız: {$sonuc['basarisiz']}", 'info', 'fiyatlariGuncelle', 0);
                    }
                }
            }

            // Kısa bir süre bekle
            usleep(500000); // 500ms
        }

        // Zaman aşımı kontrolü
        if (time() >= $timeout) {
            saveLog("Bazı alt işlemler zaman aşımına uğradı!", 'warning', 'fiyatlariGuncelle', 0);
        }
    } else {
        // Az sayıda hisse varsa toplu işleme yap
        $hisse_gruplari = array_chunk($hisseler, 10); // Her seferde 10 hisse işle

        foreach ($hisse_gruplari as $grup) {
            // Toplu fiyat çekme
            $fiyatlar = topluHisseFiyatiCek($grup);

            // Toplu veritabanı güncellemesi
            $sonuc = topluVeritabaniGuncelle($fiyatlar, $hisse_isimleri);
            $guncellenen += $sonuc['guncellenen'];
            $basarisiz += $sonuc['basarisiz'];

            // Başarısız olanları say
            $basarisiz += count($grup) - count($fiyatlar);

            // API limitlerini aşmamak için kısa bir bekleme
            usleep(500000); // 500ms bekle
        }
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
saveLog(sprintf(
    "Cron tamamlandı - Toplam: %d, Güncellenen: %d, Başarısız: %d, Süre: %d saniye",
    $sonuc['toplam'],
    $sonuc['guncellenen'],
    $sonuc['basarisiz'],
    $sure
), 'info', 'fiyatlariGuncelle', 0);

echo "Cron tamamlandı - Toplam: " . $sonuc['toplam'] . ", Güncellenen: " . $sonuc['guncellenen'] . ", Başarısız: " . $sonuc['basarisiz'] . ", Süre: " . $sure . " saniye";
