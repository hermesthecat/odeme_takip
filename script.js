/**
 * @author A. Kerem Gök
 */

// Döviz kuru API URL'i
const TCMB_API_URL = 'https://api.exchangerate.host/live?source=TRY&access_key=4f467070688418cb9958422c637c880c';

// LocalStorage anahtarları
const STORAGE_KEY = 'payments';
const INCOME_STORAGE_KEY = 'incomes';
const SAVING_STORAGE_KEY = 'savings';
const EXCHANGE_RATES_KEY = 'exchangeRates';
const LAST_UPDATE_KEY = 'lastExchangeUpdate';

// Grafik değişkenleri
let incomeExpenseChart = null;
let savingsChart = null;

// LocalStorage'dan ödemeleri yükleme
function loadPayments() {
    try {
        const payments = localStorage.getItem(STORAGE_KEY);
        return payments ? JSON.parse(payments) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödemeler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// LocalStorage'a ödemeleri kaydetme
function savePayments(payments) {
    try {
        const data = JSON.stringify(payments);
        localStorage.setItem(STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödemeler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Gelirleri yükleme
function loadIncomes() {
    try {
        const incomes = localStorage.getItem(INCOME_STORAGE_KEY);
        return incomes ? JSON.parse(incomes) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelirler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Gelirleri kaydetme
function saveIncomes(incomes) {
    try {
        const data = JSON.stringify(incomes);
        localStorage.setItem(INCOME_STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelirler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Birikimleri yükleme
function loadSavings() {
    try {
        const savings = localStorage.getItem(SAVING_STORAGE_KEY);
        return savings ? JSON.parse(savings) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Birikimleri kaydetme
function saveSavings(savings) {
    try {
        const data = JSON.stringify(savings);
        localStorage.setItem(SAVING_STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Sonraki ödeme tarihini hesaplama
function calculateNextPaymentDate(firstPaymentDate, frequency) {
    const firstDate = new Date(firstPaymentDate);
    const today = new Date();

    if (frequency === '0') return firstDate;

    let nextDate = new Date(firstDate);
    while (nextDate <= today) {
        nextDate.setMonth(nextDate.getMonth() + parseInt(frequency));
    }

    return nextDate;
}

// Tarihi formatla
function formatDate(date) {
    return new Date(date).toLocaleDateString('tr-TR');
}

// Ödeme listesini güncelleme
function updatePaymentList() {
    const tbody = document.getElementById('paymentList');
    if (!tbody) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödeme listesi tablosu bulunamadı!'
        });
        return;
    }

    const payments = loadPayments();
    tbody.innerHTML = '';

    if (!Array.isArray(payments) || payments.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center">Henüz ödeme kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    payments.forEach((payment, index) => {
        try {
            const nextPaymentDate = calculateNextPaymentDate(payment.firstPaymentDate, payment.frequency);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${payment.name || '-'}</td>
                <td>${payment.amount ? payment.amount.toFixed(2) : '0.00'}</td>
                <td>${payment.currency || '-'}</td>
                <td>${formatDate(payment.firstPaymentDate)}</td>
                <td>${getFrequencyText(payment.frequency)}</td>
                <td>${formatDate(nextPaymentDate)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deletePayment(${index})">Sil</button>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            alert(`Ödeme ${index + 1} gösterilirken hata oluştu: ${error.message}`);
        }
    });
}

// Gelir listesini güncelleme
function updateIncomeList() {
    const tbody = document.getElementById('incomeList');
    if (!tbody) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelir listesi tablosu bulunamadı!'
        });
        return;
    }

    const incomes = loadIncomes();
    tbody.innerHTML = '';

    if (!Array.isArray(incomes) || incomes.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center">Henüz gelir kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    incomes.forEach((income, index) => {
        try {
            const nextIncomeDate = calculateNextPaymentDate(income.firstIncomeDate, income.frequency);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${income.name || '-'}</td>
                <td>${income.amount ? income.amount.toFixed(2) : '0.00'}</td>
                <td>${income.currency || '-'}</td>
                <td>${formatDate(income.firstIncomeDate)}</td>
                <td>${getFrequencyText(income.frequency)}</td>
                <td>${formatDate(nextIncomeDate)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="deleteIncome(${index})">Sil</button>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            alert(`Gelir ${index + 1} gösterilirken hata oluştu: ${error.message}`);
        }
    });
}

// Tekrarlama sıklığı metnini alma
function getFrequencyText(frequency) {
    const frequencies = {
        '0': 'Tekrar Yok',
        '1': 'Her Ay',
        '2': '2 Ayda Bir',
        '3': '3 Ayda Bir',
        '4': '4 Ayda Bir',
        '5': '5 Ayda Bir',
        '6': '6 Ayda Bir',
        '12': 'Yıllık'
    };
    return frequencies[frequency] || '';
}

// Ödeme silme
function deletePayment(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu ödemeyi silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const payments = loadPayments();
            payments.splice(index, 1);
            if (savePayments(payments)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Ödeme başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    });
}

// Gelir silme
function deleteIncome(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu geliri silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const incomes = loadIncomes();
            incomes.splice(index, 1);
            if (saveIncomes(incomes)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Gelir başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    });
}

// Form işlemleri
if (document.getElementById('paymentForm')) {
    const form = document.getElementById('paymentForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        try {
            const payment = {
                name: document.getElementById('paymentName').value.trim(),
                amount: parseFloat(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                firstPaymentDate: document.getElementById('firstPaymentDate').value,
                frequency: document.getElementById('frequency').value
            };

            if (!payment.name || isNaN(payment.amount) || !payment.firstPaymentDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Lütfen tüm alanları doğru şekilde doldurunuz.'
                });
                return;
            }

            const payments = loadPayments();
            payments.push(payment);

            if (savePayments(payments)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Ödeme başarıyla kaydedildi!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'index.html';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Ödeme kaydedilirken bir hata oluştu!'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.'
            });
        }
    });
}

// Gelir formu işlemleri
if (document.getElementById('incomeForm')) {
    const form = document.getElementById('incomeForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        try {
            const income = {
                name: document.getElementById('incomeName').value.trim(),
                amount: parseFloat(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                firstIncomeDate: document.getElementById('firstIncomeDate').value,
                frequency: document.getElementById('frequency').value
            };

            if (!income.name || isNaN(income.amount) || !income.firstIncomeDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Lütfen tüm alanları doğru şekilde doldurunuz.'
                });
                return;
            }

            const incomes = loadIncomes();
            incomes.push(income);

            if (saveIncomes(incomes)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Gelir başarıyla kaydedildi!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'index.html';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Gelir kaydedilirken bir hata oluştu!'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.'
            });
        }
    });
}

