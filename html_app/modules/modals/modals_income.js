// Gelir modalları modülü
import { getCurrentTheme } from '../theme.js';
import { loadIncomes, saveIncomes } from '../storage.js';
import { updateIncomeList } from '../lists/index.js';
import { updateSummaryCards } from '../calculations.js';
import { updateCharts } from '../charts.js';
import { updateCalendar } from '../calendar.js';

// Gelir ekleme modalını göster
export function showAddIncomeModal(existingIncome = null, editIndex = -1) {
    const modalTitle = existingIncome ? 'Gelir Düzenle' : 'Gelir Ekle';
    const buttonText = existingIncome ? 'Güncelle' : 'Kaydet';

    Swal.fire({
        title: `<i class="bi bi-plus-circle-fill text-success me-2"></i>${modalTitle}`,
        html: `
            <form id="incomeModalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="incomeName" class="form-label d-flex align-items-center">
                        <i class="bi bi-tag-fill me-2 text-primary"></i>Gelir İsmi
                    </label>
                    <input type="text" class="form-control form-control-lg shadow-sm" id="incomeName" 
                           placeholder="Örn: Maaş, Kira Geliri" value="${existingIncome?.name || ''}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="amount" class="form-label d-flex align-items-center">
                            <i class="bi bi-cash-stack me-2 text-success"></i>Tutar
                        </label>
                        <input type="number" class="form-control form-control-lg shadow-sm" id="amount" 
                               step="0.01" placeholder="0.00" value="${existingIncome?.amount || ''}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="currency" class="form-label d-flex align-items-center">
                            <i class="bi bi-currency-exchange me-2 text-warning"></i>Para Birimi
                        </label>
                        <select class="form-select form-select-lg shadow-sm" id="currency" required>
                            <option value="TRY" ${existingIncome?.currency === 'TRY' ? 'selected' : ''}>₺ TRY</option>
                            <option value="USD" ${existingIncome?.currency === 'USD' ? 'selected' : ''}>$ USD</option>
                            <option value="EUR" ${existingIncome?.currency === 'EUR' ? 'selected' : ''}>€ EUR</option>
                            <option value="GBP" ${existingIncome?.currency === 'GBP' ? 'selected' : ''}>£ GBP</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="firstIncomeDate" class="form-label d-flex align-items-center">
                        <i class="bi bi-calendar-check-fill me-2 text-info"></i>İlk Gelir Tarihi
                    </label>
                    <input type="date" class="form-control form-control-lg shadow-sm" id="firstIncomeDate" 
                           value="${existingIncome?.firstIncomeDate || ''}" required>
                </div>
                <div class="mb-3">
                    <label for="frequency" class="form-label d-flex align-items-center">
                        <i class="bi bi-arrow-repeat me-2 text-secondary"></i>Tekrarlama Sıklığı
                    </label>
                    <select class="form-select form-select-lg shadow-sm" id="frequency" required>
                        <option value="0" ${existingIncome?.frequency === '0' ? 'selected' : ''}>🔄 Tekrar Yok</option>
                        <option value="1" ${existingIncome?.frequency === '1' ? 'selected' : ''}>📅 Her Ay</option>
                        <option value="2" ${existingIncome?.frequency === '2' ? 'selected' : ''}>📅 2 Ayda Bir</option>
                        <option value="3" ${existingIncome?.frequency === '3' ? 'selected' : ''}>📅 3 Ayda Bir</option>
                        <option value="4" ${existingIncome?.frequency === '4' ? 'selected' : ''}>📅 4 Ayda Bir</option>
                        <option value="5" ${existingIncome?.frequency === '5' ? 'selected' : ''}>📅 5 Ayda Bir</option>
                        <option value="6" ${existingIncome?.frequency === '6' ? 'selected' : ''}>📅 6 Ayda Bir</option>
                        <option value="12" ${existingIncome?.frequency === '12' ? 'selected' : ''}>📅 Yıllık</option>
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
            const form = document.getElementById('incomeModalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const income = {
                name: document.getElementById('incomeName').value.trim(),
                amount: parseFloat(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                firstIncomeDate: document.getElementById('firstIncomeDate').value,
                frequency: document.getElementById('frequency').value
            };

            if (!income.name || isNaN(income.amount) || !income.firstIncomeDate) {
                Swal.showValidationMessage('Lütfen tüm alanları doğru şekilde doldurunuz.');
                return false;
            }

            return income;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const incomes = loadIncomes();
            if (editIndex >= 0) {
                incomes[editIndex] = result.value;
            } else {
                incomes.push(result.value);
            }
            
            if (saveIncomes(incomes)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: `Gelir başarıyla ${editIndex >= 0 ? 'güncellendi' : 'kaydedildi'}!`,
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: {
                        popup: 'shadow border-0'
                    }
                }).then(() => {
                    // Tüm gerekli güncellemeleri yap
                    updateIncomeList();
                    updateSummaryCards();
                    updateCharts();
                    updateCalendar();
                });
            }
        }
    });

    // Yeni gelir eklerken bugünün tarihini varsayılan olarak ayarla
    if (!existingIncome) {
        document.getElementById('firstIncomeDate').valueAsDate = new Date();
    }
} 