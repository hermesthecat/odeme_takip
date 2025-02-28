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
    <link rel="stylesheet" href="style.css" />

</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0"><?php echo t('site_name'); ?></h1>
            <div class="d-flex align-items-center">
                <?php if ($_SESSION['is_admin'] == 1) : ?>
                    <a href="admin.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-list-nested me-1"></i>Kullanıcılar
                    </a>
                <?php endif; ?>
                <?php if ($_SESSION['is_admin'] == 1) : ?>
                    <a href="log.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-list-nested me-1"></i>Log
                    </a>
                <?php endif; ?>
                <a href="borsa.php" class="btn btn-outline-success me-2">
                    <i class="bi bi-graph-up me-1"></i>Borsa
                </a>
                <button class="btn btn-outline-primary me-2" onclick="openUserSettings()">
                    <i class="bi bi-gear me-1"></i><?php echo t('settings.title'); ?>
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
                        <button class="btn btn-link text-primary me-3 fs-4" onclick="previousMonth()" title="<?php echo t('app.previous_month'); ?>">
                            <i class="bi bi-chevron-left"></i>
                        </button>

                        <div class="date-selector bg-light rounded-pill px-4 py-2 shadow-sm d-flex align-items-center">
                            <select id="monthSelect" class="form-select form-select-lg border-0 bg-transparent me-2" style="width: auto;">
                                <?php foreach (t('months') as $num => $month): ?>
                                    <option value="<?php echo $num - 1; ?>"><?php echo $month; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="yearSelect" class="form-select form-select-lg border-0 bg-transparent" style="width: auto;">
                                <?php
                                $currentYear = date('Y');
                                for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++) {
                                    echo "<option value=\"$year\">$year</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <button class="btn btn-link text-primary ms-3 fs-4" onclick="nextMonth()" title="<?php echo t('app.next_month'); ?>">
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
                            <?php echo t('app.monthly_income'); ?>
                        </h5>
                        <span class="d-flex justify-content-between mt-4">
                            <h3 class="card-text" id="monthlyIncome">0.00 </h3>
                            <h3 class="card-text"><?php echo $user_default_currency; ?></h3>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger bg-opacity-10 border-danger border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger text-opacity-75">
                            <i class="bi bi-graph-down me-2"></i>
                            <?php echo t('app.monthly_expense'); ?>
                        </h5>
                        <span class="d-flex justify-content-between mt-4">
                            <h3 class="card-text" id="monthlyExpense">0.00 </h3>
                            <h3 class="card-text"><?php echo $user_default_currency; ?></h3>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info bg-opacity-10 border-info border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-info text-opacity-75">
                            <i class="bi bi-wallet2 me-2"></i>
                            <?php echo t('app.net_balance'); ?>
                        </h5>
                        <span class="d-flex justify-content-between mt-4">
                            <h3 class="card-text" id="monthlyBalance">0.00 </h3>
                            <h3 class="card-text"><?php echo $user_default_currency; ?></h3>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning bg-opacity-10 border-warning border-opacity-25 h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning text-opacity-75">
                            <i class="bi bi-calendar3 me-2"></i>
                            <?php echo t('app.period'); ?>
                        </h5>
                        <h3 class="card-text mt-4" id="currentPeriod"></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gelirler Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-success bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?php echo t('incomes'); ?></h2>
                    <button class="btn btn-success" data-action="add" data-type="income">
                        <i class="bi bi-plus-lg me-1"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="incomeLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <div class="mt-2"><?php echo t('loading'); ?></div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-center"><?php echo t('income_name'); ?></th>
                                <th class="text-center"><?php echo t('income_amount'); ?></th>
                                <th class="text-center"><?php echo t('income.currency'); ?></th>
                                <th class="text-center"><?php echo t('income_date'); ?></th>
                                <th class="text-center"><?php echo t('income_frequency'); ?></th>
                                <th class="text-center"><?php echo t('app.next_income'); ?></th>
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
                    <h2 class="mb-0"><?php echo t('savings'); ?></h2>
                    <button class="btn btn-primary" data-action="add" data-type="saving">
                        <i class="bi bi-plus-lg me-1"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="savingsLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <div class="mt-2"><?php echo t('loading'); ?></div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="text-center"><?php echo t('saving_name'); ?></th>
                                <th class="text-center"><?php echo t('target_amount'); ?></th>
                                <th class="text-center"><?php echo t('current_amount'); ?></th>
                                <th class="text-center"><?php echo t('start_date'); ?></th>
                                <th class="text-center"><?php echo t('target_date'); ?></th>
                                <th class="text-center"><?php echo t('saving.progress'); ?></th>
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
                    <h2 class="mb-0"><?php echo t('payments'); ?></h2>
                    <button class="btn btn-danger" data-action="add" data-type="payment">
                        <i class="bi bi-plus-lg me-1"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="paymentsLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <div class="mt-2"><?php echo t('loading'); ?></div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-center"><?php echo t('payment_name'); ?></th>
                                <th class="text-center"><?php echo t('payment_amount'); ?></th>
                                <th class="text-center"><?php echo t('payment.currency'); ?></th>
                                <th class="text-center"><?php echo t('payment_date'); ?></th>
                                <th class="text-center"><?php echo t('payment_frequency'); ?></th>
                                <th class="text-center"><?php echo t('app.next_payment'); ?></th>
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
                    <h2 class="mb-0"><?php echo t('app.payment_power'); ?></h2>
                    <small class="text-muted"></small>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="recurringPaymentsLoadingSpinner" class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <div class="mt-2"><?php echo t('loading'); ?></div>
                    </div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo t('payment_name'); ?></th>
                                <th class="text-center"><?php echo t('payment_amount'); ?></th>
                                <th class="text-center"><?php echo t('payment.currency'); ?></th>
                                <th class="text-center"><?php echo t('app.installment_info'); ?></th>
                                <th class="text-center"><?php echo t('app.total'); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="recurringPaymentsList"></tbody>
                        <tfoot>
                            <tr class="table-info">
                                <td class="text-end fw-bold"><?php echo t('app.total_payment'); ?>:</td>
                                <td id="totalYearlyPayment" class="fw-bold"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sorumluluk Reddi -->
    <div class="container mt-5 mb-3">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-exclamation-triangle me-2"></i>Sorumluluk Reddi
            </div>
            <div class="card-body">
                <p class="card-text small text-muted">
                    Bu uygulama sadece kişisel bütçe yönetimi amacıyla kullanılmaktadır. Burada sunulan bilgiler finansal tavsiye niteliği taşımamaktadır.
                    Uygulama üzerinden yapılan işlemler ve alınan kararlardan doğabilecek sonuçlardan kullanıcı sorumludur.
                    Döviz kurları ve finansal veriler bilgi amaçlı olup, gerçek zamanlı değişiklik gösterebilir.
                </p>
                <p class="small text-center text-muted mb-0 mt-3">
                    &copy; <?php echo date('Y'); ?> <?php echo t('site_name'); ?> - Tüm hakları saklıdır.
                </p>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'modals.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JavaScript için dil çevirileri -->
    <script>
        const translations = {
            auth: {
                login: {
                    title: '<?php echo t('login.title'); ?>',
                    loading: '<?php echo t('login.loading'); ?>',
                    success: '<?php echo t('login.success'); ?>',
                    error: '<?php echo t('login.error'); ?>',
                    invalid: '<?php echo t('login.invalid'); ?>'
                },
                logout: {
                    confirm: '<?php echo t('logout_confirm'); ?>',
                    yes: '<?php echo t('yes'); ?>',
                    no: '<?php echo t('no'); ?>',
                    success: '<?php echo t('logout_success'); ?>'
                },
                buttons: {
                    login: '<?php echo t('login.title'); ?>',
                    register: '<?php echo t('register.title'); ?>',
                    cancel: '<?php echo t('cancel'); ?>',
                    confirm: '<?php echo t('confirm'); ?>'
                }
            },
            utils: {
                validation: {
                    required: '<?php echo t('utils.validation.required'); ?>',
                    numeric: '<?php echo t('utils.validation.numeric'); ?>',
                    date: '<?php echo t('utils.validation.date'); ?>',
                    currency: '<?php echo t('utils.validation.currency'); ?>',
                    frequency: '<?php echo t('utils.validation.frequency'); ?>',
                    min_value: '<?php echo t('utils.validation.min_value'); ?>',
                    max_value: '<?php echo t('utils.validation.max_value'); ?>',
                    error_title: '<?php echo t('utils.validation.error_title'); ?>',
                    confirm_button: '<?php echo t('utils.validation.confirm_button'); ?>'
                },
                session: {
                    error_title: '<?php echo t('utils.session.error_title'); ?>',
                    invalid_token: '<?php echo t('utils.session.invalid_token'); ?>'
                },
                frequency: {
                    none: '<?php echo t('utils.frequency.none'); ?>',
                    monthly: '<?php echo t('utils.frequency.monthly'); ?>',
                    bimonthly: '<?php echo t('utils.frequency.bimonthly'); ?>',
                    quarterly: '<?php echo t('utils.frequency.quarterly'); ?>',
                    fourmonthly: '<?php echo t('utils.frequency.fourmonthly'); ?>',
                    fivemonthly: '<?php echo t('utils.frequency.fivemonthly'); ?>',
                    sixmonthly: '<?php echo t('utils.frequency.sixmonthly'); ?>',
                    yearly: '<?php echo t('utils.frequency.yearly'); ?>'
                },
                form: {
                    income_name: '<?php echo t('utils.form.income_name'); ?>',
                    payment_name: '<?php echo t('utils.form.payment_name'); ?>',
                    amount: '<?php echo t('utils.form.amount'); ?>',
                    currency: '<?php echo t('utils.form.currency'); ?>',
                    date: '<?php echo t('utils.form.date'); ?>',
                    frequency: '<?php echo t('utils.form.frequency'); ?>',
                    saving_name: '<?php echo t('utils.form.saving_name'); ?>',
                    target_amount: '<?php echo t('utils.form.target_amount'); ?>',
                    current_amount: '<?php echo t('utils.form.current_amount'); ?>',
                    start_date: '<?php echo t('utils.form.start_date'); ?>',
                    target_date: '<?php echo t('utils.form.target_date'); ?>'
                }
            },
            savings: {
                no_data: '<?php echo t('saving.not_found'); ?>',
                delete: {
                    title: '<?php echo t('saving.delete_confirm'); ?>',
                    confirm: '<?php echo t('app.yes_delete'); ?>',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>'
                },
                progress: {
                    title: '<?php echo t('saving.progress'); ?>',
                    completed: '<?php echo t('saving.completed'); ?>',
                    on_track: '<?php echo t('saving.on_track'); ?>',
                    behind: '<?php echo t('saving.behind'); ?>',
                    ahead: '<?php echo t('saving.ahead'); ?>'
                }
            },
            income: {
                no_data: '<?php echo t('income.not_found'); ?>',
                mark_received: {
                    mark_as_received: '<?php echo t('income.mark_received'); ?>',
                    mark_as_not_received: '<?php echo t('income.mark_not_received'); ?>'
                },
                delete: {
                    title: '<?php echo t('income.delete_confirm'); ?>',
                    confirm: '<?php echo t('app.yes_delete'); ?>',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>'
                },
                modal: {
                    error_title: '<?php echo t('error'); ?>',
                    error_not_found: '<?php echo t('income.not_found'); ?>',
                    success_title: '<?php echo t('success'); ?>',
                    success_message: '<?php echo t('income.edit_success'); ?>',
                    error_message: '<?php echo t('income.edit_error'); ?>',
                    current_rate: '<?php echo t('currency.current_rate'); ?>'
                }
            },
            payment: {
                no_data: '<?php echo t('payment.not_found'); ?>',
                date: '<?php echo t('payment.date'); ?>',
                amount: '<?php echo t('payment.amount'); ?>',
                currency: '<?php echo t('payment.currency'); ?>',
                mark_paid: {
                    mark_as_paid: '<?php echo t('payment.mark_paid'); ?>',
                    mark_as_not_paid: '<?php echo t('payment.mark_not_paid'); ?>'
                },
                delete: {
                    title: '<?php echo t('payment.delete_confirm'); ?>',
                    confirm: '<?php echo t('app.yes_delete'); ?>',
                    confirm_all: '❗️ Bu seçenek işaretlendiğinde, bu ödemeye bağlı önceki ve sonraki tüm ödemeler silinecektir. ❗️',
                    cancel: '<?php echo t('cancel'); ?>'
                },
                buttons: {
                    edit: '<?php echo t('edit'); ?>',
                    delete: '<?php echo t('delete'); ?>',
                    transfer: '<?php echo t('payment.transfer'); ?>'
                },
                modal: {
                    error_title: '<?php echo t('error'); ?>',
                    error_not_found: '<?php echo t('payment.not_found'); ?>',
                    success_title: '<?php echo t('success'); ?>',
                    success_message: '<?php echo t('payment.edit_success'); ?>',
                    error_message: '<?php echo t('payment.edit_error'); ?>',
                    current_rate: '<?php echo t('currency.current_rate'); ?>'
                },
                recurring: {
                    total_payment: '<?php echo t('payment.recurring.total_payment'); ?>',
                    pending_payment: '<?php echo t('payment.recurring.pending_payment'); ?>'
                }
            },
            transfer: {
                title: '<?php echo t('transfer.title'); ?>',
                confirm: '<?php echo t('transfer.confirm'); ?>',
                transfer_button: '<?php echo t('transfer.transfer_button'); ?>',
                cancel_button: '<?php echo t('transfer.cancel_button'); ?>',
                error: '<?php echo t('transfer.error'); ?>',
                success: '<?php echo t('transfer.success'); ?>'
            }
        };
    </script>

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