// Birikim formu işlemleri
if (document.getElementById('savingForm')) {
    const form = document.getElementById('savingForm');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        try {
            const saving = {
                name: document.getElementById('savingName').value.trim(),
                targetAmount: parseFloat(document.getElementById('targetAmount').value),
                currentAmount: parseFloat(document.getElementById('currentAmount').value),
                currency: document.getElementById('currency').value,
                startDate: document.getElementById('startDate').value,
                targetDate: document.getElementById('targetDate').value
            };

            if (!saving.name || isNaN(saving.targetAmount) || isNaN(saving.currentAmount) ||
                !saving.startDate || !saving.targetDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Lütfen tüm alanları doğru şekilde doldurunuz.'
                });
                return;
            }

            if (saving.currentAmount > saving.targetAmount) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Mevcut tutar hedef tutardan büyük olamaz!'
                });
                return;
            }

            const startDateObj = new Date(saving.startDate);
            const targetDateObj = new Date(saving.targetDate);
            if (targetDateObj <= startDateObj) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Hedef tarihi başlangıç tarihinden sonra olmalıdır!'
                });
                return;
            }

            const savings = loadSavings();
            savings.push(saving);

            if (saveSavings(savings)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Birikim başarıyla kaydedildi!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'index.html';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Birikim kaydedilirken bir hata oluştu!'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.'
            });
        }
    });
}

