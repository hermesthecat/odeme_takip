// Liste işlemleri modülü
import { loadPayments, loadIncomes, loadSavings, savePayments, saveIncomes, saveSavings, loadBudgetGoals } from './storage.js';
import { formatDate, calculateProgress, getFrequencyText } from './utils.js';
import { formatMoney } from './currency.js';
import { calculateMonthlyBalance, calculateCategoryExpenses } from './calculations.js';
import { showAddIncomeModal, showAddPaymentModal, showAddSavingModal, showUpdateBudgetGoalModal, showAddCategoryGoalModal, showDeleteCategoryGoalModal, showUpdateCategoryGoalModal } from './modals.js';

// Ödeme listesini güncelleme
export function updatePaymentList() {
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
                    <button class="btn btn-primary btn-sm me-1" data-action="update-payment" data-index="${index}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" data-action="delete-payment" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            alert(`Ödeme ${index + 1} gösterilirken hata oluştu: ${error.message}`);
        }
    });

    // Event listener'ları ekle
    tbody.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', handleListAction);
    });
}

// Gelir listesini güncelleme
export function updateIncomeList() {
    const tbody = document.getElementById('incomeList');
    if (!tbody) return;

    const incomes = loadIncomes();
    tbody.innerHTML = '';

    if (!Array.isArray(incomes) || incomes.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center">Henüz gelir kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    incomes.forEach((income, index) => {
        try {
            const nextIncomeDate = calculateNextPaymentDate(income.firstIncomeDate, income.frequency);

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${income.name || '-'}</td>
                <td>${income.amount ? income.amount.toFixed(2) : '0.00'}</td>
                <td>${income.currency || '-'}</td>
                <td>${formatDate(income.firstIncomeDate)}</td>
                <td>${getFrequencyText(income.frequency)}</td>
                <td>${formatDate(nextIncomeDate)}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-primary btn-sm" data-action="update-income" data-index="${index}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" data-action="delete-income" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            console.error(`Gelir ${index + 1} gösterilirken hata oluştu:`, error);
        }
    });

    // Event listener'ları ekle
    tbody.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', handleListAction);
    });
}

// Birikim listesini güncelleme
export function updateSavingList() {
    const tbody = document.getElementById('savingList');
    if (!tbody) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikim listesi tablosu bulunamadı!'
        });
        return;
    }

    const savings = loadSavings();
    tbody.innerHTML = '';

    if (!Array.isArray(savings) || savings.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="8" class="text-center">Henüz birikim kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    savings.forEach((saving, index) => {
        try {
            const progress = calculateProgress(saving.targetAmount, saving.currentAmount);
            const progressClass = progress >= 100 ? 'bg-success' : 'bg-primary';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${saving.name || '-'}</td>
                <td>${saving.targetAmount ? saving.targetAmount.toFixed(2) : '0.00'}</td>
                <td>${saving.currentAmount ? saving.currentAmount.toFixed(2) : '0.00'}</td>
                <td>${saving.currency || '-'}</td>
                <td>${formatDate(saving.startDate)}</td>
                <td>${formatDate(saving.targetDate)}</td>
                <td>
                    <div class="progress">
                        <div class="progress-bar ${progressClass}" role="progressbar" 
                             style="width: ${Math.min(100, progress)}%" 
                             aria-valuenow="${Math.min(100, progress)}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${progress}%
                        </div>
                    </div>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm me-1" data-action="update-saving" data-index="${index}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" data-action="delete-saving" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: `Birikim ${index + 1} gösterilirken hata oluştu: ${error.message}`
            });
        }
    });

    // Event listener'ları ekle
    tbody.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', handleListAction);
    });
}

// Liste öğelerinin işlemlerini yönet
function handleListAction(e) {
    const action = e.target.closest('button').dataset.action;
    const index = parseInt(e.target.closest('button').dataset.index);

    switch (action) {
        case 'update-income':
            updateIncome(index);
            break;
        case 'delete-income':
            deleteIncome(index);
            break;
        case 'update-payment':
            updatePayment(index);
            break;
        case 'delete-payment':
            deletePayment(index);
            break;
        case 'update-saving':
            updateSaving(index);
            break;
        case 'delete-saving':
            deleteSaving(index);
            break;
    }
}

// Gelir güncelleme
export function updateIncome(index) {
    const incomes = loadIncomes();
    const income = incomes[index];

    showAddIncomeModal(income, index);
}

