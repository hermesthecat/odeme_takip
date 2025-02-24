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
        $sql = "DELETE FROM portfolio WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Portföydeki hisseleri listeler
     */
    public function portfoyListele()
    {
        $sql = "SELECT * FROM portfolio ORDER BY alis_tarihi DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        // Aranan metne göre filtrele
        $aranan = mb_strtoupper($aranan, 'UTF-8');
        $sonuclar = [];
        $limit = 10; // Maksimum 10 sonuç göster
        $count = 0;

        foreach ($data['data'] as $hisse) {
            // Boş kayıtları atla
            if (empty($hisse['kod']) || empty($hisse['ad'])) {
                continue;
            }

            if (
                strpos(mb_strtoupper($hisse['kod'], 'UTF-8'), $aranan) !== false ||
                strpos(mb_strtoupper($hisse['ad'], 'UTF-8'), $aranan) !== false
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

// Sayfa yüklendiğinde portföy listesini getir
if (isset($_GET['liste'])) {
    $borsaTakip = new BorsaTakip();
    $portfoy = $borsaTakip->portfoyListele();

    foreach ($portfoy as $hisse) {
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

        $kar_zarar = $borsaTakip->karZararHesapla($hisse);
        $kar_zarar_class = $kar_zarar >= 0 ? 'kar' : 'zarar';

        // Son güncelleme zamanını al
        $son_guncelleme = isset($hisse['son_guncelleme']) ? date('H:i:s', strtotime($hisse['son_guncelleme'])) : '';
        $guncelleme_bilgisi = $son_guncelleme ? " <small class='text-muted'>($son_guncelleme)</small>" : '';

        // Hisse adını ve sembolü birleştir
        $hisse_baslik = $hisse['hisse_adi'] ? $hisse['hisse_adi'] . " (" . $hisse['sembol'] . ")" : $hisse['sembol'];

        // Debug için HTML çıktısı
        $html_output = "<tr>
                <td class='sembol'>{$hisse_baslik}</td>
                <td class='adet'>{$hisse['adet']}</td>
                <td class='alis_fiyati'>{$hisse['alis_fiyati']} ₺</td>
                <td class='anlik_fiyat'>{$anlik_fiyat} ₺{$guncelleme_bilgisi}</td>
                <td class='{$kar_zarar_class}'>" . number_format($kar_zarar, 2) . " ₺</td>
                <td>
                    <button class='btn btn-danger btn-sm' onclick='hisseSil({$hisse['id']})'>Sil</button>
                </td>
            </tr>";

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
    <link rel="stylesheet" href="borsa.css">
</head>

<body>
    <div class="container mt-5">
        <h1>Portföy Takip Sistemi</h1>

        <!-- Yeni Hisse Ekleme Formu -->
        <div class="card mb-4">
            <div class="card-header">Yeni Hisse Ekle</div>
            <div class="card-body">
                <form method="POST" action="" id="hisseForm">
                    <div class="row">
                        <div class="col-md-3 sembol-input-container">
                            <input type="text" name="sembol" id="sembolInput" class="form-control"
                                placeholder="Hisse Sembolü veya Adı" required autocomplete="off">
                            <div id="sembolOnerileri" class="autocomplete-items"></div>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="adet" class="form-control" placeholder="Adet" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="alis_fiyati" class="form-control" placeholder="Alış Fiyatı" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Ekle</button>
                        </div>
                        <input type="hidden" name="hisse_adi" value="">
                    </div>
                </form>
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

    <script>
        // Portföy listesini güncelle
        function portfoyGuncelle() {
            fetch('borsa.php?liste=1')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('portfoyListesi').innerHTML = data;
                })
                .catch(error => console.error('Hata:', error));
        }

        // Hisse sil
        function hisseSil(id) {
            if (confirm('Bu hisseyi silmek istediğinizden emin misiniz?')) {
                fetch('borsa.php?sil=' + id)
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
        const doneTypingInterval = 300;
        const sembolInput = document.getElementById('sembolInput');
        const sembolOnerileri = document.getElementById('sembolOnerileri');

        sembolInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            if (this.value.length > 1) {
                typingTimer = setTimeout(hisseAra, doneTypingInterval);
            } else {
                sembolOnerileri.innerHTML = '';
            }
        });

        function hisseAra() {
            const aranan = sembolInput.value;
            fetch('borsa.php?ara=' + encodeURIComponent(aranan))
                .then(response => response.json())
                .then(data => {
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