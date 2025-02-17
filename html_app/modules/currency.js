// Döviz işlemleri modülü
import { EXCHANGE_RATES_KEY, LAST_UPDATE_KEY } from './storage.js';

export const TCMB_API_URL = 'https://api.exchangerate.host/live?source=TRY&access_key=4f467070688418cb9958422c637c880c';

// Para birimlerinin TL karşılıkları (sabit kur için)
export const EXCHANGE_RATES = {
    'TRY': 1,
    'USD': 30.50,
    'EUR': 33.20,
    'GBP': 38.70
};

// Döviz kurlarını güncelle
export async function updateExchangeRates() {
    try {
        const response = await fetch(TCMB_API_URL);
        const data = await response.json();

        if (data && data.rates) {
            const exchangeRates = {
                'TRY': 1,
                'USD': 1 / data.rates.TRYUSD,
                'EUR': 1 / data.rates.TRYEUR,
                'GBP': 1 / data.rates.TRYGBP
            };

            localStorage.setItem(EXCHANGE_RATES_KEY, JSON.stringify(exchangeRates));
            localStorage.setItem(LAST_UPDATE_KEY, new Date().toISOString());

            return exchangeRates;
        }
    } catch (error) {
        // Hata durumunda son kaydedilen kurları kullan
        const savedRates = localStorage.getItem(EXCHANGE_RATES_KEY);
        return savedRates ? JSON.parse(savedRates) : EXCHANGE_RATES;
    }
}

// Döviz kurlarını göster
export function showExchangeRates() {
    const ratesContainer = document.getElementById('exchangeRates');
    if (!ratesContainer) return;

    // Son güncelleme zamanını kontrol et
    const lastUpdate = localStorage.getItem(LAST_UPDATE_KEY);
    const lastUpdateDate = lastUpdate ? new Date(lastUpdate) : null;
    const now = new Date();

    // 24 saatten eski ise uyarı göster
    const isOld = lastUpdateDate && (now - lastUpdateDate) > 24 * 60 * 60 * 1000;

    // Kurları al
    const rates = localStorage.getItem(EXCHANGE_RATES_KEY);
    const exchangeRates = rates ? JSON.parse(rates) : EXCHANGE_RATES;

    const html = `
        <div class="card shadow-sm">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-dollar me-1"></i>
                            <span class="fw-bold">USD:</span>
                            <span class="ms-1">${exchangeRates.USD.toFixed(2)}₺</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-euro me-1"></i>
                            <span class="fw-bold">EUR:</span>
                            <span class="ms-1">${exchangeRates.EUR.toFixed(2)}₺</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-currency-pound me-1"></i>
                            <span class="fw-bold">GBP:</span>
                            <span class="ms-1">${exchangeRates.GBP.toFixed(2)}₺</span>
                        </div>
                    </div>
                    <small class="text-muted ${isOld ? 'text-danger' : ''}">
                        <i class="bi bi-clock-history me-1"></i>
                        Son güncelleme: ${lastUpdateDate ? lastUpdateDate.toLocaleString('tr-TR') : 'Bilinmiyor'}
                        ${isOld ? ' (Güncel değil!)' : ''}
                    </small>
                </div>
            </div>
        </div>
    `;

    ratesContainer.innerHTML = html;
}

// Tutarı TL'ye çevir
export function convertToTRY(amount, currency) {
    return amount * EXCHANGE_RATES[currency];
}

// Tutarı formatla
export function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
} 