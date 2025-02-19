<?php
// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/models/Payment.php';
require_once __DIR__ . '/models/Income.php';
require_once __DIR__ . '/models/Saving.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/BudgetGoal.php';
require_once __DIR__ . '/models/ExchangeRate.php';

class ApiTester
{
    private $userId = 1;
    private $baseUrl = '/api';
    private $testResults = [];

    public function runTests()
    {
        try {
            // Test database connection
            $this->testDatabaseConnection();

            // Test endpoints
            $this->testPaymentEndpoints();
            $this->testIncomeEndpoints();
            $this->testSavingEndpoints();
            $this->testCategoryEndpoints();
            $this->testBudgetGoalEndpoints();
            $this->testExchangeRateEndpoints();

            // Display results
            $this->displayResults();
        } catch (Exception $e) {
            echo "Test suite failed: " . $e->getMessage() . "\n";
        }
    }

    private function testDatabaseConnection()
    {
        $this->startTest('Database Connection');
        try {
            $db = Database::getInstance();
            $db->getConnection()->query('SELECT 1');
            $this->passTest('Database Connection');
        } catch (Exception $e) {
            $this->failTest('Database Connection', $e->getMessage());
        }
    }

    private function testPaymentEndpoints()
    {
        // Test payment creation
        $this->startTest('Create Payment');
        try {
            $payment = [
                'name' => 'Test Payment',
                'amount' => 100.00,
                'currency' => 'TRY',
                'first_payment_date' => date('Y-m-d'),
                'frequency' => 0
            ];

            $controller = new PaymentController($this->userId);
            $result = $controller->handleRequest('POST', [], $payment);

            if (!isset($result['id'])) {
                throw new Exception('Payment creation failed');
            }

            $paymentId = $result['id'];
            $this->passTest('Create Payment');

            // Test payment retrieval
            $this->startTest('Get Payment');
            $result = $controller->handleRequest('GET', [$paymentId], []);
            if (!isset($result['name']) || $result['name'] !== $payment['name']) {
                throw new Exception('Payment retrieval failed');
            }
            $this->passTest('Get Payment');

            // Test payment update
            $this->startTest('Update Payment');
            $payment['name'] = 'Updated Test Payment';
            $result = $controller->handleRequest('PUT', [$paymentId], $payment);
            if (!$result['success']) {
                throw new Exception('Payment update failed');
            }
            $this->passTest('Update Payment');

            // Test payment deletion
            $this->startTest('Delete Payment');
            $result = $controller->handleRequest('DELETE', [$paymentId], []);
            if (!$result['success']) {
                throw new Exception('Payment deletion failed');
            }
            $this->passTest('Delete Payment');
        } catch (Exception $e) {
            $this->failTest('Payment Tests', $e->getMessage());
        }
    }

    private function testIncomeEndpoints()
    {
        // Similar structure to payment tests
        $this->startTest('Income Tests');
        try {
            $income = [
                'name' => 'Test Income',
                'amount' => 5000.00,
                'currency' => 'TRY',
                'first_income_date' => date('Y-m-d'),
                'frequency' => 1
            ];

            $controller = new IncomeController($this->userId);
            $result = $controller->handleRequest('POST', [], $income);

            if (!isset($result['id'])) {
                throw new Exception('Income creation failed');
            }

            $this->passTest('Income Tests');
        } catch (Exception $e) {
            $this->failTest('Income Tests', $e->getMessage());
        }
    }

    private function testSavingEndpoints()
    {
        $this->startTest('Saving Tests');
        try {
            $saving = [
                'name' => 'Test Saving',
                'target_amount' => 10000.00,
                'current_amount' => 0,
                'currency' => 'TRY',
                'start_date' => date('Y-m-d'),
                'target_date' => date('Y-m-d', strtotime('+1 year'))
            ];

            $controller = new SavingController($this->userId);
            $result = $controller->handleRequest('POST', [], $saving);

            if (!isset($result['id'])) {
                throw new Exception('Saving creation failed');
            }

            $this->passTest('Saving Tests');
        } catch (Exception $e) {
            $this->failTest('Saving Tests', $e->getMessage());
        }
    }

    private function testCategoryEndpoints()
    {
        $this->startTest('Category Tests');
        try {
            $category = [
                'name' => 'Test Category',
                'monthly_limit' => 1000.00
            ];

            $controller = new CategoryController($this->userId);
            $result = $controller->handleRequest('POST', [], $category);

            if (!isset($result['id'])) {
                throw new Exception('Category creation failed');
            }

            $this->passTest('Category Tests');
        } catch (Exception $e) {
            $this->failTest('Category Tests', $e->getMessage());
        }
    }

    private function testBudgetGoalEndpoints()
    {
        $this->startTest('Budget Goal Tests');
        try {
            $budget = [
                'year' => date('Y'),
                'month' => date('n'),
                'limit' => 5000.00
            ];

            $controller = new BudgetGoalController($this->userId);
            $result = $controller->handleRequest('POST', ['monthly'], $budget);

            if (!isset($result['success']) || !$result['success']) {
                throw new Exception('Budget goal creation failed');
            }

            $this->passTest('Budget Goal Tests');
        } catch (Exception $e) {
            $this->failTest('Budget Goal Tests', $e->getMessage());
        }
    }

    private function testExchangeRateEndpoints()
    {
        $this->startTest('Exchange Rate Tests');
        try {
            $controller = new ExchangeRateController();
            $result = $controller->handleRequest('GET', ['USD', 'TRY'], []);

            if (!isset($result['rate'])) {
                throw new Exception('Exchange rate retrieval failed');
            }

            $this->passTest('Exchange Rate Tests');
        } catch (Exception $e) {
            $this->failTest('Exchange Rate Tests', $e->getMessage());
        }
    }

    private function startTest($name)
    {
        echo "\nStarting test: $name\n";
    }

    private function passTest($name)
    {
        $this->testResults[$name] = true;
        echo "✓ $name passed\n";
    }

    private function failTest($name, $error)
    {
        $this->testResults[$name] = false;
        echo "✗ $name failed: $error\n";
    }

    private function displayResults()
    {
        echo "\n=== Test Results ===\n";
        $total = count($this->testResults);
        $passed = count(array_filter($this->testResults));

        foreach ($this->testResults as $test => $passed) {
            echo ($passed ? '✓' : '✗') . " $test\n";
        }

        echo "\nTotal: $total, Passed: $passed, Failed: " . ($total - $passed) . "\n";
    }
}

// Run tests
$tester = new ApiTester();
$tester->runTests();
