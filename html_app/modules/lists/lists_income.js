// Gelir listesi işlemleri
import { loadIncomes, saveIncomes } from '../storage.js';
import { formatDate, getFrequencyText } from '../utils.js';
import { showAddIncomeModal } from '../modals/index.js';
import { calculateNextPaymentDate } from './utils.js';

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
        button.addEventListener('click', handleIncomeAction);
    });
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

// Gelir işlemlerini yönet
function handleIncomeAction(e) {
    const action = e.target.closest('button').dataset.action;
    const index = parseInt(e.target.closest('button').dataset.index);

    switch (action) {
        case 'update-income':
            updateIncome(index);
            break;
        case 'delete-income':
            deleteIncome(index);
            break;
    }
} 