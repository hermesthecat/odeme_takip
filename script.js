/**
 * @author A. Kerem Gök
 */

// LocalStorage anahtarları
const STORAGE_KEY = 'payments';
const INCOME_STORAGE_KEY = 'incomes';

// Eski verileri kontrol et ve taşı
function migrateOldData() {
    try {
        const oldData = localStorage.getItem('payment_tracking_data');
        if (oldData) {
            localStorage.setItem(STORAGE_KEY, oldData);
            localStorage.removeItem('payment_tracking_data');
            console.log('Eski veriler taşındı');
        }
    } catch (error) {
        console.error('Veri taşıma hatası:', error);
    }
}

// LocalStorage'dan ödemeleri yükleme
function loadPayments() {
    try {
        migrateOldData(); // Eski verileri kontrol et
        const payments = localStorage.getItem(STORAGE_KEY);
        console.log('Yüklenen ödemeler:', payments);
        return payments ? JSON.parse(payments) : [];
    } catch (error) {
        console.error('Ödemeler yüklenirken hata:', error);
        return [];
    }
}

// LocalStorage'a ödemeleri kaydetme
function savePayments(payments) {
    try {
        const data = JSON.stringify(payments);
        localStorage.setItem(STORAGE_KEY, data);
        console.log('Kaydedilen ödemeler:', payments);
        return true;
    } catch (error) {
        console.error('Ödemeler kaydedilirken hata:', error);
        return false;
    }
}

// Gelirleri yükleme
function loadIncomes() {
    try {
        const incomes = localStorage.getItem(INCOME_STORAGE_KEY);
        console.log('Yüklenen gelirler:', incomes);
        return incomes ? JSON.parse(incomes) : [];
    } catch (error) {
        console.error('Gelirler yüklenirken hata:', error);
        return [];
    }
}

// Gelirleri kaydetme
function saveIncomes(incomes) {
    try {
        const data = JSON.stringify(incomes);
        localStorage.setItem(INCOME_STORAGE_KEY, data);
        console.log('Kaydedilen gelirler:', incomes);
        return true;
    } catch (error) {
        console.error('Gelirler kaydedilirken hata:', error);
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
    console.log('updatePaymentList fonksiyonu çağrıldı');
    
    const tbody = document.getElementById('paymentList');
    if (!tbody) {
        console.error('paymentList elementi bulunamadı!');
        return;
    }

    const payments = loadPayments();
    console.log('Yüklenen ödeme sayısı:', payments.length);

    tbody.innerHTML = '';
    
    if (!Array.isArray(payments) || payments.length === 0) {
        console.log('Ödeme listesi boş');
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
            console.error(`Ödeme ${index + 1} gösterilirken hata:`, error, payment);
        }
    });
}

// Gelir listesini güncelleme
function updateIncomeList() {
    console.log('updateIncomeList fonksiyonu çağrıldı');
    
    const tbody = document.getElementById('incomeList');
    if (!tbody) {
        console.error('incomeList elementi bulunamadı!');
        return;
    }

    const incomes = loadIncomes();
    console.log('Yüklenen gelir sayısı:', incomes.length);

    tbody.innerHTML = '';
    
    if (!Array.isArray(incomes) || incomes.length === 0) {
        console.log('Gelir listesi boş');
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
            console.error(`Gelir ${index + 1} gösterilirken hata:`, error, income);
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
    if (confirm('Bu ödemeyi silmek istediğinizden emin misiniz?')) {
        const payments = loadPayments();
        payments.splice(index, 1);
        if (savePayments(payments)) {
            updatePaymentList();
        }
    }
}

// Gelir silme
function deleteIncome(index) {
    if (confirm('Bu geliri silmek istediğinizden emin misiniz?')) {
        const incomes = loadIncomes();
        incomes.splice(index, 1);
        if (saveIncomes(incomes)) {
            updateIncomeList();
            updateCalendar();
        }
    }
}

// Form işlemleri
if (document.getElementById('paymentForm')) {
    const form = document.getElementById('paymentForm');
    
    // Form gönderildiğinde
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        try {
            // Form verilerini al
            const payment = {
                name: document.getElementById('paymentName').value.trim(),
                amount: parseFloat(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                firstPaymentDate: document.getElementById('firstPaymentDate').value,
                frequency: document.getElementById('frequency').value
            };
            
            // Veri kontrolü
            if (!payment.name || isNaN(payment.amount) || !payment.firstPaymentDate) {
                alert('Lütfen tüm alanları doğru şekilde doldurunuz.');
                return;
            }
            
            // Mevcut ödemeleri yükle ve yeni ödemeyi ekle
            const payments = loadPayments();
            payments.push(payment);
            
            // Kaydet ve yönlendir
            if (savePayments(payments)) {
                alert('Ödeme başarıyla kaydedildi!');
                window.location.href = 'index.html'; // URL düzeltildi
            } else {
                alert('Ödeme kaydedilirken bir hata oluştu!');
            }
        } catch (error) {
            console.error('Kayıt sırasında hata:', error);
            alert('Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.');
        }
    });
}

// Gelir formu işlemleri
if (document.getElementById('incomeForm')) {
    const form = document.getElementById('incomeForm');
    
    form.addEventListener('submit', function(e) {
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
                alert('Lütfen tüm alanları doğru şekilde doldurunuz.');
                return;
            }
            
            const incomes = loadIncomes();
            incomes.push(income);
            
            if (saveIncomes(incomes)) {
                alert('Gelir başarıyla kaydedildi!');
                window.location.href = 'index.html';
            } else {
                alert('Gelir kaydedilirken bir hata oluştu!');
            }
        } catch (error) {
            console.error('Kayıt sırasında hata:', error);
            alert('Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.');
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
                title: `Ödeme: ${payment.name} - ${payment.amount} ${payment.currency}`,
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
                title: `Gelir: ${income.name} - ${income.amount} ${income.currency}`,
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
        // Takvimi ilk kez oluştur
        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'tr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek'
            },
            events: events,
            eventClick: function(info) {
                alert(`Ödeme Detayları:
                    \nÖdeme: ${info.event.title}
                    \nTarih: ${formatDate(info.event.start)}
                    \nTutar: ${info.event.extendedProps.amount} ${info.event.extendedProps.currency}
                    \nTekrar: ${getFrequencyText(info.event.extendedProps.frequency)}`);
            }
        });
        calendar.render();
        calendarEl.fullCalendar = calendar;
    } else {
        // Mevcut takvimi güncelle
        calendarEl.fullCalendar.removeAllEvents();
        calendarEl.fullCalendar.addEventSource(events);
    }
}

// Ana sayfa yüklendiğinde
if (document.getElementById('paymentList')) {
    // Sayfa yüklendiğinde listeyi ve takvimi güncelle
    window.addEventListener('load', function() {
        console.log('Ana sayfa yüklendi');
        migrateOldData();
        updatePaymentList();
        updateIncomeList();
        updateCalendar();
    });
    
    // Test butonu için event listener
    const testButton = document.getElementById('testButton');
    if (testButton) {
        testButton.addEventListener('click', function() {
            const payments = localStorage.getItem(STORAGE_KEY);
            console.log('LocalStorage içeriği:', payments);
            if (payments) {
                console.log('Ayrıştırılmış veriler:', JSON.parse(payments));
            }
            updatePaymentList();
        });
    }
} 