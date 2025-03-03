<!-- Kullanıcı Ayarları Modal -->
<?php
require_once __DIR__ . '/../config.php';

// Telegram bağlantı durumunu kontrol et
$stmt = $pdo->prepare("SELECT * FROM telegram_users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$telegram_user = $stmt->fetch();

// Yeni kod oluştur
if (isset($_POST['generate_code'])) {
    // Rastgele 6 haneli kod oluştur
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    if ($telegram_user) {
        // Mevcut kaydı güncelle
        $stmt = $pdo->prepare("UPDATE telegram_users SET verification_code = ?, is_verified = 0 WHERE user_id = ?");
        $stmt->execute([$code, $_SESSION['user_id']]);
    } else {
        // Yeni kayıt oluştur
        $stmt = $pdo->prepare("INSERT INTO telegram_users (user_id, verification_code) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $code]);
    }

    $_SESSION['success'] = "Yeni doğrulama kodu oluşturuldu: " . $code;
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Telegram bağlantısını kaldır
if (isset($_POST['unlink_telegram'])) {
    $stmt = $pdo->prepare("DELETE FROM telegram_users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    $_SESSION['success'] = "Telegram bağlantısı kaldırıldı.";
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
<div class="modal fade" id="userSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('settings.title'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form data-type="user_settings">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('settings.base_currency'); ?></label>
                        <select class="form-select" name="base_currency" id="user_base_currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted"><?php echo t('settings.base_currency_info'); ?></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('settings.theme'); ?></label>
                        <select class="form-select" name="theme_preference" id="user_theme_preference" required>
                            <option value="light"><?php echo t('settings.theme_light'); ?></option>
                            <option value="dark"><?php echo t('settings.theme_dark'); ?></option>
                        </select>
                        <small class="text-muted"><?php echo t('settings.theme_info'); ?></small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo t('save'); ?></button>
                    </div>
                </form>

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
    </div>
</div>