# Pecunia - Personal Finance Management System
# Pecunia - Kişisel Finans Yönetim Sistemi

[English](#english) | [Türkçe](#türkçe)

## English

### Overview
Pecunia is a comprehensive personal finance management system that helps you track your income, expenses, and investments. With AI-powered document analysis capabilities and Telegram bot integration, it makes financial tracking easier than ever.

### Features
- Income and expense tracking
- Multi-currency support
- PDF/Excel document analysis with AI
- Investment portfolio management
- Savings goals tracking
- Dark/Light theme support
- Multi-language support
- Telegram bot integration for receipt analysis

### Requirements
- PHP >= 7.4
- MariaDB/MySQL
- Composer
- Google Gemini API Key
- Telegram Bot Token

### Installation
1. Clone the repository
```bash
git clone https://github.com/hermesthecat/odeme_takip.git
cd pecunia
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
# Edit .env file with your configuration
```

4. Set up database
```bash
# Import database structure
mysql -u your_user -p your_database < database.sql
```

5. Set permissions
```bash
mkdir uploads
chmod 777 uploads
```

6. Configure Telegram Bot
```bash
# Create a bot via @BotFather on Telegram and get your token
# Add these to your .env file:
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username
WEBHOOK_URL=https://your-domain.com

# Set up the webhook by running
php telegram_bot.php

# Ensure your server is accessible via HTTPS
# Telegram requires HTTPS for webhooks
```

### Usage
1. Configure your Gemini API key in `.env` file
2. Start tracking your finances
3. Upload financial documents for AI analysis
4. Review and approve AI suggestions
5. Monitor your financial health
6. Connect your Telegram account:
   - Go to your profile page
   - Click "Get Verification Code"
   - Start a chat with your bot on Telegram
   - Send the verification code using the /verify command
   - Now you can send receipt photos directly to the bot for analysis

---

## Türkçe

### Genel Bakış
Pecunia, gelirlerinizi, giderlerinizi ve yatırımlarınızı takip etmenize yardımcı olan kapsamlı bir kişisel finans yönetim sistemidir. Yapay zeka destekli döküman analizi özellikleri ve Telegram bot entegrasyonuyla finansal takibi her zamankinden daha kolay hale getirir.

### Özellikler
- Gelir ve gider takibi
- Çoklu para birimi desteği
- Yapay zeka ile PDF/Excel döküman analizi
- Yatırım portföyü yönetimi
- Birikim hedefleri takibi
- Koyu/Açık tema desteği
- Çoklu dil desteği
- Fiş analizi için Telegram bot entegrasyonu

### Gereksinimler
- PHP >= 7.4
- MariaDB/MySQL
- Composer
- Google Gemini API Anahtarı
- Telegram Bot Token

### Kurulum
1. Depoyu klonlayın
```bash
git clone https://github.com/hermesthecat/odeme_takip.git
cd pecunia
```

2. Bağımlılıkları yükleyin
```bash
composer install
```

3. Ortam yapılandırması
```bash
cp .env.example .env
# .env dosyasını kendi yapılandırmanıza göre düzenleyin
```

4. Veritabanı kurulumu
```bash
# Veritabanı yapısını içe aktarın
mysql -u kullanici_adiniz -p veritabani_adiniz < database.sql
```

5. İzinleri ayarlayın
```bash
mkdir uploads
chmod 777 uploads
```

6. Telegram Bot Yapılandırması
```bash
# Telegram'da @BotFather üzerinden bir bot oluşturun ve token alın
# .env dosyasına şunları ekleyin:
TELEGRAM_BOT_TOKEN=bot_token
TELEGRAM_BOT_USERNAME=bot_kullanici_adi
WEBHOOK_URL=https://alan-adiniz.com

# Webhook'u ayarlamak için şu komutu çalıştırın
php telegram_bot.php

# Sunucunuzun HTTPS üzerinden erişilebilir olduğundan emin olun
# Telegram, webhook'lar için HTTPS gerektirir
```

### Kullanım
1. Gemini API anahtarınızı `.env` dosyasında yapılandırın
2. Finanslarınızı takip etmeye başlayın
3. Yapay zeka analizi için finansal dökümanları yükleyin
4. Yapay zeka önerilerini inceleyin ve onaylayın
5. Finansal sağlığınızı izleyin
6. Telegram hesabınızı bağlayın:
   - Profil sayfanıza gidin
   - "Doğrulama Kodu Al" butonuna tıklayın
   - Telegram'da botunuzla sohbet başlatın
   - /verify komutunu kullanarak doğrulama kodunu gönderin
   - Artık fiş fotoğraflarını doğrudan bota gönderebilirsiniz

---

## Author / Yazar
A. Kerem Gök

## License / Lisans
MIT