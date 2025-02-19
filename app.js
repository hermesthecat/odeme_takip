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
    }).done(function(response) {
        if (response.status === 'success') {
            updateIncomeList(response.data.incomes);
            updateSavingsList(response.data.savings);
            updatePaymentsList(response.data.payments);
            updateSummary(response.data);
        }
    });
}

// Gelir listesini güncelle
function updateIncomeList(incomes) {
    const tbody = $('#incomeList');
    tbody.empty();

    incomes.forEach(function(income) {
        tbody.append(`
            <tr>
                <td>${income.name}</td>
                <td>${income.amount} ${income.currency}</td>
                <td>${income.currency}</td>
                <td>${income.first_date}</td>
                <td>${income.frequency}</td>
                <td>${income.next_date}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deleteIncome(${income.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

// Birikimler listesini güncelle
function updateSavingsList(savings) {
    const tbody = $('#savingList');
    tbody.empty();

    savings.forEach(function(saving) {
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

    payments.forEach(function(payment) {
        tbody.append(`
            <tr>
                <td>${payment.name}</td>
                <td>${payment.amount} ${payment.currency}</td>
                <td>${payment.currency}</td>
                <td>${payment.first_date}</td>
                <td>${payment.frequency}</td>
                <td>${payment.next_date}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
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
        }).done(function(response) {
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
        }).done(function(response) {
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
        }).done(function(response) {
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
$(document).ready(function() {
    // Mevcut ay ve yılı seç
    const now = new Date();
    $('#monthSelect').val(now.getMonth());
    $('#yearSelect').val(now.getFullYear());

    // Verileri yükle
    loadData();

    // Form submit işlemleri
    $('[data-action]').click(function() {
        const type = $(this).data('type');
        const modal = new bootstrap.Modal(document.getElementById(`${type}Modal`));
        modal.show();
    });

    // Form submit işlemleri
    $('.modal form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const action = 'add_' + form.data('type');

        ajaxRequest({
            action: action,
            ...form.serializeObject()
        }).done(function(response) {
            if (response.status === 'success') {
                form.closest('.modal').modal('hide');
                form[0].reset();
                loadData();
            }
        });
    });
}); 