// Ödeme listesi işlemleri
import { loadPayments, savePayments } from '../storage.js';
import { formatDate, getFrequencyText } from '../utils.js';
import { showAddPaymentModal } from '../modals/index.js';
import { calculateNextPaymentDate } from './utils.js';

// Ödeme listesini güncelleme
export function updatePaymentList(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
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

    const startDate = new Date(selectedYear, selectedMonth, 1);
    const endDate = new Date(selectedYear, selectedMonth + 1, 0);
    let hasUnpaidPayments = false;
    let visiblePayments = []; // Görünür ödemeleri takip etmek için array

    payments.forEach((payment, index) => {
        try {
            const firstDate = new Date(payment.firstPaymentDate);
            let shouldShow = false;

            if (payment.frequency === '0') {
                // Tek seferlik ödeme
                shouldShow = firstDate >= startDate && firstDate <= endDate;
            } else {
                // Tekrarlı ödeme
                let currentDate = new Date(firstDate);
                let repeatCounter = 0;

                while (currentDate <= endDate) {
                    if (currentDate >= startDate && currentDate <= endDate) {
                        shouldShow = true;
                        break;
                    }
                    if (payment.repeatCount && repeatCounter >= payment.repeatCount) break;
                    currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
                    repeatCounter++;
                }
            }

            if (shouldShow) {
                // Seçili ayın ödenme durumunu kontrol et
                const monthKey = `${selectedYear}-${String(selectedMonth + 1).padStart(2, '0')}`;
                const isPaid = payment.paidMonths && payment.paidMonths.includes(monthKey);

                visiblePayments.push({ ...payment, index, isPaid }); // Görünür ödemeyi kaydet
                const nextPaymentDate = calculateNextPaymentDate(payment.firstPaymentDate, payment.frequency, payment.repeatCount);
                const frequencyText = getFrequencyText(payment.frequency);
                const repeatText = payment.frequency !== '0' ? 
                                 (payment.repeatCount ? ` (${payment.repeatCount} tekrar)` : ' (Sonsuz)') : '';

                // Mevcut tekrar sayısını hesapla
                let currentRepeat = 0;
                if (payment.frequency !== '0') {
                    const today = new Date();
                    let currentDate = new Date(payment.firstPaymentDate);
                    while (currentDate <= today) {
                        currentRepeat++;
                        currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
                    }
                }

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center">
                            <button class="btn ${isPaid ? 'btn-success' : 'btn-outline-success'} btn-sm me-2" 
                                    data-action="toggle-payment" data-index="${index}" 
                                    title="${isPaid ? 'Ödendi' : 'Ödenmedi'}">
                                <i class="bi bi-check-circle${isPaid ? '-fill' : ''}"></i>
                            </button>
                            <span class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${payment.name || '-'}</span>
                        </div>
                    </td>
                    <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${payment.amount ? payment.amount.toFixed(2) : '0.00'}</td>
                    <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${payment.currency || '-'}</td>
                    <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${formatDate(payment.firstPaymentDate)}</td>
                    <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${frequencyText}${repeatText}</td>
                    <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${formatDate(nextPaymentDate)}</td>
                    <td>
                        <button class="btn btn-primary btn-sm me-1" data-action="update-payment" data-index="${index}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" data-action="delete-payment" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            }
        } catch (error) {
            alert(`Ödeme ${index + 1} gösterilirken hata oluştu: ${error.message}`);
        }
    });

    // Ödenmemiş ödemeler için sonraki aya aktarma butonu ekle
    const unpaidPayments = visiblePayments.filter(payment => !payment.isPaid);

    // Mevcut tfoot'u temizle
    const existingTfoot = document.querySelector('tfoot');
    if (existingTfoot) {
        existingTfoot.remove();
    }

    // Eğer ödenmemiş ödeme varsa uyarı satırını ekle
    if (unpaidPayments.length > 0) {
        const tfoot = document.createElement('tfoot');
        const warningRow = document.createElement('tr');
        warningRow.innerHTML = `
            <td colspan="7" class="text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <span class="me-2 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Bu ayda ${unpaidPayments.length} adet ödenmemiş ödeme bulunuyor
                    </span>
                    <button class="btn btn-warning" data-action="move-to-next-month">
                        <i class="bi bi-arrow-right-circle me-1"></i>
                        Sonraki Aya Aktar
                    </button>
                </div>
            </td>
        `;
        tfoot.appendChild(warningRow);
        tbody.parentNode.appendChild(tfoot);

        // Sonraki aya aktarma butonu için event listener ekle
        warningRow.querySelector('button[data-action="move-to-next-month"]')?.addEventListener('click', () => {
            moveUnpaidToNextMonth(selectedYear, selectedMonth, unpaidPayments);
        });
    }

    // Event listener'ları ekle
    tbody.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', handlePaymentAction);
    });
}

