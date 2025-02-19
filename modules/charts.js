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
    updateSavingsChart(period, selectedYear, selectedMonth);
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

// Birikimler grafiğini güncelle
async function updateSavingsChart(period = 'month', selectedYear, selectedMonth) {
    try {
        const savings = await loadSavings();
        console.log('Birikimler grafiği için veriler:', savings);

        if (!Array.isArray(savings)) {
            console.error('Birikimler verisi dizi değil:', savings);
            return;
        }

        // Verileri para birimine göre grupla
        const groupedData = savings.reduce((acc, saving) => {
            const currency = saving.currency || 'TRY';
            if (!acc[currency]) {
                acc[currency] = {
                    target: 0,
                    current: 0,
                    count: 0
                };
            }
            acc[currency].target += parseFloat(saving.target_amount || 0);
            acc[currency].current += parseFloat(saving.current_amount || 0);
            acc[currency].count++;
            return acc;
        }, {});

        // Grafik verilerini oluştur
        const labels = Object.keys(groupedData);
        const targetData = labels.map(currency => groupedData[currency].target);
        const currentData = labels.map(currency => groupedData[currency].current);

        // Grafik konfigürasyonu
        const config = {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Hedef Tutar',
                        data: targetData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Mevcut Tutar',
                        data: currentData,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Birikimler Durumu'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('tr-TR', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                }
            }
        };

        // Mevcut grafik varsa güncelle, yoksa yeni oluştur
        if (window.savingsChart) {
            window.savingsChart.data = config.data;
            window.savingsChart.update();
        } else {
            const ctx = document.getElementById('savingsChart')?.getContext('2d');
            if (ctx) {
                window.savingsChart = new Chart(ctx, config);
            }
        }

        console.log('Birikimler grafiği güncellendi');
    } catch (error) {
        console.error('Birikimler grafiği güncellenirken hata:', error);
    }
}

// Grafik değişkenlerini dışa aktar
export const charts = {
    incomeExpenseChart: window.incomeExpenseChart,
    savingsChart: window.savingsChart
};