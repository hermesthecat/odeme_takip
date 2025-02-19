<?php
require_once 'config.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title>Bütçe Kontrol Sistemi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Bütçe Kontrol Sistemi</h1>
            <div class="d-flex align-items-center">
                <span class="me-3">Hoş geldiniz, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Çıkış Yap
                </a>
            </div>
        </div>

        <!-- Ay Seçimi -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="month-selector d-flex align-items-center justify-content-center">
                        <button class="btn btn-link text-primary me-2" onclick="previousMonth()">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="d-flex align-items-center">
                            <select id="monthSelect" class="form-select me-2">
                                <option value="0">Ocak</option>
                                <option value="1">Şubat</option>
                                <option value="2">Mart</option>
                                <option value="3">Nisan</option>
                                <option value="4">Mayıs</option>
                                <option value="5">Haziran</option>
                                <option value="6">Temmuz</option>
                                <option value="7">Ağustos</option>
                                <option value="8">Eylül</option>
                                <option value="9">Ekim</option>
                                <option value="10">Kasım</option>
                                <option value="11">Aralık</option>
                            </select>
                            <select id="yearSelect" class="form-select">
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear - 1; $year <= $currentYear + 5; $year++) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button class="btn btn-link text-primary ms-2" onclick="nextMonth()">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aylık Özet -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success bg-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title">Aylık Gelir</h5>
                        <h3 class="card-text" id="monthlyIncome">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger bg-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title">Aylık Gider</h5>
                        <h3 class="card-text" id="monthlyExpense">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title">Net Durum</h5>
                        <h3 class="card-text" id="monthlyBalance">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title">Dönem</h5>
                        <h3 class="card-text" id="currentPeriod"></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gelirler Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-success bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Gelirler</h2>
                    <button class="btn btn-success" data-action="add" data-type="income">
                        <i class="bi bi-plus-lg me-1"></i>Gelir Ekle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Durum</th>
                                <th>Gelir İsmi</th>
                                <th>Tutar</th>
                                <th>Kur</th>
                                <th>İlk Gelir Tarihi</th>
                                <th>Tekrarlama Sıklığı</th>
                                <th>Sonraki Gelir</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="incomeList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Birikimler Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Birikimler</h2>
                    <button class="btn btn-primary" data-action="add" data-type="saving">
                        <i class="bi bi-plus-lg me-1"></i>Birikim Ekle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Birikim İsmi</th>
                                <th>Hedef Tutar</th>
                                <th>Biriken Tutar</th>
                                <th>Kur</th>
                                <th>Başlangıç Tarihi</th>
                                <th>Hedef Tarihi</th>
                                <th>İlerleme</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="savingList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ödemeler Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-danger bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Ödemeler</h2>
                    <button class="btn btn-danger" data-action="add" data-type="payment">
                        <i class="bi bi-plus-lg me-1"></i>Ödeme Ekle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Durum</th>
                                <th>Ödeme İsmi</th>
                                <th>Tutar</th>
                                <th>Kur</th>
                                <th>İlk Ödeme Tarihi</th>
                                <th>Tekrarlama Sıklığı</th>
                                <th>Sonraki Ödeme</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="paymentList"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'modals.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="app.js"></script>
</body>

</html>