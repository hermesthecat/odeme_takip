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

                // Tekrar bilgisi metni
                const repeatCountText = payment.repeatCount && currentRepeat > 0 ? 
                                      ` <span class="badge bg-info">${currentRepeat}/${payment.repeatCount}</span>` : '';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${payment.name || '-'}${repeatCountText}</td>
                    <td>${payment.amount ? payment.amount.toFixed(2) : '0.00'}</td>
                    <td>${payment.currency || '-'}</td>
                    <td>${formatDate(payment.firstPaymentDate)}</td>
                    <td>${frequencyText}${repeatText}</td>
                    <td>${formatDate(nextPaymentDate)}</td>
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
    }
} 