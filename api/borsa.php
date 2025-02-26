<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/log.php';
checkLogin();

// get user default currency from session
$user_default_currency = $_SESSION['base_currency'];
global $pdo;

function convertCurrencyToTRY($amount)
{
    // NOT : Türkiyede ondalık ayıracı VİRGÜL'dür. Binlik ayıracı NOKTA'dır. Fakat PHP İNGİLİZCE alt yapıya sahip olduğundan PHP içerisinde NOKTA ondalık ayıracıdır. VİRGÜL ise binlik ayıracıdır. Yani ondalıklı sayılar nokta ile gösterilir. 

    // Tutar 1 350 TL. 556 Kuruş sayısnı Türkiye 1.350,25 yazılır. İngilizce de 1,350.25 yazılır.

    // number_format(değişken,ondalık basamak sayısı, "ondalık ayıracı" , "binlik ayıracı");

    // Ondalık ayıracı ve binlik ayıracı çift tırnaklar içine yazılacak.

    return number_format($amount, 2, ",", ".");
}

/**
 * Yeni hisse senedi ekler
 */
function hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi, $hisse_adi = '')
{
    global $pdo;
    // Önce anlık fiyatı API'den al
    $anlik_fiyat = collectApiFiyatCek($sembol);
    $user_id = $_SESSION['user_id'];

    saveLog("Yeni hisse eklenirken anlık fiyat alındı - Hisse: $sembol, Fiyat: $anlik_fiyat", 'info', 'hisseEkle', $_SESSION['user_id']);

    $sql = "INSERT INTO portfolio (sembol, adet, alis_fiyati, anlik_fiyat, hisse_adi, user_id) 
                VALUES (:sembol, :adet, :alis_fiyati, :anlik_fiyat, :hisse_adi, :user_id)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'sembol' => $sembol,
        'adet' => $adet,
        'alis_fiyati' => $alis_fiyati,
        'anlik_fiyat' => $anlik_fiyat,
        'hisse_adi' => $hisse_adi,
        'user_id' => $user_id
    ]);
}

/**
 * Hisse senedi siler
 */
function hisseSil($id)
{
    global $pdo;
    // Virgülle ayrılmış ID'leri diziye çevir
    $ids = explode(',', $id);
    $ids = array_map('intval', $ids);

    $sql = "DELETE FROM portfolio WHERE id IN (" . implode(',', $ids) . ")";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute();
}

/**
 * Portföydeki hisseleri listeler
 */
