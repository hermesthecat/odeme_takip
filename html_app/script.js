/**
 * @author A. Kerem Gök
 */

// Modülleri import et
import * as theme from './modules/theme.js';
import * as storage from './modules/storage.js';
import * as currency from './modules/currency.js';
import * as utils from './modules/utils.js';
import * as charts from './modules/charts.js';
import * as calculations from './modules/calculations.js';
import * as modals from './modules/modals/index.js';
import * as calendar from './modules/calendar.js';
import * as lists from './modules/lists/index.js';

// Ana sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
    // Tema ayarlarını uygula
    const currentTheme = theme.getCurrentTheme();
    theme.setTheme(currentTheme);

    // Tooltip'leri aktif et
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Tema değiştirme butonunu ekle
    const navButtons = document.querySelector('.d-flex.justify-content-between div');
    if (navButtons) {
        const themeButton = document.createElement('button');
        themeButton.className = 'btn theme-toggle-btn ms-2';
        themeButton.innerHTML = `<i class="bi bi-${currentTheme === 'dark' ? 'sun' : 'moon'}"></i>`;
        themeButton.onclick = theme.toggleTheme;
        navButtons.appendChild(themeButton);
    }

    // Grafik periyodu butonları için event listener ekle
    document.querySelectorAll('.chart-period-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            // Aktif sınıfını güncelle
            document.querySelectorAll('.chart-period-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            e.target.classList.add('active');

            // Grafikleri güncelle
            charts.updateCharts(e.target.dataset.period);
        });
    });

    // Modal açma butonları için event listener ekle
    document.querySelectorAll('.add-modal-btn').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            switch (type) {
                case 'income':
                    modals.showAddIncomeModal();
                    break;
                case 'saving':
                    modals.showAddSavingModal();
                    break;
                case 'payment':
                    modals.showAddPaymentModal();
                    break;
            }
        });
    });

    // Veri import/export butonları için event listener ekle
    document.querySelector('button[data-action="export-data"]')?.addEventListener('click', utils.exportData);
    document.querySelector('button[data-action="import-data"]')?.addEventListener('click', utils.importData);

    // Sayfa yüklendiğinde gerekli güncellemeleri yap
    window.addEventListener('load', async function () {
        // Döviz kurlarını güncelle ve göster
        await currency.updateExchangeRates();
        currency.showExchangeRates();

        // Diğer güncellemeler
        lists.updatePaymentList();
        lists.updateIncomeList();
        lists.updateSavingList();
        calendar.updateCalendar();
        calculations.updateSummaryCards();
        charts.updateCharts();
        lists.updateBudgetGoalsDisplay();

        // Her saat başı kurları güncelle
        setInterval(async () => {
            await currency.updateExchangeRates();
            currency.showExchangeRates();
            calculations.updateSummaryCards();
        }, 60 * 60 * 1000); // 1 saat
    });
});
