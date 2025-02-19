<?php
require_once __DIR__ . '/../config.php';
checkLogin();

require_once __DIR__ . '/./currency.php';

// Tekrar sayısını hesaplama fonksiyonu
function calculateRepeatCount($frequency)
{
    switch ($frequency) {
        case 'none':
            return 1;
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 1;
    }
}

// İki tarih arasındaki ay sayısını hesaplama
function getMonthDifference($start_date, $end_date)
{
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);

    $interval = $start->diff($end);
    return $interval->y * 12 + $interval->m;
}

// Tekrarlama sıklığına göre ay aralığını hesapla
function getMonthInterval($frequency)
{
    switch ($frequency) {
        case 'monthly':
            return 1;
        case 'bimonthly':
            return 2;
        case 'quarterly':
            return 3;
        case 'fourmonthly':
            return 4;
        case 'fivemonthly':
            return 5;
        case 'sixmonthly':
            return 6;
        case 'yearly':
            return 12;
        default:
            return 0;
    }
}

// Sonraki ödeme tarihini hesapla
function calculateNextPaymentDate($date, $months)
{
    $next_date = new DateTime($date);
    $next_date->modify('+' . $months . ' months');
    return $next_date->format('Y-m-d');
}

// Sonraki tarihi hesaplama fonksiyonu
function calculateNextDate($date, $frequency, $count = 1)
{
    $nextDate = new DateTime($date);

    switch ($frequency) {
        case 'monthly':
            $nextDate->modify('+' . $count . ' month');
            break;
        case 'bimonthly':
            $nextDate->modify('+' . ($count * 2) . ' months');
            break;
        case 'quarterly':
            $nextDate->modify('+' . ($count * 3) . ' months');
            break;
        case 'fourmonthly':
            $nextDate->modify('+' . ($count * 4) . ' months');
            break;
        case 'fivemonthly':
            $nextDate->modify('+' . ($count * 5) . ' months');
            break;
        case 'sixmonthly':
            $nextDate->modify('+' . ($count * 6) . ' months');
            break;
        case 'yearly':
            $nextDate->modify('+' . $count . ' year');
            break;
        default:
            return $date;
    }

    return $nextDate->format('Y-m-d');
}


// Sayısal değerleri formatla
function formatNumber($number, $decimals = 2)
{
    if (!is_numeric($number)) {
        return '0.00';
    }
    return number_format((float)$number, $decimals, '.', '');
}
