# CRON Jobs

Bu klasör projenin cron job dosyalarını içerir.

## Dosyalar

### `cron_borsa.php`

- **Açıklama**: Ana borsa portföy güncelleme script'i
- **İşlev**: Portföydeki hisse senetlerinin güncel fiyatlarını BigPara API'den çeker
- **Çalışma Sıklığı**: Saatlik (önerilen)
- **Özellikler**:
  - Paralel işleme desteği (çok sayıda hisse için)
  - Multi-curl ile hızlı API çağrıları
  - Transaction güvenliği
  - Detaylı loglama

### `cron_borsa_worker.php`

- **Açıklama**: Worker script (yardımcı işlem)
- **İşlev**: `cron_borsa.php` tarafından çağrılır, belirli hisse gruplarını işler
- **Kullanım**: Otomatik olarak ana script tarafından çalıştırılır
- **Özellikler**:
  - Bağımsız işlem yönetimi
  - Sonuçları geçici dosyalarda saklar
  - Hata durumunda rollback

## Kurulum

### 1. Crontab Ayarı

```bash
# Her saat başı çalıştır
0 * * * * /usr/bin/php /path/to/project/cron/cron_borsa.php

# Örnek tam yol:
0 * * * * /usr/bin/php /var/www/html/butce/cron/cron_borsa.php
```

### 2. Manuel Çalıştırma

```bash
# Ana dizinden
php cron/cron_borsa.php

# Cron dizininden
cd cron
php cron_borsa.php
```

## Gereksinimler

- PHP 7.4+
- curl extension
- PDO MySQL extension
- BigPara API erişimi
- Yazma izni (log dosyaları için)

## Loglama

Tüm cron işlemleri `logs` tablosuna kaydedilir:

- Başarılı güncellemeler
- API hataları
- Veritabanı işlem sonuçları
- Performance metrikleri

## Troubleshooting

### Yaygın Sorunlar

1. **API Rate Limiting**
   - BigPara API'den çok fazla istek
   - Çözüm: `usleep()` ile bekleme süreleri

2. **Timeout Hataları**
   - Çok fazla hisse ile uzun işlem süreleri
   - Çözüm: Paralel işleme otomatik devreye girer

3. **Veritabanı Bağlantı Hatası**
   - `config.php` dosyasındaki DB ayarları
   - Çözüm: Bağlantı parametrelerini kontrol et

4. **Dosya İzin Hataları**
   - Worker script'ler geçici dosya oluşturamaz
   - Çözüm: `/tmp` dizini yazma izni ver

### Log Kontrolü

```sql
-- Son 24 saatin cron logları
SELECT * FROM logs 
WHERE log_method LIKE '%cron%' 
AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;

-- Hata logları
SELECT * FROM logs 
WHERE type = 'error' 
AND log_method LIKE '%borsa%'
ORDER BY created_at DESC;
```

## Performance

- **Normal Durumda**: ~30 saniye (10-50 hisse)
- **Yoğun Durumda**: ~2-3 dakika (100+ hisse)
- **API Limiti**: 1000 request/saat (BigPara)
- **Paralel İşlem**: 5 worker max

## Güvenlik

- HTTPS API çağrıları
- SQL prepared statements
- Transaction rollback
- Input validation
- Error sanitization
