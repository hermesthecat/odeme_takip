<?php
require_once __DIR__ . '/api/common.php';
require_once __DIR__ . '/models/Payment.php';
require_once __DIR__ . '/models/Income.php';
require_once __DIR__ . '/models/Saving.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/BudgetGoal.php';
require_once __DIR__ . '/models/ExchangeRate.php';

class DatabaseMigration {
    private $db;
    private $defaultUserId = 1;
    private $exportFile;
    private $progress = [];

    public function __construct() {
        $this->exportFile = __DIR__ . '/localstorage-export.json';
        $this->checkRequirements();
        $this->db = Database::getInstance();
    }

    private function checkRequirements(): void {
        // Check if export file exists
        if (!file_exists($this->exportFile)) {
            sendErrorResponse(
                'Export dosyası bulunamadı',
                400,
                [
                    'file' => $this->exportFile,
                    'help' => 'Lütfen önce export.html sayfasını kullanarak verileri dışa aktarın.'
                ]
            );
        }

        // Verify JSON format
        $content = file_get_contents($this->exportFile);
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendErrorResponse(
                'Export dosyası geçersiz JSON formatında',
                400,
                ['error' => json_last_error_msg()]
            );
        }

        // Check required data sections
        $required = ['payments', 'incomes', 'savings', 'budgetGoals'];
        $missing = array_diff($required, array_keys($data));
        if (!empty($missing)) {
            sendErrorResponse(
                'Export dosyasında eksik veriler var',
                400,
                ['missing' => $missing]
            );
        }
    }

    public function migrate(): void {
        try {
            // Start transaction
            $this->db->beginTransaction();
            $this->addProgress('transaction', 'success', 'Transaction başlatıldı');

            // Create schema
            $this->executeSchemaFile();
            $this->addProgress('schema', 'success', 'Veritabanı şeması oluşturuldu');

            // Create default user
            $this->createDefaultUser();
            $this->addProgress('user', 'success', 'Varsayılan kullanıcı oluşturuldu');

            // Migrate data
            $this->migrateFromLocalStorage();
            $this->addProgress('data', 'success', 'Veriler aktarıldı');

            // Verify migration
            $this->verifyMigration();
            $this->addProgress('verify', 'success', 'Veri doğrulaması başarılı');

            // Commit transaction
            $this->db->commit();
            $this->addProgress('commit', 'success', 'Transaction commit edildi');

            sendJsonResponse([
                'success' => true,
                'progress' => $this->progress,
                'message' => 'Migration başarıyla tamamlandı'
            ]);

        } catch (Exception $e) {
            if ($this->db && $this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
                $this->addProgress('rollback', 'error', 'Transaction geri alındı');
            }

            sendErrorResponse(
                'Migration başarısız: ' . $e->getMessage(),
                500,
                ['progress' => $this->progress]
            );
        }
    }

    private function addProgress(string $step, string $status, string $message): void {
        $this->progress[] = [
            'step' => $step,
            'status' => $status,
            'message' => $message,
            'timestamp' => date('c')
        ];
    }

    private function executeSchemaFile(): void {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $statements = array_filter(
            array_map(
                'trim',
                preg_split("/;(\r\n|\n)/", $sql)
            )
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $this->db->exec($statement);
            }
        }
    }

    private function createDefaultUser(): void {
        $this->db->insertOrUpdate('users', [
            'id' => $this->defaultUserId,
            'username' => 'default',
            'password_hash' => password_hash('default', PASSWORD_DEFAULT)
        ]);
    }

    private function migrateFromLocalStorage(): void {
        $data = $this->getLocalStorageData();

        // Migrate in correct order
        $this->migrateBudgetGoals($data['budgetGoals'] ?? []);
        $this->addProgress('budget_goals', 'success', 'Bütçe hedefleri aktarıldı');

        $this->migratePayments($data['payments'] ?? []);
        $this->addProgress('payments', 'success', 'Ödemeler aktarıldı');

        $this->migrateIncomes($data['incomes'] ?? []);
        $this->addProgress('incomes', 'success', 'Gelirler aktarıldı');

        $this->migrateSavings($data['savings'] ?? []);
        $this->addProgress('savings', 'success', 'Birikimler aktarıldı');

        $this->migrateExchangeRates($data['exchangeRates'] ?? []);
        $this->addProgress('exchange_rates', 'success', 'Döviz kurları aktarıldı');
    }

    private function getLocalStorageData(): array {
        $jsonData = file_get_contents($this->exportFile);
        if ($jsonData === false) {
            throw new Exception("Failed to read export file: " . $this->exportFile);
        }

        $data = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Failed to parse JSON data: " . json_last_error_msg());
        }

        return $data;
    }

    private function migrateBudgetGoals(array $goals): void {
        $categoryModel = new Category();
        $budgetModel = new BudgetGoal();

        // Migrate categories
        foreach ($goals['categories'] ?? [] as $category) {
            $categoryModel->create([
                'user_id' => $this->defaultUserId,
                'name' => $category['name'],
                'monthly_limit' => $category['limit']
            ]);
        }

        // Migrate monthly limits
        foreach ($goals['monthlyLimits'] ?? [] as $monthKey => $limit) {
            list($year, $month) = explode('-', $monthKey);
            $budgetModel->setMonthlyBudget(
                $this->defaultUserId,
                (int)$year,
                (int)$month,
                (float)$limit
            );
        }
    }

    private function migratePayments(array $payments): void {
        $paymentModel = new Payment();

        foreach ($payments as $payment) {
            $data = [
                'user_id' => $this->defaultUserId,
                'name' => $payment['name'],
                'amount' => $payment['amount'],
                'currency' => $payment['currency'],
                'first_payment_date' => $payment['firstPaymentDate'],
                'frequency' => $payment['frequency'],
                'repeat_count' => $payment['repeatCount'] ?? null
            ];

            if (isset($payment['category'])) {
                $data['category_id'] = $this->getCategoryId($payment['category']);
            }

            $paymentId = $paymentModel->create($data);

            if (isset($payment['paidMonths']) && is_array($payment['paidMonths'])) {
                foreach ($payment['paidMonths'] as $monthKey) {
                    list($year, $month) = explode('-', $monthKey);
                    $paymentModel->updateStatus($paymentId, (int)$year, (int)$month, true);
                }
            }
        }
    }

    private function migrateIncomes(array $incomes): void {
        $incomeModel = new Income();

        foreach ($incomes as $income) {
            $incomeModel->create([
                'user_id' => $this->defaultUserId,
                'name' => $income['name'],
                'amount' => $income['amount'],
                'currency' => $income['currency'],
                'first_income_date' => $income['firstIncomeDate'],
                'frequency' => $income['frequency'],
                'repeat_count' => $income['repeatCount'] ?? null
            ]);
        }
    }

    private function migrateSavings(array $savings): void {
        $savingModel = new Saving();

        foreach ($savings as $saving) {
            $savingModel->create([
                'user_id' => $this->defaultUserId,
                'name' => $saving['name'],
                'target_amount' => $saving['targetAmount'],
                'current_amount' => $saving['currentAmount'],
                'currency' => $saving['currency'],
                'start_date' => $saving['startDate'],
                'target_date' => $saving['targetDate']
            ]);
        }
    }

    private function migrateExchangeRates(array $rates): void {
        foreach ($rates as $baseCurrency => $targetRates) {
            foreach ($targetRates as $targetCurrency => $rate) {
                $this->db->insertOrUpdate('exchange_rates', [
                    'base_currency' => $baseCurrency,
                    'target_currency' => $targetCurrency,
                    'rate' => (float)$rate,
                    'fetched_at' => date('Y-m-d H:i:s')
                ], ['rate', 'fetched_at']);
            }
        }
    }

    private function verifyMigration(): void {
        $data = $this->getLocalStorageData();
        $checks = [
            'payments' => "SELECT COUNT(*) as count FROM payments",
            'incomes' => "SELECT COUNT(*) as count FROM incomes",
            'savings' => "SELECT COUNT(*) as count FROM savings",
            'categories' => "SELECT COUNT(*) as count FROM categories"
        ];

        foreach ($checks as $key => $query) {
            $result = $this->db->select($query);
            $dbCount = (int)$result[0]['count'];
            $jsonCount = count($data[$key] ?? []);

            if ($dbCount !== $jsonCount) {
                throw new Exception(sprintf(
                    "%s için veri tutarsızlığı. Beklenen: %d, Bulunan: %d",
                    $key, $jsonCount, $dbCount
                ));
            }
        }
    }

    private function getCategoryId(?string $categoryName): ?int {
        if (!$categoryName) {
            return null;
        }

        $result = $this->db->select(
            "SELECT id FROM categories WHERE user_id = :user_id AND name = :name",
            ['user_id' => $this->defaultUserId, 'name' => $categoryName]
        );

        return $result ? $result[0]['id'] : null;
    }
}

try {
    $migration = new DatabaseMigration();
    $migration->migrate();
} catch (Exception $e) {
    sendErrorResponse(
        $e->getMessage(),
        500,
        [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ]
    );
}
