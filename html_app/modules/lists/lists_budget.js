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
    const goals = loadBudgetGoals();
    const currentExpenses = calculateMonthlyBalance(new Date().getFullYear(), new Date().getMonth()).expense;
    const monthlyLimitProgress = (currentExpenses / goals.monthlyExpenseLimit) * 100;
    const categoryExpenses = calculateCategoryExpenses(new Date().getFullYear(), new Date().getMonth());

    // Aylık limit progress barını güncelle
    const progressBar = document.getElementById('monthlyLimitProgress');
    if (progressBar) {
        const percentage = Math.min(100, Math.round(monthlyLimitProgress));
        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute('aria-valuenow', percentage);
        progressBar.textContent = `${percentage}%`;
        progressBar.className = `progress-bar ${percentage >= 100 ? 'bg-danger' : percentage >= 80 ? 'bg-warning' : 'bg-success'}`;
    }

    // Mevcut harcama ve limit metinlerini güncelle
    const currentExpenseText = document.getElementById('currentExpenseText');
    if (currentExpenseText) {
        currentExpenseText.textContent = `Mevcut Harcama: ${formatMoney(currentExpenses)}`;
    }

    const monthlyLimitText = document.getElementById('monthlyLimitText');
    if (monthlyLimitText) {
        monthlyLimitText.textContent = `Limit: ${formatMoney(goals.monthlyExpenseLimit)}`;
    }

    // Kategori hedeflerini listele
    const tbody = document.getElementById('categoryGoalsList');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!goals.categories || goals.categories.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="6" class="text-center">Henüz kategori hedefi bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    goals.categories.forEach((category, index) => {
        const currentAmount = categoryExpenses[category.name] || 0;
        const remainingAmount = Math.max(0, category.limit - currentAmount);
        const progress = Math.min(100, Math.round((currentAmount / category.limit) * 100));

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <i class="bi bi-folder-fill text-warning me-2"></i>${category.name}
            </td>
            <td>${formatMoney(category.limit)}</td>
            <td>${formatMoney(currentAmount)}</td>
            <td>${formatMoney(remainingAmount)}</td>
            <td>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar ${progress >= 100 ? 'bg-danger' : progress >= 80 ? 'bg-warning' : 'bg-success'}" 
                         role="progressbar" style="width: ${progress}%;" 
                         aria-valuenow="${progress}" aria-valuemin="0" aria-valuemax="100">
                        ${progress}%
                    </div>
                </div>
            </td>
            <td>
                <button class="btn btn-primary btn-sm me-1" data-action="update-category" data-index="${index}">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-danger btn-sm" data-action="delete-category" data-index="${index}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Event listener'ları ekle
    document.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', (e) => {
            const action = e.target.closest('button').dataset.action;
            const index = parseInt(e.target.closest('button').dataset.index);

            switch (action) {
                case 'update-monthly-limit':
                    showUpdateBudgetGoalModal();
                    break;
                case 'add-category-goal':
                    showAddCategoryGoalModal();
                    break;
                case 'update-category':
                    showUpdateCategoryGoalModal(index);
                    break;
                case 'delete-category':
                    showDeleteCategoryGoalModal(index);
                    break;
            }
        });
    });
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