// Takvim olaylarını oluştur
function createCalendarEvents(payments) {
    const events = [];
    const today = new Date();
    const sixMonthsLater = new Date();
    sixMonthsLater.setMonth(today.getMonth() + 6);

    // Ödemeleri ekle
    payments.forEach(payment => {
        let currentDate = new Date(payment.firstPaymentDate);

        while (currentDate <= sixMonthsLater) {
            events.push({
                title: `${payment.name} - ${payment.amount} ${payment.currency}`,
                start: currentDate.toISOString().split('T')[0],
                backgroundColor: '#dc3545', // Kırmızı
                borderColor: '#dc3545',
                extendedProps: {
                    type: 'payment',
                    amount: payment.amount,
                    currency: payment.currency,
                    frequency: payment.frequency
                }
            });

            if (payment.frequency === '0') break;

            const nextDate = new Date(currentDate);
            nextDate.setMonth(nextDate.getMonth() + parseInt(payment.frequency));
            currentDate = nextDate;
        }
    });

    // Gelirleri ekle
    const incomes = loadIncomes();
    incomes.forEach(income => {
        let currentDate = new Date(income.firstIncomeDate);

        while (currentDate <= sixMonthsLater) {
            events.push({
                title: `${income.name} - ${income.amount} ${income.currency}`,
                start: currentDate.toISOString().split('T')[0],
                backgroundColor: '#198754', // Yeşil
                borderColor: '#198754',
                extendedProps: {
                    type: 'income',
                    amount: income.amount,
                    currency: income.currency,
                    frequency: income.frequency
                }
            });

            if (income.frequency === '0') break;

            const nextDate = new Date(currentDate);
            nextDate.setMonth(nextDate.getMonth() + parseInt(income.frequency));
            currentDate = nextDate;
        }
    });

    return events;
}

// Takvimi güncelle
function updateCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const payments = loadPayments();
    const events = createCalendarEvents(payments);

    if (!calendarEl.fullCalendar) {
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'tr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            events: events,
            eventClick: function (info) {
                Swal.fire({
                    title: info.event.extendedProps.type === 'payment' ? 'Ödeme Detayları' : 'Gelir Detayları',
                    html: `
                        <div class="text-start">
                            <p><strong>İsim:</strong> ${info.event.title}</p>
                            <p><strong>Tarih:</strong> ${formatDate(info.event.start)}</p>
                            <p><strong>Tutar:</strong> ${info.event.extendedProps.amount} ${info.event.extendedProps.currency}</p>
                            <p><strong>Tekrar:</strong> ${getFrequencyText(info.event.extendedProps.frequency)}</p>
                        </div>
                    `,
                    icon: info.event.extendedProps.type === 'payment' ? 'error' : 'success'
                });
            }
        });
        calendar.render();
        calendarEl.fullCalendar = calendar;
    } else {
        calendarEl.fullCalendar.removeAllEvents();
        calendarEl.fullCalendar.addEventSource(events);
    }
}

// Döviz kurlarını güncelle
async function updateExchangeRates() {
    try {
        const response = await fetch(TCMB_API_URL);
        const data = await response.json();

        if (data && data.rates) {
            const exchangeRates = {
                'TRY': 1,
                'USD': 1 / data.rates.TRYUSD,
                'EUR': 1 / data.rates.TRYEUR,
                'GBP': 1 / data.rates.TRYGBP
            };

            localStorage.setItem(EXCHANGE_RATES_KEY, JSON.stringify(exchangeRates));
            localStorage.setItem(LAST_UPDATE_KEY, new Date().toISOString());

            return exchangeRates;
        }
    } catch (error) {
        // Hata durumunda son kaydedilen kurları kullan
        const savedRates = localStorage.getItem(EXCHANGE_RATES_KEY);
        return savedRates ? JSON.parse(savedRates) : EXCHANGE_RATES;
    }
}

// Döviz kurlarını göster
function showExchangeRates() {
    const ratesContainer = document.getElementById('exchangeRates');
    if (!ratesContainer) return;

    // Son güncelleme zamanını kontrol et
    const lastUpdate = localStorage.getItem(LAST_UPDATE_KEY);
    const lastUpdateDate = lastUpdate ? new Date(lastUpdate) : null;
    const now = new Date();

    // 24 saatten eski ise uyarı göster
    const isOld = lastUpdateDate && (now - lastUpdateDate) > 24 * 60 * 60 * 1000;

    // Kurları al
    const rates = localStorage.getItem(EXCHANGE_RATES_KEY);
    const exchangeRates = rates ? JSON.parse(rates) : EXCHANGE_RATES;

    const html = `
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-dollar me-1"></i>
                            <span class="fw-bold">USD:</span>
                            <span class="ms-1">${exchangeRates.USD.toFixed(2)}₺</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-euro me-1"></i>
                            <span class="fw-bold">EUR:</span>
                            <span class="ms-1">${exchangeRates.EUR.toFixed(2)}₺</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-pound me-1"></i>
                            <span class="fw-bold">GBP:</span>
                            <span class="ms-1">${exchangeRates.GBP.toFixed(2)}₺</span>
                        </div>
                    </div>
                    <small class="text-muted ${isOld ? 'text-danger' : ''}">
                        <i class="bi bi-clock-history me-1"></i>
                        Son güncelleme: ${lastUpdateDate ? lastUpdateDate.toLocaleString('tr-TR') : 'Bilinmiyor'}
                        ${isOld ? ' (Güncel değil!)' : ''}
                    </small>
                </div>
            </div>
        </div>
    `;

    ratesContainer.innerHTML = html;
}

