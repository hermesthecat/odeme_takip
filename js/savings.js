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

        let target_amountText = `${formatMyMoney(parseFloat(saving.target_amount).toFixed(2))} ${saving.currency}`;
        let current_amountText = `${formatMyMoney(parseFloat(saving.current_amount).toFixed(2))} ${saving.currency}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (saving.currency !== data.user.base_currency && saving.exchange_rate) {
            const converted_targetAmount = parseFloat(saving.target_amount) * parseFloat(saving.exchange_rate);
            const converted_currentAmount = parseFloat(saving.current_amount) * parseFloat(saving.exchange_rate
            );
            target_amountText += `<br><small class="text-muted">(${formatMyMoney(converted_targetAmount.toFixed(2))} ${data.user.base_currency})</small>`;
            current_amountText += `<br><small class="text-muted">(${formatMyMoney(converted_currentAmount.toFixed(2))} ${data.user.base_currency})</small>`;
        }

        let goalText = '';

        // check if the saving is a goal
        if (saving.goal) {
            goalText = '✔️';
        } else {
            goalText = '';
        }

        tbody.append(`
            <tr>
                <td>
                ${progress === 100 ?
                `${goalText} ${saving.name} ${goalText}`
                :
                `${saving.name}`
            }
                </td>
                <td>${target_amountText}</td>
                <td>${current_amountText}</td>
                <td>${saving.start_date}</td>
                <td>${saving.target_date}</td>
                <td class="text-center">
                    ${progress === 100 ?
                `💯`
                :
                `<div class="progress mb-1">
                            <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                                 aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted text-center d-block">%${progress.toFixed(0)}</small>`
            }
                </td>
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateSavingModal(${saving.id})" title="${translations.savings.buttons.edit}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSaving(${saving.id})" title="${translations.savings.buttons.delete}">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-info" onclick="showSavingsHistory(${saving.id})" title="History">
                           <i class="bi bi-clock-history"></i>
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
function openUpdateSavingModal(savingId) {
    ajaxRequest({
        action: 'get_saving_details',
        id: savingId
    }).done(function (response) {
        if (response.status === 'success') {
            const saving = response.data;
            $('#update_saving_id').val(saving.id);
            $('#update_saving_name').val(saving.name);
            $('#update_saving_target_amount').val(saving.target_amount);
            $('#update_saving_current_amount').val(saving.current_amount);
            $('#update_saving_currency').val(saving.currency);
            $('#update_saving_start_date').val(saving.start_date);
            $('#update_saving_target_date').val(saving.target_date);

            const modal = new bootstrap.Modal(document.getElementById('updateSavingModal'));
            modal.show();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load saving details',
            });
        }
    });
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

function getSavingsHistory(savingId) {
    return ajaxRequest({
        action: 'get_savings_history',
        id: savingId
    });
}

// Set default dates when the add saving modal is opened
$('#savingModal').on('show.bs.modal', function (e) {
    const today = new Date();
    const nextYear = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());

    const todayFormatted = today.toISOString().slice(0, 10);
    const nextYearFormatted = nextYear.toISOString().slice(0, 10);

    $(this).find('input[name="start_date"]').val(todayFormatted);
    $(this).find('input[name="target_date"]').val(nextYearFormatted);
});

function showSavingsHistory(savingId) {
    getSavingsHistory(savingId).done(function (response) {
        if (response.status === 'success') {
            const history = response.data;
            let historyHtml = '<table class="table table-bordered">';
            historyHtml += '<thead><tr><th>Date</th><th>Amount</th></tr></thead>';
            historyHtml += '<tbody>';
            history.forEach(item => {
                const formattedDate = new Date(item.created_at).toLocaleDateString();
                let current_amountText = '';

                // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
                if (item.currency !== data.user.base_currency && item.exchange_rate) {
                    const converted_currentAmount = parseFloat(item.current_amount) * parseFloat(item.exchange_rate
                    );
                    current_amountText += `<br><small class="text-muted">(${formatMyMoney(converted_currentAmount.toFixed(2))} ${data.user.base_currency})</small>`;
                }

                // chck change_direction or amount_difference is null or not and create a current_amountText
                if (item.change_direction === null || item.amount_difference === null) {
                    current_amountText = `${formatMyMoney(parseFloat(item.current_amount).toFixed(2))} ${item.currency}`;
                } else {
                    current_amountText = `${formatMyMoney(parseFloat(item.current_amount).toFixed(2))}<br><small class="text-muted">${item.amount_difference} ${item.change_direction} </small>`;
                }


                historyHtml += `<tr>
                <td>${formattedDate}</td>
                <td>${current_amountText}</td>
                </tr>`;
            });
            historyHtml += '</tbody></table>';

            Swal.fire({
                title: 'Savings History',
                html: historyHtml,
                confirmButtonText: 'Close'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load savings history',
            });
        }
    });
}