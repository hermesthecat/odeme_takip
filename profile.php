<?php
require_once 'config.php';
checkLogin();

// get user default currency from session
$user_default_currency = $_SESSION['base_currency'];

// Telegram bağlantı durumunu kontrol et
$stmt = $pdo->prepare("SELECT * FROM telegram_users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$telegram_user = $stmt->fetch();


// check post request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['generate_code'])) {
        generate_telegram_code();
    } else if (isset($_POST['unlink_telegram'])) {
        unlink_telegram();
    }
}

// Yeni kod oluştur
function generate_telegram_code()
{
    global $pdo, $telegram_user;
    // Rastgele 6 haneli kod oluştur
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // üretilen rastgele kod veritabanında var mı kontrol et
    // eğer varsa yeni bir kod üret
    $stmt = $pdo->prepare("SELECT * FROM telegram_users WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$code]);
    $user = $stmt->fetch();
    if ($user) {
        generate_telegram_code();
    }

    if ($telegram_user) {
        // Mevcut kaydı güncelle
        $stmt = $pdo->prepare("UPDATE telegram_users SET verification_code = ?, is_verified = 0 WHERE user_id = ?");
        $stmt->execute([$code, $_SESSION['user_id']]);
    } else {
        // Yeni kayıt oluştur
        $stmt = $pdo->prepare("INSERT INTO telegram_users (user_id, verification_code, telegram_id) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $code, '']);
    }

    //$_SESSION['success'] = "Yeni doğrulama kodu oluşturuldu: " . $code;
    echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
    exit;
}

// Telegram bağlantısını kaldır
function unlink_telegram()
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM telegram_users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    //$_SESSION['success'] = "Telegram bağlantısı kaldırıldı.";
    echo "<script>window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';</script>";
    exit;
}
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
    <link rel="stylesheet" href="profile.css" />

</head>

<body>
    <div class="container mt-4">

        <?php include 'navbar_app.php'; ?>

        <div class="container mt-5">
            <!-- Telegram Bağlantısı -->
            <div class="mt-4">
                <h5><?php echo t('settings.telegram_title'); ?></h5>

                <?php if ($telegram_user && $telegram_user['is_verified']): ?>
                    <p class="text-success">✅ <?php echo t('settings.telegram_connected'); ?></p>
                    <form method="post" class="d-inline">
                        <button type="submit" name="unlink_telegram" class="btn btn-danger" onclick="return confirm('<?php echo t('settings.telegram_confirm_unlink'); ?>')">
                            <?php echo t('settings.telegram_unlink'); ?>
                        </button>
                    </form>
                <?php else: ?>
                    <p><?php echo t('settings.telegram_info'); ?></p>
                    <ol>
                        <li><?php echo t('settings.telegram_step1'); ?>: <a href="https://t.me/<?php echo getenv('TELEGRAM_BOT_USERNAME'); ?>" target="_blank">@<?php echo getenv('TELEGRAM_BOT_USERNAME'); ?></a></li>
                        <li><?php echo t('settings.telegram_step2'); ?></li>
                        <li><?php echo t('settings.telegram_step3'); ?></li>
                        <li><?php echo t('settings.telegram_step4'); ?></li>
                    </ol>
                    <form method="post">
                        <button type="submit" name="generate_code" class="btn btn-primary">
                            <?php echo t('settings.telegram_get_code'); ?>
                        </button>
                    </form>
                    <?php if ($telegram_user && $telegram_user['verification_code']): ?>
                        <div class="mt-3">
                            <p><?php echo t('settings.telegram_current_code'); ?>: <strong><?php echo $telegram_user['verification_code']; ?></strong></p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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

    <script>
        // Sayfa yüklendiğinde ve her 5 dakikada bir güncelle
        window.onload = function() {
            // Tema ayarını uygula
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
                document.body.setAttribute('data-theme', savedTheme);
            }
        };

        // Kullanıcı temasını yükle
        ajaxRequest({
            action: 'get_user_data'
        }).done(function(response) {
            if (response.status === 'success') {
                setTheme(response.data.theme_preference);
            }
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