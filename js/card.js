// Ödeme yöntemi listesini güncelle
function updateCardList(cards) {

    const tbody = $('#cardList');

    tbody.empty();

    if (!cards || cards.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="2" class="text-center">
                    <p class="text-muted mb-0">${translations.card.no_data || 'Henüz ödeme yöntemi eklenmemiş'}</p>
                </td>
            </tr>
        `);
        return;
    }

    cards.forEach(function (card, index) {
        tbody.append(`
            <tr>
                <td class="text-center align-middle">${card.name}</td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateCardModal(${card.id})" title="${translations.card.buttons.edit || 'Düzenle'}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCard(${card.id})" title="${translations.card.buttons.delete || 'Sil'}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });

    // Tablo ve container'ı göster
    const table = tbody.closest('.table');
    const container = tbody.closest('.table-responsive');

    $('#cardLoadingSpinner').hide();
    container.show();
    table.show();
}

// Ödeme yöntemi sil
function deleteCard(id) {
    Swal.fire({
        icon: 'warning',
        title: translations.card.delete.title,
        showCancelButton: true,
        confirmButtonText: translations.card.delete.confirm,
        cancelButtonText: translations.card.delete.cancel,
    }).then((result) => {
        if (result.isConfirmed) {
            ajaxRequest({
                action: 'delete_card',
                id: id
            }).done(function (response) {
                if (response.status === 'success') {
                    loadCardData();
                }
            });
        }
    });
}

// Ödeme yöntemi  güncelleme modalını aç
function openUpdateCardModal(id) {
    // Ödeme yöntemi verilerini yükle
    ajaxRequest({
        action: 'get_data',
        load_type: 'card'
    }).done(function (response) {
        if (response.status === 'success') {

            // bilgileri yüklemeden önce Modal alanlarını resetle
            $('#update_card_id').val('');
            $('#update_card_name').val('');

            const card = response.data.cards.find(card => String(card.id) === String(id));

            if (card) {
                // Modal alanlarını doldur
                $('#update_card_id').val(card.id);
                $('#update_card_name').val(card.name);

                // Modalı göster
                const modal = new bootstrap.Modal(document.getElementById('updateCardModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: translations.card.modal.error_title,
                    text: translations.card.modal.error_not_found.replace(':id', id)
                });
            }
        }
    });
}

// Ödeme yöntemi güncelle
function updateCard() {
    // Form verilerini obje olarak al
    const formData = $('#updateCardForm').serializeObject();
    formData.action = 'update_card';

    // Ödeme yöntemi güncelle
    ajaxRequest(formData).done(function (response) {
        if (response.status === 'success') {
            // Modalı kapat
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateCardModal'));
            modal.hide();

            // Tabloyu güncelle
            loadCardData();

            // Başarı mesajı göster
            Swal.fire({
                icon: 'success',
                title: translations.card.modal.success_title,
                text: translations.card.modal.success_message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: translations.card.modal.error_title,
                text: response.message || translations.card.modal.error_message
            });
        }
    });
}