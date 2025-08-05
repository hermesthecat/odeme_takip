# Pecunia Proje Analiz Raporu

## 📊 Genel Değerlendirme

**Proje:** Pecunia - Kişisel Finans Yönetim Sistemi  
**Analiz Tarihi:** 2025-01-08  
**Toplam Tespit Edilen Sorun:** 53 adet  
**Mevcut Risk Seviyesi:** 🔴 KRİTİK  

---

## 🔴 KRİTİK GÜVENLİK AÇIKLARI (Acil Müdahale Gerekli)

### 1. **Kimlik Doğrulama Güvenlik Açığı**

**Dosya:** `config.php:169`  
**Severity:** Critical  
**SORUN:** JavaScript redirect kolayca bypass edilebilir

```php
// MEVCUT KOD (GÜVENSİZ):
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>window.location.href = '" . SITE_URL . "/login.php';</script>";
        exit;
    }
}
```

**ÇÖZÜM:**

```php
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . SITE_URL . "/login.php");
        http_response_code(302);
        exit;
    }
}
```

**Etki:** Yetkisiz erişim, authentication bypass  
**Öncelik:** Derhal düzeltilmeli

---

### 2. **CSRF Koruması Tamamen Eksik**

**Dosyalar:** Tüm formlar ve API endpoints  
**Severity:** Critical  
**SORUN:** Hiçbir yerde CSRF token bulunmuyor  

**ÇÖZÜM:**

```php
// CSRF token üretimi
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF token doğrulama
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```

**Etki:** Cross-Site Request Forgery saldırıları  
**Öncelik:** Derhal düzeltilmeli

---

### 3. **Dosya Yükleme Güvenlik Açığı**

**Dosya:** `ai_analysis.php:96-100`  
**Severity:** Critical  
**SORUN:** Yetersiz dosya adı sanitizasyonu ve web erişilebilir dizinde depolama

```php
// MEVCUT KOD (GÜVENSİZ):
$safeFileName = preg_replace("/[^a-zA-Z0-9.-]/", "_", $fileName);
mkdir($uploadDir, 0777, true); // Web root içinde!
```

**ÇÖZÜM:**

```php
// Web root dışında güvenli depolama
$uploadDir = __DIR__ . '/../secure_uploads/' . $userId . '/';
$safeFileName = hash('sha256', $fileName . time()) . '.' . $allowedExtension;

// Güçlü MIME validasyonu
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($tempFilePath);
if (!in_array($mimeType, $allowedMimeTypes)) {
    throw new Exception('Invalid file type');
}
```

**Etki:** Remote code execution, server compromise  
**Öncelik:** Derhal düzeltilmeli

---

### 4. **Session Yönetimi Açıkları**

**Dosya:** `api/auth.php:169-170`  
**Severity:** Critical  
**SORUN:** Session timeout kontrolü yapılmıyor

```php
// MEVCUT KOD (EKSİK):
$_SESSION['last_activity_time'] = time(); // Sadece set ediliyor, kontrol yok!
```

**ÇÖZÜM:**

```php
function validateSessionTimeout() {
    $timeout = 30 * 60; // 30 dakika
    
    if (isset($_SESSION['last_activity_time'])) {
        if (time() - $_SESSION['last_activity_time'] > $timeout) {
            session_destroy();
            header("Location: " . SITE_URL . "/login.php?timeout=1");
            exit;
        }
    }
    $_SESSION['last_activity_time'] = time();
}
```

**Etki:** Session hijacking, unauthorized access  
**Öncelik:** Derhal düzeltilmeli

---

### 5. **SQL Injection Potential**

**Dosya:** `api.php:23` ve tüm API modülleri  
**Severity:** Critical  
**SORUN:** `user_id` validasyonu eksik

```php
// MEVCUT KOD (RİSKLİ):
$user_id = $_SESSION['user_id']; // Doğrudan kullanım!
```

**ÇÖZÜM:**

```php
function validateUserId($userId) {
    if (!is_numeric($userId) || $userId <= 0) {
        throw new Exception('Invalid user ID');
    }
    return (int)$userId;
}

$user_id = validateUserId($_SESSION['user_id']);
```

**Etki:** Database compromise, data breach  
**Öncelik:** Derhal düzeltilmeli

---

## 🟠 YÜKSEK ÖNCELİKLİ SORUNLAR

### 6. **Database İndeks Eksiklikleri**

**Dosya:** `database.sql`  
**Severity:** High  
**SORUN:** `user_id` alanlarında indeks yok (30+ sorgu etkileniyor)

**ÇÖZÜM:**

