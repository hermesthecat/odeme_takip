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

// Tekrarlayan ödemeleri güncelle
function updateRecurringPaymentsList(recurring_payments) {
    const tbody = $('#recurringPaymentsList');
    tbody.empty();

    let totalYearlyPayment = 0;

    recurring_payments.forEach(function (payment) {
        if (payment.currency === 'TRY') {
            totalYearlyPayment += parseFloat(payment.yearly_total);
        }

        // Taksit bilgisini parçala (örn: "2/5" -> [2, 5])
        const [paid, total] = payment.payment_status.split('/').map(Number);
        const progress = (paid / total) * 100;
        const progressClass = progress < 25 ? 'bg-danger' :
            progress < 50 ? 'bg-warning' :
                progress < 75 ? 'bg-info' :
                    'bg-success';

        tbody.append(`
            <tr class="payment-parent" data-payment-id="${payment.id}" style="cursor: pointer;">
                <td>
                    <i class="bi bi-chevron-right me-2 toggle-icon"></i>
                    ${payment.name}
                </td>
                <td>${parseFloat(payment.amount).toFixed(2)}</td>
                <td>${payment.currency}</td>
                <td>
                    <div class="progress mb-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted text-center d-block">${payment.payment_status}</small>
                </td>
                <td>${parseFloat(payment.yearly_total).toFixed(2)}</td>
            </tr>
            <tr class="payment-children d-none" data-parent-id="${payment.id}">
                <td colspan="5" class="p-0">
                    <div class="child-payments-container bg-opacity-10 p-3">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Durum</th>
                                        <th>Ödeme Tarihi</th>
                                        <th>Tutar</th>
                                        <th>Kur</th>
                                    </tr>
                                </thead>
                                <tbody class="child-payments" data-parent-id="${payment.id}">
                                    <!-- Child ödemeler buraya eklenecek -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Child ödemeleri yükle
        loadChildPayments(payment.id);
    });

    $('#totalYearlyPayment').text(totalYearlyPayment.toFixed(2));

    // Ana kayıtlara tıklama olayı ekle
    $('.payment-parent').on('click', function () {
        const paymentId = $(this).data('payment-id');
        const childrenRow = $(`.payment-children[data-parent-id="${paymentId}"]`);
        const toggleIcon = $(this).find('.toggle-icon');

        childrenRow.toggleClass('d-none');
        toggleIcon.toggleClass('bi-chevron-right bi-chevron-down');

        if (!childrenRow.hasClass('d-none')) {
            loadChildPayments(paymentId);
        }
    });
}

// Child ödemeleri yükle
function loadChildPayments(parentId) {
    ajaxRequest({
        action: 'get_child_payments',
        parent_id: parentId
    }).done(function (response) {
        if (response.status === 'success') {
            const { parent, children } = response.data;
            const tbody = $(`.child-payments[data-parent-id="${parentId}"]`);
            tbody.empty();

            // Ana kaydı ekle
            let parentAmountText = `${parseFloat(parent.amount).toFixed(2)} ${parent.currency}`;
            if (parent.currency !== data.user.base_currency && parent.exchange_rate) {
                const convertedAmount = parseFloat(parent.amount) * parseFloat(parent.exchange_rate);
                parentAmountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
            }

            tbody.append(`
                <tr class="table-primary" style="cursor: default;">
                    <td>
                        <button
                            class="btn btn-sm ${parent.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                            onclick="markAsPaid(${parent.id}); return false;"
                            title="${parent.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                        >
                            <i class="bi ${parent.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                        </button>
                    </td>
                    <td>${parent.first_date}</td>
                    <td>${parentAmountText}</td>
                    <td>${parent.currency}</td>
                </tr>
            `);

            // Child kayıtları ekle
            children.forEach(function (payment) {
                let amountText = `${parseFloat(payment.amount).toFixed(2)} ${payment.currency}`;
                if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
                    const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
                    amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
                }

                tbody.append(`
                    <tr style="cursor: default;">
                        <td>
                            <button
                                class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAsPaid(${payment.id}); return false;"
                                title="${payment.status === 'paid' ? 'Ödenmedi olarak işaretle' : 'Ödendi olarak işaretle'}"
                            >
                                <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${payment.first_date}</td>
                        <td>${amountText}</td>
                        <td>${payment.currency}</td>
                    </tr>
                `);
            });
        }
    });
} 