// Ödeme güncelleme
export function updatePayment(index) {
    const payments = loadPayments();
    const payment = payments[index];
    showAddPaymentModal(payment, index);
}

// Ödeme silme
export function deletePayment(index) {
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

// Ödeme durumunu değiştir
function togglePaymentStatus(index) {
    const payments = loadPayments();
    const payment = payments[index];
    
    // paidMonths dizisi yoksa oluştur
    if (!payment.paidMonths) {
        payment.paidMonths = [];
    }
    
    // Seçili ayın string temsilini oluştur (YYYY-MM formatında)
    const currentDate = new Date();
    const monthKey = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`;
    
    // Eğer bu ay ödendiyse ödemedi yap, ödenmediyse ödendi yap
    const monthIndex = payment.paidMonths.indexOf(monthKey);
    if (monthIndex === -1) {
        payment.paidMonths.push(monthKey);
    } else {
        payment.paidMonths.splice(monthIndex, 1);
    }
    
    if (savePayments(payments)) {
        // Başarılı mesajı göster
        const isPaid = monthIndex === -1;
        const message = isPaid ? 'Ödeme yapıldı olarak işaretlendi!' : 'Ödeme yapılmadı olarak işaretlendi!';
        const icon = isPaid ? 'success' : 'info';
        
        Swal.fire({
            icon: icon,
            title: 'Başarılı!',
            text: message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            // Mevcut tfoot'u temizle
            const existingTfoot = document.querySelector('tfoot');
            if (existingTfoot) {
                existingTfoot.remove();
            }
            
            // Ödeme listesini güncelle
            updatePaymentList();
        });
    }
}

// Ödeme işlemlerini yönet
function handlePaymentAction(e) {
    const action = e.target.closest('button').dataset.action;
    const index = parseInt(e.target.closest('button').dataset.index);

    switch (action) {
        case 'update-payment':
            updatePayment(index);
            break;
        case 'delete-payment':
            deletePayment(index);
            break;
        case 'toggle-payment':
            togglePaymentStatus(index);
            break;
    }
}

// Ödenmemiş ödemeleri sonraki aya aktar
function moveUnpaidToNextMonth(currentYear, currentMonth, unpaidPayments) {
    const payments = loadPayments();
    let nextMonth = currentMonth + 1;
    let nextYear = currentYear;
    
    if (nextMonth > 11) {
        nextMonth = 0;
        nextYear++;
    }

    if (unpaidPayments.length === 0) return;

    // Onay mesajı göster
    Swal.fire({
        title: 'Ödemeleri Aktar',
        html: `
            <p>${unpaidPayments.length} adet ödenmemiş ödemeyi sonraki aya aktarmak istediğinizden emin misiniz?</p>
            <p class="text-muted">Aktarılacak ay: ${new Date(nextYear, nextMonth).toLocaleString('tr-TR', { month: 'long', year: 'numeric' })}</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Evet, Aktar',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Her ödenmemiş ödeme için yeni bir kopya oluştur
            unpaidPayments.forEach(payment => {
                const newPayment = {
                    name: `${payment.name} (${new Date(currentYear, currentMonth).toLocaleString('tr-TR', { month: 'long' })} Aktarımı)`,
                    amount: payment.amount,
                    currency: payment.currency,
                    category: payment.category,
                    firstPaymentDate: new Date(nextYear, nextMonth, 1).toISOString().split('T')[0],
                    frequency: '0', // Tek seferlik ödeme olarak ayarla
                    repeatCount: null,
                    paidMonths: [] // Boş paidMonths dizisi ile başlat
                };
                
                payments.push(newPayment);
            });

            if (savePayments(payments)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: `${unpaidPayments.length} adet ödeme sonraki aya aktarıldı.`,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Seçili ayı güncelle ve listeyi yenile
                    const monthSelect = document.getElementById('monthSelect');
                    const yearSelect = document.getElementById('yearSelect');
                    
                    if (monthSelect && yearSelect) {
                        monthSelect.value = nextMonth;
                        yearSelect.value = nextYear;
                        
                        // Değişikliği tetikle
                        monthSelect.dispatchEvent(new Event('change'));
                    }
                });
            }
        }
    });
} 