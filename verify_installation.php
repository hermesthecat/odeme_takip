<?php
/**
 * Pecunia Installation Verification Script
 * @author A. Kerem Gök
 * @date 2025-01-25
 */

require_once 'config.php';

class InstallationVerifier
{
    private $checks = [];
    private $errors = [];
    private $warnings = [];
    private $passed = 0;
    private $total = 0;

    public function __construct()
    {
        echo "🔍 Pecunia Installation Verification\n";
        echo "═══════════════════════════════════════\n\n";
    }

    /**
     * Run all verification checks
     */
    public function verify()
    {
        $this->checkEnvironment();
        $this->checkDatabase();
        $this->checkFiles();
        $this->checkPermissions();
        $this->checkConfiguration();
        $this->checkSecurity();
        $this->generateReport();
    }

    /**
     * Check system environment
     */
    private function checkEnvironment()
    {
        echo "🌐 Environment Checks\n";
        echo "──────────────────────\n";

        $this->check('PHP Version >= 7.4', version_compare(PHP_VERSION, '7.4.0', '>='));
        $this->check('PDO Extension', extension_loaded('pdo'));
        $this->check('PDO MySQL Extension', extension_loaded('pdo_mysql'));
        $this->check('mbstring Extension', extension_loaded('mbstring'));
        $this->check('JSON Extension', extension_loaded('json'));
        $this->check('OpenSSL Extension', extension_loaded('openssl'));
        $this->check('cURL Extension', extension_loaded('curl'));
        $this->check('GD Extension', extension_loaded('gd'));
        
        echo "\n";
    }

    /**
     * Check database connectivity and structure
     */
    private function checkDatabase()
    {
        echo "🗄️ Database Checks\n";
        echo "──────────────────\n";

        global $pdo;
        
        // Database connection
        $this->check('Database Connection', $pdo !== null);

        if ($pdo) {
            try {
                // Check required tables
                $requiredTables = [
                    'users', 'income', 'payments', 'savings', 'cards', 'portfolio',
                    'exchange_rates', 'telegram_users', 'ai_analysis_temp', 'logs',
                    'rate_limiting'
                ];

                foreach ($requiredTables as $table) {
                    $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
                    $this->check("Table '{$table}' exists", $stmt->rowCount() > 0);
                }

                // Check admin user
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 1");
                $adminCount = $stmt->fetchColumn();
                $this->check('Admin user exists', $adminCount > 0);

                // Check indexes
                $this->checkDatabaseIndexes();
                
                // Check foreign keys
                $this->checkForeignKeys();

            } catch (Exception $e) {
                $this->check('Database Query Test', false, $e->getMessage());
            }
        }

        echo "\n";
    }

    /**
     * Check required files exist
     */
    private function checkFiles()
    {
        echo "📁 File Structure Checks\n";
        echo "────────────────────────\n";

        $requiredFiles = [
            'config.php' => 'Main configuration file',
            '.env' => 'Environment variables',
            'database/database.sql' => 'Database schema',
            'classes/Language.php' => 'Language system',
            'api/validate.php' => 'Validation utilities',
            'js/utils.js' => 'JavaScript utilities',
            'css/style.css' => 'Main stylesheet'
        ];

        foreach ($requiredFiles as $file => $description) {
            $exists = file_exists(__DIR__ . '/' . $file);
            $this->check("{$description} ({$file})", $exists);
        }

        $requiredDirs = [
            'api' => 'API modules',
            'css' => 'Stylesheets',
            'js' => 'JavaScript files',
            'lang' => 'Language files',
            'database' => 'Database files',
            'secure_uploads' => 'Upload directory'
        ];

        foreach ($requiredDirs as $dir => $description) {
            $exists = is_dir(__DIR__ . '/' . $dir);
            $this->check("{$description} ({$dir}/)", $exists);
        }

        echo "\n";
    }

