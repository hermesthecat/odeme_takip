// Modal işlemleri modülü
import { getCurrentTheme } from './theme.js';
import { loadBudgetGoals, saveIncomes, saveSavings, savePayments, loadIncomes, loadSavings, loadPayments, saveBudgetGoals } from './storage.js';
import { formatDate, getFrequencyText } from './utils.js';
import { updateIncomeList, updatePaymentList, updateSavingList, updateBudgetGoalsDisplay } from './lists.js';
import { updateSummaryCards } from './calculations.js';
import { updateCharts } from './charts.js';
import { updateCalendar } from './calendar.js';

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

// Bütçe hedefi güncelleme modalını göster
export function showUpdateBudgetGoalModal() {
    const goals = loadBudgetGoals();

    Swal.fire({
        title: '<i class="bi bi-graph-up-arrow text-primary me-2"></i>Aylık Bütçe Hedefi',
        html: `
            <form id="budgetGoalForm" class="needs-validation">
                <div class="mb-3 position-relative">
                    <label for="monthlyLimit" class="form-label d-flex align-items-center">
                        <i class="bi bi-wallet-fill me-2 text-success"></i>Aylık Harcama Limiti
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text">₺</span>
                        <input type="number" class="form-control form-control-lg shadow-sm" 
                               id="monthlyLimit" value="${goals.monthlyExpenseLimit}" 
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
            goals.monthlyExpenseLimit = result.value;
            if (saveBudgetGoals(goals)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: 'Bütçe hedefi güncellendi.',
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