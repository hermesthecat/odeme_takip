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
</script>