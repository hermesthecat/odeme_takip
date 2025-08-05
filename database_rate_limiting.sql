-- Rate Limiting Database Table
-- MySQL tabanlı rate limiting sistemi için gerekli tablolar

-- Rate limiting kayıtları tablosu
CREATE TABLE IF NOT EXISTS `rate_limits` (
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

-- Rate limit kuralları tablosu
CREATE TABLE IF NOT EXISTS `rate_limit_rules` (
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

-- Rate limiting ihlalleri için log tablosu
CREATE TABLE IF NOT EXISTS `rate_limit_violations` (
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
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci COMMENT='Rate limit violation logs';

-- Varsayılan rate limit kuralları
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

-- Temizlik için stored procedure
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

-- Otomatik temizlik için event scheduler (isteğe bağlı)
-- SET GLOBAL event_scheduler = ON;
-- CREATE EVENT IF NOT EXISTS clean_rate_limits
-- ON SCHEDULE EVERY 1 HOUR
-- DO CALL CleanExpiredRateLimits();