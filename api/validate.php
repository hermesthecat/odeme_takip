<?php

require_once __DIR__ . '/../config.php';
checkLogin();

// Validasyon fonksiyonları
function validateRequired($value, $field_name)
{
    if (empty($value)) {
        throw new Exception($field_name . " alanı zorunludur");
    }
    return $value;
}

function validateNumeric($value, $field_name)
{
    if (!is_numeric($value)) {
        throw new Exception($field_name . " alanı sayısal olmalıdır");
    }
    return floatval($value);
}

function validateDate($value, $field_name)
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        throw new Exception($field_name . " alanı geçerli bir tarih olmalıdır (YYYY-MM-DD)");
    }
    return $value;
}

function validateCurrency($value, $field_name)
{
    $valid_currencies = ['TRY', 'USD', 'EUR', 'GBP'];
    if (!in_array($value, $valid_currencies)) {
        throw new Exception($field_name . " alanı geçerli bir para birimi olmalıdır");
    }
    return $value;
}

function validateFrequency($value, $field_name)
{
    $valid_frequencies = ['none', 'monthly', 'bimonthly', 'quarterly', 'fourmonthly', 'fivemonthly', 'sixmonthly', 'yearly'];
    if (!in_array($value, $valid_frequencies)) {
        throw new Exception($field_name . " alanı geçerli bir tekrarlama sıklığı olmalıdır");
    }
    return $value;
}

function validateMinValue($value, $min, $field_name)
{
    if (floatval($value) < $min) {
        throw new Exception($field_name . " alanı en az " . $min . " olmalıdır");
    }
    return floatval($value);
}

function validateMaxValue($value, $max, $field_name)
{
    if (floatval($value) > $max) {
        throw new Exception($field_name . " alanı en fazla " . $max . " olmalıdır");
    }
    return floatval($value);
}

function validateDateRange($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    if ($start > $end) {
        throw new Exception("Başlangıç tarihi bitiş tarihinden büyük olamaz");
    }
    return true;
}
