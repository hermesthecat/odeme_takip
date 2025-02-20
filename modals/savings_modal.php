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