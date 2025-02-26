<?php
require_once 'config.php';
require_once 'classes/log.php';
checkLogin();

if ($_SESSION['is_admin'] != 1) {
    header("Location: app.php");
    exit;
}

// Sayfa başına gösterilecek log sayısı
$logs_per_page = 50;

// Mevcut sayfa numarası
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;

// Filtreleme parametreleri
$log_type = isset($_GET['log_type']) ? $_GET['log_type'] : '';
$log_method = isset($_GET['log_method']) ? $_GET['log_method'] : '';
$username = isset($_GET['username']) ? $_GET['username'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '';

// SQL sorgusu için WHERE koşulları
$where_conditions = [];
$params = [];

if (!empty($log_type)) {
    $where_conditions[] = "l.type = :log_type";
    $params[':log_type'] = $log_type;
}

if (!empty($log_method)) {
    $where_conditions[] = "l.log_method LIKE :log_method";
    $params[':log_method'] = '%' . $log_method . '%';
}

if (!empty($username)) {
    $where_conditions[] = "u.username LIKE :username";
    $params[':username'] = '%' . $username . '%';
}

if (!empty($date_range)) {
    // Tarih aralığını işle (örnek: 01.01.2023-31.01.2023)
    $dates = explode('-', $date_range);
    if (count($dates) == 2) {
        $start_date = date('Y-m-d 00:00:00', strtotime(trim($dates[0])));
        $end_date = date('Y-m-d 23:59:59', strtotime(trim($dates[1])));

        $where_conditions[] = "l.created_at BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
}

// WHERE koşulunu oluştur
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
}

// Toplam log sayısını al
global $pdo;
$count_sql = "SELECT COUNT(*) FROM logs l LEFT JOIN users u ON l.user_id = u.id" . $where_clause;
$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_logs = $count_stmt->fetchColumn();

// Toplam sayfa sayısını hesapla
$total_pages = ceil($total_logs / $logs_per_page);

// Mevcut sayfanın loglarını al
$offset = ($current_page - 1) * $logs_per_page;
$logs_sql = "SELECT l.*, u.username 
             FROM logs l 
             LEFT JOIN users u ON l.user_id = u.id 
             $where_clause
             ORDER BY l.created_at DESC 
             LIMIT :limit OFFSET :offset";
$logs_stmt = $pdo->prepare($logs_sql);
foreach ($params as $key => $value) {
    $logs_stmt->bindValue($key, $value);
}
$logs_stmt->bindParam(':limit', $logs_per_page, PDO::PARAM_INT);
$logs_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$logs_stmt->execute();
$logs = $logs_stmt->fetchAll(PDO::FETCH_ASSOC);

// Log tipine göre renk sınıfları
$log_type_classes = [
    'info' => 'text-info',
    'error' => 'text-danger',
    'warning' => 'text-warning',
    'success' => 'text-success',
    'debug' => 'text-secondary'
];

// Log tipine göre arka plan renk sınıfları
$log_type_bg_classes = [
    'info' => 'bg-info bg-opacity-25',
    'error' => 'bg-danger bg-opacity-25',
    'warning' => 'bg-warning bg-opacity-25',
    'success' => 'bg-success bg-opacity-25',
    'debug' => 'bg-secondary bg-opacity-25'
];

?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title><?php echo t('site_name'); ?> - Sistem Logları</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <style>
        /* Dark Mode Stilleri */
        [data-theme="dark"] {
            --bs-body-bg: #212529;
            --bs-body-color: #f8f9fa;
            --bs-card-bg: #343a40;
            --bs-card-border-color: #495057;
            --bs-table-color: #f8f9fa;
            --bs-table-bg: #343a40;
            --bs-table-border-color: #495057;
            --bs-modal-bg: #343a40;
            --bs-modal-border-color: #495057;
            --bs-modal-color: #f8f9fa;
        }

        [data-theme="dark"] .card {
            background-color: var(--bs-card-bg);
            border-color: var(--bs-card-border-color);
            color: var(--bs-body-color);
        }

        [data-theme="dark"] .table {
            color: var(--bs-table-color);
            background-color: var(--bs-table-bg);
            border-color: var(--bs-table-border-color);
        }

        [data-theme="dark"] .modal-content {
            background-color: var(--bs-modal-bg);
            border-color: var(--bs-modal-border-color);
            color: var(--bs-modal-color);
        }

        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select {
            background-color: #495057;
            border-color: #6c757d;
            color: #f8f9fa;
        }

        [data-theme="dark"] .form-control:focus,
        [data-theme="dark"] .form-select:focus {
            background-color: #495057;
            border-color: #0d6efd;
            color: #f8f9fa;
        }

        [data-theme="dark"] .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        [data-theme="dark"] .daterangepicker {
            background-color: #343a40;
            border-color: #495057;
            color: #f8f9fa;
        }

        [data-theme="dark"] .daterangepicker .calendar-table {
            background-color: #343a40;
            border-color: #495057;
        }

        [data-theme="dark"] .daterangepicker td.off,
        [data-theme="dark"] .daterangepicker td.off.in-range,
        [data-theme="dark"] .daterangepicker td.off.start-date,
        [data-theme="dark"] .daterangepicker td.off.end-date {
            background-color: #212529;
            color: #6c757d;
        }

        [data-theme="dark"] .daterangepicker td.active,
        [data-theme="dark"] .daterangepicker td.active:hover {
            background-color: #0d6efd;
            color: #fff;
        }

        [data-theme="dark"] .daterangepicker .drp-buttons {
            border-top-color: #495057;
        }

        [data-theme="dark"] .daterangepicker .ranges li:hover {
            background-color: #495057;
        }

        [data-theme="dark"] .daterangepicker .ranges li.active {
            background-color: #0d6efd;
        }

        /* Tablo satırları için güçlü stil kuralları */
        [data-theme="dark"] body .table tbody tr {
            background-color: #343a40 !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr[data-type="info"] {
            background-color: #0d4c66 !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr[data-type="error"] {
            background-color: #842029 !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr[data-type="warning"] {
            background-color: #664d03 !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr[data-type="success"] {
            background-color: #0f5132 !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr[data-type="debug"] {
            background-color: #41464b !important;
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] body .table tbody tr td {
            color: rgb(0, 0, 0) !important;
        }

        [data-theme="dark"] pre {
            color: rgb(0, 0, 0) !important;
        }

        /* Tablo satırları için güçlü stil kuralları */
        body .table tbody tr {
            background-color: white !important;
        }

        body .table tbody tr[data-type="info"] {
            background-color: #9fe5f7 !important;
        }

        body .table tbody tr[data-type="error"] {
            background-color: #f5c2c7 !important;
        }

        body .table tbody tr[data-type="warning"] {
            background-color: #ffe69c !important;
        }

        body .table tbody tr[data-type="success"] {
            background-color: #a3cfbb !important;
        }

        body .table tbody tr[data-type="debug"] {
            background-color: #c5c6c9 !important;
        }

        /* Hover ve zebra çizgilerini devre dışı bırak */
        body .table tr:hover,
        body .table tr:nth-child(even),
        body .table tr:nth-child(odd) {
            background-color: inherit !important;
        }
    </style>

</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Sistem Logları</h1>
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

        <!-- Log Filtreleme -->
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Log Filtreleme</h2>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="log.php" class="row g-3">
                    <div class="col-md-3">
                        <label for="log_type" class="form-label">Log Tipi</label>
                        <select name="log_type" id="log_type" class="form-select">
                            <option value="">Tümü</option>
                            <option value="info" <?php echo $log_type == 'info' ? 'selected' : ''; ?>>Bilgi</option>
                            <option value="error" <?php echo $log_type == 'error' ? 'selected' : ''; ?>>Hata</option>
                            <option value="warning" <?php echo $log_type == 'warning' ? 'selected' : ''; ?>>Uyarı</option>
                            <option value="success" <?php echo $log_type == 'success' ? 'selected' : ''; ?>>Başarılı</option>
                            <option value="debug" <?php echo $log_type == 'debug' ? 'selected' : ''; ?>>Debug</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="log_method" class="form-label">Metod</label>
                        <input type="text" name="log_method" id="log_method" class="form-control" placeholder="Metod adı" value="<?php echo htmlspecialchars($log_method); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="username" class="form-label">Kullanıcı</label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Kullanıcı adı" value="<?php echo htmlspecialchars($username); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="date_range" class="form-label">Tarih Aralığı</label>
                        <input type="text" name="date_range" id="date_range" class="form-control" placeholder="Tarih aralığı" value="<?php echo htmlspecialchars($date_range); ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                        <a href="log.php" class="btn btn-secondary">Sıfırla</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loglar Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-info bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Sistem Logları</h2>
                    <span class="badge bg-primary"><?php echo $total_logs; ?> kayıt bulundu</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-responsive">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Tür</th>
                                <th>Metod</th>
                                <th>Kullanıcı</th>
                                <th>Mesaj</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                    $bg_color = '';
                                    switch ($log['type']) {
                                        case 'info':
                                            $bg_color = '#9fe5f7'; // Daha koyu bg-info
                                            break;
                                        case 'error':
                                            $bg_color = '#f5c2c7'; // Daha koyu bg-danger
                                            break;
                                        case 'warning':
                                            $bg_color = '#ffe69c'; // Daha koyu bg-warning
                                            break;
                                        case 'success':
                                            $bg_color = '#a3cfbb'; // Daha koyu bg-success
                                            break;
                                        case 'debug':
                                            $bg_color = '#c5c6c9'; // Daha koyu bg-secondary
                                            break;
                                        default:
                                            $bg_color = 'white';
                                    }
                                    ?>
                                    <tr>
                                        <td style="background-color: <?php echo $bg_color; ?> !important;"><?php echo $log['id']; ?></td>
                                        <td style="background-color: <?php echo $bg_color; ?> !important;"><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td style="background-color: <?php echo $bg_color; ?> !important;">
                                            <span class="badge <?php echo isset($log_type_classes[$log['type']]) ? 'bg-' . substr($log_type_classes[$log['type']], 5) : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($log['type']); ?>
                                            </span>
                                        </td>
                                        <td style="background-color: <?php echo $bg_color; ?> !important;"><?php echo "<pre>" . htmlspecialchars($log['log_method']) . "</pre>"; ?></td>
                                        <td style="background-color: <?php echo $bg_color; ?> !important;"><?php echo htmlspecialchars($log['username'] ?? 'Sistem'); ?></td>
                                        <td style="background-color: <?php echo $bg_color; ?> !important; max-width: 300px; cursor: pointer;" class="text-truncate message-cell" data-message="<?php echo htmlspecialchars($log['log_text']); ?>"><?php echo htmlspecialchars(mb_substr($log['log_text'], 0, 50) . (mb_strlen($log['log_text']) > 50 ? '...' : '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Kayıt bulunamadı</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Sayfalama -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Sayfalama">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>" aria-label="Önceki">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);

                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1' . (!empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '') . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($i == $current_page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . (!empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '') . '">' . $i . '</a></li>';
                            }

                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . (!empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '') . '">' . $total_pages . '</a></li>';
                            }
                            ?>

                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : ''; ?>" aria-label="Sonraki">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/modals/user_settings_modal.php'; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="js/utils.js"></script>
    <script src="js/theme.js"></script>
    <script>
        // Kullanıcı temasını yükle
        ajaxRequest({
            action: 'get_user_data'
        }).done(function(response) {
            if (response.status === 'success') {
                setTheme(response.data.theme_preference);
                // Tema değişikliğinden sonra portföyü güncelle
                portfoyGuncelle();
            }
        });

        // Kullanıcı ayarları formu submit
        $('form[data-type="user_settings"]').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const formData = form.serializeObject();

            ajaxRequest({
                action: 'update_user_settings',
                ...formData
            }).done(function(response) {
                if (response.status === 'success') {
                    const modalElement = form.closest('.modal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();
                    setTheme(formData.theme_preference);
                    // Tema değişikliğinden sonra portföyü güncelle
                    portfoyGuncelle();
                    loadData();
                }
            });
        });

        // Tema ayarını uygula
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.body.setAttribute('data-theme', savedTheme);
        }

        // Tarih aralığı seçici
        $(function() {
            // Tablo satırlarının arka plan renklerini JavaScript ile ayarla
            $('tr').each(function() {
                var bgColor = $(this).css('background-color');
                if (bgColor === 'rgba(0, 0, 0, 0)' || bgColor === 'transparent') {
                    var type = $(this).find('td:eq(2) span').text().toLowerCase().trim();
                    switch (type) {
                        case 'info':
                            $(this).css('background-color', '#9fe5f7');
                            break;
                        case 'error':
                            $(this).css('background-color', '#f5c2c7');
                            break;
                        case 'warning':
                            $(this).css('background-color', '#ffe69c');
                            break;
                        case 'success':
                            $(this).css('background-color', '#a3cfbb');
                            break;
                        case 'debug':
                            $(this).css('background-color', '#c5c6c9');
                            break;
                    }
                }
            });

            $('#date_range').daterangepicker({
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Uygula',
                    cancelLabel: 'İptal',
                    fromLabel: 'Başlangıç',
                    toLabel: 'Bitiş',
                    customRangeLabel: 'Özel Aralık',
                    weekLabel: 'H',
                    daysOfWeek: ['Pz', 'Pt', 'Sa', 'Ça', 'Pe', 'Cu', 'Ct'],
                    monthNames: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'],
                    firstDay: 1
                },
                autoUpdateInput: false,
                ranges: {
                    'Bugün': [moment(), moment()],
                    'Dün': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Son 7 Gün': [moment().subtract(6, 'days'), moment()],
                    'Son 30 Gün': [moment().subtract(29, 'days'), moment()],
                    'Bu Ay': [moment().startOf('month'), moment().endOf('month')],
                    'Geçen Ay': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD.MM.YYYY') + ' - ' + picker.endDate.format('DD.MM.YYYY'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            // Mesaj hücrelerine tıklandığında modal gösterme
            $('.message-cell').on('click', function() {
                const message = $(this).data('message');
                Swal.fire({
                    title: 'Log Mesajı',
                    html: `<div class="text-start">${message}</div>`,
                    width: '80%',
                    confirmButtonText: 'Kapat'
                });
            });
        });

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