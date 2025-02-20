<?php

require_once __DIR__ . '/../config.php';
checkLogin();

// Validasyon fonksiyonları
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
    $valid_currencies = ['TRY', 'USD', 'EUR', 'GBP'];
    if (!in_array($value, $valid_currencies)) {
        throw new Exception(sprintf(t('validation.field_currency'), $field_name));
    }
    return $value;
}

function validateFrequency($value, $field_name)
{
    $valid_frequencies = ['none', 'monthly', 'bimonthly', 'quarterly', 'fourmonthly', 'fivemonthly', 'sixmonthly', 'yearly'];
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
