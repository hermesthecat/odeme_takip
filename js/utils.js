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

// HTML escape fonksiyonu
function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') {
        return unsafe;
    }
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Sayısal değerleri formatla
function formatNumber(number, decimals = 2) {
    if (typeof number !== 'number') {
        number = parseFloat(number);
    }
    if (isNaN(number)) {
        return '0.00';
    }
    return number.toFixed(decimals);
}

// Güvenli HTML oluştur
function createSafeHtml(template, data) {
    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            let value = data[key];
            // Sayısal değerler için formatlama
            if (key.includes('amount') || key.includes('total')) {
                value = formatNumber(value);
            }
            // Diğer değerler için HTML escape
            else if (typeof value === 'string') {
                value = escapeHtml(value);
            }
            template = template.replace(new RegExp('\\${' + key + '}', 'g'), value);
        }
    }
    return template;
}

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
        const fieldTranslation = t(`utils.form.${field}`) || field;

        for (const rule of fieldRules) {
            let isValid = true;
            let errorMessage = '';

            switch (rule.type) {
                case 'required':
                    isValid = validationRules.required(value);
                    errorMessage = rule.message || t('utils.validation.required', {field: fieldTranslation});
                    break;
                case 'numeric':
                    isValid = validationRules.numeric(value);
                    errorMessage = rule.message || t('utils.validation.numeric', {field: fieldTranslation});
                    break;
                case 'date':
                    isValid = validationRules.date(value);
                    errorMessage = rule.message || t('utils.validation.date', {field: fieldTranslation});
                    break;
                case 'currency':
                    isValid = validationRules.currency(value);
                    errorMessage = rule.message || t('utils.validation.currency', {field: fieldTranslation});
                    break;
                case 'frequency':
                    isValid = validationRules.frequency(value);
                    errorMessage = rule.message || t('utils.validation.frequency', {field: fieldTranslation});
                    break;
                case 'minValue':
                    isValid = validationRules.minValue(value, rule.value);
                    errorMessage = rule.message || t('utils.validation.min_value', {field: fieldTranslation, min: rule.value});
                    break;
                case 'maxValue':
                    isValid = validationRules.maxValue(value, rule.value);
                    errorMessage = rule.message || t('utils.validation.max_value', {field: fieldTranslation, max: rule.value});
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
        // Gelir formları için
        'add_income': {
            name: [
                { type: 'required' }
            ],
            amount: [
                { type: 'required' },
                { type: 'numeric' },
                { type: 'minValue', value: 0 }
            ],
            currency: [
                { type: 'required' },
                { type: 'currency' }
            ],
            first_date: [
                { type: 'required' },
                { type: 'date' }
            ],
            frequency: [
                { type: 'required' },
                { type: 'frequency' }
            ]
        },
        // Ödeme formları için
        'add_payment': {
            name: [
                { type: 'required' }
            ],
            amount: [
                { type: 'required' },
                { type: 'numeric' },
                { type: 'minValue', value: 0 }
            ],
            currency: [
                { type: 'required' },
                { type: 'currency' }
            ],
            first_date: [
                { type: 'required' },
                { type: 'date' }
            ],
            frequency: [
                { type: 'required' },
                { type: 'frequency' }
            ]
        },
        // Birikim formları için
        'add_saving': {
            name: [
                { type: 'required' }
            ],
            target_amount: [
                { type: 'required' },
                { type: 'numeric' },
                { type: 'minValue', value: 0 }
            ],
            currency: [
                { type: 'required' },
                { type: 'currency' }
            ],
            start_date: [
                { type: 'required' },
                { type: 'date' }
            ],
            target_date: [
                { type: 'required' },
                { type: 'date' }
            ]
        }
    };

    // Validasyon kurallarını kontrol et
    if (validationRules[data.action]) {
        const errors = validateForm(data, validationRules[data.action]);
        if (errors.length > 0) {
            return $.Deferred().reject(new Error('Validasyon hatası')).always(() => {
                Swal.fire({
                    icon: 'error',
                    title: t('utils.validation.error_title'),
                    html: errors.join('<br>'),
                    confirmButtonText: t('utils.validation.confirm_button')
                });
            });
        }
    }

    // CSRF token ekle (eğer mevcut değilse)
    if (!data.csrf_token && typeof window.csrfToken !== 'undefined') {
        data.csrf_token = window.csrfToken;
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
            if (response.message) {
                Swal.fire({
                    icon: 'error',
                    title: t('error'),
                    text: escapeHtml(response.message),
                    confirmButtonText: t('utils.validation.confirm_button')
                }).then(() => {
                    window.location.href = 'login.php';
                });
            }
            return Promise.reject(response);
        }
        return response;
    });
}

// Tekrarlama sıklığı metni
function getFrequencyText(frequency) {
    return t(`utils.frequency.${frequency}`) || frequency;
}

// URL'den tarih parametrelerini al
function getDateFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const month = parseInt(urlParams.get('month')) || new Date().getMonth();
    const year = parseInt(urlParams.get('year')) || new Date().getFullYear();
    return { month, year };
}

// URL'i güncelle
function updateUrl(month, year) {
    const url = new URL(window.location.href);
    url.searchParams.set('month', month);
    url.searchParams.set('year', year);
    window.history.pushState({}, '', url);
}

function formatMyMoney(price) {

    var currency_symbol = "₺"

    var formattedOutput = new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 2,
    });

    return formattedOutput.format(price).replace(currency_symbol, '')
}