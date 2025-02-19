class DataAdapter {
    constructor(endpoint) {
        this.endpoint = endpoint;
    }

    async getAll() {
        try {
            console.log('Fetching from:', this.endpoint);
            const response = await fetch(this.endpoint, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('API Response:', data);
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async get(id) {
        try {
            let url = this.endpoint;
            if (id) {
                url = `${this.endpoint}/${id}`;
            }
            console.log('Fetching from:', url);
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            console.log('API Response:', data);
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async create(data) {
        try {
            console.log('Creating at:', this.endpoint, 'with data:', data);
            const response = await fetch(this.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            console.log('API Response:', result);
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async update(id, data) {
        try {
            console.log('Updating at:', `${this.endpoint}/${id}`, 'with data:', data);
            const response = await fetch(`${this.endpoint}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            console.log('API Response:', result);
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    async delete(id) {
        try {
            console.log('Deleting at:', `${this.endpoint}/${id}`);
            const response = await fetch(`${this.endpoint}/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            console.log('API Response:', result);
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
}

class PaymentDataAdapter extends DataAdapter {
    constructor() {
        super('/payments/index.php');
    }
}

class IncomeDataAdapter extends DataAdapter {
    constructor() {
        super('/incomes/index.php');
    }
}

class SavingDataAdapter extends DataAdapter {
    constructor() {
        super('/savings/index.php');
    }
}

class CategoryDataAdapter extends DataAdapter {
    constructor() {
        super('/categories/index.php');
    }
}

class BudgetGoalDataAdapter extends DataAdapter {
    constructor() {
        super('/budget-goals/index.php');
    }
}

class ExchangeRateDataAdapter extends DataAdapter {
    constructor() {
        super('/exchange-rates/index.php');
    }
}

export {
    PaymentDataAdapter,
    IncomeDataAdapter,
    SavingDataAdapter,
    CategoryDataAdapter,
    BudgetGoalDataAdapter,
    ExchangeRateDataAdapter
};