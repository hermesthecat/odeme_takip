# CSS Styles

Bu klasör projenin tüm CSS stil dosyalarını içerir.

## Dosyalar

### `style.css`

- **Açıklama**: Ana stil dosyası
- **Kullanıldığı Sayfalar**: Çoğu sayfa (app.php, card.php, log.php, payment_power.php, admin.php)
- **İçerik**:
  - Global stiller
  - Component stilleri
  - Responsive tasarım kuralları
  - Bootstrap customization

### `login.css`

- **Açıklama**: Giriş sayfası stilleri
- **Kullanıldığı Sayfalar**: header.php (global)
- **İçerik**:
  - Login form stilleri
  - Authentication sayfası düzeni
  - Responsive login tasarımı

### `register.css`

- **Açıklama**: Kayıt sayfası stilleri
- **Kullanıldığı Sayfalar**: header.php (global)
- **İçerik**:
  - Registration form stilleri
  - Kullanıcı kayıt sayfası düzeni
  - Form validation gösterimi

### `profile.css`

- **Açıklama**: Profil sayfası stilleri
- **Kullanıldığı Sayfalar**: profile.php
- **İçerik**:
  - Kullanıcı profil düzeni
  - Settings form stilleri
  - Telegram integration UI

### `borsa.css`

- **Açıklama**: Borsa/portföy sayfası stilleri
- **Kullanıldığı Sayfalar**: borsa.php
- **İçerik**:
  - Portfolio table stilleri
  - Stock price display
  - Performance indicators
  - Chart integration styles

## Stil Mimarisi

### Naming Convention

- **BEM metodolojisi** kullanılıyor
- **Component-based** yaklaşım
- **Semantic class names**

### Responsive Design

- **Mobile-first** yaklaşım
- **Bootstrap integration**
- **Custom breakpoints**

### Theme Support

- **Light/Dark tema** desteği
- **CSS custom properties** kullanımı
- **Theme switching** JavaScript ile

## Teknolojiler

### Framework & Libraries

- **Bootstrap 5.3.0** - Ana CSS framework
- **Bootstrap Icons 1.11.3** - Icon set
- **SweetAlert2** - Modal ve alert stilleri
- **Font Awesome 6.4.0** - Ek iconlar

### CSS Özellikleri

- **Flexbox & Grid** layout
- **CSS Variables** tema desteği için
- **Media queries** responsive tasarım
- **Animations & Transitions**

## Development Guidelines

### Yeni Stil Ekleme

1. İlgili CSS dosyasını belirle
2. Component-specific stiller için yeni class'lar ekle
3. Global stiller için `style.css` kullan
4. Responsive kuralları ekle

### Performance

- **Critical CSS** inline olarak yükle
- **Non-critical** stilleri defer et
- **CSS minification** production'da
- **Unused CSS** kaldır

### Browser Support

- **Modern browsers** (Chrome, Firefox, Safari, Edge)
- **IE11** için fallback'ler
- **Mobile browsers** optimization

## File Organization

```
css/
├── style.css        # Ana stil dosyası
├── login.css        # Authentication stilleri
├── register.css     # Kayıt stilleri
├── profile.css      # Profil sayfası stilleri
├── borsa.css        # Portfolio stilleri
└── README.md        # Bu dokümantasyon
```

## Maintenance

### Regular Tasks

1. **CSS validation** düzenli olarak çalıştır
2. **Browser compatibility** test et
3. **Performance metrics** ölç
4. **Unused styles** temizle

### Updates

- Bootstrap güncellemelerini takip et
- Icon library'leri güncelle
- Browser support değişikliklerini kontrol et

## Integration Notes

### PHP Integration

CSS dosyaları PHP sayfalarında şu şekilde include ediliyor:

```html
<link rel="stylesheet" href="css/style.css" />
```

### JavaScript Integration

Theme switching ve dynamic styling için JavaScript ile etkileşim:

```javascript
// Theme switching
document.documentElement.setAttribute('data-theme', theme);
```

### Development Mode

Development'da değişiklikler real-time görülür, cache temizleme gerekebilir.

### Production Mode

Production'da CSS dosyaları minify edilmeli ve cache edilmeli.
