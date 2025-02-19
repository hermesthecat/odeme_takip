// Base adapter class for database operations
export class DataAdapter {
    constructor(endpoint) {
        this.endpoint = endpoint;
    }

    async request(method, path, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`${this.endpoint}${path}`, options);
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'API request failed');
        }

        return response.json();
    }

    async get(path) {
        return this.request('GET', path);
    }

    async post(path, data) {
        return this.request('POST', path, data);
    }

    async put(path, data) {
        return this.request('PUT', path, data);
    }

    async delete(path) {
        return this.request('DELETE', path);
    }
}

// Specific adapters for each entity type
export class PaymentAdapter extends DataAdapter {
    constructor() {
        super('/api/payments');
    }

    async getMonthlyPayments(year, month) {
        return this.get(`/monthly/${year}/${month}`);
    }

    async createPayment(payment) {
        return this.post('/', payment);
    }

    async updatePayment(id, payment) {
        return this.put(`/${id}`, payment);
    }

    async deletePayment(id) {
        return this.delete(`/${id}`);
    }

    async updateStatus(id, year, month, isPaid) {
        return this.put(`/${id}/status`, { year, month, isPaid });
    }
}

export class IncomeAdapter extends DataAdapter {
    constructor() {
        super('/api/incomes');
    }

    async getMonthlyIncomes(year, month) {
        return this.get(`/monthly/${year}/${month}`);
    }

    async createIncome(income) {
        return this.post('/', income);
    }

    async updateIncome(id, income) {
        return this.put(`/${id}`, income);
    }

    async deleteIncome(id) {
        return this.delete(`/${id}`);
    }
}

export class SavingAdapter extends DataAdapter {
    constructor() {
        super('/api/savings');
    }

    async getActiveSavings() {
        return this.get('/active');
    }

    async createSaving(saving) {
        return this.post('/', saving);
    }

    async updateSaving(id, saving) {
        return this.put(`/${id}`, saving);
    }

    async updateProgress(id, amount) {
        return this.put(`/${id}/progress`, { amount });
    }

    async deleteSaving(id) {
        return this.delete(`/${id}`);
    }
}

export class CategoryAdapter extends DataAdapter {
    constructor() {
        super('/api/categories');
    }

    async getCategories() {
        return this.get('/');
    }

    async getCategoryWithSpending(id, year, month) {
        return this.get(`/${id}/spending/${year}/${month}`);
    }

    async createCategory(category) {
        return this.post('/', category);
    }

    async updateCategory(id, category) {
        return this.put(`/${id}`, category);
    }

    async deleteCategory(id) {
        return this.delete(`/${id}`);
    }
}

export class BudgetGoalAdapter extends DataAdapter {
    constructor() {
        super('/api/budget-goals');
    }

    async getMonthlyBudget(year, month) {
        return this.get(`/monthly/${year}/${month}`);
    }

    async setMonthlyBudget(year, month, limit) {
        return this.post('/monthly', { year, month, limit });
    }

    async getYearlySummary(year) {
        return this.get(`/yearly/${year}`);
    }
}

export class ExchangeRateAdapter extends DataAdapter {
    constructor() {
        super('/api/exchange-rates');
    }

    async getRate(baseCurrency, targetCurrency) {
        return this.get(`/${baseCurrency}/${targetCurrency}`);
    }

    async getCurrentRates() {
        return this.get('/current');
    }

    async convert(amount, fromCurrency, toCurrency) {
        return this.get(`/convert/${fromCurrency}/${toCurrency}/${amount}`);
    }
}