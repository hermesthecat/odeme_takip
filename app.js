// Form verilerini objeye çeviren yardımcı fonksiyon
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// AJAX istekleri için yardımcı fonksiyon
function ajaxRequest(data) {
    return $.ajax({
        url: 'api.php',
        type: 'POST',
        data: data,
        dataType: 'json'
    });
}

// Verileri yükle
function loadData() {
    const month = $('#monthSelect').val();
    const year = $('#yearSelect').val();

    ajaxRequest({
        action: 'get_data',
        month: month,
        year: year
    }).done(function (response) {
        if (response.status === 'success') {
            // Debug bilgisini konsola yazdır
            if (response.debug) {
                console.log('Debug Bilgisi:', response.debug);
            }

            updateIncomeList(response.data.incomes);
            updateSavingsList(response.data.savings);
            updatePaymentsList(response.data.payments);
            updateSummary(response.data);
        } else {
            console.error('Veri yükleme hatası:', response.message);
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX hatası:', textStatus, errorThrown);
    });
}

// Tekrarlama sıklığı çevirisi
function getFrequencyText(frequency) {
    const frequencies = {
        'none': 'Tekrar Yok',
        'monthly': 'Aylık',
        'bimonthly': '2 Ayda Bir',
        'quarterly': '3 Ayda Bir',
        'fourmonthly': '4 Ayda Bir',
        'fivemonthly': '5 Ayda Bir',
        'sixmonthly': '6 Ayda Bir',
        'yearly': 'Yıllık'
    };
    return frequencies[frequency] || frequency;
}

// Gelir listesini güncelle
function updateIncomeList(incomes) {
    const tbody = $('#incomeList');
    tbody.empty();

    incomes.forEach(function (income) {
        const isChild = income.parent_id !== null;
        const rowClass = isChild ? 'table-light' : '';

        tbody.append(`
                    <tr class="${rowClass}">
                        <td style="width: 50px;">
                            <button
                                class="btn btn-sm ${income.status === 'received' ? 'btn-success' : 'btn-outline-success'}"
                                onclick="markAsReceived(${income.id})"
                                title="${income.status === 'received' ? 'Alındı' : 'Bekliyor'}"
                            >
                                <i class="bi ${income.status === 'received' ? 'bi-check-circle-fill' : 'bi-check-circle'}"></i>
                            </button>
                        </td>
                        <td>${income.name}</td>
                        <td>${income.amount} ${income.currency}</td>
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

// Birikimler listesini güncelle
function updateSavingsList(savings) {
    const tbody = $('#savingList');
    tbody.empty();

    savings.forEach(function (saving) {
        const progress = (saving.current_amount / saving.target_amount) * 100;
        tbody.append(`
            <tr>
                <td>${saving.name}</td>
                <td>${saving.target_amount} ${saving.currency}</td>
                <td>${saving.current_amount} ${saving.currency}</td>
                <td>${saving.currency}</td>
                <td>${saving.start_date}</td>
                <td>${saving.target_date}</td>
                <td>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: ${progress}%" 
                             aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                            ${progress.toFixed(1)}%
                        </div>
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteSaving(${saving.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

// Ödemeler listesini güncelle
function updatePaymentsList(payments) {
    const tbody = $('#paymentList');
    tbody.empty();

    payments.forEach(function (payment) {
        const isChild = payment.parent_id !== null;
        const rowClass = isChild ? 'table-light' : '';

        tbody.append(`
            <tr class="${rowClass}">
                <td>${payment.name}</td>
                <td>${payment.amount} ${payment.currency}</td>
                <td>${payment.currency}</td>
                <td>${payment.first_date}</td>
                <td>${getFrequencyText(payment.frequency)}</td>
                <td>${payment.next_payment_date || ''}</td>
                <td>
                    ${isChild ? `
                        <button class="btn btn-sm btn-success" onclick="markAsPaid(${payment.id})">
                            ${payment.status === 'paid' ? '<span class="badge bg-success">Ödendi</span>' : '<span class="badge bg-warning">Bekliyor</span>'}
                        </button>
                    ` : `
                        <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    `}
                </td>
            </tr>
        `);
    });
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

// Özet bilgileri güncelle
function updateSummary(data) {
    let totalIncome = 0;
    let totalExpense = 0;

    data.incomes.forEach(income => {
        if (income.currency === 'TRY') {
            totalIncome += parseFloat(income.amount);
        }
    });

    data.payments.forEach(payment => {
        if (payment.currency === 'TRY') {
            totalExpense += parseFloat(payment.amount);
        }
    });

    const balance = totalIncome - totalExpense;

    $('#monthlyIncome').text(totalIncome.toFixed(2) + ' TL');
    $('#monthlyExpense').text(totalExpense.toFixed(2) + ' TL');
    $('#monthlyBalance').text(balance.toFixed(2) + ' TL');
    $('#currentPeriod').text($('#monthSelect option:selected').text() + ' ' + $('#yearSelect').val());
}

// Silme işlemleri
function deleteIncome(id) {
    if (confirm('Bu geliri silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_income',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

function deleteSaving(id) {
    if (confirm('Bu birikimi silmek istediğinizden emin misiniz?')) {
        ajaxRequest({
            action: 'delete_saving',
            id: id
        }).done(function (response) {
            if (response.status === 'success') {
                loadData();
            }
        });
    }
}

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

// Ay/yıl değişikliği
function previousMonth() {
    let month = parseInt($('#monthSelect').val());
    let year = parseInt($('#yearSelect').val());

    if (month === 0) {
        month = 11;
        year--;
    } else {
        month--;
    }

    $('#monthSelect').val(month);
    $('#yearSelect').val(year);
    loadData();
}

function nextMonth() {
    let month = parseInt($('#monthSelect').val());
    let year = parseInt($('#yearSelect').val());

    if (month === 11) {
        month = 0;
        year++;
    } else {
        month++;
    }

    $('#monthSelect').val(month);
    $('#yearSelect').val(year);
    loadData();
}

// Sayfa yüklendiğinde
$(document).ready(function () {
    // Mevcut ay ve yılı seç
    const now = new Date();
    $('#monthSelect').val(now.getMonth());
    $('#yearSelect').val(now.getFullYear());

    // Verileri yükle
    loadData();

    // Form submit işlemleri
    $('[data-action]').click(function () {
        const type = $(this).data('type');
        const modal = new bootstrap.Modal(document.getElementById(`${type}Modal`));
        modal.show();
    });

    // Form submit işlemleri
    $('.modal form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const formData = form.serializeObject();
        const action = 'add_' + form.data('type');

        // next_date değerini kaldır çünkü API'de hesaplanacak
        if (formData.next_date) {
            delete formData.next_date;
        }

        ajaxRequest({
            action: action,
            ...formData
        }).done(function (response) {
            if (response.status === 'success') {
                const modalElement = form.closest('.modal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                form[0].reset();
                loadData();
            }
        });
    });
}); 