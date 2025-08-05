-- PECUNIA - COMPLETE DATABASE SCHEMA
-- Personal Finance Management System

-- MySQL Configuration
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL COMMENT 'Kullanıcı adı',
    `password` varchar(255) NOT NULL COMMENT 'Şifre (hash)',
    `base_currency` varchar(3) NOT NULL DEFAULT 'TRY' COMMENT 'Temel para birimi',
    `theme_preference` varchar(10) NOT NULL DEFAULT 'light' COMMENT 'Tema tercihi',
    `language` varchar(5) DEFAULT 'tr' COMMENT 'User preferred language (tr, en, etc.)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    `remember_token` varchar(64) DEFAULT NULL COMMENT 'Hatırlama tokeni',
    `is_admin` tinyint(1) DEFAULT 0 COMMENT 'Yönetici mi? (1=evet, 0=hayır)',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Aktif mi? (1=evet, 0=hayır)',
    `last_login` timestamp NULL DEFAULT NULL COMMENT 'Son giriş tarihi',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    KEY `idx_remember_token` (`remember_token`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_users_language` (`language`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Kullanıcı kayıtları tablosu';

-- --------------------------------------------------------
-- Table structure for table `exchange_rates`
-- --------------------------------------------------------
CREATE TABLE `exchange_rates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `from_currency` varchar(3) NOT NULL COMMENT 'Kaynak para birimi kodu',
    `to_currency` varchar(3) NOT NULL COMMENT 'Hedef para birimi kodu',
    `rate` decimal(10, 4) NOT NULL COMMENT 'Dönüşüm oranı',
    `date` date NOT NULL COMMENT 'Kur tarihi',
    `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Son güncelleme tarihi',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_currency_date` (`from_currency`, `to_currency`, `date`),
    KEY `idx_currencies_date` (`from_currency`, `to_currency`, `date`),
    KEY `idx_exchange_currencies` (`from_currency`, `to_currency`),
    KEY `idx_exchange_date` (`date`),
    KEY `idx_created_at` (`created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Döviz kurları tablosu';

-- --------------------------------------------------------
-- Table structure for table `income`
-- --------------------------------------------------------
CREATE TABLE `income` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `parent_id` int(11) DEFAULT NULL COMMENT 'Üst gelir kaydı ID referansı',
    `name` varchar(100) NOT NULL COMMENT 'Gelir adı',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Gelir miktarı',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `first_date` date NOT NULL COMMENT 'İlk gelir tarihi',
    `frequency` varchar(20) NOT NULL COMMENT 'Gelir sıklığı (aylık, yıllık, vb.)',
    `status` enum('pending', 'received') DEFAULT 'pending' COMMENT 'Gelir durumu',
    `exchange_rate` decimal(10, 4) DEFAULT NULL COMMENT 'Döviz kuru (varsa)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_user_date` (`user_id`, `first_date`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_status` (`status`),
    KEY `idx_income_user_status` (`user_id`, `status`),
    KEY `idx_income_compound` (`user_id`, `first_date`, `status`),
    KEY `idx_income_parent_user` (`parent_id`, `user_id`),
    CONSTRAINT `fk_income_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_income_parent` FOREIGN KEY (`parent_id`) REFERENCES `income` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Gelir kayıtları tablosu';

-- --------------------------------------------------------
-- Table structure for table `card`
-- --------------------------------------------------------
CREATE TABLE `card` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `name` varchar(100) NOT NULL COMMENT 'Kart adı',
    `card_number` varchar(20) DEFAULT NULL COMMENT 'Kart numarası (maskelenmiş)',
    `card_type` enum('credit', 'debit') DEFAULT 'credit' COMMENT 'Kart tipi',
    `limit_amount` decimal(10, 2) DEFAULT NULL COMMENT 'Kart limiti',
    `current_balance` decimal(10, 2) DEFAULT 0.00 COMMENT 'Mevcut bakiye',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `is_active` tinyint(1) DEFAULT 1 COMMENT 'Aktif mi? (1=evet, 0=hayır)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_active` (`is_active`),
    CONSTRAINT `fk_card_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Kredi/Banka kartları tablosu';

-- --------------------------------------------------------
-- Table structure for table `payments`
-- --------------------------------------------------------
CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `parent_id` int(11) DEFAULT NULL COMMENT 'Üst ödeme kaydı ID referansı',
    `card_id` int(11) DEFAULT NULL COMMENT 'Kart ID referansı',
    `name` varchar(100) NOT NULL COMMENT 'Ödeme adı',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Ödeme miktarı',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `first_date` date NOT NULL COMMENT 'İlk ödeme tarihi',
    `frequency` varchar(20) NOT NULL COMMENT 'Ödeme sıklığı (aylık, yıllık, vb.)',
    `status` enum('pending', 'paid') DEFAULT 'pending' COMMENT 'Ödeme durumu',
    `exchange_rate` decimal(10, 4) DEFAULT NULL COMMENT 'Döviz kuru (varsa)',
    `payment_power` int(11) DEFAULT 0 COMMENT 'Ödeme önceliği/gücü',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_user_date` (`user_id`, `first_date`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_status` (`status`),
    KEY `idx_payment_power` (`payment_power`),
    KEY `idx_card_id` (`card_id`),
    KEY `idx_payments_user_status` (`user_id`, `status`),
    KEY `idx_payments_compound` (`user_id`, `first_date`, `status`),
    KEY `idx_payments_parent_user` (`parent_id`, `user_id`),
    CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_parent` FOREIGN KEY (`parent_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_payments_card` FOREIGN KEY (`card_id`) REFERENCES `card` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Ödeme kayıtları tablosu';

-- --------------------------------------------------------
-- Table structure for table `savings`
-- --------------------------------------------------------
CREATE TABLE `savings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `parent_id` int(11) DEFAULT NULL COMMENT 'Üst birikim kaydı ID referansı',
    `update_type` enum('initial', 'update') DEFAULT 'initial' COMMENT 'Güncelleme tipi',
    `name` varchar(100) NOT NULL COMMENT 'Birikim adı',
    `target_amount` decimal(10, 2) NOT NULL COMMENT 'Hedef miktar',
    `current_amount` decimal(10, 2) DEFAULT 0.00 COMMENT 'Mevcut miktar',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `exchange_rate` decimal(10, 4) DEFAULT NULL COMMENT 'Döviz kuru (varsa)',
    `start_date` date NOT NULL COMMENT 'Başlangıç tarihi',
    `target_date` date NOT NULL COMMENT 'Hedef tarihi',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_user_date` (`user_id`, `start_date`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_target_date` (`target_date`),
    KEY `idx_savings_parent_user` (`parent_id`, `user_id`),
    CONSTRAINT `fk_savings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_savings_parent` FOREIGN KEY (`parent_id`) REFERENCES `savings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Birikim kayıtları tablosu';

-- --------------------------------------------------------
-- Table structure for table `portfolio`
-- --------------------------------------------------------
CREATE TABLE `portfolio` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL COMMENT 'Kullanıcı ID referansı',
    `sembol` varchar(10) NOT NULL COMMENT 'Hisse senedi sembolü',
    `adet` int(11) NOT NULL COMMENT 'Hisse adedi',
    `alis_fiyati` decimal(10, 2) NOT NULL COMMENT 'Alış fiyatı',
    `alis_tarihi` timestamp NULL DEFAULT current_timestamp() COMMENT 'Alış tarihi',
    `anlik_fiyat` decimal(10, 2) DEFAULT 0.00 COMMENT 'Anlık fiyat',
    `son_guncelleme` timestamp NULL DEFAULT current_timestamp() COMMENT 'Son güncelleme tarihi',
    `hisse_adi` varchar(255) DEFAULT '' COMMENT 'Hisse senedi adı',
    `satis_fiyati` decimal(10, 2) DEFAULT NULL COMMENT 'Satış fiyatı',
    `satis_tarihi` timestamp NULL DEFAULT NULL COMMENT 'Satış tarihi',
    `satis_adet` int(11) DEFAULT NULL COMMENT 'Satış adedi',
    `durum` enum('aktif', 'satildi', 'kismi_satildi', 'satis_kaydi') DEFAULT 'aktif' COMMENT 'Hisse durumu',
    `referans_alis_id` int(11) DEFAULT NULL COMMENT 'Referans alış kaydı ID',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_sembol` (`sembol`),
    KEY `idx_durum` (`durum`),
    KEY `idx_referans_alis_id` (`referans_alis_id`),
    KEY `idx_portfolio_user_symbol` (`user_id`, `sembol`),
    CONSTRAINT `fk_portfolio_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_portfolio_referans` FOREIGN KEY (`referans_alis_id`) REFERENCES `portfolio` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Portföy kayıtları tablosu';

-- --------------------------------------------------------
-- Table structure for table `logs`
-- --------------------------------------------------------
CREATE TABLE `logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `log_method` text NOT NULL COMMENT 'Log metodu',
    `log_text` text NOT NULL COMMENT 'Log metni',
    `type` text DEFAULT NULL COMMENT 'Log tipi',
    `user_id` int(11) DEFAULT NULL COMMENT 'Kullanıcı ID referansı',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_type` (`type`(255)),
    CONSTRAINT `fk_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Sistem log kayıtları';

-- --------------------------------------------------------
-- Table structure for table `telegram_users`
-- --------------------------------------------------------
CREATE TABLE `telegram_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `telegram_id` varchar(50) NOT NULL COMMENT 'Telegram kullanıcı ID',
    `is_verified` tinyint(1) DEFAULT 0 COMMENT 'Doğrulanmış mı? (1=evet, 0=hayır)',
    `verification_code` varchar(6) DEFAULT NULL COMMENT 'Doğrulama kodu',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    UNIQUE KEY `telegram_id` (`telegram_id`),
    KEY `user_id` (`user_id`),
    KEY `idx_telegram_chat_id` (`telegram_id`),
    CONSTRAINT `fk_telegram_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Telegram kullanıcı bağlantıları';

-- --------------------------------------------------------
-- Table structure for table `ai_analysis_temp`
-- --------------------------------------------------------
CREATE TABLE `ai_analysis_temp` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `file_name` varchar(255) NOT NULL COMMENT 'Dosya adı',
    `file_type` enum('pdf', 'excel') NOT NULL COMMENT 'Dosya tipi',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Miktar',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `category` enum('income', 'expense') NOT NULL COMMENT 'Kategori (gelir/gider)',
    `suggested_name` varchar(100) NOT NULL COMMENT 'Önerilen isim',
    `description` text DEFAULT NULL COMMENT 'Açıklama',
    `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Onaylandı mı? (1=evet, 0=hayır)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `idx_is_approved` (`is_approved`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_ai_analysis_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'AI analiz geçici tablosu';


-- --------------------------------------------------------
-- Table structure for table `rate_limits`
-- --------------------------------------------------------
CREATE TABLE `rate_limits` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `identifier` varchar(255) NOT NULL COMMENT 'IP, user_id, or API key',
    `endpoint` varchar(100) NOT NULL COMMENT 'API endpoint or action',
    `request_count` int(11) NOT NULL DEFAULT 1,
    `window_start` datetime NOT NULL COMMENT 'Rate limit window start time',
    `expires_at` datetime NOT NULL COMMENT 'When this record expires',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_limit` (`identifier`, `endpoint`, `window_start`),
    KEY `idx_identifier_endpoint` (`identifier`, `endpoint`),
    KEY `idx_expires_at` (`expires_at`),
    KEY `idx_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Rate limiting records';

-- --------------------------------------------------------
-- Table structure for table `rate_limit_rules`
-- --------------------------------------------------------
CREATE TABLE `rate_limit_rules` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `endpoint` varchar(100) NOT NULL COMMENT 'API endpoint pattern',
    `max_requests` int(11) NOT NULL COMMENT 'Maximum requests allowed',
    `window_minutes` int(11) NOT NULL COMMENT 'Time window in minutes',
    `identifier_type` enum('ip','user','api_key','combined') NOT NULL DEFAULT 'ip' COMMENT 'What to track',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_rule` (`endpoint`, `identifier_type`),
    KEY `idx_endpoint` (`endpoint`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Rate limiting rules configuration';

-- --------------------------------------------------------
-- Table structure for table `rate_limit_violations`
-- --------------------------------------------------------
CREATE TABLE `rate_limit_violations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `identifier` varchar(255) NOT NULL,
    `endpoint` varchar(100) NOT NULL,
    `request_count` int(11) NOT NULL,
    `max_allowed` int(11) NOT NULL,
    `user_agent` text DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `violation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_identifier` (`identifier`),
    KEY `idx_endpoint` (`endpoint`),
    KEY `idx_violation_time` (`violation_time`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_rate_limit_violations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Rate limit violation logs';


-- Default admin user
INSERT INTO `users` (
    `id`,
    `username`,
    `password`,
    `base_currency`,
    `theme_preference`,
    `language`,
    `created_at`,
    `remember_token`,
    `is_admin`,
    `is_active`,
    `last_login`
) VALUES (
    1,
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'TRY',
    'dark',
    'tr',
    '2025-02-19 11:55:56',
    '25e8e6950886f32f9af41233af993d7e18a51cdfdfdf0877f26023bdf3fa8587',
    1,
    1,
    '2025-02-28 07:22:42'
);

-- Sample exchange rate
INSERT INTO `exchange_rates` (
    `id`,
    `from_currency`,
    `to_currency`,
    `rate`,
    `date`,
    `created_at`
) VALUES (
    2,
    'usd',
    'try',
    36.4467,
    '2025-02-25',
    '2025-02-25 07:27:59'
);

-- Default rate limit rules
INSERT INTO `rate_limit_rules` (`endpoint`, `max_requests`, `window_minutes`, `identifier_type`) VALUES
-- API genel limitleri
('api.php', 100, 5, 'ip'),                    -- Genel API: 100 req/5min per IP
('api.php', 200, 5, 'user'),                  -- Genel API: 200 req/5min per user

-- Özel endpoint limitleri  
('ai_analysis', 5, 10, 'user'),               -- AI analiz: 5 req/10min per user
('file_upload', 10, 5, 'user'),               -- File upload: 10 req/5min per user
('exchange_rate_refresh', 20, 10, 'user'),    -- Döviz: 20 req/10min per user
('telegram_webhook', 50, 1, 'ip'),            -- Telegram: 50 req/1min per IP

-- Authentication limitleri
('login', 5, 15, 'ip'),                       -- Login: 5 attempt/15min per IP
('register', 3, 60, 'ip'),                    -- Register: 3 req/60min per IP
('password_reset', 3, 60, 'ip'),              -- Password reset: 3 req/60min per IP

-- Borsa API limitleri
('borsa_update', 10, 1, 'user'),              -- Borsa güncelleme: 10 req/1min per user

-- Backup ve admin limitleri
('admin_actions', 20, 5, 'user'),             -- Admin: 20 req/5min per user
('export_data', 5, 15, 'user'),               -- Export: 5 req/15min per user

-- Brute force koruması
('api_error', 50, 5, 'ip'),                   -- Error limit: 50 error/5min per IP
('suspicious_activity', 10, 60, 'ip')         -- Şüpheli aktivite: 10 req/60min per IP
ON DUPLICATE KEY UPDATE 
  `max_requests` = VALUES(`max_requests`),
  `window_minutes` = VALUES(`window_minutes`),
  `updated_at` = CURRENT_TIMESTAMP;

ALTER TABLE `users` AUTO_INCREMENT = 2;
ALTER TABLE `exchange_rates` AUTO_INCREMENT = 3;
ALTER TABLE `income` AUTO_INCREMENT = 1;
ALTER TABLE `payments` AUTO_INCREMENT = 1;
ALTER TABLE `savings` AUTO_INCREMENT = 1;
ALTER TABLE `portfolio` AUTO_INCREMENT = 1;
ALTER TABLE `logs` AUTO_INCREMENT = 1;
ALTER TABLE `card` AUTO_INCREMENT = 1;
ALTER TABLE `telegram_users` AUTO_INCREMENT = 1;
ALTER TABLE `ai_analysis_temp` AUTO_INCREMENT = 1;
ALTER TABLE `rate_limits` AUTO_INCREMENT = 1;
ALTER TABLE `rate_limit_rules` AUTO_INCREMENT = 13;
ALTER TABLE `rate_limit_violations` AUTO_INCREMENT = 1;


-- Rate limit cleanup procedure
DELIMITER //
CREATE PROCEDURE CleanExpiredRateLimits()
BEGIN
    -- Süresi dolmuş rate limit kayıtlarını temizle
    DELETE FROM rate_limits WHERE expires_at < NOW();
    
    -- 30 günden eski violation loglarını temizle
    DELETE FROM rate_limit_violations WHERE violation_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- İstatistik döndür
    SELECT 
        (SELECT COUNT(*) FROM rate_limits) as active_limits,
        (SELECT COUNT(*) FROM rate_limit_violations WHERE violation_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as violations_24h;
END //
DELIMITER ;




COMMIT;