function portfoyListele()
{
    global $pdo;
    $user_id = $_SESSION['user_id'];

    // Portföydeki benzersiz hisseleri getir
    $sql = "SELECT 
                sembol, 
                son_guncelleme, 
                GROUP_CONCAT(id) as ids, 
                SUM(CASE 
                    WHEN durum = 'aktif' THEN adet 
                    WHEN durum = 'kismi_satildi' THEN (adet - satis_adet) 
                    ELSE 0 
                END) as toplam_adet,
                SUM(CASE 
                    WHEN durum != 'satis_kaydi' THEN adet 
                    ELSE 0 
                END) as toplam_alis_adet,
                CASE WHEN SUM(CASE WHEN durum = 'satildi' OR durum = 'kismi_satildi' OR durum = 'satis_kaydi' THEN 1 ELSE 0 END) > 0 THEN 1 ELSE 0 END as has_sold,
                MAX(anlik_fiyat) as anlik_fiyat, 
                MAX(hisse_adi) as hisse_adi
            FROM portfolio 
            WHERE user_id = :user_id 
            GROUP BY sembol 
            HAVING toplam_adet > 0 OR has_sold = 1
            ORDER BY sembol ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $user_id]);
    $hisseler = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = '';
    foreach ($hisseler as $hisse) {
        $sembol = $hisse['sembol'];
        $ids = $hisse['ids'];
        $toplam_adet = $hisse['toplam_adet'];
        $toplam_alis_adet = $hisse['toplam_alis_adet'];
        $has_sold = $hisse['has_sold'];
        $anlik_fiyat = $hisse['anlik_fiyat'];
        $hisse_adi = $hisse['hisse_adi'] ?: $sembol;
        $son_guncelleme = $hisse['son_guncelleme'];

        // Hissenin tüm alış kayıtlarını getir
        $sql = "SELECT id, adet, alis_fiyati, alis_tarihi, anlik_fiyat, durum, satis_fiyati, satis_tarihi, satis_adet
                FROM portfolio 
                WHERE user_id = :user_id AND sembol = :sembol AND durum != 'satis_kaydi'
                ORDER BY alis_tarihi ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'sembol' => $sembol]);
        $alislar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Ortalama alış fiyatını hesapla
        $toplam_maliyet = 0;
        $toplam_aktif_adet = 0;
        $toplam_alis_maliyet = 0;
        $toplam_alis_lot = 0;

        foreach ($alislar as $alis) {
            // Aktif veya kısmen satılmış hisseler için mevcut değerleri hesapla
            if ($alis['durum'] == 'aktif' || $alis['durum'] == 'kismi_satildi') {
                $kalan_adet = $alis['durum'] == 'kismi_satildi' ? $alis['adet'] - $alis['satis_adet'] : $alis['adet'];
                $toplam_maliyet += $alis['alis_fiyati'] * $kalan_adet;
                $toplam_aktif_adet += $kalan_adet;
            }

            // Tüm hisseler için toplam alış maliyeti ve lot sayısını hesapla
            $toplam_alis_maliyet += $alis['alis_fiyati'] * $alis['adet'];
            $toplam_alis_lot += $alis['adet'];
        }

        // Ortalama alış fiyatı her zaman toplam alış maliyeti / toplam alış lot sayısı olarak hesaplanacak
        $ortalama_alis = $toplam_alis_lot > 0 ? $toplam_alis_maliyet / $toplam_alis_lot : 0;

        // Toplam maliyet her zaman tüm alımların toplam alış maliyeti olarak hesaplanacak
        $gosterilecek_toplam_maliyet = $toplam_alis_maliyet;

        // Kalan adetlerin toplam alış maliyeti
        $kalan_adetlerin_toplam_maliyet = $toplam_maliyet;

        // Kar/zarar hesapla - ortalama alış fiyatını kullan
        $kar_zarar = ($anlik_fiyat - $ortalama_alis) * $toplam_adet;
        $kar_zarar_class = $kar_zarar >= 0 ? 'kar' : 'zarar';
        $kar_zarar_formatted = convertCurrencyToTRY($kar_zarar);

        // Satış karı hesapla
        $satis_kari = 0;
        $sql = "SELECT id, adet, alis_fiyati, satis_fiyati, satis_adet, durum
                FROM portfolio 
                WHERE user_id = :user_id AND sembol = :sembol AND durum = 'satis_kaydi'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'sembol' => $sembol]);
        $satislar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($satislar as $satis) {
            $satis_kari += ($satis['satis_fiyati'] - $satis['alis_fiyati']) * $satis['adet'];
        }
        $satis_kari_class = $satis_kari >= 0 ? 'kar' : 'zarar';
        $satis_kari_formatted = convertCurrencyToTRY($satis_kari);

        // Ana satır
        $output .= '<tr class="ana-satir" data-sembol="' . $sembol . '">';
        $output .= '<td><i class="fa-solid fa-chevron-right me-2"></i>' . $sembol . ' <small class="text-muted">' . $hisse_adi . '</small>';

        // Tamamen satılmış hisseler için etiket ekle
        if ($toplam_adet == 0 && $has_sold == 1) {
            $output .= ' <span class="badge bg-secondary">Tamamen Satıldı</span>';
        }

        $output .= '</td>';
        $output .= '<td class="adet">' . $toplam_adet . '/' . $toplam_alis_adet . '</td>';
        $output .= '<td class="alis-fiyat">' . (count($alislar) > 1 ? 'Çeşitli' : (count($alislar) == 1 ? convertCurrencyToTRY($alislar[0]['alis_fiyati']) : '-')) . '</td>';
        $output .= '<td class="anlik_fiyat text-center">' . convertCurrencyToTRY($anlik_fiyat) . '<br><small class="text-muted">(' . date('d.m.Y H:i:s', strtotime($son_guncelleme)) . ')</small></td>';
        $output .= '<td class="ortalama-alis">' . convertCurrencyToTRY($ortalama_alis) . '</td>';
        $output .= '<td class="toplam-maliyet">' . convertCurrencyToTRY($kalan_adetlerin_toplam_maliyet) . ' / ' . convertCurrencyToTRY($gosterilecek_toplam_maliyet) . '</td>';

        // Tamamen satılmış hisseler için değer sütununda "-" göster
        if ($toplam_adet == 0 && $has_sold == 1) {
            $output .= '<td class="deger">-</td>';
            $output .= '<td class="kar-zarar-hucre">-</td>';
        } else {
            $output .= '<td class="deger">' . convertCurrencyToTRY($anlik_fiyat * $toplam_adet) . '</td>';
            $output .= '<td class="kar-zarar-hucre ' . $kar_zarar_class . '">' . $kar_zarar_formatted . '</td>';
        }

        $output .= '<td class="satis-kar-hucre ' . $satis_kari_class . '">' . $satis_kari_formatted . '</td>';
        $output .= '<td>';

        // Tamamen satılmış hisseler için satış butonunu gizle
        if ($toplam_adet > 0) {
            $output .= '<button class="btn btn-sm btn-success me-1" onclick="topluSatisFormunuGoster(\'' . $sembol . '\', ' . $anlik_fiyat . ', event)">Sat</button>';
        }

        $output .= '<button class="btn btn-sm btn-danger" onclick="hisseSil(\'' . $ids . '\', event)">Tümünü Sil</button>';
        $output .= '</td>';
        $output .= '</tr>';

        // Detay satırı
        $output .= '<tr class="detay-satir" data-sembol="' . $sembol . '" style="display: none;">';
        $output .= '<td colspan="10">';

        // Satış formu
        $output .= "<div id='satis-form-{$sembol}' class='satis-form mb-3' style='display:none;'>
            <div class='card'>
                <div class='card-header'>
                    <h6 class='mb-0'>Satış Detayları</h6>
                </div>
                <div class='card-body'>
                    <div class='alert alert-info'>
                        <i class='fa-solid fa-circle-info me-2'></i>
                        Satış işlemleri FIFO (First In First Out - İlk Giren İlk Çıkar) prensibine göre yapılmaktadır. 
                        En eski alımdan başlayarak satış gerçekleştirilir.
                    </div>
                    <div class='row mt-3'>
                        <div class='col-md-4'>
                            <div class='input-group input-group-sm'>
                                <span class='input-group-text'>Satış Fiyatı</span>
                                <input type='number' class='form-control' id='satis-fiyat-{$sembol}' 
                                       step='0.01' value='{$anlik_fiyat}'>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <div class='input-group input-group-sm'>
                                <span class='input-group-text'>Satılacak Lot</span>
                                <input type='number' class='form-control' id='toplam-satis-adet-{$sembol}' 
                                       min='0' max='{$toplam_adet}' value='0'>
                            </div>
                        </div>
                        <div class='col-md-4'>
                            <small class='text-muted'>Tahmini Kar/Zarar: <span id='kar-zarar-{$sembol}'>0.00 ₺</span></small>
                        </div>
                    </div>
                    <div class='mt-3'>
                        <button class='btn btn-primary btn-sm' onclick='topluSatisKaydet(\"{$sembol}\")'>Kaydet</button>
                        <button class='btn btn-secondary btn-sm' onclick='topluSatisFormunuGizle(\"{$sembol}\", event)'>İptal</button>
                    </div>
                </div>
            </div>
        </div>";

        // Alış ve satış tablolarını yan yana göstermek için row oluştur
        $output .= '<div class="row">';

        // Alış kayıtları tablosu - sol taraf
        $output .= '<div class="col-md-6">';
        $output .= '<h6 class="mb-2">Alış Kayıtları</h6>';
        $output .= '<table class="table table-sm">';
        $output .= '<thead class="table-light">';
        $output .= '<tr>';
        $output .= '<th class="text-center">Alış Tarihi</th>';
        $output .= '<th>Lot</th>';
        $output .= '<th>Alış</th>';
        $output .= '<th data-bs-toggle="tooltip" data-bs-placement="top" title="Kar/Zarar = (Güncel Fiyat - Alış Fiyatı) x Lot Sayısı">Kar/Zarar</th>';
        $output .= '<th>Durum</th>';
        $output .= '<th>İşlem</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        // Alış kayıtları
        foreach ($alislar as $alis) {
            // Kalan adet hesapla
            $kalan_adet = 0;
            $durum_badge = '';

            if ($alis['durum'] == 'aktif') {
                $kalan_adet = $alis['adet'];
                $durum_badge = '<span class="badge bg-success">Aktif</span>';
            } else if ($alis['durum'] == 'kismi_satildi') {
                $kalan_adet = $alis['adet'] - $alis['satis_adet'];
                $durum_badge = '<span class="badge bg-warning">Kısmi Satış</span>';
            } else if ($alis['durum'] == 'satildi') {
                $kalan_adet = 0;
                $durum_badge = '<span class="badge bg-secondary">Satıldı</span>';
            }

            // Kar/zarar hesapla (sadece aktif veya kısmen satılmış hisseler için)
            $alis_kar_zarar = 0;
            $alis_kar_zarar_class = '';

            if ($kalan_adet > 0) {
                $alis_kar_zarar = ($anlik_fiyat - $alis['alis_fiyati']) * $kalan_adet;
                $alis_kar_zarar_class = $alis_kar_zarar >= 0 ? 'kar' : 'zarar';
            }

            $output .= '<tr data-alis-tarihi="' . $alis['alis_tarihi'] . '" data-alis-fiyati="' . $alis['alis_fiyati'] . '" data-max-adet="' . $kalan_adet . '">';
            $output .= '<td class="text-center">' . date('d.m.Y H:i', strtotime($alis['alis_tarihi'])) . '</td>';
            $output .= '<td>' . $kalan_adet . '/' . $alis['adet'] . '</td>';
            $output .= '<td>' . convertCurrencyToTRY($alis['alis_fiyati']) . '</td>';
            $output .= '<td class="' . $alis_kar_zarar_class . '">' . ($kalan_adet > 0 ? convertCurrencyToTRY($alis_kar_zarar) : '-') . '</td>';
            $output .= '<td>' . $durum_badge . '</td>';
            $output .= '<td>
                <div class="d-flex align-items-center">';

            // Sadece aktif veya kısmen satılmış hisseler için sil butonu göster
            if ($alis['durum'] != 'satildi') {
                $output .= '<button class="btn btn-sm btn-danger" onclick="hisseSil(' . $alis['id'] . ', event)">Sil</button>';
            }

            $output .= '</div>
            </td>';
            $output .= '</tr>';
        }

        $output .= '</tbody></table></div>';

        // Satış kayıtları tablosu - sağ taraf
        $output .= '<div class="col-md-6">';
        $output .= '<h6 class="mb-2">Satış Kayıtları</h6>';
        $output .= '<table class="table table-sm">';
        $output .= '<thead class="table-light">';
        $output .= '<tr>';
        $output .= '<th class="text-center">Alış Tarihi</th>';
        $output .= '<th>Lot</th>';
        $output .= '<th>Alış</th>';
        $output .= '<th>Satış</th>';
        $output .= '<th data-bs-toggle="tooltip" data-bs-placement="top" title="Kar/Zarar = (Satış Fiyatı - Alış Fiyatı) x Satılan Lot Sayısı">Kar/Zarar</th>';
        $output .= '<th class="text-center">Durum</th>';
        $output .= '</tr>';
        $output .= '</thead>';
        $output .= '<tbody>';

        // Satış kayıtlarını listele - SADECE satis_kaydi durumundaki kayıtları listele
        $sql = "SELECT id, adet, alis_fiyati, satis_fiyati, satis_tarihi, satis_adet, durum, alis_tarihi, referans_alis_id
                FROM portfolio 
                WHERE user_id = :user_id AND sembol = :sembol AND durum = 'satis_kaydi'
                ORDER BY satis_tarihi DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'sembol' => $sembol]);
        $satilmis_hisseler = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($satilmis_hisseler) > 0) {
            foreach ($satilmis_hisseler as $satis) {
                // satis_kaydi durumunda doğrudan adet değerini kullan
                $satis_adedi = $satis['adet'];

                $satis_kar_zarar = ($satis['satis_fiyati'] - $satis['alis_fiyati']) * $satis_adedi;
                $satis_kar_zarar_class = $satis_kar_zarar >= 0 ? 'kar' : 'zarar';

                $output .= '<tr>';
                $output .= '<td class="text-center">' . date('d.m.Y H:i', strtotime($satis['alis_tarihi'])) . '</td>';
                $output .= '<td>' . $satis_adedi . '</td>';
                $output .= '<td>' . convertCurrencyToTRY($satis['alis_fiyati']) . '</td>';
                $output .= '<td>' . convertCurrencyToTRY($satis['satis_fiyati']) . '</td>';
                $output .= '<td class="' . $satis_kar_zarar_class . '">' . convertCurrencyToTRY($satis_kar_zarar) . '</td>';
                $output .= '<td class="text-center">Satıldı (' . date('d.m.Y H:i', strtotime($satis['satis_tarihi'])) . ')</td>';
                $output .= '</tr>';
            }
        } else {
            $output .= '<tr><td colspan="6" class="text-center">Henüz satış kaydı bulunmuyor</td></tr>';
        }

        $output .= '</tbody></table></div>';

        // Row'u kapat
        $output .= '</div>';

        $output .= '</td></tr>';
    }

    return $output;
}

