<!-- Gelir Ekleme Modal -->
<div class="modal fade" id="incomeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Gelir Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="income">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Gelir İsmi</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Para Birimi</label>
                        <select class="form-select" name="currency" required>
                            <?php foreach ($supported_currencies as $code => $name) : ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlk Gelir Tarihi</label>
                        <input type="date" class="form-control" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tekrarlama Sıklığı</label>
                        <select class="form-select" name="frequency" id="incomeFrequency" required>
                            <option value="none">Tekrar Yok</option>
                            <option value="monthly">Aylık</option>
                            <option value="bimonthly">2 Ayda Bir</option>
                            <option value="quarterly">3 Ayda Bir</option>
                            <option value="fourmonthly">4 Ayda Bir</option>
                            <option value="fivemonthly">5 Ayda Bir</option>
                            <option value="sixmonthly">6 Ayda Bir</option>
                            <option value="yearly">Yıllık</option>
                        </select>
                    </div>
                    <div class="mb-3" id="incomeEndDateGroup" style="display: none;">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Kaydet</button>
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
                <h5 class="modal-title">Gelir Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateIncomeForm">
                    <input type="hidden" id="update_income_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Gelir İsmi</label>
                        <input type="text" class="form-control" id="update_income_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar</label>
                        <input type="number" class="form-control" id="update_income_amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Para Birimi</label>
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
                                Güncel kur ile güncelle
                            </label>
                        </div>
                        <small id="current_income_exchange_rate" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlk Gelir Tarihi</label>
                        <input type="date" class="form-control" id="update_income_first_date" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tekrarlama Sıklığı</label>
                        <select class="form-select" id="update_income_frequency" name="frequency" required>
                            <option value="none">Tekrar Yok</option>
                            <option value="monthly">Aylık</option>
                            <option value="bimonthly">2 Ayda Bir</option>
                            <option value="quarterly">3 Ayda Bir</option>
                            <option value="fourmonthly">4 Ayda Bir</option>
                            <option value="fivemonthly">5 Ayda Bir</option>
                            <option value="sixmonthly">6 Ayda Bir</option>
                            <option value="yearly">Yıllık</option>
                        </select>
                    </div>
                    <div class="mb-3" id="updateIncomeEndDateGroup" style="display: none;">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="updateIncome()">Güncelle</button>
            </div>
        </div>
    </div>
</div>