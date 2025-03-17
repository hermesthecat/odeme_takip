<!-- Ödeme Yönetimi Ekleme Modal -->
<div class="modal fade" id="cardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ödeme Yönetimi Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form data-type="card">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ödeme Yönetimi Adı</label>
                        <input type="text" class="form-control" name="name" required>
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

<!-- Ödeme Yönetimi Güncelleme Modal -->
<div class="modal fade" id="updateCardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ödeme Yöntemi Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateCardForm">
                    <input type="hidden" id="update_card_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Ödeme Yöntemi Adı</label>
                        <input type="text" class="form-control" id="update_card_name" name="name" required>
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