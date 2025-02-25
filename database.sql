-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 05:14 PM
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
        1,
        'usd',
        'try',
        36.2504,
        '2025-02-19',
        '2025-02-19 13:56:22'
    ),
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
--
-- Dumping data for table `payments`
--
INSERT INTO `payments` (
        `id`,
        `user_id`,
        `parent_id`,
        `name`,
        `amount`,
        `currency`,
        `first_date`,
        `frequency`,
        `status`,
        `exchange_rate`,
        `created_at`
    )
VALUES (
        29,
        1,
        NULL,
        'sdsddssd',
        150.00,
        'TRY',
        '2025-02-25',
        'monthly',
        'pending',
        NULL,
        '2025-02-25 13:51:47'
    ),
    (
        30,
        1,
        29,
        'sdsddssd',
        150.00,
        'TRY',
        '2025-03-25',
        'monthly',
        'pending',
        NULL,
        '2025-02-25 13:51:47'
    ),
    (
        31,
        1,
        29,
        'sdsddssd',
        150.00,
        'TRY',
        '2025-04-25',
        'monthly',
        'pending',
        NULL,
        '2025-02-25 13:51:47'
    ),
    (
        32,
        1,
        29,
        'sdsddssd',
        150.00,
        'TRY',
        '2025-05-25',
        'monthly',
        'pending',
        NULL,
        '2025-02-25 13:51:47'
    ),
    (
        33,
        1,
        29,
        'sdsddssd',
        150.00,
        'TRY',
        '2025-06-25',
        'monthly',
        'pending',
        NULL,
        '2025-02-25 13:51:47'
    );
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
    `durum` enum('aktif', 'satildi', 'kismi_satildi') DEFAULT 'aktif',
    `user_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_turkish_ci;
--
-- Dumping data for table `portfolio`
--
INSERT INTO `portfolio` (
        `id`,
        `sembol`,
        `adet`,
        `alis_fiyati`,
        `alis_tarihi`,
        `anlik_fiyat`,
        `son_guncelleme`,
        `hisse_adi`,
        `satis_fiyati`,
        `satis_tarihi`,
        `satis_adet`,
        `durum`,
        `user_id`
    )
VALUES (
        14,
        'PGSUS',
        250,
        231.15,
        '2025-02-25 09:00:00',
        233.55,
        '2025-02-25 10:29:53',
        'PEGASUS',
        233.55,
        '2025-02-25 10:30:34',
        250,
        'satildi',
        1
    ),
    (
        15,
        'PGSUS',
        250,
        234.25,
        '2025-02-25 10:00:00',
        233.55,
        '2025-02-25 10:29:53',
        'PEGASUS',
        233.55,
        '2025-02-25 10:46:39',
        5,
        'kismi_satildi',
        1
    ),
    (
        16,
        'ISCTR',
        500,
        14.49,
        '2025-02-25 10:00:00',
        14.49,
        '2025-02-25 10:29:53',
        'IS BANKASI (C)',
        NULL,
        NULL,
        NULL,
        'aktif',
        1
    ),
    (
        17,
        'ASELS',
        250,
        81.88,
        '2025-02-25 10:16:02',
        81.83,
        '2025-02-25 10:29:53',
        'ASELSAN',
        NULL,
        NULL,
        NULL,
        'aktif',
        1
    );
-- --------------------------------------------------------
--
-- Table structure for table `savings`
--
CREATE TABLE `savings` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `name` varchar(100) NOT NULL,
    `target_amount` decimal(10, 2) NOT NULL,
    `current_amount` decimal(10, 2) DEFAULT 0.00,
    `currency` varchar(3) DEFAULT 'TRY',
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
    `is_admin` int(11) DEFAULT NULL
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
        `is_admin`
    )
VALUES (
        1,
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'TRY',
        'dark',
        '2025-02-19 11:55:56',
        '0f00406e2806ffe53ab209d0ab1e42e4424aa99c29391182b8cc4133e5544be7',
        1
    );
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
    ADD KEY `user_id` (`user_id`);
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
    AUTO_INCREMENT = 33;
--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 937;
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
    AUTO_INCREMENT = 18;
--
-- AUTO_INCREMENT for table `savings`
--
ALTER TABLE `savings`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;
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
ADD CONSTRAINT `savings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;