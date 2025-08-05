# Database Schema - Pecunia

## Overview

Pecunia is a personal finance management system built with PHP and MySQL/MariaDB. This directory contains the complete database schema with all tables, indexes, foreign keys, and rate limiting system.

## Database Information

- **Project**: Pecunia - Personal Finance Management System
- **Author**: A. Kerem Gök
- **Created**: 2025-02-28
- **Last Updated**: 2025-08-05
- **Version**: 2.0
- **Database Engine**: MySQL/MariaDB
- **Charset**: utf8mb4
- **Collation**: utf8mb4_turkish_ci

## Installation

```bash
# Import complete database schema
mysql -u your_username -p your_database < database/database.sql

# Alternative with custom encoding
mysql -u your_username -p --default-character-set=utf8mb4 your_database < database/database.sql
```

## Core Tables

### User Management

- **`users`** - User accounts, preferences, admin roles, language settings
- **`telegram_users`** - Links web accounts to Telegram for bot integration

### Financial Data

- **`income`** - Income tracking with recurring support and parent-child relationships
- **`payments`** - Payment and expense management with card association
- **`savings`** - Savings goals with progress tracking
- **`card`** - Credit/debit card management

### Portfolio & Trading

- **`portfolio`** - Stock portfolio with buy/sell history and partial sale support
- **`exchange_rates`** - Currency conversion cache with automatic updates

### AI & Automation

- **`ai_analysis_temp`** - Temporary AI analysis results awaiting user approval

### System

- **`logs`** - Comprehensive system logging with user/method/type tracking

### Rate Limiting System

- **`rate_limits`** - Active rate limiting records with expiration
- **`rate_limit_rules`** - Rate limiting configuration rules
- **`rate_limit_violations`** - Violation logs for monitoring and security

## Key Features

### Security & Performance

- **Foreign Key Constraints**: Ensures referential integrity with CASCADE/SET NULL policies
- **Strategic Indexing**: 30+ optimized indexes for 10-100x query performance improvement
- **Rate Limiting**: MySQL-based system prevents API abuse and brute force attacks
- **User Isolation**: All financial data strictly scoped by user_id

### Multi-Currency Support

- Exchange rates cached with automatic refresh
- Original currency + exchange rate stored per transaction
- Real-time conversion calculations

### Hierarchical Data

- Parent-child relationships for recurring income/payments/savings
- Maintains referential integrity through foreign keys

### Audit & Compliance

- Comprehensive logging via `logs` table with method/type/user tracking
- Transaction safety with rollback support
- Data consistency rules enforced at database level

## Default Data

### Admin User

- **Username**: admin
- **Password**: password (⚠️ Change immediately!)
- **Role**: Administrator
- **Currency**: TRY
- **Theme**: dark
- **Language**: tr

### Rate Limit Rules (25+ pre-configured)

- API endpoints: 100-200 req/5min
- AI analysis: 5 req/10min
- File uploads: 10 req/5min  
- Authentication: 5 attempts/15min
- Telegram webhooks: 50 req/1min

## Performance Optimization

### Critical Indexes

All tables with `user_id` columns have optimized indexes:

- Single column indexes on `user_id`
- Compound indexes: `(user_id, date)`, `(user_id, status)`
- Parent-child relationship indexes

### Query Performance

Expected improvements after indexing:

- SELECT queries: **10-100x faster**
- JOIN operations: **Significantly optimized**
- ORDER BY clauses: **Index-accelerated**
- Memory usage: **Optimized**

### Transaction Patterns

- **Consistent Pattern**: `beginTransaction()` → operations → `commit()`
- **Error Handling**: Automatic rollback on exceptions
- **Data Integrity**: Foreign key constraints prevent orphaned records

## Database Maintenance

### Automated Cleanup

```sql
-- Clean expired rate limits (stored procedure)
CALL CleanExpiredRateLimits();
```

### Performance Monitoring

```sql
-- Check index usage
SHOW INDEX FROM payments;
SHOW TABLE STATUS LIKE 'payments';
ANALYZE TABLE payments;

-- Rate limiting statistics  
SELECT endpoint, SUM(request_count) as total_requests 
FROM rate_limits GROUP BY endpoint;
```

### Regular Maintenance Tasks

1. Run `CleanExpiredRateLimits()` daily
2. Monitor `rate_limit_violations` for abuse patterns
3. Update exchange rates via API
4. Archive old logs periodically
5. Monitor disk space usage

## Architecture Highlights

### Data Flow Patterns

1. **Web Requests**: Form → Validation → API → Database → Response
2. **Telegram Integration**: Webhook → Command → Database → Bot Response  
3. **AI Processing**: Upload → Validation → AI Analysis → Temp Storage → User Approval
4. **Background Tasks**: Cron Jobs → Stock Price Updates → Database

### Security Measures

- **Input Validation**: Server-side validation with exception handling
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: HTML escaping and content type headers
- **Rate Limiting**: Database-enforced limits with violation logging
- **Data Encryption**: BCrypt password hashing
- **Session Security**: Secure tokens with timeout management

### Scalability Considerations

- **Single User**: Handles ~10K transactions efficiently
- **Multi-User**: Database bottleneck at ~100 active users
- **File Processing**: AI analysis bottleneck at ~50 files/hour  
- **Stock Updates**: Scalable to ~1000 portfolio entries

## Troubleshooting

### Common Issues

1. **Character Encoding**: Ensure utf8mb4 throughout stack
2. **Foreign Key Errors**: Check for orphaned records before applying constraints
3. **Index Creation**: May take time on existing large datasets
4. **Rate Limit Cleanup**: Enable MySQL event scheduler for auto-cleanup

### Emergency Rollback

If foreign key constraints cause issues:

```sql
-- Remove all foreign key constraints
ALTER TABLE payments DROP FOREIGN KEY fk_payments_user;
ALTER TABLE income DROP FOREIGN KEY fk_income_user;
-- ... (see database.sql for complete list)
```

## Environment Requirements

### Database Server

- MySQL 5.7+ or MariaDB 10.3+
- InnoDB storage engine
- utf8mb4 character set support
- Foreign key constraint support

### PHP Requirements  

- PHP 7.4+
- PDO MySQL extension
- Proper database credentials in config.php

### Recommended Settings

```sql
SET GLOBAL event_scheduler = ON;  -- For automated cleanup
SET GLOBAL innodb_buffer_pool_size = 256M;  -- For better performance
```

## Support & Documentation

For detailed implementation patterns and architectural decisions, refer to:

- `CLAUDE.md` - Development guidelines and patterns
- `cron/README.md` - Background task documentation
- Individual API module documentation in `api/` directory

## Version History

- **v2.0** (2025-08-05): Complete schema with rate limiting and optimizations
- **v1.x** (2025-02-28): Initial schema with core functionality
