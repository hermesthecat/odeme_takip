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

// Global validation configuration loaded from server
let validationConfig = null;

// Load validation configuration from server
async function loadValidationConfig() {
    if (validationConfig !== null) {
        return validationConfig;
    }
    
    try {
        const response = await $.ajax({
            url: 'api/validation_rules.php',
            type: 'GET',
            dataType: 'json'
        });
        
        if (response.status === 'success') {
            validationConfig = response.data;
            return validationConfig;
        }
    } catch (error) {
        console.warn('Could not load validation config from server, using fallback');
    }
    
    // Fallback configuration if server request fails
    validationConfig = {
        income: {
            name: { required: true, type: "string", min_length: 1, max_length: 100 },
            amount: { required: true, type: "numeric", min_value: 0.01, max_value: 999999999.99 },
            currency: { required: true, type: "enum", values: ["TRY", "USD", "EUR", "GBP"] },
            first_date: { required: true, type: "date", format: "Y-m-d" },
            frequency: { required: true, type: "enum", values: ["none", "monthly", "bimonthly", "quarterly", "fourmonthly", "fivemonthly", "sixmonthly", "yearly"] }
        },
        payment: {
            name: { required: true, type: "string", min_length: 1, max_length: 100 },
            amount: { required: true, type: "numeric", min_value: 0.01, max_value: 999999999.99 },
            currency: { required: true, type: "enum", values: ["TRY", "USD", "EUR", "GBP"] },
            first_date: { required: true, type: "date", format: "Y-m-d" },
            frequency: { required: true, type: "enum", values: ["none", "monthly", "bimonthly", "quarterly", "fourmonthly", "fivemonthly", "sixmonthly", "yearly"] }
        },
        saving: {
            name: { required: true, type: "string", min_length: 1, max_length: 100 },
            target_amount: { required: true, type: "numeric", min_value: 0.01, max_value: 999999999.99 },
            currency: { required: true, type: "enum", values: ["TRY", "USD", "EUR", "GBP"] },
            start_date: { required: true, type: "date", format: "Y-m-d" },
            target_date: { required: true, type: "date", format: "Y-m-d" }
        }
    };
    
    return validationConfig;
}

// Validation functions using server configuration
const validationRules = {
    required: (value) => {
        return value !== undefined && value !== null && value.toString().trim() !== '';
    },
    numeric: (value) => {
        return !isNaN(parseFloat(value)) && isFinite(value);
    },
    integer: (value) => {
        return Number.isInteger(parseFloat(value));
    },
    date: (value) => {
        return !isNaN(Date.parse(value));
    },
    enum: (value, allowedValues) => {
        return allowedValues.includes(value);
    },
    minValue: (value, min) => {
        return parseFloat(value) >= min;
    },
    maxValue: (value, max) => {
        return parseFloat(value) <= max;
    },
    minLength: (value, min) => {
        return value.toString().length >= min;
    },
    maxLength: (value, max) => {
        return value.toString().length <= max;
    },
    dateRange: (startDate, endDate) => {
        return new Date(startDate) <= new Date(endDate);
    }
};

// Unified form validation using server configuration
async function validateForm(formData, formType) {
    const errors = [];
    const config = await loadValidationConfig();
    
    if (!config[formType]) {
        console.warn(`Unknown form type: ${formType}`);
        return errors;
    }
    
    const formRules = config[formType];
    
    for (const fieldName in formRules) {
        const fieldRules = formRules[fieldName];
        const value = formData[fieldName];
        const fieldTranslation = t(`utils.form.${fieldName}`) || fieldName;
        
        // Required validation
        if (fieldRules.required && !validationRules.required(value)) {
            errors.push(t('utils.validation.required', {field: fieldTranslation}) || `${fieldTranslation} is required`);
            continue;
        }
        
        // Skip other validations if value is empty and not required
        if (!validationRules.required(value) && !fieldRules.required) {
            continue;
        }
        
        // Type validation
        switch (fieldRules.type) {
            case 'numeric':
                if (!validationRules.numeric(value)) {
                    errors.push(t('utils.validation.numeric', {field: fieldTranslation}) || `${fieldTranslation} must be numeric`);
                    continue;
                }
                break;
                
            case 'integer':
                if (!validationRules.integer(value)) {
                    errors.push(t('utils.validation.integer', {field: fieldTranslation}) || `${fieldTranslation} must be an integer`);
                    continue;
                }
                break;
                
            case 'date':
                if (!validationRules.date(value)) {
                    errors.push(t('utils.validation.date', {field: fieldTranslation}) || `${fieldTranslation} must be a valid date`);
                    continue;
                }
                break;
                
            case 'enum':
                if (!validationRules.enum(value, fieldRules.values)) {
                    errors.push(t('utils.validation.enum', {field: fieldTranslation}) || `${fieldTranslation} has invalid value`);
                    continue;
                }
                break;
        }
        
        // Min/Max value validation
        if (fieldRules.min_value !== undefined && !validationRules.minValue(value, fieldRules.min_value)) {
            errors.push(t('utils.validation.min_value', {field: fieldTranslation, min: fieldRules.min_value}) || 
                       `${fieldTranslation} must be at least ${fieldRules.min_value}`);
        }
        
        if (fieldRules.max_value !== undefined && !validationRules.maxValue(value, fieldRules.max_value)) {
            errors.push(t('utils.validation.max_value', {field: fieldTranslation, max: fieldRules.max_value}) || 
                       `${fieldTranslation} must be at most ${fieldRules.max_value}`);
        }
        
        // String length validation
        if (fieldRules.min_length !== undefined && !validationRules.minLength(value, fieldRules.min_length)) {
            errors.push(t('utils.validation.min_length', {field: fieldTranslation, min: fieldRules.min_length}) || 
                       `${fieldTranslation} must be at least ${fieldRules.min_length} characters`);
        }
        
        if (fieldRules.max_length !== undefined && !validationRules.maxLength(value, fieldRules.max_length)) {
            errors.push(t('utils.validation.max_length', {field: fieldTranslation, max: fieldRules.max_length}) || 
                       `${fieldTranslation} must be at most ${fieldRules.max_length} characters`);
        }
    }
    
    return errors;
}

// AJAX istekleri için yardımcı fonksiyon
function ajaxRequest(data) {
    // Form type mapping for validation
    const actionToFormType = {
        'add_income': 'income',
        'update_income': 'income',
        'add_payment': 'payment', 
        'update_payment': 'payment',
        'add_saving': 'saving',
        'update_saving': 'saving',
        'add_card': 'card',
        'update_card': 'card',
        'update_user_settings': 'user_settings'
    };

    // Perform validation using unified system
    const formType = actionToFormType[data.action];
    if (formType) {
        return validateForm(data, formType).then(errors => {
            if (errors.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: t('utils.validation.error_title') || 'Validation Error',
                    html: errors.join('<br>'),
                    confirmButtonText: t('utils.validation.confirm_button') || 'OK'
                });
                return Promise.reject(new Error('Validation failed'));
            }
            
            // Validation passed, proceed with AJAX request
            return performAjaxRequest(data);
        }).catch(error => {
            if (error.message !== 'Validation failed') {
                console.error('Validation error:', error);
            }
            return Promise.reject(error);
        });
    }
    
    // No validation rules for this action, proceed directly
    return performAjaxRequest(data);
}

// Separated AJAX request logic
function performAjaxRequest(data) {
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
                    title: t('error') || 'Error',
                    text: escapeHtml(response.message),
                    confirmButtonText: t('utils.validation.confirm_button') || 'OK'
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
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