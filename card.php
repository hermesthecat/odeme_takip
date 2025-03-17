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
    <title><?php echo $site_name; ?> - <?php echo $site_description; ?></title>

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
                <a href="profile.php" class="btn btn-outline-success me-2">
                    <i class="bi bi-telegram me-1"></i>Telegram
                </a>
                <button class="btn btn-outline-primary me-2" onclick="openUserSettings()">
                    <i class="bi bi-gear me-1"></i><?php echo t('settings.title'); ?>
                </button>
                <button class="btn btn-outline-danger logout-btn">
                    <?php echo htmlspecialchars($_SESSION['username']); ?> <i class="bi bi-box-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        <!-- Ödeme Yöntemleri Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-success bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?php echo t('cards'); ?></h2>
                    <button class="btn btn-success" data-action="add" data-type="card">
                        <i class="bi bi-plus-lg me-1"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="cardLoadingSpinner" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                        <div class="mt-2"><?php echo t('loading'); ?></div>
                    </div>
                    <table class="table table-striped" style="display: table;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 70%">Ödeme Yöntemi</th>
                                <th class="text-end" style="width: 30%">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="cardList"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ödeme Yöntemleri - Ödemeler Listesi Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-success bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Ödemeler Listesi</h2>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php
                    // Kullanıcının kartlarını al
                    $cards = get_user_cards();

                    // Her kart için ödemeleri listele
                    foreach ($cards as $card) {
                        // Karta ait ödemeleri al
                        $sql = "SELECT p.*, 
                               CASE 
                                   WHEN p.parent_id IS NULL THEN (
                                       SELECT MIN(p2.first_date)
                                       FROM payments p2
                                       WHERE p2.parent_id = p.id
                                       AND p2.user_id = p.user_id
                                   )
                                   ELSE (
                                       SELECT MIN(p2.first_date)
                                       FROM payments p2
                                       WHERE p2.parent_id = p.parent_id
                                       AND p2.first_date > p.first_date
                                       AND p2.user_id = p.user_id
                                   )
                               END as next_payment_date
                               FROM payments p 
                               WHERE p.user_id = ? 
                               AND p.card_id = ?
                               ORDER BY p.name ASC";
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$_SESSION['user_id'], $card['id']]);
                        $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Kart başlığını göster
                        echo '<div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">' . htmlspecialchars($card['name']) . '</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Ödeme Adı</th>
                                                <th class="text-end">İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>';

                        if (count($payments) > 0) {
                            foreach ($payments as $payment) {
                                echo '<tr>
                                        <td class="text-center align-middle">' . htmlspecialchars($payment['name']) . '</td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-danger" onclick="deletePayment(' . $payment['id'] . ')" title="Sil">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>';
                            }
                        } else {
                            echo '<tr>
                                    <td colspan="6" class="text-center">
                                        <p class="text-muted mb-0">Bu ödeme yöntemine ait ödeme bulunmamaktadır.</p>
                                    </td>
                                </tr>';
                        }

                        echo '</tbody>
                            </table>
                        </div>
                    </div>';
                    }
                    ?>
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
                        &copy; <?php echo date('Y'); ?> <?php echo $site_name; ?> - Tüm hakları saklıdır.
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
                card: {
                    name: '<?php echo t('card.name'); ?>',
                    add_error: '<?php echo t('card.add_error'); ?>',
                    delete_error: '<?php echo t('card.delete_error'); ?>',
                    load_error: '<?php echo t('card.load_error'); ?>',
                    no_data: '<?php echo t('card.no_data'); ?>',
                    buttons: {
                        edit: '<?php echo t('card.buttons.edit'); ?>',
                        delete: '<?php echo t('card.buttons.delete'); ?>'
                    }
                }
            };
        </script>

        <script src="js/utils.js"></script>
        <script src="js/card.js"></script>
        <script src="js/summary.js"></script>
        <script src="js/theme.js"></script>
        <script src="js/app.js"></script>
        <script src="js/auth.js"></script>

</body>

</html>