    /**
     * Check file permissions
     */
    private function checkPermissions()
    {
        echo "🔒 Permission Checks\n";
        echo "────────────────────\n";

        $this->check('secure_uploads/ writable', is_writable(__DIR__ . '/secure_uploads'));
        $this->check('.env readable', is_readable(__DIR__ . '/.env'));
        $this->check('config.php readable', is_readable(__DIR__ . '/config.php'));
        
        // Check if web installer still exists (security issue)
        if (file_exists(__DIR__ . '/install_web.php')) {
            $this->warning('Web installer still exists - should be deleted for security');
        }

        echo "\n";
    }

    /**
     * Check configuration
     */
    private function checkConfiguration()
    {
        echo "⚙️ Configuration Checks\n";
        echo "─────────────────────\n";

        // Check constants are defined
        $requiredConstants = [
            'DB_NAME' => 'Database name',
            'SITE_NAME' => 'Site name',
            'SITE_URL' => 'Site URL',
            'APP_ENV' => 'Environment'
        ];

        foreach ($requiredConstants as $constant => $description) {
            $defined = defined($constant);
            $this->check("{$description} ({$constant})", $defined);
        }

        // Check optional API keys
        if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
            echo "✅ Google Gemini API key configured\n";
        } else {
            $this->warning('Google Gemini API key not configured - AI features disabled');
        }

        if (defined('TELEGRAM_BOT_TOKEN') && !empty(TELEGRAM_BOT_TOKEN)) {
            echo "✅ Telegram bot token configured\n";
        } else {
            $this->warning('Telegram bot token not configured - bot features disabled');
        }

