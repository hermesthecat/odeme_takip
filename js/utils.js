// Form verilerini objeye çeviren yardımcı fonksiyon
$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

// Validasyon fonksiyonları
const validationRules = {
    required: (value) => {
        return value !== undefined && value !== null && value.toString().trim() !== '';
    },
    numeric: (value) => {
        return !isNaN(parseFloat(value)) && isFinite(value);
    },
    date: (value) => {
        return !isNaN(Date.parse(value));
    },
    currency: (value) => {
        return ['TRY', 'USD', 'EUR', 'GBP'].includes(value);
    },
    frequency: (value) => {
        return ['none', 'monthly', 'bimonthly', 'quarterly', 'fourmonthly', 'fivemonthly', 'sixmonthly', 'yearly'].includes(value);
    },
    minValue: (value, min) => {
        return parseFloat(value) >= min;
    },
    maxValue: (value, max) => {
        return parseFloat(value) <= max;
    },
    dateRange: (startDate, endDate) => {
        return new Date(startDate) <= new Date(endDate);
    }
};

// Form validasyonu
function validateForm(formData, rules) {
    const errors = [];

    for (const field in rules) {
        const fieldRules = rules[field];
        const value = formData[field];

        for (const rule of fieldRules) {
            let isValid = true;
            let errorMessage = '';

            switch (rule.type) {
                case 'required':
                    isValid = validationRules.required(value);
                    errorMessage = rule.message || `${field} alanı zorunludur`;
                    break;
                case 'numeric':
                    isValid = validationRules.numeric(value);
                    errorMessage = rule.message || `${field} alanı sayısal olmalıdır`;
                    break;
                case 'date':
                    isValid = validationRules.date(value);
                    errorMessage = rule.message || `${field} alanı geçerli bir tarih olmalıdır`;
                    break;
                case 'currency':
                    isValid = validationRules.currency(value);
                    errorMessage = rule.message || `${field} alanı geçerli bir para birimi olmalıdır`;
                    break;
                case 'frequency':
                    isValid = validationRules.frequency(value);
                    errorMessage = rule.message || `${field} alanı geçerli bir tekrarlama sıklığı olmalıdır`;
                    break;
                case 'minValue':
                    isValid = validationRules.minValue(value, rule.value);
                    errorMessage = rule.message || `${field} alanı en az ${rule.value} olmalıdır`;
                    break;
                case 'maxValue':
                    isValid = validationRules.maxValue(value, rule.value);
                    errorMessage = rule.message || `${field} alanı en fazla ${rule.value} olmalıdır`;
                    break;
            }

            if (!isValid) {
                errors.push(errorMessage);
            }
        }
    }

    return errors;
}