// Para birimlerinin TL karşılıkları (sabit kur için)
const EXCHANGE_RATES = {
    'TRY': 1,
    'USD': 30.50,
    'EUR': 33.20,
    'GBP': 38.70
};

// Tutarı TL'ye çevir
function convertToTRY(amount, currency) {
    return amount * EXCHANGE_RATES[currency];
}

// Tutarı formatla
function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
}

// Belirli bir ay için gelir ve giderleri hesapla
function calculateMonthlyBalance(year, month) {
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);

    let totalIncome = 0;
    let totalExpense = 0;

    // Gelirleri hesapla
    const incomes = loadIncomes();
    incomes.forEach(income => {
        const firstDate = new Date(income.firstIncomeDate);

        if (income.frequency === '0') {
            // Tek seferlik gelir
            if (firstDate.getFullYear() === year && firstDate.getMonth() === month) {
                totalIncome += convertToTRY(income.amount, income.currency);
            }
        } else {
            // Tekrarlı gelir
            let currentDate = new Date(firstDate);

            // İlk tarihi ayın başına getir
            while (currentDate > startDate) {
                currentDate.setMonth(currentDate.getMonth() - parseInt(income.frequency));
            }

            // Sonraki ödeme tarihini bul
            while (currentDate <= startDate) {
                currentDate.setMonth(currentDate.getMonth() + parseInt(income.frequency));
            }

            // Eğer bu ay içindeyse ekle
            if (currentDate <= endDate) {
                totalIncome += convertToTRY(income.amount, income.currency);
            }
        }
    });

    // Giderleri hesapla
    const payments = loadPayments();
    payments.forEach(payment => {
        const firstDate = new Date(payment.firstPaymentDate);

        if (payment.frequency === '0') {
            // Tek seferlik ödeme
            if (firstDate.getFullYear() === year && firstDate.getMonth() === month) {
                totalExpense += convertToTRY(payment.amount, payment.currency);
            }
        } else {
            // Tekrarlı ödeme
            let currentDate = new Date(firstDate);

            // İlk tarihi ayın başına getir
            while (currentDate > startDate) {
                currentDate.setMonth(currentDate.getMonth() - parseInt(payment.frequency));
            }

            // Sonraki ödeme tarihini bul
            while (currentDate <= startDate) {
                currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
            }

            // Eğer bu ay içindeyse ekle
            if (currentDate <= endDate) {
                totalExpense += convertToTRY(payment.amount, payment.currency);
            }
        }
    });

    return {
        income: totalIncome,
        expense: totalExpense,
        balance: totalIncome - totalExpense
    };
}

// Özet kartlarını güncelle
function updateSummaryCards() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();

    const balance = calculateMonthlyBalance(year, month);

    // Gelir kartını güncelle
    const incomeElement = document.getElementById('monthlyIncome');
    if (incomeElement) {
        incomeElement.textContent = formatMoney(balance.income);
        incomeElement.classList.toggle('text-success', balance.income > 0);
    }

    // Gider kartını güncelle
    const expenseElement = document.getElementById('monthlyExpense');
    if (expenseElement) {
        expenseElement.textContent = formatMoney(balance.expense);
        expenseElement.classList.toggle('text-danger', balance.expense > 0);
    }

    // Net durum kartını güncelle
    const balanceElement = document.getElementById('monthlyBalance');
    if (balanceElement) {
        balanceElement.textContent = formatMoney(balance.balance);
        balanceElement.classList.toggle('text-success', balance.balance > 0);
        balanceElement.classList.toggle('text-danger', balance.balance < 0);
    }

    // Dönem kartını güncelle
    const periodElement = document.getElementById('currentPeriod');
    if (periodElement) {
        periodElement.textContent = new Intl.DateTimeFormat('tr-TR', {
            year: 'numeric',
            month: 'long'
        }).format(now);
    }
}

