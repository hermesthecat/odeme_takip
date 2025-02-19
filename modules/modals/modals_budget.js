// Bütçe hedefleri modalları modülü
import { getCurrentTheme } from '../theme.js';
import { loadBudgetGoals, saveBudgetGoals } from '../storage.js';
import { updateBudgetGoalsDisplay } from '../lists/index.js';
import { updateSummaryCards } from '../calculations.js';

// Bütçe hedefi güncelleme modalını göster
export function showUpdateBudgetGoalModal() {
    const goals = loadBudgetGoals();

    // Seçili ay ve yılı al
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const selectedMonth = parseInt(monthSelect.value) + 1;
    const selectedYear = parseInt(yearSelect.value);

    // Ay-yıl anahtarını oluştur
    const monthKey = `${selectedYear}-${String(selectedMonth).padStart(2, '0')}`;

    // Seçili ayın limitini al
    const currentLimit = goals.monthlyLimits[monthKey] || 0;

    // Seçili ayın adını al
    const monthName = new Date(selectedYear, selectedMonth - 1).toLocaleString('tr-TR', { month: 'long', year: 'numeric' });

    Swal.fire({
        title: '<i class="bi bi-graph-up-arrow text-primary me-2"></i>Aylık Bütçe Hedefi',
        html: `
            <form id="budgetGoalForm" class="needs-validation">
                <div class="mb-3">
                    <h5 class="text-center text-primary mb-3">${monthName}</h5>
                </div>
                <div class="mb-3 position-relative">
                    <label for="monthlyLimit" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Aylık Harcama Limiti
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">₺</span>
                        <input type="number" class="form-control form-control-lg shadow-sm" 
                               id="monthlyLimit" value="${currentLimit}" 
                               min="0" step="100" placeholder="0.00" required>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Kaydet',
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
            const form = document.getElementById('budgetGoalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const limit = parseFloat(document.getElementById('monthlyLimit').value);
            if (isNaN(limit) || limit < 0) {
                Swal.showValidationMessage('Geçerli bir limit giriniz');
                return false;
            }

            return limit;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Seçili ay için limiti güncelle
            goals.monthlyLimits[monthKey] = result.value;

            if (saveBudgetGoals(goals)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Bütçe hedefi güncellendi.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    updateBudgetGoalsDisplay(selectedYear, selectedMonth - 1);
                    updateSummaryCards(selectedYear, selectedMonth - 1);
                });
            }
        }
    });
}

// Kategori hedefi ekleme modalını göster
export function showAddCategoryGoalModal() {
    Swal.fire({
        title: '<i class="bi bi-graph-up-arrow text-primary me-2"></i>Kategori Hedefi Ekle',
        html: `
            <form id="categoryGoalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="categoryName" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Kategori Adı
                    </label>
                    <input type="text" class="form-control form-control-lg shadow-sm" 
                           id="categoryName" placeholder="Örn: Market, Faturalar, Eğlence" required>
                </div>
                <div class="mb-3 position-relative">
                    <label for="categoryLimit" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Aylık Harcama Limiti
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">₺</span>
                        <input type="number" class="form-control form-control-lg shadow-sm" 
                               id="categoryLimit" min="0" step="100" placeholder="0.00" required>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Kaydet',
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
            const form = document.getElementById('categoryGoalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const name = document.getElementById('categoryName').value.trim();
            const limit = parseFloat(document.getElementById('categoryLimit').value);

            if (!name) {
                Swal.showValidationMessage('Kategori adı gereklidir');
                return false;
            }
            if (isNaN(limit) || limit < 0) {
                Swal.showValidationMessage('Geçerli bir limit giriniz');
                return false;
            }

            return { name, limit };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const goals = loadBudgetGoals();
            goals.categories.push(result.value);
            if (saveBudgetGoals(goals)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Kategori hedefi eklendi.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    updateBudgetGoalsDisplay();
                    updateSummaryCards();
                });
            }
        }
    });
}

// Kategori hedefi silme modalını göster
export function showDeleteCategoryGoalModal(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu kategori hedefini silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const goals = loadBudgetGoals();
            goals.categories.splice(index, 1);
            if (saveBudgetGoals(goals)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Kategori hedefi başarıyla silindi.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    updateBudgetGoalsDisplay();
                    updateSummaryCards();
                });
            }
        }
    });
}

// Kategori hedefi güncelleme modalını göster
export function showUpdateCategoryGoalModal(index) {
    const goals = loadBudgetGoals();
    const category = goals.categories[index];

    Swal.fire({
        title: '<i class="bi bi-graph-up-arrow text-primary me-2"></i>Kategori Hedefi Güncelle',
        html: `
            <form id="categoryGoalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="categoryName" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Kategori Adı
                    </label>
                    <input type="text" class="form-control form-control-lg shadow-sm" 
                           id="categoryName" placeholder="Örn: Market, Faturalar, Eğlence" 
                           value="${category.name}" required>
                </div>
                <div class="mb-3 position-relative">
                    <label for="categoryLimit" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Aylık Harcama Limiti
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">₺</span>
                        <input type="number" class="form-control form-control-lg shadow-sm" 
                               id="categoryLimit" min="0" step="100" placeholder="0.00" 
                               value="${category.limit}" required>
                    </div>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-2"></i>Güncelle',
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
            const form = document.getElementById('categoryGoalForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            const name = document.getElementById('categoryName').value.trim();
            const limit = parseFloat(document.getElementById('categoryLimit').value);

            if (!name) {
                Swal.showValidationMessage('Kategori adı gereklidir');
                return false;
            }
            if (isNaN(limit) || limit < 0) {
                Swal.showValidationMessage('Geçerli bir limit giriniz');
                return false;
            }

            return { name, limit };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            goals.categories[index] = result.value;
            if (saveBudgetGoals(goals)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Kategori hedefi güncellendi.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    updateBudgetGoalsDisplay();
                    updateSummaryCards();
                });
            }
        }
    });
} 