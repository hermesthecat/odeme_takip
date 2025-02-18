// Gelir listesi işlemleri
import { loadIncomes, saveIncomes } from '../storage.js';
import { formatDate, getFrequencyText } from '../utils.js';
import { showAddIncomeModal } from '../modals/index.js';
import { calculateNextPaymentDate } from './utils.js';

// Gelir listesini güncelleme
export function updateIncomeList(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    const tbody = document.getElementById('incomeList');
    if (!tbody) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelir listesi tablosu bulunamadı!'
        });
        return;
    }

    const incomes = loadIncomes();
    tbody.innerHTML = '';

    if (!Array.isArray(incomes) || incomes.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center">Henüz gelir kaydı bulunmamaktadır.</td>';
        tbody.appendChild(row);
        return;
    }

    const startDate = new Date(selectedYear, selectedMonth, 1);
    const endDate = new Date(selectedYear, selectedMonth + 1, 0);

    incomes.forEach((income, index) => {
        try {
            const firstDate = new Date(income.firstIncomeDate);
            let shouldShow = false;

            if (income.frequency === '0') {
                // Tek seferlik gelir
                shouldShow = firstDate >= startDate && firstDate <= endDate;
            } else {
                // Tekrarlı gelir
                let currentDate = new Date(firstDate);
                let repeatCounter = 0;

                while (currentDate <= endDate) {
                    if (currentDate >= startDate && currentDate <= endDate) {
                        shouldShow = true;
                        break;
                    }
                    if (income.repeatCount && repeatCounter >= income.repeatCount) break;
                    currentDate.setMonth(currentDate.getMonth() + parseInt(income.frequency));
                    repeatCounter++;
                }
            }

            if (shouldShow) {
                // Seçili aydaki gelir tarihini hesapla
                let currentIncomeDate = new Date(firstDate);
                if (income.frequency !== '0') {
                    // Seçili aya kadar ilerlet
                    while (currentIncomeDate < startDate) {
                        currentIncomeDate.setMonth(currentIncomeDate.getMonth() + parseInt(income.frequency));
                    }
                    // Eğer seçili ayı geçtiyse bir önceki tarihe geri dön
                    if (currentIncomeDate > endDate) {
                        currentIncomeDate.setMonth(currentIncomeDate.getMonth() - parseInt(income.frequency));
                    }
                }

                // Sonraki gelir tarihini hesapla (seçili aydan sonraki ilk gelir)
                let nextIncomeDate = new Date(currentIncomeDate);
                if (income.frequency !== '0') {
                    nextIncomeDate.setMonth(nextIncomeDate.getMonth() + parseInt(income.frequency));
                }

                const frequencyText = getFrequencyText(income.frequency);
                const repeatText = income.frequency !== '0' ? 
                                 (income.repeatCount ? ` (${income.repeatCount} tekrar)` : ' (Sonsuz)') : '';

                // Mevcut tekrar sayısını hesapla
                let currentRepeat = 0;
                if (income.frequency !== '0') {
                    let tempDate = new Date(income.firstIncomeDate);
                    while (tempDate <= currentIncomeDate) {
                        currentRepeat++;
                        tempDate.setMonth(tempDate.getMonth() + parseInt(income.frequency));
                    }
                }

                // Tekrar bilgisi metni
                const repeatCountText = income.repeatCount && currentRepeat > 0 ? 
                                      ` <span class="badge bg-info">${currentRepeat}/${income.repeatCount}</span>` : '';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${income.name || '-'}${repeatCountText}</td>
                    <td>${income.amount ? income.amount.toFixed(2) : '0.00'}</td>
                    <td>${income.currency || '-'}</td>
                    <td>${formatDate(currentIncomeDate)}</td>
                    <td>${frequencyText}${repeatText}</td>
                    <td>${formatDate(nextIncomeDate)}</td>
                    <td>
                        <button class="btn btn-primary btn-sm me-1" data-action="update-income" data-index="${index}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" data-action="delete-income" data-index="${index}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            }
        } catch (error) {
            alert(`Gelir ${index + 1} gösterilirken hata oluştu: ${error.message}`);
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