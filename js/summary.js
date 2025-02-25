// Özet bilgileri güncelle
function updateSummary(data) {
    if (!data.summary) {
        console.error('Özet verisi bulunamadı');
        return;
    }

    const totalIncome = parseFloat(data.summary.total_income);
    const totalExpense = parseFloat(data.summary.total_expense);
    const balance = totalIncome - totalExpense;

    // Özet bilgileri güncelle
    $('#monthlyIncome').text(formatMyMoney(totalIncome.toFixed(2)));
    $('#monthlyExpense').text(formatMyMoney(totalExpense.toFixed(2)));
    $('#monthlyBalance').text(formatMyMoney(balance.toFixed(2)));
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