<!-- Gelir Ekleme Modal -->
<div class="modal fade" id="incomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('add_income'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="income">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_name'); ?></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_amount'); ?></label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income.currency'); ?></label>
                        <select class="form-select" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_date'); ?></label>
                        <input type="date" class="form-control" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_frequency'); ?></label>
                        <select class="form-select" name="frequency" id="incomeFrequency" required>
                            <option value="none"><?php echo t('frequency.none'); ?></option>
                            <option value="monthly"><?php echo t('frequency.monthly'); ?></option>
                            <option value="bimonthly"><?php echo t('frequency.bimonthly'); ?></option>
                            <option value="quarterly"><?php echo t('frequency.quarterly'); ?></option>
                            <option value="fourmonthly"><?php echo t('frequency.fourmonthly'); ?></option>
                            <option value="fivemonthly"><?php echo t('frequency.fivemonthly'); ?></option>
                            <option value="sixmonthly"><?php echo t('frequency.sixmonthly'); ?></option>
                            <option value="yearly"><?php echo t('frequency.yearly'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3" id="incomeEndDateGroup" style="display: none;">
                        <label class="form-label"><?php echo t('income_end_date'); ?></label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-success"><?php echo t('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Gelir Güncelleme Modal -->
<div class="modal fade" id="updateIncomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('edit_income'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateIncomeForm">
                    <input type="hidden" id="update_income_id" name="id">
                    <input type="hidden" id="update_income_is_parent" name="is_parent" value="0">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_name'); ?></label>
                        <input type="text" class="form-control" id="update_income_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_amount'); ?></label>
                        <input type="number" class="form-control" id="update_income_amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income.currency'); ?></label>
                        <select class="form-select" id="update_income_currency" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="updateIncomeExchangeRateGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_income_exchange_rate" name="update_exchange_rate">
                            <label class="form-check-label" for="update_income_exchange_rate">
                                <?php echo t('update_rate'); ?>
                            </label>
                        </div>
                        <small id="current_income_exchange_rate" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_date'); ?></label>
                        <input type="date" class="form-control" id="update_income_first_date" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('income_frequency'); ?></label>
                        <select class="form-select" id="update_income_frequency" name="frequency" required>
                            <option value="none"><?php echo t('frequency.none'); ?></option>
                            <option value="monthly"><?php echo t('frequency.monthly'); ?></option>
                            <option value="bimonthly"><?php echo t('frequency.bimonthly'); ?></option>
                            <option value="quarterly"><?php echo t('frequency.quarterly'); ?></option>
                            <option value="fourmonthly"><?php echo t('frequency.fourmonthly'); ?></option>
                            <option value="fivemonthly"><?php echo t('frequency.fivemonthly'); ?></option>
                            <option value="sixmonthly"><?php echo t('frequency.sixmonthly'); ?></option>
                            <option value="yearly"><?php echo t('frequency.yearly'); ?></option>
                        </select>
                    </div>
                    <div class="mb-3" id="updateIncomeEndDateGroup" style="display: none;">
                        <label class="form-label"><?php echo t('income_end_date'); ?></label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                    <div class="mb-3" id="updateIncomeChildrenGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_income_children" name="update_children" checked>
                            <label class="form-check-label" for="update_income_children">
                                <?php echo t('income.update_children'); ?>
                            </label>
                            <small class="text-muted d-block mt-1" id="update_income_children_info"></small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                <button type="button" class="btn btn-primary" onclick="updateIncome()"><?php echo t('update'); ?></button>
            </div>
        </div>
    </div>
</div>