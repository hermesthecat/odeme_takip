// Veri saklama modülü
export const STORAGE_KEY = 'payments';
export const INCOME_STORAGE_KEY = 'incomes';
export const SAVING_STORAGE_KEY = 'savings';
export const EXCHANGE_RATES_KEY = 'exchangeRates';
export const LAST_UPDATE_KEY = 'lastExchangeUpdate';
export const BUDGET_GOALS_KEY = 'budgetGoals';

// LocalStorage'dan ödemeleri yükleme
export function loadPayments() {
    try {
        const payments = localStorage.getItem(STORAGE_KEY);
        return payments ? JSON.parse(payments) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödemeler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// LocalStorage'a ödemeleri kaydetme
export function savePayments(payments) {
    try {
        const data = JSON.stringify(payments);
        localStorage.setItem(STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödemeler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Gelirleri yükleme
export function loadIncomes() {
    try {
        const incomes = localStorage.getItem(INCOME_STORAGE_KEY);
        return incomes ? JSON.parse(incomes) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelirler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Gelirleri kaydetme
export function saveIncomes(incomes) {
    try {
        const data = JSON.stringify(incomes);
        localStorage.setItem(INCOME_STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelirler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Birikimleri yükleme
export function loadSavings() {
    try {
        const savings = localStorage.getItem(SAVING_STORAGE_KEY);
        return savings ? JSON.parse(savings) : [];
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Birikimleri kaydetme
export function saveSavings(savings) {
    try {
        const data = JSON.stringify(savings);
        localStorage.setItem(SAVING_STORAGE_KEY, data);
        return true;
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Birikimler kaydedilirken hata oluştu: ' + error.message
        });
        return false;
    }
}

// Bütçe hedeflerini yükle
export function loadBudgetGoals() {
    try {
        const goals = localStorage.getItem(BUDGET_GOALS_KEY);
        const defaultGoals = {
            monthlyLimits: {},  // Her ay için ayrı limit
            categories: []
        };

        if (!goals) return defaultGoals;

        const parsedGoals = JSON.parse(goals);

        // Eski yapıdan yeni yapıya geçiş için kontrol
        if (parsedGoals.monthlyExpenseLimit !== undefined) {
            // Eski yapıdaki limiti tüm aylar için varsayılan olarak ata
            const oldLimit = parsedGoals.monthlyExpenseLimit;
            parsedGoals.monthlyLimits = {};

            // Şu anki yıl için tüm aylara eski limiti ata
            const currentYear = new Date().getFullYear();
            for (let month = 0; month < 12; month++) {
                const monthKey = `${currentYear}-${String(month + 1).padStart(2, '0')}`;
                parsedGoals.monthlyLimits[monthKey] = oldLimit;
            }

            // Eski alanı sil
            delete parsedGoals.monthlyExpenseLimit;

            // Yeni yapıyı kaydet
            localStorage.setItem(BUDGET_GOALS_KEY, JSON.stringify(parsedGoals));
        }

        return {
            ...defaultGoals,
            ...parsedGoals
        };
    } catch (error) {
        console.error('Bütçe hedefleri yüklenirken hata:', error);
        return {
            monthlyLimits: {},
            categories: []
        };
    }
}

// Bütçe hedeflerini kaydet
export function saveBudgetGoals(goals) {
    try {
        localStorage.setItem(BUDGET_GOALS_KEY, JSON.stringify(goals));
        return true;
    } catch (error) {
        console.error('Bütçe hedefleri kaydedilirken hata:', error);
        return false;
    }
} 