        echo "\n";
    }

    /**
     * Check security configuration
     */
    private function checkSecurity()
    {
        echo "🛡️ Security Checks\n";
        echo "──────────────────\n";

        // Check session configuration
        $this->check('Session auto-start disabled', !ini_get('session.auto_start'));
        $this->check('Secure session settings', 
            ini_get('session.cookie_httponly') && 
            (ini_get('session.cookie_secure') || !isset($_SERVER['HTTPS']))
        );

        // Check error reporting
        if (defined('APP_ENV') && APP_ENV === 'production') {
            $this->check('Error display disabled (production)', !ini_get('display_errors'));
        } else {
            $this->warning('Development mode - error display enabled');
        }

        // Check file upload security
        $this->check('File uploads enabled', ini_get('file_uploads'));
        $maxSize = ini_get('upload_max_filesize');
        $this->check("Upload size limit reasonable ({$maxSize})", 
            $this->parseSize($maxSize) <= $this->parseSize('50M')
        );

        echo "\n";
    }

    /**
     * Check database indexes
     */
    private function checkDatabaseIndexes()
    {
        global $pdo;
        
        $criticalIndexes = [
            'users' => ['username', 'email'],
            'income' => ['user_id'],
            'payments' => ['user_id'],
            'savings' => ['user_id'],
            'portfolio' => ['user_id'],
            'logs' => ['user_id', 'created_at']
        ];

        foreach ($criticalIndexes as $table => $columns) {
            foreach ($columns as $column) {
                try {
                    $stmt = $pdo->query("SHOW INDEX FROM {$table} WHERE Column_name = '{$column}'");
                    $hasIndex = $stmt->rowCount() > 0;
                    $this->check("Index on {$table}.{$column}", $hasIndex);
                } catch (Exception $e) {
                    $this->check("Index check {$table}.{$column}", false, $e->getMessage());
                }
            }
        }
    }

    /**
     * Check foreign key constraints
     */
    private function checkForeignKeys()
    {
        global $pdo;
        
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as fk_count 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' 
                AND CONSTRAINT_SCHEMA = '" . DB_NAME . "'
            ");
            $fkCount = $stmt->fetchColumn();
            $this->check('Foreign key constraints', $fkCount > 5, "Found {$fkCount} foreign keys");
        } catch (Exception $e) {
            $this->check('Foreign key check', false, $e->getMessage());
        }
    }

    /**
     * Helper methods
     */
    private function check($description, $passed, $details = '')
    {
        $this->total++;
        
        if ($passed) {
            $this->passed++;
            echo "✅ {$description}\n";
        } else {
            $this->errors[] = $description . ($details ? " ({$details})" : '');
            echo "❌ {$description}" . ($details ? " - {$details}" : '') . "\n";
        }
    }

    private function warning($message)
    {
        $this->warnings[] = $message;
        echo "⚠️ {$message}\n";
    }

    private function parseSize($size)
    {
        $units = ['B', 'K', 'M', 'G'];
        $size = trim($size);
        $last = strtoupper(substr($size, -1));
        $size = (int) $size;
        
        if (in_array($last, $units)) {
            $size *= pow(1024, array_search($last, $units));
        }
        
        return $size;
    }

    /**
     * Generate final report
     */
    private function generateReport()
    {
        echo "📊 Verification Report\n";
        echo "══════════════════════\n";
        
        $percentage = $this->total > 0 ? round(($this->passed / $this->total) * 100, 1) : 0;
        
        echo "✅ Passed: {$this->passed}/{$this->total} ({$percentage}%)\n";
        echo "❌ Failed: " . count($this->errors) . "\n";
        echo "⚠️ Warnings: " . count($this->warnings) . "\n\n";

        if (!empty($this->errors)) {
            echo "🚨 Critical Issues:\n";
            foreach ($this->errors as $error) {
                echo "  • {$error}\n";
            }
            echo "\n";
        }

        if (!empty($this->warnings)) {
            echo "⚠️ Warnings:\n";
            foreach ($this->warnings as $warning) {
                echo "  • {$warning}\n";
            }
            echo "\n";
        }

        // Overall status
        if (count($this->errors) === 0) {
            echo "🎉 Installation verification PASSED!\n";
            echo "✅ Pecunia is properly installed and ready to use.\n\n";
            
            echo "🔗 Next Steps:\n";
            echo "  • Visit your site: " . (defined('SITE_URL') ? SITE_URL : 'http://localhost') . "\n";
            echo "  • Login with your admin credentials\n";
            echo "  • Configure additional settings as needed\n";
            
            if (file_exists(__DIR__ . '/install_web.php')) {
                echo "  • Delete install_web.php for security\n";
            }
            
        } else {
            echo "❌ Installation verification FAILED!\n";
            echo "🔧 Please fix the critical issues above before using Pecunia.\n";
        }

        echo "\n" . str_repeat('═', 50) . "\n";
        echo "Verification completed at " . date('Y-m-d H:i:s') . "\n";
        
        // Save report to file
        $this->saveReport();
    }

    private function saveReport()
    {
        $report = "Pecunia Installation Verification Report\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n";
        $report .= str_repeat('=', 50) . "\n\n";
        
        $report .= "Summary:\n";
        $report .= "- Passed: {$this->passed}/{$this->total}\n";
        $report .= "- Failed: " . count($this->errors) . "\n";
        $report .= "- Warnings: " . count($this->warnings) . "\n\n";
        
        if (!empty($this->errors)) {
            $report .= "Critical Issues:\n";
            foreach ($this->errors as $error) {
                $report .= "- {$error}\n";
            }
            $report .= "\n";
        }
        
        if (!empty($this->warnings)) {
            $report .= "Warnings:\n";
            foreach ($this->warnings as $warning) {
                $report .= "- {$warning}\n";
            }
        }
        
        file_put_contents(__DIR__ . '/verification_report.txt', $report);
        echo "📄 Report saved to: verification_report.txt\n";
    }
}

// Run verification if called directly
if (php_sapi_name() === 'cli') {
    $verifier = new InstallationVerifier();
    $verifier->verify();
} else {
    // Simple web interface
    if (isset($_GET['run'])) {
        header('Content-Type: text/plain');
        $verifier = new InstallationVerifier();
        $verifier->verify();
    } else {
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Pecunia Installation Verification</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔍 Pecunia Installation Verification</h1>
    <p>This tool will verify that your Pecunia installation is complete and properly configured.</p>
    <p><a href="?run=1" class="btn">Run Verification</a></p>
    <p><small>For detailed output, run from command line: <code>php verify_installation.php</code></small></p>
</body>
</html>';
    }
}
?>