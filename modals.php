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
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
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

<!-- Birikim Ekleme Modal -->
<div class="modal fade" id="savingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Birikim Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="saving">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Birikim İsmi</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" required>
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
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Tarihi</label>
                        <input type="date" class="form-control" name="target_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<!-- Birikim Güncelleme Modal -->
<div class="modal fade" id="updateSavingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Birikim Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="update_saving">
                <input type="hidden" name="id" id="update_saving_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Birikim İsmi</label>
                        <input type="text" class="form-control" name="name" id="update_saving_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="target_amount" id="update_saving_target_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Biriken Tutar</label>
                        <input type="number" step="0.01" class="form-control" name="current_amount" id="update_saving_current_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Para Birimi</label>
                        <select class="form-select" name="currency" id="update_saving_currency" required>
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" class="form-control" name="start_date" id="update_saving_start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hedef Tarihi</label>
                        <input type="date" class="form-control" name="target_date" id="update_saving_target_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Kullanıcı Ayarları Modal -->
<div class="modal fade" id="userSettingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kullanıcı Ayarları</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="user_settings">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ana Para Birimi</label>
                        <select class="form-select" name="base_currency" id="user_base_currency" required>
                            <option value="TRY">TRY - Türk Lirası</option>
                            <option value="USD">USD - Amerikan Doları</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                        <small class="text-muted">Tüm hesaplamalar bu para birimi üzerinden yapılacaktır.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tema</label>
                        <select class="form-select" name="theme_preference" id="user_theme_preference" required>
                            <option value="light">Açık Tema</option>
                            <option value="dark">Koyu Tema</option>
                        </select>
                        <small class="text-muted">Arayüz renk teması seçimi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
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
                            <option value="TRY">TRY</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
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
                            <option value="none">Tek Seferlik</option>
                            <option value="monthly">Aylık</option>
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

<script>
    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Gelirler için)
    document.getElementById('incomeFrequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('incomeEndDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });

    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Ödemeler için)
    document.getElementById('paymentFrequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('endDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });

    // Modal açıldığında tarihleri otomatik doldur
    document.addEventListener('DOMContentLoaded', function() {
        // Bugünün tarihini YYYY-MM-DD formatında al
        function getTodayDate() {
            const today = new Date();
            return today.toISOString().split('T')[0];
        }

        // Gelir modalı için
        const incomeModal = document.getElementById('incomeModal');
        incomeModal.addEventListener('show.bs.modal', function() {
            this.querySelector('input[name="first_date"]').value = getTodayDate();
        });

        // Ödeme modalı için
        const paymentModal = document.getElementById('paymentModal');
        paymentModal.addEventListener('show.bs.modal', function() {
            this.querySelector('input[name="first_date"]').value = getTodayDate();
        });
    });

    // Tekrarlama seçeneğine göre bitiş tarihi alanını göster/gizle (Ödeme güncelleme için)
    document.getElementById('update_payment_frequency').addEventListener('change', function() {
        const endDateGroup = document.getElementById('updatePaymentEndDateGroup');
        const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

        if (this.value === 'none') {
            endDateGroup.style.display = 'none';
            endDateInput.removeAttribute('required');
        } else {
            endDateGroup.style.display = 'block';
            endDateInput.setAttribute('required', 'required');
        }
    });
</script>