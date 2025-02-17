// Ödeme modalları modülü
import { getCurrentTheme } from '../theme.js';
import { loadPayments, savePayments, loadBudgetGoals } from '../storage.js';
import { updatePaymentList, updateBudgetGoalsDisplay } from '../lists/index.js';
import { updateSummaryCards } from '../calculations.js';
import { updateCharts } from '../charts.js';
import { updateCalendar } from '../calendar.js';

// Ödeme ekleme modalını göster
export function showAddPaymentModal(existingPayment = null, editIndex = -1) {
    const modalTitle = existingPayment ? 'Ödeme Düzenle' : 'Ödeme Ekle';
    const buttonText = existingPayment ? 'Güncelle' : 'Kaydet';

    // Kategorileri yükle
    const goals = loadBudgetGoals();
    const categoryOptions = goals.categories.map(category =>
        `<option value="${category.name}" ${existingPayment?.category === category.name ? 'selected' : ''}>${category.name}</option>`
    ).join('');

    Swal.fire({
        title: `<i class="bi bi-credit-card-fill text-danger me-2"></i>${modalTitle}`,
        html: `
            <form id="paymentModalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="paymentName" class="form-label d-flex align-items-center">
                        <i class="bi bi-tag-fill me-2 text-primary"></i>Ödeme İsmi
                    </label>
                    <input type="text" class="form-control form-control-lg shadow-sm" id="paymentName" 
                           placeholder="Örn: Elektrik, Su, Doğalgaz" value="${existingPayment?.name || ''}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="amount" class="form-label d-flex align-items-center">
                            <i class="bi bi-cash-stack me-2 text-success"></i>Tutar
                        </label>
                        <input type="number" class="form-control form-control-lg shadow-sm" id="amount" 
                               step="0.01" placeholder="0.00" value="${existingPayment?.amount || ''}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="currency" class="form-label d-flex align-items-center">
                            <i class="bi bi-currency-exchange me-2 text-warning"></i>Para Birimi
                        </label>
                        <select class="form-select form-select-lg shadow-sm" id="currency" required>
                            <option value="TRY" ${existingPayment?.currency === 'TRY' ? 'selected' : ''}>₺ TRY</option>
                            <option value="USD" ${existingPayment?.currency === 'USD' ? 'selected' : ''}>$ USD</option>
                            <option value="EUR" ${existingPayment?.currency === 'EUR' ? 'selected' : ''}>€ EUR</option>
                            <option value="GBP" ${existingPayment?.currency === 'GBP' ? 'selected' : ''}>£ GBP</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label d-flex align-items-center">
                        <i class="bi bi-folder-fill me-2 text-warning"></i>Kategori
                    </label>
                    <select class="form-select form-select-lg shadow-sm" id="category" required>
                        <option value="">Kategori Seçin</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label for="firstPaymentDate" class="form-label d-flex align-items-center">
                        <i class="bi bi-calendar-check-fill me-2 text-info"></i>İlk Ödeme Tarihi
                    </label>
                    <input type="date" class="form-control form-control-lg shadow-sm" id="firstPaymentDate" 
                           value="${existingPayment?.firstPaymentDate || ''}" required>
                </div>
                <div class="mb-3">
                    <label for="frequency" class="form-label d-flex align-items-center">
                        <i class="bi bi-arrow-repeat me-2 text-secondary"></i>Tekrarlama Sıklığı
                    </label>
                    <select class="form-select form-select-lg shadow-sm" id="frequency" required>
                        <option value="0" ${existingPayment?.frequency === '0' ? 'selected' : ''}>🔄 Tekrar Yok</option>
                        <option value="1" ${existingPayment?.frequency === '1' ? 'selected' : ''}>📅 Her Ay</option>
                        <option value="2" ${existingPayment?.frequency === '2' ? 'selected' : ''}>📅 2 Ayda Bir</option>
                        <option value="3" ${existingPayment?.frequency === '3' ? 'selected' : ''}>📅 3 Ayda Bir</option>
                        <option value="4" ${existingPayment?.frequency === '4' ? 'selected' : ''}>📅 4 Ayda Bir</option>
                        <option value="5" ${existingPayment?.frequency === '5' ? 'selected' : ''}>📅 5 Ayda Bir</option>
                        <option value="6" ${existingPayment?.frequency === '6' ? 'selected' : ''}>📅 6 Ayda Bir</option>
                        <option value="12" ${existingPayment?.frequency === '12' ? 'selected' : ''}>📅 Yıllık</option>
                    </select>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: `<i class="bi bi-check-lg me-2"></i>${buttonText}`,
        cancelButtonText: '<i class="bi bi-x-lg me-2"></i>İptal',
        customClass: {
            container: getCurrentTheme() === 'dark' ? 'swal2-dark' : '',
            popup: 'shadow-lg border-0',
            title: 'text-center fs-4 fw-bold',
            htmlContainer: 'text-start',
            confirmButton: 'btn btn-success px-3 me-3',
            cancelButton: 'btn btn-outline-secondary px-3'
        },
        width: '32rem',
        padding: '2rem',
        buttonsStyling: false,
        focusConfirm: false,
        preConfirm: () => {
            const form = document.getElementById('paymentModalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const payment = {
                name: document.getElementById('paymentName').value.trim(),
                amount: parseFloat(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                category: document.getElementById('category').value,
                firstPaymentDate: document.getElementById('firstPaymentDate').value,
                frequency: document.getElementById('frequency').value
            };

            if (!payment.name || isNaN(payment.amount) || !payment.firstPaymentDate || !payment.category) {
                Swal.showValidationMessage('Lütfen tüm alanları doğru şekilde doldurunuz.');
                return false;
            }

            return payment;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const payments = loadPayments();
            if (editIndex >= 0) {
                payments[editIndex] = result.value;
            } else {
                payments.push(result.value);
            }
            
            if (savePayments(payments)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: `Ödeme başarıyla ${editIndex >= 0 ? 'güncellendi' : 'kaydedildi'}!`,
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: {
                        popup: 'shadow border-0'
                    }
                }).then(() => {
                    // Tüm gerekli güncellemeleri yap
                    updatePaymentList();
                    updateSummaryCards();
                    updateCharts();
                    updateCalendar();
                    updateBudgetGoalsDisplay();
                });
            }
        }
    });

    // Yeni ödeme eklerken bugünün tarihini varsayılan olarak ayarla
    if (!existingPayment) {
        document.getElementById('firstPaymentDate').valueAsDate = new Date();
    }
} 