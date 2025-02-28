// Ödemeler listesini güncelle
function updatePaymentsList(payments) {
    const tbody = $('#paymentList');
    tbody.empty();

    if (payments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">${translations.payment.no_data}</p>
                </td>
            </tr>
        `);
        return;
    }

    // Ödenmemiş ödemeleri kontrol et
    const hasUnpaidPayments = payments.some(payment => payment.status !== 'paid');

    payments.forEach(function (payment) {
        const isChild = payment.parent_id !== null;
        let amountText = `${formatMyMoney(parseFloat(payment.amount).toFixed(2))}`;

        if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
            const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
            amountText += `<br><small class="text-muted">(${formatMyMoney(convertedAmount.toFixed(2))} ${data.user.base_currency})</small>`;
        }

        tbody.append(`
            <tr>
                <td style="width: 50px;">
                    <button
                        class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                        onclick="markAsPaid(${payment.id})"
                        title="${payment.status === 'paid' ? translations.payment.mark_paid.mark_as_paid : translations.payment.mark_paid.mark_as_not_paid}"
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
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdatePaymentModal(${payment.id})" title="${translations.payment.buttons.edit}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})" title="${translations.payment.buttons.delete}">
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
                        ${translations.payment.buttons.transfer}
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
                button.attr('title', translations.payment.mark_paid.mark_as_paid);
            } else {
                button.removeClass('btn-outline-success').addClass('btn-success');
                icon.removeClass('bi-check-circle').addClass('bi-check-circle-fill');
                button.attr('title', translations.payment.mark_paid.mark_as_not_paid);
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

                            // Tekrarlayan ödemeleri yeniden yükle ve toplam tutarları güncelle
                            ajaxRequest({
                                action: 'get_data',
                                month: $('#monthSelect').val(),
                                year: $('#yearSelect').val(),
                                load_type: 'recurring_payments'
                            }).done(function (response) {
                                if (response.status === 'success' && response.data && response.data.recurring_payments) {
                                    try {
                                        let totalYearlyPayment = 0;
                                        let totalUnpaidPayment = 0;

                                        response.data.recurring_payments.forEach(payment => {
                                            if (payment && payment.yearly_total) {
                                                totalYearlyPayment += parseFloat(payment.yearly_total) || 0;
                                                totalUnpaidPayment += parseFloat(payment.unpaid_total) || 0;
                                            }
                                        });

                                        // Tablo altbilgisini güncelle
                                        const tfoot = $('#recurringPaymentsList').closest('table').find('tfoot');
                                        tfoot.html(`
                                            <tr class="text-end">
                                                <td colspan="5" class="text-end fw-bold">${translations.payment.recurring.total_payment}:</td>
                                                <td class="fw-bold">${formatMyMoney(totalYearlyPayment.toFixed(2))} ${data.user.base_currency}</td>
                                            </tr>
                                            <tr class="text-end">
                                                <td colspan="5" class="text-end fw-bold">${translations.payment.recurring.pending_payment}:</td>
                                                <td class="fw-bold">${formatMyMoney(totalUnpaidPayment.toFixed(2))} ${data.user.base_currency}</td>
                                            </tr>
                                        `);

                                    } catch (error) {
                                        console.error('Toplam tutarlar güncellenirken hata:', error);
                                    }
                                } else {
                                    console.error('Recurring payments verisi alınamadı:', response);
                                }
                            }).fail(function (jqXHR, textStatus, errorThrown) {
                                console.error('Ajax isteği başarısız:', textStatus, errorThrown);
                            });
                        }
                    });
                }
            });
        }
    });
}

