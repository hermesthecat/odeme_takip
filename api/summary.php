<?php

require_once __DIR__ . '/../config.php';
checkLogin();


function loadSummary()
{
    global $pdo, $user_id, $month, $year;

    // Özet için gerekli verileri al
    $sql_summary = "SELECT 
                        (SELECT COALESCE(SUM(CASE 
                            WHEN i.currency = 'TRY' THEN i.amount 
                            ELSE i.amount * i.exchange_rate 
                        END), 0)
                        FROM income i 
                        WHERE i.user_id = ? 
                        AND MONTH(i.first_date) = ? 
                        AND YEAR(i.first_date) = ?) as total_income,
                        
                        (SELECT COALESCE(SUM(CASE 
                            WHEN p.currency = 'TRY' THEN p.amount 
                            ELSE p.amount * p.exchange_rate 
                        END), 0)
                        FROM payments p 
                        WHERE p.user_id = ? 
                        AND MONTH(p.first_date) = ? 
                        AND YEAR(p.first_date) = ?) as total_expense";

    $stmt_summary = $pdo->prepare($sql_summary);
    $stmt_summary->execute([$user_id, $month, $year, $user_id, $month, $year]);
    $summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);
    return $summary;
}
