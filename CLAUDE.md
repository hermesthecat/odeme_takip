# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Pecunia is a personal finance management system built with PHP and MySQL/MariaDB. It features AI-powered document analysis using Google Gemini API, Telegram bot integration for receipt processing, and multi-currency support with stock portfolio tracking.

## Development Commands

### Setup and Installation

```bash
# Install PHP dependencies
composer install

# Set up environment configuration
cp .env.example .env
# Edit .env with your database and API credentials

# Import database structure
mysql -u your_user -p your_database < database.sql

# Create uploads directory with proper permissions
mkdir uploads
chmod 777 uploads

# Set up Telegram bot webhook (requires HTTPS)
php telegram_bot.php
```

### Testing

No automated test suite is configured. Testing is done manually through the web interface.

### Background Tasks

```bash
# Stock portfolio update cron job (run every hour)
php cron/cron_borsa.php

# Stock portfolio worker process (for real-time updates)
php cron/cron_borsa_worker.php

# Example crontab entry for stock updates:
# 0 * * * * /usr/bin/php /path/to/project/cron/cron_borsa.php
```

### Development Server

This is a PHP web application that runs on Apache/Nginx with PHP. No build process required - changes are reflected immediately.

### Debugging and Logging

```bash
# View application logs (admin access required)
# Access via: http://your-domain/log.php
# Logs are stored in MySQL `logs` table

# Key debugging endpoints:
# /ai_analysis.php - Review AI-processed documents
# /log.php - System logs (admin only)
# /api.php - Main API endpoint for testing
```

### Common Development Tasks