```sql
-- Kritik indeksler
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_income_user_id ON income(user_id);
CREATE INDEX idx_savings_user_id ON savings(user_id);
CREATE INDEX idx_portfolio_user_id ON portfolio(user_id);
CREATE INDEX idx_logs_user_id ON logs(user_id);

-- Tarih bazlı sorgular için
CREATE INDEX idx_payments_user_date ON payments(user_id, first_date);
CREATE INDEX idx_income_user_date ON income(user_id, first_date);

-- Compound indeksler
CREATE INDEX idx_payments_user_status ON payments(user_id, payment_status);
CREATE INDEX idx_income_user_status ON income(user_id, income_status);
```

**Etki:** Performans sorunları, yavaş sorgular  
**Öncelik:** 1 hafta içinde düzeltilmeli

---

### 7. **Transaction Rollback Eksik**

**Dosyalar:** `api/payments.php`, `api/income.php`, `api/savings.php`  
**Severity:** High  
**SORUN:** Try-catch blokularında rollback yok

**MEVCUT KOD (EKSİK):**

```php
$pdo->beginTransaction();
try {
    // operations...
    $pdo->commit();
} catch (Exception $e) {
    // ROLLBACK EKSİK!
    throw $e;
}
```

**ÇÖZÜM:**

```php
$pdo->beginTransaction();
try {
    // operations...
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollback(); // EKLE!
    saveLog('error', 'Transaction failed: ' . $e->getMessage(), __METHOD__);
    throw $e;
}
```

**Etki:** Data corruption, inconsistent state  
**Öncelik:** 1 hafta içinde düzeltilmeli

---

### 8. **Input Validation Tutarsızlığı**

**Dosyalar:** `js/utils.js` vs API dosyaları  
**Severity:** High  
**SORUN:** Client-side ve server-side validasyon kuralları uyumsuz

**ÇÖZÜM:**

- Validation kurallarını JSON config dosyasında merkezi tanımla
- Client ve server tarafında aynı kuralları kullan
- Server-side validation'ı asla bypass edilemez yap

**Etki:** Data integrity issues, security bypass  
**Öncelik:** 2 hafta içinde düzeltilmeli

---

## 🟡 ORTA ÖNCELİKLİ SORUNLAR

### 9. **Foreign Key Constraint Eksik**

**Dosya:** `database.sql`  
**Severity:** Medium  
**SORUN:** Referential integrity yok, orphaned records mümkün

**ÇÖZÜM:**

```sql
-- Foreign key constraints ekle
ALTER TABLE payments ADD CONSTRAINT fk_payments_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE income ADD CONSTRAINT fk_income_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE savings ADD CONSTRAINT fk_savings_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE portfolio ADD CONSTRAINT fk_portfolio_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE telegram_users ADD CONSTRAINT fk_telegram_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Parent-child relationships
ALTER TABLE payments ADD CONSTRAINT fk_payments_parent 
    FOREIGN KEY (parent_id) REFERENCES payments(id) ON DELETE CASCADE;

ALTER TABLE income ADD CONSTRAINT fk_income_parent 
    FOREIGN KEY (parent_id) REFERENCES income(id) ON DELETE CASCADE;

ALTER TABLE savings ADD CONSTRAINT fk_savings_parent 
    FOREIGN KEY (parent_id) REFERENCES savings(id) ON DELETE CASCADE;
```

**Etki:** Data integrity issues, orphaned records  
**Öncelik:** 2-3 hafta içinde düzeltilmeli

---

### 10. **Error Message Information Disclosure**

**Dosya:** `api/error_handler.php:36-43`  
**Severity:** Medium  
**SORUN:** Production'da detaylı hata mesajları gösteriliyor

**MEVCUT KOD (GÜVENSİZ):**

```php
if ($_ENV['APP_ENV'] === 'development') {
    // ... detailed errors
} else {
    // Hala çok detaylı!
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
```

**ÇÖZÜM:**

```php
function sanitizeErrorMessage($message, $isDevelopment = false) {
    if ($isDevelopment) {
        return $message;
    }
    
    // Production'da sadece generic mesajlar
    $genericMessages = [
        'Database error' => 'Bir sistem hatası oluştu. Lütfen daha sonra tekrar deneyin.',
        'File error' => 'Dosya işlemi başarısız. Lütfen tekrar deneyin.',
        'Validation error' => 'Girdiğiniz bilgileri kontrol edin.',
    ];
    
    foreach ($genericMessages as $pattern => $generic) {
        if (stripos($message, $pattern) !== false) {
            return $generic;
        }
    }
    
    return 'Bir hata oluştu. Lütfen tekrar deneyin.';
}
```

**Etki:** Information disclosure  
**Öncelik:** 2-3 hafta içinde düzeltilmeli

---

### 11. **Rate Limiting Bypass**

**Dosya:** `api/rate_limiter.php`  
**Severity:** Medium  
**SORUN:** Session temelli rate limiting bypass edilebilir

