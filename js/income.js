// Gelir listesini güncelle
function updateIncomeList(incomes) {
    const tbody = $('#incomeList');
    tbody.empty();

    if (incomes.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">Henüz bir gelir eklenmemiş.</p>
                </td>
            </tr>
        `);
        return;
    }

    incomes.forEach(function (income) {
        const isChild = income.parent_id !== null;
        // const rowClass = isChild ? 'table-light' : '';
        let amountText = `${parseFloat(income.amount).toFixed(2)} ${income.currency}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (income.currency !== data.user.base_currency && income.exchange_rate) {
            const convertedAmount = parseFloat(income.amount) * parseFloat(income.exchange_rate);
            amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
        }

        tbody.append(`
            <tr>
                <td style="width: 50px;">
                    <button
                        class="btn btn-sm ${income.status === 'received' ? 'btn-success' : 'btn-outline-success'}"
                        onclick="markAsReceived(${income.id})"
                        title="${income.status === 'received' ? 'Alınmadı olarak işaretle' : 'Alındı olarak işaretle'}"
                    >
                        <i class="bi ${income.status === 'received' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                    </button>
                </td>
                <td>${income.name}</td>
                <td>${amountText}</td>
                <td>${income.currency}</td>
                <td>${income.first_date}</td>
                <td>${getFrequencyText(income.frequency)}</td>
                <td>${income.next_income_date || ''}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteIncome(${income.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

// Gelir durumunu güncelle
function markAsReceived(id) {
    ajaxRequest({
        action: 'mark_income_received',
        id: id
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
        }
    });
}

// Geliri sil
function deleteIncome(id) {
    Swal.fire({
        icon: 'warning',
        title: 'Silmek istediğinize emin misiniz?',
        showCancelButton: true,
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal',
    }).then((result) => {
        if (result.isConfirmed) {
            ajaxRequest({
                action: 'delete_income',
                id: id
            }).done(function (response) {
                if (response.status === 'success') {
                    loadData();
                }
            });
        }
    });
} 