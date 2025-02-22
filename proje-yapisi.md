# Bütçe Yönetim Sistemi Proje Yapısı
*Author: A. Kerem Gök*

## 1. Kök Dizin

### Konfigürasyon Dosyaları
- `.gitignore`: Git versiyon kontrol sistemi için yoksayılacak dosyaların listesi.
    - `.env`: Ortam değişkenlerini içeren dosya (API anahtarları, veritabanı bilgileri vb.)
    - `/vendor/`: Composer ile yüklenen bağımlılıkların bulunduğu dizin.
    - `/logs/*`: Uygulama tarafından oluşturulan log dosyalarının bulunduğu dizin.
    - `/data/*`: Uygulama tarafından kullanılan veri dosyalarının bulunduğu dizin.
    - `.cache/`: Önbellek dosyalarının bulunduğu dizin.
    - `.idea/`: IntelliJ IDEA IDE'si tarafından oluşturulan proje dosyalarının bulunduğu dizin.
    - `.vscode/`: VS Code IDE'si tarafından oluşturulan proje dosyalarının bulunduğu dizin.
    - `/node_modules/`: Node.js projelerinde kullanılan bağımlılıkların bulunduğu dizin.
- `.htaccess`: Apache web sunucusu konfigürasyon dosyası.
    - CGI scriptlerini çalıştırma ayarları.
    - URL yönlendirme ve güvenlik ayarları.
- `config.php`: Ana konfigürasyon dosyası
    - Veritabanı bağlantısı (MySQL/PDO)
    - Oturum yönetimi
    - Dil sistemi yapılandırması
    - Para birimleri konfigürasyonu (TRY, USD, EUR, GBP)
    - Site meta bilgileri (isim, açıklama, anahtar kelimeler)

### Ana Sistem Dosyaları
- `app.php`: Ana uygulama arayüzü
    - Oturum kontrolü ve kimlik doğrulama
    - Frontend kütüphaneleri entegrasyonu
        - Bootstrap 5.3.0
        - jQuery 3.6.0
        - Bootstrap Icons
        - SweetAlert2
    - Dil çevirileri için JavaScript yapılandırması
    - Dashboard panelleri
        - Aylık özet kartları (gelir, gider, net bakiye, dönem)
        - Gelirler tablosu
        - Birikimler tablosu
        - Ödemeler tablosu
        - Ödeme gücü analiz tablosu
    - Modal pencere entegrasyonları
    - Responsive tasarım yapısı
- `index.php`: Uygulamanın giriş noktası
    - Ana sayfa içeriği (hero bölümü, özellikler, yorumlar, CTA)
    - Navbar ve footer bileşenlerinin dahil edilmesi
- `database.sql`: Veritabanı şema dosyası

### Kimlik Doğrulama
- `login.php`: Giriş sayfası
    - Kullanıcı adı ve şifre ile giriş yapma formu
    - "Beni hatırla" özelliği
    - Kayıt sayfasına yönlendirme bağlantısı
- `register.php`: Kayıt sayfası
    - Kullanıcı adı, şifre ve para birimi seçimi ile kayıt olma formu
    - Giriş sayfasına yönlendirme bağlantısı
- `login.css`: Giriş sayfası stilleri
    - Giriş formu ve logo stilleri
    - Arka plan ve genel düzen stilleri
- `register.css`: Kayıt sayfası stilleri
    - Kayıt formu ve logo stilleri
    - Arka plan ve genel düzen stilleri

### Sayfa Bileşenleri
- `header.php`: Sayfa üst kısmı (head elementi)
    - Meta etiketleri (karakter seti, viewport, başlık, açıklama, anahtar kelimeler, yazar)
    - CSS stilleri (Bootstrap, Bootstrap Icons, SweetAlert2, özel stiller)
- `navbar.php`: Navigasyon çubuğu
    - Site logosu ve başlığı
    - Menü bağlantıları (özellikler, yorumlar, giriş, kayıt)
    - Dil seçimi menüsü
- `footer.php`: Sayfa alt kısmı
    - JavaScript kütüphaneleri (jQuery, Bootstrap, SweetAlert2)
    - Özel JavaScript dosyaları (auth.js)