// İlerleme yüzdesini hesapla
function calculateProgress(targetAmount, currentAmount) {
    return Math.min(100, Math.round((currentAmount / targetAmount) * 100));
}

// Birikim listesini güncelleme
function updateSavingList() {
    const tbody = document.getElementById('savingList');
    if (!tbody) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikim listesi tablosu bulunamadı!'
        });
        return;
    }

    const savings = loadSavings();
    tbody.innerHTML = '';

    if (!Array.isArray(savings) || savings.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="8" class="text-center">Henüz birikim kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    savings.forEach((saving, index) => {
        try {
            const progress = calculateProgress(saving.targetAmount, saving.currentAmount);
            const progressClass = progress >= 100 ? 'bg-success' : 'bg-primary';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${saving.name || '-'}</td>
                <td>${saving.targetAmount ? saving.targetAmount.toFixed(2) : '0.00'}</td>
                <td>${saving.currentAmount ? saving.currentAmount.toFixed(2) : '0.00'}</td>
                <td>${saving.currency || '-'}</td>
                <td>${formatDate(saving.startDate)}</td>
                <td>${formatDate(saving.targetDate)}</td>
                <td>
                    <div class="progress">
                        <div class="progress-bar ${progressClass}" role="progressbar" 
                             style="width: ${progress}%" 
                             aria-valuenow="${progress}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${progress}%
                        </div>
                    </div>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm me-1" onclick="updateSaving(${index})">Güncelle</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteSaving(${index})">Sil</button>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: `Birikim ${index + 1} gösterilirken hata oluştu: ${error.message}`
            });
        }
    });
}

