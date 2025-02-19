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

    // Ödenmemiş ödemeleri kontrol et
    const hasUnpaidPayments = payments.some(payment => payment.status !== 'paid');

    payments.forEach(function (payment) {
        const isChild = payment.parent_id !== null;
        // const rowClass = isChild ? 'table-light' : '';
        let amountText = `${parseFloat(payment.amount).toFixed(2)} ${payment.currency}`;

        if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
            const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
            amountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
        }

        tbody.append(`
            <tr>
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
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdatePaymentModal(${payment.id})" title="Düzenle">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})" title="Sil">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `);
    });

    // Ödenmemiş ödeme varsa aktarma butonu satırını ekle
    if (hasUnpaidPayments) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-end">
                    <button class="btn btn-warning" onclick="transferUnpaidPayments()">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Sonraki Aya Aktar
                    </button>
                </td>
            </tr>
        `);
    }
}

// Ödeme durumunu güncelle
function markAsPaid(id) {
    ajaxRequest({
        action: 'mark_payment_paid',
        id: id
    }).done(function (response) {
        if (response.status === 'success') {
            // İlgili buton ve ikonu bul
            const button = $(`button[onclick="markAsPaid(${id})"]`);
            const icon = button.find('i');
            const currentStatus = button.hasClass('btn-success');

            // Buton ve ikon sınıflarını güncelle
            if (currentStatus) {
                button.removeClass('btn-success').addClass('btn-outline-success');
                icon.removeClass('bi-check-circle-fill').addClass('bi-check-circle');
                button.attr('title', 'Ödendi olarak işaretle');
            } else {
                button.removeClass('btn-outline-success').addClass('btn-success');
                icon.removeClass('bi-check-circle').addClass('bi-check-circle-fill');
                button.attr('title', 'Ödenmedi olarak işaretle');
            }

            // Eğer bu bir child ödeme ise parent'ın progress bar'ını güncelle
            ajaxRequest({
                action: 'get_payment_details',
                id: id
            }).done(function (detailsResponse) {
                if (detailsResponse.status === 'success' && detailsResponse.data) {
                    const payment = detailsResponse.data;
                    const parentId = payment.parent_id || payment.id;

                    // Sadece ilgili child kayıtları güncelle
                    ajaxRequest({
                        action: 'get_child_payments',
                        parent_id: parentId
                    }).done(function (childResponse) {
                        if (childResponse.status === 'success') {
                            const { parent, children } = childResponse.data;
                            updateChildPayments(parentId, parent, children);

                            // Ödeme gücü tablosundaki ilerleme çubuğunu güncelle
                            const paidCount = [parent, ...children].filter(p => p.status === 'paid').length;
                            const totalCount = children.length + 1;
                            const progress = (paidCount / totalCount) * 100;
                            const progressClass = progress < 25 ? 'bg-danger' :
                                progress < 50 ? 'bg-warning' :
                                    progress < 75 ? 'bg-info' :
                                        'bg-success';

                            const parentRow = $(`.payment-parent[data-payment-id="${parentId}"]`);
                            const progressBar = parentRow.find('.progress-bar');
                            const statusText = parentRow.find('.text-muted');

                            progressBar.removeClass('bg-danger bg-warning bg-info bg-success')
                                .addClass(progressClass)
                                .css('width', progress + '%')
                                .attr('aria-valuenow', progress);

                            statusText.text(`${paidCount}/${totalCount}`);
                        }
                    });
                }
            });
        }
    });
}

// Child ödemeleri güncelle (yeni fonksiyon)
function updateChildPayments(parentId, parent, children) {
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
                    onclick="event.stopPropagation(); markAsPaid(${parent.id}); return false;"
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
                        onclick="event.stopPropagation(); markAsPaid(${payment.id}); return false;"
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

// Ödemeyi sil
function deletePayment(id) {
    Swal.fire({
        icon: 'warning',
        title: 'Bu ödemeyi silmek istediğinize emin misiniz?',
        showCancelButton: true,
        confirmButtonText: 'Evet, sil',
        cancelButtonText: 'İptal',
    }).then((result) => {
        if (result.isConfirmed) {
            ajaxRequest({
                action: 'delete_payment',
                id: id
            }).done(function (response) {
                if (response.status === 'success') {
                    loadData();
                }
            });
        }
    });
}

// Ödenmemiş ödemeleri sonraki aya aktar
function transferUnpaidPayments() {
    Swal.fire({
        icon: 'warning',
        title: 'Ödenmemiş ödemeleri sonraki aya aktarmak istediğinize emin misiniz?',
        showCancelButton: true,
        confirmButtonText: 'Evet, aktar',
        cancelButtonText: 'İptal',
    }).then((result) => {
        if (result.isConfirmed) {
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Hata',
                        text: response.message,
                        timer: 1500
                    });
                }
            });
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
                            onclick="event.stopPropagation(); markAsPaid(${parent.id}); return false;"
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
                                onclick="event.stopPropagation(); markAsPaid(${payment.id}); return false;"
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

// Ödeme güncelleme modalını aç
function openUpdatePaymentModal(id) {
    // Ödeme verilerini yükle
    ajaxRequest({
        action: 'get_data',
        month: $('#monthSelect').val(),
        year: $('#yearSelect').val(),
        load_type: 'payments'
    }).done(function (response) {
        if (response.status === 'success') {
            const payment = response.data.payments.find(p => String(p.id) === String(id));

            if (payment) {
                // Modal alanlarını doldur
                $('#update_payment_id').val(payment.id);
                $('#update_payment_name').val(payment.name);
                $('#update_payment_amount').val(payment.amount);
                $('#update_payment_currency').val(payment.currency);
                $('#update_payment_first_date').val(payment.first_date);
                $('#update_payment_frequency').val(payment.frequency);

                // Frekansa göre bitiş tarihi alanını göster/gizle
                const endDateGroup = document.getElementById('updatePaymentEndDateGroup');
                const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

                if (payment.frequency === 'none') {
                    endDateGroup.style.display = 'none';
                    endDateInput.removeAttribute('required');
                } else {
                    endDateGroup.style.display = 'block';
                    endDateInput.setAttribute('required', 'required');
                }

                // Modalı göster
                const modal = new bootstrap.Modal(document.getElementById('updatePaymentModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: `Ödeme bulunamadı (ID: ${id})`
                });
            }
        }
    });
}

// Ödemeyi güncelle
function updatePayment() {
    // Form verilerini obje olarak al
    const formData = $('#updatePaymentForm').serializeObject();
    formData.action = 'update_payment';

    ajaxRequest(formData).done(function (response) {
        if (response.status === 'success') {
            // Modalı kapat
            const modal = bootstrap.Modal.getInstance(document.getElementById('updatePaymentModal'));
            modal.hide();

            // Tabloyu güncelle
            loadData();

            // Başarı mesajı göster
            Swal.fire({
                icon: 'success',
                title: 'Başarılı',
                text: 'Ödeme başarıyla güncellendi'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Hata',
                text: response.message || 'Ödeme güncellenirken bir hata oluştu'
            });
        }
    });
} 