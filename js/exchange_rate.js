/**
 * Exchange Rate Management JavaScript
 * Kur yönetimi için JavaScript fonksiyonları
 */

// Kur bilgisini göster ve yenileme butonu ekle
function addExchangeRateRefreshButton(fromCurrency, toCurrency, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Mevcut kur bilgisini al
    fetch(`api/exchange_rate_refresh.php?from_currency=${fromCurrency}&to_currency=${toCurrency}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const rateInfo = document.createElement('div');
                rateInfo.className = 'exchange-rate-info d-flex align-items-center';
                rateInfo.innerHTML = `
                    <div class="me-3">
                        <small class="text-muted">
                            Kur: 1 ${fromCurrency} = ${data.data.rate} ${toCurrency}
                            <br>
                            <span class="text-${data.data.cache_age_minutes > 30 ? 'warning' : 'success'}">
                                ${data.data.cache_age_minutes} dakika önce güncellendi
                            </span>
                        </small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                            onclick="refreshExchangeRate('${fromCurrency}', '${toCurrency}', '${containerId}')"
                            title="${t('exchange.refresh_tooltip')}">
                        <i class="fa-solid fa-refresh"></i>
                    </button>
                `;
                
                container.appendChild(rateInfo);
            }
        })
        .catch(error => {
            console.error('Exchange rate info error:', error);
        });
}

// Kur bilgisini yenile
function refreshExchangeRate(fromCurrency, toCurrency, containerId) {
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    
    // Loading state
    button.disabled = true;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    
    const formData = new FormData();
    formData.append('from_currency', fromCurrency);
    formData.append('to_currency', toCurrency);
    
    fetch('api/exchange_rate_refresh.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                title: t('exchange.success'),
                text: `Kur bilgisi güncellendi: 1 ${fromCurrency} = ${data.data.rate} ${toCurrency}`,
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
            
            // Sayfayı yenile veya ilgili alanları güncelle
            location.reload();
        } else {
            Swal.fire({
                title: t('exchange.error'),
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Exchange rate refresh error:', error);
        Swal.fire({
            title: t('exchange.error'),
            text: t('exchange.update_error'),
            icon: 'error'
        });
    })
    .finally(() => {
        // Reset button state
        button.disabled = false;
        button.innerHTML = originalHTML;
    });
}

// Otomatik kur yaş kontrolü - sayfada kur bilgisi varsa kontrol et
function checkExchangeRateAge() {
    const exchangeRateElements = document.querySelectorAll('[data-exchange-rate]');
    
    exchangeRateElements.forEach(element => {
        const fromCurrency = element.dataset.fromCurrency;
        const toCurrency = element.dataset.toCurrency;
        
        if (fromCurrency && toCurrency) {
            fetch(`api/exchange_rate_refresh.php?from_currency=${fromCurrency}&to_currency=${toCurrency}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.data.cache_age_minutes > 60) {
                        // 1 saatten eski kur varsa uyarı göster
                        const warningBadge = document.createElement('span');
                        warningBadge.className = 'badge bg-warning ms-2';
                        warningBadge.innerHTML = 'Eski Kur';
                        warningBadge.title = 'Kur bilgisi 1 saatten eski. Yenilemek için tıklayın.';
                        warningBadge.style.cursor = 'pointer';
                        warningBadge.onclick = () => refreshExchangeRate(fromCurrency, toCurrency, element.id);
                        
                        element.appendChild(warningBadge);
                    }
                });
        }
    });
}

// Sayfa yüklendiğinde kur yaşını kontrol et
document.addEventListener('DOMContentLoaded', function() {
    checkExchangeRateAge();
});