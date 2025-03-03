<!-- Kullanıcı Ayarları Modal -->
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
            </div>
        </div>
    </div>
</div>