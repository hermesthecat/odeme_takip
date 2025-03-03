-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2025 at 10:42 AM
-- Server version: 10.11.11-MariaDB
-- PHP Version: 7.4.33
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
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
    `from_currency` varchar(3) NOT NULL,
    `to_currency` varchar(3) NOT NULL,
    `rate` decimal(10, 4) NOT NULL,
    `date` date NOT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
--
-- Dumping data for table `exchange_rates`
--
INSERT INTO `exchange_rates` (
        `id`,
        `from_currency`,
        `to_currency`,
        `rate`,
        `date`,
        `created_at`
    )
VALUES (
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
    `user_id` int(11) NOT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `amount` decimal(10, 2) NOT NULL,
    `currency` varchar(3) DEFAULT 'TRY',
    `first_date` date NOT NULL,
    `frequency` varchar(20) NOT NULL,
    `status` enum('pending', 'received') DEFAULT 'pending',
    `exchange_rate` decimal(10, 4) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
-- --------------------------------------------------------
--
-- Table structure for table `logs`
--
CREATE TABLE `logs` (
    `id` int(11) NOT NULL,
    `log_method` text NOT NULL,
    `log_text` text NOT NULL,
    `type` text DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
-- --------------------------------------------------------
--
-- Table structure for table `payments`
--
CREATE TABLE `payments` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `name` varchar(100) NOT NULL,
    `amount` decimal(10, 2) NOT NULL,
    `currency` varchar(3) DEFAULT 'TRY',
    `first_date` date NOT NULL,
    `frequency` varchar(20) NOT NULL,
    `status` enum('pending', 'paid') DEFAULT 'pending',
    `exchange_rate` decimal(10, 4) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
-- --------------------------------------------------------
--
-- Table structure for table `portfolio`
--
CREATE TABLE `portfolio` (
    `id` int(11) NOT NULL,
    `sembol` varchar(10) NOT NULL,
    `adet` int(11) NOT NULL,
    `alis_fiyati` decimal(10, 2) NOT NULL,
    `alis_tarihi` timestamp NULL DEFAULT current_timestamp(),
    `anlik_fiyat` decimal(10, 2) DEFAULT 0.00,
    `son_guncelleme` timestamp NULL DEFAULT current_timestamp(),
    `hisse_adi` varchar(255) DEFAULT '',
    `satis_fiyati` decimal(10, 2) DEFAULT NULL,
    `satis_tarihi` timestamp NULL DEFAULT NULL,
    `satis_adet` int(11) DEFAULT NULL,
    `durum` enum(
        'aktif',
        'satildi',
        'kismi_satildi',
        'satis_kaydi'
    ) DEFAULT 'aktif',
    `user_id` int(11) DEFAULT NULL,
    `referans_alis_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
-- --------------------------------------------------------
--
-- Table structure for table `savings`
--
CREATE TABLE `savings` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `update_type` enum('initial', 'update') DEFAULT 'initial',
    `name` varchar(100) NOT NULL,
    `target_amount` decimal(10, 2) NOT NULL,
    `current_amount` decimal(10, 2) DEFAULT 0.00,
    `currency` varchar(3) DEFAULT 'TRY',
    `exchange_rate` decimal(10, 4) DEFAULT NULL,
    `start_date` date NOT NULL,
    `target_date` date NOT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
-- --------------------------------------------------------
--
-- Table structure for table `users`
--
CREATE TABLE `users` (
    `id` int(11) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `base_currency` varchar(3) NOT NULL DEFAULT 'TRY',
    `theme_preference` varchar(10) NOT NULL DEFAULT 'light',
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `remember_token` varchar(64) DEFAULT NULL,
    `is_admin` int(11) DEFAULT NULL,
    `is_active` int(11) NOT NULL DEFAULT 1,
    `last_login` timestamp NULL DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
--
-- Dumping data for table `users`
--
INSERT INTO `users` (
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
VALUES (
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
    `user_id` int(11) NOT NULL,
    `telegram_id` varchar(50) NOT NULL,
    `is_verified` tinyint(1) DEFAULT 0,
    `verification_code` varchar(6) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `telegram_id` (`telegram_id`),
    KEY `user_id` (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
--
-- Table structure for table `ai_analysis_temp`
--
CREATE TABLE `ai_analysis_temp` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `file_name` varchar(255) NOT NULL,
    `file_type` enum('pdf', 'excel') NOT NULL,
    `amount` decimal(10, 2) NOT NULL,
    `currency` varchar(3) DEFAULT 'TRY',
    `category` enum('income', 'expense') NOT NULL,
    `suggested_name` varchar(100) NOT NULL,
    `is_approved` tinyint(1) DEFAULT 0,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
--
-- Indexes for dumped tables
--
--
-- Indexes for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
ADD PRIMARY KEY (`id`),
    ADD KEY `idx_date` (`date`),
    ADD KEY `idx_currencies` (`from_currency`, `to_currency`);
--
-- Indexes for table `income`
--
ALTER TABLE `income`
ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `parent_id` (`parent_id`);
--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
ADD PRIMARY KEY (`id`);
--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `parent_id` (`parent_id`);
--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
ADD PRIMARY KEY (`id`);
--
-- Indexes for table `savings`
--
ALTER TABLE `savings`
ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `parent_id` (`parent_id`);
--
-- Indexes for table `users`
--
ALTER TABLE `users`
ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username` (`username`);
--
-- AUTO_INCREMENT for dumped tables
--
--
-- AUTO_INCREMENT for table `exchange_rates`
--
ALTER TABLE `exchange_rates`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 3;
--
-- AUTO_INCREMENT for table `income`
--
ALTER TABLE `income`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 49;
--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2109;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 34;
--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 61;
--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 13;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;
--
-- Constraints for dumped tables
--
--
-- Constraints for table `income`
--
ALTER TABLE `income`
ADD CONSTRAINT `income_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `income_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `income` (`id`) ON DELETE CASCADE;
--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;
--
-- Constraints for table `savings`
--
ALTER TABLE `savings`
ADD CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `savings_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `savings` (`id`) ON DELETE CASCADE;
COMMIT;
--
-- Constraints for table `telegram_users`
--
ALTER TABLE `telegram_users`
ADD CONSTRAINT `telegram_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
--
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;