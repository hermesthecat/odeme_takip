<?php
header('Content-Type: application/json');

// Set default timezone
date_default_timezone_set('Europe/Istanbul');

// Common response function
function sendJsonResponse($data, $status = 200)
{
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code($status);
    }

    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Common error response function
function sendErrorResponse($message, $code = 500)
{
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
require_once __DIR__ . '/models/BudgetGoal.php';

$tests = [];
$helpContent = '';
$success = true;

try {
    $type = $_GET['type'] ?? 'basic';

    // Run tests based on type parameter
    switch ($type) {
        case 'basic':
            runBasicTests();
            break;
        case 'budget':
            runBudgetTests();
            break;
        case 'transaction':
            runTransactionTests();
            break;
        default:
            throw new Exception('Geçersiz test tipi');
    }

    $response = [
        'success' => $success,
        'tests' => $tests,
        'help' => $helpContent,
        'error' => null
    ];

    sendJsonResponse($response, 200);
} catch (Exception $e) {
    $response = [
        'success' => false,
        'tests' => $tests,
        'help' => $helpContent,
        'error' => $e->getMessage()
    ];
    sendJsonResponse($response, 500);
}

function runBasicTests(): void
{
    global $tests, $success, $helpContent;
    try {
        $db = Database::getInstance();

        // Test 1: Create temporary table
        $tests[] = [
            'name' => 'create_table',
            'status' => 'running',
            'message' => 'Test tablosu oluşturuluyor...'
        ];
        $db->exec("
            CREATE TEMPORARY TABLE test_table (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50),
                value DECIMAL(10,2)
            )
        ");
        $tests[] = [
            'name' => 'create_table',
            'status' => 'success',
            'message' => 'Test tablosu oluşturuldu'
        ];

        // Test 2: Insert data
        $tests[] = [
            'name' => 'insert',
            'status' => 'running',
            'message' => 'Veri ekleniyor...'
        ];
        $id = $db->insert(
            "INSERT INTO test_table (name, value) VALUES (:name, :value)",
            ['name' => 'Test Item', 'value' => 123.45]
        );
        if (!$id) throw new Exception("Veri eklenemedi");
        $tests[] = [
            'name' => 'insert',
            'status' => 'success',
            'message' => "Veri eklendi (ID: $id)"
        ];

        // Test 3: Select data
        $tests[] = [
            'name' => 'select',
            'status' => 'running',
            'message' => 'Veri sorgulanıyor...'
        ];
        $result = $db->select(
            "SELECT * FROM test_table WHERE id = :id",
            ['id' => $id]
        );
        if (empty($result) || $result[0]['name'] !== 'Test Item') {
            throw new Exception("Veri doğru şekilde okunamadı");
        }
        $tests[] = [
            'name' => 'select',
            'status' => 'success',
            'message' => 'Veri okundu'
        ];

        // Test 4: Update data
        $tests[] = [
            'name' => 'update',
            'status' => 'running',
            'message' => 'Veri güncelleniyor...'
        ];
        $result = $db->update(
            "UPDATE test_table SET value = :value WHERE id = :id",
            ['id' => $id, 'value' => 999.99]
        );
        if ($result !== 1) throw new Exception("Veri güncellenemedi");
        $tests[] = [
            'name' => 'update',
            'status' => 'success',
            'message' => 'Veri güncellendi'
        ];

        // Test 5: Delete data
        $tests[] = [
            'name' => 'delete',
            'status' => 'running',
            'message' => 'Veri siliniyor...'
        ];
        $result = $db->delete(
            "DELETE FROM test_table WHERE id = :id",
            ['id' => $id]
        );
        if ($result !== 1) throw new Exception("Veri silinemedi");
        $tests[] = [
            'name' => 'delete',
            'status' => 'success',
            'message' => 'Veri silindi'
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'system',
            'status' => 'error',
            'message' => 'Test hatası: ' . $e->getMessage()
        ];
        global $success;
        $success = false;
    } finally {
        try {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS test_table");
        } catch (Exception $e) {
            // Log the error but don't fail the test
            error_log("Failed to drop temporary table: " . $e->getMessage());
        }
    }
}

function runBudgetTests(): void
{
    global $tests, $success, $helpContent;
    try {
        $db = Database::getInstance();

        // Drop tables if exists
        $db->exec("DROP TEMPORARY TABLE IF EXISTS monthly_budgets");
        $db->exec("DROP TEMPORARY TABLE IF EXISTS payments");

        // Create budget test table
        $db->exec("
            CREATE TEMPORARY TABLE monthly_budgets (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                year INT NOT NULL,
                month INT NOT NULL,
                limit_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_budget_per_month (user_id, year, month)
            )
        ");
        $tests[] = [
            'name' => 'budget_table',
            'status' => 'success',
            'message' => 'Bütçe tablosu oluşturuldu'
        ];

        // Create payments test table
        $db->exec("
            CREATE TEMPORARY TABLE payments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                date DATE NOT NULL
            )
        ");
        $tests[] = [
            'name' => 'payments_table',
            'status' => 'success',
            'message' => 'Ödeme tablosu oluşturuldu'
        ];

        // Create payment_statuses test table
        $db->exec("
            CREATE TEMPORARY TABLE payment_statuses (
                id INT PRIMARY KEY AUTO_INCREMENT,
                payment_id INT NOT NULL,
                year INT NOT NULL,
                month INT NOT NULL,
                is_paid BOOLEAN NOT NULL DEFAULT 0
            )
        ");
        $tests[] = [
            'name' => 'payment_statuses_table',
            'status' => 'success',
            'message' => 'Ödeme durumları tablosu oluşturuldu'
        ];

        // Create incomes test table
        $db->exec("
            CREATE TEMPORARY TABLE incomes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                first_income_date DATE NOT NULL,
                frequency INT NOT NULL DEFAULT 0,
                repeat_count INT NULL
            )
        ");
        $tests[] = [
            'name' => 'incomes_table',
            'status' => 'success',
            'message' => 'Gelirler tablosu oluşturuldu'
        ];

        // Test budget operations
        $budgetModel = new BudgetGoal();

        // Test creating budget
        $tests[] = [
            'name' => 'budget_create',
            'status' => 'running',
            'message' => 'Bütçe oluşturuluyor...'
        ];
        $result = $budgetModel->setMonthlyBudget(1, 2025, 2, 1000.00);
        if (!$result) throw new Exception("Bütçe oluşturulamadı");
        $tests[] = [
            'name' => 'budget_create',
            'status' => 'success',
            'message' => 'Bütçe başarıyla oluşturuldu'
        ];

        // Test updating budget
        $tests[] = [
            'name' => 'budget_update',
            'status' => 'running',
            'message' => 'Bütçe güncelleniyor...'
        ];
        $result = $budgetModel->setMonthlyBudget(1, 2025, 2, 1500.00);
        if (!$result) throw new Exception("Bütçe güncellenemedi");
        $tests[] = [
            'name' => 'budget_update',
            'status' => 'success',
            'message' => 'Bütçe başarıyla güncellendi'
        ];

        // Test getting budget
        $tests[] = [
            'name' => 'budget_get',
            'status' => 'running',
            'message' => 'Bütçe alınıyor...'
        ];
        $budget = $budgetModel->getMonthlyBudget(1, 2025, 2);
        if ($budget === false) {
            throw new Exception("Bütçe bulunamadı");
        }
        $tests[] = [
            'name' => 'budget_get',
            'status' => 'success',
            'message' => 'Bütçe alındı'
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'system',
            'status' => 'error',
            'message' => 'Test hatası: ' . $e->getMessage()
        ];
        global $success;
        $success = false;
    } finally {
        try {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS monthly_budgets");
            $db->exec("DROP TEMPORARY TABLE IF EXISTS payments");
            $db->exec("DROP TEMPORARY TABLE IF EXISTS payment_statuses");
            $db->exec("DROP TEMPORARY TABLE IF EXISTS incomes");
        } catch (Exception $e) {
            // Log the error but don't fail the test
            error_log("Failed to drop temporary table: " . $e->getMessage());
        }
    }
}


function runTransactionTests(): void
{
    global $tests, $success, $helpContent;
    try {
        $db = Database::getInstance();

        // Test nested transactions
        $tests[] = [
            'name' => 'transaction_1',
            'status' => 'running',
            'message' => 'İlk transaction başlatılıyor...'
        ];
        $db->beginTransaction();
        $tests[] = [
            'name' => 'transaction_1',
            'status' => 'success',
            'message' => 'İlk transaction başlatıldı'
        ];

        $tests[] = [
            'name' => 'transaction_2',
            'status' => 'running',
            'message' => 'İkinci transaction başlatılıyor...'
        ];
        $db->beginTransaction();
        $tests[] = [
            'name' => 'transaction_2',
            'status' => 'success',
            'message' => 'İkinci transaction başlatıldı'
        ];

        // Test rollback
        $tests[] = [
            'name' => 'rollback',
            'status' => 'running',
            'message' => 'Rollback test ediliyor...'
        ];
        $db->rollback();
        $tests[] = [
            'name' => 'rollback',
            'status' => 'success',
            'message' => 'Rollback başarılı'
        ];

        // Test: Create temporary table within transaction
        $tests[] = [
            'name' => 'transaction_create_table',
            'status' => 'running',
            'message' => 'Transaction içinde tablo oluşturuluyor...'
        ];
        $db->exec("CREATE TEMPORARY TABLE transaction_test_table (id INT)");
        $tests[] = [
            'name' => 'transaction_create_table',
            'status' => 'success',
            'message' => 'Transaction içinde tablo oluşturuldu'
        ];

        // Test: Rollback transaction
        $tests[] = [
            'name' => 'transaction_rollback',
            'status' => 'running',
            'message' => 'Transaction geri alınıyor...'
        ];
        $db->rollback();
        $tests[] = [
            'name' => 'transaction_rollback',
            'status' => 'success',
            'message' => 'Transaction geri alındı'
        ];
    } catch (Exception $e) {
        $db->rollback(); // Rollback in case of exception
        $tests[] = [
            'name' => 'system',
            'status' => 'error',
            'message' => 'Test hatası: ' . $e->getMessage()
        ];
        global $success;
        $success = false;
    } finally {
        try {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS transaction_test_table");
        } catch (Exception $e) {
            // Log the error but don't fail the test
            error_log("Failed to drop temporary table: " . $e->getMessage());
        }
    }
}