/**
 * BigPara API'den fiyat bilgisi çeker
 */
function collectApiFiyatCek($sembol)
{
    $curl = curl_init();

    // BigPara hisse detay endpoint'i
    $url = "https://bigpara.hurriyet.com.tr/api/v1/borsa/hisseyuzeysel/" . urlencode($sembol);
    saveLog("API İsteği URL: " . $url, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);

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
    saveLog("BigPara API İstek - Hisse: " . $sembol, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);
    saveLog("BigPara API Yanıt Kodu: " . $httpcode, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);
    saveLog("BigPara API Ham Yanıt: " . $response, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);

    if ($err) {
        saveLog("BigPara API Curl Hatası: " . $err, 'error', 'collectApiFiyatCek', $_SESSION['user_id']);
        return 0;
    }

    if ($httpcode !== 200) {
        saveLog("BigPara API HTTP Hata Kodu: " . $httpcode, 'error', 'collectApiFiyatCek', $_SESSION['user_id']);
        return 0;
    }

    $data = json_decode($response, true);
    if (!isset($data['data']['hisseYuzeysel'])) {
        saveLog("BigPara API Geçersiz Yanıt: " . print_r($data, true), 'error', 'collectApiFiyatCek', $_SESSION['user_id']);
        return 0;
    }

    $hisse = $data['data']['hisseYuzeysel'];
    saveLog("İşlenen hisse verisi: " . print_r($hisse, true), 'info', 'collectApiFiyatCek', $_SESSION['user_id']);

    // Son işlem fiyatını al (alış-satış ortalaması)
    if (
        isset($hisse['alis']) && isset($hisse['satis']) &&
        is_numeric($hisse['alis']) && is_numeric($hisse['satis'])
    ) {
        $fiyat = ($hisse['alis'] + $hisse['satis']) / 2;
        saveLog("Fiyat alış-satış ortalamasından alındı: " . $fiyat, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);
        return $fiyat;
    }

    // Alternatif olarak son fiyatı kontrol et
    if (isset($hisse['kapanis']) && is_numeric($hisse['kapanis'])) {
        $fiyat = floatval($hisse['kapanis']);
        saveLog("Fiyat kapanıştan alındı: " . $fiyat, 'info', 'collectApiFiyatCek', $_SESSION['user_id']);
        return $fiyat;
    }

    saveLog("Hisse fiyatı bulunamadı: " . $sembol, 'error', 'collectApiFiyatCek', $_SESSION['user_id']);
    return 0;
}