// Child ödemeleri güncelle
function updateChildPayments(parentId, parent, children) {
    const tbody = $(`.child-payments[data-parent-id="${parentId}"]`);
    tbody.closest('.table-responsive').addClass('w-100');
    tbody.empty();

    // Ana kaydı ekle
    let parentAmountText = `${parseFloat(parent.amount).toFixed(2)} ${parent.currency}`;
    if (parent.currency !== data.user.base_currency && parent.exchange_rate) {
        const convertedAmount = parseFloat(parent.amount) * parseFloat(parent.exchange_rate);
        parentAmountText += ` (${convertedAmount.toFixed(2)} ${data.user.base_currency})`;
    }

    tbody.append(`
        <tr style="cursor: default;">
            <td>
                <button
                    class="btn btn-sm ${parent.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                    onclick="event.stopPropagation(); markAsPaid(${parent.id}); return false;"
                    title="${parent.status === 'paid' ? translations.payment.mark_paid.mark_as_not_paid : translations.payment.mark_paid.mark_as_paid}"
                >
                    <i class="bi ${parent.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                </button>
            </td>
            <td>${parent.first_date}</td>
            <td>${parentAmountText}</td>
            <td>${parent.currency}</td>
            <td></td>
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
                        title="${payment.status === 'paid' ? translations.payment.mark_paid.mark_as_not_paid : translations.payment.mark_paid.mark_as_paid}"
                    >
                        <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                    </button>
                </td>
                <td>${payment.first_date}</td>
                <td>${amountText}</td>
                <td>${payment.currency}</td>
                <td></td>
            </tr>
        `);
    });
}

