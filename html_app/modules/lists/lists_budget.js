// Bütçe hedefleri listesi işlemleri
import { loadBudgetGoals, loadPayments } from '../storage.js';
import { formatMoney, convertToTRY } from '../currency.js';
import { calculateMonthlyBalance, calculateCategoryExpenses } from '../calculations.js';
import { getFrequencyText } from '../utils.js';
import {
    showUpdateBudgetGoalModal,
    showAddCategoryGoalModal,
    showDeleteCategoryGoalModal,
    showUpdateCategoryGoalModal
} from '../modals/index.js';

// Ödeme gücü tablosunu güncelle
function updatePaymentPowerList() {
    const tbody = document.getElementById('paymentPowerList');
    if (!tbody) return;

    const payments = loadPayments();
    tbody.innerHTML = '';

    // Sadece tekrarlayan ödemeleri filtrele
    const recurringPayments = payments.filter(payment => payment.frequency !== '0');

    if (recurringPayments.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center">Tekrarlayan ödeme bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    let totalBurden = 0;
    let totalMonths = 0;

    recurringPayments.forEach(payment => {
        // Kalan tekrar sayısını hesapla
        let remainingRepeats = payment.repeatCount;
        if (payment.repeatCount) {
            const today = new Date();
            let currentDate = new Date(payment.firstPaymentDate);
            let pastRepeats = 0;
            while (currentDate <= today) {
                pastRepeats++;
                currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
            }
            remainingRepeats = Math.max(0, payment.repeatCount - pastRepeats);
        }

        // Toplam yükü hesapla
        const repeats = payment.repeatCount || 120; // Sonsuz tekrar için 10 yıl varsayalım
        const actualRepeats = payment.repeatCount ? Math.min(repeats, remainingRepeats) : repeats;
        const totalAmount = convertToTRY(payment.amount * actualRepeats, payment.currency);
        const monthlyAverage = totalAmount / (actualRepeats * parseInt(payment.frequency));

        totalBurden += totalAmount;
        totalMonths = Math.max(totalMonths, actualRepeats * parseInt(payment.frequency));

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <i class="bi bi-credit-card-fill text-danger me-2"></i>${payment.name}
            </td>
            <td>${payment.amount.toFixed(2)}</td>
            <td>${payment.currency}</td>
            <td>${getFrequencyText(payment.frequency)}</td>
            <td>
                ${payment.repeatCount ?
                `<span class="badge bg-info">${remainingRepeats}/${payment.repeatCount}</span>` :
                '<span class="badge bg-secondary">Sonsuz</span>'}
            </td>
            <td>${formatMoney(totalAmount)}</td>
            <td>${formatMoney(monthlyAverage)}</td>
        `;
        tbody.appendChild(row);
    });

    // Toplamları güncelle
    const totalBurdenElement = document.getElementById('totalPaymentBurden');
    if (totalBurdenElement) {
        totalBurdenElement.textContent = formatMoney(totalBurden);
    }

    const averageMonthlyBurdenElement = document.getElementById('averageMonthlyBurden');
    if (averageMonthlyBurdenElement) {
        averageMonthlyBurdenElement.textContent = formatMoney(totalBurden / totalMonths);
    }
}

// Bütçe hedeflerini görüntüle
export function updateBudgetGoalsDisplay(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    const goals = loadBudgetGoals();
    const currentExpenses = calculateMonthlyBalance(selectedYear, selectedMonth).expense;
    const monthlyLimitProgress = (currentExpenses / goals.monthlyExpenseLimit) * 100;
    const categoryExpenses = calculateCategoryExpenses(selectedYear, selectedMonth);

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

    // Seçilen ayın adını al
    const monthName = new Date(selectedYear, selectedMonth).toLocaleString('tr-TR', { month: 'long' });

    // Başlık satırını güncelle
    const headerRow = document.querySelector('#categoryGoalsList').closest('.table').querySelector('thead tr');
    if (headerRow) {
        headerRow.innerHTML = `
            <th>Kategori</th>
            <th>Aylık Hedef</th>
            <th>${monthName} Harcaması</th>
            <th>Kalan</th>
            <th>İlerleme</th>
            <th>İşlemler</th>
        `;
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

    // Ödeme gücü tablosunu güncelle
    updatePaymentPowerList();
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