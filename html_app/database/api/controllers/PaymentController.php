<?php
require_once __DIR__ . '/Controller.php';

class PaymentController extends Controller
{
    private $model;

    public function __construct(int $userId = 1)
    {
        parent::__construct($userId);
        $this->model = new Payment();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($pathParts[0]) && $pathParts[0] === 'monthly') {
                    return $this->getMonthlyPayments($pathParts[1] ?? null, $pathParts[2] ?? null);
                } elseif (isset($pathParts[0])) {
                    return $this->getPayment($this->getIdFromPath($pathParts));
                }
                return $this->getAllPayments();

            case 'POST':
                if (!$this->validateRequired($data, ['name', 'amount', 'currency', 'first_payment_date'])) {
                    return $this->respondWithError('Missing required fields');
                }
                return $this->createPayment($data);

            case 'PUT':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Payment ID is required');
                }

                if (isset($pathParts[1]) && $pathParts[1] === 'status') {
                    if (!$this->validateRequired($data, ['year', 'month', 'isPaid'])) {
                        return $this->respondWithError('Missing required fields for status update');
                    }
                    return $this->updatePaymentStatus($id, $data);
                }

                return $this->updatePayment($id, $data);

            case 'DELETE':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Payment ID is required');
                }
                return $this->deletePayment($id);

            default:
                return $this->respondWithError('Method not allowed', 405);
        }
    }

    private function getMonthlyPayments(?string $year, ?string $month): array
    {
        if (!$year || !$month || !is_numeric($year) || !is_numeric($month)) {
            return $this->respondWithError('Invalid year or month');
        }

        return $this->model->getMonthlyPayments(
            $this->userId,
            (int)$year,
            (int)$month
        );
    }

    private function getAllPayments(): array
    {
        return $this->model->whereAnd([
            'user_id' => $this->userId
        ]);
    }

    private function getPayment(int $id): array
    {
        $payment = $this->model->find($id);
        if (!$payment || $payment['user_id'] !== $this->userId) {
            return $this->respondWithError('Payment not found', 404);
        }
        return $payment;
    }

    private function createPayment(array $data): array
    {
        $data['user_id'] = $this->userId;

        // Handle optional fields
        $data['frequency'] = $data['frequency'] ?? 0;
        $data['repeat_count'] = $data['repeat_count'] ?? null;
        $data['category_id'] = $data['category_id'] ?? null;

        $id = $this->model->createWithStatus($data, $data['is_paid'] ?? false);
        return ['id' => $id];
    }

    private function updatePayment(int $id, array $data): array
    {
        // Ensure the payment belongs to the user
        $payment = $this->model->find($id);
        if (!$payment || $payment['user_id'] !== $this->userId) {
            return $this->respondWithError('Payment not found', 404);
        }

        $data['user_id'] = $this->userId;
        $this->model->update($id, $data);
        return ['success' => true];
    }

    private function updatePaymentStatus(int $id, array $data): array
    {
        // Ensure the payment belongs to the user
        $payment = $this->model->find($id);
        if (!$payment || $payment['user_id'] !== $this->userId) {
            return $this->respondWithError('Payment not found', 404);
        }

        $success = $this->model->updateStatus(
            $id,
            (int)$data['year'],
            (int)$data['month'],
            (bool)$data['isPaid']
        );

        return ['success' => $success];
    }

    private function deletePayment(int $id): array
    {
        // Ensure the payment belongs to the user
        $payment = $this->model->find($id);
        if (!$payment || $payment['user_id'] !== $this->userId) {
            return $this->respondWithError('Payment not found', 404);
        }

        $this->model->delete($id);
        return ['success' => true];
    }
}
