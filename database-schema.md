# Database Schema Design for Butce Application

## Overview
This document outlines the database schema design for migrating the Butce application from localStorage to MySQL. The schema is designed to support all current functionality while also allowing for future enhancements.

## Tables

### users
Users table for future multi-user support:
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### categories
Budget categories with monthly limits:
```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    monthly_limit DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_per_user (user_id, name)
);
```

### monthly_budgets
Monthly budget limits:
```sql
CREATE TABLE monthly_budgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    limit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_budget_per_month (user_id, year, month)
);
```

### payments
Recurring and one-time payments:
```sql
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'TRY',
    first_payment_date DATE NOT NULL,
    frequency INT NOT NULL DEFAULT 0, -- 0: one-time, 1: monthly, etc.
    repeat_count INT NULL,           -- NULL: infinite
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

### payment_statuses
Track which months payments have been made:
```sql
CREATE TABLE payment_statuses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    is_paid BOOLEAN NOT NULL DEFAULT FALSE,
    paid_at TIMESTAMP NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_payment_status (payment_id, year, month)
);
```

### incomes
Recurring and one-time incomes:
```sql
CREATE TABLE incomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'TRY',
    first_income_date DATE NOT NULL,
    frequency INT NOT NULL DEFAULT 0, -- 0: one-time, 1: monthly, etc.
    repeat_count INT NULL,           -- NULL: infinite
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### savings
Savings goals and progress:
```sql
CREATE TABLE savings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(3) NOT NULL DEFAULT 'TRY',
    start_date DATE NOT NULL,
    target_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### exchange_rates
Currency exchange rates cache:
```sql
CREATE TABLE exchange_rates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    base_currency VARCHAR(3) NOT NULL,
    target_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(15,6) NOT NULL,
    fetched_at TIMESTAMP NOT NULL,
    UNIQUE KEY unique_currency_pair (base_currency, target_currency)
);
```

## Key Design Decisions

1. **Multi-user Support**: All tables include a user_id foreign key, allowing for future multi-user functionality.

2. **Categories**: Implemented as a separate table to support budget tracking per category.

3. **Payment Status Tracking**: Separate table for tracking payment status by month, replacing the paidMonths array in localStorage.

4. **Currency Support**: All financial tables include a currency field, supporting multi-currency transactions.

5. **Recurring Transactions**: Both payments and incomes support frequency and repeat_count for recurring transactions.

## Migration Strategy

1. Create a default user for existing data
2. Migrate categories and monthly budgets
3. Migrate payments and their status history
4. Migrate incomes
5. Migrate savings goals
6. Migrate exchange rates

## Next Steps

1. Implement database connection configuration
2. Create database migration scripts
3. Update application code to use MySQL instead of localStorage
4. Add proper error handling for database operations
5. Consider adding data validation and sanitization layers

## Future Enhancements

1. User authentication and authorization
2. Multi-user support with data isolation
3. Shared budgets between users
4. Transaction history and audit logs
5. Data export/import functionality