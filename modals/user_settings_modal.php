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