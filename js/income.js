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
        let amountText = `${parseFloat(income.amount).toFixed(2)}`;

        // Eğer baz para biriminden farklıysa ve kur bilgisi varsa dönüştürülmüş tutarı ekle
        if (income.currency !== data.user.base_currency && income.exchange_rate) {
            const convertedAmount = parseFloat(income.amount) * parseFloat(income.exchange_rate);
            amountText += `<br><small class="text-muted">(${convertedAmount.toFixed(2)} ${data.user.base_currency})</small>`;
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
                    <div class="btn-group">
                        <button class="btn btn-sm btn-primary" onclick="openUpdateIncomeModal(${income.id})" title="Düzenle">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteIncome(${income.id})" title="Sil">
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

                // Frekansa göre bitiş tarihi alanını göster/gizle
                const endDateGroup = document.getElementById('updateIncomeEndDateGroup');
                const endDateInput = endDateGroup.querySelector('input[name="end_date"]');

                if (income.frequency === 'none') {
                    endDateGroup.style.display = 'none';
                    endDateInput.removeAttribute('required');
                } else {
                    endDateGroup.style.display = 'block';
                    endDateInput.setAttribute('required', 'required');
                }

                // Modalı göster
                const modal = new bootstrap.Modal(document.getElementById('updateIncomeModal'));
                modal.show();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata',
                    text: `Gelir bulunamadı (ID: ${id})`
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
                title: 'Başarılı',
                text: 'Gelir başarıyla güncellendi'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Hata',
                text: response.message || 'Gelir güncellenirken bir hata oluştu'
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