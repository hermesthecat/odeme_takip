/**
 * @author A. Kerem Gök
 */

// LocalStorage anahtarı
const STORAGE_KEY = 'payments';

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

// Takvim olaylarını oluştur
function createCalendarEvents(payments) {
    const events = [];
    const today = new Date();
    const sixMonthsLater = new Date();
    sixMonthsLater.setMonth(today.getMonth() + 6);

    payments.forEach(payment => {
        let currentDate = new Date(payment.firstPaymentDate);
        
        // Gelecek 6 aylık ödemeleri hesapla
        while (currentDate <= sixMonthsLater) {
            events.push({
                title: `${payment.name} - ${payment.amount} ${payment.currency}`,
                start: currentDate.toISOString().split('T')[0],
                backgroundColor: getPaymentColor(payment.currency),
                borderColor: getPaymentColor(payment.currency),
                extendedProps: {
                    amount: payment.amount,
                    currency: payment.currency,
                    frequency: payment.frequency
                }
            });

            // Bir sonraki ödeme tarihini hesapla
            if (payment.frequency === '0') break; // Tek seferlik ödeme
            
            const nextDate = new Date(currentDate);
            nextDate.setMonth(nextDate.getMonth() + parseInt(payment.frequency));
            currentDate = nextDate;
        }
    });

    return events;
}

// Para birimine göre renk döndür
function getPaymentColor(currency) {
    return '#dc3545'; // Bootstrap kırmızı renk kodu
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