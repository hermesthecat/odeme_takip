<?php

require_once __DIR__ . '/../config.php';
checkLogin();

function loadSummary()
{
    global $pdo, $user_id, $month, $year;

    // Kullanıcının ana para birimini al
    $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
    if (!$stmt->execute([$user_id])) {
        throw new Exception(t('summary.user_not_found'));
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $base_currency = $user['base_currency'];

    // Özet için gerekli verileri al
    $sql_summary = "SELECT 
                        (SELECT COALESCE(SUM(CASE 
                            WHEN i.currency = ? THEN i.amount 
                            ELSE i.amount * COALESCE(i.exchange_rate, 1) 
                        END), 0)
                        FROM income i 
                        WHERE i.user_id = ? 
                        AND MONTH(i.first_date) = ? 
                        AND YEAR(i.first_date) = ?) as total_income,
                        
                        (SELECT COALESCE(SUM(CASE 
                            WHEN p.currency = ? THEN p.amount 
                            ELSE p.amount * COALESCE(p.exchange_rate, 1) 
                        END), 0)
                        FROM payments p 
                        WHERE p.user_id = ? 
                        AND MONTH(p.first_date) = ? 
                        AND YEAR(p.first_date) = ?) as total_expense";

    $stmt_summary = $pdo->prepare($sql_summary);
    if (!$stmt_summary->execute([$base_currency, $user_id, $month, $year, $base_currency, $user_id, $month, $year])) {
        throw new Exception(t('summary.load_error'));
    }
    $summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

    // Ek özet bilgilerini hesapla
    $summary['net_balance'] = $summary['total_income'] - $summary['total_expense'];
    $summary['currency'] = $base_currency;
    $summary['month'] = $month;
    $summary['year'] = $year;
    $summary['month_name'] = t('months.' . $month);
    $summary['status'] = $summary['net_balance'] >= 0 ? t('summary.positive_balance') : t('summary.negative_balance');
    $summary['percentage'] = $summary['total_income'] > 0 ? 
        round(($summary['total_expense'] / $summary['total_income']) * 100, 2) : 0;

    return $summary;
}
