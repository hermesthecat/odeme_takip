# Telegram Bot Integration

Bu klasör projenin Telegram bot entegrasyonunu içerir.

## Dosyalar

### Bot Ana Dosyaları

#### `telegram_bot.php`
- **Açıklama**: Ana Telegram bot kurulum dosyası
- **İşlev**: Bot token ve webhook konfigürasyonu
- **Kullanım**: Bot'u başlatmak ve webhook ayarlamak için
- **Özellikler**:
  - Webhook URL ayarlama
  - Komut dizini konfigürasyonu
  - Error handling ve logging

#### `telegram_webhook.php`
- **Açıklama**: Webhook endpoint'i
- **İşlev**: Telegram'dan gelen mesajları işler
- **URL**: `https://yourdomain.com/telegram/telegram_webhook.php`
- **Özellikler**:
  - Rate limiting (30 req/min)
  - Message routing
  - Error handling
  - Security validation

### Komutlar (`commands/`)

#### `StartCommand.php`
- **Komut**: `/start`
- **Açıklama**: Bot'a ilk başlangıç komutu
- **İşlev**: Kullanıcıyı karşılar ve mevcut komutları listeler

#### `HelpCommand.php`
- **Komut**: `/help`
- **Açıklama**: Yardım menüsü
- **İşlev**: Tüm mevcut komutları ve kullanımlarını gösterir

#### `VerifyCommand.php`
- **Komut**: `/verify <kod>`
- **Açıklama**: Hesap doğrulama
- **İşlev**: Web uygulamasından alınan 6 haneli kodu doğrular
- **Özellikler**:
  - Database verification
  - Account linking
  - Security validation

#### `ReceiptCommand.php`
- **Komut**: Fotoğraf gönderme
- **Açıklama**: Fiş/fatura analizi
- **İşlev**: Gönderilen fotoğrafları Google Gemini AI ile analiz eder
- **Özellikler**:
  - Image processing
  - AI integration
  - Temporary storage
  - Web approval flow

#### `GenericmessageCommand.php`
- **Açıklama**: Genel mesaj işleyici
- **İşlev**: Tanınmayan mesajları karşılar
- **Response**: Kullanıcıyı doğru komutlara yönlendirir

## Kurulum

### 1. Bot Oluşturma
```bash
# Telegram'da @BotFather ile bot oluştur
# Token ve username al
```

### 2. Environment Variables
```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_BOT_USERNAME=your_bot_username
WEBHOOK_URL=https://yourdomain.com/telegram/telegram_webhook.php
```

### 3. Webhook Ayarlama
```bash
# Bot webhook'unu ayarla
php telegram/telegram_bot.php

# Webhook durumunu kontrol et
curl -X GET "https://api.telegram.org/bot<TOKEN>/getWebhookInfo"
```

### 4. Web Server Configuration
```apache
# Apache .htaccess (telegram/ klasörü için)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ telegram_webhook.php [QSA,L]
```

## İş Akışı

### Account Verification Flow
1. **Web App**: Kullanıcı profile sayfasında kod üretir
2. **Database**: 6 haneli kod `telegram_users` tablosuna kaydedilir
3. **Telegram**: Kullanıcı `/verify 123456` komutunu gönderir
4. **Bot**: Kodu database'de doğrular ve hesabı bağlar
5. **Success**: Kullanıcı artık bot özelliklerini kullanabilir

### Receipt Analysis Flow
1. **Telegram**: Kullanıcı fotoğraf gönderir
2. **Bot**: Fotoğrafı indirir ve geçici klasöre kaydeder
3. **AI**: Google Gemini Vision API ile analiz edilir
4. **Database**: Sonuçlar `ai_analysis_temp` tablosuna kaydedilir
5. **Web**: Kullanıcı web arayüzünde sonuçları onaylar
6. **Integration**: Onaylanan veriler `income`/`payments` tablolarına aktarılır

## Security Features

