// Yardımcı fonksiyonlar modülü
import { loadIncomes, loadPayments, loadSavings, loadBudgetGoals, saveIncomes, savePayments, saveSavings, saveBudgetGoals, EXCHANGE_RATES_KEY, LAST_UPDATE_KEY } from './storage.js';
import { THEME_KEY } from './theme.js';

// Sonraki ödeme tarihini hesaplama
export function calculateNextPaymentDate(firstPaymentDate, frequency, repeatCount = null) {
    const firstDate = new Date(firstPaymentDate);
    const today = new Date();

    if (frequency === '0') return firstDate;

    let nextDate = new Date(firstDate);
    let repeatCounter = 0;

    while (nextDate <= today) {
        nextDate.setMonth(nextDate.getMonth() + parseInt(frequency));
        repeatCounter++;

        // Tekrar sayısı dolmuşsa son tarihi döndür
        if (repeatCount !== null && repeatCounter >= repeatCount) {
            return nextDate;
        }
    }

    // Tekrar sayısı kontrolü
    if (repeatCount !== null) {
        const futureDate = new Date(firstDate);
        const totalMonths = (repeatCount - 1) * parseInt(frequency);
        futureDate.setMonth(futureDate.getMonth() + totalMonths);

        // Eğer sonraki tarih, son tekrar tarihinden sonraysa
        if (nextDate > futureDate) {
            return futureDate;
        }
    }

    return nextDate;
}

// Tarihi formatla
export function formatDate(date) {
    return new Date(date).toLocaleDateString('tr-TR');
}

// İlerleme yüzdesini hesapla
export function calculateProgress(targetAmount, currentAmount) {
    return Math.min(100, Math.round((currentAmount / targetAmount) * 100));
}

// Tekrarlama sıklığı metnini alma
export function getFrequencyText(frequency) {
    const frequencies = {
        '0': 'Tekrar Yok',
        '1': 'Her Ay',
        '2': '2 Ayda Bir',
        '3': '3 Ayda Bir',
        '4': '4 Ayda Bir',
        '5': '5 Ayda Bir',
        '6': '6 Ayda Bir',
        '12': 'Yıllık'
    };
    return frequencies[frequency] || '';
}

// Veri export/import fonksiyonları
export function exportData() {
    const data = {
        incomes: loadIncomes(),
        payments: loadPayments(),
        savings: loadSavings(),
        budgetGoals: loadBudgetGoals(),
        exchangeRates: localStorage.getItem(EXCHANGE_RATES_KEY),
        lastUpdate: localStorage.getItem(LAST_UPDATE_KEY),
        theme: localStorage.getItem(THEME_KEY),
        exportDate: new Date().toISOString()
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `butce_verileri_${formatDate(new Date())}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    // Başarılı mesajı göster
    Swal.fire({
        icon: 'success',
        title: 'Başarılı!',
        text: 'Veriler başarıyla dışa aktarıldı.',
        showConfirmButton: false,
        timer: 1500
    });
}

export function importData() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';

    input.onchange = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (event) => {
            try {
                const data = JSON.parse(event.target.result);

                // Ana verileri kaydet
                if (data.incomes) saveIncomes(data.incomes);
                if (data.payments) savePayments(data.payments);
                if (data.savings) saveSavings(data.savings);
                if (data.budgetGoals) saveBudgetGoals(data.budgetGoals);

                // Ek verileri kaydet
                if (data.exchangeRates) localStorage.setItem(EXCHANGE_RATES_KEY, data.exchangeRates);
                if (data.lastUpdate) localStorage.setItem(LAST_UPDATE_KEY, data.lastUpdate);
                if (data.theme) localStorage.setItem(THEME_KEY, data.theme);

                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    html: `
                        Veriler başarıyla içe aktarıldı.<br>
                        <small class="text-muted">Dışa aktarım tarihi: ${new Date(data.exportDate).toLocaleString('tr-TR')}</small>
                    `,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.reload();
                });
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: 'Dosya içeriği geçerli değil!'
                });
            }
        };
        reader.readAsText(file);
    };

    input.click();
}

// Event listener'ları ayarla
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button[data-action]').forEach(button => {
        button.addEventListener('click', (e) => {
            const action = e.target.closest('button').dataset.action;
            switch (action) {
                case 'export-data':
                    exportData();
                    break;
                case 'import-data':
                    importData();
                    break;
            }
        });
    });
}); 