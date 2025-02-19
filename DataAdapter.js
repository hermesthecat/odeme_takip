class DataAdapter {
    constructor(endpoint) {
        this.endpoint = endpoint;
    }

    async getAll() {
        const response = await fetch(this.endpoint);
        return response.json();
    }

    async get(id) {
        let url = this.endpoint;
        if (id) {
            url = `${this.endpoint}/${id}`;
        }
        const response = await fetch(url);
        return response.json();
    }

    async create(data) {
        const response = await fetch(this.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    async update(id, data) {
        const response = await fetch(`${this.endpoint}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        return response.json();
    }

    async delete(id) {
        const response = await fetch(`${this.endpoint}/${id}`, {
            method: 'DELETE'
        });
        return response.json();
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