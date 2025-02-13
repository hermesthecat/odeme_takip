/**
 * @author A. Kerem Gök
 */

// LocalStorage'dan ödemeleri yükleme
function loadPayments() {
    try {
        const payments = localStorage.getItem('payments');
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
        localStorage.setItem('payments', JSON.stringify(payments));
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
    
    if (frequency === 0) return firstDate;
    
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
    const payments = loadPayments();
    const tbody = document.getElementById('paymentList');
    console.log('Liste güncellenirken mevcut ödemeler:', payments);
    
    if (!tbody) {
        console.error('paymentList elementi bulunamadı');
        return;
    }

    tbody.innerHTML = '';
    
    if (payments.length === 0) {
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
            console.error(`Ödeme gösterilirken hata (index: ${index}):`, error);
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
        savePayments(payments);
        updatePaymentList();
    }
}

// Form gönderildiğinde
if (document.getElementById('paymentForm')) {
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        try {
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
            
            const payments = loadPayments();
            payments.push(payment);
            
            if (savePayments(payments)) {
                alert('Ödeme başarıyla kaydedildi!');
                window.location.href = 'index.html';
            } else {
                alert('Ödeme kaydedilirken bir hata oluştu!');
            }
        } catch (error) {
            console.error('Kayıt sırasında hata oluştu:', error);
            alert('Kayıt sırasında bir hata oluştu. Lütfen tekrar deneyiniz.');
        }
    });
}

// Sayfa yüklendiğinde ödeme listesini güncelle
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sayfa yüklendi, liste güncelleniyor...');
    updatePaymentList();
}); 