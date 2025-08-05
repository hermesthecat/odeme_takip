# Modern Language System - Kullanım Rehberi

## Genel Bakış

Pecunia projesi artık modern AJAX tabanlı dil değişim sistemi ile güçlendirilmiştir. Bu sistem:

- ✅ Sayfa yenileme olmadan dil değişimi
- ✅ localStorage ile önbellekleme
- ✅ Otomatik çeviri güncelleme
- ✅ Veritabanında kullanıcı dil tercihi kaydetme

## Yeni Özellikler

### 1. AJAX Dil Değişimi

```javascript
// Programatik dil değişimi
languageManager.changeLanguage('en');

// Event listener ile
document.addEventListener('languageChanged', (e) => {
    console.log('Language changed to:', e.detail.language);
});
```

### 2. Merkezi Çeviri Yönetimi

```javascript
// Global t() fonksiyonu
const message = t('income.add_success');
const parameterized = t('validation.field_required', {field: 'Name'});
```

### 3. Otomatik UI Güncelleme

```html
<!-- Bu elementler otomatik güncellenir -->
<span data-translate="income.title"></span>
<input data-translate="save" type="button">
<input placeholder="" data-translate="income.name">
```

## API Endpoints

### `/api.php` - Dil Değişimi

```javascript
const formData = new FormData();
formData.append('action', 'change_language');
formData.append('language', 'en');

fetch('/api.php', {
    method: 'POST',
    body: formData
});
```

### `/api/translations.php` - Çeviri Verisi

```javascript
const response = await fetch('/api/translations.php?lang=tr');
const data = await response.json();
console.log(data.translations);
```

## Yeni Dosyalar

### 1. `/js/language.js`

Modern language manager sınıfı:

- LanguageManager class
- localStorage önbellekleme
- AJAX dil değişimi
- UI otomatik güncelleme

### 2. `/api/translations.php`

Merkezi çeviri API'si:

- Frontend için optimize edilmiş çeviriler  
- JSON format
- Cache headers (1 saat)

### 3. `/api/change_language.php` (opsiyonel)

Standalone dil değişim endpoint'i (ana API'ye de entegre edildi)

### 4. `/database/add_language_column.sql`

Veritabanı güncelleme scripti:

```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS language VARCHAR(5) DEFAULT 'tr';
```

## Geliştirici Kullanımı

### Yeni Sayfaya Language.js Ekleme

```html
<!-- Bootstrap, SweetAlert2 vb. sonrasında -->
<script src="js/language.js"></script>
<script src="js/your-page.js"></script>
```

### HTML'de Çevirilebilir Elementler

```html
<!-- Otomatik çeviri -->
<h1 data-translate="page.title"></h1>
<button data-translate="save" class="btn btn-primary"></button>

<!-- Placeholder çevirisi -->
<input data-translate="income.name" placeholder="">

<!-- Title çevirisi -->
<div data-translate-title="page.title"></div>
```

### JavaScript'te Çeviri Kullanımı

```javascript
// Modern kullanım (language.js yüklendikten sonra)
const message = t('income.add_success');

// Parametreli çeviri
const error = t('validation.field_required', {field: 'Income Name'});

// Dil değişim eventi
document.addEventListener('languageChanged', (e) => {
    // UI güncelleme kodları
    updateCustomElements();
});
```

## Mevcut Sistem ile Uyumluluk

### Geri Uyumluluk

- Eski `?lang=tr` URL parametresi hala çalışır
- Mevcut `t()` fonksiyonu korundu
- Navbar dropdown mevcut görünümde

### Hibrit Yaklaşım

- Server-side rendering korundu
- AJAX enhancement eklendi
- Progressive enhancement prensibi

## Veritabanı Değişikleri

### Users Tablosu

```sql
-- Yeni kolon
language VARCHAR(5) DEFAULT 'tr'

-- Index
idx_users_language ON users(language)
```

### Kullanıcı Dil Tercihi

- Kayıt sırasında para birimine göre otomatik set
- Dil değişiminde veritabanında güncelleme
- Oturum açarken kullanıcı tercihini yükleme

## Test Senaryoları

### 1. Temel Dil Değişimi

1. Navbar'da dil dropdown'ını tıkla
2. Farklı dil seç
3. Sayfa yenilenmeden çevirilerin güncellenmesini kontrol et

### 2. Önbellek Kontrolü

1. Developer Tools > Application > Local Storage
2. `pecunia_translations_tr` ve `pecunia_translations_en` kayıtlarını kontrol et
3. 1 saat sonra otomatik temizlenmeyi test et

### 3. Offline/Error Handling

1. Network tab'ında requests'i blockla
2. Dil değişimi dene
3. Fallback çevirilerin çalışmasını kontrol et

## Performans Optimizasyonları

### Önbellekleme Stratejisi

- **Client**: localStorage (1 saat TTL)
- **Server**: HTTP Cache headers (1 saat)
- **Database**: User language preference indexing

### Lazy Loading

- Çeviriler sadece gerektiğinde yüklenir
- İlk sayfa yüklemede mevcut dil
- Dil değişiminde AJAX ile yeni çeviriler

### Memory Management

- Cache temizleme fonksiyonu
- Expired entries otomatik silinir
- Browser storage limit kontrolü

## Troubleshooting

### Yaygın Sorunlar

1. **Çeviriler yüklenmiyor**

   ```javascript
   // Console'da kontrol et
   console.log(languageManager.getTranslations());
   ```

2. **Dil değişimi çalışmıyor**

   ```javascript
   // Event listener kontrolü
   languageManager.clearCache(); // Cache temizle
   location.reload(); // Sayfayı yenile
   ```

3. **localStorage dolu**

   ```javascript
   languageManager.clearCache(); // Tüm cache temizle
   ```

### Debug Modları

```javascript
// Language manager durumu
console.log(languageManager.getCurrentLanguage());
console.log(languageManager.getTranslations());

// Cache durumu
console.log(localStorage.getItem('pecunia_translations_tr'));
```

## Gelecek Geliştirmeler

### Planlanan Özellikler

- [ ] Daha fazla dil desteği (Almanca, Fransızca)
- [ ] RTL dil desteği (Arapça, İbranice)
- [ ] Date/number format yerelleştirmesi
- [ ] Dinamik dil dosyası yükleme
- [ ] Admin panel ile çeviri yönetimi

### API Genişletmeleri

- [ ] `/api/languages.php` - Kullanılabilir diller
- [ ] `/api/translation-stats.php` - Çeviri istatistikleri
- [ ] `/api/missing-translations.php` - Eksik çeviriler

## Sonuç

Modern language system başarıyla entegre edildi. Sistem:

- Performanslı (localStorage cache)
- Kullanıcı dostu (seamless switching)
- Developer friendly (modern API)
- Backward compatible (eski sistem korundu)

Herhangi bir sorun durumunda bu rehbere başvurabilir veya debug araçlarını kullanabilirsiniz.
