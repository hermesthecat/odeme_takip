/**
 * Modern Language Management System
 * Provides AJAX-based language switching with localStorage caching
 */
class LanguageManager {
    constructor() {
        this.currentLanguage = this.detectCurrentLanguage();
        this.translations = {};
        this.cachePrefix = 'pecunia_translations_';
        this.cacheExpiry = 3600000; // 1 hour in milliseconds
        this.availableLanguages = ['tr', 'en'];
        
        this.init();
    }
    
    /**
     * Initialize language manager
     */
    async init() {
        try {
            await this.loadTranslations(this.currentLanguage);
            this.bindEvents();
            this.updateUI();
        } catch (error) {
            console.error('Language Manager initialization failed:', error);
        }
    }
    
    /**
     * Detect current language from various sources
     */
    detectCurrentLanguage() {
        // Priority: URL param > localStorage > session > browser
        const urlParams = new URLSearchParams(window.location.search);
        const urlLang = urlParams.get('lang');
        
        if (urlLang && this.availableLanguages.includes(urlLang)) {
            return urlLang;
        }
        
        const storedLang = localStorage.getItem('pecunia_language');
        if (storedLang && this.availableLanguages.includes(storedLang)) {
            return storedLang;
        }
        
        // Fallback to browser language
        const browserLang = navigator.language.substr(0, 2);
        return this.availableLanguages.includes(browserLang) ? browserLang : 'tr';
    }
    