```bash
# Test API endpoints with session simulation
curl -X POST http://localhost/api.php \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "action=get_summary&month=1&year=2025"

# Test exchange rate refresh API
curl -X POST http://localhost/api/exchange_rate_refresh.php \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d "from_currency=USD&to_currency=TRY"

# Check database performance (user_id queries)
SELECT table_name, index_name 
FROM information_schema.statistics 
WHERE column_name = 'user_id';

# Monitor AI API usage
grep "Gemini API" /path/to/logs/* | wc -l

# Test transaction rollback behavior
# Temporarily add "throw new Exception('test');" in API methods

# Database backup with proper encoding
mysqldump -u user -p --default-character-set=utf8mb4 database_name > backup.sql

# Reset Telegram webhook with verification
php telegram_bot.php && curl -s "https://api.telegram.org/bot$TOKEN/getWebhookInfo"

# Check session storage usage
ls -la /tmp/sess_* | wc -l  # or configured session path

# Test rate limiting behavior (should return 429 after limit)
for i in {1..15}; do 
  curl -X POST http://localhost/api/exchange_rate_refresh.php \
    -H "Cookie: PHPSESSID=your_session_id" \
    -d "from_currency=USD&to_currency=TRY"
done

# Clear cache manually (when debugging cache issues)
# Access via: http://localhost/api.php with action=clear_cache

# Test AI document processing pipeline
curl -X POST http://localhost/upload_handler.php \
  -F "file=@test_receipt.jpg" \
  -F "type=ai_analysis" \
  -H "Cookie: PHPSESSID=your_session_id"

# Monitor database transaction locks
SELECT * FROM information_schema.INNODB_TRX;

# Check exchange rate cache status
SELECT currency_from, currency_to, rate, updated_at 
FROM exchange_rates 
WHERE updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

## Architecture

### Core Structure

- **Entry Points**: `index.php` (main app), `login.php`, `register.php`, `app.php` (dashboard)
- **API Layer**: `api.php` serves as the main API router, delegating to specialized modules in `api/` directory
- **Configuration**: `config.php` handles environment variables, database connection, and global settings
- **Database**: MySQL/MariaDB with UTF-8 support, schema defined in `database.sql`

### Authentication & Session Architecture

- **Session Management**: 30-minute timeout with regeneration on login, secure remember-me tokens
- **Brute Force Protection**: 5 failed attempts trigger 15-minute lockout
- **Password Security**: BCrypt hashing with enforced complexity (8+ chars, mixed case, numbers, symbols)
- **Security Headers**: XSS protection, content type validation, frame options
- **User Verification**: Telegram account linking via 6-digit verification codes

### Key Components

#### API Design Patterns & Error Handling

The API system follows consistent patterns across all modules:

- **Centralized Routing**: `api.php` acts as single endpoint dispatcher
- **Validation Strategy**: Exception-based validation with `validate.php` utilities
  - Required field validation with localized error messages
  - Type validation (numeric, date, currency, frequency)
  - Range validation (min/max values, date ranges)
- **Transaction Safety**: Database transactions for complex operations with rollback
- **Currency Handling**: Multi-currency support with automatic exchange rate fetching
- **User Scoping**: All operations validate user ownership via session `user_id`
- **Error Responses**: Standardized JSON format with status/message structure

API Modules:

- `api/income.php` - Income tracking operations
- `api/payments.php` - Payment and expense management  
- `api/savings.php` - Savings goals tracking
- `api/card.php` - Payment card management
- `api/user.php` - User profile operations
- `api/transfer.php` - Money transfer between accounts
- `api/currency.php` - Exchange rate management
- `api/summary.php` - Financial summaries and analytics
- `api/validate.php` - Input validation utilities
- `api/utils.php` - Common utility functions
- `api/xss.php` - Security utilities
- `api/error_handler.php` - Global error handling with dev/prod modes
- `api/rate_limiter.php` - API rate limiting for external services
- `api/exchange_rate_refresh.php` - Manual exchange rate updates

#### Internationalization

- `Language.php` class provides singleton pattern for translations
- Language files in `lang/` directory (tr.php, en.php)
- Global `t()` function for translation keys
- Session, cookie, and browser language detection

#### Telegram Bot Integration

- Bot commands in `commands/` directory extending TelegramBot framework
- Receipt analysis via image processing with AI
- User verification system linking Telegram accounts
- Webhook handler at `telegram_webhook.php`

#### Stock Portfolio System

- Real-time stock price tracking via cron jobs
- Portfolio management with automatic updates
- Currency conversion support

### Frontend Architecture & AJAX Patterns

- **Centralized AJAX**: `utils.js` provides `ajaxRequest()` function with built-in validation
- **Dual Validation**: Client-side validation mirrors server-side rules before requests
- **Security**: HTML escaping, XSS protection, safe template rendering
- **Error Handling**: Standardized error display with SweetAlert2, session expiry detection
- **State Management**: Global `data` object, URL parameter management for filtering
- **UI Patterns**: Loading states, real-time list updates, currency formatting
- **Translation Support**: Localized validation messages and UI text
- JavaScript modules mirror API structure:
  - `js/auth.js` - Authentication flows
  - `js/payments.js` - Payment management
  - `js/utils.js` - Common utilities and validation
  - `js/app.js` - Main application logic
  - `js/theme.js` - Theme switching

### Database Design Patterns

- **Hierarchical Data**: `parent_id` pattern for recurring income/payments/savings
- **Multi-Currency**: Original currency + exchange rate stored per transaction
- **User Isolation**: All financial data strictly scoped by `user_id`
- **Audit Logging**: Comprehensive logging in `logs` table with method/type/user tracking
- **Portfolio Tracking**: Stock purchases/sales with partial sale support via `referans_alis_id`
- **Collation**: UTF-8 Turkish collation (`utf8mb4_turkish_ci`) for proper text handling

#### Core Tables

- `users` - Authentication, preferences, admin roles
- `income/payments/savings` - Financial tracking with recurring support
- `portfolio` - Stock portfolio with buy/sell history
- `exchange_rates` - Currency conversion cache
- `telegram_users` - Links web accounts to Telegram
- `ai_analysis_temp` - Temporary AI analysis results for approval

### Telegram Bot Integration Architecture

- **Command Pattern**: Separate command classes extending TelegramBot framework
- **Webhook Processing**: `telegram_webhook.php` handles all updates
- **User Linking**: Web-generated verification codes link accounts
- **Receipt Processing**: Photo upload → Gemini Vision API → temp storage → web approval
- **Error Handling**: Graceful error responses to users

Commands:

- `VerifyCommand` - Links Telegram to web account
- `ReceiptCommand` - Processes receipt photos with AI
- `HelpCommand` - User assistance
- `StartCommand` - Bot initialization

### AI Integration & Document Processing Workflow

- **Multi-Channel Input**: Web upload (PDF/Excel/CSV/images) + Telegram photos
- **Security Validation**: MIME type, file size, malicious content scanning
- **AI Processing**: Google Gemini Vision API with structured prompts
- **Approval Workflow**: AI results → temporary storage → user review → batch approval
- **Data Integration**: Approved items automatically added to financial tables
- **Error Recovery**: Comprehensive error handling throughout pipeline

### Security Architecture

- **Input Validation**: Server-side validation with exception handling
- **File Security**: MIME validation, size limits, malicious content detection
- **Session Security**: Regeneration, timeout, secure tokens
- **Database Security**: Prepared statements, transaction isolation
- **XSS Protection**: HTML escaping, content type headers
- **Authentication**: Strong password policy, brute force protection
- **Error Handling**: Global error handlers with environment-aware responses
- **Rate Limiting**: Built-in rate limiting for:
  - Gemini API: 10 requests per 5 minutes per user
  - Exchange Rate API: 20 requests per 10 minutes per user
  - File uploads: 5 files per 5 minutes per user
  - Telegram webhooks: 30 requests per minute per bot

## Critical Architectural Insights

### Critical Execution Paths & Decision Points

**Authentication Flow (Most Critical Path)**:

1. `config.php` → Environment loading → Database connection → Language initialization
2. `checkLogin()` → Session validation → User context establishment
3. All protected pages **MUST** follow: `require_once 'config.php'` → `checkLogin()`
4. **Decision Point**: Session timeout (30 min) vs remember-me token validation
5. **Decision Point**: Admin role check (`$_SESSION['is_admin']`) for privileged operations

**API Request Flow**:

1. `api.php` → `$_POST['action']` routing → Module dispatch
2. **Decision Point**: User ownership validation via `$user_id = $_SESSION['user_id']`
3. Exception-based validation → Database transaction → JSON response
4. **Critical**: All API modules assume `checkLogin()` already called

### Extension Patterns for Adding New Features

**Adding a New API Module** (Follow this exact pattern):

1. **Create API Module**: `api/newmodule.php`

   ```php
   require_once __DIR__ . '/../config.php';
   checkLogin();
   
   function addNewItem() {
       global $pdo, $user_id;
       // Validation with validate.php functions
       // Database transaction with try-catch
       // User scoping with user_id
   }
   ```

2. **Update Main Router**: Add to `api.php` switch statement

   ```php
   case 'add_new_item':
       try {
           if (addNewItem()) {
               $response = ['status' => 'success', 'message' => t('newmodule.add_success')];
           }
       } catch (Exception $e) {
           $response = ['status' => 'error', 'message' => $e->getMessage()];
       }
       break;
   ```

3. **Add JavaScript Module**: `js/newmodule.js` using `ajaxRequest()` pattern
4. **Add Language Keys**: Update `lang/tr.php` and `lang/en.php`
5. **Database Schema**: Add user_id foreign key + proper indexes

**Adding New Telegram Commands**:

1. Create `commands/NewCommand.php` extending `UserCommand`
2. Implement `execute()` method with error handling
3. Commands auto-discovered by TelegramBot framework

### Configuration Cascade & Dependency Flow

**Hierarchical Dependency Chain**:

```text
.env file → config.php → Database/Language/PDO → Individual modules
          ↓
