// Özet bilgileri güncelle
function updateSummary(data) {
    let totalIncome = 0;
    let totalExpense = 0;

    // Gelirleri topla
    data.incomes.forEach(income => {
        let amount = parseFloat(income.amount);
        if (income.currency !== 'TRY' && income.exchange_rate) {
            amount *= parseFloat(income.exchange_rate);
        }
        totalIncome += amount;
    });

    // Ödemeleri topla
    data.payments.forEach(payment => {
        let amount = parseFloat(payment.amount);
        if (payment.currency !== 'TRY' && payment.exchange_rate) {
            amount *= parseFloat(payment.exchange_rate);
        }
        totalExpense += amount;
    });

    const balance = totalIncome - totalExpense;

    // Özet bilgileri güncelle
    $('#monthlyIncome').text(totalIncome.toFixed(2) + ' TL');
    $('#monthlyExpense').text(totalExpense.toFixed(2) + ' TL');
    $('#monthlyBalance').text(balance.toFixed(2) + ' TL');
    $('#currentPeriod').text($('#monthSelect option:selected').text() + ' ' + $('#yearSelect').val());
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
    updateUrl(month, year);
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
    updateUrl(month, year);
    loadData();
} 