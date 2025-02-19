// Ödeme listesi işlemleri
import { loadPayments, savePayments } from '../storage.js';
import { formatDate, getFrequencyText } from '../utils.js';
import { showAddPaymentModal } from '../modals/index.js';
import { calculateNextPaymentDate } from './utils.js';
import { updateBudgetGoalsDisplay } from './lists_budget.js';
import { updateSummaryCards } from '../calculations.js';
import { updateCharts } from '../charts.js';

// Ödeme listesini güncelleme
export async function updatePaymentList(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    console.log('Ödeme listesi güncelleniyor...', { selectedYear, selectedMonth });
    const tbody = document.getElementById('paymentList');
    if (!tbody) {
        console.error('Ödeme listesi tablosu bulunamadı!');
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödeme listesi tablosu bulunamadı!'
        });
        return;
    }

    try {
        const payments = await loadPayments();
        console.log('Yüklenen ödemeler:', payments);
        tbody.innerHTML = '';

        if (!Array.isArray(payments) || payments.length === 0) {
            console.log('Ödeme bulunamadı veya dizi değil:', payments);
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="7" class="text-center">Henüz ödeme kaydı bulunmamaktadır.</td>';
            tbody.appendChild(row);
            return;
        }

        const startDate = new Date(selectedYear, selectedMonth, 1);
        const endDate = new Date(selectedYear, selectedMonth + 1, 0);
        console.log('Tarih aralığı:', { başlangıç: startDate, bitiş: endDate });
        let hasUnpaidPayments = false;
        let visiblePayments = [];

        payments.forEach((payment, index) => {
            try {
                console.log(`Ödeme #${index + 1} işleniyor:`, payment);
                const firstPaymentDate = payment.first_payment_date;
                const firstDate = firstPaymentDate ? new Date(firstPaymentDate) : null;

                if (!firstDate) {
                    console.error(`Ödeme #${index + 1} için geçersiz tarih:`, firstPaymentDate);
                    return;
                }

                let shouldShow = false;
                console.log(`Ödeme #${index + 1} tarih kontrolü:`, {
                    ilkÖdemeTarihi: firstDate,
                    seçiliYıl: selectedYear,
                    seçiliAy: selectedMonth,
                    sıklık: payment.frequency
                });

                if (payment.frequency === 0 || payment.frequency === '0') {
                    // Tek seferlik ödeme - sadece ay ve yıl kontrolü yap
                    const paymentYear = firstDate.getFullYear();
                    const paymentMonth = firstDate.getMonth();
                    shouldShow = paymentYear === selectedYear && paymentMonth === selectedMonth;
                    console.log(`Tek seferlik ödeme #${index + 1} gösterilmeli mi:`, {
                        ödemeTarihi: { yıl: paymentYear, ay: paymentMonth },
                        seçiliTarih: { yıl: selectedYear, ay: selectedMonth },
                        göster: shouldShow
                    });
                } else {
                    // Tekrarlı ödeme
                    let currentDate = new Date(firstDate);
                    let repeatCounter = 0;

                    while (currentDate <= endDate) {
                        if (currentDate >= startDate && currentDate <= endDate) {
                            shouldShow = true;
                            console.log(`Tekrarlı ödeme #${index + 1} bu ay gösterilecek:`, {
                                şuAnkiTarih: currentDate,
                                tekrarSayısı: repeatCounter
                            });
                            break;
                        }
                        if (payment.repeat_count && repeatCounter >= payment.repeat_count) {
                            console.log(`Tekrarlı ödeme #${index + 1} tekrar limitine ulaştı:`, {
                                limit: payment.repeat_count,
                                şuAnkiTekrar: repeatCounter
                            });
                            break;
                        }
                        currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
                        repeatCounter++;
                    }
                }

                if (shouldShow) {
                    console.log(`Ödeme #${index + 1} tabloya ekleniyor`);
                    // Seçili ayın ödenme durumunu kontrol et
                    const monthKey = `${selectedYear}-${String(selectedMonth + 1).padStart(2, '0')}`;
                    const isPaid = payment.paid_months && payment.paid_months.includes(monthKey);

                    visiblePayments.push({ ...payment, index, isPaid });
                    const nextPaymentDate = calculateNextPaymentDate(payment.first_payment_date, payment.frequency, payment.repeat_count);
                    const frequencyText = getFrequencyText(payment.frequency);
                    const repeatText = payment.frequency !== '0' ?
                        (payment.repeat_count ? ` (${payment.repeat_count} tekrar)` : ' (Sonsuz)') : '';

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
                        <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${payment.amount ? parseFloat(payment.amount).toFixed(2) : '0.00'}</td>
                        <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${payment.currency || '-'}</td>
                        <td class="${isPaid ? 'text-decoration-line-through text-muted' : ''}">${formatDate(payment.first_payment_date)}</td>
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
                } else {
                    console.log(`Ödeme #${index + 1} bu ay gösterilmeyecek`);
                }
            } catch (error) {
                console.error(`Ödeme ${index + 1} işlenirken hata:`, error);
            }
        });

        // Ödenmemiş ödemeler için sonraki aya aktarma butonu ekle
        const unpaidPayments = visiblePayments.filter(payment => !payment.isPaid);

        // Mevcut tfoot'u temizle
        const paymentTable = tbody.closest('table');
        const existingTfoot = paymentTable.querySelector('tfoot');
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
    } catch (error) {
        console.error('Ödeme listesi güncellenirken genel hata:', error);
    }
}

