<?php
/**
 * Pecunia Web-Based Installation Script
 * @author A. Kerem Gök
 * @date 2025-01-25
 */

session_start();

// Security check - only allow installation if config.php doesn't exist or database is empty
if (file_exists('config.php')) {
    try {
        require_once 'config.php';
        if (isset($pdo)) {
            $stmt = $pdo->query("SHOW TABLES");
            if ($stmt->rowCount() > 0) {
                die('<h1>Installation Error</h1><p>Pecunia is already installed. Delete config.php to reinstall.</p>');
            }
        }
    } catch (Exception $e) {
        // Config exists but database connection fails - allow installation
    }
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors = [];
$success = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            $step = processSystemCheck() ? 2 : 1;
            break;
        case 2:
            $step = processDatabaseConfig() ? 3 : 2;
            break;
        case 3:
            $step = processApplicationConfig() ? 4 : 3;
            break;
        case 4:
            $step = processInstallation() ? 5 : 4;
            break;
    }
}

function processSystemCheck() {
    global $errors;
    
    $requirements = [
        'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'mbstring Extension' => extension_loaded('mbstring'),
        'JSON Extension' => extension_loaded('json'),
        'OpenSSL Extension' => extension_loaded('openssl'),
        'cURL Extension' => extension_loaded('curl'),
        'GD Extension' => extension_loaded('gd'),
        'Writable Directory' => is_writable(__DIR__)
    ];
    
    foreach ($requirements as $req => $met) {
        if (!$met) {
            $errors[] = "{$req} requirement not met";
        }
    }
    
    return empty($errors);
}

function processDatabaseConfig() {
    global $errors;
    
    $config = [
        'host' => $_POST['db_host'] ?? '',
        'port' => $_POST['db_port'] ?? '3306',
        'username' => $_POST['db_username'] ?? '',
        'password' => $_POST['db_password'] ?? '',
        'database' => $_POST['db_name'] ?? ''
    ];
    
    // Validate inputs
    if (empty($config['host'])) $errors[] = 'Database host is required';
    if (empty($config['username'])) $errors[] = 'Database username is required';
    if (empty($config['database'])) $errors[] = 'Database name is required';
    
    if (empty($errors)) {
        // Test connection
        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $_SESSION['db_config'] = $config;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }
    
    return empty($errors);
}

function processApplicationConfig() {
    global $errors;
    
    $config = [
        'site_name' => $_POST['site_name'] ?? 'Pecunia',
        'site_url' => rtrim($_POST['site_url'] ?? '', '/'),
        'app_env' => $_POST['app_env'] ?? 'production',
        'gemini_api_key' => $_POST['gemini_api_key'] ?? '',
        'telegram_bot_token' => $_POST['telegram_bot_token'] ?? ''
    ];
    
    if (empty($config['site_url'])) {
        $errors[] = 'Site URL is required';
    }
    
    if (empty($errors)) {
        $_SESSION['app_config'] = $config;
    }
    
    return empty($errors);
}

