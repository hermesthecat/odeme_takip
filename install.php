<?php

/**
 * Pecunia Database Installation Script
 * @author A. Kerem Gök
 * @date 2025-01-25
 * @description Automated database setup and configuration
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class PecuniaInstaller
{
    private $config = [];
    private $pdo = null;
    private $errors = [];
    private $log = [];

    public function __construct()
    {
        $this->log("Pecunia Installation Started");
        $this->checkRequirements();
    }

    /**
     * Check system requirements
     */
    private function checkRequirements()
    {
        $required = [
            'PHP' => '7.4.0',
            'Extensions' => ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'curl']
        ];

        // PHP version check
        if (version_compare(PHP_VERSION, $required['PHP'], '<')) {
            $this->addError("PHP version {$required['PHP']} or higher is required. Current: " . PHP_VERSION);
        }

        // Extension checks
        foreach ($required['Extensions'] as $ext) {
            if (!extension_loaded($ext)) {
                $this->addError("Required PHP extension '{$ext}' is not installed");
            }
        }

        if (!empty($this->errors)) {
            $this->displayErrors();
            exit(1);
        }

        $this->log("System requirements check passed");
    }

    /**
     * Run the complete installation process
     */
    public function install()
    {
        $this->displayHeader();
        $this->collectDatabaseInfo();
        $this->testConnection();
        $this->createDatabase();
        $this->importSchema();
        $this->createAdminUser();
        $this->createEnvFile();
        $this->setPermissions();
        $this->displaySuccess();
    }

    /**
     * Display installation header
     */
    private function displayHeader()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    PECUNIA INSTALLER                         ║\n";
        echo "║              Personal Finance Management System              ║\n";
        echo "║                                                              ║\n";
        echo "║  This script will set up your Pecunia database and          ║\n";
        echo "║  configuration files automatically.                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    /**
     * Collect database configuration from user
     */
    private function collectDatabaseInfo()
    {
        echo "📋 Database Configuration\n";
        echo "═══════════════════════════\n\n";

        $this->config['db_host'] = $this->prompt('Database Host', '127.0.0.1');
        $this->config['db_port'] = $this->prompt('Database Port', '3306');
        $this->config['db_username'] = $this->prompt('Database Username', 'root');
        $this->config['db_password'] = $this->promptPassword('Database Password');
        $this->config['db_name'] = $this->prompt('Database Name', 'pecunia');

        echo "\n🔧 Application Configuration\n";
        echo "═══════════════════════════════\n\n";

        $this->config['site_name'] = $this->prompt('Site Name', 'Pecunia');
        $this->config['site_url'] = $this->prompt('Site URL (without trailing slash)', 'http://localhost/butce');
        $this->config['gemini_api_key'] = $this->prompt('Google Gemini API Key (optional)', '');
        $this->config['telegram_bot_token'] = $this->prompt('Telegram Bot Token (optional)', '');
        $this->config['app_env'] = $this->prompt('Environment (development/production)', 'development');

        $this->log("Configuration collected successfully");
    }

    /**
     * Test database connection
     */
    private function testConnection()
    {
        echo "\n🔗 Testing Database Connection...\n";
        echo "═══════════════════════════════════\n";

        try {
            $dsn = "mysql:host={$this->config['db_host']};port={$this->config['db_port']};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->config['db_username'], $this->config['db_password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci"
            ]);

            echo "✅ Database connection successful!\n";
            $this->log("Database connection established");
        } catch (PDOException $e) {
            $this->addError("Database connection failed: " . $e->getMessage());
            $this->displayErrors();
            exit(1);
        }
    }

    /**
     * Create database if it doesn't exist
     */
    private function createDatabase()
    {
        echo "\n🗄️ Creating Database...\n";
        echo "═══════════════════════════\n";

        try {
            // Check if database exists
            $stmt = $this->pdo->query("SHOW DATABASES LIKE '{$this->config['db_name']}'");
            if ($stmt->rowCount() > 0) {
                $confirm = $this->prompt("Database '{$this->config['db_name']}' already exists. Drop and recreate? (yes/no)", 'no');
                if (strtolower($confirm) === 'yes') {
                    $this->pdo->exec("DROP DATABASE `{$this->config['db_name']}`");
                    echo "🗑️ Existing database dropped\n";
                }
            }

            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->config['db_name']}` 
                            CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci");
            $this->pdo->exec("USE `{$this->config['db_name']}`");

            echo "✅ Database '{$this->config['db_name']}' created successfully!\n";
            $this->log("Database created: {$this->config['db_name']}");
        } catch (PDOException $e) {
            $this->addError("Database creation failed: " . $e->getMessage());
            $this->displayErrors();
            exit(1);
        }
    }

    /**
     * Import database schema
     */
    private function importSchema()
    {
        echo "\n📊 Importing Database Schema...\n";
        echo "═══════════════════════════════════\n";

        $schemaFile = __DIR__ . '/database/database.sql';

        if (!file_exists($schemaFile)) {
            $this->addError("Schema file not found: {$schemaFile}");
            $this->displayErrors();
            exit(1);
        }

        try {
            $sql = file_get_contents($schemaFile);

            // Remove MySQL dump headers and footers
            $sql = preg_replace('/^\/\*!40\d+.*?\*\/;?$/m', '', $sql);
            $sql = preg_replace('/^--.*$/m', '', $sql);
            $sql = preg_replace('/^\s*$/m', '', $sql);

            // Split by semicolon but handle stored procedures
            $statements = $this->splitSqlStatements($sql);

            $executed = 0;
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (empty($statement) || $statement === 'COMMIT;') continue;

                try {
                    $this->pdo->exec($statement);
                    $executed++;
                } catch (PDOException $e) {
                    // Skip if table already exists
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        throw $e;
                    }
                }
            }

            echo "✅ Schema imported successfully! ({$executed} statements executed)\n";
            $this->log("Database schema imported: {$executed} statements");
        } catch (Exception $e) {
            $this->addError("Schema import failed: " . $e->getMessage());
            $this->displayErrors();
            exit(1);
        }
    }

    /**
     * Create admin user
     */
    private function createAdminUser()
    {
        echo "\n👤 Creating Admin User...\n";
        echo "═══════════════════════════════\n";

        $username = $this->prompt('Admin Username', 'admin');
        $email = $this->prompt('Admin Email', 'admin@pecunia.local');
        $password = $this->promptPassword('Admin Password');
        $fullName = $this->prompt('Admin Full Name', 'System Administrator');

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, full_name, is_admin, is_verified, verification_code, created_at) 
                VALUES (?, ?, ?, ?, 1, 1, ?, NOW())
            ");

            $stmt->execute([$username, $email, $hashedPassword, $fullName, $verificationCode]);

            echo "✅ Admin user created successfully!\n";
            echo "   Username: {$username}\n";
            echo "   Email: {$email}\n";

            $this->log("Admin user created: {$username} ({$email})");
        } catch (PDOException $e) {
            $this->addError("Admin user creation failed: " . $e->getMessage());
            $this->displayErrors();
            exit(1);
        }
    }

    /**
     * Create .env configuration file
     */
    private function createEnvFile()
    {
        echo "\n⚙️ Creating Configuration File...\n";
        echo "═══════════════════════════════════\n";

        $envContent = <<<ENV
# Pecunia Configuration File
# Generated by installer on {date}

# Database Configuration
DB_SERVER={$this->config['db_host']}:{$this->config['db_port']}
DB_USERNAME={$this->config['db_username']}
DB_PASSWORD={$this->config['db_password']}
DB_NAME={$this->config['db_name']}

# Application Configuration
SITE_NAME={$this->config['site_name']}
SITE_URL={$this->config['site_url']}
APP_ENV={$this->config['app_env']}

# API Keys
GEMINI_API_KEY={$this->config['gemini_api_key']}
TELEGRAM_BOT_TOKEN={$this->config['telegram_bot_token']}
TELEGRAM_BOT_USERNAME=

# Security
SESSION_TIMEOUT=1800
BCRYPT_COST=12
CSRF_TOKEN_EXPIRE=3600

# File Upload
MAX_FILE_SIZE=10485760
UPLOAD_PATH=secure_uploads

# Rate Limiting
RATE_LIMIT_ENABLED=1
RATE_LIMIT_CLEANUP_INTERVAL=3600
ENV;

        $envContent = str_replace('{date}', date('Y-m-d H:i:s'), $envContent);

        try {
            file_put_contents(__DIR__ . '/.env', $envContent);
            echo "✅ Configuration file (.env) created successfully!\n";
            $this->log("Configuration file created");
        } catch (Exception $e) {
            $this->addError("Configuration file creation failed: " . $e->getMessage());
            $this->displayErrors();
            exit(1);
        }
    }

    /**
     * Set proper file permissions
     */
    private function setPermissions()
    {
        echo "\n🔒 Setting File Permissions...\n";
        echo "═══════════════════════════════════\n";

        $directories = [
            'secure_uploads' => 0755,
            'database' => 0755
        ];

        $files = [
            '.env' => 0644,
            'config.php' => 0644
        ];

        foreach ($directories as $dir => $perm) {
            $path = __DIR__ . '/' . $dir;
            if (!file_exists($path)) {
                mkdir($path, $perm, true);
                echo "✅ Created directory: {$dir}\n";
            }
            chmod($path, $perm);
        }

        foreach ($files as $file => $perm) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                chmod($path, $perm);
                echo "✅ Set permissions for: {$file}\n";
            }
        }

        $this->log("File permissions set");
    }

    /**
     * Display successful installation message
     */
    private function displaySuccess()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    INSTALLATION COMPLETE!                   ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "🎉 Pecunia has been installed successfully!\n\n";
        echo "📍 Next Steps:\n";
        echo "   1. Navigate to: {$this->config['site_url']}\n";
        echo "   2. Login with your admin credentials\n";
        echo "   3. Configure additional settings as needed\n";
        echo "   4. Set up cron jobs for stock updates (optional)\n\n";
        echo "📚 Documentation: database/README.md\n";
        echo "🔧 Configuration: .env file\n";
        echo "🗂️ Database: {$this->config['db_name']}\n\n";
        echo "⚠️  Security Reminder:\n";
        echo "   - Delete this install.php file after installation\n";
        echo "   - Keep your .env file secure\n";
        echo "   - Set up HTTPS for production use\n\n";

        $this->log("Installation completed successfully");
        $this->saveInstallationLog();
    }

    /**
     * Helper methods
     */
    private function prompt($question, $default = '')
    {
        $defaultText = $default ? " [{$default}]" : '';
        echo "{$question}{$defaultText}: ";
        $input = trim(fgets(STDIN));
        return $input ?: $default;
    }

    private function promptPassword($question)
    {
        echo "{$question}: ";
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        echo "\n";
        return $password;
    }

    private function addError($error)
    {
        $this->errors[] = $error;
        $this->log("ERROR: {$error}");
    }

    private function displayErrors()
    {
        echo "\n❌ Installation Failed!\n";
        echo "═══════════════════════\n";
        foreach ($this->errors as $error) {
            echo "• {$error}\n";
        }
        echo "\n";
    }

    private function log($message)
    {
        $this->log[] = date('Y-m-d H:i:s') . " - {$message}";
    }

    private function saveInstallationLog()
    {
        $logContent = "Pecunia Installation Log\n";
        $logContent .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= str_repeat('=', 50) . "\n\n";
        $logContent .= implode("\n", $this->log);

        file_put_contents(__DIR__ . '/installation.log', $logContent);
    }

    private function splitSqlStatements($sql)
    {
        $statements = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '--') === 0) continue;

            for ($i = 0; $i < strlen($line); $i++) {
                $char = $line[$i];

                if (!$inQuotes && ($char === '"' || $char === "'")) {
                    $inQuotes = true;
                    $quoteChar = $char;
                } elseif ($inQuotes && $char === $quoteChar && ($i === 0 || $line[$i - 1] !== '\\')) {
                    $inQuotes = false;
                } elseif (!$inQuotes && $char === ';') {
                    $current .= $char;
                    $statements[] = trim($current);
                    $current = '';
                    continue;
                }

                $current .= $char;
            }
            $current .= "\n";
        }

        if (trim($current)) {
            $statements[] = trim($current);
        }

        return array_filter($statements);
    }
}

// Run installer if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    if (php_sapi_name() !== 'cli') {
        die("This installer must be run from the command line.\nUsage: php install.php\n");
    }

    $installer = new PecuniaInstaller();
    $installer->install();
}