/**
 * Anlık fiyat bilgisini getirir
 * @param string $sembol Hisse senedi sembolü
 * @param bool $forceApi API'den zorla fiyat çek (arama için)
 * @return float Hisse fiyatı
 */
function anlikFiyatGetir($sembol, $forceApi = false)
{
    global $pdo;
    // Eğer API'den fiyat çekilmesi istendiyse (arama için)
    if ($forceApi) {
        $fiyat = collectApiFiyatCek($sembol);
        saveLog("Anlık fiyat API'den alındı - Hisse: " . $sembol . ", Fiyat: " . $fiyat, 'info', 'anlikFiyatGetir', $_SESSION['user_id']);
        return $fiyat;
    }

    // Veritabanından anlık fiyatı al (hisse bazında son fiyat)
    $sql = "SELECT DISTINCT anlik_fiyat, son_guncelleme 
                FROM portfolio 
                WHERE sembol = :sembol 
                ORDER BY son_guncelleme DESC 
                LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['sembol' => $sembol]);
    $sonuc = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sonuc && $sonuc['anlik_fiyat'] > 0) {
        error_log("Anlık fiyat DB'den alındı - Hisse: " . $sembol . ", Fiyat: " . $sonuc['anlik_fiyat'] . ", Son Güncelleme: " . $sonuc['son_guncelleme']);
        return $sonuc['anlik_fiyat'];
    }

    saveLog("Anlık fiyat DB'de bulunamadı - Hisse: " . $sembol, 'error', 'anlikFiyatGetir', $_SESSION['user_id']);
    return 0;
}

