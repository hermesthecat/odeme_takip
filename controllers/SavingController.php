<?php
require_once __DIR__ . '/Controller.php';

class SavingController extends Controller
{
    private $model;

    public function __construct(int $userId = 1)
    {
        parent::__construct($userId);
        $this->model = new Saving();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($pathParts[0]) && $pathParts[0] === 'active') {
                    return $this->getActiveSavings();
                } elseif (isset($pathParts[0])) {
                    return $this->getSaving($this->getIdFromPath($pathParts));
                }
                return $this->getAllSavings();

            case 'POST':
                if (!$this->validateRequired($data, [
                    'name',
                    'target_amount',
                    'currency',
                    'start_date',
                    'target_date'
                ])) {
                    return $this->respondWithError('Missing required fields');
                }
                return $this->createSaving($data);

            case 'PUT':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Saving ID is required');
                }

                if (isset($pathParts[1]) && $pathParts[1] === 'progress') {
                    if (!isset($data['amount'])) {
                        return $this->respondWithError('Amount is required for progress update');
                    }
                    return $this->updateProgress($id, $data['amount']);
                }

                return $this->updateSaving($id, $data);

            case 'DELETE':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Saving ID is required');
                }
                return $this->deleteSaving($id);

            default:
                return $this->respondWithError('Method not allowed', 405);
        }
    }

    private function getActiveSavings(): array
    {
        return $this->model->getActiveSavings($this->userId);
    }

    private function getAllSavings(): array
    {
        return $this->model->getAllWithProgress($this->userId);
    }

    private function getSaving(int $id): array
    {
        $saving = $this->model->find($id);
        if (!$saving || $saving['user_id'] !== $this->userId) {
            return $this->respondWithError('Saving not found', 404);
        }
        return $saving;
    }

    private function createSaving(array $data): array
    {
        $data['user_id'] = $this->userId;
        $data['current_amount'] = $data['current_amount'] ?? 0;

        $id = $this->model->create($data);
        return ['id' => $id];
    }

    private function updateSaving(int $id, array $data): array
    {
        // Ensure the saving belongs to the user
        $saving = $this->model->find($id);
        if (!$saving || $saving['user_id'] !== $this->userId) {
            return $this->respondWithError('Saving not found', 404);
        }

        $data['user_id'] = $this->userId;
        $this->model->update($id, $data);
        return ['success' => true];
    }

    private function updateProgress(int $id, float $amount): array
    {
        // Ensure the saving belongs to the user
        $saving = $this->model->find($id);
        if (!$saving || $saving['user_id'] !== $this->userId) {
            return $this->respondWithError('Saving not found', 404);
        }

        $success = $this->model->updateProgress($id, $amount);
        if (!$success) {
            return $this->respondWithError('Failed to update progress');
        }

        return [
            'success' => true,
            'current_amount' => $amount,
            'progress' => ($amount / $saving['target_amount']) * 100
        ];
    }

    private function deleteSaving(int $id): array
    {
        // Ensure the saving belongs to the user
        $saving = $this->model->find($id);
        if (!$saving || $saving['user_id'] !== $this->userId) {
            return $this->respondWithError('Saving not found', 404);
        }

        $this->model->delete($id);
        return ['success' => true];
    }
}
