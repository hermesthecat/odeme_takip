<?php
require_once 'config.php';
checkLogin();

// get user default currency from session
$user_default_currency = $_SESSION['base_currency'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title><?php echo $site_name; ?> - <?php echo t('site_description'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="borsa.css" />

</head>

<body>
    <div class="container mt-4">

        <?php include 'navbar_app.php'; ?>

        <div class="container mt-5">

            <!-- Yeni Hisse Ekleme Formu -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Hisse Ekle</span>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#yeniHisseForm">
                        <i class="fa-solid fa-plus me-1"></i> Ekle
                    </button>
                </div>
                <div class="collapse" id="yeniHisseForm">
                    <div class="card-body">
                        <form id="hisseForm" class="needs-validation" novalidate>
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
                                        <label for="adetInput">Lot</label>
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
                                        <i class="fa-solid fa-circle-info me-2"></i>
                                        <div>
                                            <strong>Tahmini Maliyet:</strong>
                                            <span id="tahminiMaliyet">0.00 ₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-check me-1"></i> Ekle
                                    </button>
                                    <button type="reset" class="btn btn-secondary ms-2">
                                        <i class="fa-solid fa-rotate me-1"></i> Temizle
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
                </div>
                <div class="collapse show" id="maliDurumDetay">
                    <div class="card-body py-3">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <canvas id="maliDurumGrafik" style="max-height: 220px;"></canvas>
                            </div>
                            <div class="col-md-7">
                                <div class="row row-cols-1 row-cols-md-3 g-3">
                                    <div class="col">
                                        <div class="mali-ozet-kart border rounded p-3 h-100 shadow-sm">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="mali-ozet-ikon bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i class="fas fa-wallet text-primary"></i>
                                                </div>
                                                <h6 class="mb-0">Portföy Değeri</h6>
                                            </div>
                                            <h4 id="toplamPortfoyDeger" class="mb-0 mt-2">0.00</h4>
                                            <small class="text-muted">Güncel piyasa değeri</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mali-ozet-kart border rounded p-3 h-100 shadow-sm">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="mali-ozet-ikon bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i class="fas fa-chart-line text-info"></i>
                                                </div>
                                                <h6 class="mb-0">Kar/Zarar</h6>
                                            </div>
                                            <h4 id="toplamKarZarar" class="mb-0 mt-2">0.00</h4>
                                            <small class="text-muted">Güncel değere göre</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mali-ozet-kart border rounded p-3 h-100 shadow-sm">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="mali-ozet-ikon bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                                    <i class="fas fa-money-bill-wave text-success"></i>
                                                </div>
                                                <h6 class="mb-0">Gerçekleşen K/Z</h6>
                                            </div>
                                            <h4 id="toplamSatisKar" class="mb-0 mt-2">0.00</h4>
                                            <small class="text-muted">Satış işlemlerinden</small>
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
                    <table class="table table-striped table-responsive">
                        <thead>
                            <tr>
                                <th>Hisse</th>
                                <th>Lot</th>
                                <th>Alış</th>
                                <th class="text-center">Güncel</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Ortalama Alış Fiyatı = Toplam Alış Maliyeti / Toplam Alış Lot Sayısı">Ort. Alış</th>
                                <th class="text-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Maliyet = Kalan Adetlerin Toplam Maliyeti / Toplam Alış Maliyeti">Maliyet</th>
                                <th>Güncel Değer</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Kar/Zarar = (Güncel Fiyat - Ortalama Alış Fiyatı) × Lot Sayısı">Kar/Zarar</th>
                                <th data-bs-toggle="tooltip" data-bs-placement="top" title="Satış Karı = (Satış Fiyatı - Alış Fiyatı) x Satılan Lot Sayısı">Satış Karı</th>
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

        <!-- Sorumluluk Reddi Metni -->
        <div class="container mt-5 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle me-2"></i>Sorumluluk Reddi
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-0">
                        Bu uygulama sadece kişisel portföy takibi amacıyla kullanılmaktadır ve herhangi bir yatırım tavsiyesi içermemektedir.
                        Burada sunulan veriler ve bilgiler yalnızca bilgilendirme amaçlıdır ve doğruluğu, güncelliği veya eksiksizliği garanti edilmemektedir.
                        Kullanıcılar, bu uygulamadaki bilgilere dayanarak yapacakları yatırım kararlarından kendileri sorumludur.
                    </p>
                    <p class="small text-muted mb-0">
                        Hisse senedi fiyatları ve diğer finansal veriler, üçüncü taraf kaynaklardan alınmaktadır ve bu verilerin doğruluğu veya güncelliği
                        garanti edilmemektedir. Piyasa koşulları hızla değişebilir ve burada gösterilen veriler gerçek zamanlı olmayabilir.
                    </p>
                    <p class="small text-muted mb-0">
                        Yatırım kararları vermeden önce profesyonel finansal danışmanlık almanız ve kendi araştırmanızı yapmanız önerilir.
                        Bu uygulamanın geliştiricisi ve sağlayıcısı, uygulamanın kullanımından kaynaklanan herhangi bir kayıp veya zarardan sorumlu tutulamaz.
                    </p>
                    <p class="small text-center text-muted mb-0 mt-3">
                        &copy; <?php echo date('Y'); ?> <?php echo $site_name; ?> - Tüm hakları saklıdır.
                    </p>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/modals/user_settings_modal.php'; ?>

        <!-- JavaScript Kütüphaneleri -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
        <script src="js/utils.js"></script>
        <script src="js/language.js"></script>
        <script src="js/theme.js"></script>
        <script src="js/borsa.js"></script>

        <script>
            // Çıkış işlemi
            document.querySelector('.logout-btn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Çıkış yapmak istediğinize emin misiniz?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Evet, çıkış yap',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'api/auth.php',
                            type: 'POST',
                            data: {
                                action: 'logout'
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    window.location.href = 'index.php';
                                }
                            }
                        });
                    }
                });
            });
        </script>

</body>

</html>