/**
 * Kar/Zarar durumunu hesaplar
 */
function karZararHesapla($hisse)
{
    $anlik_fiyat = anlikFiyatGetir($hisse['sembol']);

    // merge error log to save log
    saveLog("Kar/Zarar Hesaplama - Hisse: " . $hisse['sembol'] . " <br> Alış Fiyatı: " . $hisse['alis_fiyati'] . " <br> Anlık Fiyat: " . $anlik_fiyat . " <br> Adet: " . $hisse['adet'], 'info', 'karZararHesapla', $_SESSION['user_id']);

    // Kar/Zarar = (Güncel Fiyat - Alış Fiyatı) * Adet
    $kar_zarar = ($anlik_fiyat - $hisse['alis_fiyati']) * $hisse['adet'];

    saveLog("Kar/Zarar: " . $kar_zarar, 'info', 'karZararHesapla', $_SESSION['user_id']);
    return $kar_zarar;
}

/**
 * Türkçe karakterleri İngilizce karakterlere çevirir
 */
function turkceKarakterleriCevir($str)
{
    $turkce = array("ı", "ğ", "ü", "ş", "ö", "ç", "İ", "Ğ", "Ü", "Ş", "Ö", "Ç");
    $ingilizce = array("i", "g", "u", "s", "o", "c", "I", "G", "U", "S", "O", "C");
    return str_replace($turkce, $ingilizce, $str);
}

