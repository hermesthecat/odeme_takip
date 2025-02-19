# MySQL Migration Guide

This guide explains how to migrate your Butce application data from localStorage to MySQL database.

## Quick Start

1. Open `migration.html` in your web browser
2. Follow the step-by-step migration wizard
3. Fix any issues reported by the system

## Migration Steps in Detail

### 1. Database Configuration

Edit `config.php` with your database credentials:
```php
return [
    'host' => 'localhost',
    'dbname' => 'butce',
    'username' => 'butce_user',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```

### 2. Web-Based Migration

1. Open `migration.html` in your browser
2. Follow these steps:
   - Database Check: Verifies configuration and connectivity
   - Data Export: Exports localStorage data to JSON
   - Data Migration: Transfers data to MySQL

### 3. Manual Migration (If Needed)

If you prefer command-line migration:

```bash
# Check database setup
php check-db.php

# Run migration
php migrate.php
```

## Directory Structure

```
database/
├── api/               # API endpoints
│   ├── controllers/   # API controllers
│   ├── .htaccess     # Apache rewrite rules
│   └── index.php     # API router
├── models/           # Database models
├── adapters/         # Frontend adapters
├── migration.html    # Web migration interface
├── check-db.html    # Database check interface
├── export.html      # Data export interface
├── config.php       # Database configuration
├── Database.php     # Database connection class
├── migrate.php      # Migration script
└── schema.sql       # Database schema
```

## Troubleshooting

### Database Connection Issues

Use `check-db.html` to diagnose:
- Database credentials
- MySQL server status
- User privileges
- Database existence

### Data Export Issues

If export fails:
1. Open browser console
2. Check for JavaScript errors
3. Verify localStorage data exists
4. Try manual export using export.html

### Migration Issues

Common problems and solutions:

1. **"Access denied" Error**
   - Check database credentials in config.php
   - Verify user privileges
   - Ensure MySQL server is running

2. **"Table already exists" Error**
   - Drop existing tables
   - Clear database
   - Run migration again

3. **"Data integrity check failed" Error**
   - Verify export file exists
   - Check JSON file format
   - Ensure all data was exported

## Verification

After migration, verify:

1. Data Integrity:
```sql
-- Check record counts
SELECT COUNT(*) FROM payments;
SELECT COUNT(*) FROM incomes;
SELECT COUNT(*) FROM savings;
```

2. Relationships:
```sql
-- Check category relationships
SELECT c.name, COUNT(p.id) as payment_count
FROM categories c
LEFT JOIN payments p ON c.id = p.category_id
GROUP BY c.name;
```

3. API Functionality:
```bash
# Test database operations
php test-db.php
```

## Rolling Back

To rollback migration:

1. Drop database tables:
```sql
DROP TABLE IF EXISTS
    exchange_rates, payment_statuses, payments,
    incomes, savings, monthly_budgets, categories, users;
```

2. Return to localStorage usage:
```javascript
// Update storage.js to use localStorage
```

## Security Notes

1. File Permissions:
```bash
chmod 640 config.php
chmod 644 *.html
chmod 755 *.php
```

2. Database Security:
- Use strong passwords
- Limit database user privileges
- Enable SSL for database connections

3. Web Security:
- Use HTTPS
- Implement proper authentication
- Validate all inputs

## Post-Migration Tasks

1. Update Frontend:
```javascript
// Update storage.js to use new adapters
import { PaymentAdapter, IncomeAdapter } from './database/adapters/DataAdapter.js';
```

2. Verify Features:
- Monthly payments
- Recurring transactions
- Budget calculations
- Currency conversions

3. Clean Up:
- Backup localStorage data
- Remove migration tools in production
- Update documentation

## Support

For migration issues:

1. Check error logs:
- PHP error log
- MySQL error log
- Browser console

2. Run diagnostics:
```bash
php check-db.php
php test-db.php
```

3. Contact support with:
- Error messages
- Log files
- Configuration details (sanitized)

## Future Improvements

1. Multi-user Support:
- User authentication
- Data isolation
- Role-based access

2. Performance:
- Database indexing
- Query optimization
- Connection pooling

3. Features:
- Data backup/restore
- Import/export tools
- Audit logging

4. Security:
- Password hashing
- API authentication
- Rate limiting