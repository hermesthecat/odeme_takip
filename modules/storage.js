// Veri saklama modülü
import { PaymentDataAdapter, IncomeDataAdapter, SavingDataAdapter, BudgetGoalDataAdapter } from '../DataAdapter.js';

const paymentAdapter = new PaymentDataAdapter();
const incomeAdapter = new IncomeDataAdapter();
const savingAdapter = new SavingDataAdapter();
const budgetGoalAdapter = new BudgetGoalDataAdapter();

// Ödemeleri yükleme
export async function loadPayments() {
    try {
        console.log('Ödemeler yükleniyor...');
        const response = await paymentAdapter.getAll();
        console.log('API yanıtı:', response);

        if (response?.success && Array.isArray(response.data)) {
            console.log(`${response.data.length} adet ödeme yüklendi:`, response.data);
            return response.data;
        }
        
        console.error('API yanıtı geçersiz format:', response);
        return [];
    } catch (error) {
        console.error('Ödemeler yüklenirken hata:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Ödemeler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Ödemeleri kaydetme
export async function savePayments(payments) {
    try {
        await paymentAdapter.create(payments);
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
export async function loadIncomes() {
    try {
        console.log('Gelirler yükleniyor...');
        const response = await incomeAdapter.get();
        console.log('API yanıtı:', response);

        if (response?.success && Array.isArray(response.data)) {
            console.log(`${response.data.length} adet gelir yüklendi`);
            return response.data;
        }

        console.error('Gelirler API yanıtı geçersiz format:', response);
        return [];
    } catch (error) {
        console.error('Gelirler yüklenirken hata:', error);
        Swal.fire({
            icon: 'error',
            title: 'Hata!',
            text: 'Gelirler yüklenirken hata oluştu: ' + error.message
        });
        return [];
    }
}

// Gelirleri kaydetme
export async function saveIncomes(incomes) {
    try {
        await incomeAdapter.create(incomes);
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
export async function loadSavings() {
    try {
        const response = await savingAdapter.get();
        if (response?.success && Array.isArray(response.data)) {
            return response.data;
        }
        console.error('Birikimler API yanıtı geçersiz format:', response);
        return [];
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
export async function saveSavings(savings) {
    try {
        await savingAdapter.create(savings);
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
export async function loadBudgetGoals() {
    try {
        const response = await budgetGoalAdapter.get();
        if (response?.success && response.data) {
            const data = response.data;
            return {
                monthlyLimits: data.monthlyLimits || {},
                categories: data.categories || [],
                total_months: data.total_months,
                months_with_budget: data.months_with_budget,
                average_budget: data.average_budget,
                months_within_budget: data.months_within_budget
            };
        }
        console.error('Bütçe hedefleri API yanıtı geçersiz format:', response);
        return {
            monthlyLimits: {},
            categories: []
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
export async function saveBudgetGoals(goals) {
    try {
        await budgetGoalAdapter.create(goals);
        return true;
    } catch (error) {
        console.error('Bütçe hedefleri kaydedilirken hata:', error);
        return false;
    }
}