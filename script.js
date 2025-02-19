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
import * as navigation from './modules/navigation.js';

// Global fonksiyonları tanımla
window.previousMonth = navigation.previousMonth;
window.nextMonth = navigation.nextMonth;
window.updateDisplays = async () => {
    const selectedYear = parseInt(document.getElementById('yearSelect').value);
    const selectedMonth = parseInt(document.getElementById('monthSelect').value);

    try {
        await lists.updatePaymentList(selectedYear, selectedMonth);
        await lists.updateIncomeList(selectedYear, selectedMonth);
        await lists.updateSavingList(selectedYear, selectedMonth);
        await lists.updatePaymentPowerList(selectedYear, selectedMonth);
        await calculations.updateSummaryCards(selectedYear, selectedMonth);
        await charts.updateCharts(undefined, selectedYear, selectedMonth);
        await lists.updateBudgetGoalsDisplay(selectedYear, selectedMonth);
        await calendar.updateCalendar(selectedYear, selectedMonth);
    } catch (error) {
        console.error('Ekranlar güncellenirken hata:', error);
    }
};

// Ana sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
    // Tema ayarlarını uygula
    const currentTheme = theme.getCurrentTheme();
    theme.setTheme(currentTheme);

    // Ay seçiciyi başlat
    navigation.initializeMonthSelector();

    // Tooltip'leri aktif et
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Bütçe hedefleri butonları için event listener'ları ekle
    document.querySelector('button[data-action="update-monthly-limit"]')?.addEventListener('click', () => {
        modals.showUpdateBudgetGoalModal();
    });

    document.querySelector('button[data-action="add-category-goal"]')?.addEventListener('click', () => {
        modals.showAddCategoryGoalModal();
    });

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
        button.addEventListener('click', function () {
            const type = this.getAttribute('data-type');
            switch (type) {
                case 'income':
                    modals.showAddIncomeModal();
                    break;
                case 'saving':
                    modals.showAddSavingModal();
                    break;
                case 'payment':
                    // Kategorileri kontrol et
                    const goals = storage.loadBudgetGoals();
                    if (!goals.categories || goals.categories.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kategori Gerekli!',
                            text: 'Ödeme ekleyebilmek için önce en az bir kategori eklemelisiniz.',
                            confirmButtonText: 'Kategori Ekle',
                            showCancelButton: true,
                            cancelButtonText: 'İptal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                modals.showAddCategoryGoalModal();
                            }
                        });
                    } else {
                        modals.showAddPaymentModal();
                    }
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

        // İlk yükleme için seçili ay/yıl değerlerini al
        const selectedYear = parseInt(document.getElementById('yearSelect').value);
        const selectedMonth = parseInt(document.getElementById('monthSelect').value);

        // Diğer güncellemeler
        updateDisplays();

        // Her saat başı kurları güncelle
        setInterval(async () => {
            await currency.updateExchangeRates();
            currency.showExchangeRates();
            calculations.updateSummaryCards(selectedYear, selectedMonth);
        }, 60 * 60 * 1000); // 1 saat
    });
});