- `footer_body.php`: Body kapanışından önce yüklenen bileşenler
    - Footer içeriği (site adı, açıklama, bağlantılar, iletişim bilgileri, telif hakkı)
- `modals.php`: Modal pencereler için ana dosya
    - Gelir, gider, birikim ve kullanıcı ayarları modal pencerelerinin dahil edilmesi
    - Modal pencerelerinin davranışlarını kontrol eden JavaScript kodları
- `style.css`: Ana stil dosyası
    - Genel site stilleri (renkler, fontlar, düzen)
    - Dark mode desteği için CSS değişkenleri ve stilleri
    - Responsive tasarım için medya sorguları

## 2. API Dizini (/api)

### Kullanıcı ve Kimlik Doğrulama
- `auth.php`: Kimlik doğrulama API'leri
    - `register`: Kullanıcı kaydı
    - `login`: Kullanıcı girişi
    - `logout`: Kullanıcı çıkışı
- `user.php`: Kullanıcı işlemleri API'leri
    - `updateUserSettings`: Kullanıcı ayarlarını güncelleme (para birimi, tema)

### Finansal İşlemler
- `income.php`: Gelir yönetimi API'leri
    - `addIncome()`: Yeni gelir ekleme
    - `deleteIncome()`: Gelir silme
    - `loadIncomes()`: Gelirleri listeleme
    - `markIncomeReceived()`: Geliri alındı olarak işaretleme
    - `updateIncome()`: Gelir güncelleme
- `payments.php`: Ödeme yönetimi API'leri
    - `addPayment()`: Yeni ödeme ekleme
    - `deletePayment()`: Ödeme silme
    - `loadPayments()`: Ödemeleri listeleme
    - `loadRecurringPayments()`: Tekrarlayan ödemeleri listeleme
    - `markPaymentPaid()`: Ödemeyi yapıldı olarak işaretleme
    - `updatePayment()`: Ödeme güncelleme
- `savings.php`: Tasarruf yönetimi API'leri
    - `addSaving()`: Yeni tasarruf ekleme
    - `deleteSaving()`: Tasarruf silme
    - `loadSavings()`: Tasarrufları listeleme
    - `updateSaving()`: Tasarruf güncelleme
    - `updateFullSaving()`: Tam tasarruf güncelleme
- `transfer.php`: Transfer işlemleri API'si
    - `transferUnpaidPayments()`: Ödenmemiş ödemeleri transfer etme

### Yardımcı API'ler
- `currency.php`: Döviz kuru işlemleri
    - `getExchangeRate()`: İki para birimi arasındaki kur oranını alma
- `summary.php`: Özet rapor API'si
    - `loadSummary()`: Finansal özet verilerini yükleme
- `utils.php`: Yardımcı fonksiyonlar
    - `calculateRepeatCount()`: Tekrar sayısını hesaplama
    - `getMonthDifference()`: İki tarih arasındaki ay farkını hesaplama
    - `getMonthInterval()`: Aylık aralığı hesaplama
    - `calculateNextPaymentDate()`: Sonraki ödeme tarihini hesaplama
    - `calculateNextDate()`: Sonraki tarihi hesaplama
    - `formatNumber()`: Sayı formatla
- `validate.php`: Doğrulama fonksiyonları
    - `validateRequired()`: Zorunlu alan kontrolü
    - `validateNumeric()`: Sayısal değer kontrolü
    - `validateDate()`: Tarih formatı kontrolü
    - `validateCurrency()`: Para birimi kontrolü
    - `validateFrequency()`: Frekans kontrolü
    - `validateMinValue()`: Minimum değer kontrolü
    - `validateMaxValue()`: Maksimum değer kontrolü
    - `validateDateRange()`: Tarih aralığı kontrolü
- `xss.php`: XSS koruma fonksiyonları
    - `sanitizeOutput()`: Çıktı temizleme

## 3. Sınıflar Dizini (/classes)

