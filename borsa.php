<?php

/**
 * Borsa Portföy Takip Sistemi
 * @author A. Kerem Gök
 * @version 1.0
 */

require_once 'config.php';

class BorsaTakip
{
    private $db;

    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    /**
     * Yeni hisse senedi ekler
     */
    public function hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi, $hisse_adi = '')
    {
        // Önce anlık fiyatı API'den al
        $anlik_fiyat = $this->collectApiFiyatCek($sembol);
        error_log("Yeni hisse eklenirken anlık fiyat alındı - Hisse: $sembol, Fiyat: $anlik_fiyat");

        $sql = "INSERT INTO portfolio (sembol, adet, alis_fiyati, alis_tarihi, anlik_fiyat, son_guncelleme, hisse_adi) 
                VALUES (:sembol, :adet, :alis_fiyati, :alis_tarihi, :anlik_fiyat, CURRENT_TIMESTAMP, :hisse_adi)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sembol' => $sembol,
            'adet' => $adet,
            'alis_fiyati' => $alis_fiyati,
            'alis_tarihi' => $alis_tarihi,
            'anlik_fiyat' => $anlik_fiyat,
            'hisse_adi' => $hisse_adi
        ]);
    }

    /**
     * Hisse senedi siler
     */
    public function hisseSil($id)
    {
        // Virgülle ayrılmış ID'leri diziye çevir
        $ids = explode(',', $id);
        $ids = array_map('intval', $ids);

        $sql = "DELETE FROM portfolio WHERE id IN (" . implode(',', $ids) . ")";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Portföydeki hisseleri listeler
     */
    public function portfoyListele()
    {
        // Önce özet bilgileri al
        $sql = "SELECT 
                    sembol,
                    hisse_adi,
                    SUM(adet) as toplam_adet,
                    GROUP_CONCAT(id) as kayit_idler,
                    MAX(son_guncelleme) as son_guncelleme,
                    MAX(anlik_fiyat) as anlik_fiyat
                FROM portfolio 
                GROUP BY sembol, hisse_adi 
                ORDER BY id DESC";

        $stmt = $this->db->query($sql);
        $ozet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sonra tüm kayıtları al
        $sql = "SELECT * FROM portfolio ORDER BY alis_tarihi DESC";
        $stmt = $this->db->query($sql);
        $detaylar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Detayları sembol bazında grupla
        $detay_grup = [];
        foreach ($detaylar as $detay) {
            $detay_grup[$detay['sembol']][] = $detay;
        }

        return [
            'ozet' => $ozet,
            'detaylar' => $detay_grup
        ];
    }

    /**
     * BigPara API'den fiyat bilgisi çeker
     */
    private function collectApiFiyatCek($sembol)
    {
        $curl = curl_init();

        // BigPara hisse detay endpoint'i
        $url = "https://bigpara.hurriyet.com.tr/api/v1/borsa/hisseyuzeysel/" . urlencode($sembol);
        error_log("API İsteği URL: " . $url);

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
        error_log("BigPara API İstek - Hisse: " . $sembol);
        error_log("BigPara API Yanıt Kodu: " . $httpcode);
        error_log("BigPara API Hata: " . $err);
        error_log("BigPara API Ham Yanıt: " . $response);

        if ($err) {
            error_log("BigPara API Curl Hatası: " . $err);
            return 0;
        }

        if ($httpcode !== 200) {
            error_log("BigPara API HTTP Hata Kodu: " . $httpcode);
            return 0;
        }

        $data = json_decode($response, true);
        if (!isset($data['data']['hisseYuzeysel'])) {
            error_log("BigPara API Geçersiz Yanıt: " . print_r($data, true));
            return 0;
        }

        $hisse = $data['data']['hisseYuzeysel'];
        error_log("İşlenen hisse verisi: " . print_r($hisse, true));

        // Son işlem fiyatını al (alış-satış ortalaması)
        if (
            isset($hisse['alis']) && isset($hisse['satis']) &&
            is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
        ) {
            $fiyat = ($hisse['alis'] + $hisse['satis']) / 2;
            error_log("Fiyat alış-satış ortalamasından alındı: " . $fiyat);
            return $fiyat;
        }

        // Alternatif olarak son fiyatı kontrol et
        if (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
            $fiyat = floatval($hisse['kapanis']);
            error_log("Fiyat kapanıştan alındı: " . $fiyat);
            return $fiyat;
        }

        error_log("Hisse fiyatı bulunamadı: " . $sembol);
        return 0;
    }

    /**
     * Anlık fiyat bilgisini getirir
     * @param string $sembol Hisse senedi sembolü
     * @param bool $forceApi API'den zorla fiyat çek (arama için)
     * @return float Hisse fiyatı
     */
    public function anlikFiyatGetir($sembol, $forceApi = false)
    {
        // Eğer API'den fiyat çekilmesi istendiyse (arama için)
        if ($forceApi) {
            $fiyat = $this->collectApiFiyatCek($sembol);
            error_log("Anlık fiyat API'den alındı - Hisse: " . $sembol . ", Fiyat: " . $fiyat);
            return $fiyat;
        }

        // Veritabanından anlık fiyatı al (hisse bazında son fiyat)
        $sql = "SELECT DISTINCT anlik_fiyat, son_guncelleme 
                FROM portfolio 
                WHERE sembol = :sembol 
                ORDER BY son_guncelleme DESC 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sembol' => $sembol]);
        $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sonuc && $sonuc['anlik_fiyat'] > 0) {
            error_log("Anlık fiyat DB'den alındı - Hisse: " . $sembol . ", Fiyat: " . $sonuc['anlik_fiyat'] . ", Son Güncelleme: " . $sonuc['son_guncelleme']);
            return $sonuc['anlik_fiyat'];
        }

        error_log("Anlık fiyat DB'de bulunamadı - Hisse: " . $sembol);
        return 0;
    }

    /**
     * Kar/Zarar durumunu hesaplar
     */
    public function karZararHesapla($hisse)
    {
        $anlik_fiyat = $this->anlikFiyatGetir($hisse['sembol']);
        error_log("Kar/Zarar Hesaplama - Hisse: " . $hisse['sembol']);
        error_log("Alış Fiyatı: " . $hisse['alis_fiyati']);
        error_log("Anlık Fiyat: " . $anlik_fiyat);
        error_log("Adet: " . $hisse['adet']);

        // Kar/Zarar = (Güncel Fiyat - Alış Fiyatı) * Adet
        $kar_zarar = ($anlik_fiyat - $hisse['alis_fiyati']) * $hisse['adet'];

        error_log("Kar/Zarar: " . $kar_zarar);
        return $kar_zarar;
    }

    /**
     * Türkçe karakterleri İngilizce karakterlere çevirir
     */
    private function turkceKarakterleriCevir($str)
    {
        $turkce = array("ı", "ğ", "ü", "ş", "ö", "ç", "İ", "Ğ", "Ü", "Ş", "Ö", "Ç");
        $ingilizce = array("i", "g", "u", "s", "o", "c", "I", "G", "U", "S", "O", "C");
        return str_replace($turkce, $ingilizce, $str);
    }

    /**
     * Hisse senedi ara
     */
    public function hisseAra($aranan)
    {
        $curl = curl_init();

        $url = "https://bigpara.hurriyet.com.tr/api/v1/hisse/list";
        error_log("Hisse Listesi API İsteği URL: " . $url);

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
        $content_type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        // Debug bilgileri
        error_log("Hisse Listesi API Yanıt Kodu: " . $httpcode);
        error_log("Hisse Listesi API Content-Type: " . $content_type);
        error_log("Hisse Listesi API Hata: " . $err);
        error_log("Hisse Listesi API Ham Yanıt: " . $response);

        if ($err) {
            error_log("Hisse Listesi API Curl Hatası: " . $err);
            return [
                ['code' => 'ERROR', 'title' => 'API Hatası: ' . $err, 'price' => '0.00']
            ];
        }

        if ($httpcode !== 200) {
            error_log("Hisse Listesi API HTTP Hata Kodu: " . $httpcode);
            return [
                ['code' => 'ERROR', 'title' => 'API HTTP Hatası: ' . $httpcode, 'price' => '0.00']
            ];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Hatası: " . json_last_error_msg());
            error_log("Ham Yanıt: " . $response);
            return [
                ['code' => 'ERROR', 'title' => 'JSON Parse Hatası: ' . json_last_error_msg(), 'price' => '0.00']
            ];
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            error_log("Hisse Listesi API Geçersiz Yanıt: " . print_r($data, true));
            return [
                ['code' => 'ERROR', 'title' => 'API Yanıt Formatı Hatası', 'price' => '0.00']
            ];
        }

        // Aranan metni Türkçe karakterlerden arındır ve büyük harfe çevir
        $aranan = mb_strtoupper($this->turkceKarakterleriCevir($aranan), 'UTF-8');
        $sonuclar = [];
        $limit = 10; // Maksimum 10 sonuç göster
        $count = 0;

        foreach ($data['data'] as $hisse) {
            // Boş kayıtları atla
            if (empty($hisse['kod']) || empty($hisse['ad'])) {
                continue;
            }

            // Hisse kodu ve adını Türkçe karakterlerden arındır
            $hisse_kod = mb_strtoupper($this->turkceKarakterleriCevir($hisse['kod']), 'UTF-8');
            $hisse_ad = mb_strtoupper($this->turkceKarakterleriCevir($hisse['ad']), 'UTF-8');

            if (
                strpos($hisse_kod, $aranan) !== false ||
                strpos($hisse_ad, $aranan) !== false
            ) {
                // Hisse detayını çek
                $curl = curl_init();
                $url = "https://bigpara.hurriyet.com.tr/api/v1/borsa/hisseyuzeysel/" . urlencode($hisse['kod']);

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
                $data_hisse = json_decode($response, true);
                curl_close($curl);

                $fiyat = 0;
                if (isset($data_hisse['data']['hisseYuzeysel'])) {
                    $detay = $data_hisse['data']['hisseYuzeysel'];
                    if (isset($detay['alis'], $detay['satis']) && is_numeric($detay['alis']) && is_numeric($detay['satis'])) {
                        $fiyat = ($detay['alis'] + $detay['satis']) / 2;
                    } elseif (isset($detay['kapanis']) && is_numeric($detay['kapanis'])) {
                        $fiyat = floatval($detay['kapanis']);
                    }
                }

                $sonuclar[] = [
                    'code' => $hisse['kod'],
                    'title' => $hisse['ad'],
                    'price' => number_format($fiyat, 2, '.', '')
                ];

                $count++;
                if ($count >= $limit) {
                    break;
                }

                // API limitlerini aşmamak için kısa bir bekleme
                usleep(200000); // 200ms bekle
            }
        }

        // Sonuç bulunamadıysa bilgi mesajı döndür
        if (empty($sonuclar)) {
            return [
                ['code' => 'INFO', 'title' => 'Sonuç bulunamadı: ' . $aranan, 'price' => '0.00']
            ];
        }

        return $sonuclar;
    }

    /**
     * Hisse satış kaydı ekler (FIFO mantığı ile)
     */
    public function hisseSat($id, $satis_adet, $satis_fiyati)
    {
        try {
            // İşlemi transaction içinde yap
            $this->db->beginTransaction();

            // Önce satılacak hissenin sembolünü bul
            $sql = "SELECT sembol FROM portfolio WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $sembol = $stmt->fetchColumn();

            if (!$sembol) {
                throw new Exception("Hisse bulunamadı");
            }

            // Bu sembole ait tüm aktif kayıtları alış tarihine göre sırala (FIFO)
            $sql = "SELECT * FROM portfolio 
                    WHERE sembol = :sembol 
                    AND (durum = 'aktif' OR durum = 'kismi_satildi')
                    ORDER BY alis_tarihi ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['sembol' => $sembol]);
            $kayitlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $kalan_satis_adet = $satis_adet;

            foreach ($kayitlar as $kayit) {
                // Her kayıt için satılabilecek maksimum adedi hesapla
                $mevcut_satis = $kayit['satis_adet'] ? $kayit['satis_adet'] : 0;
                $satilabilir_adet = $kayit['adet'] - $mevcut_satis;

                if ($satilabilir_adet <= 0) continue;

                // Bu kayıttan satılacak adedi belirle
                $bu_satis_adet = min($kalan_satis_adet, $satilabilir_adet);

                if ($bu_satis_adet <= 0) break;

                // Satış durumunu belirle
                $yeni_durum = ($bu_satis_adet + $mevcut_satis == $kayit['adet']) ? 'satildi' : 'kismi_satildi';

                // Satış kaydını güncelle
                $sql = "UPDATE portfolio SET 
                        satis_fiyati = :satis_fiyati,
                        satis_tarihi = CURRENT_TIMESTAMP,
                        satis_adet = IFNULL(satis_adet, 0) + :satis_adet,
                        durum = :durum
                        WHERE id = :id";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'satis_fiyati' => $satis_fiyati,
                    'satis_adet' => $bu_satis_adet,
                    'durum' => $yeni_durum,
                    'id' => $kayit['id']
                ]);

                $kalan_satis_adet -= $bu_satis_adet;

                error_log("Satış kaydı eklendi - ID: {$kayit['id']}, Adet: $bu_satis_adet, Fiyat: $satis_fiyati");

                if ($kalan_satis_adet <= 0) break;
            }

            if ($kalan_satis_adet > 0) {
                throw new Exception("Yeterli satılabilir hisse bulunamadı");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Satış hatası: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Satış karını hesaplar
     */
    public function satisKariHesapla($kayit)
    {
        if ($kayit['durum'] == 'aktif' || !$kayit['satis_fiyati']) {
            return null;
        }

        $satis_adet = $kayit['satis_adet'] ?? 0;
        $kar = ($kayit['satis_fiyati'] - $kayit['alis_fiyati']) * $satis_adet;
        return $kar;
    }
}