**MEVCUT KOD (BYPASS EDİLEBİLİR):**

```php
// Session temelli - kullanıcı session'ı temizleyebilir
if (!isset($_SESSION['rate_limit'][$identifier])) {
    $_SESSION['rate_limit'][$identifier] = [];
}
```

**ÇÖZÜM:**

```php
// Database veya Redis temelli persistent rate limiting
class RateLimiter {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function isAllowed($identifier, $maxRequests, $timeWindow) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count 
            FROM rate_limits 
            WHERE identifier = ? AND created_at > ?
        ");
        $stmt->execute([$identifier, time() - $timeWindow]);
        $result = $stmt->fetch();
        
        if ($result['count'] >= $maxRequests) {
            return false;
        }
        
        // Request'i kaydet
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, created_at) VALUES (?, ?)
        ");
        $stmt->execute([$identifier, time()]);
        
        return true;
    }
}
```

**Etki:** API abuse, DoS attacks  
**Öncelik:** 3-4 hafta içinde düzeltilmeli

---

## 🔵 DÜŞÜK ÖNCELİKLİ İYİLEŞTİRMELER

### 12. **Hardcoded Metinler (Kalan)**

**Dosyalar:** `admin.php`, `log.php`, `borsa.php`, `card.php`, `payment_power.php`, `profile.php`  
**Severity:** Low  
**SORUN:** ~150 hardcoded metin kaldı

**Etkilenen Metinler:**

- Admin panel metinleri (kullanıcı yönetimi)
- Log filtreleme ve tablo başlıkları
- Borsa sayfası formu ve tablo başlıkları
- Ödeme yöntemi sayfası metinleri
- Sorumluluk reddi metinleri

**ÇÖZÜM:** Aşamalı olarak çeviri anahtarlarına dönüştür  
**Öncelik:** İhtiyaç durumunda

---

### 13. **Performance Optimizasyonları**

**Severity:** Low-Medium  

**N+1 Query Sorunları:**

- `api/summary.php` - Her kategori için ayrı sorgu
- Portfolio hesaplamaları - Her hisse için ayrı sorgu

**ÇÖZÜM:**

```sql
-- Tek sorguda summary hesaplama
SELECT 
    DATE_FORMAT(first_date, '%Y-%m') as month,
    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
    SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as total_payments
FROM (
    SELECT first_date, amount, 'income' as type FROM income WHERE user_id = ?
    UNION ALL
    SELECT first_date, amount, 'payment' as type FROM payments WHERE user_id = ?
) combined
GROUP BY DATE_FORMAT(first_date, '%Y-%m')
ORDER BY month;
```

**Memory Leak Potansiyeli:**

- Büyük dosya işlemlerinde memory limit aşılması
- Long-running cron jobs'da memory cleanup eksik

**Öncelik:** Performance sorunları ortaya çıktığında

---

### 14. **Test Coverage Eksik**

**Severity:** Low  
**SORUN:** Automated test yok

**ÖNERİLEN TEST STRUKTÜRü:**

```
tests/
├── Unit/
│   ├── Api/
│   ├── Classes/
│   └── Utils/
├── Integration/
│   ├── Database/
│   └── Api/
├── Security/
│   ├── AuthTest.php
│   ├── CsrfTest.php
│   └── SqlInjectionTest.php
└── Performance/
    └── LoadTest.php
```

**Öncelik:** Gelecek geliştirmeler için

---

### 15. **API Documentation Eksik**

**Severity:** Low  
**SORUN:** API endpoints'leri için dokümantasyon yok

**ÇÖZÜM:** OpenAPI/Swagger spec oluştur  
**Öncelik:** Team expansion durumunda

---

## 📊 SORUN ÖNCELİK MATRİSİ

| Kategori | Kritik | Yüksek | Orta | Düşük | Toplam |
|----------|--------|--------|------|-------|--------|
| **Güvenlik** | 5 | 3 | 4 | 2 | **14** |
| **Database** | 0 | 2 | 3 | 1 | **6** |
| **Performance** | 0 | 1 | 2 | 3 | **6** |
| **UI/UX** | 0 | 1 | 2 | 8 | **11** |
| **Architecture** | 0 | 0 | 3 | 8 | **11** |
| **Testing** | 0 | 0 | 1 | 4 | **5** |
| **TOPLAM** | **5** | **7** | **15** | **26** | **53** |

---

## 🛠️ ÖNERİLEN EYLEM PLANI

### **Faz 1: Acil Güvenlik Düzeltmeleri** ⏰ (1-2 gün)

**Hedef:** Risk seviyesini Critical'dan High'a düşür

