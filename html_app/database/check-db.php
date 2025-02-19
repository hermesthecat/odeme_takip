<?php
header('Content-Type: application/json');

// Set default timezone
date_default_timezone_set('Europe/Istanbul');

// Common response function
function sendJsonResponse($data, $status = 200) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($status);
    }

    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Common error response function
function sendErrorResponse($message, $code = 500) {
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ];

    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($code);
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

require_once __DIR__ . '/Database.php';

$tests = [];
$helpContent = '';
$success = true;

// 1. Check config file
$configTest = checkConfigFile();
$tests[] = $configTest;
if ($configTest['status'] === 'error') {
    $success = false;
}

// 2. Check MySQL server connection
$mysqlTest = checkMySQLServer();
$tests[] = $mysqlTest;
if ($mysqlTest['status'] === 'error') {
    $success = false;
}

// 3. Check database existence and access
$databaseTest = checkDatabase();
$tests[] = $databaseTest;
if ($databaseTest['status'] === 'error') {
    $success = false;
}

// 4. Check user privileges
$privilegesTest = checkPrivileges();
$tests[] = $privilegesTest;
if ($privilegesTest['status'] === 'error') {
    $success = false;
}

$response = [
    'success' => $success,
    'tests' => $tests,
    'help' => $helpContent,
    'error' => null
];

sendJsonResponse($response, 200);

function checkConfigFile(): array {
    try {
        if (!file_exists(__DIR__ . '/config.php')) {
            throw new Exception("config.php dosyası bulunamadı");
        }

        $config = require __DIR__ . '/config.php';
        $required = ['host', 'dbname', 'username', 'password', 'charset'];
        $missing = array_diff($required, array_keys($config));

        if (!empty($missing)) {
            throw new Exception('Eksik yapılandırma parametreleri: ' . implode(', ', $missing));
        }

        return [
            'name' => 'config',
            'status' => 'success',
            'message' => 'Yapılandırma dosyası geçerli'
        ];
    } catch (Exception $e) {
        return [
            'name' => 'config',
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function checkMySQLServer(): array {
    try {
        $config = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$config['host']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        
        return [
            'name' => 'mysql_server',
            'status' => 'success',
            'message' => 'MySQL sunucusuna bağlantı başarılı'
        ];
    } catch (PDOException $e) {
        return [
            'name' => 'mysql_server',
            'status' => 'error',
            'message' => 'MySQL bağlantı hatası: ' . $e->getMessage()
        ];
    }
}

function checkDatabase(): array {
    try {
        $config = require __DIR__ . '/config.php';
        $pdo = new PDO(
            "mysql:host={$config['host']}",
            $config['username'],
            $config['password']
        );

        $dbname = $config['dbname'];
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
        $exists = $stmt->rowCount() > 0;

        if ($exists) {
            $dbPdo = new PDO(
                "mysql:host={$config['host']};dbname={$dbname};charset={$config['charset']}",
                $config['username'],
                $config['password']
            );
            return [
                'name' => 'database',
                'status' => 'success',
                'message' => 'Veritabanı mevcut'
            ];
        } else {
            return [
                'name' => 'database',
                'status' => 'error',
                'message' => 'Veritabanı bulunamadı',
                'help' => getDatabaseCreationHelp($config)
            ];
        }
    } catch (PDOException $e) {
        return [
            'name' => 'database_connection',
            'status' => 'error',
            'message' => 'Veritabanı bağlantı hatası: ' . $e->getMessage()
        ];
    }
}

function checkPrivileges(): array {
    try {
        $db = Database::getInstance();
        $privileges = $db->query("SHOW GRANTS")->fetchAll(PDO::FETCH_COLUMN);
        
        $hasAllPrivileges = false;
        $requiredPrivileges = ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE'];
        $missingPrivileges = [];

        foreach ($privileges as $grant) {
            if (strpos($grant, 'ALL PRIVILEGES') !== false) {
                $hasAllPrivileges = true;
                break;
            }

            foreach ($requiredPrivileges as $priv) {
                if (strpos($grant, $priv) !== false) {
                    $key = array_search($priv, $requiredPrivileges);
                    if ($key !== false) {
                        unset($requiredPrivileges[$key]);
                    }
                }
            }
        }

        if ($hasAllPrivileges) {
            return [
                'name' => 'privileges',
                'status' => 'success',
                'message' => 'Kullanıcı tüm yetkilere sahip'
            ];
        } else {
            if (empty($requiredPrivileges)) {
                return [
                    'name' => 'privileges',
                    'status' => 'success',
                    'message' => 'Kullanıcı gerekli yetkilere sahip'
                ];
            } else {
                return [
                    'name' => 'privileges',
                    'status' => 'error',
                    'message' => 'Eksik yetkiler: ' . implode(', ', $requiredPrivileges),
                    'help' => getPrivilegesHelp()
                ];
            }
        }
    } catch (PDOException $e) {
        return [
            'name' => 'privileges',
            'status' => 'error',
            'message' => 'Yetki kontrolü başarısız: ' . $e->getMessage()
        ];
    }
}

function getDatabaseCreationHelp(array $config): string {
    return sprintf('
        <h4>Veritabanı Oluşturma</h4>
        <p>Aşağıdaki SQL komutlarını çalıştırın:</p>
        <div class="code">
CREATE DATABASE %s CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON %s.* TO \'%s\'@\'localhost\';
FLUSH PRIVILEGES;
        </div>
    ',
        $config['dbname'],
        $config['dbname'],
        $config['username']
    );
}

function getPrivilegesHelp(): string {
    return '
        <h4>Kullanıcı Yetkileri</h4>
        <p>Kullanıcıya aşağıdaki yetkilerin verilmesi gerekiyor:</p>
        <ul>
            <li>SELECT</li>
            <li>INSERT</li>
            <li>UPDATE</li>
            <li>DELETE</li>
            <li>CREATE</li>
        </ul>
        <p>Yetkileri güncellemek için:</p>
        <div class="code">
GRANT ALL PRIVILEGES ON database_name.* TO \'username\'@\'localhost\';
FLUSH PRIVILEGES;
        </div>
    ';
}
