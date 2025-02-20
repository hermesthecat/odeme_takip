<!-- Birikim Ekleme Modal -->
<div class="modal fade" id="savingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('add_saving'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="saving">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('saving_name'); ?></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('target_amount'); ?></label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('currency'); ?></label>
                        <select class="form-select" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('start_date'); ?></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('target_date'); ?></label>
                        <input type="date" class="form-control" name="target_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo t('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Birikim Güncelleme Modal -->
<div class="modal fade" id="updateSavingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('edit_saving'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="update_saving">
                <input type="hidden" name="id" id="update_saving_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('saving_name'); ?></label>
                        <input type="text" class="form-control" name="name" id="update_saving_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('target_amount'); ?></label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" id="update_saving_target_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('current_amount'); ?></label>
                        <input type="number" step="0.01" class="form-control" name="current_amount" id="update_saving_current_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('currency'); ?></label>
                        <select class="form-select" name="currency" id="update_saving_currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('start_date'); ?></label>
                        <input type="date" class="form-control" name="start_date" id="update_saving_start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('target_date'); ?></label>
                        <input type="date" class="form-control" name="target_date" id="update_saving_target_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo t('update'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>