/**
 * Hisse senedi ara
 */
function hisseAra($aranan)
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
    saveLog("Hisse Listesi API Yanıt Kodu: " . $httpcode, 'info', 'hisseAra', $_SESSION['user_id']);
    saveLog("Hisse Listesi API Content-Type: " . $content_type, 'info', 'hisseAra', $_SESSION['user_id']);
    saveLog("Hisse Listesi API Ham Yanıt: " . $response, 'info', 'hisseAra', $_SESSION['user_id']);

    if ($err) {
        saveLog("Hisse Listesi API Curl Hatası: " . $err, 'error', 'hisseAra', $_SESSION['user_id']);
        return [
            ['code' => 'ERROR', 'title' => 'API Hatası: ' . $err, 'price' => '0.00']
        ];
    }

    if ($httpcode !== 200) {
        saveLog("Hisse Listesi API HTTP Hata Kodu: " . $httpcode, 'error', 'hisseAra', $_SESSION['user_id']);
        return [
            ['code' => 'ERROR', 'title' => 'API HTTP Hatası: ' . $httpcode, 'price' => '0.00']
        ];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        saveLog("JSON Decode Hatası: " . json_last_error_msg(), 'error', 'hisseAra', $_SESSION['user_id']);
        saveLog("Ham Yanıt: " . $response, 'info', 'hisseAra', $_SESSION['user_id']);
        return [
            ['code' => 'ERROR', 'title' => 'JSON Parse Hatası: ' . json_last_error_msg(), 'price' => '0.00']
        ];
    }

    if (!isset($data['data']) || !is_array($data['data'])) {
        saveLog("Hisse Listesi API Geçersiz Yanıt: " . print_r($data, true), 'error', 'hisseAra', $_SESSION['user_id']);
        return [
            ['code' => 'ERROR', 'title' => 'API Yanıt Formatı Hatası', 'price' => '0.00']
        ];
    }

    // Aranan metni Türkçe karakterlerden arındır ve büyük harfe çevir
    $aranan = mb_strtoupper(turkceKarakterleriCevir($aranan), 'UTF-8');
    $sonuclar = [];
    $limit = 10; // Maksimum 10 sonuç göster
    $count = 0;

    foreach ($data['data'] as $hisse) {
        // Boş kayıtları atla
        if (empty($hisse['kod']) || empty($hisse['ad'])) {
            continue;
        }

        // Hisse kodu ve adını Türkçe karakterlerden arındır
        $hisse_kod = mb_strtoupper(turkceKarakterleriCevir($hisse['kod']), 'UTF-8');
        $hisse_ad = mb_strtoupper(turkceKarakterleriCevir($hisse['ad']), 'UTF-8');

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
function hisseSat($id, $satis_adet, $satis_fiyati)
{
    global $pdo;
    try {
        // İşlemi transaction içinde yap
        $pdo->beginTransaction();

        // Önce satılacak hissenin sembolünü bul
        $sql = "SELECT sembol FROM portfolio WHERE id = :id";
        $stmt = $pdo->prepare($sql);
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
        $stmt = $pdo->prepare($sql);
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

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'satis_fiyati' => $satis_fiyati,
                'satis_adet' => $bu_satis_adet,
                'durum' => $yeni_durum,
                'id' => $kayit['id']
            ]);

            // YENİ: Satış kaydını ayrı bir kayıt olarak ekle
            $sql = "INSERT INTO portfolio (
                        sembol, 
                        adet, 
                        alis_fiyati, 
                        alis_tarihi,
                        anlik_fiyat,
                        hisse_adi,
                        user_id,
                        durum,
                        satis_fiyati,
                        satis_tarihi,
                        satis_adet,
                        referans_alis_id
                    ) VALUES (
                        :sembol,
                        :adet,
                        :alis_fiyati,
                        :alis_tarihi,
                        :anlik_fiyat,
                        :hisse_adi,
                        :user_id,
                        'satis_kaydi',
                        :satis_fiyati,
                        CURRENT_TIMESTAMP,
                        :satis_adet,
                        :referans_alis_id
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'sembol' => $kayit['sembol'],
                'adet' => $bu_satis_adet,
                'alis_fiyati' => $kayit['alis_fiyati'],
                'alis_tarihi' => $kayit['alis_tarihi'],
                'anlik_fiyat' => $kayit['anlik_fiyat'],
                'hisse_adi' => $kayit['hisse_adi'],
                'user_id' => $kayit['user_id'],
                'satis_fiyati' => $satis_fiyati,
                'satis_adet' => $bu_satis_adet,
                'referans_alis_id' => $kayit['id']
            ]);

            $kalan_satis_adet -= $bu_satis_adet;

            saveLog("Satış kaydı eklendi - ID: {$kayit['id']} <br> Adet: $bu_satis_adet <br> Fiyat: $satis_fiyati", 'info', 'hisseSat', $_SESSION['user_id']);

            if ($kalan_satis_adet <= 0) break;
        }

        if ($kalan_satis_adet > 0) {
            saveLog("Yeterli satılabilir hisse bulunamadı - Kalan adet: $kalan_satis_adet", 'error', 'hisseSat', $_SESSION['user_id']);
            return false;
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        saveLog("Satış hatası: " . $e->getMessage(), 'error', 'hisseSat', $_SESSION['user_id']);
        return false;
    }
}

