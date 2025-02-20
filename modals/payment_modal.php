<!-- Ödeme Ekleme Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Ödeme Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="payment">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ödeme İsmi</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Para Birimi</label>
                        <select class="form-select" name="currency" required>
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlk Ödeme Tarihi</label>
                        <input type="date" class="form-control" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tekrarlama Sıklığı</label>
                        <select class="form-select" name="frequency" id="paymentFrequency" required>
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
                    <div class="mb-3" id="endDateGroup" style="display: none;">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-danger">Kaydet</button>
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
                <h5 class="modal-title">Ödeme Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updatePaymentForm">
                    <input type="hidden" id="update_payment_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Ödeme İsmi</label>
                        <input type="text" class="form-control" id="update_payment_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tutar</label>
                        <input type="number" class="form-control" id="update_payment_amount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label for="update_payment_currency" class="form-label">Para Birimi</label>
                        <select class="form-select" id="update_payment_currency" name="currency" required>
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                        </select>
                    </div>
                    <div class="mb-3" id="updatePaymentExchangeRateGroup" style="display: none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="update_exchange_rate" name="update_exchange_rate">
                            <label class="form-check-label" for="update_exchange_rate">
                                Güncel kur ile güncelle
                            </label>
                        </div>
                        <small id="current_exchange_rate" class="text-muted d-block mt-1"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">İlk Ödeme Tarihi</label>
                        <input type="date" class="form-control" id="update_payment_first_date" name="first_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tekrarlama Sıklığı</label>
                        <select class="form-select" id="update_payment_frequency" name="frequency" required>
                            <option value="none">Tek Seferlik</option>
                            <option value="monthly">Aylık</option>
                            <option value="yearly">Yıllık</option>
                        </select>
                    </div>
                    <div class="mb-3" id="updatePaymentEndDateGroup" style="display: none;">
                        <label class="form-label">Bitiş Tarihi</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="updatePayment()">Güncelle</button>
            </div>
        </div>
    </div>
</div>