function processInstallation() {
    global $errors, $success;
    
    try {
        $dbConfig = $_SESSION['db_config'];
        $appConfig = $_SESSION['app_config'];
        
        // Create database connection
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci");
        $pdo->exec("USE `{$dbConfig['database']}`");
        
        // Import schema
        $schemaFile = __DIR__ . '/database/database.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception('Database schema file not found');
        }
        
        $sql = file_get_contents($schemaFile);
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Create admin user
        $adminData = [
            'username' => $_POST['admin_username'] ?? 'admin',
            'email' => $_POST['admin_email'] ?? 'admin@pecunia.local',
            'password' => $_POST['admin_password'] ?? '',
            'full_name' => $_POST['admin_fullname'] ?? 'Administrator'
        ];
        
        if (empty($adminData['password'])) {
            throw new Exception('Admin password is required');
        }
        
        $hashedPassword = password_hash($adminData['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, is_admin, is_verified, created_at) VALUES (?, ?, ?, ?, 1, 1, NOW())");
        $stmt->execute([$adminData['username'], $adminData['email'], $hashedPassword, $adminData['full_name']]);
        
        // Create .env file
        $envContent = createEnvContent($dbConfig, $appConfig);
        file_put_contents(__DIR__ . '/.env', $envContent);
        
        // Create uploads directory
        $uploadDir = __DIR__ . '/secure_uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $_SESSION['admin_data'] = $adminData;
        $success[] = 'Installation completed successfully!';
        
        return true;
        
    } catch (Exception $e) {
        $errors[] = 'Installation failed: ' . $e->getMessage();
        return false;
    }
}

function createEnvContent($dbConfig, $appConfig) {
    return <<<ENV
# Pecunia Configuration File
# Generated by web installer

# Database Configuration
DB_SERVER={$dbConfig['host']}:{$dbConfig['port']}
DB_USERNAME={$dbConfig['username']}
DB_PASSWORD={$dbConfig['password']}
DB_NAME={$dbConfig['database']}

# Application Configuration
SITE_NAME={$appConfig['site_name']}
SITE_URL={$appConfig['site_url']}
APP_ENV={$appConfig['app_env']}

# API Keys
GEMINI_API_KEY={$appConfig['gemini_api_key']}
TELEGRAM_BOT_TOKEN={$appConfig['telegram_bot_token']}

# Security
SESSION_TIMEOUT=1800
BCRYPT_COST=12

# File Upload
MAX_FILE_SIZE=10485760
UPLOAD_PATH=secure_uploads
ENV;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pecunia Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .installation-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            color: white;
            font-weight: bold;
        }
        .step.active {
            background: #28a745;
        }
        .step.completed {
            background: #17a2b8;
        }
        .step.pending {
            background: #6c757d;
        }
        .requirement-check {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
        }
        .requirement-met {
            background-color: #d4edda;
            color: #155724;
        }
        .requirement-failed {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="installation-header text-center">
        <div class="container">
            <h1><i class="bi bi-gear-fill"></i> Pecunia Installation</h1>
            <p class="lead">Personal Finance Management System Setup</p>
        </div>
    </div>

    <div class="container mt-4">
        <div class="step-indicator">
            <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'pending' ?>">1</div>
            <div class="step <?= $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'pending' ?>">2</div>
            <div class="step <?= $step >= 3 ? ($step > 3 ? 'completed' : 'active') : 'pending' ?>">3</div>
            <div class="step <?= $step >= 4 ? ($step > 4 ? 'completed' : 'active') : 'pending' ?>">4</div>
            <div class="step <?= $step >= 5 ? 'completed' : 'pending' ?>">5</div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <h5><i class="bi bi-exclamation-triangle"></i> Errors:</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle"></i> Success:</h5>
                <ul class="mb-0">
                    <?php foreach ($success as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <?php if ($step === 1): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-clipboard-check"></i> Step 1: System Requirements</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $requirements = [
                                'PHP Version (' . PHP_VERSION . ')' => version_compare(PHP_VERSION, '7.4.0', '>='),
                                'PDO Extension' => extension_loaded('pdo'),
                                'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
                                'mbstring Extension' => extension_loaded('mbstring'),
                                'JSON Extension' => extension_loaded('json'),
                                'OpenSSL Extension' => extension_loaded('openssl'),
                                'cURL Extension' => extension_loaded('curl'),
                                'GD Extension' => extension_loaded('gd'),
                                'Writable Directory' => is_writable(__DIR__)
                            ];
                            
                            $allMet = true;
                            foreach ($requirements as $req => $met):
                                if (!$met) $allMet = false;
                            ?>
                                <div class="requirement-check <?= $met ? 'requirement-met' : 'requirement-failed' ?>">
                                    <i class="bi bi-<?= $met ? 'check-circle' : 'x-circle' ?>"></i>
                                    <?= $req ?> - <?= $met ? 'OK' : 'FAILED' ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <form method="post" class="mt-3">
                                <button type="submit" class="btn btn-primary" <?= !$allMet ? 'disabled' : '' ?>>
                                    <i class="bi bi-arrow-right"></i> Continue
                                </button>
                            </form>
                        </div>
                    </div>

                <?php elseif ($step === 2): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-database"></i> Step 2: Database Configuration</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Database Host</label>
                                    <input type="text" class="form-control" name="db_host" value="<?= $_POST['db_host'] ?? '127.0.0.1' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Database Port</label>
                                    <input type="number" class="form-control" name="db_port" value="<?= $_POST['db_port'] ?? '3306' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Database Username</label>
                                    <input type="text" class="form-control" name="db_username" value="<?= $_POST['db_username'] ?? 'root' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Database Password</label>
                                    <input type="password" class="form-control" name="db_password" value="<?= $_POST['db_password'] ?? '' ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Database Name</label>
                                    <input type="text" class="form-control" name="db_name" value="<?= $_POST['db_name'] ?? 'pecunia' ?>" required>
                                    <div class="form-text">Database will be created if it doesn't exist</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Continue
                                </button>
                            </form>
                        </div>
                    </div>

                <?php elseif ($step === 3): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-gear"></i> Step 3: Application Configuration</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="site_name" value="<?= $_POST['site_name'] ?? 'Pecunia' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Site URL</label>
                                    <input type="url" class="form-control" name="site_url" value="<?= $_POST['site_url'] ?? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) ?>" required>
                                    <div class="form-text">Full URL without trailing slash</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Environment</label>
                                    <select class="form-control" name="app_env">
                                        <option value="production" <?= ($_POST['app_env'] ?? 'production') === 'production' ? 'selected' : '' ?>>Production</option>
                                        <option value="development" <?= ($_POST['app_env'] ?? '') === 'development' ? 'selected' : '' ?>>Development</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Google Gemini API Key (Optional)</label>
                                    <input type="text" class="form-control" name="gemini_api_key" value="<?= $_POST['gemini_api_key'] ?? '' ?>">
                                    <div class="form-text">For AI document analysis features</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Telegram Bot Token (Optional)</label>
                                    <input type="text" class="form-control" name="telegram_bot_token" value="<?= $_POST['telegram_bot_token'] ?? '' ?>">
                                    <div class="form-text">For Telegram bot integration</div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-right"></i> Continue
                                </button>
                            </form>
                        </div>
                    </div>

                <?php elseif ($step === 4): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="bi bi-person-plus"></i> Step 4: Admin User & Installation</h4>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Admin Username</label>
                                    <input type="text" class="form-control" name="admin_username" value="<?= $_POST['admin_username'] ?? 'admin' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" name="admin_email" value="<?= $_POST['admin_email'] ?? 'admin@pecunia.local' ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Password</label>
                                    <input type="password" class="form-control" name="admin_password" required>
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin Full Name</label>
                                    <input type="text" class="form-control" name="admin_fullname" value="<?= $_POST['admin_fullname'] ?? 'System Administrator' ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-download"></i> Install Pecunia
                                </button>
                            </form>
                        </div>
                    </div>

                <?php elseif ($step === 5): ?>
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h4><i class="bi bi-check-circle"></i> Installation Complete!</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h5>🎉 Pecunia has been installed successfully!</h5>
                                <p>Your personal finance management system is ready to use.</p>
                            </div>
                            
                            <h6>📍 Next Steps:</h6>
                            <ol>
                                <li><strong>Security:</strong> Delete this installation file for security</li>
                                <li><strong>Login:</strong> Use your admin credentials to access the system</li>
                                <li><strong>Configuration:</strong> Review and adjust settings as needed</li>
                                <li><strong>Documentation:</strong> Check database/README.md for detailed information</li>
                            </ol>
                            
                            <h6>📊 Installation Summary:</h6>
                            <ul>
                                <li>Database: <?= $_SESSION['db_config']['database'] ?></li>
                                <li>Admin User: <?= $_SESSION['admin_data']['username'] ?></li>
                                <li>Site URL: <?= $_SESSION['app_config']['site_url'] ?></li>
                            </ul>
                            
                            <div class="mt-4">
                                <a href="<?= $_SESSION['app_config']['site_url'] ?>" class="btn btn-primary btn-lg me-2">
                                    <i class="bi bi-house"></i> Go to Pecunia
                                </a>
                                <a href="<?= $_SESSION['app_config']['site_url'] ?>/login.php" class="btn btn-outline-primary btn-lg">
                                    <i class="bi bi-person"></i> Admin Login
                                </a>
                            </div>
                            
                            <div class="alert alert-warning mt-4">
                                <strong><i class="bi bi-shield-exclamation"></i> Security Reminder:</strong>
                                Delete this <code>install_web.php</code> file after installation for security reasons.
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    // Clear session data after successful installation
                    session_destroy();
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>