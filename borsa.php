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
    <title><?php echo t('site_name'); ?> - <?php echo t('site_description'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="borsa.css" />

</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><?php echo t('site_name'); ?></h1>
            <div class="d-flex align-items-center">
                <a href="app.php" class="btn btn-outline-success me-2">
                    <i class="bi bi-graph-up me-1"></i>Bütçe
                </a>
                <button class="btn btn-outline-primary me-2" onclick="openUserSettings()">
                    <i class="bi bi-gear me-1"></i><?php echo t('settings.title'); ?>
                </button>
                <button class="btn btn-outline-danger logout-btn">
                    <?php echo htmlspecialchars($_SESSION['username']); ?> <i class="bi bi-box-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <div class="container mt-5">

            <!-- Yeni Hisse Ekleme Formu -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Hisse Ekle</span>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#yeniHisseForm">
                        <i class="fas fa-plus me-1"></i> Ekle
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
                                        <i class="fas fa-info-circle me-2"></i>
                                        <div>
                                            <strong>Tahmini Maliyet:</strong>
                                            <span id="tahminiMaliyet">0.00 ₺</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check me-1"></i> Ekle
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
                                <th>Lot</th>
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

        <?php require_once __DIR__ . '/modals/user_settings_modal.php'; ?>


        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="js/utils.js"></script>
        <script src="js/theme.js"></script>
        <script src="js/borsa.js"></script>
</body>

</html>