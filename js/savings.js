// Birikimler listesini güncelle
function updateSavingsList(savings) {
    const tbody = $('#savingList');
    tbody.empty();

    if (savings.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">${translations.savings.no_data}</p>
                </td>
            </tr>
        `);
        return;
    }

    savings.forEach(function (saving) {
        const progress = (saving.current_amount / saving.target_amount) * 100;
        const progressClass = progress < 25 ? 'bg-danger' :
            progress < 50 ? 'bg-warning' :
                progress < 75 ? 'bg-info' :
                    'bg-success';
        tbody.append(`
            <tr>
                <td>${saving.name}</td>
                <td>${saving.target_amount}</td>
                <td>${saving.current_amount}</td>
                <td>${saving.currency}</td>
                <td>${saving.start_date}</td>
                <td>${saving.target_date}</td>
                <td>
                    <div class="progress mb-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted text-center d-block">%${progress.toFixed(0)}</small>
                </td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateSavingModal(${JSON.stringify(saving).replace(/"/g, '&quot;')})" title="${translations.savings.buttons.edit}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSaving(${saving.id})" title="${translations.savings.buttons.delete}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });
}

// Birikim güncelleme
function updateSaving(id, current_amount) {
    ajaxRequest({
        action: 'update_saving',
        id: id,
        current_amount: current_amount
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
        }
    });
}

// Birikim güncelleme modalını aç
function openUpdateSavingModal(saving) {
    $('#update_saving_id').val(saving.id);
    $('#update_saving_name').val(saving.name);
    $('#update_saving_target_amount').val(saving.target_amount);
    $('#update_saving_current_amount').val(saving.current_amount);
    $('#update_saving_currency').val(saving.currency);
    $('#update_saving_start_date').val(saving.start_date);
    $('#update_saving_target_date').val(saving.target_date);

    const modal = new bootstrap.Modal(document.getElementById('updateSavingModal'));
    modal.show();
}

// Birikimi sil
function deleteSaving(id) {
    Swal.fire({
        icon: 'warning',
        title: translations.savings.delete.title,
        showCancelButton: true,
        confirmButtonText: translations.savings.delete.confirm,
        cancelButtonText: translations.savings.delete.cancel,
    }).then((result) => {
        if (result.isConfirmed) {
            ajaxRequest({
                action: 'delete_saving',
                id: id
            }).done(function (response) {
                if (response.status === 'success') {
                    loadData();
                }
            });
        }
    });
} 