<?php
require_once 'config.php';
checkLogin();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title><?php echo $site_name; ?> - <?php echo $site_slogan; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><?php echo $site_name; ?></h1>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary me-2" onclick="openUserSettings()">
                    <i class="bi bi-gear me-1"></i>Ayarlar
                </button>
                <button class="btn btn-outline-danger logout-btn">
                    <?php echo htmlspecialchars($_SESSION['username']); ?> <i class="bi bi-box-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- Ay Seçimi -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="month-selector d-flex align-items-center justify-content-center">
                        <button class="btn btn-link text-primary me-3 fs-4" onclick="previousMonth()" title="Önceki Ay">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <div class="date-selector bg-light rounded-pill px-4 py-2 shadow-sm d-flex align-items-center">
                            <select id="monthSelect" class="form-select form-select-lg border-0 bg-transparent me-2" style="width: auto;">
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
                            <select id="yearSelect" class="form-select form-select-lg border-0 bg-transparent" style="width: auto;">
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear - 1; $year <= $currentYear + 5; $year++) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button class="btn btn-link text-primary ms-3 fs-4" onclick="nextMonth()" title="Sonraki Ay">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aylık Özet -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success bg-opacity-10 border-success border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success text-opacity-75">
                            <i class="bi bi-graph-up me-2"></i>
                            Aylık Gelir
                        </h5>
                        <h3 class="card-text" id="monthlyIncome">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger text-opacity-75">
                            <i class="bi bi-graph-down me-2"></i>
                            Aylık Gider
                        </h5>
                        <h3 class="card-text" id="monthlyExpense">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-10 border-info border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-info text-opacity-75">
                            <i class="bi bi-wallet2 me-2"></i>
                            Net Durum
                        </h5>
                        <h3 class="card-text" id="monthlyBalance">0.00 TL</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning text-opacity-75">
                            <i class="bi bi-calendar3 me-2"></i>
                            Dönem
                        </h5>
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
                        <i class="bi bi-plus-lg me-1"></i>Ekle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Gelir İsmi</th>
                                <th>Tutar</th>
                                <th>Kur</th>
                                <th>İlk Gelir Tarihi</th>
                                <th>Tekrarlama Sıklığı</th>
                                <th>Sonraki Gelir</th>
                                <th></th>
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
                        <i class="bi bi-plus-lg me-1"></i>Ekle
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
                                <th></th>
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
                        <i class="bi bi-plus-lg me-1"></i>Ekle
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Ödeme İsmi</th>
                                <th>Tutar</th>
                                <th>Kur</th>
                                <th>İlk Ödeme Tarihi</th>
                                <th>Tekrarlama Sıklığı</th>
                                <th>Sonraki Ödeme</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="paymentList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ödeme Gücü Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-info bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Ödeme Gücü</h2>
                    <small class="text-muted"></small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ödeme İsmi</th>
                                <th>Tutar</th>
                                <th>Kur</th>
                                <th>Taksit Bilgisi</th>
                                <th>Toplam</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="recurringPaymentsList"></tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td class="text-end fw-bold">Toplam Ödeme:</td>
                                <td id="totalYearlyPayment" class="fw-bold"></td>
                            </tr>
                        </tfoot>
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
    <script src="js/utils.js"></script>
    <script src="js/income.js"></script>
    <script src="js/savings.js"></script>
    <script src="js/payments.js"></script>
    <script src="js/summary.js"></script>
    <script src="js/theme.js"></script>
    <script src="js/app.js"></script>
    <script src="js/auth.js"></script>
</body>

</html>