### Dil Yönetimi
- `Language.php`: Çoklu dil desteği sınıfı (Singleton Pattern)
    - `getInstance()`: Singleton instance alma
    - `setLanguage()`: Dil ayarlama
    - `getCurrentLanguage()`: Mevcut dili alma
    - `getAvailableLanguages()`: Kullanılabilir dilleri alma
    - `loadAvailableLanguages()`: Dilleri yükleme
    - `loadLanguage()`: Dil dosyasını yükleme
    - `get()`: Dil anahtarına göre çeviri alma
    - `getLanguageName()`: Dil koduna göre dil adını alma
    - `t()`: Statik çeviri metodu

## 4. JavaScript Dizini (/js)

### İşlem Modülleri
- `auth.js`: Kimlik doğrulama işlemleri
    - Giriş işlemleri (login)
        - Form validasyonu
        - AJAX ile API'ye bağlantı
        - Başarılı/başarısız durumlarını yönetme
        - Remember me özelliği
        - SweetAlert2 ile bildirimler
    - Kayıt işlemleri (register)
        - Form validasyonu ve şifre eşleştirme
        - Baz para birimi seçimi
        - AJAX ile API'ye bağlantı
        - Kayıt sonrası yönlendirme
    - Çıkış işlemleri (logout)
        - Onay dialogu
        - Oturum sonlandırma
        - Ana sayfaya yönlendirme
- `income.js`: Gelir yönetimi işlemleri
    - Gelir listesi işlemleri
        - Dinamik tablo güncelleme
        - Para birimi dönüşümleri
        - Durum göstergeleri (alındı/alınmadı)
    - Gelir durumu yönetimi
        - Alındı olarak işaretleme
        - İşaret kaldırma
    - CRUD operasyonları
        - Gelir silme (onay dialogu ile)
        - Gelir güncelleme
            - Form validasyonu
            - Kur güncelleme seçeneği
            - Frekans bazlı bitiş tarihi kontrolü
    - Modal pencere yönetimi
        - Güncelleme modalı
        - Form verilerini doldurma
        - Başarılı/başarısız durumları
- `payments.js`: Ödeme yönetimi işlemleri
    - Ödemeler listesi yönetimi
        - Dinamik tablo güncellemesi
        - Para birimi dönüşümleri
        - Durum göstergeleri (ödendi/ödenmedi)
        - Ödenmemiş ödemeleri transfer etme butonu
    - Ödeme durumu yönetimi
        - Ödendi olarak işaretleme
        - İşaret kaldırma
        - İlerleme çubuğu güncellemesi
    - Tekrarlayan ödemeler sistemi
        - Ana ve alt ödemeler yapısı
        - Taksit ilerleme göstergesi
        - Toplam ve kalan ödeme hesaplamaları
        - Child ödemelerin yönetimi
    - CRUD operasyonları
        - Ödeme silme (onay dialogu ile)
        - Ödeme güncelleme
            - Form validasyonu
            - Kur güncelleme seçeneği
            - Frekans bazlı bitiş tarihi kontrolü
    - Transfer işlemleri
        - Ödenmemiş ödemeleri sonraki aya aktarma
        - Toplu transfer onayı
    - Modal pencere yönetimi
        - Güncelleme modalı
        - Form verilerini doldurma
        - Başarılı/başarısız durumları
- `savings.js`: Tasarruf yönetimi işlemleri
    - Birikimler listesi yönetimi
        - Dinamik tablo güncellemesi
        - İlerleme çubuğu gösterimi
            - Yüzdesel ilerleme hesaplama
            - Duruma göre renk değişimi (tehlike/uyarı/bilgi/başarı)
        - Para birimi gösterimi
    - CRUD operasyonları
        - Birikim silme (onay dialogu ile)
        - Birikim güncelleme
            - Hedef tutar yönetimi
            - Mevcut tutar takibi
            - Başlangıç ve bitiş tarihleri
    - Modal pencere yönetimi
        - Güncelleme modalı
        - Form verilerini doldurma
        - JSON verilerini güvenli şekilde işleme
