// Tema yönetimi modülü
export const THEME_KEY = 'theme';

export function getCurrentTheme() {
    return localStorage.getItem(THEME_KEY) || 'light';
}

export function setTheme(theme) {
    localStorage.setItem(THEME_KEY, theme);
    document.documentElement.setAttribute('data-theme', theme);
    updateChartTheme(theme);
}

export function toggleTheme() {
    const currentTheme = getCurrentTheme();
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);

    // Tema değişikliği bildirimini göster
    Swal.fire({
        icon: 'success',
        title: newTheme === 'dark' ? 'Karanlık Mod Aktif' : 'Aydınlık Mod Aktif',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        background: newTheme === 'dark' ? '#2d2d2d' : '#ffffff',
        color: newTheme === 'dark' ? '#ffffff' : '#2c3e50'
    });
}

export function updateChartTheme(theme) {
    const chartOptions = {
        plugins: {
            legend: {
                labels: {
                    color: theme === 'dark' ? '#ffffff' : '#2c3e50'
                }
            }
        },
        scales: {
            x: {
                grid: {
                    color: theme === 'dark' ? '#404040' : '#dee2e6'
                },
                ticks: {
                    color: theme === 'dark' ? '#ffffff' : '#2c3e50'
                }
            },
            y: {
                grid: {
                    color: theme === 'dark' ? '#404040' : '#dee2e6'
                },
                ticks: {
                    color: theme === 'dark' ? '#ffffff' : '#2c3e50'
                }
            }
        }
    };

    // Mevcut grafikleri güncelle
    if (window.incomeExpenseChart) {
        window.incomeExpenseChart.options = { ...window.incomeExpenseChart.options, ...chartOptions };
        window.incomeExpenseChart.update();
    }
    if (window.savingsChart) {
        window.savingsChart.options = { ...window.savingsChart.options, ...chartOptions };
        window.savingsChart.update();
    }
} 