// AJAX istekleri için yardımcı fonksiyon
function ajaxRequest(data) {
    // İstek öncesi validasyon kuralları
    const validationRules = {
        // Gelir/Gider formları için
        'add_income': {
            name: [
                { type: 'required', message: 'Gelir adı zorunludur' }
            ],
            amount: [
                { type: 'required', message: 'Tutar zorunludur' },
                { type: 'numeric', message: 'Tutar sayısal olmalıdır' },
                { type: 'minValue', value: 0, message: 'Tutar 0\'dan büyük olmalıdır' }
            ],
            currency: [
                { type: 'required', message: 'Para birimi zorunludur' },
                { type: 'currency', message: 'Geçerli bir para birimi seçiniz' }
            ],
            first_date: [
                { type: 'required', message: 'Tarih zorunludur' },
                { type: 'date', message: 'Geçerli bir tarih giriniz' }
            ],
            frequency: [
                { type: 'required', message: 'Tekrarlama sıklığı zorunludur' },
                { type: 'frequency', message: 'Geçerli bir tekrarlama sıklığı seçiniz' }
            ]
        },
        'add_payment': {
            name: [
                { type: 'required', message: 'Ödeme adı zorunludur' }
            ],
            amount: [
                { type: 'required', message: 'Tutar zorunludur' },
                { type: 'numeric', message: 'Tutar sayısal olmalıdır' },
                { type: 'minValue', value: 0, message: 'Tutar 0\'dan büyük olmalıdır' }
            ],
            currency: [
                { type: 'required', message: 'Para birimi zorunludur' },
                { type: 'currency', message: 'Geçerli bir para birimi seçiniz' }
            ],
            first_date: [
                { type: 'required', message: 'Tarih zorunludur' },
                { type: 'date', message: 'Geçerli bir tarih giriniz' }
            ],
            frequency: [
                { type: 'required', message: 'Tekrarlama sıklığı zorunludur' },
                { type: 'frequency', message: 'Geçerli bir tekrarlama sıklığı seçiniz' }
            ]
        },
        'add_saving': {
            name: [
                { type: 'required', message: 'Birikim adı zorunludur' }
            ],
            target_amount: [
                { type: 'required', message: 'Hedef tutar zorunludur' },
                { type: 'numeric', message: 'Hedef tutar sayısal olmalıdır' },
                { type: 'minValue', value: 0, message: 'Hedef tutar 0\'dan büyük olmalıdır' }
            ],
            current_amount: [
                { type: 'required', message: 'Mevcut tutar zorunludur' },
                { type: 'numeric', message: 'Mevcut tutar sayısal olmalıdır' },
                { type: 'minValue', value: 0, message: 'Mevcut tutar 0\'dan büyük olmalıdır' }
            ],
            currency: [
                { type: 'required', message: 'Para birimi zorunludur' },
                { type: 'currency', message: 'Geçerli bir para birimi seçiniz' }
            ],
            start_date: [
                { type: 'required', message: 'Başlangıç tarihi zorunludur' },
                { type: 'date', message: 'Geçerli bir başlangıç tarihi giriniz' }
            ],
            target_date: [
                { type: 'required', message: 'Hedef tarihi zorunludur' },
                { type: 'date', message: 'Geçerli bir hedef tarihi giriniz' }
            ]
        }
    };

    // Validasyon kurallarını kontrol et
    if (validationRules[data.action]) {
        const errors = validateForm(data, validationRules[data.action]);
        if (errors.length > 0) {
            return new Promise((resolve, reject) => {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasyon Hatası',
                    html: errors.join('<br>'),
                    confirmButtonText: 'Tamam'
                });
                reject(new Error('Validasyon hatası'));
            });
        }
    }

    // AJAX isteği
    return $.ajax({
        url: 'api.php',
        type: 'POST',
        data: data,
        dataType: 'json'
    }).then(function (response) {
        if (response.status === 'error') {
            // Token hatası kontrolü
            if (response.message && response.message.includes('Geçersiz güvenlik tokeni')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oturum Hatası',
                    text: 'Oturumunuz sonlanmış. Sayfa yenilenecek.',
                    confirmButtonText: 'Tamam'
                }).then(() => {
                    window.location.reload();
                });
                throw new Error('Token hatası');
            }
            throw new Error(response.message);
        }
        return response;
    });
}

// Tekrarlama sıklığı çevirisi
function getFrequencyText(frequency) {
    const frequencies = {
        'none': 'Tekrar Yok',
        'monthly': 'Aylık',
        'bimonthly': '2 Ayda Bir',
        'quarterly': '3 Ayda Bir',
        'fourmonthly': '4 Ayda Bir',
        'fivemonthly': '5 Ayda Bir',
        'sixmonthly': '6 Ayda Bir',
        'yearly': 'Yıllık'
    };
    return frequencies[frequency] || frequency;
}

// URL'den ay ve yıl bilgisini al
function getDateFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const month = params.get('month');
    const year = params.get('year');

    if (month !== null && year !== null) {
        return { month: parseInt(month), year: parseInt(year) };
    }

    // URL'de tarih yoksa mevcut ay/yıl
    const now = new Date();
    return { month: now.getMonth(), year: now.getFullYear() };
}

// URL'i güncelle
function updateUrl(month, year) {
    const url = new URL(window.location.href);
    url.searchParams.set('month', month);
    url.searchParams.set('year', year);
    window.history.pushState({}, '', url);
} 