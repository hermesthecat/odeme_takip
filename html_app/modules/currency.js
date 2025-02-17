// Döviz işlemleri modülü
import { EXCHANGE_RATES_KEY, LAST_UPDATE_KEY } from './storage.js';

export const CURRENCY_API_URL = 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/try.json';

// Döviz kurlarını güncelle
export async function updateExchangeRates() {
    try {
        const response = await fetch(CURRENCY_API_URL);
        const data = await response.json();

        if (data && data.try) {
            const exchangeRates = {
                'TRY': 1,
                'USD': 1 / data.try.usd,
                'EUR': 1 / data.try.eur,
                'GBP': 1 / data.try.gbp
            };

            localStorage.setItem(EXCHANGE_RATES_KEY, JSON.stringify(exchangeRates));
            localStorage.setItem(LAST_UPDATE_KEY, new Date().toISOString());

            return exchangeRates;
        }
        throw new Error('API yanıtı geçerli değil');
    } catch (error) {
        console.error('Döviz kurları güncellenirken hata:', error);
        // Hata durumunda son kaydedilen kurları kullan
        const savedRates = localStorage.getItem(EXCHANGE_RATES_KEY);
        if (!savedRates) {
            Swal.fire({
                icon: 'error',
                title: 'Döviz Kuru Hatası',
                text: 'Döviz kurları alınamadı ve kayıtlı kur verisi bulunamadı. Lütfen internet bağlantınızı kontrol edin.',
                showConfirmButton: true
            });
            return null;
        }
        return JSON.parse(savedRates);
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
    if (!rates) {
        ratesContainer.innerHTML = `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Döviz kurları yüklenemedi. Lütfen internet bağlantınızı kontrol edin.
            </div>
        `;
        return;
    }

    const exchangeRates = JSON.parse(rates);
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
    const rates = localStorage.getItem(EXCHANGE_RATES_KEY);
    if (!rates) {
        Swal.fire({
            icon: 'error',
            title: 'Döviz Kuru Hatası',
            text: 'Döviz kurları bulunamadı. Lütfen internet bağlantınızı kontrol edin.',
            showConfirmButton: true
        });
        return 0;
    }
    const exchangeRates = JSON.parse(rates);
    return amount * exchangeRates[currency];
}

// Tutarı formatla
export function formatMoney(amount) {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY'
    }).format(amount);
} 