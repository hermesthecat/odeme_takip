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
    public function hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi)
    {
        $sql = "INSERT INTO portfolio (sembol, adet, alis_fiyati, alis_tarihi) 
                VALUES (:sembol, :adet, :alis_fiyati, :alis_tarihi)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'sembol' => $sembol,
            'adet' => $adet,
            'alis_fiyati' => $alis_fiyati,
            'alis_tarihi' => $alis_tarihi
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
     * Anlık fiyat bilgisini getirir (API entegrasyonu gerekir)
     */
    public function anlikFiyatGetir($sembol)
    {
        // Borsa İstanbul API veya başka bir API kullanılabilir
        // Örnek olarak sabit bir değer döndürüyoruz
        return 100.50;
    }

    /**
     * Kar/Zarar durumunu hesaplar
     */
    public function karZararHesapla($hisse)
    {
        $anlik_fiyat = $this->anlikFiyatGetir($hisse['sembol']);
        $maliyet = $hisse['alis_fiyati'] * $hisse['adet'];
        $guncel_deger = $anlik_fiyat * $hisse['adet'];
        return $guncel_deger - $maliyet;
    }
}

// POST işlemlerini kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borsaTakip = new BorsaTakip();
    
    if (isset($_POST['sembol'], $_POST['adet'], $_POST['alis_fiyati'])) {
        $sembol = strtoupper(trim($_POST['sembol']));
        $adet = intval($_POST['adet']);
        $alis_fiyati = floatval($_POST['alis_fiyati']);
        $alis_tarihi = date('Y-m-d H:i:s');
        
        if ($borsaTakip->hisseEkle($sembol, $adet, $alis_fiyati, $alis_tarihi)) {
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
        $anlik_fiyat = $borsaTakip->anlikFiyatGetir($hisse['sembol']);
        $kar_zarar = $borsaTakip->karZararHesapla($hisse);
        $kar_zarar_class = $kar_zarar >= 0 ? 'kar' : 'zarar';
        
        echo "<tr>
                <td>{$hisse['sembol']}</td>
                <td>{$hisse['adet']}</td>
                <td>{$hisse['alis_fiyati']} ₺</td>
                <td>{$anlik_fiyat} ₺</td>
                <td class='{$kar_zarar_class}'>" . number_format($kar_zarar, 2) . " ₺</td>
                <td>
                    <button class='btn btn-danger btn-sm' onclick='hisseSil({$hisse['id']})'>Sil</button>
                </td>
            </tr>";
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Borsa Portföy Takip</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .kar {
            color: green;
        }

        .zarar {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Portföy Takip Sistemi</h1>

        <!-- Yeni Hisse Ekleme Formu -->
        <div class="card mb-4">
            <div class="card-header">Yeni Hisse Ekle</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="sembol" class="form-control" placeholder="Hisse Sembolü" required>
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
                            <th>Sembol</th>
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

        // Sayfa yüklendiğinde ve her 5 dakikada bir güncelle
        window.onload = function() {
            portfoyGuncelle();
            setInterval(portfoyGuncelle, 300000);
        };
    </script>
</body>

</html>