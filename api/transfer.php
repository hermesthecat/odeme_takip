<?php

require_once __DIR__ . '/../config.php';
checkLogin();

require_once __DIR__ . '/./currency.php';


function transferUnpaidPayments()
{
    global $pdo, $user_id;

    $exchange_rate = getExchangeRate($_POST['currency'], 'TRY');

    $pdo->beginTransaction();

    // Mevcut ayın ödenmemiş ödemelerini al (hem ana hem de tekrarlanan ödemeler)
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND MONTH(first_date) = ? AND YEAR(first_date) = ? AND status = 'pending'");
    $stmt->execute([$user_id, $_POST['current_month'], $_POST['current_year']]);
    $unpaid_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ay ve yıl hesaplama
    $next_month = $_POST['current_month'] == 12 ? 1 : $_POST['current_month'] + 1;
    $next_year = $_POST['current_month'] == 12 ? $_POST['current_year'] + 1 : $_POST['current_year'];
    $current_month_name = date('F', mktime(0, 0, 0, $_POST['current_month'], 1));

    foreach ($unpaid_payments as $payment) {
        // Yeni ödeme tarihi
        $new_date = date('Y-m-d', mktime(0, 0, 0, $next_month, date('d', strtotime($payment['first_date'])), $next_year));

        // Eğer tekrarlanan bir ödeme ise
        if ($payment['frequency'] !== 'none') {
            // Ana kayıt ise
            if ($payment['parent_id'] === null) {
                // Yeni ana kaydı ekle
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) 
                                     VALUES (?, NULL, ?, ?, ?, ?, ?, ?)");
                $new_name = $payment['name'] . ' (' . $current_month_name . ' Ayından Aktarıldı)';

                if (!$stmt->execute([
                    $user_id,
                    $new_name,
                    $payment['amount'],
                    $payment['currency'],
                    $new_date,
                    $payment['frequency'],
                    $exchange_rate
                ])) {
                    throw new Exception("Yeni ana kayıt eklenemedi");
                }

                $new_parent_id = $pdo->lastInsertId();

                // Child kayıtları da aktar
                $stmt = $pdo->prepare("SELECT * FROM payments WHERE parent_id = ? AND first_date > ?");
                $stmt->execute([$payment['id'], $payment['first_date']]);
                $child_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($child_payments as $child) {
                    $child_new_date = date('Y-m-d', strtotime($child['first_date'] . ' +1 month'));
                    $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if (!$stmt->execute([
                        $user_id,
                        $new_parent_id,
                        $new_name,
                        $child['amount'],
                        $child['currency'],
                        $child_new_date,
                        $child['frequency'],
                        $exchange_rate
                    ])) {
                        throw new Exception("Child kayıt eklenemedi");
                    }
                }
            }
        } else {
            // Tekrarlanmayan ödeme ise
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, parent_id, name, amount, currency, first_date, frequency, exchange_rate) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $new_name = $payment['name'] . ' (' . $current_month_name . ' Ayından Aktarıldı)';

            if (!$stmt->execute([
                $user_id,
                $payment['parent_id'],
                $new_name,
                $payment['amount'],
                $payment['currency'],
                $new_date,
                $payment['frequency'],
                $exchange_rate
            ])) {
                throw new Exception("Yeni ödeme eklenemedi");
            }
        }

        // Eski ödemeyi sil
        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND user_id = ?");
        if (!$stmt->execute([$payment['id'], $user_id])) {
            throw new Exception("Eski ödeme silinemedi");
        }
    }

    $pdo->commit();
    return true;
}
