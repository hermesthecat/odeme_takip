// Grafik işlemleri modülü
import { loadPayments, loadIncomes, loadSavings } from './storage.js';
import { convertToTRY, formatMoney } from './currency.js';
import { calculateMonthlyBalance } from './calculations.js';

// Grafik değişkenlerini window nesnesine bağla
window.incomeExpenseChart = null;
window.savingsChart = null;

// Grafikleri güncelle
export function updateCharts(period = 'month') {
    updateIncomeExpenseChart(period);
    updateSavingsChart();
}

// Gelir-Gider grafiğini güncelle
function updateIncomeExpenseChart(period) {
    const ctx = document.getElementById('incomeExpenseChart');
    if (!ctx) return;

    let labels, incomeData, expenseData;

    if (period === 'month') {
        // Son 6 ayın verilerini al
        const months = [];
        const incomes = [];
        const expenses = [];

        for (let i = 5; i >= 0; i--) {
            const date = new Date();
            date.setMonth(date.getMonth() - i);
            const monthYear = new Intl.DateTimeFormat('tr-TR', { month: 'long', year: 'numeric' }).format(date);
            months.push(monthYear);

            const balance = calculateMonthlyBalance(date.getFullYear(), date.getMonth());
            incomes.push(balance.income);
            expenses.push(balance.expense);
        }

        labels = months;
        incomeData = incomes;
        expenseData = expenses;
    } else {
        // Yıllık verileri al (son 3 yıl)
        const years = [];
        const incomes = [];
        const expenses = [];

        for (let i = 2; i >= 0; i--) {
            const year = new Date().getFullYear() - i;
            years.push(year.toString());

            let yearlyIncome = 0;
            let yearlyExpense = 0;

            for (let month = 0; month < 12; month++) {
                const balance = calculateMonthlyBalance(year, month);
                yearlyIncome += balance.income;
                yearlyExpense += balance.expense;
            }

            incomes.push(yearlyIncome);
            expenses.push(yearlyExpense);
        }

        labels = years;
        incomeData = incomes;
        expenseData = expenses;
    }

    // Eğer grafik zaten varsa güncelle, yoksa oluştur
    if (window.incomeExpenseChart) {
        window.incomeExpenseChart.data.labels = labels;
        window.incomeExpenseChart.data.datasets[0].data = incomeData;
        window.incomeExpenseChart.data.datasets[1].data = expenseData;
        window.incomeExpenseChart.update();
    } else {
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
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatMoney(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatMoney(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Birikim grafiğini güncelle
function updateSavingsChart() {
    const ctx = document.getElementById('savingsChart');
    if (!ctx) return;

    const savings = loadSavings();
    const labels = [];
    const currentData = [];
    const targetData = [];

    savings.forEach(saving => {
        labels.push(saving.name);
        currentData.push(convertToTRY(saving.currentAmount, saving.currency));
        targetData.push(convertToTRY(saving.targetAmount, saving.currency));
    });

    // Eğer grafik zaten varsa güncelle, yoksa oluştur
    if (window.savingsChart) {
        window.savingsChart.data.labels = labels;
        window.savingsChart.data.datasets[0].data = currentData;
        window.savingsChart.data.datasets[1].data = targetData;
        window.savingsChart.update();
    } else {
        window.savingsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Mevcut Tutar',
                        data: currentData,
                        backgroundColor: 'rgba(13, 110, 253, 0.5)',
                        borderColor: 'rgb(13, 110, 253)',
                        borderWidth: 1
                    },
                    {
                        label: 'Hedef Tutar',
                        data: targetData,
                        backgroundColor: 'rgba(108, 117, 125, 0.5)',
                        borderColor: 'rgb(108, 117, 125)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatMoney(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatMoney(context.raw);
                            }
                        }
                    }
                }
            }
        });
    }
}

// Grafik değişkenlerini dışa aktar
export const charts = {
    incomeExpenseChart: window.incomeExpenseChart,
    savingsChart: window.savingsChart
}; 