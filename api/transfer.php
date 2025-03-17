<?php

require_once __DIR__ . '/../config.php';
checkLogin();

require_once __DIR__ . '/./currency.php';

function transferUnpaidPayments()
{
    global $pdo, $user_id;

    try {
        // Kullanıcının ana para birimini al
        $stmt = $pdo->prepare("SELECT base_currency FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $base_currency = $user['base_currency'];

        // Mevcut ayın ödenmemiş ödemelerini al
        $stmt = $pdo->prepare("SELECT * FROM payments 
                              WHERE user_id = ? 
                              AND MONTH(first_date) = ? 
                              AND YEAR(first_date) = ? 
                              AND status = 'pending'");
        $stmt->execute([
            $user_id,
            $_POST['current_month'],
            $_POST['current_year']
        ]);
        $unpaid_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($unpaid_payments)) {
            throw new Exception(t('transfer.no_unpaid_payments'));
        }

        // Ay ve yıl hesaplama
        $next_month = $_POST['next_month'];
        $next_year = $_POST['next_year'];
        $current_month_name = date('F', mktime(0, 0, 0, $_POST['current_month'], 1));

        foreach ($unpaid_payments as $payment) {
            // Yeni ödeme tarihi
            $new_date = date('Y-m-d', mktime(0, 0, 0, $next_month, date('d', strtotime($payment['first_date'])), $next_year));

            // Kur bilgisini al
            $exchange_rate = null;
            if ($payment['currency'] !== $base_currency) {
                $exchange_rate = getExchangeRate($payment['currency'], $base_currency);
            }

            // Mevcut kaydı güncelle
            $stmt = $pdo->prepare("UPDATE payments SET 
                first_date = ?,
                name = ?,
                exchange_rate = ?
                WHERE id = ? AND user_id = ?");

            // Eğer ödeme zaten aktarılmış bir ödeme ise, adını değiştirme
            if (strpos($payment['name'], t('transfer.payment_transferred_from')) !== false) {
                $new_name = $payment['name'];
            } else {
                $new_name = sprintf(t('transfer.payment_transferred_from'), $payment['name'], $current_month_name);
            }

            if (!$stmt->execute([
                $new_date,
                $new_name,
                $exchange_rate,
                $payment['id'],
                $user_id
            ])) {
                throw new Exception(t('transfer.update_error'));
            }
        }

        return true;
    } catch (Exception $e) {
        throw $e;
    }
}
