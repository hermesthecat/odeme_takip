-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2025 at 10:42 AM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 7.4.33
SET
    SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET
    time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;

/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `odeme_takip`
--

-- --------------------------------------------------------
--
-- Table structure for table `exchange_rates`
--
CREATE TABLE `exchange_rates` (
    `id` int(11) NOT NULL,
    `from_currency` varchar(3) NOT NULL COMMENT 'Kaynak para birimi kodu',
    `to_currency` varchar(3) NOT NULL COMMENT 'Hedef para birimi kodu',
    `rate` decimal(10, 4) NOT NULL COMMENT 'Dönüşüm oranı',
    `date` date NOT NULL COMMENT 'Kur tarihi',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Döviz kurları tablosu';

--
-- Dumping data for table `exchange_rates`
--
INSERT INTO
    `exchange_rates` (
        `id`,
        `from_currency`,
        `to_currency`,
        `rate`,
        `date`,
        `created_at`
    )
VALUES
    (
        2,
        'usd',
        'try',
        36.4467,
        '2025-02-25',
        '2025-02-25 07:27:59'
    );

-- --------------------------------------------------------
--
-- Table structure for table `income`
--
CREATE TABLE `income` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `parent_id` int(11) DEFAULT NULL COMMENT 'Üst gelir kaydı ID referansı',
    `name` varchar(100) NOT NULL COMMENT 'Gelir adı',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Gelir miktarı',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `first_date` date NOT NULL COMMENT 'İlk gelir tarihi',
    `frequency` varchar(20) NOT NULL COMMENT 'Gelir sıklığı (aylık, yıllık, vb.)',
    `status` enum('pending', 'received') DEFAULT 'pending' COMMENT 'Gelir durumu',
    `exchange_rate` decimal(10, 4) DEFAULT NULL COMMENT 'Döviz kuru (varsa)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Gelir kayıtları tablosu';

-- --------------------------------------------------------
--
-- Table structure for table `logs`
--
CREATE TABLE `logs` (
    `id` int(11) NOT NULL,
    `log_method` text NOT NULL COMMENT 'Log metodu',
    `log_text` text NOT NULL COMMENT 'Log metni',
    `type` text DEFAULT NULL COMMENT 'Log tipi',
    `user_id` int(11) DEFAULT NULL COMMENT 'Kullanıcı ID referansı',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Sistem log kayıtları';

-- --------------------------------------------------------
--
-- Table structure for table `payments`
--
CREATE TABLE `payments` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `parent_id` int(11) DEFAULT NULL COMMENT 'Üst ödeme kaydı ID referansı',
    `name` varchar(100) NOT NULL COMMENT 'Ödeme adı',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Ödeme miktarı',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `first_date` date NOT NULL COMMENT 'İlk ödeme tarihi',
    `frequency` varchar(20) NOT NULL COMMENT 'Ödeme sıklığı (aylık, yıllık, vb.)',
    `status` enum('pending', 'paid') DEFAULT 'pending' COMMENT 'Ödeme durumu',
    `exchange_rate` decimal(10, 4) DEFAULT NULL COMMENT 'Döviz kuru (varsa)',
    `payment_power` int(11) DEFAULT 0 COMMENT 'Ödeme önceliği/gücü',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Ödeme kayıtları tablosu';

-- --------------------------------------------------------
--
-- Table structure for table `portfolio`
--
CREATE TABLE `portfolio` (
    `id` int(11) NOT NULL,
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
    `durum` enum(
        'aktif',
        'satildi',
        'kismi_satildi',
        'satis_kaydi'
    ) DEFAULT 'aktif' COMMENT 'Hisse durumu',
    `user_id` int(11) DEFAULT NULL COMMENT 'Kullanıcı ID referansı',
    `referans_alis_id` int(11) DEFAULT NULL COMMENT 'Referans alış kaydı ID'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Portföy kayıtları tablosu';

-- --------------------------------------------------------
--
-- Table structure for table `savings`
--
CREATE TABLE `savings` (
    `id` int(11) NOT NULL,
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
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Birikim kayıtları tablosu';

-- --------------------------------------------------------
--
-- Table structure for table `users`
--
CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `username` varchar(50) NOT NULL COMMENT 'Kullanıcı adı',
    `password` varchar(255) NOT NULL COMMENT 'Şifre (hash)',
    `base_currency` varchar(3) NOT NULL DEFAULT 'TRY' COMMENT 'Temel para birimi',
    `theme_preference` varchar(10) NOT NULL DEFAULT 'light' COMMENT 'Tema tercihi',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    `remember_token` varchar(64) DEFAULT NULL COMMENT 'Hatırlama tokeni',
    `is_admin` tinyint(1) DEFAULT 0 COMMENT 'Yönetici mi? (1=evet, 0=hayır)',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Aktif mi? (1=evet, 0=hayır)',
    `last_login` timestamp NULL DEFAULT NULL COMMENT 'Son giriş tarihi'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Kullanıcı kayıtları tablosu';

--
-- Dumping data for table `users`
--
INSERT INTO
    `users` (
        `id`,
        `username`,
        `password`,
        `base_currency`,
        `theme_preference`,
        `created_at`,
        `remember_token`,
        `is_admin`,
        `is_active`,
        `last_login`
    )
VALUES
    (
        1,
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'TRY',
        'dark',
        '2025-02-19 11:55:56',
        '25e8e6950886f32f9af41233af993d7e18a51cdfdfdf0877f26023bdf3fa8587',
        1,
        1,
        '2025-02-28 07:22:42'
    );

--
-- Table structure for table `telegram_users`
--
DROP TABLE IF EXISTS `telegram_users`;

CREATE TABLE `telegram_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `telegram_id` varchar(50) NOT NULL COMMENT 'Telegram kullanıcı ID',
    `is_verified` tinyint(1) DEFAULT 0 COMMENT 'Doğrulanmış mı? (1=evet, 0=hayır)',
    `verification_code` varchar(6) DEFAULT NULL COMMENT 'Doğrulama kodu',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    UNIQUE KEY `telegram_id` (`telegram_id`),
    KEY `user_id` (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'Telegram kullanıcı bağlantıları';

--
-- Table structure for table `ai_analysis_temp`
--
CREATE TABLE `ai_analysis_temp` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL COMMENT 'Kullanıcı ID referansı',
    `file_name` varchar(255) NOT NULL COMMENT 'Dosya adı',
    `file_type` enum('pdf', 'excel') NOT NULL COMMENT 'Dosya tipi',
    `amount` decimal(10, 2) NOT NULL COMMENT 'Miktar',
    `currency` varchar(3) DEFAULT 'TRY' COMMENT 'Para birimi',
    `category` enum('income', 'expense') NOT NULL COMMENT 'Kategori (gelir/gider)',
    `suggested_name` varchar(100) NOT NULL COMMENT 'Önerilen isim',
    `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Onaylandı mı? (1=evet, 0=hayır)',
    `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Kayıt oluşturma tarihi',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci COMMENT = 'AI analiz geçici tablosu';

--
-- Indexes for dumped tables
--
--
-- Indexes for table `exchange_rates`
--
ALTER TABLE