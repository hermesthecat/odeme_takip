# Pecunia Installation Guide

Complete installation guide for Pecunia Personal Finance Management System.

## 📋 System Requirements

### Minimum Requirements

- **PHP**: 7.4+ (Recommended: PHP 8.0+)
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 256MB RAM minimum, 512MB recommended
- **Storage**: 500MB available space

### Required PHP Extensions

- `pdo` and `pdo_mysql`
- `mbstring`
- `json`
- `openssl`
- `curl`
- `gd`
- `zip` (for backup functionality)

### Optional Extensions (for enhanced features)

- `imagick` (better image processing)
- `intl` (internationalization)
- `opcache` (performance)

## 🚀 Installation Methods

### Method 1: Command Line Installation (Recommended)

**Step 1**: Download and extract Pecunia files to your web directory

**Step 2**: Run the CLI installer

```bash
cd /path/to/pecunia
php install.php
```

**Step 3**: Follow the interactive prompts:

- Database configuration (host, port, username, password, database name)
- Application settings (site name, URL, environment)
- API keys (optional: Gemini API, Telegram Bot)
- Admin user creation

**Step 4**: Verify installation

```bash
php verify_installation.php
```

### Method 2: Web-Based Installation

**Step 1**: Upload Pecunia files to your web server

**Step 2**: Navigate to the web installer:

```
http://your-domain.com/install_web.php
```

**Step 3**: Follow the 5-step web interface:

1. System requirements check
2. Database configuration
3. Application settings
4. Admin user creation
5. Installation completion

**Step 4**: **Important**: Delete `install_web.php` after installation for security

### Method 3: Manual Installation

**Step 1**: Create database and import schema

```sql
CREATE DATABASE pecunia CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE pecunia;
SOURCE database/database.sql;
```

**Step 2**: Create environment file

```bash
cp .env.example .env
# Edit .env with your configuration
```

**Step 3**: Create secure upload directory

```bash
mkdir secure_uploads
chmod 755 secure_uploads
```

**Step 4**: Create admin user (via database or registration)

## ⚙️ Configuration

### Environment Variables (.env)

```env
# Database Configuration
DB_SERVER=127.0.0.1:3306
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_NAME=pecunia

# Application Configuration
SITE_NAME=Pecunia
SITE_URL=http://localhost/pecunia
APP_ENV=production

# API Keys (Optional)
GEMINI_API_KEY=your_gemini_api_key
TELEGRAM_BOT_TOKEN=your_telegram_bot_token

# Security Settings
SESSION_TIMEOUT=1800
BCRYPT_COST=12
```

### Web Server Configuration

#### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"

# Protect sensitive files
<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>
```

#### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ^~ /api/ {
    try_files $uri /api.php?$query_string;
}

location ~ /\.env {
    deny all;
}

location ~* \.(jpg|jpeg|png|gif|css|js)$ {
    expires 1M;
    add_header Cache-Control "public, immutable";
}
```

## 🔐 Security Setup

### File Permissions

```bash
# Set appropriate permissions
chmod 755 /path/to/pecunia
chmod 644 /path/to/pecunia/.env
chmod 755 /path/to/pecunia/secure_uploads
chmod -R 644 /path/to/pecunia/css/
chmod -R 644 /path/to/pecunia/js/
```

### SSL/HTTPS Setup

For production use, enable HTTPS:

1. Obtain SSL certificate
2. Configure web server for SSL
3. Update `SITE_URL` in `.env` to use `https://`
4. Required for Telegram bot webhooks

## 📊 Database Setup

### Automatic Setup

The installation scripts handle database creation automatically. The schema includes:

- User management and authentication
- Financial data tables (income, payments, savings)
- Multi-currency exchange rates
- AI analysis temporary storage
- Comprehensive logging system
- Rate limiting tables

### Manual Database Optimization

```sql
-- Add indexes for performance (included in schema)
-- Check current indexes
SHOW INDEX FROM users;
SHOW INDEX FROM payments;
SHOW INDEX FROM income;

-- Monitor performance
SHOW PROCESSLIST;
ANALYZE TABLE users, payments, income, savings;
```

## 🤖 Optional Features Setup

### Google Gemini AI Integration

