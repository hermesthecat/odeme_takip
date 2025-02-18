// Birikim modalları modülü
import { getCurrentTheme } from '../theme.js';
import { loadSavings, saveSavings } from '../storage.js';
import { updateSavingList } from '../lists/index.js';
import { updateSummaryCards } from '../calculations.js';
import { updateCharts } from '../charts.js';

// Birikim ekleme modalını göster
export function showAddSavingModal(existingSaving = null, editIndex = -1) {
    const modalTitle = existingSaving ? 'Birikim Düzenle' : 'Birikim Ekle';
    const buttonText = existingSaving ? 'Güncelle' : 'Kaydet';

    Swal.fire({
        title: `<i class="bi bi-piggy-bank-fill text-primary me-2"></i>${modalTitle}`,
        html: `
            <form id="savingModalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="savingName" class="form-label d-flex align-items-center">
                        <i class="bi bi-tag-fill me-2 text-primary"></i>Birikim İsmi
                    </label>
                    <input type="text" class="form-control form-control-lg shadow-sm" id="savingName" 
                           placeholder="Örn: Araba, Ev, Tatil" value="${existingSaving?.name || ''}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="targetAmount" class="form-label d-flex align-items-center">
                            <i class="bi bi-bullseye me-2 text-danger"></i>Hedef Tutar
                        </label>
                        <input type="number" class="form-control form-control-lg shadow-sm" id="targetAmount" 
                               step="0.01" placeholder="0.00" value="${existingSaving?.targetAmount || ''}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="currency" class="form-label d-flex align-items-center">
                            <i class="bi bi-currency-exchange me-2 text-warning"></i>Para Birimi
                        </label>
                        <select class="form-select form-select-lg shadow-sm" id="currency" required>
                            <option value="TRY" ${existingSaving?.currency === 'TRY' ? 'selected' : ''}>₺ TRY</option>
                            <option value="USD" ${existingSaving?.currency === 'USD' ? 'selected' : ''}>$ USD</option>
                            <option value="EUR" ${existingSaving?.currency === 'EUR' ? 'selected' : ''}>€ EUR</option>
                            <option value="GBP" ${existingSaving?.currency === 'GBP' ? 'selected' : ''}>£ GBP</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="currentAmount" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet2 me-2 text-success"></i>Mevcut Tutar
                    </label>
                    <input type="number" class="form-control form-control-lg shadow-sm" id="currentAmount" 
                           step="0.01" placeholder="0.00" value="${existingSaving?.currentAmount || ''}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="startDate" class="form-label d-flex align-items-center">
                            <i class="bi bi-calendar-check-fill me-2 text-info"></i>Başlangıç Tarihi
                        </label>
                        <input type="date" class="form-control form-control-lg shadow-sm" id="startDate" 
                               value="${existingSaving?.startDate || ''}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="targetDate" class="form-label d-flex align-items-center">
                            <i class="bi bi-calendar2-check-fill me-2 text-secondary"></i>Hedef Tarihi
                        </label>
                        <input type="date" class="form-control form-control-lg shadow-sm" id="targetDate" 
                               value="${existingSaving?.targetDate || ''}" required>
                    </div>
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
            const form = document.getElementById('savingModalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

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
                Swal.showValidationMessage('Lütfen tüm alanları doğru şekilde doldurunuz.');
                return false;
            }

            if (saving.currentAmount > saving.targetAmount) {
                Swal.showValidationMessage('Mevcut tutar hedef tutardan büyük olamaz!');
                return false;
            }

            const startDateObj = new Date(saving.startDate);
            const targetDateObj = new Date(saving.targetDate);
            if (targetDateObj <= startDateObj) {
                Swal.showValidationMessage('Hedef tarihi başlangıç tarihinden sonra olmalıdır!');
                return false;
            }

            return saving;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const savings = loadSavings();
            if (editIndex >= 0) {
                savings[editIndex] = result.value;
            } else {
                savings.push(result.value);
            }

            if (saveSavings(savings)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: `Birikim başarıyla ${editIndex >= 0 ? 'güncellendi' : 'kaydedildi'}!`,
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: {
                        popup: 'shadow border-0'
                    }
                }).then(() => {
                    // Tüm gerekli güncellemeleri yap
                    updateSavingList();
                    updateSummaryCards();
                    updateCharts();
                });
            }
        }
    });

    // Yeni birikim eklerken varsayılan tarihleri ayarla
    if (!existingSaving) {
        document.getElementById('startDate').valueAsDate = new Date();
        const targetDate = new Date();
        targetDate.setMonth(targetDate.getMonth() + 12); // Varsayılan olarak 1 yıl sonrası
        document.getElementById('targetDate').valueAsDate = targetDate;
    }
} 