    /**
     * Load translations for specified language
     */
    async loadTranslations(language) {
        try {
            // Check cache first
            const cached = this.getCachedTranslations(language);
            if (cached) {
                this.translations = cached.translations;
                this.currentLanguage = language;
                return;
            }
            
            // Fetch from server
            const response = await fetch(`/api/translations.php?lang=${language}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.status === 'success') {
                this.translations = data.translations;
                this.currentLanguage = language;
                
                // Cache translations
                this.cacheTranslations(language, data);
                
                // Store language preference
                localStorage.setItem('pecunia_language', language);
            } else {
                throw new Error(data.message || 'Failed to load translations');
            }
        } catch (error) {
            console.error('Error loading translations:', error);
            // Fallback to existing translations if available
            if (Object.keys(this.translations).length === 0) {
                // Load emergency fallback translations
                this.loadFallbackTranslations();
            }
        }
    }
    
    /**
     * Get cached translations
     */
    getCachedTranslations(language) {
        try {
            const cacheKey = this.cachePrefix + language;
            const cached = localStorage.getItem(cacheKey);
            
            if (cached) {
                const data = JSON.parse(cached);
                const now = Date.now();
                
                if (now - data.timestamp < this.cacheExpiry) {
                    return data;
                }
                
                // Remove expired cache
                localStorage.removeItem(cacheKey);
            }
        } catch (error) {
            console.error('Error reading cache:', error);
        }
        
        return null;
    }
    
    /**
     * Cache translations
     */
    cacheTranslations(language, data) {
        try {
            const cacheKey = this.cachePrefix + language;
            const cacheData = {
                language: language,
                translations: data.translations,
                timestamp: Date.now()
            };
            
            localStorage.setItem(cacheKey, JSON.stringify(cacheData));
        } catch (error) {
            console.error('Error caching translations:', error);
        }
    }
    
    /**
     * Load emergency fallback translations
     */
    loadFallbackTranslations() {
        this.translations = {
            error: 'Error!',
            success: 'Success!',
            loading: 'Loading...',
            save: 'Save',
            cancel: 'Cancel',
            delete: 'Delete',
            edit: 'Edit',
            yes: 'Yes',
            no: 'No',
            confirm: 'Confirm'
        };
    }
    
    /**
     * Change language with AJAX
     */
    async changeLanguage(language) {
        if (!this.availableLanguages.includes(language)) {
            throw new Error('Invalid language: ' + language);
        }
        
        if (language === this.currentLanguage) {
            return; // No change needed
        }
        
        try {
            // Show loading indicator
            this.showLanguageLoading(true);
            
            // Send AJAX request to change language
            const formData = new FormData();
            formData.append('action', 'change_language');
            formData.append('language', language);
            
            const response = await fetch('/api.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (data.status === 'success') {
                // Load new translations
                await this.loadTranslations(language);
                
                // Update UI
                this.updateUI();
                
                // Trigger custom event for other components
                this.dispatchLanguageChangeEvent(language);
                
                // Show success message
                this.showMessage(this.t('settings.save_success'), 'success');
            } else {
                throw new Error(data.message || 'Failed to change language');
            }
        } catch (error) {
            console.error('Error changing language:', error);
            this.showMessage(this.t('settings.save_error'), 'error');
        } finally {
            this.showLanguageLoading(false);
        }
    }
    
    /**
     * Get translation by key
     */
    t(key, params = {}) {
        const keys = key.split('.');
        let value = this.translations;
        
        for (const k of keys) {
            if (value && typeof value === 'object' && k in value) {
                value = value[k];
            } else {
                return key; // Return key if translation not found
            }
        }
        
        // Replace parameters
        if (typeof value === 'string' && Object.keys(params).length > 0) {
            for (const [param, replacement] of Object.entries(params)) {
                value = value.replace(new RegExp(':' + param, 'g'), replacement);
            }
        }
        
        return value;
    }
    
    /**
     * Update UI elements with new translations
     */
    updateUI() {
        // Update language dropdown
        this.updateLanguageDropdown();
        
        // Update data-translate elements
        this.updateTranslatableElements();
        
        // Update document title if needed
        this.updateDocumentTitle();
    }
    
    /**
     * Update language dropdown in navbar
     */
    updateLanguageDropdown() {
        const dropdown = document.querySelector('#languageDropdown');
        if (dropdown) {
            const currentLangName = this.getCurrentLanguageName();
            dropdown.innerHTML = `
                <i class="bi bi-globe me-1"></i>
                ${currentLangName}
            `;
        }
        
        // Update dropdown items
        const dropdownItems = document.querySelectorAll('.language-option');
        dropdownItems.forEach(item => {
            const lang = item.dataset.lang;
            if (lang === this.currentLanguage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    /**
     * Update elements with data-translate attribute
     */
    updateTranslatableElements() {
        const elements = document.querySelectorAll('[data-translate]');
        elements.forEach(element => {
            const key = element.dataset.translate;
            const translation = this.t(key);
            
            if (element.tagName.toLowerCase() === 'input' && element.type === 'button') {
                element.value = translation;
            } else if (element.tagName.toLowerCase() === 'input' && element.placeholder !== undefined) {
                element.placeholder = translation;
            } else {
                element.textContent = translation;
            }
        });
    }
    
    /**
     * Update document title
     */
    updateDocumentTitle() {
        const titleElement = document.querySelector('[data-translate-title]');
        if (titleElement) {
            const key = titleElement.dataset.translateTitle;
            document.title = this.t(key);
        }
    }
    
    /**
     * Get current language name
     */
    getCurrentLanguageName() {
        const languageNames = {
            'tr': 'Türkçe',
            'en': 'English'
        };
        return languageNames[this.currentLanguage] || this.currentLanguage;
    }
    
    /**
     * Bind event listeners
     */
    bindEvents() {
        // Language dropdown clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('language-option')) {
                e.preventDefault();
                const language = e.target.dataset.lang;
                if (language) {
                    this.changeLanguage(language);
                }
            }
        });
        
        // Listen for language change events from other components
        document.addEventListener('languageChanged', (e) => {
            this.loadTranslations(e.detail.language);
        });
    }
    
    /**
     * Dispatch language change event
     */
    dispatchLanguageChangeEvent(language) {
        const event = new CustomEvent('languageChanged', {
            detail: {
                language: language,
                previousLanguage: this.currentLanguage
            }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Show loading indicator for language change
     */
    showLanguageLoading(show) {
        const dropdown = document.querySelector('#languageDropdown');
        if (dropdown) {
            if (show) {
                dropdown.innerHTML = `
                    <i class="bi bi-arrow-clockwise spin me-1"></i>
                    ${this.t('app.loading')}
                `;
            }
        }
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        // Use SweetAlert2 if available, otherwise console
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                text: message,
                icon: type === 'error' ? 'error' : 'success',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
    
    /**
     * Clear all cached translations
     */
    clearCache() {
        this.availableLanguages.forEach(lang => {
            const cacheKey = this.cachePrefix + lang;
            localStorage.removeItem(cacheKey);
        });
        localStorage.removeItem('pecunia_language');
    }
    
    /**
     * Get current language
     */
    getCurrentLanguage() {
        return this.currentLanguage;
    }
    
    /**
     * Get all translations
     */
    getTranslations() {
        return this.translations;
    }
}

// Global instance
let languageManager;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    languageManager = new LanguageManager();
    
    // Make global t() function available
    window.t = (key, params) => languageManager.t(key, params);
});

// CSS for loading animation
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);