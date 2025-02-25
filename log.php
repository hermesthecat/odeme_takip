<?php
require_once 'config.php';
require_once 'classes/log.php';
checkLogin();

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

</head>

<body>
    <div class="container mt-4">
        <!-- Başlık ve Kullanıcı Bilgisi -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Sistem Logları</h1>
            <div class="d-flex align-items-center">
                <a href="app.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-house me-1"></i>Ana Sayfa
                </a>
                <a href="borsa.php" class="btn btn-outline-success me-2">
                    <i class="bi bi-graph-up me-1"></i>Borsa
                </a>
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
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tarih</th>
                                <th>Tip</th>
                                <th>Metod</th>
                                <th>Kullanıcı</th>
                                <th>Mesaj</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php echo $log['id']; ?></td>
                                        <td><?php echo date('d.m.Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo isset($log_type_classes[$log['type']]) ? 'bg-' . substr($log_type_classes[$log['type']], 5) : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($log['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['log_method']); ?></td>
                                        <td><?php echo htmlspecialchars($log['username'] ?? 'Sistem'); ?></td>
                                        <td><?php echo htmlspecialchars($log['log_text']); ?></td>
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

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        // Tarih aralığı seçici
        $(function() {
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
                    window.location.href = 'logout.php';
                }
            });
        });
    </script>
</body>

</html> 