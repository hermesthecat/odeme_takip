<?php
require_once __DIR__ . '/Controller.php';

class BudgetGoalController extends Controller
{
    private $model;

    public function __construct(int $userId = 1)
    {
        parent::__construct($userId);
        $this->model = new BudgetGoal();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($pathParts[0])) {
                    if ($pathParts[0] === 'monthly') {
                        return $this->getMonthlyBudget($pathParts[1] ?? null, $pathParts[2] ?? null);
                    } elseif ($pathParts[0] === 'yearly') {
                        return $this->getYearlySummary($pathParts[1] ?? null);
                    }
                }
                return $this->getBudgetStats();

            case 'POST':
                if (isset($pathParts[0]) && $pathParts[0] === 'monthly') {
                    if (!$this->validateRequired($data, ['year', 'month', 'limit'])) {
                        return $this->respondWithError('Missing required fields');
                    }
                    return $this->setMonthlyBudget($data);
                }
                return $this->respondWithError('Invalid endpoint');

            default:
                return $this->respondWithError('Method not allowed', 405);
        }
    }

    private function getMonthlyBudget(?string $year, ?string $month): array
    {
        if (!$year || !$month || !is_numeric($year) || !is_numeric($month)) {
            return $this->respondWithError('Invalid year or month');
        }

        $budget = $this->model->getMonthlyBudget(
            $this->userId,
            (int)$year,
            (int)$month
        );

        return $budget ?? [
            'user_id' => $this->userId,
            'year' => (int)$year,
            'month' => (int)$month,
            'limit_amount' => 0,
            'total_spent' => 0,
            'total_income' => 0
        ];
    }

    private function getYearlySummary(?string $year): array
    {
        if (!$year || !is_numeric($year)) {
            return $this->respondWithError('Invalid year');
        }

        return $this->model->getYearlySummary(
            $this->userId,
            (int)$year
        );
    }

    private function getBudgetStats(): array
    {
        return $this->model->getBudgetStats($this->userId) ?? [
            'total_months' => 0,
            'months_with_budget' => 0,
            'average_budget' => 0,
            'months_within_budget' => 0
        ];
    }

    private function setMonthlyBudget(array $data): array
    {
        $success = $this->model->setMonthlyBudget(
            $this->userId,
            (int)$data['year'],
            (int)$data['month'],
            (float)$data['limit']
        );

        if (!$success) {
            return $this->respondWithError('Failed to set budget');
        }

        return [
            'success' => true,
            'budget' => $this->getMonthlyBudget($data['year'], $data['month'])
        ];
    }
}