Session management → User context → Feature modules
```

**Critical Dependencies** (In load order):

1. **Environment Variables**: Database credentials, API keys
2. **Database Connection**: `$pdo` global variable used everywhere
3. **Language System**: `$lang` singleton → `t()` function
4. **Session Management**: `$_SESSION['user_id']` → All user-scoped operations
5. **Authentication State**: Determines page access and admin features

**Configuration Override Hierarchy**:

1. Environment variables (highest priority)
2. Session values (user preferences)
3. Cookie values (remembered preferences)
4. Browser language detection (lowest priority)

### Architectural Bottlenecks & Scaling Constraints

**Database Performance Issues**:

- **30+ queries filter by `user_id`** → Critical: Index all `user_id` columns
- **Summary calculations** in `api/summary.php` use complex subqueries → Consider materialized views
- **Real-time exchange rates** fetched per transaction → Implement aggressive caching
- **File uploads** processed synchronously → Move to background queue for large files

**Memory & Session Bottlenecks**:

- **PHP file sessions** don't scale → Move to Redis/Memcached for high traffic
- **AI document processing** loads entire file in memory → Stream processing needed
- **Telegram photo downloads** stored temporarily → Disk space management required

**API Rate Limiting Concerns**:

- **Google Gemini API** calls not rate-limited → Could exhaust quotas
- **Exchange rate APIs** called frequently → Cache for 1+ hours
- **Stock price updates** every hour → Consider WebSocket for real-time needs

### State Consistency & Data Synchronization

**Transaction Patterns** (9 files use transactions):

- **Consistent Pattern**: `$pdo->beginTransaction()` → operations → `$pdo->commit()`
- **Critical Gap**: Many files missing `rollback()` on exception
- **Best Practice**: Always wrap transactions in try-catch with rollback

**Data Consistency Rules**:

1. **User Isolation**: Every financial record MUST include `user_id` check
2. **Currency Consistency**: Exchange rates stored with original amounts
3. **Recurring Data**: Parent-child relationships maintain referential integrity
4. **Audit Trail**: All significant operations logged via `saveLog()`

**State Synchronization Issues**:

- **Frontend-Backend**: No real-time sync → Manual page refresh required
- **Multi-Currency**: Exchange rates can become stale → Background updates needed
- **Telegram-Web**: No bidirectional sync → Changes in web not reflected in bot

**Critical Consistency Checks**:

- Transfer operations update dates but preserve relationships
- Payment status changes logged for audit trail
- Portfolio updates maintain historical data integrity

## Development Patterns & Data Flow

### Request/Response Flow

1. **Web Requests**: Form submission → JavaScript validation → AJAX to `api.php` → specific API module
2. **Telegram Requests**: Webhook → command processing → database → response to user
3. **AI Processing**: File upload → security validation → AI analysis → temp storage → user approval

### Recurring Payment Architecture

- **Parent-Child Relationship**: Original payment creates children for future months
- **Transfer Logic**: Unpaid payments can be transferred to next month
- **Status Tracking**: `pending` → `paid` workflow with mark-as-paid functionality
- **Frequency Calculations**: Utility functions in `utils.php` for date calculations

### Multi-Currency Data Flow

1. User selects currency different from base currency
2. System fetches exchange rate from `exchange_rates` table or external API
3. Original amount + currency + exchange rate stored
4. Display conversion calculated on-the-fly
5. Reports can show both original and converted amounts

### Error Handling Strategy

- **Validation Layer**: Client-side → Server-side → Database constraints
- **Exception Propagation**: Validation functions throw exceptions with localized messages
- **Transaction Rollback**: Complex operations wrapped in database transactions
- **Logging**: All significant events logged via `saveLog()` with context
- **User Feedback**: Errors displayed via SweetAlert2 with proper internationalization

### File Processing Security Pipeline

1. **Upload Validation**: Size, MIME type, extension checks
2. **Content Scanning**: Search for malicious patterns in file content
3. **Header Validation**: Verify file headers match claimed type
4. **Temporary Storage**: Files processed and immediately cleaned up
5. **Database Isolation**: Analysis results stored separately before approval

### Development Notes

- **Turkish Localization**: UTF-8 Turkish collation throughout database
- **Admin Features**: Comprehensive logging viewable at `log.php` (admin only)
- **Stock Portfolio**: Real-time price updates via cron jobs (`cron_borsa.php`)
- **Theme Support**: User preference stored and applied via CSS classes

## Environment Variables Required

- **Database**: `DB_SERVER`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`
- **APIs**: `GEMINI_API_KEY` (Google AI for document analysis)
- **Telegram**: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_BOT_USERNAME`
- **Site**: `SITE_NAME`, `SITE_AUTHOR`
- **Environment**: `APP_ENV` (development/production - affects error display)

## Performance Considerations

### Critical Performance Requirements

- **Database Indexing**: **MANDATORY** indexes on `user_id` fields (30+ queries depend on this)
- **Query Optimization**: Summary calculations need optimization for >1000 transactions
- **File Processing**: Large file uploads (>10MB) should be processed asynchronously
- **Exchange Rates**: Cache rates for minimum 1 hour to avoid API exhaustion
- **Session Storage**: PHP file sessions limit to ~100 concurrent users
- **Image Processing**: Compress images before Gemini API to reduce costs

### Scaling Thresholds

- **Single User**: Current architecture handles ~10K transactions efficiently
- **Multi-User**: Database becomes bottleneck at ~100 active users
- **File Processing**: AI analysis becomes bottleneck at ~50 files/hour
- **Stock Updates**: Cron job scalable to ~1000 portfolio entries

## Deployment Notes

- **HTTPS Required**: For Telegram webhooks and secure file uploads
- **File Permissions**: `uploads/` directory needs write permissions
- **Cron Jobs**: Set up `cron_borsa.php` for stock price updates
- **Database Migrations**: Run `database.sql` for initial schema
- **Composer Dependencies**: Run `composer install` for PHP packages

## Integration Points & External Dependencies

### Google Gemini API Integration

- **Vision API**: Used for receipt/document image analysis
- **Structured Prompts**: JSON response format for consistent parsing
- **Error Handling**: Graceful fallback when API unavailable
- **Rate Limiting**: Consider API quotas when processing bulk uploads

### Telegram Bot Integration

- **Webhook URL**: Must be HTTPS for Telegram requirements
- **File Handling**: Downloads photos, processes, then cleans up
- **User Context**: Links Telegram users to web app users
- **Command Extension**: Easy to add new commands by creating command classes

### External APIs

- **Exchange Rates**: Fetched from external services and cached
- **Stock Prices**: Real-time updates for portfolio tracking

## Testing and Quality Assurance

No automated test suite is currently configured. Testing is done manually through the web interface. When developing new features:

- **CRUD Testing**: Test all create/read/update/delete operations
- **API Validation**: Verify endpoints return proper JSON responses
- **Multi-User Testing**: Test with different user roles (admin vs regular user)
- **Internationalization**: Verify both Turkish and English languages work
- **Currency Testing**: Test multi-currency scenarios with exchange rates
- **Security Testing**: Verify file upload security, XSS protection, session handling
- **Telegram Integration**: Test bot commands and photo processing
- **AI Workflow**: Test document upload → analysis → approval flow
- **Rate Limiting**: Verify API rate limits work correctly
- **Error Handling**: Test both development and production error modes

## Architectural Decision Guidelines

### When to Use Transactions

- **ALWAYS**: When creating parent-child relationships (recurring payments/income)
- **ALWAYS**: When moving money between accounts or categories
- **ALWAYS**: When batch operations could leave system in inconsistent state
- **EXAMPLE**: Adding recurring payment creates multiple future instances

### Security Decision Points

- **User Scoping**: Every database operation MUST validate user ownership
- **File Uploads**: ALWAYS validate MIME type, size, and scan content
- **API Access**: All endpoints require `checkLogin()` except `api/auth.php`
- **Admin Features**: Double-check `$_SESSION['is_admin']` for destructive operations

### Extension Decision Matrix

| Feature Type | Implementation Pattern | Files to Modify |
|--------------|----------------------|------------------|
| New Financial Category | API module + JS + DB table | `api/`, `js/`, `database.sql`, `lang/` |
| New Telegram Command | Command class only | `commands/` |
| New Report Type | Summary function + frontend | `api/summary.php`, `js/` |
| New User Role | Session logic + UI checks | `config.php`, templates |
| New Currency | Exchange rate logic | `api/currency.php`, `lang/` |

### Anti-Patterns to Avoid

- **DON'T**: Direct database access without user_id filtering
- **DON'T**: File operations without proper cleanup
- **DON'T**: API endpoints without authentication (except `api/auth.php`)
- **DON'T**: Frontend state changes without backend validation
- **DON'T**: Hardcoded currency or language assumptions
- **DON'T**: Database operations without proper error handling
- **DON'T**: External API calls without rate limiting checks
- **DON'T**: Missing rollback() on database transaction exceptions