/**
 * Satış karını hesaplar
 */
function satisKariHesapla($kayit)
{
    if ($kayit['durum'] == 'aktif' || !$kayit['satis_fiyati']) {
        return null;
    }

    $satis_adet = $kayit['satis_adet'] ?? 0;
    $kar = ($kayit['satis_fiyati'] - $kayit['alis_fiyati']) * $satis_adet;
    return $kar;
}


// POST işlemlerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // JSON yanıtı için header ayarla
    header('Content-Type: application/json');

    // addStock aksiyonu için
    if (isset($_POST['action']) && $_POST['action'] === 'addStock') {
        // Gerekli parametreleri kontrol et
        if (
            isset($_POST['sembol']) &&
            isset($_POST['adet']) &&
            isset($_POST['alis_fiyati']) &&
            isset($_POST['alis_tarihi'])
        ) {
            $sembol = trim(strtoupper($_POST['sembol']));
            $adet = intval($_POST['adet']);
            $alis_fiyati = floatval($_POST['alis_fiyati']);
            $alis_tarihi = $_POST['alis_tarihi'];
            $hisse_adi = isset($_POST['hisse_adi']) ? $_POST['hisse_adi'] : '';

            // Parametrelerin geçerliliğini kontrol et
            if (empty($sembol) || $adet <= 0 || $alis_fiyati <= 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Geçersiz parametreler. Lütfen tüm alanları doğru şekilde doldurun.'
                ]);
                exit;
            }

            try {
                // Hisseyi ekle
                $result = hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi, $hisse_adi);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Hisse başarıyla eklendi.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Hisse eklenirken bir hata oluştu.'
                    ]);
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Hata: ' . $e->getMessage()
                ]);
            }
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Eksik parametreler.'
            ]);
            exit;
        }
    }
}

// AJAX silme işlemi için
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    if (hisseSil($_GET['sil'])) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// AJAX satış işlemi için
if (isset($_GET['sat'], $_GET['id'], $_GET['adet'], $_GET['fiyat'])) {
    if (hisseSat($_GET['id'], $_GET['adet'], $_GET['fiyat'])) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// Sayfa yüklendiğinde portföy listesini getir
if (isset($_GET['liste'])) {
    $portfoy = portfoyListele();

    echo $portfoy;
    exit;
}

// Hisse arama API endpoint'i
if (isset($_GET['ara'])) {
    header('Content-Type: application/json');
    $sonuclar = hisseAra($_GET['ara']);

    // Debug için API yanıtını logla
    saveLog("Aranan Hisse: '" . $_GET['ara'] . "': " . print_r($sonuclar, true), 'info', 'hisseAra', $_SESSION['user_id']);

    echo json_encode($sonuclar);
    exit;
}
