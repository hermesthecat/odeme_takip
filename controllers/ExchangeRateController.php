<?php
require_once __DIR__ . '/Controller.php';

class ExchangeRateController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ExchangeRate();
    }

    public function handleRequest(string $method, array $pathParts, array $data): array
    {
        if ($method !== 'GET') {
            return $this->respondWithError('Method not allowed', 405);
        }

        switch ($pathParts[0] ?? '') {
            case 'current':
                return $this->getCurrentRates();

            case 'convert':
                if (!isset($pathParts[1], $pathParts[2], $pathParts[3])) {
                    return $this->respondWithError('Missing parameters');
                }
                return $this->convert($pathParts[1], $pathParts[2], $pathParts[3]);

            default:
                if (isset($pathParts[0], $pathParts[1])) {
                    return $this->getRate($pathParts[0], $pathParts[1]);
                }
                return $this->respondWithError('Invalid endpoint');
        }
    }

    private function getCurrentRates(): array
    {
        $rates = $this->model->getCurrentRates();

        // Clean up old rates while we're at it
        $this->model->cleanupOldRates();

        return array_map(function ($rate) {
            return [
                'from' => $rate['base_currency'],
                'to' => $rate['target_currency'],
                'rate' => (float)$rate['rate'],
                'updated' => $rate['fetched_at']
            ];
        }, $rates);
    }

    private function getRate(string $from, string $to): array
    {
        try {
            $rate = $this->model->getRate($from, $to);
            return [
                'from' => $from,
                'to' => $to,
                'rate' => $rate
            ];
        } catch (Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }

    private function convert(string $from, string $to, string $amount): array
    {
        if (!is_numeric($amount)) {
            return $this->respondWithError('Invalid amount');
        }

        try {
            $converted = $this->model->convert((float)$amount, $from, $to);
            return [
                'from' => [
                    'currency' => $from,
                    'amount' => (float)$amount
                ],
                'to' => [
                    'currency' => $to,
                    'amount' => $converted
                ],
                'rate' => $this->model->getRate($from, $to)
            ];
        } catch (Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }
}