// POST işlemlerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borsaTakip = new BorsaTakip();

    if (isset($_POST['sembol'], $_POST['adet'], $_POST['alis_fiyati'], $_POST['hisse_adi'])) {
        $sembol = strtoupper(trim($_POST['sembol']));
        $adet = intval($_POST['adet']);
        $alis_fiyati = floatval($_POST['alis_fiyati']);
        $hisse_adi = trim($_POST['hisse_adi']);
        $alis_tarihi = date('Y-m-d H:i:s');

        if ($borsaTakip->hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi, $hisse_adi)) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// AJAX silme işlemi için
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $borsaTakip = new BorsaTakip();
    if ($borsaTakip->hisseSil($_GET['sil'])) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// AJAX satış işlemi için
if (isset($_GET['sat'], $_GET['id'], $_GET['adet'], $_GET['fiyat'])) {
    $borsaTakip = new BorsaTakip();
    if ($borsaTakip->hisseSat($_GET['id'], $_GET['adet'], $_GET['fiyat'])) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// Sayfa yüklendiğinde portföy listesini getir
if (isset($_GET['liste'])) {
    $borsaTakip = new BorsaTakip();
    $portfoy = $borsaTakip->portfoyListele();

    foreach ($portfoy['ozet'] as $hisse) {
        // Debug bilgileri
        error_log("Portföy Listesi - İşlenen Hisse: " . print_r($hisse, true));

        $anlik_fiyat = $borsaTakip->anlikFiyatGetir($hisse['sembol']);
        error_log("Anlık Fiyat Alındı: " . $anlik_fiyat . " için " . $hisse['sembol']);

        if ($anlik_fiyat <= 0) {
            error_log("UYARI: Sıfır veya negatif fiyat tespit edildi: " . $hisse['sembol']);
            // Veritabanında fiyat yoksa API'den dene
            $anlik_fiyat = $borsaTakip->anlikFiyatGetir($hisse['sembol'], true);
            error_log("API'den Alınan Fiyat: " . $anlik_fiyat);
        }

        // Toplam kar/zarar ve satış karı hesapla
        $toplam_kar_zarar = 0;
        $toplam_satis_kari = 0;
        foreach ($portfoy['detaylar'][$hisse['sembol']] as $detay) {
            $kalan_adet = $detay['adet'];
            if (isset($detay['satis_adet']) && $detay['satis_adet'] > 0) {
                $kalan_adet -= $detay['satis_adet'];
                // Satış karı hesapla
                $satis_kari = ($detay['satis_fiyati'] - $detay['alis_fiyati']) * $detay['satis_adet'];
                $toplam_satis_kari += $satis_kari;
            }
            // Kalan hisselerin kar/zararı
            if ($kalan_adet > 0) {
                $toplam_kar_zarar += ($anlik_fiyat - $detay['alis_fiyati']) * $kalan_adet;
            }
        }
        $kar_zarar_class = $toplam_kar_zarar >= 0 ? 'kar' : 'zarar';
        $satis_kar_class = $toplam_satis_kari >= 0 ? 'kar' : 'zarar';

        // Son güncelleme zamanını al
        $son_guncelleme = isset($hisse['son_guncelleme']) ? date('H:i:s', strtotime($hisse['son_guncelleme'])) : '';
        $guncelleme_bilgisi = $son_guncelleme ? " <small class='text-muted'>($son_guncelleme)</small>" : '';

        // Hisse adını ve sembolü birleştir
        $hisse_baslik = $hisse['hisse_adi'] ? $hisse['hisse_adi'] . " (" . $hisse['sembol'] . ")" : $hisse['sembol'];

        // Ana satır
        $html_output = "<tr class='ana-satir' data-sembol='{$hisse['sembol']}' style='cursor: pointer;'>
                <td class='sembol'><i class='fas fa-chevron-right me-2'></i>{$hisse_baslik}</td>
                <td class='adet'>{$hisse['toplam_adet']}</td>
                <td class='alis_fiyati'>";

        // Alış fiyatı kontrolü - tek alım varsa fiyatını göster, değilse "Çeşitli" yaz
        $alim_sayisi = count($portfoy['detaylar'][$hisse['sembol']]);
        if ($alim_sayisi == 1) {
            $tek_alim = reset($portfoy['detaylar'][$hisse['sembol']]);
            $html_output .= number_format($tek_alim['alis_fiyati'], 2) . " ₺";
        } else {
            $html_output .= "Çeşitli";
        }

        $html_output .= "</td>
                <td class='anlik_fiyat'>{$anlik_fiyat} ₺{$guncelleme_bilgisi}</td>
                <td class='{$kar_zarar_class}'>" . number_format($toplam_kar_zarar, 2) . " ₺</td>
                <td class='{$satis_kar_class}'>" . number_format($toplam_satis_kari, 2) . " ₺</td>
                <td>
                    <button class='btn btn-success btn-sm' onclick='topluSatisFormunuGoster(\"{$hisse['sembol']}\", {$anlik_fiyat}, event)'>Sat</button>
                    <button class='btn btn-danger btn-sm ms-1' onclick='hisseSil(\"{$hisse['kayit_idler']}\", event)'>Tümünü Sil</button>
                </td>
            </tr>";

        // Detay satırları (başlangıçta gizli)
        $html_output .= "<tr class='detay-satir' data-sembol='{$hisse['sembol']}' style='display: none; background-color: #f8f9fa;'><td colspan='7'><div class='p-3'>";

        // Satış formu
        $html_output .= "<div id='satis-form-{$hisse['sembol']}' class='satis-form mb-3' style='display:none;'>
            <div class='card'>
                <div class='card-header'>
                    <h6 class='mb-0'>Satış Detayları</h6>
                </div>
                <div class='card-body'>
                    <div class='table-responsive'>
                        <table class='table table-sm'>
                            <thead>
                                <tr>
                                    <th>Seç</th>
                                    <th>Alış Tarihi</th>
                                    <th>Alış Fiyatı</th>
                                    <th>Kalan Adet</th>
                                    <th>Satılacak Adet</th>
                                </tr>
                            </thead>
                            <tbody>";

        // Aktif alım kayıtlarını listele
        foreach ($portfoy['detaylar'][$hisse['sembol']] as $detay) {
            $kalan_adet = $detay['adet'];
            if (isset($detay['satis_adet'])) {
                $kalan_adet -= $detay['satis_adet'];
            }

            if ($kalan_adet > 0) {
                $alis_tarihi = date('d.m.Y H:i', strtotime($detay['alis_tarihi']));
                $html_output .= "<tr data-alis-fiyati='{$detay['alis_fiyati']}' data-alis-tarihi='{$detay['alis_tarihi']}'>
                    <td>
                        <input type='checkbox' class='form-check-input satis-secim' 
                               data-id='{$detay['id']}' data-max-adet='{$kalan_adet}' checked disabled>
                    </td>
                    <td>{$alis_tarihi}</td>
                    <td>{$detay['alis_fiyati']} ₺</td>
                    <td>{$kalan_adet}</td>
                    <td>
                        <input type='number' class='form-control form-control-sm satis-adet' 
                               min='0' max='{$kalan_adet}' value='0'>
                    </td>
                </tr>";
            }
        }

        $html_output .= "</tbody></table>
                    </div>
                    <div class='row mt-3'>
                        <div class='col-md-4'>
                            <div class='input-group input-group-sm'>
                                <span class='input-group-text'>Satış Fiyatı</span>
                                <input type='number' class='form-control' id='satis-fiyat-{$hisse['sembol']}' 
                                       step='0.01' value='{$anlik_fiyat}'>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <div class='input-group input-group-sm'>
                                <span class='input-group-text'>Toplam Satış Adedi</span>
                                <input type='number' class='form-control' id='toplam-satis-adet-{$hisse['sembol']}' 
                                       min='0' max='{$hisse['toplam_adet']}' value='0'>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <small class='text-muted'>Tahmini Kar/Zarar: <span id='kar-zarar-{$hisse['sembol']}'>0.00 ₺</span></small>
                        </div>
                    </div>
                    <div class='mt-3'>
                        <button class='btn btn-primary btn-sm' onclick='topluSatisKaydet(\"{$hisse['sembol']}\")'>Satışı Onayla</button>
                        <button class='btn btn-secondary btn-sm' onclick='topluSatisFormunuGizle(\"{$hisse['sembol']}\", event)'>İptal</button>
                    </div>
                </div>
            </div>
        </div>";

        // Alım detayları tablosu
        $html_output .= "<table class='table table-sm mb-0'><thead><tr>
                <th>Alış Tarihi</th>
                <th>Adet</th>
                <th>Alış Fiyatı</th>
                <th>Güncel Fiyat</th>
                <th>Kar/Zarar</th>
                <th>Satış Durumu</th>
                <th>İşlem</th>
            </tr></thead><tbody>";

        foreach ($portfoy['detaylar'][$hisse['sembol']] as $detay) {
            $detay_kar_zarar = ($anlik_fiyat - $detay['alis_fiyati']) * $detay['adet'];
            $detay_kar_zarar_class = $detay_kar_zarar >= 0 ? 'kar' : 'zarar';
            $alis_tarihi = date('d.m.Y H:i', strtotime($detay['alis_tarihi']));

            // Satış durumu ve kalan adet hesapla
            $satis_durumu = '';
            $kalan_adet = $detay['adet'];
            if ($detay['durum'] == 'satildi') {
                $satis_durumu = '<span class="badge bg-success">Satıldı</span>';
                $kalan_adet = 0;
            } elseif ($detay['durum'] == 'kismi_satildi') {
                $satilan_adet = $detay['satis_adet'] ?? 0;
                $kalan_adet = $detay['adet'] - $satilan_adet;
                $satis_durumu = "<span class='badge bg-warning'>{$satilan_adet} Adet Satıldı</span>";
            }

            $html_output .= "<tr>
                <td>{$alis_tarihi}</td>
                <td>{$detay['adet']}</td>
                <td>{$detay['alis_fiyati']} ₺</td>
                <td>{$anlik_fiyat} ₺</td>
                <td class='{$detay_kar_zarar_class}'>" . number_format($detay_kar_zarar, 2) . " ₺</td>
                <td>{$satis_durumu}</td>
                <td>
                    <button class='btn btn-danger btn-sm' onclick='hisseSil({$detay['id']}, event)'>Sil</button>
                </td>
            </tr>";

            // Satış detayları (eğer satış yapılmışsa)
            if ($detay['durum'] != 'aktif' && isset($detay['satis_fiyati'])) {
                $satis_tarihi = date('d.m.Y H:i', strtotime($detay['satis_tarihi']));
                $satis_kar = ($detay['satis_fiyati'] - $detay['alis_fiyati']) * $detay['satis_adet'];
                $satis_kar_class = $satis_kar >= 0 ? 'kar' : 'zarar';

                $html_output .= "<tr class='table-light'>
                    <td><small><i>Satış: {$satis_tarihi}</i></small></td>
                    <td><small>{$detay['satis_adet']}</small></td>
                    <td>-</td>
                    <td><small>{$detay['satis_fiyati']} ₺</small></td>
                    <td class='{$satis_kar_class}'><small>" . number_format($satis_kar, 2) . " ₺</small></td>
                    <td colspan='2'></td>
                </tr>";
            }
        }

        $html_output .= "</tbody></table></div></td></tr>";

        error_log("Oluşturulan HTML: " . $html_output);
        echo $html_output;
    }
    exit;
}

// Hisse arama API endpoint'i
if (isset($_GET['ara'])) {
    header('Content-Type: application/json');
    $borsaTakip = new BorsaTakip();
    $sonuclar = $borsaTakip->hisseAra($_GET['ara']);

    // Debug için API yanıtını logla
    error_log("Search Results for '" . $_GET['ara'] . "': " . print_r($sonuclar, true));

    echo json_encode($sonuclar);
    exit;
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Borsa Portföy Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="borsa.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Portföy Takip Sistemi</h1>

        <!-- Yeni Hisse Ekleme Formu -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Yeni Hisse Ekle</span>
                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#yeniHisseForm">
                    <i class="fas fa-plus me-1"></i> Yeni Ekle
                </button>
            </div>
            <div class="collapse" id="yeniHisseForm">
                <div class="card-body">
                    <form method="POST" action="" id="hisseForm" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating sembol-input-container">
                                    <input type="text" name="sembol" id="sembolInput" class="form-control"
                                        placeholder="Hisse Sembolü veya Adı" required autocomplete="off">
                                    <label for="sembolInput">Hisse Sembolü veya Adı</label>
                                    <div id="sembolOnerileri" class="autocomplete-items shadow-sm"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" name="adet" id="adetInput" class="form-control"
                                        placeholder="Adet" required min="1">
                                    <label for="adetInput">Adet</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-floating">
                                    <input type="number" step="0.01" name="alis_fiyati" id="alisFiyatiInput"
                                        class="form-control" placeholder="Alış Fiyatı" required min="0.01">
                                    <label for="alisFiyatiInput">Alış Fiyatı (₺)</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="hisse_adi" value="">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="alert alert-info mb-0 d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <div>
                                        <strong>Tahmini Maliyet:</strong>
                                        <span id="tahminiMaliyet">0.00 ₺</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-1"></i> Hisseyi Ekle
                                </button>
                                <button type="reset" class="btn btn-secondary ms-2">
                                    <i class="fas fa-undo me-1"></i> Temizle
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mali Durum Grafiği -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Mali Durum Özeti</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" type="button" data-bs-toggle="collapse" data-bs-target="#maliDurumDetay">
                        <i class="fas fa-chart-pie me-1"></i> Grafik
                    </button>
                </div>
            </div>
            <div class="collapse show" id="maliDurumDetay">
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <canvas id="maliDurumGrafik" style="max-height: 200px;"></canvas>
                        </div>
                        <div class="col-md-7">
                            <div class="row row-cols-1 row-cols-md-3 g-2">
                                <div class="col">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">Portföy Değeri</small>
                                        <h5 id="toplamPortfoyDeger" class="mb-0 mt-1">0.00 ₺</h5>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">Kar/Zarar</small>
                                        <h5 id="toplamKarZarar" class="mb-0 mt-1">0.00 ₺</h5>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border rounded p-2 h-100">
                                        <small class="text-muted d-block">Gerçekleşen K/Z</small>
                                        <h5 id="toplamSatisKar" class="mb-0 mt-1">0.00 ₺</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portföy Listesi -->
        <div class="card">
            <div class="card-header">Portföyüm</div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hisse</th>
                            <th>Adet</th>
                            <th>Alış Fiyatı</th>
                            <th>Güncel Fiyat</th>
                            <th>Kar/Zarar</th>
                            <th>Satış Karı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="portfoyListesi">
                        <!-- JavaScript ile doldurulacak -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validasyonu için
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Tahmini maliyet hesaplama
        function tahminiMaliyetHesapla() {
            const adet = parseFloat(document.getElementById('adetInput').value) || 0;
            const fiyat = parseFloat(document.getElementById('alisFiyatiInput').value) || 0;
            const maliyet = adet * fiyat;
            document.getElementById('tahminiMaliyet').textContent =
                new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(maliyet);
        }

        // Input değişikliklerini dinle
        document.getElementById('adetInput').addEventListener('input', tahminiMaliyetHesapla);
        document.getElementById('alisFiyatiInput').addEventListener('input', tahminiMaliyetHesapla);

        // Otomatik tamamlama stil güncellemesi
        document.getElementById('sembolOnerileri').style.cssText = `
            position: absolute;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            background: white;
            border-radius: 0.375rem;
            border: 1px solid rgba(0,0,0,.125);
            margin-top: 2px;
        `;

        // Öneri öğelerinin stilini güncelle
        const style = document.createElement('style');
        style.textContent = `
            .autocomplete-items div {
                padding: 10px 15px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
            }
            .autocomplete-items div:hover {
                background-color: #f8f9fa;
            }
            .autocomplete-items div:last-child {
                border-bottom: none;
            }
            .fiyat-bilgisi {
                float: right;
                color: #6c757d;
            }
            .form-floating > .form-control::placeholder {
                color: transparent;
            }
            .form-floating > .form-control:not(:placeholder-shown) ~ label {
                opacity: .65;
                transform: scale(.85) translateY(-.5rem) translateX(.15rem);
            }
        `;
        document.head.appendChild(style);

        // Mevcut JavaScript kodları buraya gelecek

        // Mali durum grafiği için yeni fonksiyonlar
        let maliDurumChart = null;

        function maliDurumGrafigiGuncelle(portfoyData) {
            const ctx = document.getElementById('maliDurumGrafik').getContext('2d');

            // Eğer grafik zaten varsa yok et
            if (maliDurumChart) {
                maliDurumChart.destroy();
            }

            let toplamDeger = 0;
            let toplamKarZarar = 0;
            let toplamSatisKar = 0;
            const hisseler = [];
            const degerler = [];
            const renkler = [];

            // Verileri hazırla
            portfoyData.forEach(hisse => {
                const guncelDeger = parseFloat(hisse.anlik_fiyat) * parseInt(hisse.toplam_adet);
                toplamDeger += guncelDeger;

                if (guncelDeger > 0) {
                    hisseler.push(hisse.sembol);
                    degerler.push(guncelDeger);
                    // Rastgele renk üret
                    renkler.push('#' + Math.floor(Math.random() * 16777215).toString(16));
                }
            });

            // Grafiği oluştur
            maliDurumChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: hisseler,
                    datasets: [{
                        data: degerler,
                        backgroundColor: renkler,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Portföy Dağılımı'
                        }
                    }
                }
            });

            // Özet bilgileri güncelle
            document.getElementById('toplamPortfoyDeger').textContent =
                new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(toplamDeger);

            // Kar/zarar ve satış karı bilgilerini güncelle
            let totalKarZarar = 0;
            let totalSatisKar = 0;

            portfoyData.forEach(hisse => {
                const detaylar = document.querySelector(`.ana-satir[data-sembol="${hisse.sembol}"]`);
                if (detaylar) {
                    const karZararElement = detaylar.querySelector('.kar, .zarar');
                    const satisKarElement = detaylar.querySelector('.kar:last-child, .zarar:last-child');

                    if (karZararElement) {
                        const karZararText = karZararElement.textContent;
                        totalKarZarar += parseFloat(karZararText.replace(/[^0-9.-]+/g, ""));
                    }

                    if (satisKarElement) {
                        const satisKarText = satisKarElement.textContent;
                        totalSatisKar += parseFloat(satisKarText.replace(/[^0-9.-]+/g, ""));
                    }
                }
            });

            document.getElementById('toplamKarZarar').textContent =
                new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(totalKarZarar);
            document.getElementById('toplamSatisKar').textContent =
                new Intl.NumberFormat('tr-TR', {
                    style: 'currency',
                    currency: 'TRY'
                }).format(totalSatisKar);

            // Renk sınıflarını güncelle
            document.getElementById('toplamKarZarar').className = totalKarZarar >= 0 ? 'text-success' : 'text-danger';
            document.getElementById('toplamSatisKar').className = totalSatisKar >= 0 ? 'text-success' : 'text-danger';
        }

        // Portföy güncelleme fonksiyonunu güncelle
        function portfoyGuncelle() {
            fetch('borsa.php?liste=1')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('portfoyListesi').innerHTML = data;

                    // Portföy verilerini topla
                    const portfoyData = [];
                    document.querySelectorAll('.ana-satir').forEach(row => {
                        portfoyData.push({
                            sembol: row.dataset.sembol,
                            toplam_adet: parseInt(row.querySelector('.adet').textContent),
                            anlik_fiyat: parseFloat(row.querySelector('.anlik_fiyat').textContent)
                        });
                    });

                    // Mali durum grafiğini güncelle
                    maliDurumGrafigiGuncelle(portfoyData);

                    // Tıklama olaylarını ekle
                    document.querySelectorAll('.ana-satir').forEach(row => {
                        row.addEventListener('click', function() {
                            const sembol = this.dataset.sembol;
                            const detayRow = document.querySelector(`.detay-satir[data-sembol="${sembol}"]`);
                            const icon = this.querySelector('.fas');

                            if (detayRow.style.display === 'none') {
                                detayRow.style.display = 'table-row';
                                icon.classList.remove('fa-chevron-right');
                                icon.classList.add('fa-chevron-down');
                            } else {
                                detayRow.style.display = 'none';
                                icon.classList.remove('fa-chevron-down');
                                icon.classList.add('fa-chevron-right');
                            }
                        });
                    });
                })
                .catch(error => console.error('Hata:', error));
        }

        // Toplu satış formunu göster
        function topluSatisFormunuGoster(sembol, guncelFiyat, event) {
            if (event) {
                event.stopPropagation();
            }

            const detayRow = document.querySelector(`.detay-satir[data-sembol="${sembol}"]`);
            if (detayRow) {
                detayRow.style.display = 'table-row';
                const anaSatir = document.querySelector(`.ana-satir[data-sembol="${sembol}"]`);
                if (anaSatir) {
                    const icon = anaSatir.querySelector('.fas');
                    icon.classList.remove('fa-chevron-right');
                    icon.classList.add('fa-chevron-down');
                }
            }

            const form = document.getElementById(`satis-form-${sembol}`);
            if (form) {
                form.style.display = 'block';
                document.getElementById(`satis-fiyat-${sembol}`).value = guncelFiyat;

                // Toplam satış adedi inputunu dinle
                const toplamAdetInput = document.getElementById(`toplam-satis-adet-${sembol}`);
                if (toplamAdetInput) {
                    toplamAdetInput.addEventListener('input', function() {
                        dagitimYap(sembol, this.value);
                    });
                }

                // Fiyat inputunu dinle
                const fiyatInput = document.getElementById(`satis-fiyat-${sembol}`);
                if (fiyatInput) {
                    fiyatInput.addEventListener('input', () => karZararHesapla(sembol));
                }
            }
        }

        // FIFO mantığına göre adetleri dağıt
        function dagitimYap(sembol, toplamAdet) {
            const form = document.getElementById(`satis-form-${sembol}`);
            if (!form) return;

            const satirlar = Array.from(form.querySelectorAll('tr[data-alis-tarihi]'))
                .sort((a, b) => new Date(a.dataset.alisTarihi) - new Date(b.dataset.alisTarihi));

            let kalanAdet = parseInt(toplamAdet) || 0;

            satirlar.forEach(satir => {
                const adetInput = satir.querySelector('.satis-adet');
                const maxAdet = parseInt(adetInput.max);

                if (kalanAdet > 0) {
                    const dagitilacakAdet = Math.min(kalanAdet, maxAdet);
                    adetInput.value = dagitilacakAdet;
                    kalanAdet -= dagitilacakAdet;
                } else {
                    adetInput.value = 0;
                }
            });

            karZararHesapla(sembol);
        }

        // Kar/zarar hesaplama fonksiyonunu güncelle
        function karZararHesapla(sembol) {
            const form = document.getElementById(`satis-form-${sembol}`);
            const fiyatInput = document.getElementById(`satis-fiyat-${sembol}`);
            const satisFiyati = fiyatInput ? (parseFloat(fiyatInput.value) || 0) : 0;
            let toplamKar = 0;

            if (form) {
                form.querySelectorAll('tr[data-alis-fiyati]').forEach(row => {
                    const alisFiyati = parseFloat(row.dataset.alisFiyati);
                    const adetInput = row.querySelector('.satis-adet');
                    const adet = adetInput ? (parseFloat(adetInput.value) || 0) : 0;
                    toplamKar += (satisFiyati - alisFiyati) * adet;
                });

                const karZararSpan = document.getElementById(`kar-zarar-${sembol}`);
                if (karZararSpan) {
                    karZararSpan.textContent = toplamKar.toFixed(2) + ' ₺';
                    karZararSpan.className = toplamKar >= 0 ? 'text-success' : 'text-danger';
                }
            }
        }

        // Satış kaydını kaydet
        function topluSatisKaydet(sembol) {
            const form = document.getElementById(`satis-form-${sembol}`);
            const satisFiyati = document.getElementById(`satis-fiyat-${sembol}`).value;
            const satislar = [];

            form.querySelectorAll('.satis-secim:checked').forEach(checkbox => {
                const adetInput = checkbox.closest('tr').querySelector('.satis-adet');
                const adet = parseInt(adetInput.value);
                const maxAdet = parseInt(checkbox.dataset.maxAdet);

                if (adet > 0 && adet <= maxAdet) {
                    satislar.push({
                        id: checkbox.dataset.id,
                        adet: adet
                    });
                }
            });

            if (satislar.length === 0) {
                alert('Lütfen satılacak hisseleri seçin!');
                return;
            }

            if (!satisFiyati || satisFiyati <= 0) {
                alert('Lütfen geçerli bir satış fiyatı girin!');
                return;
            }

            // Her bir satış için ayrı istek gönder
            Promise.all(satislar.map(satis =>
                    fetch(`borsa.php?sat=1&id=${satis.id}&adet=${satis.adet}&fiyat=${satisFiyati}`)
                    .then(response => response.text())
                ))
                .then(results => {
                    const basarili = results.every(result => result === 'success');
                    if (basarili) {
                        topluSatisFormunuGizle(sembol);
                        portfoyGuncelle();
                    } else {
                        alert('Bazı satış işlemleri başarısız oldu!');
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Satış işlemi sırasında bir hata oluştu!');
                });
        }

        // Toplu satış formunu gizle
        function topluSatisFormunuGizle(sembol, event) {
            if (event) {
                event.stopPropagation();
            }
            const form = document.getElementById(`satis-form-${sembol}`);
            if (form) {
                form.style.display = 'none';

                // Form içindeki inputları sıfırla
                form.querySelectorAll('.satis-secim').forEach(checkbox => {
                    checkbox.checked = false;
                    const adetInput = checkbox.closest('tr').querySelector('.satis-adet');
                    adetInput.disabled = true;
                    adetInput.value = 0;
                });

                karZararHesapla(sembol);
            }
        }

        // Hisse sil
        function hisseSil(ids, event) {
            if (event) {
                event.stopPropagation();
            }

            // ids'yi string'e çevir
            ids = ids.toString();

            const idList = ids.split(',');
            const message = idList.length > 1 ?
                'Bu hissenin tüm kayıtlarını silmek istediğinizden emin misiniz?' :
                'Bu hisse kaydını silmek istediğinizden emin misiniz?';

            if (confirm(message)) {
                fetch('borsa.php?sil=' + encodeURIComponent(ids))
                    .then(response => response.text())
                    .then(data => {
                        if (data === 'success') {
                            portfoyGuncelle();
                        } else {
                            alert('Hisse silinirken bir hata oluştu!');
                        }
                    })
                    .catch(error => console.error('Hata:', error));
            }
        }

        // Hisse arama ve otomatik tamamlama
        let typingTimer;
        let lastSearchTerm = '';
        const doneTypingInterval = 750; // 750ms bekleme süresi
        const minSearchLength = 3; // Minimum 3 karakter
        const sembolInput = document.getElementById('sembolInput');
        const sembolOnerileri = document.getElementById('sembolOnerileri');

        // Input event listener'ı güncellendi
        sembolInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            sembolOnerileri.innerHTML = '';

            const searchTerm = this.value.trim();

            // 3 karakterden az ise hiçbir şey yapma
            if (searchTerm.length < minSearchLength) {
                lastSearchTerm = '';
                return;
            }

            // Aynı terim için tekrar arama yapma
            if (searchTerm === lastSearchTerm) {
                return;
            }

            // Yeterli süre bekledikten sonra aramayı yap
            typingTimer = setTimeout(() => {
                if (searchTerm.length >= minSearchLength) {
                    lastSearchTerm = searchTerm;
                    hisseAra();
                }
            }, doneTypingInterval);
        });

        function hisseAra() {
            const aranan = sembolInput.value.trim();

            // Minimum karakter kontrolünü tekrar yap
            if (!aranan || aranan.length < minSearchLength) {
                sembolOnerileri.innerHTML = '';
                return;
            }

            // Arama yapılıyor göstergesi
            sembolOnerileri.innerHTML = '<div class="text-muted"><small>Aranıyor...</small></div>';

            fetch('borsa.php?ara=' + encodeURIComponent(aranan))
                .then(response => response.json())
                .then(data => {
                    // Arama sırasında input temizlendiyse sonuçları gösterme
                    if (sembolInput.value.trim().length < minSearchLength) {
                        sembolOnerileri.innerHTML = '';
                        return;
                    }

                    sembolOnerileri.innerHTML = '';
                    if (!Array.isArray(data) || data.length === 0) {
                        const div = document.createElement('div');
                        div.innerHTML = 'Sonuç bulunamadı';
                        sembolOnerileri.appendChild(div);
                        return;
                    }

                    data.forEach(hisse => {
                        const div = document.createElement('div');
                        if (hisse.code === 'ERROR' || hisse.code === 'INFO') {
                            div.innerHTML = `<span style="color: red;">${hisse.title}</span>`;
                        } else {
                            div.innerHTML = `<strong>${hisse.code}</strong> - ${hisse.title} <span class="fiyat-bilgisi">${hisse.price} ₺</span>`;
                            div.addEventListener('click', function() {
                                sembolInput.value = hisse.code;
                                document.getElementsByName('alis_fiyati')[0].value = hisse.price;
                                document.getElementsByName('hisse_adi')[0].value = hisse.title;
                                sembolOnerileri.innerHTML = '';
                                lastSearchTerm = hisse.code;
                            });
                        }
                        sembolOnerileri.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Hata:', error);
                    sembolOnerileri.innerHTML = '<div style="color: red;">Arama sırasında bir hata oluştu!</div>';
                });
        }

        // Sayfa yüklendiğinde ve her 5 dakikada bir güncelle
        window.onload = function() {
            portfoyGuncelle();
            setInterval(portfoyGuncelle, 300000);
        };

        // Tıklama ile önerileri kapat
        document.addEventListener('click', function(e) {
            if (e.target !== sembolInput) {
                sembolOnerileri.innerHTML = '';
            }
        });
    </script>
</body>

</html>