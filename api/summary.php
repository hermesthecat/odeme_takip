<?php

require_once __DIR__ . '/../config.php';
checkLogin();

function loadSummary()
{
    global $pdo, $user_id, $month, $year;

    // Cache'i kontrol et önce - performans optimizasyonu
    $cached_summary = getCachedSummary($user_id, $month, $year);
    if ($cached_summary) {
        return $cached_summary;
    }

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception(t('summary.user_not_found'));
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Optimize edilmiş ayrı query'ler - index'leri kullanabilir
    $date_start = sprintf('%04d-%02d-01', $year, $month);
    $date_end = date('Y-m-t', strtotime($date_start)); // Ayın son günü
    
    // Gelir toplamı - optimize edilmiş query
    $sql_income = "SELECT COALESCE(SUM(CASE 
                        WHEN currency = ? THEN amount 
                        ELSE amount * COALESCE(exchange_rate, 1) 
                    END), 0) as total_income
                   FROM income 
                   WHERE user_id = ? 
                   AND first_date >= ? 
                   AND first_date <= ?";
    
    $stmt_income = $pdo->prepare($sql_income);
    if (!$stmt_income->execute([$base_currency, $user_id, $date_start, $date_end])) {
        throw new Exception(t('summary.load_error'));
    }
    $income_result = $stmt_income->fetch(PDO::FETCH_ASSOC);
    
    // Gider toplamı - optimize edilmiş query
    $sql_expense = "SELECT COALESCE(SUM(CASE 
                        WHEN currency = ? THEN amount 
                        ELSE amount * COALESCE(exchange_rate, 1) 
                    END), 0) as total_expense
                   FROM payments 
                   WHERE user_id = ? 
                   AND first_date >= ? 
                   AND first_date <= ?";
    
    $stmt_expense = $pdo->prepare($sql_expense);
    if (!$stmt_expense->execute([$base_currency, $user_id, $date_start, $date_end])) {
        throw new Exception(t('summary.load_error'));
    }
    $expense_result = $stmt_expense->fetch(PDO::FETCH_ASSOC);
    
    // Sonuçları birleştir
    $summary = [
        'total_income' => $income_result['total_income'],
        'total_expense' => $expense_result['total_expense']
    ];

    // Ek özet bilgilerini hesapla
    $summary['net_balance'] = $summary['total_income'] - $summary['total_expense'];
    $summary['currency'] = $base_currency;
    $summary['month'] = $month;
    $summary['year'] = $year;
    $summary['month_name'] = t('months.' . $month);
    $summary['status'] = $summary['net_balance'] >= 0 ? t('summary.positive_balance') : t('summary.negative_balance');
    $summary['percentage'] = $summary['total_income'] > 0 ?
        round(($summary['total_expense'] / $summary['total_income']) * 100, 2) : 0;

    // Cache'e kaydet - performans optimizasyonu
    cacheSummary($user_id, $month, $year, $summary);

    return $summary;
}
