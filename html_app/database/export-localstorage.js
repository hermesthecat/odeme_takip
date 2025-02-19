// Script to export localStorage data to JSON file
(function exportLocalStorage() {
    const data = {
        payments: JSON.parse(localStorage.getItem('payments') || '[]'),
        incomes: JSON.parse(localStorage.getItem('incomes') || '[]'),
        savings: JSON.parse(localStorage.getItem('savings') || '[]'),
        budgetGoals: JSON.parse(localStorage.getItem('budgetGoals') || '{}'),
        exchangeRates: JSON.parse(localStorage.getItem('exchangeRates') || '{}')
    };

    // Create a Blob with the JSON data
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);

    // Create download link
    const a = document.createElement('a');
    a.href = url;
    a.download = 'localstorage-export.json';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
})();