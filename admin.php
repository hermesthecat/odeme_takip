<?php
require_once 'config.php';
require_once 'classes/log.php';
checkLogin();

if ($_SESSION['is_admin'] != 1) {
    echo "<script>window.location.href = '" . SITE_URL . "/app.php';</script>";
    exit;
}

// Sayfa başına gösterilecek kullanıcı sayısı
$users_per_page = 20;

// Mevcut sayfa numarası
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($current_page < 1) $current_page = 1;

// Filtreleme parametreleri
$username_filter = isset($_GET['username']) ? $_GET['username'] : '';
$is_admin_filter = isset($_GET['is_admin']) ? $_GET['is_admin'] : '';
$is_active_filter = isset($_GET['is_active']) ? $_GET['is_active'] : '';

// SQL sorgusu için WHERE koşulları
$where_conditions = [];
$params = [];

if (!empty($username_filter)) {
    $where_conditions[] = "username LIKE :username";
    $params[':username'] = '%' . $username_filter . '%';
}

if ($is_admin_filter !== '') {
    $where_conditions[] = "is_admin = :is_admin";
    $params[':is_admin'] = $is_admin_filter;
}

if ($is_active_filter !== '') {
    $where_conditions[] = "is_active = :is_active";
    $params[':is_active'] = $is_active_filter;
}

// WHERE koşulunu oluştur
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = ' WHERE ' . implode(' AND ', $where_conditions);
}

// Toplam kullanıcı sayısını al
global $pdo;
$count_sql = "SELECT COUNT(*) FROM users" . $where_clause;
$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_users = $count_stmt->fetchColumn();

// Toplam sayfa sayısını hesapla
$total_pages = ceil($total_users / $users_per_page);

// Mevcut sayfanın kullanıcılarını al
$offset = ($current_page - 1) * $users_per_page;
$users_sql = "SELECT * FROM users $where_clause ORDER BY id DESC LIMIT :limit OFFSET :offset";
$users_stmt = $pdo->prepare($users_sql);
foreach ($params as $key => $value) {
    $users_stmt->bindValue($key, $value);
}
$users_stmt->bindParam(':limit', $users_per_page, PDO::PARAM_INT);
$users_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLanguage(); ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title><?php echo t('site_name'); ?> - <?php echo t('admin.user_management'); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
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
    </style>
</head>

