-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS butce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE butce_db;
-- Kullanıcılar tablosu
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    base_currency VARCHAR(3) DEFAULT 'TRY',
    theme_preference VARCHAR(10) DEFAULT 'light',
    remember_token VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Gelirler tablosu
CREATE TABLE income (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,      -- Ana kayıt için null, child kayıtlar için ana kaydın id'si
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    first_date DATE NOT NULL,        -- Gelir tarihi
    frequency VARCHAR(20) NOT NULL,   -- Tekrarlama sıklığı
    next_date DATE NOT NULL,         -- Bir sonraki gelir (child kayıtlar için null)
    status ENUM('pending', 'received') DEFAULT 'pending',  -- Gelir durumu
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    exchange_rate DECIMAL(10, 4) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES income(id) ON DELETE CASCADE
);
-- Birikimler tablosu
CREATE TABLE savings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(10, 2) NOT NULL,
    current_amount DECIMAL(10, 2) DEFAULT 0,
    currency VARCHAR(3) DEFAULT 'TRY',
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
-- Ödemeler tablosu
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,      -- Ana kayıt için null, child kayıtlar için ana kaydın id'si
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'TRY',
    first_date DATE NOT NULL,        -- Ödeme tarihi
    frequency VARCHAR(20) NOT NULL,   -- Tekrarlama sıklığı
    next_date DATE NOT NULL,         -- Bir sonraki ödeme (child kayıtlar için null)
    status ENUM('pending', 'paid') DEFAULT 'pending',  -- Ödeme durumu
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    exchange_rate DECIMAL(10, 4) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES payments(id) ON DELETE CASCADE
);
-- Kur geçmişi tablosu
CREATE TABLE exchange_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(10, 4) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_currencies (from_currency, to_currency)
);
-- Örnek kullanıcı ekleme (şifre: 123456)
INSERT INTO users (username, password)
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    );