<!-- Ödeme Ekleme Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('add_payment'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="payment">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_name'); ?></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_amount'); ?></label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment.currency'); ?></label>
                        <select class="form-select" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Yöntemi</label>
                        <select class="form-select" id="update_payment_card" name="card" required>
                            <option value=""><?php echo t('select_card'); ?></option>
                            <?php
                            // get user cards
                            $cards = get_user_cards();
                            foreach ($cards as $card) : ?>
                                <option value="<?php echo $card['id']; ?>"><?php echo $card['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_date'); ?></label>
                        <input type="date" class="form-control" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_frequency'); ?></label>
                        <select class="form-select" name="frequency" id="paymentFrequency" required>
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
                    <div class="mb-3" id="endDateGroup" style="display: none;">
                        <label class="form-label"><?php echo t('payment_end_date'); ?></label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo t('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ödeme Güncelleme Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo t('edit_payment'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updatePaymentForm">
                    <input type="hidden" id="update_payment_id" name="id">
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_name'); ?></label>
                        <input type="text" class="form-control" id="update_payment_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_amount'); ?></label>
                        <input type="number" class="form-control" id="update_payment_amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment.currency'); ?></label>
                        <select class="form-select" id="update_payment_currency" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ödeme Yöntemi</label>
                        <select class="form-select" id="update_payment_card" name="card" required>
                            <option value=""><?php echo t('select_card'); ?></option>
                            <?php
                            // get user cards
                            $cards = get_user_cards();
                            foreach ($cards as $card) : ?>
                                <option value="<?php echo $card['id']; ?>"><?php echo $card['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="updatePaymentExchangeRateGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_exchange_rate" name="update_exchange_rate">
                            <label class="form-check-label" for="update_exchange_rate">
                                <?php echo t('update_rate'); ?>
                            </label>
                        </div>
                        <small id="current_exchange_rate" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3" id="updatePaymentPower">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_payment_power" name="update_payment_power">
                            <label class="form-check-label" for="update_payment_power">
                                Ödeme Gücü Listesine Dahiil Et.
                            </label>
                        </div>
                        <small id="current_payment_power" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_date'); ?></label>
                        <input type="date" class="form-control" id="update_payment_first_date" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo t('payment_frequency'); ?></label>
                        <select class="form-select" id="update_payment_frequency" name="frequency" required>
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
                    <div class="mb-3" id="updatePaymentEndDateGroup" style="display: none;">
                        <label class="form-label"><?php echo t('payment_end_date'); ?></label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                    <div class="mb-3" id="updatePaymentChildrenGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_payment_children" name="update_children">
                            <label class="form-check-label" for="update_payment_children">
                                <?php echo t('income.update_children'); ?>
                            </label>
                            <small class="text-muted d-block mt-1" id="update_payment_children_info"></small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                <button type="button" class="btn btn-primary" onclick="updatePayment()"><?php echo t('update'); ?></button>
            </div>
        </div>
    </div>
</div>