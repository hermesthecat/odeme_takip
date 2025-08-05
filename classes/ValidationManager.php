<?php

/**
 * Unified Validation Manager
 * Client ve Server-side validation kurallarını senkronize eder
 */
class ValidationManager
{
    private static $instance = null;
    private $rules = [];
    private $errorMessages = [];

    private function __construct()
    {
        $this->loadValidationConfig();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadValidationConfig()
    {
        $configPath = __DIR__ . '/../validation_config.json';
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            $this->rules = $config['validation_rules'] ?? [];
            $this->errorMessages = $config['error_messages'] ?? [];
        }
    }

    /**
     * Server-side validation
     */
    public function validateField($formType, $fieldName, $value)
    {
        if (!isset($this->rules[$formType][$fieldName])) {
            return $value; // No validation rules defined
        }

        $rules = $this->rules[$formType][$fieldName];

        // Required validation
        if (isset($rules['required']) && $rules['required']) {
            if (empty($value) && $value !== '0' && $value !== 0) {
                throw new Exception($this->getErrorMessage('required', $fieldName));
            }
        }

        // Skip other validations if value is empty and not required
        if (empty($value) && !($rules['required'] ?? false)) {
            return $value;
        }

        // Type validation
        switch ($rules['type'] ?? 'string') {
            case 'numeric':
                if (!is_numeric($value)) {
                    throw new Exception($this->getErrorMessage('numeric', $fieldName));
                }
                $value = floatval($value);
                break;

            case 'integer':
                if (!is_numeric($value) || floatval($value) != intval($value)) {
                    throw new Exception($this->getErrorMessage('numeric', $fieldName));
                }
                $value = intval($value);
                break;

            case 'date':
                $format = $rules['format'] ?? 'Y-m-d';
                $date = DateTime::createFromFormat($format, $value);
                if (!$date || $date->format($format) !== $value) {
                    throw new Exception($this->getErrorMessage('date', $fieldName));
                }
                break;

            case 'enum':
                if (!in_array($value, $rules['values'] ?? [])) {
                    throw new Exception($this->getErrorMessage('enum', $fieldName));
                }
                break;
        }

        // Min/Max value validation
        if (isset($rules['min_value']) && is_numeric($value)) {
            if (floatval($value) < $rules['min_value']) {
                throw new Exception($this->getErrorMessage('min_value', $fieldName, ['min' => $rules['min_value']]));
            }
        }

        if (isset($rules['max_value']) && is_numeric($value)) {
            if (floatval($value) > $rules['max_value']) {
                throw new Exception($this->getErrorMessage('max_value', $fieldName, ['max' => $rules['max_value']]));
            }
        }

        // String length validation
        if (isset($rules['min_length']) && is_string($value)) {
            if (strlen($value) < $rules['min_length']) {
                throw new Exception($this->getErrorMessage('min_length', $fieldName, ['min' => $rules['min_length']]));
            }
        }

        if (isset($rules['max_length']) && is_string($value)) {
            if (strlen($value) > $rules['max_length']) {
                throw new Exception($this->getErrorMessage('max_length', $fieldName, ['max' => $rules['max_length']]));
            }
        }

        return $value;
    }

    /**
     * Validate entire form data
     */
    public function validateForm($formType, $data)
    {
        $validatedData = [];
        $errors = [];

        if (!isset($this->rules[$formType])) {
            throw new Exception("Unknown form type: $formType");
        }

        foreach ($this->rules[$formType] as $fieldName => $rules) {
            try {
                $value = $data[$fieldName] ?? null;
                $validatedData[$fieldName] = $this->validateField($formType, $fieldName, $value);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode('; ', $errors));
        }

        return $validatedData;
    }

    /**
     * Get validation rules for JavaScript
     */
    public function getClientValidationRules($formType = null)
    {
        if ($formType) {
            return $this->rules[$formType] ?? [];
        }
        return $this->rules;
    }

    /**
     * Get error message with placeholders
     */
    private function getErrorMessage($type, $fieldName, $params = [])
    {
        $message = $this->errorMessages[$type] ?? "Validation error for $fieldName";
        
        // Replace placeholders
        foreach ($params as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }

    /**
     * Get supported currencies
     */
    public function getSupportedCurrencies()
    {
        $configPath = __DIR__ . '/../validation_config.json';
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            return $config['supported_currencies'] ?? ['TRY', 'USD', 'EUR', 'GBP'];
        }
        return ['TRY', 'USD', 'EUR', 'GBP'];
    }

    /**
     * Get supported frequencies
     */
    public function getSupportedFrequencies()
    {
        $configPath = __DIR__ . '/../validation_config.json';
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
            return $config['supported_frequencies'] ?? ['none', 'monthly', 'yearly'];
        }
        return ['none', 'monthly', 'yearly'];
    }
}