// Ödeme güncelleme
export function updatePayment(index) {
    const payments = loadPayments();
    const payment = payments[index];
    showAddPaymentModal(payment, index).then(() => {
        // Seçili ay ve yılı al
        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');

        if (monthSelect && yearSelect) {
            const selectedMonth = parseInt(monthSelect.value);
            const selectedYear = parseInt(yearSelect.value);

            // Tüm listeleri güncelle
            updatePaymentList(selectedYear, selectedMonth);

            // Diğer ilgili güncellemeleri de yap
            const event = new Event('change');
            monthSelect.dispatchEvent(event);
        }
    });
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
    if (!payment.paid_months) {
        payment.paid_months = [];
    }

    // Seçili ayın string temsilini oluştur (YYYY-MM formatında)
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const selectedMonth = parseInt(monthSelect.value);
    const selectedYear = parseInt(yearSelect.value);
    const monthKey = `${selectedYear}-${String(selectedMonth + 1).padStart(2, '0')}`;

    // Eğer bu ay ödendiyse ödemedi yap, ödenmediyse ödendi yap
    const monthIndex = payment.paid_months.indexOf(monthKey);
    if (monthIndex === -1) {
        payment.paid_months.push(monthKey);
    } else {
        payment.paid_months.splice(monthIndex, 1);
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
            // Sadece ödeme listesinin tfoot'unu temizle
            const paymentTable = document.getElementById('paymentList').closest('table');
            const existingTfoot = paymentTable.querySelector('tfoot');
            if (existingTfoot) {
                existingTfoot.remove();
            }

            // Ödeme listesini güncelle
            updatePaymentList(selectedYear, selectedMonth);

            // Bütçe hedeflerini güncelle
            updateBudgetGoalsDisplay(selectedYear, selectedMonth);

            // Özet kartlarını güncelle
            updateSummaryCards(selectedYear, selectedMonth);

            // Grafikleri güncelle
            updateCharts(undefined, selectedYear, selectedMonth);
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
            // Her ödenmemiş ödeme için işlem yap
            unpaidPayments.forEach(payment => {
                // Orijinal ödemenin indeksini bul
                const originalPayment = payments.find((p, idx) => idx === payment.index);
                if (originalPayment) {
                    // Aktarım bilgisini oluştur
                    const fromDate = new Date(currentYear, currentMonth);
                    const transferInfo = `(${fromDate.toLocaleString('tr-TR', { month: 'long', year: 'numeric' })}'den aktarıldı)`;

                    // Eğer ödemede daha önce aktarım bilgisi varsa, onu kaldır
                    const cleanName = originalPayment.name.replace(/\s*\([^)]*'[^)]*\)\s*$/, '').trim();

                    // Yeni adı oluştur
                    originalPayment.name = `${cleanName} ${transferInfo}`;

                    // Tarihi güncelle
                    const originalDate = new Date(originalPayment.first_payment_date);
                    originalDate.setFullYear(nextYear);
                    originalDate.setMonth(nextMonth);
                    originalPayment.first_payment_date = originalDate.toISOString().split('T')[0];
                }
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