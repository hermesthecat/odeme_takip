// Gelir listesini güncelle
function updateIncomeList(incomes) {
    const tbody = $('#incomeList');
    tbody.empty();

    if (incomes.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="8" class="text-center">
                    <p class="text-muted">${translations.income.no_data}</p>
                </td>
            </tr>
        `);
        return;
    }

    incomes.forEach(function (income) {
        const isChild = income.parent_id !== null;
        let amountText = `${formatMyMoney(parseFloat(income.amount).toFixed(2))}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (income.currency !== data.user.base_currency && income.exchange_rate) {
            const convertedAmount = parseFloat(income.amount) * parseFloat(income.exchange_rate);
            amountText += `<br><small class="text-muted">(${formatMyMoney(convertedAmount.toFixed(2))} ${data.user.base_currency})</small>`;
        }

        tbody.append(`
            <tr>
                <td style="width: 50px;">
                    <button
                        class="btn btn-sm ${income.status === 'received' ? 'btn-success' : 'btn-outline-success'}"
                        onclick="markAsReceived(${income.id})"
                        title="${income.status === 'received' ? translations.income.mark_received.mark_as_not_received : translations.income.mark_received.mark_as_received}"
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
                <td class="text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateIncomeModal(${income.id})" title="${translations.income.buttons.edit}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteIncome(${income.id})" title="${translations.income.buttons.delete}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
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
        title: translations.income.delete.title,
        showCancelButton: true,
        confirmButtonText: translations.income.delete.confirm,
        cancelButtonText: translations.income.delete.cancel,
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

// Gelir güncelleme modalını aç
function openUpdateIncomeModal(id) {
    // Gelir verilerini yükle
    ajaxRequest({
        action: 'get_data',
        month: $('#monthSelect').val(),
        year: $('#yearSelect').val(),
        load_type: 'income'
    }).done(function (response) {
        if (response.status === 'success') {
            const income = response.data.incomes.find(inc => String(inc.id) === String(id));

            if (income) {
                // Modal alanlarını doldur
                $('#update_income_id').val(income.id);
                $('#update_income_name').val(income.name);
                $('#update_income_amount').val(income.amount);
                $('#update_income_currency').val(income.currency);
                $('#update_income_first_date').val(income.first_date);
                $('#update_income_frequency').val(income.frequency);

                // Parent/Child durumunu kontrol et
                const isParent = income.parent_id === null;
                $('#update_income_is_parent').val(isParent ? '1' : '0');

                // Güncelleme seçeneğini göster/gizle
                const childrenGroup = document.getElementById('updateIncomeChildrenGroup');
                if (income.frequency !== 'none') {
                    childrenGroup.style.display = 'block';

                    // Bilgi metnini güncelle
                    let infoText = '';
                    if (isParent) {
                        infoText = translations.income.update_children_info_parent || 'Bu seçenek işaretlendiğinde, bu gelire bağlı tüm gelirler güncellenecektir.';
                    } else {
                        infoText = translations.income.update_children_info_child || 'Bu seçenek işaretlendiğinde, bu gelir ve sonraki gelirler güncellenecektir.';
                    }
                    $('#update_income_children_info').text(infoText);
                } else {
                    childrenGroup.style.display = 'none';
                }

                // Kur güncelleme seçeneğini göster/gizle
                const exchangeRateGroup = document.getElementById('updateIncomeExchangeRateGroup');
                if (income.currency !== data.user.base_currency) {
                    exchangeRateGroup.style.display = 'block';
                    if (income.exchange_rate) {
                        $('#current_income_exchange_rate').text(`${translations.income.modal.current_rate}: ${income.exchange_rate}`);
                    }
                } else {
                    exchangeRateGroup.style.display = 'none';
                }

                // Frekansa göre bitiş tarihi alanını göster/gizle
                const endDateGroup = document.getElementById('updateIncomeEndDateGroup');
                const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

                if (income.frequency === 'none') {
                    endDateGroup.style.display = 'none';
                    endDateInput.removeAttribute('required');
                } else {
                    endDateGroup.style.display = 'block';
                    endDateInput.setAttribute('required', 'required');

                    // Eğer tekrarlı gelir ise, bitiş tarihini otomatik ayarla
                    if (income.frequency !== 'none') {
                        // API'den son çocuk gelirin tarihini al
                        $.ajax({
                            url: 'api.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'get_last_child_income_date',
                                id: income.id
                            },
                            success: function (response) {
                                if (response.status === 'success' && response.last_date) {
                                    endDateInput.value = response.last_date;
                                }
                            }
                        });
                    }
                }

                // Modalı göster
                const modal = new bootstrap.Modal(document.getElementById('updateIncomeModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: translations.income.modal.error_title,
                    text: translations.income.modal.error_not_found.replace(':id', id)
                });
            }
        }
    });
}

// Geliri güncelle
function updateIncome() {
    // Form verilerini obje olarak al
    const formData = $('#updateIncomeForm').serializeObject();
    formData.action = 'update_income';

    // Kur güncelleme seçeneğini kontrol et
    if ($('#update_income_exchange_rate').is(':checked')) {
        formData.update_exchange_rate = true;
    }

    // Child gelir güncelleme seçeneğini kontrol et
    if ($('#update_income_children').is(':checked')) {
        formData.update_children = true;
    } else {
        formData.update_children = false;
    }

    ajaxRequest(formData).done(function (response) {
        if (response.status === 'success') {
            // Modalı kapat
            const modal = bootstrap.Modal.getInstance(document.getElementById('updateIncomeModal'));
            modal.hide();

            // Tabloyu güncelle
            loadData();

            // Başarı mesajı göster
            Swal.fire({
                icon: 'success',
                title: translations.income.modal.success_title,
                text: translations.income.modal.success_message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: translations.income.modal.error_title,
                text: response.message || translations.income.modal.error_message
            });
        }
    });
}

// Frekans değişikliğinde bitiş tarihi alanını göster/gizle
document.getElementById('update_income_frequency').addEventListener('change', function () {
    const endDateGroup = document.getElementById('updateIncomeEndDateGroup');
    const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

    if (this.value === 'none') {
        endDateGroup.style.display = 'none';
        endDateInput.removeAttribute('required');
    } else {
        endDateGroup.style.display = 'block';
        endDateInput.setAttribute('required', 'required');
    }
}); 