1. Get API key from [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Add to `.env`: `GEMINI_API_KEY=your_key_here`
3. Test with document upload feature

### Telegram Bot Integration

1. Create bot via [@BotFather](https://t.me/botfather)
2. Add token to `.env`: `TELEGRAM_BOT_TOKEN=your_token_here`
3. Set webhook (requires HTTPS):

```bash
curl -X POST "https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook" \
     -d "url=https://yourdomain.com/telegram_webhook.php"
```

### Stock Portfolio Tracking

Set up cron job for automatic price updates:

```bash
# Add to crontab (run every hour)
0 * * * * /usr/bin/php /path/to/pecunia/cron/cron_borsa.php

# For worker process (real-time updates)
*/5 * * * * /usr/bin/php /path/to/pecunia/cron/cron_borsa_worker.php
```

## 🗄️ Backup Configuration

### Automatic Backups

```bash
# Daily backup at 2 AM
0 2 * * * /usr/bin/php /path/to/pecunia/backup.php auto

# Weekly compressed backup
0 2 * * 0 /usr/bin/php /path/to/pecunia/backup.php backup true
```

### Manual Backup

```bash
# Create backup
php backup.php backup

# List backups
php backup.php list

# Restore from backup
php backup.php restore /path/to/backup.sql.gz
```

## 🔧 Troubleshooting

### Common Issues

#### Database Connection Failed

- Check database credentials in `.env`
- Verify database server is running
- Test connection: `mysql -u username -p -h hostname`

#### Permission Denied Errors

```bash
# Fix file permissions
find /path/to/pecunia -type f -exec chmod 644 {} \;
find /path/to/pecunia -type d -exec chmod 755 {} \;
chmod 755 /path/to/pecunia/secure_uploads
```

#### Session Issues

- Check `session.save_path` in PHP configuration
- Verify web server has write permissions to session directory
- Clear browser cookies and cache

#### Upload Issues

- Check `upload_max_filesize` and `post_max_size` in php.ini
- Verify `secure_uploads/` directory exists and is writable
- Check web server error logs

### Error Debugging

#### Enable Debug Mode

In `.env`:

```env
APP_ENV=development
```

#### Check Logs

- Application logs: Access via admin panel at `/log.php`
- Web server logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- PHP logs: Check `error_log` location in php.ini

#### Database Issues

```sql
-- Check table status
SHOW TABLE STATUS LIKE 'users';

-- Check for locks
SHOW PROCESSLIST;
SELECT * FROM information_schema.INNODB_TRX;

-- Repair tables if needed
REPAIR TABLE table_name;
```

## 📈 Performance Optimization

### PHP Configuration

```ini
; Recommended php.ini settings
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 50M
post_max_size = 50M
max_input_vars = 3000

; Enable OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000
```

### Database Optimization

- Enable query cache if using MySQL
- Regular maintenance: `OPTIMIZE TABLE` on large tables
- Monitor slow queries and add indexes as needed
- Consider partitioning for large datasets

### Caching Strategy

- Enable browser caching for static assets
- Use CDN for CSS/JS libraries
- Consider Redis/Memcached for session storage in high-traffic scenarios

## 🛡️ Security Hardening

### Production Security Checklist

- [ ] Change default admin credentials
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Delete installer files (`install.php`, `install_web.php`)
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Configure firewall rules
- [ ] Regular security updates
- [ ] Backup encryption for sensitive data
- [ ] Rate limiting for API endpoints
- [ ] Regular security audits

### File Security

```apache
# .htaccess security rules
<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files ".env">
    Order Allow,Deny
    Deny from all
</Files>
```

## 📞 Support

### Getting Help

- **Documentation**: Check `database/README.md` for technical details
- **Issues**: Report bugs via project repository
- **Configuration**: Review `.env` settings
- **Logs**: Check application logs at `/log.php` (admin access required)

### Verification

Run the verification script to check installation health:

```bash
php verify_installation.php
```

This will check:

- System requirements
- Database connectivity and structure
- File permissions
- Configuration validity
- Security settings

---

## 📝 Installation Checklist

- [ ] System requirements met
- [ ] Database created and accessible
- [ ] Installation method completed successfully
- [ ] Environment variables configured
- [ ] Admin user created
- [ ] File permissions set correctly
- [ ] Verification script passed
- [ ] SSL certificate installed (production)
- [ ] Backup system configured
- [ ] Security hardening applied
- [ ] Installer files deleted

**Installation Date**: ___________  
**Version**: ___________  
**Admin Username**: ___________  
**Database Name**: ___________

---

*For additional technical documentation, see `database/README.md` and `CLAUDE.md`*
