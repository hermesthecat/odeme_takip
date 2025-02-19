// Veri saklama modülü
import { PaymentDataAdapter, IncomeDataAdapter, SavingDataAdapter, BudgetGoalDataAdapter } from '../DataAdapter.js';

const paymentAdapter = new PaymentDataAdapter();
const incomeAdapter = new IncomeDataAdapter();
const savingAdapter = new SavingDataAdapter();
const budgetGoalAdapter = new BudgetGoalDataAdapter();

// Ödemeleri yükleme
export async function loadPayments() {
    try {
        const response = await paymentAdapter.get();
        return response?.data || [];
    } catch (error) {
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
        const data = await incomeAdapter.get();
        return data.data;
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
        const data = await savingAdapter.get();
        return data.data;
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
        const data = response.data;
        return {
            monthlyLimits: {},
            categories: [],
            total_months: data.total_months,
            months_with_budget: data.months_with_budget,
            average_budget: data.average_budget,
            months_within_budget: data.months_within_budget
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