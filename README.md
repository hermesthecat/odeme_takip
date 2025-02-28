# Pecunia - Personal Finance Management System
# Pecunia - Kişisel Finans Yönetim Sistemi

[English](#english) | [Türkçe](#türkçe)

## English

### Overview
Pecunia is a comprehensive personal finance management system that helps you track your income, expenses, and investments. With AI-powered document analysis capabilities, it makes financial tracking easier than ever.

### Features
- Income and expense tracking
- Multi-currency support
- PDF/Excel document analysis with AI
- Investment portfolio management
- Savings goals tracking
- Dark/Light theme support
- Multi-language support

### Requirements
- PHP >= 7.4
- MariaDB/MySQL
- Composer
- Google Gemini API Key

### Installation
1. Clone the repository
```bash
git clone https://github.com/yourusername/pecunia.git
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

### Usage
1. Configure your Gemini API key in `.env` file
2. Start tracking your finances
3. Upload financial documents for AI analysis
4. Review and approve AI suggestions
5. Monitor your financial health

---

## Türkçe

### Genel Bakış
Pecunia, gelirlerinizi, giderlerinizi ve yatırımlarınızı takip etmenize yardımcı olan kapsamlı bir kişisel finans yönetim sistemidir. Yapay zeka destekli döküman analizi özellikleriyle finansal takibi her zamankinden daha kolay hale getirir.

### Özellikler
- Gelir ve gider takibi
- Çoklu para birimi desteği
- Yapay zeka ile PDF/Excel döküman analizi
- Yatırım portföyü yönetimi
- Birikim hedefleri takibi
- Koyu/Açık tema desteği
- Çoklu dil desteği

### Gereksinimler
- PHP >= 7.4
- MariaDB/MySQL
- Composer
- Google Gemini API Anahtarı

### Kurulum
1. Depoyu klonlayın
```bash
git clone https://github.com/yourusername/pecunia.git
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

### Kullanım
1. Gemini API anahtarınızı `.env` dosyasında yapılandırın
2. Finanslarınızı takip etmeye başlayın
3. Yapay zeka analizi için finansal dökümanları yükleyin
4. Yapay zeka önerilerini inceleyin ve onaylayın
5. Finansal sağlığınızı izleyin

---

## Author / Yazar
A. Kerem Gök

## License / Lisans
MIT