// Birikim güncelleme
function updateSaving(index) {
    const savings = loadSavings();
    const saving = savings[index];

    Swal.fire({
        title: 'Birikim Güncelle',
        html: `
            <div class="mb-3">
                <label class="form-label">Biriken Tutar</label>
                <input type="number" id="currentAmount" class="form-control" value="${saving.currentAmount}" step="0.01">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Güncelle',
        cancelButtonText: 'İptal',
        preConfirm: () => {
            const currentAmount = parseFloat(document.getElementById('currentAmount').value);
            if (isNaN(currentAmount)) {
                Swal.showValidationMessage('Lütfen geçerli bir tutar giriniz');
                return false;
            }
            return currentAmount;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            savings[index].currentAmount = result.value;
            if (saveSavings(savings)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Birikim başarıyla güncellendi!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    });
}

// Birikim silme
function deleteSaving(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu birikimi silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const savings = loadSavings();
            savings.splice(index, 1);
            if (saveSavings(savings)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Birikim başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    });
}

// Veri export/import fonksiyonları
function exportData() {
    try {
        const data = {
            payments: loadPayments(),
            incomes: loadIncomes(),
            savings: loadSavings(),
            exchangeRates: localStorage.getItem(EXCHANGE_RATES_KEY),
            lastUpdate: localStorage.getItem(LAST_UPDATE_KEY),
            exportDate: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        const date = new Date().toLocaleDateString('tr-TR').replace(/\./g, '-');
        
        a.href = url;
        a.download = `butce-verilerim-${date}.json`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        Swal.fire({
            icon: 'success',
            title: 'Başarılı!',
            text: 'Verileriniz başarıyla dışa aktarıldı.',
            showConfirmButton: false,
            timer: 1500
        });
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Veriler dışa aktarılırken bir hata oluştu: ' + error.message
        });
    }
}

function importData() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';

    input.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = JSON.parse(e.target.result);
                
                // Veri doğrulama
                if (!data.payments || !data.incomes || !data.savings) {
                    throw new Error('Geçersiz veri formatı');
                }

                // Verileri kaydet
                savePayments(data.payments);
                saveIncomes(data.incomes);
                saveSavings(data.savings);
                
                if (data.exchangeRates) {
                    localStorage.setItem(EXCHANGE_RATES_KEY, data.exchangeRates);
                }
                if (data.lastUpdate) {
                    localStorage.setItem(LAST_UPDATE_KEY, data.lastUpdate);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Verileriniz başarıyla içe aktarıldı.',
                    showConfirmButton: true,
                    confirmButtonText: 'Tamam'
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Veriler içe aktarılırken bir hata oluştu: ' + error.message
                });
            }
        };
        reader.readAsText(file);
    };

    input.click();
}

// Grafikleri güncelle
function updateCharts(period = 'month') {
    updateIncomeExpenseChart(period);
    updateSavingsChart();
}

// Gelir-Gider grafiğini güncelle
function updateIncomeExpenseChart(period) {
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) return;

    let labels, incomeData, expenseData;

    if (period === 'month') {
        // Son 6 ayın verilerini al
        const months = [];
        const incomes = [];
        const expenses = [];

        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            const monthYear = new Intl.DateTimeFormat('tr-TR', { month: 'long', year: 'numeric' }).format(date);
            months.push(monthYear);

            const balance = calculateMonthlyBalance(date.getFullYear(), date.getMonth());
            incomes.push(balance.income);
            expenses.push(balance.expense);
        }

        labels = months;
        incomeData = incomes;
        expenseData = expenses;
    } else {
        // Yıllık verileri al (son 3 yıl)
        const years = [];
        const incomes = [];
        const expenses = [];

        for (let i = 2; i >= 0; i--) {
            const year = new Date().getFullYear() - i;
            years.push(year.toString());

            let yearlyIncome = 0;
            let yearlyExpense = 0;

            for (let month = 0; month < 12; month++) {
                const balance = calculateMonthlyBalance(year, month);
                yearlyIncome += balance.income;
                yearlyExpense += balance.expense;
            }

            incomes.push(yearlyIncome);
            expenses.push(yearlyExpense);
        }

        labels = years;
        incomeData = incomes;
        expenseData = expenses;
    }

    // Eğer grafik zaten varsa güncelle, yoksa oluştur
    if (incomeExpenseChart) {
        incomeExpenseChart.data.labels = labels;
        incomeExpenseChart.data.datasets[0].data = incomeData;
        incomeExpenseChart.data.datasets[1].data = expenseData;
        incomeExpenseChart.update();
    } else {
        incomeExpenseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Gelir',
                        data: incomeData,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1
                    },
                    {
                        label: 'Gider',
                        data: expenseData,
                        backgroundColor: 'rgba(220, 53, 69, 0.5)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                    new Intl.NumberFormat('tr-TR', {
                                        style: 'currency',
                                        currency: 'TRY'
                                    }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Birikim grafiğini güncelle
function updateSavingsChart() {
    const ctx = document.getElementById('savingsChart');
    if (!ctx) return;

    const savings = loadSavings();
    const labels = [];
    const currentData = [];
    const targetData = [];

    savings.forEach(saving => {
        labels.push(saving.name);
        currentData.push(convertToTRY(saving.currentAmount, saving.currency));
        targetData.push(convertToTRY(saving.targetAmount, saving.currency));
    });

    // Eğer grafik zaten varsa güncelle, yoksa oluştur
    if (savingsChart) {
        savingsChart.data.labels = labels;
        savingsChart.data.datasets[0].data = currentData;
        savingsChart.data.datasets[1].data = targetData;
        savingsChart.update();
    } else {
        savingsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Mevcut Tutar',
                        data: currentData,
                        backgroundColor: 'rgba(13, 110, 253, 0.5)',
                        borderColor: 'rgb(13, 110, 253)',
                        borderWidth: 1
                    },
                    {
                        label: 'Hedef Tutar',
                        data: targetData,
                        backgroundColor: 'rgba(108, 117, 125, 0.5)',
                        borderColor: 'rgb(108, 117, 125)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', {
                                    style: 'currency',
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                    new Intl.NumberFormat('tr-TR', {
                                        style: 'currency',
                                        currency: 'TRY'
                                    }).format(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Ana sayfa yüklendiğinde
if (document.getElementById('paymentList')) {
    console.log('Ana sayfa yükleniyor...');
    window.addEventListener('load', async function () {
        console.log('Load event tetiklendi');
        // Döviz kurlarını güncelle ve göster
        await updateExchangeRates();
        showExchangeRates();

        // Diğer güncellemeler
        updatePaymentList();
        updateIncomeList();
        updateSavingList();
        updateCalendar();
        updateSummaryCards();
        updateCharts(); // Grafikleri güncelle

        // Her saat başı kurları güncelle
        setInterval(async () => {
            await updateExchangeRates();
            showExchangeRates();
            updateSummaryCards();
        }, 60 * 60 * 1000); // 1 saat
    });
} 