# Migration Testing Guide

This guide outlines the steps to test the database migration process before performing the actual migration.

## 1. Database Connection Test

Run the database connection test to verify your database configuration:

```bash
php test-db.php
```

This will:
- Test database connectivity
- Verify parameter binding
- Test basic CRUD operations
- Verify transaction handling

### Expected Output
```
Testing database connection and operations...
✓ Database connection established
✓ Basic query test passed
✓ Transaction started
✓ Test table created
✓ Insert test passed
✓ Select test passed
✓ Update test passed
✓ Delete test passed
✓ Transaction committed
```

## 2. Test Data Generation

1. Open test-export.html in your browser
2. Use the buttons to:
   - Create sample test data in localStorage
   - Export the test data
   - Clear the test data

The test data includes:
- Sample payments (recurring and one-time)
- Sample income
- Sample savings goal
- Budget categories and limits
- Exchange rates

## 3. Migration Test Process

1. Database Setup:
```sql
CREATE DATABASE butce_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'butce_test'@'localhost' IDENTIFIED BY 'test_password';
GRANT ALL PRIVILEGES ON butce_test.* TO 'butce_test'@'localhost';
FLUSH PRIVILEGES;
```

2. Update config.php for testing:
```php
return [
    'host' => 'localhost',
    'dbname' => 'butce_test',
    'username' => 'butce_test',
    'password' => 'test_password',
    // ... other settings
];
```

3. Generate and export test data:
   - Open test-export.html
   - Click "Create Test Data"
   - Click "Export Data"
   - Move the exported file to the database directory

4. Run the migration:
```bash
php migrate.php
```

5. Verify the migration:
```bash
# Check user table
SELECT * FROM users;

# Check payments
SELECT * FROM payments;

# Check payment statuses
SELECT * FROM payment_statuses;

# Check categories
SELECT * FROM categories;

# Check monthly budgets
SELECT * FROM monthly_budgets;
```

## Troubleshooting

### Invalid Parameter Number Error
If you encounter "Invalid parameter number" error:
1. Check the SQL query in the error message
2. Verify all named parameters (:param) match in the query and params array
3. Run test-db.php to verify basic parameter binding works
4. Check for any commented parameters in SQL statements

### Database Connection Issues
1. Verify database credentials in config.php
2. Ensure MySQL service is running
3. Check MySQL user permissions
4. Try connecting with mysql command line client

### Data Migration Issues
1. Verify the JSON export file exists and is valid
2. Check file permissions
3. Validate JSON structure matches expected format
4. Use test-export.html to regenerate test data

### Transaction Issues
1. Check if all queries in a transaction are valid
2. Verify no DDL statements in transactions
3. Ensure proper error handling and rollback

## Common Errors and Solutions

1. "Table already exists"
   - Drop existing tables or use IF NOT EXISTS
   - Check for proper cleanup in rollback

2. "Foreign key constraint fails"
   - Verify data is inserted in correct order
   - Check foreign key values exist in parent tables

3. "Data truncation"
   - Verify field lengths match data
   - Check decimal precision settings

4. "Duplicate entry"
   - Check UNIQUE constraints
   - Verify data doesn't violate unique indexes

## Validation Queries

```sql
-- Check data consistency
SELECT 
    (SELECT COUNT(*) FROM payments) as payment_count,
    (SELECT COUNT(DISTINCT payment_id) FROM payment_statuses) as payment_status_count,
    (SELECT COUNT(*) FROM categories) as category_count,
    (SELECT COUNT(*) FROM monthly_budgets) as budget_count;

-- Check recurring payments
SELECT 
    p.name,
    p.frequency,
    COUNT(ps.id) as status_count
FROM payments p
LEFT JOIN payment_statuses ps ON p.id = ps.payment_id
GROUP BY p.id
HAVING p.frequency > 0;

-- Check budget utilization
SELECT 
    c.name,
    c.monthly_limit,
    SUM(p.amount) as total_spent
FROM categories c
LEFT JOIN payments p ON c.id = p.category_id
GROUP BY c.id;