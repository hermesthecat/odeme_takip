// Ödemeler listesini güncelle
function updatePaymentsList(payments) {
    const tbody = $('#paymentList');
    tbody.empty();

    if (payments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">Henüz bir ödeme eklenmemiş.</p>
                </td>
            </tr>
        `);
        return;
    }

    payments.forEach(function (payment) {
        const isChild = payment.parent_id !== null;
        const rowClass = isChild ? 'table-light' : '';
        let amountText = `${parseFloat(payment.amount).toFixed(2)} ${payment.currency}`;

        if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
            const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
            amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
        }

        tbody.append(`
            <tr class="${rowClass}">
                <td style="width: 50px;">
                    <button
                        class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                        onclick="markAsPaid(${payment.id})"
                        title="${payment.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                    >
                        <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                    </button>
                </td>
                <td>${payment.name}</td>
                <td>${amountText}</td>
                <td>${payment.currency}</td>
                <td>${payment.first_date}</td>
                <td>${getFrequencyText(payment.frequency)}</td>
                <td>${payment.next_payment_date || ''}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    // Aktarma butonu satırı
    tbody.append(`
        <tr class="table-secondary">
            <td colspan="8" class="text-end">
                <button class="btn btn-warning" onclick="transferUnpaidPayments()">
                    <i class="bi bi-arrow-right-circle me-1"></i>
                    Sonraki Aya Aktar
                </button>
            </td>
        </tr>
    `);
}

// Ödeme durumunu güncelle
function markAsPaid(id) {
    ajaxRequest({
        action: 'mark_payment_paid',
        id: id
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
        }
    });
}

// Ödemeyi sil
function deletePayment(id) {
    if (confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_payment',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

// Ödenmemiş ödemeleri sonraki aya aktar
function transferUnpaidPayments() {
    if (!confirm('Ödenmemiş ödemeleri sonraki aya aktarmak istediğinizden emin misiniz?')) {
        return;
    }

    const month = parseInt($('#monthSelect').val()) + 1;
    const year = parseInt($('#yearSelect').val());

    ajaxRequest({
        action: 'transfer_unpaid_payments',
        current_month: month,
        current_year: year
    }).done(function (response) {
        if (response.status === 'success') {
            loadData();
            nextMonth();
        } else {
            alert('Hata: ' + response.message);
        }
    });
} 