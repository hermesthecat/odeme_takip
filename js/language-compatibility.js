/**
 * Language Compatibility Layer
 * Provides backward compatibility for pages not yet updated to modern language system
 */

// Global compatibility layer
document.addEventListener('DOMContentLoaded', function() {
    // If modern language manager is not available, create a fallback
    if (typeof languageManager === 'undefined') {
        console.log('Loading compatibility layer for language system');
        
        // Create minimal compatibility layer
        window.languageManager = {
            getCurrentLanguage: function() {
                return document.documentElement.lang || 'tr';
            },
            t: function(key, params = {}) {
                // Try to get from global translations object if available
                if (typeof translations !== 'undefined') {
                    return getTranslationFromObject(key, translations, params);
                }
                
                // Fallback: return key
                return key;
            }
        };
        
        // Make global t() function available
        window.t = window.languageManager.t;
    }
    
    // Auto-enhance pages that haven't been updated yet
    enhancePageForLanguages();
});

/**
 * Enhance current page for language support
 */
function enhancePageForLanguages() {
    // Add language switching capability to existing dropdowns
    enhanceLanguageDropdowns();
    
    // Add loading states
    addLanguageLoadingStates();
    
    // Enhance forms with language awareness
    enhanceFormsForLanguages();
}

/**
 * Enhance existing language dropdowns to work with AJAX
 */
function enhanceLanguageDropdowns() {
    const languageLinks = document.querySelectorAll('.language-option, a[href*="?lang="]');
    
    languageLinks.forEach(link => {
        // If this link doesn't have the data-lang attribute, add it
        if (!link.dataset.lang) {
            const href = link.getAttribute('href');
            const langMatch = href.match(/[?&]lang=([^&]+)/);
            if (langMatch) {
                link.dataset.lang = langMatch[1];
            }
        }
        
        // Add click handler for AJAX language switching
        link.addEventListener('click', function(e) {
            const targetLang = this.dataset.lang;
            
            // If modern language manager is available, use it
            if (typeof languageManager !== 'undefined' && languageManager.changeLanguage) {
                e.preventDefault();
                languageManager.changeLanguage(targetLang);
                return;
            }
            
            // Otherwise, try AJAX fallback
            if (targetLang && targetLang !== getCurrentPageLanguage()) {
                e.preventDefault();
                changeLanguageAjax(targetLang);
            }
        });
    });
}

/**
 * Simple AJAX language change for compatibility
 */
function changeLanguageAjax(language) {
    const formData = new FormData();
    formData.append('action', 'change_language');
    formData.append('language', language);
    
    // Show loading
    showLanguageChangeLoading(true);
    
    fetch('/api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Reload page to reflect language change
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('lang', language);
            window.location.href = currentUrl.toString();
        } else {
            throw new Error(data.message || 'Language change failed');
        }
    })
    .catch(error => {
        console.error('Language change error:', error);
        // Fallback to traditional page reload with URL parameter
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('lang', language);
        window.location.href = currentUrl.toString();
    })
    .finally(() => {
        showLanguageChangeLoading(false);
    });
}

/**
 * Get current page language
 */
function getCurrentPageLanguage() {
    return document.documentElement.lang || 'tr';
}

/**
 * Show/hide language change loading state
 */
function showLanguageChangeLoading(show) {
    const dropdown = document.querySelector('#languageDropdown');
    if (dropdown) {
        if (show) {
            dropdown.innerHTML = `
                <i class="bi bi-arrow-clockwise spin me-1"></i>
                ${getCurrentPageLanguage() === 'tr' ? 'Yükleniyor...' : 'Loading...'}
            `;
        }
    }
}

/**
 * Add loading states for language operations
 */
function addLanguageLoadingStates() {
    // Add CSS for loading animation if not already present
    if (!document.querySelector('#language-loading-styles')) {
        const style = document.createElement('style');
        style.id = 'language-loading-styles';
        style.textContent = `
            .spin {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            .language-loading {
                opacity: 0.7;
                pointer-events: none;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Enhance forms for language awareness
 */
function enhanceFormsForLanguages() {
    // Add language-aware validation messages
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Add language class for potential CSS styling
        form.classList.add('lang-' + getCurrentPageLanguage());
    });
}

/**
 * Get translation from nested object
 */
function getTranslationFromObject(key, obj, params = {}) {
    const keys = key.split('.');
    let value = obj;
    
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

// Export for global access
window.LanguageCompatibility = {
    enhancePageForLanguages,
    enhanceLanguageDropdowns,
    changeLanguageAjax,
    getCurrentPageLanguage
};