// Gelir silme
export function deleteIncome(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu geliri silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const incomes = loadIncomes();
            incomes.splice(index, 1);
            if (saveIncomes(incomes)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Gelir başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
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

// Birikim güncelleme
export function updateSaving(index) {
    const savings = loadSavings();
    const saving = savings[index];

    showAddSavingModal(saving, index);
}

// Birikim silme
export function deleteSaving(index) {
    Swal.fire({
        title: 'Emin misiniz?',
        text: "Bu birikimi silmek istediğinizden emin misiniz?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Evet, sil!',
        cancelButtonText: 'İptal'
    }).then((result) => {
        if (result.isConfirmed) {
            const savings = loadSavings();
            savings.splice(index, 1);
            if (saveSavings(savings)) {
                Swal.fire({
                    icon: 'success',
                    title: 'Silindi!',
                    text: 'Birikim başarıyla silindi.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.reload();
                });
            }
        }
    });
}

// Bütçe hedeflerini görüntüle
export function updateBudgetGoalsDisplay() {
    const container = document.getElementById('budgetGoals');
    if (!container) return;

    const goals = loadBudgetGoals();
    const currentExpenses = calculateMonthlyBalance(new Date().getFullYear(), new Date().getMonth()).expense;
    const monthlyLimitProgress = (currentExpenses / goals.monthlyExpenseLimit) * 100;
    const categoryExpenses = calculateCategoryExpenses(new Date().getFullYear(), new Date().getMonth());

    let html = `
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Aylık Bütçe Durumu</h5>
                <button class="btn btn-sm btn-outline-primary" data-action="update-budget-goal">
                    <i class="bi bi-pencil"></i> Hedefi Güncelle
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Aylık Limit: ${formatMoney(goals.monthlyExpenseLimit)}</span>
                        <span>Mevcut Harcama: ${formatMoney(currentExpenses)}</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar ${monthlyLimitProgress > 90 ? 'bg-danger' : monthlyLimitProgress > 75 ? 'bg-warning' : 'bg-success'}" 
                             role="progressbar" 
                             style="width: ${Math.min(100, monthlyLimitProgress)}%" 
                             aria-valuenow="${Math.min(100, monthlyLimitProgress)}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            ${Math.round(monthlyLimitProgress)}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Kategori Hedefleri</h5>
                <button class="btn btn-sm btn-outline-success" data-action="add-category-goal">
                    <i class="bi bi-plus"></i> Kategori Ekle
                </button>
            </div>
            <div class="card-body">
                ${goals.categories.length === 0 ?
            '<p class="text-muted mb-0">Henüz kategori hedefi eklenmemiş.</p>' :
            goals.categories.map((category, index) => {
                const expense = categoryExpenses[category.name] || 0;
                const progress = (expense / category.limit) * 100;
                return `
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>${category.name}</span>
                                <div>
                                    <span class="me-2">Harcama: ${formatMoney(expense)} / Limit: ${formatMoney(category.limit)}</span>
                                    <button class="btn btn-sm btn-outline-primary me-1" data-action="update-category-goal" data-index="${index}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-action="delete-category-goal" data-index="${index}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar ${progress > 90 ? 'bg-danger' : progress > 75 ? 'bg-warning' : 'bg-info'}" 
                                     role="progressbar" 
                                     style="width: ${Math.min(100, progress)}%" 
                                     aria-valuenow="${Math.min(100, progress)}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    ${Math.round(progress)}%
                                </div>
                            </div>
                        </div>
                    `;
            }).join('')}
            </div>
        </div>
    `;

    container.innerHTML = html;

    // Event listener'ları ekle
    container.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', handleBudgetAction);
    });

    // Kategori bazlı uyarıları kontrol et
    goals.categories.forEach(category => {
        const expense = categoryExpenses[category.name] || 0;
        const progress = (expense / category.limit) * 100;

        if (progress > 90) {
            Swal.fire({
                icon: 'warning',
                title: 'Kategori Uyarısı!',
                text: `${category.name} kategorisinde harcama limitinize çok yaklaştınız!`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    });

    // Genel bütçe uyarısı
    if (monthlyLimitProgress > 90) {
        Swal.fire({
            icon: 'warning',
            title: 'Bütçe Uyarısı!',
            text: 'Aylık harcama limitinize çok yaklaştınız!',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}

// Bütçe işlemlerini yönet
function handleBudgetAction(e) {
    const action = e.target.closest('button').dataset.action;
    const index = e.target.closest('button').dataset.index;

    switch (action) {
        case 'update-budget-goal':
            showUpdateBudgetGoalModal();
            break;
        case 'add-category-goal':
            showAddCategoryGoalModal();
            break;
        case 'update-category-goal':
            showUpdateCategoryGoalModal(parseInt(index));
            break;
        case 'delete-category-goal':
            showDeleteCategoryGoalModal(parseInt(index));
            break;
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