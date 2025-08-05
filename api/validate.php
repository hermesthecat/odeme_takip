<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/ValidationManager.php';
checkLogin();

/**
 * Unified validation functions using ValidationManager
 * Maintains backward compatibility while using centralized validation rules
 */

// Get ValidationManager instance
$validationManager = ValidationManager::getInstance();

// Legacy validation functions for backward compatibility
function validateRequired($value, $field_name)
{
    if (empty($value)) {
        throw new Exception(sprintf(t('validation.field_required'), $field_name));
    }
    return $value;
}

function validateNumeric($value, $field_name)
{
    if (!is_numeric($value)) {
        throw new Exception(sprintf(t('validation.field_numeric'), $field_name));
    }
    return floatval($value);
}

function validateDate($value, $field_name)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new Exception(sprintf(t('validation.field_date'), $field_name));
    }
    return $value;
}

function validateCurrency($value, $field_name)
{
    global $validationManager;
    $valid_currencies = $validationManager->getSupportedCurrencies();
    if (!in_array($value, $valid_currencies)) {
        throw new Exception(sprintf(t('validation.field_currency'), $field_name));
    }
    return $value;
}

function validateFrequency($value, $field_name)
{
    global $validationManager;
    $valid_frequencies = $validationManager->getSupportedFrequencies();
    if (!in_array($value, $valid_frequencies)) {
        throw new Exception(sprintf(t('validation.field_frequency'), $field_name));
    }
    return $value;
}

function validateMinValue($value, $min, $field_name)
{
    if (floatval($value) < $min) {
        throw new Exception(sprintf(t('validation.field_min_value'), $field_name, $min));
    }
    return floatval($value);
}

function validateMaxValue($value, $max, $field_name)
{
    if (floatval($value) > $max) {
        throw new Exception(sprintf(t('validation.field_max_value'), $field_name, $max));
    }
    return floatval($value);
}

function validateDateRange($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    if ($start > $end) {
        throw new Exception(t('validation.date_range_error'));
    }
    return true;
}

/**
 * New unified validation functions using ValidationManager
 */

/**
 * Validate form data using ValidationManager
 * @param string $formType Form type (income, payment, saving, card, user_settings)
 * @param array $data Form data to validate
 * @return array Validated and sanitized data
 * @throws Exception On validation failure
 */
function validateFormData($formType, $data)
{
    global $validationManager;
    return $validationManager->validateForm($formType, $data);
}

/**
 * Validate single field using ValidationManager
 * @param string $formType Form type
 * @param string $fieldName Field name
 * @param mixed $value Field value
 * @return mixed Validated and sanitized value
 * @throws Exception On validation failure
 */
function validateField($formType, $fieldName, $value)
{
    global $validationManager;
    return $validationManager->validateField($formType, $fieldName, $value);
}

/**
 * Get client-side validation rules for JavaScript
 * @param string|null $formType Optional form type filter
 * @return array Validation rules for JavaScript consumption
 */
function getClientValidationRules($formType = null)
{
    global $validationManager;
    return $validationManager->getClientValidationRules($formType);
}
