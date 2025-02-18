// Grafik işlemleri modülü
import { loadPayments, loadIncomes, loadSavings } from './storage.js';
import { convertToTRY, formatMoney } from './currency.js';
import { calculateMonthlyBalance } from './calculations.js';

// Grafik değişkenlerini window nesnesine bağla
window.incomeExpenseChart = null;
window.savingsChart = null;

// Grafikleri güncelle
export function updateCharts(period = 'month', selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    updateIncomeExpenseChart(period, selectedYear, selectedMonth);
    updateSavingsChart();
}

// Gelir-Gider dağılımı grafiğini güncelle
function updateIncomeExpenseChart(period, selectedYear, selectedMonth) {
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) return;

    // Mevcut grafiği temizle
    if (window.incomeExpenseChart) {
        window.incomeExpenseChart.destroy();
    }

    let labels, incomeData, expenseData;

    if (period === 'year') {
        // Yıllık görünüm için tüm ayların verilerini al
        labels = Array.from({ length: 12 }, (_, i) =>
            new Date(selectedYear, i).toLocaleString('tr-TR', { month: 'long' })
        );

        incomeData = Array.from({ length: 12 }, (_, i) => {
            const balance = calculateMonthlyBalance(selectedYear, i);
            return balance.income;
        });

        expenseData = Array.from({ length: 12 }, (_, i) => {
            const balance = calculateMonthlyBalance(selectedYear, i);
            return balance.expense;
        });
    } else {
        // Aylık görünüm için seçili ayın verilerini al
        const balance = calculateMonthlyBalance(selectedYear, selectedMonth);
        const monthName = new Date(selectedYear, selectedMonth).toLocaleString('tr-TR', { month: 'long', year: 'numeric' });

        labels = [monthName];
        incomeData = [balance.income];
        expenseData = [balance.expense];
    }

    // Yeni grafiği oluştur
    window.incomeExpenseChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Gelir',
                    data: incomeData,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                },
                {
                    label: 'Gider',
                    data: expenseData,
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: period === 'year' ? `${selectedYear} Yılı Gelir-Gider Dağılımı` : `${labels[0]} Gelir-Gider Dağılımı`
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${formatMoney(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return formatMoney(value);
                        }
                    }
                }
            }
        }
    });

    // Grafik başlığını güncelle
    const chartTitle = document.querySelector('.chart-card .card-title');
    if (chartTitle) {
        chartTitle.textContent = period === 'year' ?
            `${selectedYear} Yılı Gelir-Gider Dağılımı` :
            `${labels[0]} Gelir-Gider Dağılımı`;
    }
}

// Birikim hedefleri grafiğini güncelle
function updateSavingsChart() {
    const ctx = document.getElementById('savingsChart');
    if (!ctx) return;

    // Mevcut grafiği temizle
    if (window.savingsChart) {
        window.savingsChart.destroy();
    }

    const savings = loadSavings();
    const labels = savings.map(saving => saving.name);
    const targetData = savings.map(saving => saving.targetAmount);
    const currentData = savings.map(saving => saving.currentAmount);

    window.savingsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Hedef',
                    data: targetData,
                    backgroundColor: 'rgba(0, 123, 255, 0.5)',
                    borderColor: 'rgb(0, 123, 255)',
                    borderWidth: 1
                },
                {
                    label: 'Mevcut',
                    data: currentData,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Birikim Hedefleri'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.dataset.label}: ${formatMoney(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return formatMoney(value);
                        }
                    }
                }
            }
        }
    });
}

// Grafik değişkenlerini dışa aktar
export const charts = {
    incomeExpenseChart: window.incomeExpenseChart,
    savingsChart: window.savingsChart
}; 