- `summary.js`: Özet rapor işlemleri
    - Finansal özet yönetimi
        - Toplam gelir hesaplama
        - Toplam gider hesaplama
        - Net bakiye hesaplama
        - Para birimi formatlaması
    - Dönem yönetimi
        - Önceki ay navigasyonu
        - Sonraki ay navigasyonu
        - URL güncelleme
        - Veri yenileme

### Yardımcı Modüller
- `app.js`: Ana uygulama modülü
    - Sayfa başlangıç yapılandırması
    - Modül entegrasyonları
    - Olay dinleyicileri
    - Global veri yönetimi
- `theme.js`: Tema yönetimi
    - Açık/koyu tema geçişi
    - Kullanıcı tercihlerini saklama
    - Sistem temasını algılama
    - Bootstrap tema sınıfları
- `utils.js`: Yardımcı fonksiyonlar
    - AJAX istek yönetimi
    - Form verisi işleme
    - Tarih formatlamaları
    - Para birimi dönüşümleri
    - Validasyon yardımcıları

## 5. Dil Dosyaları Dizini (/lang)
- `tr.php`: Türkçe dil paketi
    - Türkçe diline ait tüm metinlerin çevirilerini içerir.

## 6. Modal Pencereler Dizini (/modals)
- `income_modal.php`: Gelir işlemleri modal penceresi
    - Gelir ekleme ve güncelleme formları
    - Form alanları (isim, tutar, para birimi, tarih, frekans, bitiş tarihi)
- `payment_modal.php`: Ödeme işlemleri modal penceresi
    - Ödeme ekleme ve güncelleme formları
    - Form alanları (isim, tutar, para birimi, tarih, frekans, bitiş tarihi)
- `savings_modal.php`: Tasarruf işlemleri modal penceresi
    - Tasarruf ekleme ve güncelleme formları
    - Form alanları (isim, hedef tutar, biriken tutar, para birimi, başlangıç tarihi, hedef tarihi)
- `user_settings_modal.php`: Kullanıcı ayarları modal penceresi
    - Kullanıcı ayarlarını değiştirme formu
    - Form alanları (ana para birimi, tema)

## 7. Güvenlik Özellikleri
- XSS (Cross-Site Scripting) koruması
- Input validasyonu
- Oturum yönetimi
- Güvenli parola yönetimi

## 8. Veritabanı Yapısı

### Tablolar ve İlişkiler

- `users`: Kullanıcı bilgileri
    - id (PK)
    - username (UNIQUE)
    - password (hash)
    - base_currency
    - theme_preference
    - remember_token
    - created_at

- `income`: Gelir kayıtları
    - id (PK)
    - user_id (FK -> users)
    - parent_id (FK -> income, tekrarlayan gelirler için)
    - name, amount, currency
    - first_date, frequency, next_date
    - status (pending/received)
    - exchange_rate
    - created_at

- `savings`: Birikim kayıtları
    - id (PK)
    - user_id (FK -> users)
    - name
    - target_amount, current_amount
    - currency
    - start_date, target_date
    - created_at

- `payments`: Ödeme kayıtları
    - id (PK)
    - user_id (FK -> users)
    - parent_id (FK -> payments, tekrarlayan ödemeler için)
    - name, amount, currency
    - first_date, frequency, next_date
    - status (pending/paid)
    - exchange_rate
    - created_at

- `exchange_rates`: Döviz kuru geçmişi
    - id (PK)
    - from_currency, to_currency
    - rate
    - date
    - created_at
    - Indexler: date, currencies

### Özellikler
- UTF8MB4 karakter seti (Türkçe desteği)
- Referans bütünlüğü (Foreign Key constraints)
- Cascading delete (tekrarlayan işlemlerde)
- İndekslenmiş arama alanları
- ENUM kullanımı durum takibi için

## 9. Özel Özellikler
- Çoklu dil desteği
- Tema desteği (Açık/Koyu)
- Döviz kuru dönüşümleri ve geçmiş takibi
- Tekrarlayan ödemeler/gelirler sistemi
- Finansal raporlama ve analiz
- İlerleme takibi ve görselleştirme
- Responsive tasarım
- Tarayıcı dili algılama
