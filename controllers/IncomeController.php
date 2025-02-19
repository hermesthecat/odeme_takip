<?php
require_once __DIR__ . '/Controller.php';

class IncomeController extends Controller
{
    private $model;

    public function __construct(int $userId = 1)
    {
        parent::__construct($userId);
        $this->model = new Income();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($pathParts[0]) && $pathParts[0] === 'monthly') {
                    return $this->getMonthlyIncomes($pathParts[1] ?? null, $pathParts[2] ?? null);
                } elseif (isset($pathParts[0])) {
                    return $this->getIncome($this->getIdFromPath($pathParts));
                }
                return $this->getAllIncomes();

            case 'POST':
                if (!$this->validateRequired($data, ['name', 'amount', 'currency', 'first_income_date'])) {
                    return $this->respondWithError('Missing required fields');
                }
                return $this->createIncome($data);

            case 'PUT':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Income ID is required');
                }
                return $this->updateIncome($id, $data);

            case 'DELETE':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Income ID is required');
                }
                return $this->deleteIncome($id);

            default:
                return $this->respondWithError('Method not allowed', 405);
        }
    }

    private function getMonthlyIncomes(?string $year, ?string $month): array
    {
        if (!$year || !$month || !is_numeric($year) || !is_numeric($month)) {
            return $this->respondWithError('Invalid year or month');
        }

        return $this->model->getMonthlyIncomes(
            $this->userId,
            (int)$year,
            (int)$month
        );
    }

    private function getAllIncomes(): array
    {
        return $this->model->whereAnd([
            'user_id' => $this->userId
        ]);
    }

    private function getIncome(int $id): array
    {
        $income = $this->model->find($id);
        if (!$income || $income['user_id'] !== $this->userId) {
            return $this->respondWithError('Income not found', 404);
        }
        return $income;
    }

    private function createIncome(array $data): array
    {
        $data['user_id'] = $this->userId;

        // Handle optional fields
        $data['frequency'] = $data['frequency'] ?? 0;
        $data['repeat_count'] = $data['repeat_count'] ?? null;

        $id = $this->model->create($data);
        return ['id' => $id];
    }

    private function updateIncome(int $id, array $data): array
    {
        // Ensure the income belongs to the user
        $income = $this->model->find($id);
        if (!$income || $income['user_id'] !== $this->userId) {
            return $this->respondWithError('Income not found', 404);
        }

        $data['user_id'] = $this->userId;
        $this->model->update($id, $data);
        return ['success' => true];
    }

    private function deleteIncome(int $id): array
    {
        // Ensure the income belongs to the user
        $income = $this->model->find($id);
        if (!$income || $income['user_id'] !== $this->userId) {
            return $this->respondWithError('Income not found', 404);
        }

        $this->model->delete($id);
        return ['success' => true];
    }
}
