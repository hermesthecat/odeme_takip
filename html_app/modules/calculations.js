// Hesaplama işlemleri modülü
import { loadPayments, loadIncomes } from './storage.js';
import { convertToTRY, formatMoney } from './currency.js';

// Belirli bir ay için gelir ve giderleri hesapla
export function calculateMonthlyBalance(year, month) {
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);

    let totalIncome = 0;
    let totalExpense = 0;

    // Gelirleri hesapla
    const incomes = loadIncomes();
    incomes.forEach(income => {
        const firstDate = new Date(income.firstIncomeDate);

        if (income.frequency === '0') {
            // Tek seferlik gelir
            if (firstDate.getFullYear() === year && firstDate.getMonth() === month) {
                totalIncome += convertToTRY(income.amount, income.currency);
            }
        } else {
            // Tekrarlı gelir
            let currentDate = new Date(firstDate);

            // İlk tarihi ayın başına getir
            while (currentDate > startDate) {
                currentDate.setMonth(currentDate.getMonth() - parseInt(income.frequency));
            }

            // Sonraki ödeme tarihini bul
            while (currentDate <= startDate) {
                currentDate.setMonth(currentDate.getMonth() + parseInt(income.frequency));
            }

            // Eğer bu ay içindeyse ekle
            if (currentDate <= endDate) {
                totalIncome += convertToTRY(income.amount, income.currency);
            }
        }
    });

    // Giderleri hesapla
    const payments = loadPayments();
    payments.forEach(payment => {
        const firstDate = new Date(payment.firstPaymentDate);

        if (payment.frequency === '0') {
            // Tek seferlik ödeme
            if (firstDate.getFullYear() === year && firstDate.getMonth() === month) {
                totalExpense += convertToTRY(payment.amount, payment.currency);
            }
        } else {
            // Tekrarlı ödeme
            let currentDate = new Date(firstDate);
            let repeatCounter = 0;

            // İlk tarihi ayın başına getir
            while (currentDate > startDate) {
                currentDate.setMonth(currentDate.getMonth() - parseInt(payment.frequency));
            }

            // Sonraki ödeme tarihini bul
            while (currentDate <= startDate) {
                currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
                repeatCounter++;
            }

            // Tekrar sayısı kontrolü
            if (payment.repeatCount === null || repeatCounter <= payment.repeatCount) {
                // Eğer bu ay içindeyse ekle
                if (currentDate <= endDate) {
                    totalExpense += convertToTRY(payment.amount, payment.currency);
                }
            }
        }
    });

    return {
        income: totalIncome,
        expense: totalExpense,
        balance: totalIncome - totalExpense
    };
}

// Kategori bazlı harcamaları hesapla
export function calculateCategoryExpenses(year, month) {
    const startDate = new Date(year, month, 1);
    const endDate = new Date(year, month + 1, 0);
    const expenses = {};

    // Ödemeleri kategorilere göre topla
    const payments = loadPayments();
    payments.forEach(payment => {
        if (!payment.category) return;

        const firstDate = new Date(payment.firstPaymentDate);
        let amount = 0;

        if (payment.frequency === '0') {
            // Tek seferlik ödeme
            if (firstDate.getFullYear() === year && firstDate.getMonth() === month) {
                amount = convertToTRY(payment.amount, payment.currency);
            }
        } else {
            // Tekrarlı ödeme
            let currentDate = new Date(firstDate);

            // İlk tarihi ayın başına getir
            while (currentDate > startDate) {
                currentDate.setMonth(currentDate.getMonth() - parseInt(payment.frequency));
            }

            // Sonraki ödeme tarihini bul
            while (currentDate <= startDate) {
                currentDate.setMonth(currentDate.getMonth() + parseInt(payment.frequency));
            }

            // Eğer bu ay içindeyse ekle
            if (currentDate <= endDate) {
                amount = convertToTRY(payment.amount, payment.currency);
            }
        }

        if (amount > 0) {
            expenses[payment.category] = (expenses[payment.category] || 0) + amount;
        }
    });

    return expenses;
}

// Özet kartlarını güncelle
export function updateSummaryCards(selectedYear = new Date().getFullYear(), selectedMonth = new Date().getMonth()) {
    const balance = calculateMonthlyBalance(selectedYear, selectedMonth);

    // Gelir kartını güncelle
    const incomeElement = document.getElementById('monthlyIncome');
    if (incomeElement) {
        incomeElement.textContent = formatMoney(balance.income);
        incomeElement.classList.toggle('text-success', balance.income > 0);
    }

    // Gider kartını güncelle
    const expenseElement = document.getElementById('monthlyExpense');
    if (expenseElement) {
        expenseElement.textContent = formatMoney(balance.expense);
        expenseElement.classList.toggle('text-danger', balance.expense > 0);
    }

    // Net durum kartını güncelle
    const balanceElement = document.getElementById('monthlyBalance');
    if (balanceElement) {
        balanceElement.textContent = formatMoney(balance.balance);
        balanceElement.classList.toggle('text-success', balance.balance > 0);
        balanceElement.classList.toggle('text-danger', balance.balance < 0);
    }

    // Dönem kartını güncelle
    const periodElement = document.getElementById('currentPeriod');
    if (periodElement) {
        const date = new Date(selectedYear, selectedMonth);
        periodElement.textContent = new Intl.DateTimeFormat('tr-TR', {
            year: 'numeric',
            month: 'long'
        }).format(date);
    }
} 