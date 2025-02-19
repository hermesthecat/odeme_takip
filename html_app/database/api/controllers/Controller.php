<?php
abstract class Controller
{
    protected $userId;

    public function __construct(int $userId = 1)
    {
        $this->userId = $userId;
    }

    abstract public function handleRequest(string $method, array $pathParts, array $data);

    protected function respondWithError(string $message, int $code = 400): array
    {
        http_response_code($code);
        return [
            'error' => $message
        ];
    }

    protected function validateRequired(array $data, array $fields): bool
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function getIdFromPath(array $pathParts): ?int
    {
        return isset($pathParts[0]) && is_numeric($pathParts[0]) ? (int)$pathParts[0] : null;
    }
}