<body>
    <div class="container mt-4">

        <?php include 'navbar_app.php'; ?>

        <!-- Kullanıcı Filtreleme -->
        <div class="card mb-4">
            <div class="card-header bg-primary bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?php echo t('admin.user_filtering'); ?></h2>
                    <button class="btn btn-primary" onclick="showAddUserModal()">
                        <i class="bi bi-person-plus me-1"></i><?php echo t('admin.new_user'); ?>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="admin.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="username" class="form-label"><?php echo t('username'); ?></label>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Kullanıcı adı" value="<?php echo htmlspecialchars($username_filter); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="is_admin" class="form-label"><?php echo t('admin.administrator'); ?></label>
                        <select name="is_admin" id="is_admin" class="form-select">
                            <option value=""><?php echo t('all'); ?></option>
                            <option value="1" <?php echo $is_admin_filter === '1' ? 'selected' : ''; ?>><?php echo t('yes'); ?></option>
                            <option value="0" <?php echo $is_admin_filter === '0' ? 'selected' : ''; ?>><?php echo t('no'); ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="is_active" class="form-label"><?php echo t('status'); ?></label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value=""><?php echo t('all'); ?></option>
                            <option value="1" <?php echo $is_active_filter === '1' ? 'selected' : ''; ?>><?php echo t('active'); ?></option>
                            <option value="0" <?php echo $is_active_filter === '0' ? 'selected' : ''; ?>><?php echo t('inactive'); ?></option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><?php echo t('filter'); ?></button>
                        <a href="admin.php" class="btn btn-secondary"><?php echo t('reset'); ?></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kullanıcılar Tablosu -->
        <div class="card mb-4">
            <div class="card-header bg-info bg-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><?php echo t('admin.user_list'); ?></h2>
                    <span class="badge bg-primary"><?php echo $total_users; ?> <?php echo t('admin.user_count'); ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo t('admin.table.id'); ?></th>
                                <th><?php echo t('admin.table.username'); ?></th>
                                <th><?php echo t('admin.table.base_currency'); ?></th>
                                <th><?php echo t('admin.table.theme'); ?></th>
                                <th><?php echo t('admin.table.is_admin'); ?></th>
                                <th><?php echo t('admin.table.status'); ?></th>
                                <th><?php echo t('admin.table.last_login'); ?></th>
                                <th><?php echo t('admin.table.actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['base_currency']); ?></td>
                                        <td><?php echo htmlspecialchars($user['theme_preference']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $user['is_admin'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $user['is_admin'] ? t('yes') : t('no'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $user['is_active'] ? 'Aktif' : 'Pasif'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['last_login'] ? date('d.m.Y H:i:s', strtotime($user['last_login'])) : '-'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['id']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center"><?php echo t('admin.no_users_found'); ?></td>
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

    <!-- Kullanıcı Ekleme/Düzenleme Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle"><?php echo t('admin.add_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="userId" name="id">
                        <div class="mb-3">
                            <label for="modalUsername" class="form-label" data-translate="username">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="modalUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="modalPassword" class="form-label" data-translate="password">Şifre</label>
                            <input type="password" class="form-control" id="modalPassword" name="password">
                            <small class="text-muted"><?php echo t('admin.password_edit_note'); ?></small>
                        </div>
                        <div class="mb-3">
                            <label for="modalBaseCurrency" class="form-label" data-translate="base_currency">Ana Para Birimi</label>
                            <select class="form-select" id="modalBaseCurrency" name="base_currency" required>
                                <option value="TRY"><?php echo t('currency.try'); ?> (TRY)</option>
                                <option value="USD"><?php echo t('currency.usd'); ?> (USD)</option>
                                <option value="EUR"><?php echo t('currency.eur'); ?> (EUR)</option>
                                <option value="GBP"><?php echo t('currency.gbp'); ?> (GBP)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modalTheme" class="form-label" data-translate="theme">Tema</label>
                            <select class="form-select" id="modalTheme" name="theme_preference" required>
                                <option value="light"><?php echo t('settings.theme_light'); ?></option>
                                <option value="dark"><?php echo t('settings.theme_dark'); ?></option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="modalIsAdmin" name="is_admin">
                                <label class="form-check-label" for="modalIsAdmin" data-translate="admin.admin_role">Yönetici</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="modalIsActive" name="is_active" checked>
                                <label class="form-check-label" for="modalIsActive" data-translate="admin.active_status">Aktif</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-translate="cancel">İptal</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()" data-translate="save">Kaydet</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/modals/user_settings_modal.php'; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/utils.js"></script>
    <script src="js/language.js"></script>
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

        // Kullanıcı düzenleme
        function editUser(id) {
            fetch(`api/admin.php?action=get_user&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const user = data.user;
                        document.getElementById('userId').value = user.id;
                        document.getElementById('modalUsername').value = user.username;
                        document.getElementById('modalPassword').value = '';
                        document.getElementById('modalPassword').required = false;
                        document.getElementById('modalBaseCurrency').value = user.base_currency;
                        document.getElementById('modalTheme').value = user.theme_preference;
                        document.getElementById('modalIsAdmin').checked = user.is_admin == 1;
                        document.getElementById('modalIsActive').checked = user.is_active == 1;
                        document.getElementById('userModalTitle').textContent = t('admin.edit_user');
                        const modal = new bootstrap.Modal(document.getElementById('userModal'));
                        modal.show();
                    } else {
                        Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('<?php echo t('error'); ?>:', error);
                    Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', '<?php echo htmlspecialchars(t('admin.user_info_error')); ?>', 'error');
                });
        }

        // Kullanıcı kaydet
        function saveUser() {
            const formData = new FormData(document.getElementById('userForm'));
            const userId = formData.get('id');
            const action = userId ? 'update_user' : 'add_user';

            fetch('api/admin.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: action,
                        ...Object.fromEntries(formData)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '<?php echo t('success'); ?>',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('<?php echo t('error'); ?>:', error);
                    Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', '<?php echo htmlspecialchars(t('app.operation_error')); ?>', 'error');
                });
        }

        // Kullanıcı sil
        function deleteUser(id) {
            Swal.fire({
                title: '<?php echo htmlspecialchars(t('ui.confirm')); ?>',
                text: "<?php echo htmlspecialchars(t('admin.delete_user_confirm')); ?>",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '<?php echo htmlspecialchars(t('ui.yes_delete')); ?>',
                cancelButtonText: '<?php echo htmlspecialchars(t('ui.cancel')); ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('api/admin.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                action: 'delete_user',
                                id: id
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: '<?php echo t('success'); ?>',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('<?php echo t('error'); ?>:', error);
                            Swal.fire('<?php echo htmlspecialchars(t('error')); ?>', '<?php echo htmlspecialchars(t('admin.delete_error')); ?>', 'error');
                        });
                }
            });
        }

        // Tema ayarını uygula
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.body.setAttribute('data-theme', savedTheme);
        }

        // Çıkış işlemi
        document.querySelector('.logout-btn').addEventListener('click', function() {
            Swal.fire({
                title: '<?php echo t('logout_confirm'); ?>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<?php echo t('logout.yes'); ?>',
                cancelButtonText: '<?php echo t('cancel'); ?>'
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