// Bütçe hedefleri listesi işlemleri
import { loadBudgetGoals } from '../storage.js';
import { formatMoney } from '../currency.js';
import { calculateMonthlyBalance, calculateCategoryExpenses } from '../calculations.js';
import { 
    showUpdateBudgetGoalModal, 
    showAddCategoryGoalModal, 
    showDeleteCategoryGoalModal, 
    showUpdateCategoryGoalModal 
} from '../modals/index.js';

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