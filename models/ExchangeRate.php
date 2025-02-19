<?php
require_once __DIR__ . '/../Model.php';

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';
    private const RATE_EXPIRY_HOURS = 24;

    // Get exchange rate, fetching from API if needed
    public function getRate(string $baseCurrency, string $targetCurrency): float
    {
        if ($baseCurrency === $targetCurrency) {
            return 1.0;
        }

        // Check cache first
        $query = "
            SELECT rate, fetched_at
            FROM exchange_rates
            WHERE base_currency = :base
            AND target_currency = :target
            AND fetched_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
            ORDER BY fetched_at DESC
            LIMIT 1";

        $result = $this->db->select($query, [
            'base' => $baseCurrency,
            'target' => $targetCurrency,
            'hours' => self::RATE_EXPIRY_HOURS
        ]);

        if ($result) {
            return (float)$result[0]['rate'];
        }

        // If not in cache or expired, fetch from API and store
        $rate = $this->fetchRateFromAPI($baseCurrency, $targetCurrency);
        $this->cacheRate($baseCurrency, $targetCurrency, $rate);

        return $rate;
    }

    // Cache exchange rate
    private function cacheRate(string $baseCurrency, string $targetCurrency, float $rate): void
    {
        $query = "
            INSERT INTO exchange_rates (base_currency, target_currency, rate, fetched_at)
            VALUES (:base, :target, :rate, NOW())
            ON DUPLICATE KEY UPDATE 
                rate = VALUES(rate),
                fetched_at = VALUES(fetched_at)";

        $this->db->insert($query, [
            'base' => $baseCurrency,
            'target' => $targetCurrency,
            'rate' => $rate
        ]);
    }

    // Get all current rates
    public function getCurrentRates(): array
    {
        $query = "
            SELECT DISTINCT ON (base_currency, target_currency)
                base_currency,
                target_currency,
                rate,
                fetched_at
            FROM exchange_rates
            WHERE fetched_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
            ORDER BY base_currency, target_currency, fetched_at DESC";

        return $this->db->select($query, ['hours' => self::RATE_EXPIRY_HOURS]);
    }

    // Cleanup old rates
    public function cleanupOldRates(): int
    {
        $query = "
            DELETE FROM exchange_rates 
            WHERE fetched_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)";

        return $this->db->delete($query, ['hours' => self::RATE_EXPIRY_HOURS]);
    }

    // Calculate conversion
    public function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        $rate = $this->getRate($fromCurrency, $toCurrency);
        return $amount * $rate;
    }

    // Fetch rate from external API
    private function fetchRateFromAPI(string $baseCurrency, string $targetCurrency): float
    {
        // TODO: Implement actual API call
        // This is a placeholder that should be replaced with actual API integration
        $apiUrl = sprintf(
            'https://api.example.com/forex?base=%s&target=%s',
            urlencode($baseCurrency),
            urlencode($targetCurrency)
        );

        // For now, return a dummy rate
        // In production, this should make an actual API call
        switch ("$baseCurrency-$targetCurrency") {
            case 'USD-TRY':
                return 31.5;
            case 'EUR-TRY':
                return 33.8;
            case 'EUR-USD':
                return 1.07;
            case 'USD-EUR':
                return 0.93;
            default:
                throw new Exception("Exchange rate not available for $baseCurrency to $targetCurrency");
        }
    }

    // Convert multiple amounts at once
    public function convertMultiple(array $amounts, string $fromCurrency, string $toCurrency): array
    {
        if ($fromCurrency === $toCurrency) {
            return $amounts;
        }

        $rate = $this->getRate($fromCurrency, $toCurrency);
        return array_map(fn($amount) => $amount * $rate, $amounts);
    }
}
