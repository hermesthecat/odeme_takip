# Pecunia - Personal Finance Management System

# Pecunia - Kişisel Finans Yönetim Sistemi

[English](#english) | [Türkçe](#türkçe)

## English

### Overview

Pecunia is a comprehensive personal finance management system that helps you track your income, expenses, and investments. Built with modern PHP architecture, it features AI-powered document analysis using Google Gemini API, Telegram bot integration for receipt processing, multi-currency support with real-time exchange rates, and advanced stock portfolio tracking.

### Key Features

#### Financial Management
- **Income & Expense Tracking**: Complete CRUD operations with recurring payment support
- **Multi-Currency Support**: Real-time exchange rates with automatic conversion and caching
- **Savings Goals**: Track progress toward financial targets with visual indicators
- **Investment Portfolio**: Real-time stock price tracking with partial sale support
- **Transfer Management**: Move funds between accounts with full audit trail

#### AI & Automation
- **Document Analysis**: AI-powered processing of receipts, invoices, and financial documents
- **Telegram Bot Integration**: Upload receipts via Telegram for instant AI analysis
- **Smart Categorization**: Automatic expense categorization based on AI analysis
- **Approval Workflow**: Review and approve AI suggestions before adding to your records

#### User Experience
- **Multi-Language Support**: Full Turkish and English localization
- **Dark/Light Themes**: User preference-based theme switching
- **Responsive Design**: Optimized for desktop and mobile devices
- **Real-time Updates**: Live data updates without page refresh
- **Advanced Filtering**: Filter transactions by date, category, currency, and more

#### Security & Performance
- **Session Management**: 30-minute timeout with secure remember-me tokens
- **Brute Force Protection**: 5 failed attempts trigger 15-minute lockout
- **Rate Limiting**: Built-in API rate limiting for external services
- **Smart Caching**: Intelligent cache invalidation for optimal performance
- **XSS Protection**: Comprehensive input validation and output sanitization

### Technical Requirements

- **PHP**: >= 7.4 with extensions (PDO, cURL, GD, mbstring)
- **Database**: MariaDB/MySQL with UTF-8 support
- **Web Server**: Apache/Nginx with HTTPS support
- **Composer**: For PHP dependency management
- **Google Gemini API Key**: For AI document analysis
- **Telegram Bot Token**: For bot integration (optional)

### Installation

#### 1. Get the Code
```bash
git clone https://github.com/hermesthecat/odeme_takip.git
cd pecunia
```

#### 2. Install Dependencies
```bash
composer install
```

#### 3. Environment Configuration
```bash
cp .env.example .env
```

Edit `.env` file with your configuration:
```env
# Database Configuration
DB_SERVER=localhost
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
DB_NAME=your_db_name

# Google Gemini API (Required for AI features)
GEMINI_API_KEY=your_gemini_api_key

# Telegram Bot (Optional)
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username

# Site Configuration
SITE_NAME=Pecunia
SITE_AUTHOR=Your Name
APP_ENV=production
```

#### 4. Database Setup
```bash
# Create database and import structure
mysql -u your_user -p -e "CREATE DATABASE your_database CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;"
mysql -u your_user -p your_database < database.sql
```

#### 5. File Permissions
```bash
mkdir uploads
chmod 755 uploads
# Ensure web server can write to uploads directory
```

#### 6. Telegram Bot Setup (Optional)
```bash
# 1. Create bot via @BotFather on Telegram
# 2. Add bot token to .env file
# 3. Set up webhook (requires HTTPS)
php telegram_bot.php
```

#### 7. Background Tasks (Optional)
Set up cron job for stock price updates:
```bash
# Add to crontab: run every hour
0 * * * * /usr/bin/php /path/to/project/cron_borsa.php
```

### Usage

#### Getting Started
1. **Access the Application**: Navigate to your domain in a web browser
2. **Create Account**: Register with email/username and secure password
3. **Configure Profile**: Set your preferred currency, language, and theme
4. **Start Tracking**: Begin adding your income, expenses, and financial goals

#### Core Workflows

**Adding Transactions**:
- Use the "+" button to add income, payments, or savings
- Set up recurring transactions for regular income/expenses
- Enable multi-currency support for international transactions

**AI Document Analysis**:
- Upload receipts, invoices, or bank statements (PDF, Excel, images)
- Review AI-generated transaction suggestions
- Bulk approve or edit suggestions before adding to your records

**Telegram Integration**:
- Go to Profile → Get Verification Code
- Start chat with your bot on Telegram
- Send `/verify your_code` to link accounts
- Send receipt photos directly to bot for instant analysis

**Portfolio Management**:
- Add stock purchases with automatic price tracking
- Monitor real-time portfolio performance
- Track partial sales and dividend income

#### Key Features Usage

**Multi-Currency**: 
- Set different currencies per transaction
- Automatic exchange rate fetching and conversion
- Historical rate preservation for accurate reporting

**Savings Goals**:
- Set target amounts and deadlines
- Track progress with visual indicators
- Link transactions to specific goals

**Reporting & Analytics**:
- Monthly/yearly financial summaries
- Category-based expense analysis
- Multi-currency consolidated reports
- Export data for external analysis

---

## Türkçe

### Genel Bakış

Pecunia, gelirlerinizi, giderlerinizi ve yatırımlarınızı takip etmenize yardımcı olan kapsamlı bir kişisel finans yönetim sistemidir. Modern PHP mimarisiyle geliştirilmiş olup, Google Gemini API kullanarak yapay zeka destekli döküman analizi, fiş işleme için Telegram bot entegrasyonu, gerçek zamanlı döviz kurları ile çoklu para birimi desteği ve gelişmiş hisse senedi portföyü takibi özelliklerini sunar.

### Temel Özellikler

#### Finansal Yönetim
- **Gelir & Gider Takibi**: Tekrarlayan ödeme desteği ile tam CRUD işlemleri
- **Çoklu Para Birimi Desteği**: Gerçek zamanlı döviz kurları, otomatik çevrim ve önbellekleme
- **Birikim Hedefleri**: Görsel göstergelerle finansal hedeflere doğru ilerleme takibi
- **Yatırım Portföyü**: Kısmi satış desteği ile gerçek zamanlı hisse fiyat takibi
- **Transfer Yönetimi**: Tam denetim izi ile hesaplar arası para transferi

#### Yapay Zeka & Otomasyon
- **Döküman Analizi**: Fişler, faturalar ve finansal dökümanların yapay zeka ile işlenmesi
- **Telegram Bot Entegrasyonu**: Anında yapay zeka analizi için Telegram üzerinden fiş yükleme
- **Akıllı Kategorilendirme**: Yapay zeka analizine dayalı otomatik gider kategorilendirmesi
- **Onay İş Akışı**: Kayıtlarınıza eklemeden önce yapay zeka önerilerini gözden geçirme ve onaylama

#### Kullanıcı Deneyimi
- **Çoklu Dil Desteği**: Tam Türkçe ve İngilizce lokalizasyonu
- **Koyu/Açık Temalar**: Kullanıcı tercihine dayalı tema değiştirme
- **Duyarlı Tasarım**: Masaüstü ve mobil cihazlar için optimize edilmiş
- **Gerçek Zamanlı Güncellemeler**: Sayfa yenileme olmadan canlı veri güncellemeleri
- **Gelişmiş Filtreleme**: Tarih, kategori, para birimi ve daha fazlasına göre işlem filtreleme

#### Güvenlik & Performans
- **Oturum Yönetimi**: Güvenli "beni hatırla" tokenleri ile 30 dakika zaman aşımı
- **Kaba Kuvvet Koruması**: 5 başarısız girişim 15 dakika kilitleme tetikler
- **Hız Sınırlama**: Dış servisler için yerleşik API hız sınırlama
- **Akıllı Önbellekleme**: Optimal performans için akıllı önbellek geçersiz kılma
- **XSS Koruması**: Kapsamlı girdi doğrulama ve çıktı temizleme

### Teknik Gereksinimler

- **PHP**: >= 7.4 ve eklentileri (PDO, cURL, GD, mbstring)
- **Veritabanı**: UTF-8 desteği ile MariaDB/MySQL
- **Web Sunucusu**: HTTPS desteği ile Apache/Nginx
- **Composer**: PHP bağımlılık yönetimi için
- **Google Gemini API Anahtarı**: Yapay zeka döküman analizi için
- **Telegram Bot Token**: Bot entegrasyonu için (opsiyonel)

### Kurulum

#### 1. Kodu İndirin
```bash
git clone https://github.com/hermesthecat/odeme_takip.git
cd pecunia
```

#### 2. Bağımlılıkları Yükleyin
```bash
composer install
```

#### 3. Ortam Yapılandırması
```bash
cp .env.example .env
```

`.env` dosyasını yapılandırmanıza göre düzenleyin:
```env
# Veritabanı Yapılandırması
DB_SERVER=localhost
DB_USERNAME=veritabani_kullanicisi
DB_PASSWORD=veritabani_sifresi
DB_NAME=veritabani_adi

# Google Gemini API (Yapay zeka özellikler için gerekli)
GEMINI_API_KEY=gemini_api_anahtariniz

# Telegram Bot (Opsiyonel)
TELEGRAM_BOT_TOKEN=bot_tokeniniz
TELEGRAM_BOT_USERNAME=bot_kullanici_adiniz

# Site Yapılandırması
SITE_NAME=Pecunia
SITE_AUTHOR=Adınız
APP_ENV=production
```

#### 4. Veritabanı Kurulumu
```bash
# Veritabanı oluşturun ve yapıyı içe aktarın
mysql -u kullanici -p -e "CREATE DATABASE veritabani_adi CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;"
mysql -u kullanici -p veritabani_adi < database.sql
```

#### 5. Dosya İzinleri
```bash
mkdir uploads
chmod 755 uploads
# Web sunucusunun uploads dizinine yazabildiğinden emin olun
```

#### 6. Telegram Bot Kurulumu (Opsiyonel)
```bash
# 1. Telegram'da @BotFather üzerinden bot oluşturun
# 2. Bot token'ını .env dosyasına ekleyin
# 3. Webhook kurulumu (HTTPS gerektirir)
php telegram_bot.php
```

#### 7. Arka Plan Görevleri (Opsiyonel)
Hisse fiyat güncellemeleri için cron job kurulumu:
```bash
# Crontab'a ekleyin: her saat çalıştır
0 * * * * /usr/bin/php /proje/yolu/cron_borsa.php
```

### Kullanım

#### Başlangıç
1. **Uygulamaya Erişim**: Web tarayıcınızda domain adresinize gidin
2. **Hesap Oluşturun**: E-posta/kullanıcı adı ve güvenli parola ile kayıt olun
3. **Profil Yapılandırması**: Tercih ettiğiniz para birimi, dil ve temayı ayarlayın
4. **Takibe Başlayın**: Gelir, gider ve finansal hedeflerinizi eklemeye başlayın

#### Temel İş Akışları

**İşlem Ekleme**:
- Gelir, ödeme veya birikim eklemek için "+" butonunu kullanın
- Düzenli gelir/gider için tekrarlayan işlemler ayarlayın
- Uluslararası işlemler için çoklu para birimi desteğini etkinleştirin

**Yapay Zeka Döküman Analizi**:
- Fiş, fatura veya banka ekstreleri yükleyin (PDF, Excel, resim)
- Yapay zeka tarafından üretilen işlem önerilerini gözden geçirin
- Kayıtlarınıza eklemeden önce önerileri toplu olarak onaylayın veya düzenleyin

**Telegram Entegrasyonu**:
- Profil → Doğrulama Kodu Al'a gidin
- Telegram'da botunuzla sohbet başlatın
- Hesapları bağlamak için `/verify kodunuz` gönderin
- Anında analiz için fiş fotoğraflarını doğrudan bota gönderin

**Portföy Yönetimi**:
- Otomatik fiyat takibi ile hisse alımları ekleyin
- Gerçek zamanlı portföy performansını izleyin
- Kısmi satışları ve temettü gelirlerini takip edin

### Architecture & Development

Pecunia is built with a modern, secure architecture:
- **MVC Pattern**: Clean separation of concerns with centralized API routing
- **Security First**: XSS protection, rate limiting, secure session management
- **Multi-Language**: Full internationalization support with dynamic language switching
- **AI Integration**: Google Gemini API for intelligent document processing
- **Real-time Data**: Live updates and background processing for financial data
- **Extensible Design**: Modular architecture for easy feature additions

For detailed technical documentation, see [CLAUDE.md](CLAUDE.md).

---

### Mimari & Geliştirme

Pecunia modern ve güvenli bir mimari ile geliştirilmiştir:
- **MVC Modeli**: Merkezi API yönlendirmesi ile temiz endişe ayrımı
- **Güvenlik Öncelikli**: XSS koruması, hız sınırlama, güvenli oturum yönetimi
- **Çoklu Dil**: Dinamik dil değiştirme ile tam uluslararasılaştırma desteği
- **Yapay Zeka Entegrasyonu**: Akıllı döküman işleme için Google Gemini API
- **Gerçek Zamanlı Veri**: Finansal veriler için canlı güncellemeler ve arka plan işleme
- **Genişletilebilir Tasarım**: Kolay özellik ekleme için modüler mimari

Detaylı teknik dokümantasyon için [CLAUDE.md](CLAUDE.md) dosyasına bakın.

---

## Author / Yazar

A. Kerem Gök

## License / Lisans

MIT
