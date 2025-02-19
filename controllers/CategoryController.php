<?php
require_once __DIR__ . '/Controller.php';

class CategoryController extends Controller
{
    private $model;

    public function __construct(int $userId = 1)
    {
        parent::__construct($userId);
        $this->model = new Category();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        switch ($method) {
            case 'GET':
                if (isset($pathParts[0]) && is_numeric($pathParts[0])) {
                    if (isset($pathParts[1]) && $pathParts[1] === 'spending') {
                        return $this->getCategorySpending(
                            (int)$pathParts[0],
                            $pathParts[2] ?? null,
                            $pathParts[3] ?? null
                        );
                    }
                    return $this->getCategory($this->getIdFromPath($pathParts));
                }
                return $this->getAllCategories();

            case 'POST':
                if (!$this->validateRequired($data, ['name', 'monthly_limit'])) {
                    return $this->respondWithError('Missing required fields');
                }
                return $this->createCategory($data);

            case 'PUT':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Category ID is required');
                }
                return $this->updateCategory($id, $data);

            case 'DELETE':
                $id = $this->getIdFromPath($pathParts);
                if (!$id) {
                    return $this->respondWithError('Category ID is required');
                }
                return $this->deleteCategory($id);

            default:
                return $this->respondWithError('Method not allowed', 405);
        }
    }

    private function getAllCategories(): array
    {
        return $this->model->whereAnd([
            'user_id' => $this->userId
        ]);
    }

    private function getCategory(int $id): array
    {
        $category = $this->model->find($id);
        if (!$category || $category['user_id'] !== $this->userId) {
            return $this->respondWithError('Category not found', 404);
        }
        return $category;
    }

    private function getCategorySpending(int $id, ?string $year, ?string $month): array
    {
        // Ensure the category belongs to the user
        $category = $this->model->find($id);
        if (!$category || $category['user_id'] !== $this->userId) {
            return $this->respondWithError('Category not found', 404);
        }

        if (!$year || !$month || !is_numeric($year) || !is_numeric($month)) {
            return $this->respondWithError('Invalid year or month');
        }

        return $this->model->getCategoriesWithSpending(
            $this->userId,
            (int)$year,
            (int)$month
        );
    }

    private function createCategory(array $data): array
    {
        $data['user_id'] = $this->userId;

        // Check if category name already exists for this user
        $existing = $this->model->whereAnd([
            'user_id' => $this->userId,
            'name' => $data['name']
        ]);

        if (!empty($existing)) {
            return $this->respondWithError('Category name already exists');
        }

        $id = $this->model->create($data);
        return ['id' => $id];
    }

    private function updateCategory(int $id, array $data): array
    {
        // Ensure the category belongs to the user
        $category = $this->model->find($id);
        if (!$category || $category['user_id'] !== $this->userId) {
            return $this->respondWithError('Category not found', 404);
        }

        // If name is being changed, check for duplicates
        if (isset($data['name']) && $data['name'] !== $category['name']) {
            $existing = $this->model->whereAnd([
                'user_id' => $this->userId,
                'name' => $data['name']
            ]);

            if (!empty($existing)) {
                return $this->respondWithError('Category name already exists');
            }
        }

        $data['user_id'] = $this->userId;
        $this->model->update($id, $data);
        return ['success' => true];
    }

    private function deleteCategory(int $id): array
    {
        // Ensure the category belongs to the user
        $category = $this->model->find($id);
        if (!$category || $category['user_id'] !== $this->userId) {
            return $this->respondWithError('Category not found', 404);
        }

        $this->model->delete($id);
        return ['success' => true];
    }
}
