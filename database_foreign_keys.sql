-- Database Integrity İyileştirmeleri
-- Foreign Key Constraints ekleniyor
-- Tarihi: 2025-01-08

-- ========================================
-- FOREIGN KEY CONSTRAINTS (Data Integrity)
-- ========================================

-- IMPORTANT: Bu script çalıştırılmadan önce
-- orphaned records'ları temizlemek gerekebilir!

-- Orphaned records kontrolü:
-- SELECT * FROM payments WHERE user_id NOT IN (SELECT id FROM users);
-- SELECT * FROM income WHERE user_id NOT IN (SELECT id FROM users);

-- ========================================
-- USER REFERENCES (En kritik)
-- ========================================

-- Payments tablosu - users tablosuna referans
ALTER TABLE `payments` 
ADD CONSTRAINT `fk_payments_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Income tablosu - users tablosuna referans
ALTER TABLE `income` 
ADD CONSTRAINT `fk_income_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Savings tablosu - users tablosuna referans
ALTER TABLE `savings` 
ADD CONSTRAINT `fk_savings_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Portfolio tablosu - users tablosuna referans
ALTER TABLE `portfolio` 
ADD CONSTRAINT `fk_portfolio_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Card tablosu - users tablosuna referans
ALTER TABLE `card` 
ADD CONSTRAINT `fk_card_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Logs tablosu - users tablosuna referans (NULL allowed)
ALTER TABLE `logs` 
ADD CONSTRAINT `fk_logs_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Telegram users tablosu - users tablosuna referans
ALTER TABLE `telegram_users` 
ADD CONSTRAINT `fk_telegram_user` 
FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- ========================================
-- PARENT-CHILD RELATIONSHIPS
-- ========================================

-- Payments parent-child relationship
ALTER TABLE `payments` 
ADD CONSTRAINT `fk_payments_parent` 
FOREIGN KEY (`parent_id`) REFERENCES `payments`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Income parent-child relationship
ALTER TABLE `income` 
ADD CONSTRAINT `fk_income_parent` 
FOREIGN KEY (`parent_id`) REFERENCES `income`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Savings parent-child relationship
ALTER TABLE `savings` 
ADD CONSTRAINT `fk_savings_parent` 
FOREIGN KEY (`parent_id`) REFERENCES `savings`(`id`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- ========================================
-- CARD RELATIONSHIPS
-- ========================================

-- Payments - Card relationship (NULL allowed)
ALTER TABLE `payments` 
ADD CONSTRAINT `fk_payments_card` 
FOREIGN KEY (`card_id`) REFERENCES `card`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ========================================
-- PORTFOLIO RELATIONSHIPS  
-- ========================================

-- Portfolio referans satış relationship (NULL allowed)
ALTER TABLE `portfolio` 
ADD CONSTRAINT `fk_portfolio_referans` 
FOREIGN KEY (`referans_alis_id`) REFERENCES `portfolio`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ========================================
-- VALIDATION QUERIES
-- ========================================

-- Bu foreign key'ler eklendikten sonra test edin:

-- 1. User silme testi (CASCADE çalışmalı):
-- DELETE FROM users WHERE id = [test_user_id];
-- -- Tüm related records otomatik silinmeli

-- 2. Parent silme testi (CASCADE çalışmalı):
-- DELETE FROM payments WHERE id = [parent_payment_id];
-- -- Tüm child payments otomatik silinmeli

-- 3. Card silme testi (SET NULL çalışmalı):
-- DELETE FROM card WHERE id = [card_id];
-- -- Related payments'lardaki card_id NULL olmalı

-- ========================================
-- BENEFITS OF THESE CONSTRAINTS
-- ========================================

-- 1. REFERENTIAL INTEGRITY:
--    - Orphaned records impossible
--    - Data consistency guaranteed
--    - Automatic cleanup on deletes

-- 2. CASCADE DELETES:
--    - User silme → Tüm data otomatik temizlenir
--    - Parent silme → Tüm children otomatik silinir
--    - Memory leak prevention

-- 3. DATA VALIDATION:
--    - Invalid user_id INSERT/UPDATE rejected
--    - Invalid parent_id references rejected
--    - Database-level validation

-- 4. PERFORMANCE:
--    - Foreign key indexes automatic
--    - JOIN operations faster
--    - Query optimizer benefits

-- ========================================
-- ROLLBACK SCRIPT (Emergency)
-- ========================================

-- Eğer sorun olursa bu foreign key'leri kaldırmak için:
-- ALTER TABLE payments DROP FOREIGN KEY fk_payments_user;
-- ALTER TABLE income DROP FOREIGN KEY fk_income_user;
-- ALTER TABLE savings DROP FOREIGN KEY fk_savings_user;
-- ALTER TABLE portfolio DROP FOREIGN KEY fk_portfolio_user;
-- ALTER TABLE card DROP FOREIGN KEY fk_card_user;
-- ALTER TABLE logs DROP FOREIGN KEY fk_logs_user;
-- ALTER TABLE telegram_users DROP FOREIGN KEY fk_telegram_user;
-- ALTER TABLE payments DROP FOREIGN KEY fk_payments_parent;
-- ALTER TABLE income DROP FOREIGN KEY fk_income_parent;
-- ALTER TABLE savings DROP FOREIGN KEY fk_savings_parent;
-- ALTER TABLE payments DROP FOREIGN KEY fk_payments_card;
-- ALTER TABLE portfolio DROP FOREIGN KEY fk_portfolio_referans;

-- ========================================
-- MONITORING
-- ========================================

-- Foreign key durumunu kontrol etmek için:
-- SHOW CREATE TABLE payments;
-- SELECT * FROM information_schema.KEY_COLUMN_USAGE 
-- WHERE REFERENCED_TABLE_NAME IS NOT NULL 
-- AND TABLE_SCHEMA = 'odeme_takip';