### Rate Limiting
```php
// Webhook rate limiting - 30 requests/minute
$telegram_id = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkTelegramWebhookLimit($telegram_id)) {
    http_response_code(429);
    exit('Rate limit exceeded');
}
```

### Validation
- **User Authentication**: Database'de user verification kontrolü
- **Image Validation**: File type, size, malicious content kontrolü
- **Input Sanitization**: Tüm user input'ları sanitize edilir
- **Database Security**: Prepared statements kullanımı

### Error Handling
- **Try-Catch Blocks**: Tüm critical operations wrapped
- **Logging**: Error'lar database'e kaydedilir
- **Graceful Responses**: User'a anlamlı error mesajları

## Bot Commands Reference

| Komut | Parametre | Açıklama | Örnek |
|-------|-----------|----------|-------|
| `/start` | - | Bot'u başlat | `/start` |
| `/help` | - | Yardım menüsü | `/help` |
| `/verify` | `<6-digit-code>` | Hesap doğrula | `/verify 123456` |
| Fotoğraf | - | Fiş analizi | *Fotoğraf gönder* |

## Configuration

### Webhook URL Structure
```
https://yourdomain.com/telegram/telegram_webhook.php
```

### File Permissions
```bash
# Telegram klasörü yazma izni
chmod 755 telegram/
chmod 644 telegram/*.php
chmod 644 telegram/commands/*.php
```

### Database Tables Used
- `telegram_users` - User verification ve linking
- `ai_analysis_temp` - AI analiz sonuçları
- `logs` - Bot activity logging
- `users` - Ana user hesapları

## Development

### Yeni Komut Ekleme
1. `commands/` klasöründe yeni dosya oluştur
2. `UserCommand` class'ından extend et
3. `execute()` metodunu implement et
4. Bot'u restart et

### Testing
```bash
# Webhook test et
curl -X POST https://yourdomain.com/telegram/telegram_webhook.php \
  -H "Content-Type: application/json" \
  -d '{"update_id":1,"message":{"message_id":1,"from":{"id":123,"first_name":"Test"},"chat":{"id":123,"type":"private"},"date":1234567890,"text":"/start"}}'
```

### Debugging
```php
// Debug modunu aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Webhook response'larını logla
file_put_contents('webhook_debug.log', json_encode($_POST), FILE_APPEND);
```

## Troubleshooting

### Yaygın Sorunlar

1. **Webhook Not Working**
   - HTTPS gerekli (HTTP kabul edilmez)
   - SSL certificate valid olmalı
   - Server 200 response dönmeli

2. **Commands Not Responding**
   - Command class'ları doğru namespace'de olmalı
   - `execute()` metodu implement edilmeli
   - Bot token ve username doğru olmalı

3. **File Upload Issues**
   - PHP upload limits kontrol et
   - Directory permissions kontrol et
   - Disk space kontrol et

4. **Database Connection**
   - Config.php path'leri kontrol et
   - Database credentials doğru olmalı
   - Foreign key constraints aktif olmalı

### Log Monitoring
```sql
-- Bot activity logs
SELECT * FROM logs 
WHERE log_method LIKE '%telegram%' 
ORDER BY created_at DESC LIMIT 50;

-- Verification attempts
SELECT * FROM telegram_users 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

## Performance

### Optimization Tips
- **Async Processing**: Büyük dosyalar için background processing
- **Caching**: Frequent queries için cache kullan
- **Rate Limiting**: API abuse'ü önle
- **File Cleanup**: Geçici dosyaları düzenli temizle

### Monitoring
- Webhook response time'ları
- AI API usage quotas
- Database query performance
- Error rates ve patterns

## Integration Notes

### Web App Integration
Bot özellikleri web app ile tight integration:
- User verification codes
- AI analysis results
- Profile settings sync
- Notification preferences

### External APIs
- **Telegram Bot API**: Message handling
- **Google Gemini Vision**: Image analysis
- **File Upload APIs**: Image processing

Bu dokümantasyon bot'un tam functionality'sini ve maintenance gereksinimlerini kapsar.