// Ödemeyi sil
function deletePayment(id) {
    Swal.fire({
        icon: 'warning',
        title: translations.payment.delete.title,
        showCancelButton: true,
        confirmButtonText: translations.payment.delete.confirm,
        cancelButtonText: translations.payment.delete.cancel
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
        title: translations.transfer.title,
        text: translations.transfer.confirm,
        showCancelButton: true,
        confirmButtonText: translations.transfer.transfer_button,
        cancelButtonText: translations.transfer.cancel_button,
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
                        title: translations.error,
                        text: response.message || translations.transfer.error,
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

    if (recurring_payments.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="6" class="text-center">
                    <p class="text-muted">Hiç taksitli ödeme eklenmemiş</p>
                </td>
            </tr>
        `);
        // table-info gizle
        $('#recurringPaymentsList').closest('table').find('tfoot').empty();
        return;
    }

    recurring_payments.forEach(function (payment) {
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
                <td>${formatMyMoney(parseFloat(payment.amount).toFixed(2))}</td>
                <td>${payment.currency}</td>
                <td>
                    <div class="progress mb-1">
                        <div class="progress-bar ${progressClass}" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted text-center d-block">${payment.payment_status}</small>
                </td>
                <td>
                    ${formatMyMoney(parseFloat(payment.yearly_total).toFixed(2))}
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-danger ms-2" onclick="event.stopPropagation(); deleteRecurringPayment(${payment.id})" title="${translations.payment.buttons.delete}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            <tr class="payment-children d-none" data-parent-id="${payment.id}">
                <td colspan="6" class="p-0">
                    <div class="child-payments-container bg-opacity-10 p-3">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>${translations.payment.date}</th>
                                        <th>${translations.payment.amount}</th>
                                        <th>${translations.payment.currency}</th>
                                        <th></th>
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

    let totalYearlyPayment = 0;
    let totalUnpaidPayment = 0;

    recurring_payments.forEach(function (payment) {
        totalYearlyPayment += parseFloat(payment.yearly_total) || 0;
        totalUnpaidPayment += parseFloat(payment.unpaid_total) || 0;
    });

    // Tablo altbilgisini güncelle
    const tfoot = $('#recurringPaymentsList').closest('table').find('tfoot');
    tfoot.html(`
        <tr class="text-end">
            <td colspan="5" class="text-end fw-bold">${translations.payment.recurring.total_payment}:</td>
            <td class="fw-bold">${formatMyMoney(totalYearlyPayment.toFixed(2))} ${data.user.base_currency}</td>
        </tr>
        <tr class="text-end">
            <td colspan="5" class="text-end fw-bold">${translations.payment.recurring.pending_payment}:</td>
            <td class="fw-bold">${formatMyMoney(totalUnpaidPayment.toFixed(2))} ${data.user.base_currency}</td>
        </tr>
    `);

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
            let parentAmountText = `${formatMyMoney(parseFloat(parent.amount).toFixed(2))}`;
            if (parent.currency !== data.user.base_currency && parent.exchange_rate) {
                const convertedAmount = parseFloat(parent.amount) * parseFloat(parent.exchange_rate);
                parentAmountText += `<br><small class="text-muted">(${formatMyMoney(convertedAmount.toFixed(2))} ${data.user.base_currency})</small>`;
            }

            tbody.append(`
                <tr style="cursor: default;">
                    <td>
                        <button
                            class="btn btn-sm ${parent.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                            onclick="event.stopPropagation(); markAsPaid(${parent.id}); return false;"
                            title="${parent.status === 'paid' ? translations.payment.mark_paid.mark_as_not_paid : translations.payment.mark_paid.mark_as_paid}"
                        >
                            <i class="bi ${parent.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                        </button>
                    </td>
                    <td>${parent.first_date}</td>
                    <td>${parentAmountText}</td>
                    <td>${parent.currency}</td>
                    <td></td>
                </tr>
            `);

            // Child kayıtları ekle
            children.forEach(function (payment) {
                let amountText = `${formatMyMoney(parseFloat(payment.amount).toFixed(2))}`;
                if (payment.currency !== data.user.base_currency && payment.exchange_rate) {
                    const convertedAmount = parseFloat(payment.amount) * parseFloat(payment.exchange_rate);
                    amountText += `<br><small class="text-muted">(${formatMyMoney(convertedAmount.toFixed(2))} ${data.user.base_currency})</small>`;
                }

                tbody.append(`
                    <tr style="cursor: default;">
                        <td>
                            <button
                                class="btn btn-sm ${payment.status === 'paid' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="event.stopPropagation(); markAsPaid(${payment.id}); return false;"
                                title="${payment.status === 'paid' ? translations.payment.mark_paid.mark_as_not_paid : translations.payment.mark_paid.mark_as_paid}"
                            >
                                <i class="bi ${payment.status === 'paid' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${payment.first_date}</td>
                        <td>${amountText}</td>
                        <td>${payment.currency}</td>
                        <td></td>
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

                // Kur güncelleme seçeneğini göster/gizle
                const exchangeRateGroup = document.getElementById('updatePaymentExchangeRateGroup');
                if (payment.currency !== data.user.base_currency) {
                    exchangeRateGroup.style.display = 'block';
                    if (payment.exchange_rate) {
                        $('#current_exchange_rate').text(`${translations.payment.modal.current_rate}: ${payment.exchange_rate}`);
                    }
                } else {
                    exchangeRateGroup.style.display = 'none';
                }

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

                // Eğer tekrarlı ödeme ise, bitiş tarihini otomatik ayarla
                if (payment.frequency !== 'none') {
                    // API'den son çocuk ödemenin tarihini al
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'get_last_child_payment_date',
                            id: payment.id
                        },
                        success: function (response) {
                            if (response.status === 'success' && response.last_date) {
                                endDateInput.value = response.last_date;
                            }
                        }
                    });
                }

                // Modalı göster
                const modal = new bootstrap.Modal(document.getElementById('updatePaymentModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: translations.payment.modal.error_title,
                    text: translations.payment.modal.error_not_found
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

    // Kur güncelleme seçeneğini kontrol et
    if ($('#update_exchange_rate').is(':checked')) {
        formData.update_exchange_rate = true;
    }

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
                title: translations.payment.modal.success_title,
                text: translations.payment.modal.success_message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: translations.payment.modal.error_title,
                text: response.message || translations.payment.modal.error_message
            });
        }
    });
}

// Tekrarlayan ödemeyi sil
function deleteRecurringPayment(parentId) {
    Swal.fire({
        icon: 'warning',
        title: translations.payment.delete.title,
        text: translations.payment.delete.confirm,
        showCancelButton: true,
        confirmButtonText: translations.payment.delete.confirm,
        cancelButtonText: translations.payment.delete.cancel,
    }).then((result) => {
        if (result.isConfirmed) {
            ajaxRequest({
                action: 'delete_payment',
                id: parentId,
                delete_children: true
            }).done(function (response) {
                if (response.status === 'success') {
                    loadData();
                    Swal.fire({
                        icon: 'success',
                        title: translations.app.delete_success,
                        text: translations.payment.delete_success,
                        timer: 1500
                    });
                }
            });
        }
    });
} 