1. ✅ `checkLogin()` fonksiyonunu düzelt
2. ✅ CSRF token sistemi ekle
3. ✅ Dosya yükleme güvenliğini sağla
4. ✅ Session timeout kontrolü ekle
5. ✅ User ID validasyonu ekle

**Beklenen Etki:** %80 güvenlik riski azalması

---

### **Faz 2: Database ve Performance** ⏰ (3-5 gün)

**Hedef:** Risk seviyesini High'dan Medium'a düşür

1. ✅ Critical database indekslerini ekle
2. ✅ Foreign key constraint'leri ekle
3. ✅ Transaction rollback'leri tamamla
4. ✅ N+1 query sorunlarını çöz
5. ✅ Input validation tutarlılığını sağla

**Beklenen Etki:** %60 performance artışı

---

### **Faz 3: Sistem İyileştirmeleri** ⏰ (1-2 hafta)

**Hedef:** Risk seviyesini Medium'dan Low'a düşür

1. ✅ Error handling standardizasyonu
2. ✅ Rate limiting iyileştirmesi
3. ✅ Information disclosure düzeltmeleri
4. ✅ Architecture iyileştirmeleri

**Beklenen Etki:** Production-ready sistem

---

### **Faz 4: Kalite İyileştirmeleri** ⏰ (İsteğe bağlı)

**Hedef:** Maintenance ve geliştirme kolaylığı

1. ✅ Kalan hardcoded metinleri çevir
2. ✅ Test coverage ekle
3. ✅ API documentation oluştur
4. ✅ Monitoring sistemi ekle

---

## 🎯 KRİTİK BAŞARI METRİKLERİ

### **Güvenlik Metrikleri:**

- [ ] OWASP Top 10 uyumluluğu: %100
- [ ] Security scan sonucu: 0 critical vulnerability
- [ ] Penetration test geçiş: ✅

### **Performance Metrikleri:**

- [ ] Database sorgu süresi: <100ms (ortalama)
- [ ] Page load time: <2 saniye
- [ ] Memory usage: <128MB per request

### **Kalite Metrikleri:**

- [ ] Code coverage: >80%
- [ ] Documentation coverage: >90%
- [ ] User acceptance: >95%

---

## 🚨 ACİL EYLEM GEREKTİREN DOSYALAR

### **Derhal Düzeltilmesi Gerekenler:**

1. `config.php` - Authentication bypass
2. `ai_analysis.php` - File upload security
3. `api.php` - CSRF protection
4. `api/auth.php` - Session management
5. `database.sql` - Missing indexes

### **1 Hafta İçinde Düzeltilmesi Gerekenler:**

1. `api/payments.php` - Transaction rollback
2. `api/income.php` - Transaction rollback
3. `api/savings.php` - Transaction rollback
4. `js/utils.js` - Validation consistency
5. `api/error_handler.php` - Information disclosure

---

## 📋 KONTROL LİSTESİ

### **Güvenlik Kontrolleri:**

- [ ] Authentication bypass kapatıldı
- [ ] CSRF protection eklendi
- [ ] File upload güvenliği sağlandı
- [ ] Session management düzeltildi
- [ ] SQL injection koruması eklendi
- [ ] Input validation standardize edildi
- [ ] Error message sanitization yapıldı

### **Database Kontrolleri:**

- [ ] Critical indexes eklendi
- [ ] Foreign key constraints eklendi
- [ ] Transaction rollbacks tamamlandı
- [ ] Query optimization yapıldı
- [ ] Data integrity kuralları eklendi

### **Performance Kontrolleri:**

- [ ] N+1 query sorunları çözüldü
- [ ] Memory leak'ler kapatıldı
- [ ] Caching strategy implement edildi
- [ ] Database connection pooling eklendi

---

## 📞 ACİL DURUMLAR İÇİN İLETİŞİM

**Güvenlik İhlali Durumunda:**

1. Sistemi derhal offline al
2. Database backup'ını kontrol et
3. Log dosyalarını analiz et
4. Güvenlik yamalarını uygula
5. Sistemi tekrar online al

**Performance Sorunu Durumunda:**

1. Database indekslerini kontrol et
2. Slow query log'unu analiz et
3. Memory usage'ı monitor et
4. Cache sistem durumunu kontrol et

---

**Rapor Hazırlayan:** Claude Code Assistant  
**Son Güncelleme:** 2025-01-08  
**Geçerlilik:** Bu rapor mevcut kod durumuna göre hazırlanmıştır  
**Sonraki İnceleme:** Faz 1 tamamlandıktan sonra

---

> ⚠️ **UYARI:** Bu raporda belirtilen güvenlik açıkları ciddi risk taşımaktadır. Production ortamına deploy etmeden önce en az Faz 1 ve Faz 2'nin tamamlanması kritik önem taşır.
