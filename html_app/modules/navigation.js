// Ay navigasyon fonksiyonları
export function previousMonth() {
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    let currentMonth = parseInt(monthSelect.value);
    let currentYear = parseInt(yearSelect.value);

    if (currentMonth === 0) {
        currentMonth = 11;
        currentYear--;
        if (yearSelect.querySelector(`option[value="${currentYear}"]`)) {
            yearSelect.value = currentYear;
        }
    } else {
        currentMonth--;
    }

    monthSelect.value = currentMonth;
    updateDisplays();

    // Animasyon efekti
    const selector = document.querySelector('.month-selector');
    selector.style.transform = 'translateX(-10px)';
    selector.style.opacity = '0.8';
    setTimeout(() => {
        selector.style.transform = 'translateX(0)';
        selector.style.opacity = '1';
    }, 200);
}

export function nextMonth() {
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    let currentMonth = parseInt(monthSelect.value);
    let currentYear = parseInt(yearSelect.value);

    if (currentMonth === 11) {
        currentMonth = 0;
        currentYear++;
        if (yearSelect.querySelector(`option[value="${currentYear}"]`)) {
            yearSelect.value = currentYear;
        }
    } else {
        currentMonth++;
    }

    monthSelect.value = currentMonth;
    updateDisplays();

    // Animasyon efekti
    const selector = document.querySelector('.month-selector');
    selector.style.transform = 'translateX(10px)';
    selector.style.opacity = '0.8';
    setTimeout(() => {
        selector.style.transform = 'translateX(0)';
        selector.style.opacity = '1';
    }, 200);
}

// Sayfa yüklendiğinde mevcut ayı seç
export function initializeMonthSelector() {
    const currentDate = new Date();
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    // Yıl seçeneklerini oluştur
    const currentYear = currentDate.getFullYear();
    yearSelect.innerHTML = '';
    for (let year = currentYear - 1; year <= currentYear + 2; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }

    // Mevcut ay ve yılı seç
    monthSelect.value = currentDate.getMonth();
    yearSelect.value = currentYear;

    // Event listener'ları ekle
    monthSelect.addEventListener('change', () => {
        updateDisplays();
        animateMonthChange();
    });

    yearSelect.addEventListener('change', () => {
        updateDisplays();
        animateMonthChange();
    });
}

function animateMonthChange() {
    const selector = document.querySelector('.month-selector');
    selector.style.transform = 'scale(0.95)';
    selector.style.opacity = '0.8';
    setTimeout(() => {
        selector.style.transform = 'scale(1)';
        selector.style.opacity = '1';
    }, 200);
}